<?php
/**
 * Infinity Learning — Database Configuration
 * Configured for MySQL (Main Website Database: u999011396_inflearning)
 */

require_once __DIR__ . '/config.php';

// Auto-detect environment from main app if available
$envFile = dirname(__DIR__, 2) . '/.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) continue;
        if (str_contains($line, '=')) {
            [$k, $v] = explode('=', $line, 2);
            $k = trim($k);
            $v = trim($v, " \t\n\r\0\x0B\"'");
            if (!getenv($k)) {
                putenv("$k=$v");
            }
        }
    }
}

// Database Driver: 'mysql' for production website
if (!defined('DB_DRIVER')) {
    define('DB_DRIVER', getenv('DB_DRIVER') ?: 'mysql');
}

// MySQL Configuration matching u999011396_inflearning
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
if (!defined('DB_PORT')) define('DB_PORT', getenv('DB_PORT') ?: '3306');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'u999011396_inflearning');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'u999011396_Inflearning');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: 'Infinitylearning@123');

require_once __DIR__ . '/../src/Database.php';
