<?php
declare(strict_types=1);

// Load Composer's autoloader FIRST so PHPMailer, FPDF, FPDI etc. are available.
$composerAutoload = BASE_PATH . '/vendor/autoload.php';
if (is_file($composerAutoload)) {
    require_once $composerAutoload;
}

// Custom autoloader for App\* classes (your application code).
spl_autoload_register(function (string $class): void {
    $prefix = 'App\\';
    if (strncmp($class, $prefix, strlen($prefix)) !== 0) return;

    $relative = substr($class, strlen($prefix));
    $file = APP_PATH . '/' . str_replace('\\', '/', $relative) . '.php';

    if (file_exists($file)) require $file;
});

require_once APP_PATH . '/Env.php';
\App\Env::load();
require_once APP_PATH . '/Helper.php';