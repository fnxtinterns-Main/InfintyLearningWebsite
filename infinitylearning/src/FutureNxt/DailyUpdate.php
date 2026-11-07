<?php
declare(strict_types=1);

namespace App\FutureNxt;

use PDO;

final class DailyUpdate
{
    /**
     * Submit or update a daily work update.
     */
    public static function submit(
        int $employeeId,
        string $date,
        string $workDone,
        ?string $tasksSummary = null,
        ?string $blockers = null,
        ?string $planTomorrow = null,
        ?float $hoursSpent = null,
        array $taskIds = []
    ): array {
        try {
            $db = Database::connect();

            $date = trim($date);
            if ($date === '') {
                $date = date('Y-m-d');
            }

            $workDone = trim($workDone);
            if ($workDone === '') {
                return ['success' => false, 'error' => 'Please provide details of the work accomplished today.'];
            }

            $tasksSummary = trim((string)$tasksSummary) ?: null;
            $blockers     = trim((string)$blockers) ?: null;
            $planTomorrow = trim((string)$planTomorrow) ?: null;
            if ($hoursSpent !== null && $hoursSpent <= 0) {
                $hoursSpent = null;
            }

            $stmt = $db->prepare("
                INSERT INTO fnx_daily_updates (
                    employee_id, update_date, tasks_summary, work_done, blockers, plan_tomorrow, hours_spent, created_at, updated_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, NOW(), NOW()
                )
                ON DUPLICATE KEY UPDATE 
                    tasks_summary = VALUES(tasks_summary),
                    work_done     = VALUES(work_done),
                    blockers      = VALUES(blockers),
                    plan_tomorrow = VALUES(plan_tomorrow),
                    hours_spent   = VALUES(hours_spent),
                    updated_at    = NOW()
            ");

            $stmt->execute([
                $employeeId,
                $date,
                $tasksSummary,
                $workDone,
                $blockers,
                $planTomorrow,
                $hoursSpent
            ]);

            $id = (int)$db->lastInsertId();
            if (!$id) {
                // If it was an update, fetch existing id
                $chk = $db->prepare("SELECT id FROM fnx_daily_updates WHERE employee_id = ? AND update_date = ? LIMIT 1");
                $chk->execute([$employeeId, $date]);
                $id = (int)$chk->fetchColumn();
            }

            // Sync tasks to fnx_daily_update_tasks
            if ($id > 0) {
                $cleanTaskIds = array_filter(array_map('intval', $taskIds), fn($v) => $v > 0);

                // Auto-detect any TSK-xxxx codes mentioned in tasksSummary or workDone
                $haystack = ($tasksSummary ?? '') . ' ' . $workDone;
                if (preg_match_all('/TSK-\d{4}/i', $haystack, $matches)) {
                    $foundCodes = array_unique(array_map('strtoupper', $matches[0]));
                    if (!empty($foundCodes)) {
                        $ph = implode(',', array_fill(0, count($foundCodes), '?'));
                        $cStmt = $db->prepare("SELECT id FROM fnx_tasks WHERE UPPER(task_code) IN ($ph)");
                        $cStmt->execute($foundCodes);
                        $matchedIds = $cStmt->fetchAll(PDO::FETCH_COLUMN);
                        foreach ($matchedIds as $mId) {
                            $cleanTaskIds[] = (int)$mId;
                        }
                    }
                }

                $cleanTaskIds = array_unique($cleanTaskIds);

                // Delete old associations for this update
                $delRel = $db->prepare("DELETE FROM fnx_daily_update_tasks WHERE daily_update_id = ?");
                $delRel->execute([$id]);

                // Insert new associations
                if (!empty($cleanTaskIds)) {
                    $insRel = $db->prepare("INSERT IGNORE INTO fnx_daily_update_tasks (daily_update_id, task_id) VALUES (?, ?)");
                    foreach ($cleanTaskIds as $tId) {
                        $insRel->execute([$id, $tId]);
                    }
                }
            }

            Audit::log('daily_update', 'daily_update', $id, "Submitted daily update for {$date}", $employeeId);

            return ['success' => true, 'id' => $id];
        } catch (\Throwable $e) {
            return ['success' => false, 'error' => 'Database error saving daily update: ' . $e->getMessage()];
        }
    }

    /**
     * Get all daily updates linked to a specific task.
     * Matches both junction table records and direct task_code mentions.
     */
    public static function getByTask(int $taskId, ?string $taskCode = null): array
    {
        try {
            $db = Database::connect();

            $sql = "
                SELECT DISTINCT
                       u.*,
                       e.full_name AS employee_name,
                       e.emp_code,
                       e.role AS employee_role,
                       e.designation,
                       e.photo_path
                FROM fnx_daily_updates u
                JOIN fnx_employees e ON e.id = u.employee_id
                LEFT JOIN fnx_daily_update_tasks ut ON ut.daily_update_id = u.id AND ut.task_id = ?
                WHERE (ut.task_id IS NOT NULL
            ";
            $params = [$taskId];

            if (!empty($taskCode)) {
                $sql .= " OR u.tasks_summary LIKE ? OR u.work_done LIKE ?";
                $params[] = '%' . $taskCode . '%';
                $params[] = '%' . $taskCode . '%';
            }

            $sql .= ") ORDER BY u.update_date DESC, u.created_at DESC";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            foreach ($rows as &$r) {
                $r['formatted_date'] = !empty($r['update_date']) ? date('d M Y', strtotime($r['update_date'])) : '';
                $r['formatted_ist_time'] = !empty($r['updated_at']) ? Audit::formatIst($r['updated_at'], 'h:i A') : (!empty($r['created_at']) ? Audit::formatIst($r['created_at'], 'h:i A') : '');
            }

            return $rows;
        } catch (\Throwable $e) {
            error_log("[DailyUpdate::getByTask] " . $e->getMessage());
            return [];
        }
    }

    /**
     * Get employee's update for a specific date (defaults to today).
     */
    public static function getToday(int $employeeId, ?string $date = null): ?array
    {
        $db = Database::connect();
        $date = $date ?: date('Y-m-d');
        $stmt = $db->prepare("
            SELECT * FROM fnx_daily_updates 
            WHERE employee_id = ? AND update_date = ? 
            LIMIT 1
        ");
        $stmt->execute([$employeeId, $date]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /**
     * List daily updates with filters.
     */
    public static function list(array $filters = []): array
    {
        $db = Database::connect();

        $sql = "
            SELECT u.*,
                e.full_name AS employee_name,
                e.emp_code,
                e.role AS employee_role,
                e.designation,
                e.photo_path,
                e.department,
                mgr.full_name AS manager_name,
                mgr.emp_code AS manager_code
            FROM fnx_daily_updates u
            JOIN fnx_employees e ON e.id = u.employee_id
            LEFT JOIN fnx_employees mgr ON mgr.id = e.manager_id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($filters['date'])) {
            $sql .= " AND u.update_date = ?";
            $params[] = $filters['date'];
        }

        if (!empty($filters['from_date']) && !empty($filters['to_date'])) {
            $sql .= " AND u.update_date BETWEEN ? AND ?";
            $params[] = $filters['from_date'];
            $params[] = $filters['to_date'];
        }

        if (!empty($filters['employee_id'])) {
            $sql .= " AND u.employee_id = ?";
            $params[] = (int)$filters['employee_id'];
        }

        if (!empty($filters['manager_id'])) {
            $sql .= " AND e.manager_id = ?";
            $params[] = (int)$filters['manager_id'];
        }

        if (!empty($filters['department'])) {
            $sql .= " AND e.department = ?";
            $params[] = $filters['department'];
        }

        if (!empty($filters['q'])) {
            $q = '%' . trim($filters['q']) . '%';
            $sql .= " AND (e.full_name LIKE ? OR e.emp_code LIKE ? OR u.work_done LIKE ? OR u.tasks_summary LIKE ?)";
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }

        $sql .= " ORDER BY u.update_date DESC, u.created_at DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Get statistics on daily update submissions for a given date (CEO and Admin are excluded from mandatory requirement).
     */
    public static function getStats(?string $date = null, ?int $managerId = null): array
    {
        $db = Database::connect();
        $date = $date ?: date('Y-m-d');

        $empSql = "SELECT COUNT(*) FROM fnx_employees WHERE status = 'active' AND role NOT IN ('ceo', 'admin')";
        $empParams = [];
        if ($managerId !== null) {
            $empSql .= " AND manager_id = ?";
            $empParams[] = $managerId;
        }
        $empStmt = $db->prepare($empSql);
        $empStmt->execute($empParams);
        $totalActive = (int)$empStmt->fetchColumn();

        $subSql = "
            SELECT COUNT(DISTINCT u.employee_id) 
            FROM fnx_daily_updates u 
            JOIN fnx_employees e ON e.id = u.employee_id
            WHERE u.update_date = ? AND e.status = 'active' AND e.role NOT IN ('ceo', 'admin')
        ";
        $subParams = [$date];
        if ($managerId !== null) {
            $subSql .= " AND e.manager_id = ?";
            $subParams[] = $managerId;
        }
        $subStmt = $db->prepare($subSql);
        $subStmt->execute($subParams);
        $submitted = (int)$subStmt->fetchColumn();

        $pending = max(0, $totalActive - $submitted);
        $percentage = $totalActive > 0 ? (int)round(($submitted / $totalActive) * 100) : 0;

        return [
            'date'         => $date,
            'total_active' => $totalActive,
            'submitted'    => $submitted,
            'pending'      => $pending,
            'percentage'   => $percentage
        ];
    }

    /**
     * Get list of active employees who haven't submitted their daily update for a given date (excludes CEO & Admin).
     */
    public static function getPendingEmployees(?string $date = null, ?int $managerId = null): array
    {
        $db = Database::connect();
        $date = $date ?: date('Y-m-d');

        $sql = "
            SELECT e.id, e.emp_code, e.full_name, e.role, e.designation, e.department, e.email, e.photo_path,
                   mgr.full_name AS manager_name, mgr.emp_code AS manager_code
            FROM fnx_employees e
            LEFT JOIN fnx_employees mgr ON mgr.id = e.manager_id
            WHERE e.status = 'active'
              AND e.role NOT IN ('ceo', 'admin')
              AND e.id NOT IN (
                  SELECT employee_id FROM fnx_daily_updates WHERE update_date = ?
              )
        ";
        $params = [$date];

        if ($managerId !== null) {
            $sql .= " AND e.manager_id = ?";
            $params[] = $managerId;
        }

        $sql .= " ORDER BY FIELD(e.role, 'cto', 'admin', 'office_manager', 'manager', 'employee', 'intern'), e.full_name ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }
}
