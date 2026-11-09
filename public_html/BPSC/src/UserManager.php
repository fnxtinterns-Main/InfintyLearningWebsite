<?php
/**
 * Infinity Learning - User Repository & Registration Manager
 * SQL-Powered User Management (Zero plaintext passwords, zero usernames, zero level column)
 */

if (!defined('BPSC_PORTAL')) {
    define('BPSC_PORTAL', true);
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';

class UserManager {
    /**
     * All 38 Districts of Bihar (Official Administrative Division)
     */
    public static function getBiharDistricts(): array {
        return [
            "Araria", "Arwal", "Aurangabad", "Banka", "Begusarai", "Bhagalpur",
            "Bhojpur (Arrah)", "Buxar", "Darbhanga", "East Champaran (Motihari)",
            "Gaya", "Gopalganj", "Jamui", "Jehanabad", "Kaimur (Bhabhua)", "Katihar",
            "Khagaria", "Kishanganj", "Lakhisarai", "Madhepura", "Madhubani", "Munger",
            "Muzaffarpur", "Nalanda (Bihar Sharif)", "Nawada", "Patna", "Purnia",
            "Rohtas (Sasaram)", "Saharsa", "Samastipur", "Saran (Chhapra)", "Sheikhpura",
            "Sheohar", "Sitamarhi", "Siwan", "Supaul", "Vaishali (Hajipur)", "West Champaran (Bettiah)"
        ];
    }

    /**
     * Cadre Levels Definition
     */
    public static function getCadreLevels(): array {
        return [
            'prm' => '1. Primary (Classes 1–5)',
            'mid' => '2. Middle School (Classes 6–8)',
            'sec' => '3. Secondary (Classes 9–10)',
            'hsec' => '4. Higher Secondary (Classes 11–12)'
        ];
    }

    /**
     * Helper to derive cadre code (prm, mid, sec, hsec) from level label
     */
    public static function getCadreCode(string $label): string {
        $l = strtolower($label);
        if (str_contains($l, 'primary') || str_contains($l, '1–5') || str_contains($l, '1-5')) return 'prm';
        if (str_contains($l, 'higher') || str_contains($l, '11–12') || str_contains($l, '11-12')) return 'hsec';
        if (str_contains($l, 'secondary') || str_contains($l, '9–10') || str_contains($l, '9-10')) return 'sec';
        return 'mid';
    }

    /**
     * Load all users from SQL database
     */
    public static function getAllUsers(): array {
        $rows = Database::fetchAll("SELECT * FROM users ORDER BY created_at ASC");
        return array_map([self::class, 'formatUserData'], $rows);
    }

    /**
     * Format user data from pure relational SQL columns
     */
    public static function formatUserData(array $user): array {
        $levelLabel = $user['level_label'] ?? 'Classes 6–8 (Middle)';
        $district = $user['district'] ?? 'Patna';

        // Direct relational mapping for subscription
        $user['subscription'] = [
            'plan_name' => $user['plan_name'] ?? ('BPSC TRE 4.0 ' . $levelLabel . ' Pass'),
            'status' => $user['plan_status'] ?? 'Active (Enrolled)',
            'badge' => $user['plan_badge'] ?? 'Active Access',
            'amount_paid' => $user['plan_amount'] ?? 'Trial Access',
            'expiry' => $user['plan_expiry'] ?? 'December 31, 2026',
            'enrolled_cadre' => $levelLabel
        ];

        // Direct relational mapping for achievements
        $rawBadges = $user['earned_badges'] ?? '';
        $badges = !empty($rawBadges)
            ? (is_array($rawBadges) ? $rawBadges : explode(',', (string)$rawBadges))
            : ['Registered Aspirant', 'Admit Ready', $district . ' Aspirant'];

        $user['achievements'] = [
            'tests_taken' => (int)($user['tests_taken'] ?? 0),
            'best_score' => (string)($user['best_score'] ?? '0.0'),
            'avg_accuracy' => ($user['avg_accuracy'] ?? '0') . '%',
            'rank_district' => $user['rank_district'] ?? 'Pending First CBT Mock',
            'earned_badges' => array_values(array_filter(array_map('trim', $badges)))
        ];

        return $user;
    }

    /**
     * Find user by Email or Phone Number (Zero username dependency)
     */
    public static function findByIdentifier(string $identifier): ?array {
        $identifier = trim(strtolower($identifier));
        if (empty($identifier)) return null;

        $cleanPhone = preg_replace('/[^0-9]/', '', $identifier);

        $sql = "SELECT * FROM users WHERE LOWER(email) = :ident OR id = :uid";
        $params = [':ident' => $identifier, ':uid' => $identifier];

        if (!empty($cleanPhone) && strlen($cleanPhone) >= 10) {
            $sql .= " OR phone LIKE :phone";
            $params[':phone'] = '%' . substr($cleanPhone, -10);
        }

        $sql .= " LIMIT 1";
        $row = Database::fetchOne($sql, $params);
        return $row ? self::formatUserData($row) : null;
    }

    /**
     * Register a Candidate (Aspirant) - Saved directly to SQL
     */
    public static function registerCandidate(array $data): array {
        $name = trim($data['name'] ?? '');
        $email = trim(strtolower($data['email'] ?? ''));
        $phone = trim($data['phone'] ?? '');
        $cadreCode = trim($data['level'] ?? 'mid');
        $district = trim($data['district'] ?? '') ?: 'Patna';
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';

        if (empty($name) || empty($email) || empty($phone) || empty($password)) {
            return ['success' => false, 'message' => 'Please fill in all mandatory candidate fields.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Please enter a valid email address.'];
        }

        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Security password must be at least 6 characters long.'];
        }

        if ($password !== $confirmPassword) {
            return ['success' => false, 'message' => 'Password and Confirm Password do not match.'];
        }

        if (self::findByIdentifier($email)) {
            return ['success' => false, 'message' => 'An account with this email is already registered. Please sign in.'];
        }

        $cadres = self::getCadreLevels();
        $levelLabel = $cadres[$cadreCode] ?? '2. Middle School (Classes 6–8)';

        $id = 'USR_STU_' . strtoupper(bin2hex(random_bytes(3)));
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $now = date('Y-m-d H:i:s');

        Database::execute(
            "INSERT INTO users (id, name, email, phone, password_hash, role, level_label, district, status, plan_name, plan_status, plan_badge, plan_amount, plan_expiry, rank_district, earned_badges, created_at)
             VALUES (:id, :name, :email, :phone, :hash, :role, :label, :dist, 'active', :plan, :status, :badge, :amount, :expiry, :rank, :badges, :now)",
            [
                ':id' => $id,
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':hash' => $passwordHash,
                ':role' => ROLE_STUDENT,
                ':label' => $levelLabel,
                ':dist' => $district,
                ':plan' => 'BPSC TRE 4.0 ' . $levelLabel . ' Pass',
                ':status' => 'Active (Free Enrolled)',
                ':badge' => 'Active Access',
                ':amount' => 'Free Trial',
                ':expiry' => 'December 31, 2026',
                ':rank' => 'Pending First CBT Mock',
                ':badges' => 'Registered Aspirant,Admit Ready,' . $district . ' Aspirant',
                ':now' => $now
            ]
        );

        $newUser = self::findByIdentifier($id);

        return [
            'success' => true,
            'message' => 'Candidate registration successful! You can now log in with your email or phone and password.',
            'user' => $newUser
        ];
    }

    /**
     * Register a Teacher (Faculty) - Requires Admin Verification
     */
    public static function registerTeacher(array $data): array {
        $name = trim($data['name'] ?? '');
        $email = trim(strtolower($data['email'] ?? ''));
        $phone = trim($data['phone'] ?? '');
        $qualification = trim($data['qualification'] ?? '');
        $subject = trim($data['subject'] ?? 'Mathematics & Science');
        $cadreCode = trim($data['level'] ?? 'mid');
        $district = trim($data['district'] ?? '') ?: 'Patna';
        $password = $data['password'] ?? '';
        $confirmPassword = $data['confirm_password'] ?? '';

        if (empty($name) || empty($email) || empty($phone) || empty($qualification) || empty($password)) {
            return ['success' => false, 'message' => 'Please provide all necessary details for teacher onboarding.'];
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Please enter a valid email address.'];
        }

        if (strlen($password) < 6) {
            return ['success' => false, 'message' => 'Password must be at least 6 characters long.'];
        }

        if ($password !== $confirmPassword) {
            return ['success' => false, 'message' => 'Password and Confirm Password do not match.'];
        }

        if (self::findByIdentifier($email)) {
            return ['success' => false, 'message' => 'This email address is already registered in the portal.'];
        }

        $cadres = self::getCadreLevels();
        $levelLabel = $cadres[$cadreCode] ?? 'Classes 6–8 (Middle)';
        $id = 'USR_FAC_' . strtoupper(bin2hex(random_bytes(3)));
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $now = date('Y-m-d H:i:s');

        Database::execute(
            "INSERT INTO users (id, name, email, phone, password_hash, role, level_label, district, status, qualification, subject, created_at)
             VALUES (:id, :name, :email, :phone, :hash, :role, :label, :dist, 'pending', :qual, :subj, :now)",
            [
                ':id' => $id,
                ':name' => $name,
                ':email' => $email,
                ':phone' => $phone,
                ':hash' => $passwordHash,
                ':role' => ROLE_TEACHER,
                ':label' => $levelLabel,
                ':dist' => $district,
                ':qual' => $qualification,
                ':subj' => $subject,
                ':now' => $now
            ]
        );

        $newTeacher = self::findByIdentifier($id);

        return [
            'success' => true,
            'message' => 'Teacher registration request submitted! Your account is pending Admin approval before login permissions are granted.',
            'user' => $newTeacher
        ];
    }

    /**
     * Get Pending Teacher Approval Requests
     */
    public static function getPendingTeachers(): array {
        $rows = Database::fetchAll("SELECT * FROM users WHERE role = 'teacher' AND status = 'pending' ORDER BY created_at DESC");
        return array_map([self::class, 'formatUserData'], $rows);
    }

    /**
     * Get Approved Active Teachers
     */
    public static function getApprovedTeachers(): array {
        $rows = Database::fetchAll("SELECT * FROM users WHERE role = 'teacher' AND status = 'approved' ORDER BY approved_at DESC, created_at DESC");
        return array_map([self::class, 'formatUserData'], $rows);
    }

    /**
     * Get Registered Active Students / Candidates
     */
    public static function getActiveStudents(): array {
        $rows = Database::fetchAll("SELECT * FROM users WHERE role = 'student' AND status = 'active' ORDER BY created_at DESC");
        return array_map([self::class, 'formatUserData'], $rows);
    }

    /**
     * Admin Action: Approve Teacher
     */
    public static function approveTeacher(string $userId): array {
        $now = date('Y-m-d H:i:s');
        Database::execute(
            "UPDATE users SET status = 'approved', approved_at = :now WHERE id = :id AND role = 'teacher'",
            [':now' => $now, ':id' => $userId]
        );

        $teacher = self::findByIdentifier($userId);
        if ($teacher && ($teacher['status'] ?? '') === 'approved') {
            return [
                'success' => true,
                'message' => "Teacher '{$teacher['name']}' has been APPROVED. Login permission granted.",
                'teacher' => $teacher
            ];
        }

        return ['success' => false, 'message' => 'Teacher account not found.'];
    }

    /**
     * Admin Action: Decline Teacher
     */
    public static function declineTeacher(string $userId): array {
        $now = date('Y-m-d H:i:s');
        Database::execute(
            "UPDATE users SET status = 'declined', approved_at = :now WHERE id = :id AND role = 'teacher'",
            [':now' => $now, ':id' => $userId]
        );

        $teacher = self::findByIdentifier($userId);
        if ($teacher && ($teacher['status'] ?? '') === 'declined') {
            return [
                'success' => true,
                'message' => "Teacher request for '{$teacher['name']}' has been DECLINED. Login permission denied.",
                'teacher' => $teacher
            ];
        }

        return ['success' => false, 'message' => 'Teacher account not found.'];
    }

    /**
     * Admin Action: Remove / Revoke Teacher
     */
    public static function removeTeacher(string $userId): array {
        $teacher = self::findByIdentifier($userId);
        if (!$teacher || ($teacher['role'] ?? '') !== 'teacher') {
            return ['success' => false, 'message' => 'Teacher account not found.'];
        }

        Database::execute("DELETE FROM users WHERE id = :id AND role = 'teacher'", [':id' => $userId]);

        return [
            'success' => true,
            'message' => "Faculty member '{$teacher['name']}' has been removed from the platform."
        ];
    }

    /**
     * Update user merit test performance stats directly in SQL
     */
    public static function updateUserStats(string $userId, array $stats): bool {
        $rank = $stats['achievements']['rank_district'] ?? ($stats['rank_district'] ?? 'Pending First CBT Mock');
        return Database::execute(
            "UPDATE users SET overall_score = :overall, best_score = :best, tests_taken = :tests, avg_accuracy = :acc, rank_district = :rank WHERE id = :id",
            [
                ':overall' => $stats['overall_score'] ?? 0.0,
                ':best' => $stats['best_score'] ?? 0.0,
                ':tests' => (int)($stats['tests_taken'] ?? 0),
                ':acc' => $stats['avg_accuracy'] ?? 0.0,
                ':rank' => $rank,
                ':id' => $userId
            ]
        );
    }
}
