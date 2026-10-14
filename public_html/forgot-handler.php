<?php
declare(strict_types=1);
define('APP_ROOT', __DIR__ . '/../infinitylearning');

require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, Helper, RateLimiter, Database, Audit};

Session::start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helper::redirect('/forgot');
if (!Session::verifyCsrf($_POST['csrf'] ?? null)) { Session::flash('error','Invalid request.'); Helper::redirect('/forgot'); }

$email = trim($_POST['email'] ?? '');
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
if (!RateLimiter::hit($ip, 'forgot', 5, 3600)) { Session::flash('error','Too many requests.'); Helper::redirect('/forgot'); }

$user = Auth::findByEmail($email);
if ($user) {
    $token = bin2hex(random_bytes(32));
    $hash  = hash('sha256', $token);
    $db = Database::connect();
    $db->prepare("DELETE FROM password_resets WHERE user_id = ?")->execute([$user['id']]);
    $db->prepare("INSERT INTO password_resets (user_id, token_hash, expires_at) VALUES (?, ?, DATE_ADD(NOW(), INTERVAL 1 HOUR))")
       ->execute([$user['id'], $hash]);

    // Build the reset URL from the trusted production base URL
    $config  = require APP_ROOT . '/config/config.php';
    $baseUrl = rtrim($config['app']['url'] ?? '', '/');
    $link    = $baseUrl . '/reset?token=' . urlencode($token) . '&email=' . urlencode($email);

    // Send the reset link by email
    \App\Mailer::send(
        $user['email'],
        'Reset your Infinity Learning password',
        \App\Mailer::passwordResetEmail($user['email'], $link)
    );

    // Never log the token or the link — only the fact that a reset was requested
    Audit::log('password.reset_requested', 'user', (int)$user['id']);
}
Session::flash('success', 'If an account exists with that email, a reset link has been sent.');
Helper::redirect('/login');