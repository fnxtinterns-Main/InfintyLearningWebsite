<?php
declare(strict_types=1);
define('APP_ROOT', __DIR__ . '/../infinitylearning');

require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';
use App\{Session, Auth, Audit, Helper};

Session::start();
$userId = $_SESSION['user_id'] ?? null;
if ($userId) Audit::log('user.logout', 'user', (int)$userId);
Auth::logout();
Helper::redirect('/login');