<?php
declare(strict_types=1);

namespace App\FutureNxt;

final class Task
{
    public const MAX_ACTIVE_TASKS_PER_EMPLOYEE = 3;

    public static function getActiveTaskCount(int $employeeId): int
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT COUNT(DISTINCT ta.task_id) 
            FROM fnx_task_assignees ta 
            JOIN fnx_tasks t ON t.id = ta.task_id 
            WHERE ta.employee_id = ? 
              AND ta.is_active = 1 
              AND t.status IN ('pending', 'in_progress', 'submitted')
        ");
        $stmt->execute([$employeeId]);
        return (int)$stmt->fetchColumn();
    }

    public static function generateTaskCode(): string
    {
        $db = Database::connect();
        $row = $db->query("SELECT id FROM fnx_tasks ORDER BY id DESC LIMIT 1")->fetch();
        $next = ($row ? (int)$row['id'] : 0) + 1;
        return 'TSK-' . str_pad((string)$next, 4, '0', STR_PAD_LEFT);
    }

    public static function create(array $data, array $assigneeIds, int $createdBy): array
    {
        $db = Database::connect();

        $title = trim((string)($data['title'] ?? ''));
        if ($title === '') {
            return ['success' => false, 'error' => 'Task title is required.'];
        }

        $dueDate = trim((string)($data['due_date'] ?? ''));
        if ($dueDate === '') {
            return ['success' => false, 'error' => 'Due date is required.'];
        }

        $assigneeIds = array_values(array_unique(array_filter(array_map('intval', $assigneeIds))));
        if (empty($assigneeIds)) {
            return ['success' => false, 'error' => 'Please assign the task to at least one employee.'];
        }

        // VALIDATION: Check each assignee against MAX 3 active tasks limit!
        foreach ($assigneeIds as $empId) {
            $activeCount = self::getActiveTaskCount($empId);
            if ($activeCount >= self::MAX_ACTIVE_TASKS_PER_EMPLOYEE) {
                $emp = Employee::find($empId);
                $name = $emp ? "{$emp['full_name']} ({$emp['emp_code']})" : "Employee #{$empId}";
                return [
                    'success' => false, 
                    'error' => "Cannot assign task: {$name} already has {$activeCount} active tasks (Maximum allowed limit is " . self::MAX_ACTIVE_TASKS_PER_EMPLOYEE . "). Please complete, reassign, or close an existing task first."
                ];
            }
        }

        $taskCode = self::generateTaskCode();
        $priority = in_array(($data['priority'] ?? ''), ['low', 'medium', 'high', 'urgent'], true) ? $data['priority'] : 'medium';
        $dueTime = !empty($data['due_time']) ? trim((string)$data['due_time']) : '18:00:00';

        $stmt = $db->prepare("
            INSERT INTO fnx_tasks (task_code, title, description, priority, due_date, due_time, status, created_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, 'in_progress', ?, NOW())
        ");
        $stmt->execute([
            $taskCode,
            $title,
            trim((string)($data['description'] ?? '')) ?: null,
            $priority,
            $dueDate,
            $dueTime,
            $createdBy
        ]);

        $taskId = (int)$db->lastInsertId();

        // Add assignees
        $ins = $db->prepare("
            INSERT INTO fnx_task_assignees (task_id, employee_id, assigned_by, assigned_at, is_active)
            VALUES (?, ?, ?, NOW(), 1)
        ");
        foreach ($assigneeIds as $empId) {
            $ins->execute([$taskId, $empId, $createdBy]);
        }

        Audit::log('create', 'task', $taskId, "Created task {$taskCode}: {$title} assigned to " . count($assigneeIds) . " partners");

        // Send email notifications to all assigned partners
        try {
            EmailNotifier::sendTaskAssigned($taskId, $assigneeIds, $createdBy);
        } catch (\Throwable $e) {
            error_log("[Task::create] Could not dispatch assignment emails: " . $e->getMessage());
        }

        return ['success' => true, 'id' => $taskId, 'task_code' => $taskCode];
    }

    public static function list(array $filters = [], ?int $forEmployeeId = null): array
    {
        $db = Database::connect();

        if ($forEmployeeId === null && !empty($filters['employee_id'])) {
            $forEmployeeId = (int)$filters['employee_id'];
        }

        $sql = "
            SELECT t.*,
                creator.full_name AS created_by_name,
                creator.emp_code AS created_by_code,
                approver.full_name AS approved_by_name,
                approver.emp_code AS approved_by_code,
                approver.role AS approved_by_role,
                approver.designation AS approved_by_designation,
                GROUP_CONCAT(DISTINCT CONCAT(e.id, ':', e.full_name, ':', e.emp_code, ':', ta.is_active) SEPARATOR '||') AS assignees_raw
            FROM fnx_tasks t
            LEFT JOIN fnx_task_assignees ta ON ta.task_id = t.id
            LEFT JOIN fnx_employees e ON e.id = ta.employee_id
            LEFT JOIN fnx_employees creator ON creator.id = t.created_by
            LEFT JOIN fnx_employees approver ON approver.id = t.approved_by
            WHERE 1=1
        ";
        $params = [];

        if ($forEmployeeId !== null) {
            $sql .= " AND ta.employee_id = ? AND ta.is_active = 1";
            $params[] = $forEmployeeId;
        }

        if (!empty($filters['status'])) {
            $sql .= " AND t.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['exclude_status'])) {
            if (is_array($filters['exclude_status'])) {
                $placeholders = implode(',', array_fill(0, count($filters['exclude_status']), '?'));
                $sql .= " AND t.status NOT IN ($placeholders)";
                foreach ($filters['exclude_status'] as $st) {
                    $params[] = $st;
                }
            } else {
                $sql .= " AND t.status != ?";
                $params[] = $filters['exclude_status'];
            }
        }
        if (!empty($filters['priority'])) {
            $sql .= " AND t.priority = ?";
            $params[] = $filters['priority'];
        }
        $searchKey = !empty($filters['search']) ? $filters['search'] : (!empty($filters['q']) ? $filters['q'] : '');
        if (!empty($searchKey)) {
            $q = '%' . trim((string)$searchKey) . '%';
            $sql .= " AND (t.title LIKE ? OR t.task_code LIKE ? OR t.description LIKE ?)";
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }

        $sql .= " GROUP BY t.id ORDER BY FIELD(t.priority, 'urgent', 'high', 'medium', 'low'), t.due_date ASC, t.id DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll() ?: [];

        // Parse assignees list
        foreach ($rows as &$r) {
            $r['assignees'] = [];
            if (!empty($r['assignees_raw'])) {
                $parts = explode('||', (string)$r['assignees_raw']);
                foreach ($parts as $p) {
                    $tokens = explode(':', $p);
                    if (count($tokens) >= 4) {
                        $r['assignees'][] = [
                            'id' => (int)$tokens[0],
                            'full_name' => $tokens[1],
                            'emp_code' => $tokens[2],
                            'is_active' => (bool)(int)$tokens[3]
                        ];
                    }
                }
            }
            $r['documentation_pdf'] = !empty($r['doc_path']) ? $r['doc_path'] : ($r['documentation_pdf'] ?? null);
        }
        return $rows;
    }

    public static function listForEmployee(int $employeeId, array $filters = []): array
    {
        return self::list($filters, $employeeId);
    }

    public static function getStats(): array
    {
        $db = Database::connect();
        $stmt = $db->query("
            SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN status IN ('pending', 'in_progress') THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'in_progress' THEN 1 ELSE 0 END) AS in_progress,
                SUM(CASE WHEN status = 'submitted' THEN 1 ELSE 0 END) AS submitted,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) AS approved,
                SUM(CASE WHEN status = 'reopened' THEN 1 ELSE 0 END) AS reopened
            FROM fnx_tasks
        ");
        return $stmt->fetch() ?: [
            'total' => 0, 'active' => 0, 'pending' => 0, 'in_progress' => 0, 'submitted' => 0, 'approved' => 0, 'reopened' => 0
        ];
    }

    public static function find(int $taskId): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT t.*,
                creator.full_name AS created_by_name,
                creator.emp_code AS created_by_code,
                approver.full_name AS approved_by_name,
                approver.emp_code AS approved_by_code,
                approver.role AS approved_by_role,
                approver.designation AS approved_by_designation
            FROM fnx_tasks t
            LEFT JOIN fnx_employees creator ON creator.id = t.created_by
            LEFT JOIN fnx_employees approver ON approver.id = t.approved_by
            WHERE t.id = ? 
            LIMIT 1
        ");
        $stmt->execute([$taskId]);
        $task = $stmt->fetch();
        if (!$task) return null;

        $aStmt = $db->prepare("
            SELECT ta.*, e.full_name, e.emp_code, e.role, e.email, e.photo_path
            FROM fnx_task_assignees ta
            JOIN fnx_employees e ON e.id = ta.employee_id
            WHERE ta.task_id = ?
            ORDER BY ta.is_active DESC, ta.id ASC
        ");
        $aStmt->execute([$taskId]);
        $task['assignees'] = $aStmt->fetchAll() ?: [];
        $task['documentation_pdf'] = !empty($task['doc_path']) ? $task['doc_path'] : ($task['documentation_pdf'] ?? null);

        return $task;
    }

    public static function getAssignees(int $taskId): array
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT ta.*, e.full_name, e.emp_code, e.role, e.email, e.photo_path
            FROM fnx_task_assignees ta
            JOIN fnx_employees e ON e.id = ta.employee_id
            WHERE ta.task_id = ? AND ta.is_active = 1
            ORDER BY ta.id ASC
        ");
        $stmt->execute([$taskId]);
        return $stmt->fetchAll() ?: [];
    }

    /**
     * Submit task documentation in PDF format
     */
    public static function submitDocumentation(int $taskId, int $employeeId, array $file, ?string $notes = null): array
    {
        $db = Database::connect();
        $task = self::find($taskId);
        if (!$task) {
            return ['success' => false, 'error' => 'Task not found.'];
        }

        // Verify that employee is an active assignee, manager, or executive
        $isAssignee = false;
        foreach ($task['assignees'] as $a) {
            if ($a['employee_id'] == $employeeId && $a['is_active']) {
                $isAssignee = true;
                break;
            }
        }
        if (!$isAssignee && !Auth::isManagerOrAbove()) {
            return ['success' => false, 'error' => 'You are not assigned to this task.'];
        }

        // Validate that uploaded file is PDF with detailed error reporting
        if (empty($file) || !isset($file['error'])) {
            return ['success' => false, 'error' => 'Please select a PDF file to upload.'];
        }
        if ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
            return ['success' => false, 'error' => 'The uploaded file exceeds the maximum allowed upload limit (50MB). Please compress or reduce the PDF size.'];
        }
        if ($file['error'] === UPLOAD_ERR_PARTIAL) {
            return ['success' => false, 'error' => 'The file was only partially uploaded. Please try again.'];
        }
        if ($file['error'] === UPLOAD_ERR_NO_FILE || empty($file['tmp_name'])) {
            return ['success' => false, 'error' => 'Please select a PDF file before submitting.'];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'File upload error (Code #' . $file['error'] . '). Please try again.'];
        }

        $origName = (string)($file['name'] ?? '');
        $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
        if ($ext !== 'pdf') {
            return ['success' => false, 'error' => 'Documentation must be uploaded in PDF format (.pdf) only. Received: .' . $ext];
        }

        $baseDir = (defined('UPLOAD_PATH') ? UPLOAD_PATH : dirname(__DIR__, 3) . '/public_html/uploads') . '/futurenxt/tasks';
        if (!is_dir($baseDir)) {
            @mkdir($baseDir, 0777, true);
        }

        $filename = 'doc_' . preg_replace('/[^A-Za-z0-9_-]/', '_', (string)$task['task_code']) . '_' . time() . '.pdf';
        $dest = $baseDir . '/' . $filename;

        // Try move_uploaded_file, fallback to copy for S3 FUSE mounts
        $saved = false;
        if (@move_uploaded_file($file['tmp_name'], $dest)) {
            $saved = true;
        } elseif (@copy($file['tmp_name'], $dest)) {
            @unlink($file['tmp_name']);
            $saved = true;
        }

        if (!$saved) {
            return ['success' => false, 'error' => 'Failed to write documentation PDF to storage directory. Please check disk permissions.'];
        }

        @chmod($dest, 0666);
        $docPath = '/uploads/futurenxt/tasks/' . $filename;

        $stmt = $db->prepare("
            UPDATE fnx_tasks 
            SET doc_path = ?, doc_submitted_at = NOW(), doc_notes = ?, status = 'submitted' 
            WHERE id = ?
        ");
        $stmt->execute([$docPath, trim((string)$notes) ?: null, $taskId]);

        Audit::log('submit_doc', 'task', $taskId, "Submitted PDF documentation for task {$task['task_code']}", $employeeId);

        return ['success' => true, 'doc_path' => $docPath, 'documentation_pdf' => $docPath];
    }

    /**
     * Reassign task from one employee to another (e.g. if employee on leave)
     */
    public static function reassign(int $taskId, int $fromEmployeeId, int $toEmployeeId, int $assignedBy, string $reason): array
    {
        $db = Database::connect();
        $task = self::find($taskId);
        if (!$task) {
            return ['success' => false, 'error' => 'Task not found.'];
        }

        // If fromEmployeeId is not explicitly set, find first active assignee
        if ($fromEmployeeId <= 0) {
            $activeAssignees = self::getAssignees($taskId);
            if (!empty($activeAssignees)) {
                $fromEmployeeId = (int)$activeAssignees[0]['employee_id'];
            }
        }

        if ($fromEmployeeId > 0 && $fromEmployeeId === $toEmployeeId) {
            return ['success' => false, 'error' => 'Target assignee must be different from current assignee.'];
        }

        // Check target employee's 3-task limit!
        $targetCount = self::getActiveTaskCount($toEmployeeId);
        if ($targetCount >= self::MAX_ACTIVE_TASKS_PER_EMPLOYEE) {
            $toEmp = Employee::find($toEmployeeId);
            $name = $toEmp ? "{$toEmp['full_name']} ({$toEmp['emp_code']})" : "Employee #{$toEmployeeId}";
            return [
                'success' => false,
                'error' => "Cannot reassign: {$name} already has {$targetCount} active tasks (Limit is " . self::MAX_ACTIVE_TASKS_PER_EMPLOYEE . ")."
            ];
        }

        // Deactivate fromEmployee assignment (or any active assignment if fromEmployeeId <= 0)
        if ($fromEmployeeId > 0) {
            $deact = $db->prepare("
                UPDATE fnx_task_assignees 
                SET is_active = 0, reassigned_to = ?, reassigned_at = NOW(), reassign_reason = ? 
                WHERE task_id = ? AND employee_id = ? AND is_active = 1
            ");
            $deact->execute([$toEmployeeId, $reason, $taskId, $fromEmployeeId]);
        } else {
            $deact = $db->prepare("
                UPDATE fnx_task_assignees 
                SET is_active = 0, reassigned_to = ?, reassigned_at = NOW(), reassign_reason = ? 
                WHERE task_id = ? AND is_active = 1
            ");
            $deact->execute([$toEmployeeId, $reason, $taskId]);
        }

        // Add new assignment
        $ins = $db->prepare("
            INSERT INTO fnx_task_assignees (task_id, employee_id, assigned_by, assigned_at, is_active)
            VALUES (?, ?, ?, NOW(), 1)
        ");
        $ins->execute([$taskId, $toEmployeeId, $assignedBy]);

        Audit::log('reassign', 'task', $taskId, "Reassigned task {$task['task_code']} from #{$fromEmployeeId} to #{$toEmployeeId}. Reason: {$reason}", $assignedBy);

        try {
            EmailNotifier::sendTaskAssigned($taskId, [$toEmployeeId], $assignedBy);
        } catch (\Throwable $e) {}

        return ['success' => true];
    }

    public static function update(int $taskId, array $data, int $updatedBy): array
    {
        $db = Database::connect();
        $task = self::find($taskId);
        if (!$task) {
            return ['success' => false, 'error' => 'Task not found.'];
        }

        $title       = trim((string)($data['title'] ?? $task['title']));
        $description = trim((string)($data['description'] ?? $task['description']));
        $priority    = $data['priority'] ?? $task['priority'];
        $dueDate     = !empty($data['due_date']) ? $data['due_date'] : $task['due_date'];
        $dueTime     = !empty($data['due_time']) ? $data['due_time'] : ($task['due_time'] ?? '18:00:00');
        $status      = $data['status'] ?? $task['status'];

        if (empty($title)) {
            return ['success' => false, 'error' => 'Task title is required.'];
        }

        $stmt = $db->prepare("
            UPDATE fnx_tasks 
            SET title = ?, description = ?, priority = ?, due_date = ?, due_time = ?, status = ?, updated_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$title, $description, $priority, $dueDate, $dueTime, $status, $taskId]);

        // If assignees list was provided, update assignees
        if (isset($data['assignee_ids']) && is_array($data['assignee_ids'])) {
            $newAssigneeIds = array_map('intval', array_filter($data['assignee_ids']));
            if (!empty($newAssigneeIds)) {
                $currentAssignees = self::getAssignees($taskId);
                $currentIds = array_map('intval', array_column($currentAssignees, 'employee_id'));

                // Deactivate assignees removed
                foreach ($currentIds as $cId) {
                    if (!in_array($cId, $newAssigneeIds, true)) {
                        $db->prepare("UPDATE fnx_task_assignees SET is_active = 0 WHERE task_id = ? AND employee_id = ?")->execute([$taskId, $cId]);
                    }
                }

                // Add newly added assignees
                foreach ($newAssigneeIds as $nId) {
                    if (!in_array($nId, $currentIds, true)) {
                        $count = self::getActiveTaskCount($nId);
                        if ($count < self::MAX_ACTIVE_TASKS_PER_EMPLOYEE) {
                            $db->prepare("INSERT INTO fnx_task_assignees (task_id, employee_id, assigned_by, assigned_at, is_active) VALUES (?, ?, ?, NOW(), 1)")
                               ->execute([$taskId, $nId, $updatedBy]);
                        }
                    }
                }
            }
        }

        Audit::log('update', 'task', $taskId, "Updated task {$task['task_code']}: {$title}", $updatedBy);
        return ['success' => true];
    }

    public static function delete(int $taskId, int $deletedBy): array
    {
        $db = Database::connect();
        $task = self::find($taskId);
        if (!$task) {
            return ['success' => false, 'error' => 'Task not found.'];
        }

        // Clean up linked daily update task associations
        try {
            $db->prepare("DELETE FROM fnx_daily_update_tasks WHERE task_id = ?")->execute([$taskId]);
        } catch (\Throwable $e) {}

        // Clean up assignees
        try {
            $db->prepare("DELETE FROM fnx_task_assignees WHERE task_id = ?")->execute([$taskId]);
        } catch (\Throwable $e) {}

        // Delete task
        $db->prepare("DELETE FROM fnx_tasks WHERE id = ?")->execute([$taskId]);

        Audit::log('delete', 'task', $taskId, "Deleted task {$task['task_code']}: {$task['title']}", $deletedBy);
        return ['success' => true];
    }

    public static function approve(int $taskId, int $approverId, ?string $feedback = null): bool
    {
        try {
            $db = Database::connect();
            $task = self::find($taskId);
            if (!$task) return false;

            $stmt = $db->prepare("
                UPDATE fnx_tasks 
                SET status = 'approved', approved_by = ?, approved_at = NOW(), review_feedback = ?
                WHERE id = ?
            ");
            $stmt->execute([$approverId, trim((string)$feedback) ?: null, $taskId]);

            Audit::log('approve', 'task', $taskId, "Approved task {$task['task_code']}", $approverId);
            try {
                EmailNotifier::sendTaskReviewDecision($taskId, 'approved', $feedback, $approverId);
            } catch (\Throwable $e) {}
            return true;
        } catch (\Throwable $e) {
            error_log('Task::approve error: ' . $e->getMessage());
            return false;
        }
    }

    public static function reject(int $taskId, int $reviewerId, ?string $feedback = null): bool
    {
        try {
            $db = Database::connect();
            $task = self::find($taskId);
            if (!$task) return false;

            try {
                $stmt = $db->prepare("
                    UPDATE fnx_tasks 
                    SET status = 'reopened', review_feedback = ? 
                    WHERE id = ?
                ");
                $stmt->execute([trim((string)$feedback) ?: 'Revision requested', $taskId]);
            } catch (\Throwable $e) {
                // Fallback to 'rejected' if 'reopened' enum is not yet loaded in current connection
                $stmt = $db->prepare("
                    UPDATE fnx_tasks 
                    SET status = 'rejected', review_feedback = ? 
                    WHERE id = ?
                ");
                $stmt->execute([trim((string)$feedback) ?: 'Revision requested', $taskId]);
            }

            Audit::log('reject', 'task', $taskId, "Returned task {$task['task_code']} for revision: " . ($feedback ?: 'Needs rework'), $reviewerId);
            try {
                EmailNotifier::sendTaskReviewDecision($taskId, 'reopened', $feedback, $reviewerId);
            } catch (\Throwable $e) {}
            return true;
        } catch (\Throwable $e) {
            error_log('Task::reject error: ' . $e->getMessage());
            return false;
        }
    }
}
