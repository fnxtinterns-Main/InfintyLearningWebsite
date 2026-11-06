<?php
declare(strict_types=1);

namespace App\FutureNxt;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $instance = null;

    public static function connect(): PDO
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        $host = getenv('FNX_DB_HOST') ?: '127.0.0.1';
        $port = (int)(getenv('FNX_DB_PORT') ?: 3306);
        $dbname = getenv('FNX_DB_NAME') ?: 'u999011396_futurenxt';
        $user = getenv('FNX_DB_USER') ?: 'u999011396_fnxt_portal';
        $pass = getenv('FNX_DB_PASS') ?: 'Inflearning@99';

        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            self::$instance = new PDO($dsn, $user, $pass, $options);
            return self::$instance;
        } catch (PDOException $e) {
            // Fallback: in case root access or main DB host is used
            error_log('FutureNxt DB connection error: ' . $e->getMessage());
            throw new \RuntimeException('FutureNxt Database connection failed: ' . $e->getMessage());
        }
    }
}
