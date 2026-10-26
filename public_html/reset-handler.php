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
use App\{Session, Auth, Helper, Validator, Database, Audit};

Session::start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helper::redirect('/login');
if (!Session::verifyCsrf($_POST['csrf'] ?? null)) { Session::flash('error','Invalid request.'); Helper::redirect('/login'); }

$token = $_POST['token'] ?? '';
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirm  = $_POST['password_confirm'] ?? '';

$v = new Validator();
$v->required('password', $password, 'Password')->password('password', $password)->matches('password_confirm', $confirm, $password);
if ($v->fails()) { Session::flash('error', implode(' ', $v->errors())); Helper::redirect('/reset?token=' . urlencode($token) . '&email=' . urlencode($email)); }

$user = Auth::findByEmail($email);
if (!$user) { Session::flash('error','Invalid or expired reset link.'); Helper::redirect('/login'); }

$hash = hash('sha256', $token);
$db = Database::connect();
$stmt = $db->prepare("SELECT id FROM password_resets WHERE user_id=? AND token_hash=? AND consumed_at IS NULL AND expires_at > NOW() LIMIT 1");
$stmt->execute([$user['id'], $hash]);
$row = $stmt->fetch();
if (!$row) { Session::flash('error','Invalid or expired reset link.'); Helper::redirect('/login'); }

$db->beginTransaction();
try {
    $db->prepare("UPDATE users SET password_hash = ? WHERE id = ?")->execute([Auth::hashPassword($password), $user['id']]);
    $db->prepare("UPDATE password_resets SET consumed_at = NOW() WHERE id = ?")->execute([$row['id']]);
    $db->commit();
    Audit::log('password.reset_completed', 'user', (int)$user['id']);
    Session::flash('success','Password updated. Please log in.');
    Helper::redirect('/login');
} catch (\Throwable $e) {
    $db->rollBack();
    Session::flash('error','Failed to reset password.');
    Helper::redirect('/login');
}