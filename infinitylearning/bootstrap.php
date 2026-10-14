<?php
declare(strict_types=1);

if (!defined('APP_ROOT')) {
    define('APP_ROOT', __DIR__);
}

require_once APP_ROOT . '/config/constants.php';
require_once APP_ROOT . '/src/autoload.php';

if (session_status() === PHP_SESSION_NONE) {
    \App\Session::start();
}
