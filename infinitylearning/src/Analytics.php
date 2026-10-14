<?php
declare(strict_types=1);

namespace App;

final class Analytics
{
    // =========================================================
    // PLATFORM-WIDE (super admin)
    // =========================================================

    public static function platformOverview(): array
    {
        $db = Database::connect();
        return [
            'students_total'     => (int)$db->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn(),
            'students_active'    => (int)$db->query("SELECT COUNT(*) FROM users WHERE role='student' AND status='active'")->fetchColumn(),
            'teachers_total'     => (int)$db->query("SELECT COUNT(*) FROM users WHERE role='teacher'")->fetchColumn(),
            'teachers_pending'   => (int)$db->query("SELECT COUNT(*) FROM users WHERE role='teacher' AND status='pending'")->fetchColumn(),
            'schools'            => (int)$db->query("SELECT COUNT(*) FROM schools WHERE status='active'")->fetchColumn(),
            'batches_active'     => (int)$db->query("SELECT COUNT(*) FROM batches WHERE status='active'")->fetchColumn(),
            'lessons_published'  => (int)$db->query("SELECT COUNT(*) FROM lessons WHERE published=1")->fetchColumn(),
            'activities_total'   => (int)$db->query("SELECT COUNT(*) FROM activities WHERE status='published'")->fetchColumn(),
            'registrations'      => (int)$db->query("SELECT COUNT(*) FROM activity_registrations")->fetchColumn(),
            'attendance_records' => (int)$db->query("SELECT COUNT(*) FROM attendance")->fetchColumn(),
        ];
    }

    /**
     * Content breakdown: how much of each type is global vs. school-scoped.
     *
     * @return array{
     *   activities: array{global:int,scoped:int,total:int},
     *   lessons:    array{global:int,scoped:int,total:int},
     *   announcements: array{global:int,scoped:int,total:int}
     * }
     */
    public static function scopeBreakdown(): array
    {
        $db = Database::connect();

        /**
         * Count rows in $table.
         * $isGlobal === true  → count rows WHERE school_id IS NULL
         * $isGlobal === false → count rows WHERE school_id IS NOT NULL
         * $isGlobal === null  → count all rows
         */
        $count = function (string $table, ?bool $isGlobal) use ($db): int {
            $sql = "SELECT COUNT(*) FROM $table";
            if ($isGlobal === true)  $sql .= " WHERE school_id IS NULL";
            if ($isGlobal === false) $sql .= " WHERE school_id IS NOT NULL";
            return (int)$db->query($sql)->fetchColumn();
        };

        return [
            'activities' => [
                'global' => $count('activities', true),
                'scoped' => $count('activities', false),
                'total'  => $count('activities', null),
            ],
            'lessons' => [
                'global' => $count('lessons', true),
                'scoped' => $count('lessons', false),
                'total'  => $count('lessons', null),
            ],
            'announcements' => [
                'global' => $count('announcements', true),
                'scoped' => $count('announcements', false),
                'total'  => $count('announcements', null),
            ],
        ];
    }

    public static function studentSignupsByMonth(int $months = 6): array
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS cnt
            FROM users
            WHERE role='student' AND created_at >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            GROUP BY ym
            ORDER BY ym
        ");
        $stmt->execute([$months]);
        return $stmt->fetchAll();
    }

    public static function platformAttendancePct(): int
    {
        $db = Database::connect();
        $row = $db->query("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status='present' THEN 1 ELSE 0 END) AS present
            FROM attendance
            WHERE attendance_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
        ")->fetch();
        if (!$row || !$row['total']) return 0;
        return (int)round($row['present'] / $row['total'] * 100);
    }

    public static function topParticipants(int $limit = 10): array
    {
        $stmt = Database::connect()->prepare("
            SELECT u.id, s.full_name, s.profile_image, u.email,
                   COUNT(ar.id) AS participation_count
            FROM activity_registrations ar
            JOIN users u ON u.id = ar.user_id
            LEFT JOIN students s ON s.user_id = u.id
            GROUP BY u.id
            ORDER BY participation_count DESC, s.full_name
            LIMIT ?
        ");
        $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function participationByCategory(?int $schoolId = null): array
    {
        $db = Database::connect();
        $sql = "
            SELECT a.category, COUNT(ar.id) AS cnt
            FROM activity_registrations ar
            JOIN activities a ON a.id = ar.activity_id
            JOIN users u      ON u.id = ar.user_id
            WHERE 1=1
        ";
        $params = [];
        if (!empty($schoolId)) {
            $sql .= " AND u.school_id = ?";
            $params[] = (int)$schoolId;
        }
        $sql .= " GROUP BY a.category ORDER BY cnt DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function topBatches(int $limit = 8, ?int $schoolId = null): array
    {
        $db = Database::connect();
        $sql = "
            SELECT b.id, b.name, b.subject,
                   (SELECT COUNT(*) FROM batch_students bs WHERE bs.batch_id = b.id) AS student_count
            FROM batches b
            WHERE b.status = 'active'
        ";
        $params = [];
        if (!empty($schoolId)) {
            $sql .= " AND b.school_id = ?";
            $params[] = (int)$schoolId;
        }
        $sql .= " ORDER BY student_count DESC LIMIT ?";
        $params[] = $limit;

        $stmt = $db->prepare($sql);
        foreach ($params as $i => $p) {
            $stmt->bindValue($i + 1, $p, is_int($p) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // =========================================================
    // PER-SCHOOL AGGREGATES
    // =========================================================

    public static function schoolAttendanceByMonth(int $schoolId, int $months = 6): array
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT DATE_FORMAT(a.attendance_date, '%Y-%m') AS ym,
                   COUNT(*) AS total,
                   SUM(CASE WHEN a.status='present' THEN 1 ELSE 0 END) AS present
            FROM attendance a
            JOIN users u ON u.id = a.student_user_id
            WHERE u.school_id = ?
              AND a.attendance_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            GROUP BY ym
            ORDER BY ym
        ");
        $stmt->bindValue(1, $schoolId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $months, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) {
            $r['pct'] = $r['total'] > 0 ? (int)round($r['present'] / $r['total'] * 100) : 0;
        }
        return $rows;
    }

    public static function schoolParticipationByCategory(int $schoolId): array
    {
        return self::participationByCategory($schoolId);
    }

    public static function schoolUserSplit(int $schoolId): array
    {
        $db = Database::connect();
        $sid = (int)$schoolId;
        return [
            'students_active'  => (int)$db->query("SELECT COUNT(*) FROM users WHERE role='student' AND status='active'  AND school_id=$sid")->fetchColumn(),
            'students_pending' => (int)$db->query("SELECT COUNT(*) FROM users WHERE role='student' AND status='pending' AND school_id=$sid")->fetchColumn(),
            'teachers_active'  => (int)$db->query("SELECT COUNT(*) FROM users WHERE role='teacher' AND status='active'  AND school_id=$sid")->fetchColumn(),
            'teachers_pending' => (int)$db->query("SELECT COUNT(*) FROM users WHERE role='teacher' AND status='pending' AND school_id=$sid")->fetchColumn(),
        ];
    }

    // =========================================================
    // BATCH-SPECIFIC (teacher dashboard)
    // =========================================================

    public static function batchAttendanceSummary(int $batchId, int $days = 30): array
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT status, COUNT(*) AS cnt
            FROM attendance
            WHERE batch_id = ? AND attendance_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            GROUP BY status
        ");
        $stmt->bindValue(1, $batchId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $days, \PDO::PARAM_INT);
        $stmt->execute();
        $out = ['present'=>0,'absent'=>0,'late'=>0,'excused'=>0];
        foreach ($stmt->fetchAll() as $r) $out[$r['status']] = (int)$r['cnt'];
        $out['total'] = array_sum($out);
        $out['present_pct'] = $out['total'] > 0
            ? (int)round($out['present'] / $out['total'] * 100)
            : 0;
        return $out;
    }

    public static function batchStudentPerformance(int $batchId, int $days = 30): array
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT u.id AS user_id, s.full_name, s.profile_image, u.email,
                   COUNT(a.id) AS total_sessions,
                   SUM(CASE WHEN a.status='present' THEN 1 ELSE 0 END) AS present_count,
                   SUM(CASE WHEN a.status='absent'  THEN 1 ELSE 0 END) AS absent_count,
                   SUM(CASE WHEN a.status='late'    THEN 1 ELSE 0 END) AS late_count
            FROM batch_students bs
            JOIN users u ON u.id = bs.student_user_id
            LEFT JOIN students s ON s.user_id = u.id
            LEFT JOIN attendance a
                ON a.student_user_id = u.id
                AND a.batch_id = bs.batch_id
                AND a.attendance_date >= DATE_SUB(CURDATE(), INTERVAL ? DAY)
            WHERE bs.batch_id = ?
            GROUP BY u.id
            ORDER BY total_sessions DESC, s.full_name
        ");
        $stmt->bindValue(1, $days, \PDO::PARAM_INT);
        $stmt->bindValue(2, $batchId, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) {
            $total = (int)$r['total_sessions'];
            $r['present_pct'] = $total > 0 ? (int)round($r['present_count'] / $total * 100) : 0;
        }
        return $rows;
    }

    public static function batchAttendanceByMonth(int $batchId, int $months = 6): array
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT DATE_FORMAT(attendance_date, '%Y-%m') AS ym,
                   COUNT(*) AS total,
                   SUM(CASE WHEN status='present' THEN 1 ELSE 0 END) AS present
            FROM attendance
            WHERE batch_id = ? AND attendance_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            GROUP BY ym
            ORDER BY ym
        ");
        $stmt->bindValue(1, $batchId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $months, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) {
            $r['pct'] = $r['total'] > 0 ? (int)round($r['present'] / $r['total'] * 100) : 0;
        }
        return $rows;
    }

    // =========================================================
    // STUDENT-SPECIFIC
    // =========================================================

    public static function studentAttendanceByMonth(int $userId, int $months = 6): array
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT DATE_FORMAT(attendance_date, '%Y-%m') AS ym,
                   COUNT(*) AS total,
                   SUM(CASE WHEN status='present' THEN 1 ELSE 0 END) AS present
            FROM attendance
            WHERE student_user_id = ? AND attendance_date >= DATE_SUB(CURDATE(), INTERVAL ? MONTH)
            GROUP BY ym
            ORDER BY ym
        ");
        $stmt->bindValue(1, $userId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $months, \PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) {
            $r['pct'] = $r['total'] > 0 ? (int)round($r['present'] / $r['total'] * 100) : 0;
        }
        return $rows;
    }

    public static function studentParticipationByCategory(int $userId): array
    {
        $stmt = Database::connect()->prepare("
            SELECT a.category, COUNT(ar.id) AS cnt
            FROM activity_registrations ar
            JOIN activities a ON a.id = ar.activity_id
            WHERE ar.user_id = ?
            GROUP BY a.category
            ORDER BY cnt DESC
        ");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public static function studentAchievementSummary(int $userId): array
    {
        $db = Database::connect();
        return [
            'achievements' => (int)$db->query("SELECT COUNT(*) FROM achievements WHERE student_user_id = " . (int)$userId)->fetchColumn(),
            'certificates' => (int)$db->query("SELECT COUNT(*) FROM certificates WHERE student_user_id = " . (int)$userId)->fetchColumn(),
        ];
    }
}