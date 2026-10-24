<?php
declare(strict_types=1);

namespace App;

use PDO;

final class FeeManagement
{
    private static bool $bootstrapped = false;

    public static function ensureTable(): void
    {
        if (self::$bootstrapped) return;
        $db = Database::connect();
        $db->exec("
            CREATE TABLE IF NOT EXISTS `student_fees` (
              `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
              `school_id` bigint(20) UNSIGNED DEFAULT NULL,
              `batch_id` bigint(20) UNSIGNED DEFAULT NULL,
              `student_user_id` bigint(20) UNSIGNED NOT NULL,
              `academic_year` varchar(20) NOT NULL DEFAULT '2026-2027',
              `fee_month` varchar(20) NOT NULL,
              `fee_type` enum('tuition','admission','transport','exam','chess_academy','activity') NOT NULL DEFAULT 'tuition',
              `amount_due` decimal(10,2) NOT NULL DEFAULT 0.00,
              `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
              `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
              `due_date` date DEFAULT NULL,
              `status` enum('pending','partial','paid','overdue','waived') NOT NULL DEFAULT 'pending',
              `payment_mode` enum('upi','cash','bank_transfer','card','cheque') DEFAULT NULL,
              `receipt_number` varchar(50) DEFAULT NULL,
              `paid_at` datetime DEFAULT NULL,
              `recorded_by` bigint(20) UNSIGNED DEFAULT NULL,
              `notes` text DEFAULT NULL,
              `created_at` datetime DEFAULT current_timestamp(),
              `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`id`),
              KEY `idx_sf_student` (`student_user_id`),
              KEY `idx_sf_school` (`school_id`),
              KEY `idx_sf_batch` (`batch_id`),
              KEY `idx_sf_month` (`fee_month`),
              KEY `idx_sf_status` (`status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        self::$bootstrapped = true;
    }

    public static function recordFee(array $d, int $recordedBy): int
    {
        self::ensureTable();
        $db = Database::connect();

        $amountDue = (float)($d['amount_due'] ?? 0.0);
        $amountPaid = (float)($d['amount_paid'] ?? 0.0);
        $discount = (float)($d['discount'] ?? 0.0);

        $netDue = max(0.0, $amountDue - $discount);
        $status = !empty($d['status']) ? (string)$d['status'] : 'pending';
        if ($netDue <= 0.0 && ($amountDue > 0 || $discount > 0)) {
            $status = 'paid';
        } elseif ($amountPaid >= $netDue && $netDue > 0) {
            $status = 'paid';
        } elseif ($amountPaid > 0) {
            $status = 'partial';
        } elseif (!empty($d['due_date']) && strtotime($d['due_date']) < time()) {
            $status = 'overdue';
        }

        $receiptNumber = !empty($d['receipt_number'])
            ? trim($d['receipt_number'])
            : ($status === 'paid' ? 'REC-' . strtoupper(substr(uniqid(), -6)) : null);

        $paidAt = ($amountPaid > 0) ? (date('Y-m-d H:i:s')) : null;

        $stmt = $db->prepare("
            INSERT INTO student_fees
            (school_id, batch_id, student_user_id, academic_year, fee_month,
             fee_type, amount_due, amount_paid, discount, due_date, status,
             payment_mode, receipt_number, paid_at, recorded_by, notes)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            !empty($d['school_id']) ? (int)$d['school_id'] : null,
            !empty($d['batch_id']) ? (int)$d['batch_id'] : null,
            (int)$d['student_user_id'],
            $d['academic_year'] ?? '2026-2027',
            $d['fee_month'] ?? date('F'),
            $d['fee_type'] ?? 'tuition',
            $amountDue,
            $amountPaid,
            $discount,
            !empty($d['due_date']) ? $d['due_date'] : null,
            $status,
            $d['payment_mode'] ?? null,
            $receiptNumber,
            $paidAt,
            $recordedBy,
            trim($d['notes'] ?? '')
        ]);

        return (int)$db->lastInsertId();
    }

    public static function updatePayment(int $feeId, float $amountPaid, string $paymentMode, int $recordedBy, string $notes = ''): bool
    {
        self::ensureTable();
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM student_fees WHERE id = ? LIMIT 1");
        $stmt->execute([$feeId]);
        $fee = $stmt->fetch();
        if (!$fee) return false;

        $newPaid = (float)$fee['amount_paid'] + $amountPaid;
        $netDue = max(0.0, (float)$fee['amount_due'] - (float)$fee['discount']);

        $status = 'partial';
        if ($newPaid >= $netDue) {
            $status = 'paid';
        }

        $receipt = $fee['receipt_number'] ?: 'REC-' . strtoupper(substr(uniqid(), -6));

        $update = $db->prepare("
            UPDATE student_fees SET
                amount_paid = ?,
                status = ?,
                payment_mode = ?,
                receipt_number = ?,
                paid_at = NOW(),
                recorded_by = ?,
                notes = CONCAT(IFNULL(notes,''), '\n', ?)
            WHERE id = ?
        ");

        return $update->execute([
            $newPaid,
            $status,
            $paymentMode,
            $receipt,
            $recordedBy,
            trim($notes),
            $feeId
        ]);
    }

    public static function list(array $filters = []): array
    {
        self::ensureTable();
        $db = Database::connect();

        $where = ['1=1'];
        $params = [];

        if (!empty($filters['school_id'])) {
            $where[] = 'sf.school_id = ?';
            $params[] = (int)$filters['school_id'];
        }

        if (!empty($filters['batch_id'])) {
            $where[] = 'sf.batch_id = ?';
            $params[] = (int)$filters['batch_id'];
        }

        if (!empty($filters['student_user_id'])) {
            $where[] = 'sf.student_user_id = ?';
            $params[] = (int)$filters['student_user_id'];
        }

        if (!empty($filters['fee_month'])) {
            $where[] = 'sf.fee_month = ?';
            $params[] = $filters['fee_month'];
        }

        if (!empty($filters['status'])) {
            $where[] = 'sf.status = ?';
            $params[] = $filters['status'];
        }

        $sql = "
            SELECT sf.*, st.full_name AS student_name, st.current_class, u.phone AS student_phone, u.email AS student_email,
                   b.name AS batch_name, s.name AS school_name
            FROM student_fees sf
            JOIN users u ON u.id = sf.student_user_id
            LEFT JOIN students st ON st.user_id = sf.student_user_id
            LEFT JOIN batches b ON b.id = sf.batch_id
            LEFT JOIN schools s ON s.id = sf.school_id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY sf.created_at DESC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function getStudentSummary(int $studentId): array
    {
        self::ensureTable();
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT
                COUNT(*) AS total_records,
                COALESCE(SUM(amount_due), 0) AS total_due,
                COALESCE(SUM(amount_paid), 0) AS total_paid,
                COALESCE(SUM(CASE WHEN status IN ('pending','overdue','partial') THEN (amount_due - discount - amount_paid) ELSE 0 END), 0) AS balance_pending
            FROM student_fees
            WHERE student_user_id = ?
        ");
        $stmt->execute([$studentId]);
        return $stmt->fetch() ?: [
            'total_records' => 0,
            'total_due' => 0,
            'total_paid' => 0,
            'balance_pending' => 0
        ];
    }

    public static function getFee(int $feeId): ?array
    {
        self::ensureTable();
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT sf.*, u.email AS student_email, u.phone AS student_phone,
                   st.full_name AS student_name, st.current_class,
                   b.name AS batch_name
            FROM student_fees sf
            JOIN users u ON u.id = sf.student_user_id
            LEFT JOIN students st ON st.user_id = sf.student_user_id
            LEFT JOIN batches b ON b.id = sf.batch_id
            WHERE sf.id = ? LIMIT 1
        ");
        $stmt->execute([$feeId]);
        $fee = $stmt->fetch();
        if (!$fee) return null;

        $school = !empty($fee['school_id']) ? School::find((int)$fee['school_id']) : null;
        $fee['school_name']         = $school['name'] ?? 'Infinity Learning';
        $fee['school_address']      = $school['address'] ?? (!empty($school['city']) ? $school['city'] . ', ' . ($school['state'] ?? '') : 'Academic Campus · Department of Accounts & Fees');
        $fee['school_email']        = $school['contact_email'] ?? ($school['email'] ?? 'info@infintylearning.net');
        $fee['school_phone']        = $school['contact_phone'] ?? ($school['phone'] ?? '+91 98015 06511');
        $fee['crest_logo']          = $school['crest_logo'] ?? ($school['logo'] ?? '');
        $fee['brand_primary_color'] = $school['brand_primary_color'] ?? '#FF7A18';
        $fee['admission_number']    = $fee['admission_number'] ?? ('IL-' . str_pad((string)$fee['student_user_id'], 5, '0', STR_PAD_LEFT));

        return $fee;
    }
}
