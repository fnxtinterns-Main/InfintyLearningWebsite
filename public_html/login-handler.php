<?php
declare(strict_types=1);
define('APP_ROOT', __DIR__ . '/../infinitylearning');

require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';
use App\{Session, Auth, Helper, RateLimiter, Audit, Validator};

Session::start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') Helper::redirect('/login');
if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request.');
    Helper::redirect('/login');
}

$email    = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';

$v = new Validator();
$v->required('email', $email, 'Email')->email('email', $email)
  ->required('password', $password, 'Password');
if ($v->fails()) {
    Session::flash('error', implode(' ', $v->errors()));
    Helper::redirect('/login');
}

$ip  = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$key = strtolower($email) . '|' . $ip;
if (!RateLimiter::hit($key, 'login', 5, 900)) {
    Session::flash('error', 'Too many failed attempts. Try again in 15 minutes.');
    Helper::redirect('/login');
}

$result = Auth::login($email, $password);

if (!$result['success']) {
    Session::flash('error', $result['error']);
    Helper::flashOld(['email' => $email]);
    Helper::redirect('/login');
}

RateLimiter::clear($key, 'login');
Audit::log('user.login', 'user', (int)$result['user']['id']);

// Route: admin (with no school) → /admin
//        school admin → /school
//        pending student/teacher → /pending-approval
//        everyone else → /dashboard
if (!empty($result['pending'])) {
    Helper::redirect('/pending-approval');
}

$role = $result['user']['role'];
$schoolId = $result['user']['school_id'] ?? null;

if ($role === 'admin') {
    Helper::redirect($schoolId ? '/school' : '/admin');
}

Helper::redirect('/dashboard');