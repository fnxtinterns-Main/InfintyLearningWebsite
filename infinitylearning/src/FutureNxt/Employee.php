<?php
declare(strict_types=1);

namespace App\FutureNxt;

final class Employee
{
    /**
     * Generate unique employee code based on role and optional reporting manager
     */
    public static function generateNextCode(string $role, ?int $managerId = null): string
    {
        $db = Database::connect();
        $role = strtolower(trim($role));

        // If assigned under a Manager (e.g. FNX-M-001):
        // Employee code will be FNX-M-001-E-001, FNX-M-001-E-002, etc.
        // Intern code will be FNX-M-001-I-001, etc.
        if ($managerId !== null && $managerId > 0) {
            $mStmt = $db->prepare("SELECT id, emp_code, role FROM fnx_employees WHERE id = ? LIMIT 1");
            $mStmt->execute([$managerId]);
            $mgr = $mStmt->fetch();
            if ($mgr) {
                $mgrRole = $mgr['role'];
                $mgrCode = $mgr['emp_code'];

                if ($mgrRole === 'manager' || $mgrRole === 'office_manager' || str_starts_with($mgrCode, 'FNX-M-')) {
                    $subType = ($role === 'intern') ? 'I' : 'E';
                    $prefix = $mgrCode . '-' . $subType . '-';

                    $stmt = $db->prepare("SELECT emp_code FROM fnx_employees WHERE emp_code LIKE ? ORDER BY id DESC");
                    $stmt->execute([$prefix . '%']);
                    $codes = $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];

                    $maxNum = 0;
                    foreach ($codes as $code) {
                        $numPart = (int)str_replace($prefix, '', (string)$code);
                        if ($numPart > $maxNum) {
                            $maxNum = $numPart;
                        }
                    }
                    $nextNum = $maxNum + 1;
                    return $prefix . str_pad((string)$nextNum, 3, '0', STR_PAD_LEFT);
                }
            }
        }

        if ($role === 'ceo') {
            $chk = $db->query("SELECT id FROM fnx_employees WHERE emp_code = 'FNX-CEO' LIMIT 1")->fetch();
            if (!$chk) return 'FNX-CEO';
        }
        if ($role === 'cto') {
            $chk = $db->query("SELECT id FROM fnx_employees WHERE emp_code = 'FNX-CTO' LIMIT 1")->fetch();
            if (!$chk) return 'FNX-CTO';
        }

        $prefix = match($role) {
            'intern'         => 'FNX-I-',
            'office_manager' => 'FNX-OI-',
            'manager'        => 'FNX-M-',
            'ceo'            => 'FNX-CEO-',
            'cto'            => 'FNX-CTO-',
            'admin'          => 'FNX-ADM-',
            default          => 'FNX-E-',
        };

        $stmt = $db->prepare("
            SELECT emp_code FROM fnx_employees 
            WHERE emp_code LIKE ? 
            ORDER BY id DESC
        ");
        $stmt->execute([$prefix . '%']);
        $codes = $stmt->fetchAll(\PDO::FETCH_COLUMN) ?: [];

        $maxNum = 0;
        foreach ($codes as $code) {
            $numPart = (int)str_replace($prefix, '', (string)$code);
            if ($numPart > $maxNum) {
                $maxNum = $numPart;
            }
        }

        $nextNum = $maxNum + 1;
        return $prefix . str_pad((string)$nextNum, 3, '0', STR_PAD_LEFT);
    }

    public static function list(array $filters = []): array
    {
        $db = Database::connect();
        $sql = "SELECT e.*, 
                    mgr.full_name AS manager_name,
                    mgr.emp_code AS manager_code,
                    mgr.role AS manager_role,
                    (SELECT COUNT(*) FROM fnx_employees sub WHERE sub.manager_id = e.id) AS reportees_count,
                    (SELECT COUNT(DISTINCT ta.task_id) 
                     FROM fnx_task_assignees ta 
                     JOIN fnx_tasks t ON t.id = ta.task_id 
                     WHERE ta.employee_id = e.id AND ta.is_active = 1 AND t.status IN ('pending', 'in_progress', 'submitted')
                    ) AS active_tasks_count
                FROM fnx_employees e 
                LEFT JOIN fnx_employees mgr ON mgr.id = e.manager_id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['role'])) {
            $sql .= " AND e.role = ?";
            $params[] = $filters['role'];
        }
        if (!empty($filters['status'])) {
            $sql .= " AND e.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['department'])) {
            $sql .= " AND e.department = ?";
            $params[] = $filters['department'];
        }
        if (!empty($filters['manager_id'])) {
            $sql .= " AND e.manager_id = ?";
            $params[] = (int)$filters['manager_id'];
        }
        if (!empty($filters['q'])) {
            $q = '%' . trim($filters['q']) . '%';
            $sql .= " AND (e.full_name LIKE ? OR e.email LIKE ? OR e.emp_code LIKE ? OR e.phone LIKE ? OR e.designation LIKE ?)";
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
            $params[] = $q;
        }

        $sql .= " ORDER BY FIELD(e.role, 'ceo', 'cto', 'admin', 'office_manager', 'manager', 'employee', 'intern'), e.id ASC";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    }

    public static function find(int $id): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT e.*,
                mgr.full_name AS manager_name,
                mgr.emp_code AS manager_code,
                mgr.role AS manager_role,
                (SELECT COUNT(*) FROM fnx_employees sub WHERE sub.manager_id = e.id) AS reportees_count,
                (SELECT COUNT(DISTINCT ta.task_id) 
                 FROM fnx_task_assignees ta 
                 JOIN fnx_tasks t ON t.id = ta.task_id 
                 WHERE ta.employee_id = e.id AND ta.is_active = 1 AND t.status IN ('pending', 'in_progress', 'submitted')
                ) AS active_tasks_count
            FROM fnx_employees e 
            LEFT JOIN fnx_employees mgr ON mgr.id = e.manager_id
            WHERE e.id = ? 
            LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) {
            $row['reportees'] = self::getReportees($id);
        }
        return $row ?: null;
    }

    public static function getManagers(): array
    {
        $db = Database::connect();
        $stmt = $db->query("
            SELECT id, emp_code, full_name, role, designation, department 
            FROM fnx_employees 
            WHERE status = 'active' 
              AND role IN ('ceo', 'cto', 'admin', 'office_manager', 'manager')
            ORDER BY FIELD(role, 'ceo', 'cto', 'admin', 'office_manager', 'manager'), full_name ASC
        ");
        return $stmt->fetchAll() ?: [];
    }

    public static function getReportees(int $managerId): array
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT e.*,
                (SELECT COUNT(DISTINCT ta.task_id) 
                 FROM fnx_task_assignees ta 
                 JOIN fnx_tasks t ON t.id = ta.task_id 
                 WHERE ta.employee_id = e.id AND ta.is_active = 1 AND t.status IN ('pending', 'in_progress', 'submitted')
                ) AS active_tasks_count
            FROM fnx_employees e 
            WHERE e.manager_id = ? AND e.status = 'active'
            ORDER BY FIELD(e.role, 'manager', 'employee', 'intern'), e.full_name ASC
        ");
        $stmt->execute([$managerId]);
        return $stmt->fetchAll() ?: [];
    }

    public static function findById(int $id): ?array
    {
        return self::find($id);
    }

    public static function getStats(): array
    {
        $db = Database::connect();
        $stmt = $db->query("
            SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) AS active,
                SUM(CASE WHEN status = 'pip' THEN 1 ELSE 0 END) AS pip,
                SUM(CASE WHEN status = 'suspended' THEN 1 ELSE 0 END) AS suspended,
                SUM(CASE WHEN role = 'intern' THEN 1 ELSE 0 END) AS interns,
                SUM(CASE WHEN role = 'manager' THEN 1 ELSE 0 END) AS managers,
                SUM(CASE WHEN role IN ('ceo', 'cto', 'admin') THEN 1 ELSE 0 END) AS leadership
            FROM fnx_employees
        ");
        return $stmt->fetch() ?: [
            'total' => 0, 'active' => 0, 'pip' => 0, 'suspended' => 0, 'interns' => 0, 'managers' => 0, 'leadership' => 0
        ];
    }

    public static function findByCode(string $code): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM fnx_employees WHERE emp_code = ? LIMIT 1");
        $stmt->execute([trim($code)]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function create(array $data, array $files = []): array
    {
        $db = Database::connect();

        $email = strtolower(trim((string)($data['email'] ?? '')));
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => 'A valid email address is required.'];
        }

        // Email uniqueness
        $chk = $db->prepare("SELECT id FROM fnx_employees WHERE email = ? LIMIT 1");
        $chk->execute([$email]);
        if ($chk->fetch()) {
            return ['success' => false, 'error' => 'An employee with this email already exists.'];
        }

        $fullName = trim((string)($data['full_name'] ?? ''));
        if ($fullName === '') {
            return ['success' => false, 'error' => 'Full name is required.'];
        }

        $role = strtolower(trim((string)($data['role'] ?? 'employee')));
        $validRoles = ['admin', 'ceo', 'cto', 'office_manager', 'manager', 'employee', 'intern'];
        if (!in_array($role, $validRoles, true)) {
            $role = 'employee';
        }

        // Manager ID / Reporting To
        $managerId = !empty($data['manager_id']) ? (int)$data['manager_id'] : null;
        if (!$managerId && (Auth::role() === 'manager' || Auth::role() === 'office_manager')) {
            $managerId = (int)Auth::id();
        }

        // Generate or validate employee code
        $empCode = trim((string)($data['emp_code'] ?? ''));
        if ($empCode === '') {
            $empCode = self::generateNextCode($role, $managerId);
        } else {
            $empCode = strtoupper($empCode);
            $chkCode = $db->prepare("SELECT id FROM fnx_employees WHERE emp_code = ? LIMIT 1");
            $chkCode->execute([$empCode]);
            if ($chkCode->fetch()) {
                return ['success' => false, 'error' => "Employee code '{$empCode}' is already taken. Please choose another or let the system auto-generate."];
            }
        }

        // Password
        $password = trim((string)($data['password'] ?? ''));
        if ($password === '') {
            $password = 'Inflearning@99'; // Default initial password
        }
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // Upload documents & photo
        $photoPath       = self::handleUpload($files['photo'] ?? null, 'photos', ['jpg','jpeg','png','webp']);
        $aadharDocPath   = self::handleUpload($files['aadhar_doc'] ?? null, 'documents', ['pdf','jpg','jpeg','png']);
        $panDocPath      = self::handleUpload($files['pan_doc'] ?? null, 'documents', ['pdf','jpg','jpeg','png']);
        $resumeDocPath   = self::handleUpload($files['resume_doc'] ?? null, 'documents', ['pdf','docx','doc']);
        $offerLetterPath = self::handleUpload($files['offer_letter_doc'] ?? null, 'documents', ['pdf']);

        $stmt = $db->prepare("
            INSERT INTO fnx_employees (
                emp_code, email, password_hash, full_name, role, manager_id, designation, department,
                phone, emergency_contact_name, emergency_contact_phone, emergency_contact_relation,
                dob, date_of_joining, permanent_address, current_address, photo_path,
                aadhar_number, aadhar_doc_path, pan_number, pan_doc_path,
                bank_name, bank_account_no, bank_ifsc, bank_account_name, upi_id,
                resume_doc_path, highest_qualification, offer_letter_doc_path,
                status, created_by, created_at
            ) VALUES (
                ?, ?, ?, ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?, ?,
                ?, ?, ?,
                ?, ?, NOW()
            )
        ");

        $stmt->execute([
            $empCode,
            $email,
            $passwordHash,
            $fullName,
            $role,
            $managerId,
            trim((string)($data['designation'] ?? ucfirst($role))),
            trim((string)($data['department'] ?? 'General')),
            trim((string)($data['phone'] ?? '')) ?: null,
            trim((string)($data['emergency_contact_name'] ?? '')) ?: null,
            trim((string)($data['emergency_contact_phone'] ?? '')) ?: null,
            trim((string)($data['emergency_contact_relation'] ?? '')) ?: null,
            !empty($data['dob']) ? $data['dob'] : null,
            !empty($data['date_of_joining']) ? $data['date_of_joining'] : date('Y-m-d'),
            trim((string)($data['permanent_address'] ?? '')) ?: null,
            trim((string)($data['current_address'] ?? '')) ?: null,
            $photoPath,
            trim((string)($data['aadhar_number'] ?? '')) ?: null,
            $aadharDocPath,
            trim((string)($data['pan_number'] ?? '')) ?: null,
            $panDocPath,
            trim((string)($data['bank_name'] ?? '')) ?: null,
            trim((string)($data['bank_account_no'] ?? '')) ?: null,
            trim((string)($data['bank_ifsc'] ?? '')) ?: null,
            trim((string)($data['bank_account_name'] ?? '')) ?: null,
            trim((string)($data['upi_id'] ?? '')) ?: null,
            $resumeDocPath,
            trim((string)($data['highest_qualification'] ?? '')) ?: null,
            $offerLetterPath,
            trim((string)($data['status'] ?? 'active')),
            Auth::id()
        ]);

        $newId = (int)$db->lastInsertId();
        Audit::log('create', 'employee', $newId, "Created employee {$empCode} ({$fullName}) with role {$role}");

        return ['success' => true, 'id' => $newId, 'emp_code' => $empCode, 'initial_password' => $password];
    }

    public static function update(int $id, array $data, array $files = []): array
    {
        $db = Database::connect();
        $curr = self::find($id);
        if (!$curr) {
            return ['success' => false, 'error' => 'Employee not found.'];
        }

        $email = strtolower(trim((string)($data['email'] ?? $curr['email'])));
        if ($email !== $curr['email']) {
            $chk = $db->prepare("SELECT id FROM fnx_employees WHERE email = ? AND id != ? LIMIT 1");
            $chk->execute([$email, $id]);
            if ($chk->fetch()) {
                return ['success' => false, 'error' => 'Email is already taken by another employee.'];
            }
        }

        // Optional uploads
        $photoPath       = self::handleUpload($files['photo'] ?? null, 'photos', ['jpg','jpeg','png','webp']) ?: $curr['photo_path'];
        $aadharDocPath   = self::handleUpload($files['aadhar_doc'] ?? null, 'documents', ['pdf','jpg','jpeg','png']) ?: $curr['aadhar_doc_path'];
        $panDocPath      = self::handleUpload($files['pan_doc'] ?? null, 'documents', ['pdf','jpg','jpeg','png']) ?: $curr['pan_doc_path'];
        $resumeDocPath   = self::handleUpload($files['resume_doc'] ?? null, 'documents', ['pdf','docx','doc']) ?: $curr['resume_doc_path'];
        $offerLetterPath = self::handleUpload($files['offer_letter_doc'] ?? null, 'documents', ['pdf']) ?: $curr['offer_letter_doc_path'];

        $sql = "UPDATE fnx_employees SET 
                    email = ?, full_name = ?, designation = ?, department = ?, phone = ?,
                    emergency_contact_name = ?, emergency_contact_phone = ?, emergency_contact_relation = ?,
                    dob = ?, date_of_joining = ?, permanent_address = ?, current_address = ?,
                    photo_path = ?, aadhar_number = ?, aadhar_doc_path = ?, pan_number = ?, pan_doc_path = ?,
                    bank_name = ?, bank_account_no = ?, bank_ifsc = ?, bank_account_name = ?, upi_id = ?,
                    resume_doc_path = ?, highest_qualification = ?, offer_letter_doc_path = ?
        ";
        $params = [
            $email,
            trim((string)($data['full_name'] ?? $curr['full_name'])),
            trim((string)($data['designation'] ?? $curr['designation'])),
            trim((string)($data['department'] ?? $curr['department'])),
            trim((string)($data['phone'] ?? $curr['phone'])),
            trim((string)($data['emergency_contact_name'] ?? $curr['emergency_contact_name'])),
            trim((string)($data['emergency_contact_phone'] ?? $curr['emergency_contact_phone'])),
            trim((string)($data['emergency_contact_relation'] ?? $curr['emergency_contact_relation'])),
            !empty($data['dob']) ? $data['dob'] : $curr['dob'],
            !empty($data['date_of_joining']) ? $data['date_of_joining'] : $curr['date_of_joining'],
            trim((string)($data['permanent_address'] ?? $curr['permanent_address'])),
            trim((string)($data['current_address'] ?? $curr['current_address'])),
            $photoPath,
            trim((string)($data['aadhar_number'] ?? $curr['aadhar_number'])),
            $aadharDocPath,
            trim((string)($data['pan_number'] ?? $curr['pan_number'])),
            $panDocPath,
            trim((string)($data['bank_name'] ?? $curr['bank_name'])),
            trim((string)($data['bank_account_no'] ?? $curr['bank_account_no'])),
            trim((string)($data['bank_ifsc'] ?? $curr['bank_ifsc'])),
            trim((string)($data['bank_account_name'] ?? $curr['bank_account_name'])),
            trim((string)($data['upi_id'] ?? $curr['upi_id'])),
            $resumeDocPath,
            trim((string)($data['highest_qualification'] ?? $curr['highest_qualification'])),
            $offerLetterPath,
        ];

        // Admin/CEO/CTO can also change role, status, PIP, reporting manager
        if (Auth::isAdminOrExec()) {
            if (!empty($data['role'])) {
                $sql .= ", role = ?";
                $params[] = $data['role'];
            }
            if (isset($data['manager_id'])) {
                $mgrId = !empty($data['manager_id']) ? (int)$data['manager_id'] : null;
                if ($mgrId !== $id) {
                    $sql .= ", manager_id = ?";
                    $params[] = $mgrId;
                }
            }
            if (!empty($data['status'])) {
                $sql .= ", status = ?";
                $params[] = $data['status'];
            }
            if (isset($data['pip_reason'])) {
                $sql .= ", pip_reason = ?";
                $params[] = $data['pip_reason'] ?: null;
            }
            if (isset($data['pip_end_date'])) {
                $sql .= ", pip_end_date = ?";
                $params[] = !empty($data['pip_end_date']) ? $data['pip_end_date'] : null;
            }
        }

        // Password change if provided
        if (!empty($data['new_password'])) {
            $sql .= ", password_hash = ?";
            $params[] = password_hash(trim($data['new_password']), PASSWORD_DEFAULT);
        }

        $sql .= " WHERE id = ?";
        $params[] = $id;

        $stmt = $db->prepare($sql);
        $stmt->execute($params);

        Audit::log('update', 'employee', $id, "Updated employee details for {$curr['emp_code']}");

        return ['success' => true];
    }

    public static function updateStatus(int $id, string $status, ?string $pipReason = null, ?string $pipEndDate = null): bool
    {
        $db = Database::connect();
        $curr = self::find($id);
        if (!$curr) return false;

        $stmt = $db->prepare("
            UPDATE fnx_employees 
            SET status = ?, pip_reason = ?, pip_end_date = ? 
            WHERE id = ?
        ");
        $stmt->execute([
            $status,
            $status === 'pip' ? $pipReason : null,
            $status === 'pip' && !empty($pipEndDate) ? $pipEndDate : null,
            $id
        ]);

        Audit::log('status_change', 'employee', $id, "Changed status of {$curr['emp_code']} to {$status}");
        return true;
    }

    public static function updatePhoto(int $id, array $file): array
    {
        $db = Database::connect();
        $curr = self::find($id);
        if (!$curr) {
            return ['success' => false, 'error' => 'Employee record not found.'];
        }

        if (empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return ['success' => false, 'error' => 'Please select a valid image file.'];
        }

        // Limit file size to 5MB
        if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
            return ['success' => false, 'error' => 'Image file is too large. Maximum allowed size is 5MB.'];
        }

        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts, true)) {
            return ['success' => false, 'error' => 'Invalid file format. Only JPG, PNG, and WEBP images are allowed.'];
        }

        $photoPath = self::handleUpload($file, 'photos', $allowedExts);
        if (!$photoPath) {
            return ['success' => false, 'error' => 'Failed to save uploaded photo to storage.'];
        }

        $stmt = $db->prepare("UPDATE fnx_employees SET photo_path = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$photoPath, $id]);

        Audit::log('update_photo', 'employee', $id, "Updated profile photo for {$curr['emp_code']}");

        return ['success' => true, 'photo_path' => $photoPath];
    }

    private static function handleUpload(?array $file, string $subfolder, array $allowedExts): ?string
    {
        if (!$file || empty($file['tmp_name']) || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        $ext = strtolower(pathinfo((string)$file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowedExts, true)) {
            return null;
        }

        $baseDir = dirname(__DIR__, 3) . '/public_html/uploads/futurenxt/' . $subfolder;
        if (!is_dir($baseDir)) {
            @mkdir($baseDir, 0775, true);
        }

        $filename = bin2hex(random_bytes(10)) . '_' . time() . '.' . $ext;
        $dest = $baseDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $dest)) {
            return '/uploads/futurenxt/' . $subfolder . '/' . $filename;
        }
        return null;
    }
}
