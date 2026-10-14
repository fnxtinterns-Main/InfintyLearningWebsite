<?php
declare(strict_types=1);

namespace App;

final class Auth
{
    public static function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public static function createUser(array $data): int
    {
        $db = Database::connect();
        $uuid = self::uuid();

        $stmt = $db->prepare("
            INSERT INTO users
                (uuid, email, phone, password_hash, role, school_id, status, phone_verified_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $uuid,
            strtolower(trim($data['email'])),
            trim($data['phone']),
            self::hashPassword($data['password']),
            $data['role'],
            !empty($data['school_id']) ? (int)$data['school_id'] : null,
            $data['status'] ?? STATUS_PENDING,
        ]);

        return (int) $db->lastInsertId();
    }

    public static function findByEmail(string $email): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
        $stmt->execute([strtolower(trim($email))]);
        return $stmt->fetch() ?: null;
    }

    public static function findById(int $id): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM users WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findStudentByUserId(int $userId): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM students WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    public static function findTeacherByUserId(int $userId): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM teachers WHERE user_id = ? LIMIT 1");
        $stmt->execute([$userId]);
        return $stmt->fetch() ?: null;
    }

    public static function login(string $email, string $password): array
    {
        $user = self::findByEmail($email);
        if (!$user) {
            return ['success' => false, 'error' => 'Invalid credentials.'];
        }

        if ($user['status'] === STATUS_SUSPENDED) {
            return ['success' => false, 'error' => 'Account suspended. Contact support.'];
        }
        if ($user['status'] === STATUS_REJECTED) {
            return ['success' => false, 'error' => 'Your account was not approved by the school. Contact the school admin.'];
        }
        if (!self::verifyPassword($password, $user['password_hash'])) {
            return ['success' => false, 'error' => 'Invalid credentials.'];
        }

        if (password_needs_rehash($user['password_hash'], PASSWORD_ARGON2ID)) {
            $db = Database::connect();
            $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?")
               ->execute([self::hashPassword($password), $user['id']]);
        }

        $isPending = ($user['status'] === STATUS_PENDING);

        self::establishSession($user);
        self::updateLastLogin((int)$user['id']);

        return [
            'success' => true,
            'user'    => $user,
            'pending' => $isPending,
        ];
    }

    public static function establishSession(array $user): void
    {
        Session::regenerate();
        $_SESSION['user_id']   = (int) $user['id'];
        $_SESSION['role']      = $user['role'];
        $_SESSION['email']     = $user['email'];
        $_SESSION['school_id'] = $user['school_id'] ?? null;
        $_SESSION['status']    = $user['status'] ?? 'active';
        $_SESSION['logged_in'] = true;
        $_SESSION['login_at']  = time();
    }

    public static function logout(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }

    public static function check(): bool
    {
        return !empty($_SESSION['logged_in']) && !empty($_SESSION['user_id']);
    }

    public static function user(): ?array
    {
        if (!self::check()) return null;
        return self::findById((int) $_SESSION['user_id']);
    }

    public static function id(): ?int
    {
        return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
    }

    public static function role(): ?string
    {
        return $_SESSION['role'] ?? null;
    }

    /** Super admin: role=admin AND school_id IS NULL. */
    public static function isSuperAdmin(): bool
    {
        if (self::role() !== 'admin') return false;
        $u = self::user();
        return empty($u['school_id']);
    }

    /** School admin: role=admin AND school_id is set. */
    public static function isSchoolAdmin(): bool
    {
        if (self::role() !== 'admin') return false;
        $u = self::user();
        return !empty($u['school_id']);
    }

    public static function schoolId(): ?int
    {
        $u = self::user();
        return $u && !empty($u['school_id']) ? (int)$u['school_id'] : null;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            Helper::redirect('/login');
        }
    }

    public static function requireRole(string ...$roles): void
    {
        self::requireLogin();
        if (!in_array(self::role(), $roles, true)) {
            http_response_code(403);
            exit('Forbidden');
        }
    }

    public static function requireTeacher(): array
    {
        self::requireRole('teacher');
        $u = self::user();
        if (!$u) {
            Helper::redirect('/login');
        }
        return $u;
    }

    /** Block everyone except school admins. Redirects super admins to /admin. */
    public static function requireSchoolAdmin(): int
    {
        self::requireLogin();
        if (self::isSchoolAdmin()) {
            return (int) self::schoolId();
        }
        if (self::isSuperAdmin()) {
            Helper::redirect('/admin');
        }
        http_response_code(403);
        exit('Forbidden — school admin access only.');
    }

    /** Block everyone except super admins. Redirects school admins to /school. */
    public static function requireSuperAdmin(): void
    {
        self::requireLogin();
        if (self::isSuperAdmin()) return;
        if (self::isSchoolAdmin()) {
            Helper::redirect('/school');
        }
        http_response_code(403);
        exit('Forbidden — super admin access only.');
    }

    private static function updateLastLogin(int $userId): void
    {
        Database::connect()
            ->prepare("UPDATE users SET last_login_at = NOW() WHERE id = ?")
            ->execute([$userId]);
    }

    public static function uuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80);
        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}