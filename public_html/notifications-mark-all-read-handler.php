<?php
declare(strict_types=1);
define('APP_ROOT', __DIR__ . '/../infinitylearning');

require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

use App\{Session, Auth, Notification, Helper};

Session::start();
Auth::requireLogin();

if (!Session::verifyCsrf($_POST['csrf'] ?? null)) {
    Session::flash('error', 'Invalid request.');
    Helper::redirect($_SERVER['HTTP_REFERER'] ?? '/dashboard');
}

Notification::markAllRead((int)Auth::user()['id']);
Helper::redirect($_SERVER['HTTP_REFERER'] ?? '/dashboard');