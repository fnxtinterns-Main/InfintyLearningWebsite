<?php
declare(strict_types=1);

namespace App\FutureNxt;

use App\Helper;

final class Auth
{
    public static function check(): bool
    {
        if (class_exists('\\App\\Session')) {
            \App\Session::start();
        } elseif (session_status() === PHP_SESSION_NONE) {
            @ini_set('session.gc_maxlifetime', '2592000');
            @ini_set('session.cookie_lifetime', '2592000');
            session_start();
        }

        if (!empty($_SESSION['fnx_logged_in']) && !empty($_SESSION['fnx_user_id'])) {
            return true;
        }

        // Auto-restore login from persistent cookie so user is never logged out unless they click logout
        if (!empty($_COOKIE['fnx_persistent'])) {
            try {
                $raw = base64_decode((string)$_COOKIE['fnx_persistent'], true);
                if ($raw) {
                    $token = json_decode($raw, true);
                    if (!empty($token['id']) && !empty($token['hash']) && ($token['exp'] ?? 0) > time()) {
                        $db = Database::connect();
                        $stmt = $db->prepare("SELECT * FROM fnx_employees WHERE id = ? LIMIT 1");
                        $stmt->execute([(int)$token['id']]);
                        $emp = $stmt->fetch();
                        if ($emp && substr($emp['password_hash'], 0, 16) === $token['hash'] && !in_array($emp['status'], ['suspended', 'terminated'], true)) {
                            $_SESSION['fnx_logged_in'] = true;
                            $_SESSION['fnx_user_id']   = (int)$emp['id'];
                            $_SESSION['fnx_emp_code']  = $emp['emp_code'];
                            $_SESSION['fnx_email']     = $emp['email'];
                            $_SESSION['fnx_full_name'] = $emp['full_name'];
                            $_SESSION['fnx_role']      = $emp['role'];
                            $_SESSION['fnx_designation']= $emp['designation'];
                            $_SESSION['fnx_photo']     = $emp['photo_path'] ?? null;
                            return true;
                        }
                    }
                }
            } catch (\Throwable $e) {}
        }

        return false;
    }

    public static function user(): ?array
    {
        if (!self::check()) return null;
        $id = (int)$_SESSION['fnx_user_id'];
        $db = Database::connect();
        $stmt = $db->prepare("SELECT * FROM fnx_employees WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        return $user ?: null;
    }

    public static function id(): ?int
    {
        return isset($_SESSION['fnx_user_id']) ? (int)$_SESSION['fnx_user_id'] : null;
    }

    public static function role(): ?string
    {
        return $_SESSION['fnx_role'] ?? null;
    }

    public static function code(): ?string
    {
        return $_SESSION['fnx_emp_code'] ?? null;
    }

    public static function name(): string
    {
        return (string)($_SESSION['fnx_full_name'] ?? 'User');
    }

    public static function isAdminOrExec(): bool
    {
        $role = self::role();
        return in_array($role, ['admin', 'ceo', 'cto'], true);
    }

    public static function isManagerOrAbove(): bool
    {
        $role = self::role();
        if (in_array($role, ['admin', 'ceo', 'cto', 'office_manager', 'manager'], true)) {
            return true;
        }
        $desig = strtolower($_SESSION['fnx_designation'] ?? '');
        return str_contains($desig, 'in-charge') || str_contains($desig, 'incharge');
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            $_SESSION['fnx_redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/futurenxt/dashboard';
            header('Location: /futurenxt/login');
            exit;
        }

        $user = self::user();
        if (!$user || in_array($user['status'], ['suspended', 'terminated'], true)) {
            self::logout();
            header('Location: /futurenxt/login?error=suspended');
            exit;
        }
    }

    public static function requireAdminOrExec(): void
    {
        self::requireLogin();
        if (!self::isAdminOrExec()) {
            http_response_code(403);
            exit('Access Denied: You do not have administrative permissions to view this section.');
        }
    }

    public static function login(string $identity, string $password): array
    {
        $identity = trim($identity);
        if ($identity === '' || $password === '') {
            return ['success' => false, 'error' => 'Please provide your Email/ID and Password.'];
        }

        // Allow alias for admin account (fnxt.admin@gmail.com or fnxt.interns@gmail.com or FNX-ADMIN)
        $lookupEmail = strtolower($identity);
        if ($lookupEmail === 'fnxt.admin@gmail.com') {
            $lookupEmail = 'fnxt.interns@gmail.com';
        }

        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT * FROM fnx_employees 
            WHERE (email = ? OR emp_code = ?) 
            LIMIT 1
        ");
        $stmt->execute([$lookupEmail, strtoupper($identity)]);
        $employee = $stmt->fetch();

        if (!$employee) {
            return ['success' => false, 'error' => 'Invalid credentials. No employee record found.'];
        }

        if (in_array($employee['status'], ['suspended', 'terminated'], true)) {
            return ['success' => false, 'error' => 'Your account is suspended or inactive. Please contact the administrator.'];
        }

        $pwMatches = password_verify($password, $employee['password_hash']);
        // For admin account (id 1 / FNX-ADMIN), accept either master password (Inflearning@99 or Futurenxt@99)
        if (!$pwMatches && (int)$employee['id'] === 1 && in_array($password, ['Inflearning@99', 'Futurenxt@99'], true)) {
            $pwMatches = true;
            $uStmt = $db->prepare("UPDATE fnx_employees SET password_hash = ? WHERE id = 1");
            $uStmt->execute([password_hash($password, PASSWORD_DEFAULT)]);
        }

        if (!$pwMatches) {
            return ['success' => false, 'error' => 'Invalid password. Please check your credentials.'];
        }

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $_SESSION['fnx_logged_in'] = true;
        $_SESSION['fnx_user_id']   = (int)$employee['id'];
        $_SESSION['fnx_emp_code']  = $employee['emp_code'];
        $_SESSION['fnx_email']     = $employee['email'];
        $_SESSION['fnx_full_name'] = $employee['full_name'];
        $_SESSION['fnx_role']      = $employee['role'];
        $_SESSION['fnx_designation']= $employee['designation'];
        $_SESSION['fnx_photo']     = $employee['photo_path'] ?? null;

        // Set persistent cookie for 1 year so user is NEVER automatically logged out
        $tokenData = [
            'id'   => (int)$employee['id'],
            'hash' => substr((string)$employee['password_hash'], 0, 16),
            'exp'  => time() + (365 * 86400)
        ];
        $cookieVal = base64_encode(json_encode($tokenData));
        setcookie('fnx_persistent', $cookieVal, [
            'expires'  => time() + (365 * 86400),
            'path'     => '/',
            'httponly' => true,
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'samesite' => 'Lax'
        ]);

        // Update last login
        try {
            $up = $db->prepare("UPDATE fnx_employees SET last_login_at = NOW() WHERE id = ?");
            $up->execute([(int)$employee['id']]);
        } catch (\Throwable $e) {}

        Audit::log('login', 'employee', (int)$employee['id'], 'User logged into FutureNxt portal', (int)$employee['id']);

        return ['success' => true, 'employee' => $employee];
    }

    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        unset(
            $_SESSION['fnx_logged_in'],
            $_SESSION['fnx_user_id'],
            $_SESSION['fnx_emp_code'],
            $_SESSION['fnx_email'],
            $_SESSION['fnx_full_name'],
            $_SESSION['fnx_role'],
            $_SESSION['fnx_designation'],
            $_SESSION['fnx_photo']
        );

        // Clear persistent cookie
        setcookie('fnx_persistent', '', [
            'expires'  => time() - 86400,
            'path'     => '/',
            'httponly' => true,
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'samesite' => 'Lax'
        ]);
        unset($_COOKIE['fnx_persistent']);
    }
}
