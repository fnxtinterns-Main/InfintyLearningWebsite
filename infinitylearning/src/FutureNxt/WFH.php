<?php
declare(strict_types=1);

namespace App\FutureNxt;

final class WFH
{
    public static function request(int $employeeId, string $startDate, string $endDate, string $reason): array
    {
        try {
            $db = Database::connect();

            $startDate = trim($startDate);
            $endDate   = trim($endDate);
            $reason    = trim($reason);

            if ($startDate === '' || $endDate === '') {
                return ['success' => false, 'error' => 'Start date and end date are required.'];
            }
            if ($startDate > $endDate) {
                return ['success' => false, 'error' => 'Start date cannot be after end date.'];
            }
            if ($reason === '') {
                return ['success' => false, 'error' => 'Please provide a reason or justification for WFA (Work From Anywhere).'];
            }

            $start = strtotime($startDate);
            $end = strtotime($endDate);
            $totalDays = ($start && $end) ? (int)round(($end - $start) / 86400) + 1 : 1;

            $stmt = $db->prepare("
                INSERT INTO fnx_wfh_requests (employee_id, start_date, end_date, total_days, reason, status, created_at)
                VALUES (?, ?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([$employeeId, $startDate, $endDate, $totalDays, $reason]);
            $wfhId = (int)$db->lastInsertId();

            Audit::log('request_wfa', 'wfh', $wfhId, "Requested WFA from {$startDate} to {$endDate} ({$totalDays} days)", $employeeId);

            return ['success' => true, 'id' => $wfhId];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => 'Database error while requesting WFA: ' . $e->getMessage()];
        }
    }

    public static function list(array $filters = [], ?int $forEmployeeId = null): array
    {
        $db = Database::connect();
        $sql = "
            SELECT w.*, 
                e.full_name AS employee_name, e.emp_code, e.role AS employee_role, e.designation, e.photo_path,
                rev.full_name AS reviewed_by_name
            FROM fnx_wfh_requests w
            JOIN fnx_employees e ON e.id = w.employee_id
            LEFT JOIN fnx_employees rev ON rev.id = w.reviewed_by
            WHERE 1=1
        ";
        $params = [];

        if ($forEmployeeId !== null) {
            $sql .= " AND w.employee_id = ?";
            $params[] = $forEmployeeId;
        }

        if (!empty($filters['status'])) {
            $sql .= " AND w.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['employee_id'])) {
            $sql .= " AND w.employee_id = ?";
            $params[] = (int)$filters['employee_id'];
        }

        $sql .= " ORDER BY w.id DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    public static function getStats(): array
    {
        $db = Database::connect();
        $stmt = $db->query("
            SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) AS rejected
            FROM fnx_wfh_requests
        ");
        return $stmt->fetch() ?: ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
    }

    public static function review(int $wfhId, string $status, int $reviewerId, ?string $remarks = null): bool
    {
        $db = Database::connect();
        if (!in_array($status, ['approved', 'rejected'], true)) {
            return false;
        }

        $stmt = $db->prepare("
            UPDATE fnx_wfh_requests 
            SET status = ?, reviewed_by = ?, reviewed_at = NOW(), reviewer_remarks = ?
            WHERE id = ?
        ");
        $stmt->execute([$status, $reviewerId, trim((string)$remarks) ?: null, $wfhId]);

        Audit::log($status . '_wfa', 'wfh', $wfhId, "{$status} WFA request #{$wfhId}", $reviewerId);
        return true;
    }
}
