<?php
declare(strict_types=1);

namespace App\FutureNxt;

use PDO;

final class Leave
{
    /**
     * Check if approving this leave will conflict with active tasks assigned to the employee.
     */
    public static function getTaskConflicts(int $employeeId, string $startDate, string $endDate): array
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT t.id, t.task_code, t.title, t.due_date, t.priority, t.status
            FROM fnx_tasks t
            JOIN fnx_task_assignees ta ON ta.task_id = t.id
            WHERE ta.employee_id = ?
              AND ta.is_active = 1
              AND t.status IN ('pending', 'in_progress', 'submitted')
              AND t.due_date BETWEEN ? AND ?
            ORDER BY t.due_date ASC
        ");
        $stmt->execute([$employeeId, $startDate, $endDate]);
        return $stmt->fetchAll() ?: [];
    }

    public static function request(int $employeeId, string $type, string $startDate, string $endDate, string $reason): array
    {
        try {
            $db = Database::connect();
            $start = strtotime($startDate);
            $end = strtotime($endDate);

            if (!$start || !$end || $end < $start) {
                return ['success' => false, 'error' => 'Invalid leave date range. End date cannot be before start date.'];
            }

            // Calculate days inclusive
            $totalDays = (int)round(($end - $start) / 86400) + 1;

            $stmt = $db->prepare("
                INSERT INTO fnx_leaves (employee_id, leave_type, start_date, end_date, total_days, reason, status, created_at)
                VALUES (?, ?, ?, ?, ?, ?, 'pending', NOW())
            ");
            $stmt->execute([$employeeId, $type, $startDate, $endDate, $totalDays, trim($reason)]);
            $leaveId = (int)$db->lastInsertId();

            Audit::log('apply_leave', 'leave', $leaveId, "Applied for {$type} leave from {$startDate} to {$endDate} ({$totalDays} days)", $employeeId);

            // Check conflicts
            $conflicts = self::getTaskConflicts($employeeId, $startDate, $endDate);

            return [
                'success' => true,
                'id' => $leaveId,
                'conflicts' => $conflicts,
                'has_conflicts' => !empty($conflicts)
            ];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => 'Database error: ' . $e->getMessage()];
        }
    }

    public static function list(array $filters = [], ?int $forEmployeeId = null): array
    {
        $db = Database::connect();
        $sql = "
            SELECT l.*,
                e.full_name AS employee_name,
                e.emp_code,
                e.role AS employee_role,
                reviewer.full_name AS reviewed_by_name
            FROM fnx_leaves l
            JOIN fnx_employees e ON e.id = l.employee_id
            LEFT JOIN fnx_employees reviewer ON reviewer.id = l.reviewed_by
            WHERE 1=1
        ";
        $params = [];

        if ($forEmployeeId !== null) {
            $sql .= " AND l.employee_id = ?";
            $params[] = $forEmployeeId;
        }

        if (!empty($filters['status'])) {
            $sql .= " AND l.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['employee_id'])) {
            $sql .= " AND l.employee_id = ?";
            $params[] = (int)$filters['employee_id'];
        }

        $sql .= " ORDER BY l.id DESC";

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
            FROM fnx_leaves
        ");
        return $stmt->fetch() ?: ['total' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
    }

    public static function review(int $leaveId, string $status, int $reviewerId, ?string $remarks = null): bool
    {
        $db = Database::connect();
        if (!in_array($status, ['approved', 'rejected'], true)) {
            return false;
        }

        $stmt = $db->prepare("
            UPDATE fnx_leaves 
            SET status = ?, reviewed_by = ?, reviewed_at = NOW(), reviewer_remarks = ?
            WHERE id = ?
        ");
        $stmt->execute([$status, $reviewerId, trim((string)$remarks) ?: null, $leaveId]);

        Audit::log($status . '_leave', 'leave', $leaveId, "{$status} leave request #{$leaveId}", $reviewerId);

        // Send email notification to employee
        try {
            EmailNotifier::sendLeaveDecision($leaveId);
        } catch (\Throwable $e) {
            error_log('[Leave::review] Email notification failed: ' . $e->getMessage());
        }

        return true;
    }
}
