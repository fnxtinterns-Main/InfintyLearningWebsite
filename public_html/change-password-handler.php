<?php
declare(strict_types=1);
if (!defined('APP_ROOT')) {
    $appRoot = is_dir(__DIR__ . '/../infinitylearning')
        ? __DIR__ . '/../infinitylearning'
        : (is_dir(__DIR__ . '/infinitylearning') ? __DIR__ . '/infinitylearning' : dirname(__DIR__) . '/infinitylearning');
    define('APP_ROOT', $appRoot);
}

if (!file_exists(APP_ROOT . '/config/constants.php') || !file_exists(APP_ROOT . '/src/autoload.php')) {
    http_response_code(503);
    if (file_exists(__DIR__ . '/maintenance.html')) {
        readfile(__DIR__ . '/maintenance.html');
    } else {
        echo 'Maintenance Break. Please check back shortly.';
    }
    exit;
}

require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, Helper, Validator, RateLimiter, Audit, Notification};

Session::start();
Auth::requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Helper::redirect('/profile');
}

if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request.');
    Helper::redirect('/profile');
}

$me = Auth::user();

// Rate limit: 5 attempts per hour per user
if (!RateLimiter::hit('user:' . $me['id'], 'password_change', 5, 3600)) {
    Session::flash('error', 'Too many password change attempts. Try again later.');
    Helper::redirect('/profile');
}

$current = $_POST['current_password'] ?? '';
$new     = $_POST['new_password'] ?? '';
$confirm = $_POST['new_password_confirm'] ?? '';

// ---- Validate ----
$v = new Validator();
$v->required('current_password', $current, 'Current password')
  ->required('new_password', $new, 'New password')
  ->password('new_password', $new)
  ->matches('new_password_confirm', $confirm, $new);

if ($v->fails()) {
    Session::flash('error', implode(' ', $v->errors()));
    Helper::redirect('/profile');
}

// ---- Verify the current password ----
if (!Auth::verifyPassword($current, $me['password_hash'])) {
    Audit::log('password.change_failed', 'user', (int)$me['id'], ['reason' => 'wrong_current']);
    Session::flash('error', 'Current password is incorrect.');
    Helper::redirect('/profile');
}

// ---- Prevent reuse of the same password ----
if (Auth::verifyPassword($new, $me['password_hash'])) {
    Session::flash('error', 'New password must be different from your current password.');
    Helper::redirect('/profile');
}

// ---- Update ----
try {
    $db = \App\Database::connect();
    $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?")
       ->execute([Auth::hashPassword($new), (int)$me['id']]);

    // Kill any other sessions (if you track them)
    $db->prepare("DELETE FROM sessions WHERE user_id = ?")->execute([(int)$me['id']]);

    // Regenerate our own session id to invalidate any prior cookies
    Session::regenerate();

    // Notification + audit
    Notification::push(
        (int)$me['id'],
        'password.changed',
        'Your password was changed',
        'If this wasn\'t you, reset it immediately and contact support.',
        '/profile'
    );
    Audit::log('password.changed', 'user', (int)$me['id']);

    RateLimiter::clear('user:' . $me['id'], 'password_change');

    Session::flash('success', 'Your password has been updated successfully.');
    Helper::redirect('/profile');

} catch (\Throwable $e) {
    error_log('Password change failed: ' . $e->getMessage());
    Session::flash('error', 'Could not update password. Please try again.');
    Helper::redirect('/profile');
}