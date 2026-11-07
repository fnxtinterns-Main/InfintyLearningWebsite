<?php
/**
 * Infinity Learning — Database Configuration
 * Configured for SQLite (Local Development) & MySQL (Main Website Database)
 */

require_once __DIR__ . '/config.php';

// Database Driver: 'sqlite' (Development) or 'mysql' (Main Website Database)
if (!defined('DB_DRIVER')) {
    define('DB_DRIVER', getenv('DB_DRIVER') ?: 'sqlite');
}

// MySQL Configuration (Fill these when connecting to your main website database)
if (!defined('DB_HOST')) define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
if (!defined('DB_PORT')) define('DB_PORT', getenv('DB_PORT') ?: '3306');
if (!defined('DB_NAME')) define('DB_NAME', getenv('DB_NAME') ?: 'infinity_bpsc');
if (!defined('DB_USER')) define('DB_USER', getenv('DB_USER') ?: 'root');
if (!defined('DB_PASS')) define('DB_PASS', getenv('DB_PASS') ?: '');

require_once __DIR__ . '/../src/Database.php';
