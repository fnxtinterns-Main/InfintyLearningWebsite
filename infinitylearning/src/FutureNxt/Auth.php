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
                    if (!empty($token['id']) && ($token['exp'] ?? 0) > time()) {
                        $db = Database::connect();
                        $stmt = $db->prepare("SELECT * FROM fnx_employees WHERE id = ? LIMIT 1");
                        $stmt->execute([(int)$token['id']]);
                        $emp = $stmt->fetch();
                        if ($emp && !in_array($emp['status'], ['suspended', 'terminated'], true)) {
                            $expectedSig = hash_hmac('sha256', (string)$emp['id'] . '|' . strtolower((string)$emp['email']), 'FutureNxtPersistentKey#2026');
                            $sigMatch = !empty($token['sig']) && hash_equals($expectedSig, (string)$token['sig']);
                            $hashMatch = !empty($token['hash']) && substr($emp['password_hash'], 0, 16) === $token['hash'];
                            if ($sigMatch || $hashMatch) {
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
                }
            } catch (\Throwable $e) {}
        }

        return false;
    }

    public static function user(): ?array
    {
        if (!self::check()) return null;
        $id = (int)$_SESSION['fnx_user_id'];
        try {
            $db = Database::connect();
            $stmt = $db->prepare("SELECT * FROM fnx_employees WHERE id = ? LIMIT 1");
            $stmt->execute([$id]);
            $user = $stmt->fetch();
            if ($user) return $user;
        } catch (\Throwable $e) {}

        // Fallback to active session values if DB connection temporarily drops
        return [
            'id'          => $id,
            'emp_code'    => $_SESSION['fnx_emp_code'] ?? 'FNX',
            'email'       => $_SESSION['fnx_email'] ?? '',
            'full_name'   => $_SESSION['fnx_full_name'] ?? 'User',
            'role'        => $_SESSION['fnx_role'] ?? 'employee',
            'designation' => $_SESSION['fnx_designation'] ?? 'Partner',
            'status'      => 'active',
            'photo_path'  => $_SESSION['fnx_photo'] ?? null,
        ];
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
            if ((!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_POST['ajax']) || isset($_GET['ajax'])) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(401);
                echo json_encode(['success' => false, 'error' => 'Your session has expired. Please refresh the page and sign in again.', 'session_expired' => true]);
                exit;
            }
            $_SESSION['fnx_redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? '/futurenxt/dashboard';
            header('Location: /futurenxt/login');
            exit;
        }

        $user = self::user();
        if ($user && in_array($user['status'], ['suspended', 'terminated'], true)) {
            self::logout();
            if ((!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_POST['ajax']) || isset($_GET['ajax'])) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Account access suspended or terminated.', 'suspended' => true]);
                exit;
            }
            header('Location: /futurenxt/login?error=suspended');
            exit;
        }
    }

    public static function requireAdminOrExec(): void
    {
        self::requireLogin();
        if (!self::isAdminOrExec()) {
            if ((!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') || isset($_POST['ajax']) || isset($_GET['ajax'])) {
                header('Content-Type: application/json; charset=utf-8');
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Access Denied: You do not have administrative permissions to perform this action.']);
                exit;
            }
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

        if ($employee['status'] === 'suspended') {
            return ['success' => false, 'error' => 'Your account has been suspended by administration. Access to the portal is temporarily disabled. Please contact HR.'];
        }

        if ($employee['status'] === 'terminated') {
            return ['success' => false, 'error' => 'Your employment has been terminated. Portal access has been permanently revoked.'];
        }

        if ($employee['status'] === 'resigned' && !empty($employee['notice_end_date']) && strtotime($employee['notice_end_date']) < strtotime(date('Y-m-d'))) {
            return ['success' => false, 'error' => 'Your 15-day notice period has concluded. Thank you for your service with FutureNxt.'];
        }

        $pwMatches = password_verify($password, $employee['password_hash']);
        // For admin account (id 1 / FNX-ADMIN), accept master passwords (Inflearning@99, Futurenxt@99, Futurenxt@2026)
        if (!$pwMatches && ((int)$employee['id'] === 1 || $employee['emp_code'] === 'FNX-ADMIN') && in_array($password, ['Inflearning@99', 'Futurenxt@99', 'Futurenxt@2026'], true)) {
            $pwMatches = true;
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $uStmt = $db->prepare("UPDATE fnx_employees SET password_hash = ? WHERE id = ?");
            $uStmt->execute([$newHash, $employee['id']]);
            $employee['password_hash'] = $newHash;
        }

        // For all partners, accept master company fallback Futurenxt@2026
        if (!$pwMatches && $password === 'Futurenxt@2026') {
            $pwMatches = true;
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            $uStmt = $db->prepare("UPDATE fnx_employees SET password_hash = ? WHERE id = ?");
            $uStmt->execute([$newHash, $employee['id']]);
            $employee['password_hash'] = $newHash;
        }

        if (!$pwMatches) {
            return ['success' => false, 'error' => 'Invalid password. Please check your credentials.'];
        }

        if (class_exists('\\App\\Session')) {
            \App\Session::start();
        } elseif (session_status() === PHP_SESSION_NONE) {
            @ini_set('session.gc_maxlifetime', '2592000');
            @ini_set('session.cookie_lifetime', '2592000');
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

        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
                    || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

        // Set persistent HMAC token for 1 year so user is NEVER automatically logged out
        $sig = hash_hmac('sha256', (string)$employee['id'] . '|' . strtolower((string)$employee['email']), 'FutureNxtPersistentKey#2026');
        $tokenData = [
            'id'   => (int)$employee['id'],
            'sig'  => $sig,
            'hash' => substr((string)$employee['password_hash'], 0, 16),
            'exp'  => time() + (365 * 86400)
        ];
        $cookieVal = base64_encode(json_encode($tokenData));
        setcookie('fnx_persistent', $cookieVal, [
            'expires'  => time() + (365 * 86400),
            'path'     => '/',
            'httponly' => true,
            'secure'   => $isSecure,
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
        if (class_exists('\\App\\Session')) {
            \App\Session::start();
        } elseif (session_status() === PHP_SESSION_NONE) {
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

        $isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https')
                    || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);

        // Clear persistent cookie
        setcookie('fnx_persistent', '', [
            'expires'  => time() - 86400,
            'path'     => '/',
            'httponly' => true,
            'secure'   => $isSecure,
            'samesite' => 'Lax'
        ]);
        unset($_COOKIE['fnx_persistent']);
    }
}
