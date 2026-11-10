<?php
/**
 * Infinity Learning - Authentication Controller
 * Manages user authentication, credential verification, and role session management
 */

if (!defined('BPSC_PORTAL')) {
    define('BPSC_PORTAL', true);
}

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/constants.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/UserManager.php';

class Auth {
    /**
     * Authenticate user using Email/Identifier & Password
     */
    public static function attempt(string $identifier, string $password, string $expectedRole = ROLE_STUDENT): array {
        $identifier = trim($identifier);

        if (empty($identifier) || empty($password)) {
            return ['success' => false, 'message' => 'Please provide both your registered identifier and security key.'];
        }

        // Authenticate against registered users in SQL database
        $user = UserManager::findByIdentifier($identifier);
        if ($user) {
            $isPasswordValid = false;

            if (!empty($user['password_hash']) && password_verify($password, $user['password_hash'])) {
                $isPasswordValid = true;
            }

            if ($isPasswordValid) {
                $actualRole   = strtolower(trim((string)($user['role'] ?? ROLE_STUDENT)));
                $expectedRole = strtolower(trim((string)($expectedRole ?: ROLE_STUDENT)));

                // STRICT ROLE ENFORCEMENT:
                // Only teacher can login to teacher, student to student, and admin to admin.
                if ($actualRole !== $expectedRole) {
                    $roleDisplayNames = [
                        ROLE_STUDENT => 'Student / Candidate',
                        ROLE_TEACHER => 'Faculty / Teacher',
                        ROLE_ADMIN   => 'Platform Administrator'
                    ];
                    $actualLabel   = $roleDisplayNames[$actualRole] ?? ucfirst($actualRole);
                    $expectedLabel = $roleDisplayNames[$expectedRole] ?? ucfirst($expectedRole);

                    return [
                        'success' => false,
                        'message' => "Access Denied: Your account is registered as {$actualLabel}. You cannot log into the {$expectedLabel} portal."
                    ];
                }

                $targetRole = $actualRole;

                // Strict Gate for Teachers: Check Approval Status
                if (($user['role'] ?? '') === ROLE_TEACHER || $targetRole === ROLE_TEACHER) {
                    $status = strtolower($user['status'] ?? 'pending');
                    if ($status === 'pending') {
                        return [
                            'success' => false,
                            'approval_pending' => true,
                            'message' => 'Your teacher registration is currently PENDING Admin approval. Please wait for the administrator to verify and approve your account before logging in.'
                        ];
                    }
                    if ($status === 'declined') {
                        return [
                            'success' => false,
                            'approval_declined' => true,
                            'message' => 'Your faculty onboarding application has been DECLINED by the administrator. Access to the Faculty Terminal is not permitted.'
                        ];
                    }
                    if ($status === 'removed' || $status === 'suspended') {
                        return [
                            'success' => false,
                            'message' => 'Your faculty account access has been revoked or removed by the platform administrator.'
                        ];
                    }
                }

                // If candidate is suspended
                if (($user['role'] ?? '') === ROLE_STUDENT && ($user['status'] ?? '') === 'suspended') {
                    return ['success' => false, 'message' => 'Your candidate account has been suspended. Please contact portal support.'];
                }

                $user = UserManager::formatUserData($user);

                // Upgrade to Argon2id and persist salt if rehash is needed
                if (password_needs_rehash($user['password_hash'], PASSWORD_ARGON2ID) || empty($user['password_salt'])) {
                    $newHash = password_hash($password, PASSWORD_ARGON2ID);
                    $pwParts = explode('$', $newHash);
                    $newSalt = $pwParts[4] ?? '';
                    try {
                        Database::execute(
                            "UPDATE users SET password_hash = :hash, password_salt = :salt WHERE id = :id",
                            [':hash' => $newHash, ':salt' => $newSalt, ':id' => $user['id']]
                        );
                        $user['password_hash'] = $newHash;
                        $user['password_salt'] = $newSalt;
                    } catch (\Throwable $e) {}
                }

                $authData = [
                    'id' => $user['id'],
                    'user_id' => $user['id'],
                    'name' => $user['name'],
                    'email' => $user['email'],
                    'phone' => $user['phone'] ?? '',
                    'role' => $targetRole,
                    'level_label' => $user['level_label'] ?? 'Classes 6–8',
                    'district' => $user['district'] ?? 'Patna',
                    'badge' => self::getBadgeForRole($targetRole),
                    'subLabel' => $user['name'] . ' · ' . ($user['level_label'] ?? ucfirst($targetRole) . ' Terminal'),
                    'subscription' => $user['subscription'],
                    'achievements' => $user['achievements'],
                    'auth_source' => 'registered_account'
                ];

                self::setSession($authData);
                return [
                    'success' => true,
                    'user' => $authData,
                    'message' => 'Welcome ' . htmlspecialchars($user['name']) . '! Logged in as ' . $authData['badge']
                ];
            } else {
                return ['success' => false, 'message' => 'Incorrect password entered. Please try again.'];
            }
        }

        return ['success' => false, 'message' => 'No active registered account found matching these credentials. Please check your details or register a new account.'];
    }

    public static function getBadgeForRole(string $role): string {
        return match ($role) {
            ROLE_ADMIN => 'AIVERSE Admin Central',
            ROLE_TEACHER => 'Faculty Terminal',
            default => 'Candidate Terminal'
        };
    }

    public static function setSession(array $user): void {
        $_SESSION['bpsc_user'] = $user;
        $_SESSION['bpsc_logged_in'] = true;
    }

    public static function check(): bool {
        return !empty($_SESSION['bpsc_logged_in']) && !empty($_SESSION['bpsc_user']);
    }

    public static function user(): ?array {
        return $_SESSION['bpsc_user'] ?? null;
    }

    public static function logout(): void {
        unset($_SESSION['bpsc_user']);
        unset($_SESSION['bpsc_logged_in']);
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }
}
