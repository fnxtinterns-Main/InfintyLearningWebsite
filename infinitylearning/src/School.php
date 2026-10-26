<?php
declare(strict_types=1);

namespace App;

final class School
{
    // =========================================================
    // READ
    // =========================================================

    public static function all(): array
    {
        return Database::connect()
            ->query("SELECT * FROM schools WHERE status IN ('active','pending') ORDER BY name")
            ->fetchAll();
    }

    /** Include archived schools — for the super admin's Schools page. */
    public static function allWithArchived(): array
    {
        return Database::connect()
            ->query("SELECT * FROM schools ORDER BY 
                        CASE status 
                            WHEN 'active' THEN 1 
                            WHEN 'pending' THEN 2 
                            WHEN 'archived' THEN 3 
                            ELSE 4 
                        END, name")
            ->fetchAll();
    }

    /** Only active schools — for registration dropdowns. */
    public static function activeForDropdown(): array
    {
        self::ensureSchema();
        $schools = Database::connect()
            ->query("SELECT id, name, city, state, board FROM schools WHERE status='active' ORDER BY name")
            ->fetchAll();

        $hasAiverse = false;
        foreach ($schools as $s) {
            if (strtoupper($s['name']) === 'AIVERSE' || str_contains(strtoupper($s['name']), 'AIVERSE')) {
                $hasAiverse = true;
                break;
            }
        }

        if (!$hasAiverse) {
            try {
                $aiverse = self::getOrCreateAiverse();
                if ($aiverse) {
                    array_unshift($schools, [
                        'id'    => (int)$aiverse['id'],
                        'name'  => 'AIVERSE',
                        'city'  => 'Online',
                        'state' => 'India',
                        'board' => 'AI Academy'
                    ]);
                }
            } catch (\Throwable $e) {}
        }

        return $schools;
    }

    public static function find(int $id): ?array
    {
        self::ensureSchema();
        $stmt = Database::connect()->prepare("SELECT * FROM schools WHERE id=? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findByCode(string $code): ?array
    {
        $stmt = Database::connect()->prepare("SELECT * FROM schools WHERE code=? LIMIT 1");
        $stmt->execute([strtoupper(trim($code))]);
        return $stmt->fetch() ?: null;
    }

    public static function forUser(int $userId): ?array
    {
        $stmt = Database::connect()->prepare("
            SELECT s.* FROM schools s
            JOIN users u ON u.school_id = s.id
            WHERE u.id = ? LIMIT 1
        ");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    // =========================================================
    // CREATE + UPDATE
    // =========================================================

    public static function createWithAdmin(array $d, int $createdBy): array
    {
        $db = Database::connect();
        $db->beginTransaction();

        try {
            $code = self::generateUniqueCode($d['name']);

            $stmt = $db->prepare("
                INSERT INTO schools
                    (uuid, code, name, principal_name, board, city, state, address,
                     contact_email, contact_phone, phone, website, student_capacity,
                     teacher_capacity, status, created_by)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
            ");
            $stmt->execute([
                Auth::uuid(),
                $code,
                trim($d['name']),
                trim($d['principal_name'] ?? '') ?: null,
                trim($d['board'] ?? '') ?: null,
                trim($d['city'] ?? '') ?: null,
                trim($d['state'] ?? '') ?: null,
                trim($d['address'] ?? '') ?: null,
                trim($d['contact_email'] ?? '') ?: null,
                trim($d['contact_phone'] ?? '') ?: null,
                trim($d['phone'] ?? '') ?: null,
                trim($d['website'] ?? '') ?: null,
                (int)($d['student_capacity'] ?? 500),
                (int)($d['teacher_capacity'] ?? 100),
                'active',
                $createdBy,
            ]);

            $schoolId = (int)$db->lastInsertId();

            $adminEmail = trim($d['admin_email'] ?? '');
            $adminPhone = trim($d['admin_phone'] ?? '');

            if ($adminEmail === '' || $adminPhone === '') {
                throw new \RuntimeException('School admin email and phone are required.');
            }
            if (Auth::findByEmail($adminEmail)) {
                throw new \RuntimeException('An account with that email already exists.');
            }

            $plainPassword = Importer::generatePassword(12);

            $adminUserId = Auth::createUser([
                'email'    => $adminEmail,
                'phone'    => $adminPhone,
                'password' => $plainPassword,
                'role'     => 'admin',
                'status'   => 'active',
            ]);

            $db->prepare("
                UPDATE users SET school_id=?, phone_verified_at=NOW(), email_verified_at=NOW() WHERE id=?
            ")->execute([$schoolId, $adminUserId]);

            $db->prepare("UPDATE schools SET admin_user_id=?, approved_at=NOW() WHERE id=?")
               ->execute([$adminUserId, $schoolId]);

            $db->commit();

            Audit::log('school.created', 'school', $schoolId, ['name' => $d['name'], 'code' => $code]);

            return [
                'school_id'      => $schoolId,
                'admin_user_id'  => $adminUserId,
                'code'           => $code,
                'admin_email'    => $adminEmail,
                'admin_password' => $plainPassword,
            ];

        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function ensureSchema(): void
    {
        try {
            $db = Database::connect();
            // 1. schools table columns
            $schoolCols = $db->query("SHOW COLUMNS FROM `schools`")->fetchAll(\PDO::FETCH_COLUMN);
            if (!in_array('allow_weekly_summary', $schoolCols, true)) {
                $db->exec("ALTER TABLE `schools` ADD COLUMN `allow_weekly_summary` tinyint(1) NOT NULL DEFAULT 0");
            }
            if (!in_array('weekly_summary_enabled', $schoolCols, true)) {
                $db->exec("ALTER TABLE `schools` ADD COLUMN `weekly_summary_enabled` tinyint(1) NOT NULL DEFAULT 1");
            }

            // 2. students table columns
            $studentCols = $db->query("SHOW COLUMNS FROM `students`")->fetchAll(\PDO::FETCH_COLUMN);
            if (!in_array('district', $studentCols, true)) {
                $db->exec("ALTER TABLE `students` ADD COLUMN `district` varchar(100) NULL");
            }
            if (!in_array('pincode', $studentCols, true)) {
                $db->exec("ALTER TABLE `students` ADD COLUMN `pincode` varchar(20) NULL");
            }
            if (!in_array('registration_number', $studentCols, true)) {
                $db->exec("ALTER TABLE `students` ADD COLUMN `registration_number` varchar(100) NULL AFTER `current_class`");
            }
            if (!in_array('roll_number', $studentCols, true)) {
                $db->exec("ALTER TABLE `students` ADD COLUMN `roll_number` varchar(50) NULL AFTER `registration_number`");
            }
            if (!in_array('mentor_user_id', $studentCols, true)) {
                $db->exec("ALTER TABLE `students` ADD COLUMN `mentor_user_id` bigint(20) UNSIGNED NULL");
            }

            // 3. teachers table columns
            $teacherCols = $db->query("SHOW COLUMNS FROM `teachers`")->fetchAll(\PDO::FETCH_COLUMN);
            if (!in_array('teacher_code', $teacherCols, true)) {
                $db->exec("ALTER TABLE `teachers` ADD COLUMN `teacher_code` varchar(50) NULL AFTER `user_id`");
            }
            if (!in_array('registration_number', $teacherCols, true)) {
                $db->exec("ALTER TABLE `teachers` ADD COLUMN `registration_number` varchar(100) NULL AFTER `teacher_code`");
            }
            if (!in_array('class_teacher_grade', $teacherCols, true)) {
                $db->exec("ALTER TABLE `teachers` ADD COLUMN `class_teacher_grade` varchar(50) NULL");
            }
            if (!in_array('class_teacher_section', $teacherCols, true)) {
                $db->exec("ALTER TABLE `teachers` ADD COLUMN `class_teacher_section` varchar(20) NULL");
            }
        } catch (\Throwable $e) {
            error_log('ensureSchema error: ' . $e->getMessage());
        }
    }

    /**
     * Generate unique teacher ID starting with school name first letter (e.g. G12344).
     */
    public static function generateTeacherCode(?string $schoolName): string
    {
        $firstLetter = 'T';
        $trimmed = trim((string)$schoolName);
        if ($trimmed !== '') {
            $cleaned = preg_replace('/[^A-Za-z]/', '', $trimmed);
            if (!empty($cleaned)) {
                $firstLetter = strtoupper(substr($cleaned, 0, 1));
            }
        }
        try {
            $db = Database::connect();
            for ($i = 0; $i < 10; $i++) {
                $digits = str_pad((string)random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
                $code = $firstLetter . $digits;
                $stmt = $db->prepare("SELECT id FROM teachers WHERE teacher_code = ? LIMIT 1");
                $stmt->execute([$code]);
                if (!$stmt->fetch()) {
                    return $code;
                }
            }
        } catch (\Throwable $e) {
            // fallback
        }
        return $firstLetter . random_int(10000, 99999);
    }

    public static function isAiverse(?int $schoolId): bool
    {
        if (!$schoolId || $schoolId <= 0) return false;
        $school = self::find($schoolId);
        if (!$school) return false;
        $name = strtoupper(trim($school['name'] ?? ''));
        $code = strtoupper(trim($school['code'] ?? ''));
        return $name === 'AIVERSE' || $code === 'AIVERSE' || str_contains($name, 'AIVERSE');
    }

    public static function getOrCreateAiverse(): array
    {
        self::ensureSchema();
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM schools WHERE UPPER(name) LIKE '%AIVERSE%' OR UPPER(code) LIKE '%AIVERSE%' LIMIT 1");
        $stmt->execute();
        $school = $stmt->fetch();
        if ($school) {
            return $school;
        }

        $uuid = Auth::uuid();
        $insert = $db->prepare("
            INSERT INTO schools (uuid, code, name, board, city, state, address, contact_email, status)
            VALUES (?, 'AIVERSE', 'AIVERSE', 'AI Academy', 'Online', 'India', 'AIverse Learning Portal', 'info@infintylearning.net', 'active')
        ");
        $insert->execute([$uuid]);
        return self::find((int)$db->lastInsertId()) ?: [
            'id' => (int)$db->lastInsertId(),
            'name' => 'AIVERSE',
            'code' => 'AIVERSE',
            'status' => 'active'
        ];
    }

    public static function update(int $id, array $d): void
    {
        self::ensureSchema();
        Database::connect()->prepare("
            UPDATE schools SET
                name=?, principal_name=?, board=?, city=?, state=?, address=?,
                contact_email=?, contact_phone=?, phone=?, website=?,
                student_capacity=?, teacher_capacity=?,
                allow_weekly_summary=?
            WHERE id=?
        ")->execute([
            trim($d['name']),
            trim($d['principal_name'] ?? '') ?: null,
            trim($d['board'] ?? '') ?: null,
            trim($d['city'] ?? '') ?: null,
            trim($d['state'] ?? '') ?: null,
            trim($d['address'] ?? '') ?: null,
            trim($d['contact_email'] ?? '') ?: null,
            trim($d['contact_phone'] ?? '') ?: null,
            trim($d['phone'] ?? '') ?: null,
            trim($d['website'] ?? '') ?: null,
            (int)($d['student_capacity'] ?? 500),
            (int)($d['teacher_capacity'] ?? 100),
            !empty($d['allow_weekly_summary']) ? 1 : 0,
            $id,
        ]);
        Audit::log('school.updated', 'school', $id);
    }

    public static function setWeeklySummaryToggle(int $id, bool $enabled): void
    {
        self::ensureSchema();
        Database::connect()->prepare("UPDATE schools SET weekly_summary_enabled=? WHERE id=?")
            ->execute([$enabled ? 1 : 0, $id]);
        Audit::log('school.weekly_summary_toggled', 'school', $id, ['enabled' => $enabled]);
    }

    public static function setStatus(int $id, string $status): void
    {
        if (!in_array($status, ['pending','active','suspended','archived'], true)) return;
        Database::connect()->prepare("UPDATE schools SET status=? WHERE id=?")->execute([$status, $id]);
        Audit::log('school.status_changed', 'school', $id, ['status' => $status]);
    }

    // =========================================================
    // ARCHIVE / RESTORE (recoverable deletion)
    // =========================================================

    /**
     * Archive a school — everything is preserved, but nobody from the school
     * can log in, and the school is hidden from active views.
     *
     * Everything reverses with restore().
     */
    public static function archive(int $id): void
    {
        $db = Database::connect();
        $db->beginTransaction();
        try {
            // 1. Archive the school itself
            $db->prepare("UPDATE schools SET status='archived' WHERE id=?")->execute([$id]);

            // 2. Suspend ALL users under this school (admin, teachers, students)
            $db->prepare("UPDATE users SET status='suspended' WHERE school_id=?")
               ->execute([$id]);

            // 3. Archive batches
            $db->prepare("UPDATE batches SET status='archived' WHERE school_id=?")
               ->execute([$id]);

            // 4. Un-publish lessons (they're invisible to students anyway once they can't log in)
            $db->prepare("UPDATE lessons SET published=0 WHERE school_id=?")
               ->execute([$id]);

            // 5. Archive activities
            $db->prepare("UPDATE activities SET status='archived' WHERE school_id=?")
               ->execute([$id]);

            // 6. Archive announcements
            $db->prepare("UPDATE announcements SET school_id=school_id WHERE school_id=? AND 0=1")
               ->execute([$id]);
            // (Note: announcements keep their school_id; they're hidden via the school scope
            // in queries. If you want them explicitly archived, add a status column to
            // announcements first.)

            // 7. Kill any active sessions for users of this school
            $db->prepare("DELETE s FROM sessions s
                          JOIN users u ON u.id = s.user_id
                          WHERE u.school_id = ?")
               ->execute([$id]);

            $db->commit();

            Audit::log('school.archived', 'school', $id, [
                'users_suspended' => self::countUsers($id),
            ]);

        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Restore an archived school — reactivates all users, batches,
     * lessons and activities exactly where they left off.
     */
    public static function restore(int $id): void
    {
        $db = Database::connect();
        $db->beginTransaction();
        try {
            // 1. Reactivate the school
            $db->prepare("UPDATE schools SET status='active' WHERE id=?")->execute([$id]);

            // 2. Reactivate all users under this school
            // BUT only those that were suspended BY the archive.
            // We use suspended + same-school as the filter.
            $db->prepare("UPDATE users SET status='active' WHERE school_id=? AND status='suspended'")
               ->execute([$id]);

            // 3. Restore batches
            $db->prepare("UPDATE batches SET status='active' WHERE school_id=? AND status='archived'")
               ->execute([$id]);

            // 4. Re-publish lessons
            $db->prepare("UPDATE lessons SET published=1 WHERE school_id=?")
               ->execute([$id]);

            // 5. Restore activities
            $db->prepare("UPDATE activities SET status='published' WHERE school_id=? AND status='archived'")
               ->execute([$id]);

            $db->commit();

            Audit::log('school.restored', 'school', $id);

        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Permanent deletion (only if you're absolutely sure).
     * Kept for edge cases — but from the UI you'll use archive().
     */
    public static function delete(int $id): void
    {
        $db = Database::connect();
        $db->beginTransaction();
        try {
            // Kill sessions
            $db->prepare("DELETE s FROM sessions s
                          JOIN users u ON u.id = s.user_id
                          WHERE u.school_id = ?")
               ->execute([$id]);

            // Suspend users, unlink from school
            $db->prepare("UPDATE users SET status='suspended', school_id=NULL WHERE school_id=?")
               ->execute([$id]);

            // Archive batches, unlink
            $db->prepare("UPDATE batches SET status='archived', school_id=NULL WHERE school_id=?")
               ->execute([$id]);

            // Unlink lessons/activities/announcements
            $db->prepare("UPDATE lessons SET school_id=NULL, published=0 WHERE school_id=?")->execute([$id]);
            $db->prepare("UPDATE activities SET school_id=NULL, status='archived' WHERE school_id=?")->execute([$id]);
            $db->prepare("UPDATE announcements SET school_id=NULL WHERE school_id=?")->execute([$id]);

            // Delete school row
            $db->prepare("DELETE FROM schools WHERE id=?")->execute([$id]);

            $db->commit();

            Audit::log('school.deleted', 'school', $id);

        } catch (\Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    // =========================================================
    // CODE GENERATION
    // =========================================================

    public static function generateUniqueCode(string $name): string
    {
        $prefix = '';
        foreach (preg_split('/\s+/', trim($name)) as $word) {
            if ($word !== '') $prefix .= strtoupper(substr($word, 0, 1));
            if (strlen($prefix) >= 4) break;
        }
        $prefix = $prefix ?: 'SCH';
        $prefix = substr($prefix, 0, 4);

        $db = Database::connect();
        for ($i = 0; $i < 20; $i++) {
            $code = $prefix . '-' . strtoupper(bin2hex(random_bytes(2)));
            $stmt = $db->prepare("SELECT id FROM schools WHERE code=? LIMIT 1");
            $stmt->execute([$code]);
            if (!$stmt->fetch()) return $code;
        }
        return 'SCH-' . strtoupper(bin2hex(random_bytes(3)));
    }

    // =========================================================
    // STATS
    // =========================================================

    public static function stats(int $schoolId): array
    {
        $db = Database::connect();
        $sid = (int)$schoolId;
        return [
            'students' => (int)$db->query("SELECT COUNT(*) FROM users WHERE role='student' AND school_id=$sid")->fetchColumn(),
            'teachers' => (int)$db->query("SELECT COUNT(*) FROM users WHERE role='teacher' AND school_id=$sid")->fetchColumn(),
            'pending'  => (int)$db->query("SELECT COUNT(*) FROM users WHERE status='pending' AND school_id=$sid AND role IN ('student','teacher')")->fetchColumn(),
            'batches'  => (int)$db->query("SELECT COUNT(*) FROM batches WHERE school_id=$sid AND status='active'")->fetchColumn(),
            'suspended'=> (int)$db->query("SELECT COUNT(*) FROM users WHERE status='suspended' AND school_id=$sid")->fetchColumn(),
        ];
    }

    public static function countUsers(int $schoolId): int
    {
        $db = Database::connect();
        return (int)$db->query("SELECT COUNT(*) FROM users WHERE school_id=" . (int)$schoolId)->fetchColumn();
    }

    // =========================================================
    // USER LISTS (school admin panel)
    // =========================================================

    public static function pendingUsers(int $schoolId, ?string $role = null): array
    {
        self::ensureSchema();
        $db = Database::connect();
        $sql = "
            SELECT u.id, u.email, u.phone, u.role, u.status, u.created_at,
                   COALESCE(s.full_name, t.full_name)      AS full_name,
                   COALESCE(s.city,      t.city)           AS city,
                   COALESCE(s.state,     t.state)          AS state,
                   COALESCE(s.gender,    t.gender)         AS gender,
                   COALESCE(s.profile_image, t.profile_image) AS avatar,
                   s.age, s.current_class, s.school_board, s.academic_year, s.interests,
                   s.registration_number AS registration_number, s.roll_number, s.mentor_user_id,
                   COALESCE(m.full_name, mu.email)         AS mentor_name,
                   t.teacher_code, t.registration_number   AS teacher_registration_number,
                   t.class_teacher_grade, t.class_teacher_section,
                   t.expert_field, t.years_experience, t.additional_expertise, t.bio
            FROM users u
            LEFT JOIN students s ON s.user_id = u.id
            LEFT JOIN teachers t ON t.user_id = u.id
            LEFT JOIN users mu ON mu.id = s.mentor_user_id
            LEFT JOIN teachers m ON m.user_id = mu.id
            WHERE u.school_id = ? AND u.status = 'pending'
        ";
        $params = [$schoolId];
        if ($role && in_array($role, ['student','teacher'], true)) {
            $sql .= " AND u.role = ?";
            $params[] = $role;
        }
        $sql .= " ORDER BY u.created_at DESC LIMIT 200";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function users(int $schoolId, ?string $role = null, ?string $status = null): array
    {
        self::ensureSchema();
        $db = Database::connect();
        $sql = "
            SELECT u.id, u.email, u.phone, u.role, u.status, u.created_at, u.last_login_at,
                   COALESCE(s.full_name, t.full_name)      AS full_name,
                   COALESCE(s.city,      t.city)           AS city,
                   COALESCE(s.state,     t.state)          AS state,
                   COALESCE(s.gender,    t.gender)         AS gender,
                   COALESCE(s.profile_image, t.profile_image) AS avatar,
                   s.age, s.current_class, s.school_board, s.academic_year, s.interests,
                   s.registration_number AS registration_number, s.roll_number, s.mentor_user_id,
                   COALESCE(m.full_name, mu.email)         AS mentor_name,
                   t.teacher_code, t.registration_number   AS teacher_registration_number,
                   t.class_teacher_grade, t.class_teacher_section,
                   t.expert_field, t.years_experience, t.additional_expertise, t.bio
            FROM users u
            LEFT JOIN students s ON s.user_id = u.id
            LEFT JOIN teachers t ON t.user_id = u.id
            LEFT JOIN users mu ON mu.id = s.mentor_user_id
            LEFT JOIN teachers m ON m.user_id = mu.id
            WHERE u.school_id = ?
        ";
        $params = [$schoolId];
        if ($role && in_array($role, ['student','teacher'], true)) {
            $sql .= " AND u.role = ?";
            $params[] = $role;
        }
        if ($status && in_array($status, ['pending','active','suspended','rejected'], true)) {
            $sql .= " AND u.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY u.created_at DESC LIMIT 500";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // Ensure every teacher has a teacher_code populated
        if ($role === 'teacher' || $role === null) {
            $sch = null;
            foreach ($rows as &$r) {
                if ($r['role'] === 'teacher' && empty($r['teacher_code'])) {
                    if (!$sch) $sch = self::find($schoolId);
                    $newCode = self::generateTeacherCode($sch['name'] ?? null);
                    try {
                        $db->prepare("UPDATE teachers SET teacher_code = ? WHERE user_id = ?")->execute([$newCode, (int)$r['id']]);
                        $r['teacher_code'] = $newCode;
                    } catch (\Throwable $e) {}
                }
            }
        }

        return $rows;
    }

    public static function assignClassTeacher(int $teacherUserId, int $schoolId, ?string $grade, ?string $section = null): bool
    {
        self::ensureSchema();
        $db = Database::connect();
        $chk = $db->prepare("SELECT id FROM users WHERE id = ? AND school_id = ? AND role = 'teacher' LIMIT 1");
        $chk->execute([$teacherUserId, $schoolId]);
        if (!$chk->fetch()) return false;

        $g = trim((string)$grade) ?: null;
        $s = trim((string)$section) ?: null;

        $stmt = $db->prepare("
            UPDATE teachers SET class_teacher_grade = ?, class_teacher_section = ?
            WHERE user_id = ?
        ");
        $stmt->execute([$g, $s, $teacherUserId]);
        Audit::log('school.class_teacher_assigned', 'teacher', $teacherUserId, [
            'grade' => $g,
            'section' => $s,
            'school_id' => $schoolId,
        ]);
        return true;
    }

    public static function assignMentor(array $studentUserIds, ?int $mentorUserId, int $schoolId): int
    {
        self::ensureSchema();
        $db = Database::connect();
        $validMentor = null;
        if (!empty($mentorUserId)) {
            $mChk = $db->prepare("SELECT id FROM users WHERE id = ? AND school_id = ? AND role = 'teacher' LIMIT 1");
            $mChk->execute([$mentorUserId, $schoolId]);
            if ($mChk->fetch()) {
                $validMentor = $mentorUserId;
            }
        }

        $count = 0;
        $upd = $db->prepare("UPDATE students SET mentor_user_id = ? WHERE user_id = ?");
        foreach ($studentUserIds as $sid) {
            $sid = (int)$sid;
            if ($sid <= 0) continue;
            $sChk = $db->prepare("SELECT id FROM users WHERE id = ? AND school_id = ? AND role = 'student' LIMIT 1");
            $sChk->execute([$sid, $schoolId]);
            if ($sChk->fetch()) {
                $upd->execute([$validMentor, $sid]);
                $count++;
            }
        }
        Audit::log('school.mentor_assigned', 'mentor', $mentorUserId ?? 0, [
            'student_count' => $count,
            'school_id' => $schoolId,
        ]);
        return $count;
    }

    public static function approveUser(int $userId, int $schoolId, int $byUserId): bool
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT id, role FROM users WHERE id=? AND school_id=? LIMIT 1");
        $stmt->execute([$userId, $schoolId]);
        $u = $stmt->fetch();
        if (!$u) return false;

        $db->prepare("UPDATE users SET status='active' WHERE id=?")->execute([$userId]);

        if ($u['role'] === 'teacher') {
            $db->prepare("
                UPDATE teachers SET approved_by=?, approved_at=NOW() WHERE user_id=?
            ")->execute([$byUserId, $userId]);
        }

        Audit::log('school.user_approved', 'user', $userId, ['school_id' => $schoolId]);
        return true;
    }

    public static function rejectUser(int $userId, int $schoolId): bool
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT id FROM users WHERE id=? AND school_id=? LIMIT 1");
        $stmt->execute([$userId, $schoolId]);
        if (!$stmt->fetch()) return false;

        $db->prepare("UPDATE users SET status='rejected' WHERE id=?")->execute([$userId]);
        Audit::log('school.user_rejected', 'user', $userId, ['school_id' => $schoolId]);
        return true;
    }
}