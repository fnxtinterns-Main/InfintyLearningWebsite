<?php
/**
 * Infinity Learning - SQL Database Connection & Migration Engine
 * Powered by PDO SQL (MySQL primary with isolated bpsc_ tables)
 */

if (!defined('BPSC_PORTAL')) {
    define('BPSC_PORTAL', true);
}

class Database {
    private static ?PDO $pdo = null;

    public static function isMysql(): bool {
        return (defined('DB_DRIVER') ? strtolower(DB_DRIVER) : 'mysql') === 'mysql';
    }

    /**
     * Get active PDO database connection with auto-initialization
     */
    public static function getConnection(): PDO {
        if (self::$pdo === null) {
            $driver = defined('DB_DRIVER') ? strtolower(DB_DRIVER) : 'mysql';

            if ($driver === 'mysql') {
                $host = defined('DB_HOST') ? DB_HOST : '127.0.0.1';
                $port = defined('DB_PORT') ? DB_PORT : '3306';
                $dbname = defined('DB_NAME') ? DB_NAME : 'u999011396_inflearning';
                $user = defined('DB_USER') ? DB_USER : 'u999011396_Inflearning';
                $pass = defined('DB_PASS') ? DB_PASS : 'Infinitylearning@123';

                $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset=utf8mb4";

                self::$pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]);
            } else {
                $dbFile = __DIR__ . '/../database/bpsc_portal.sqlite';
                self::$pdo = new PDO('sqlite:' . $dbFile, null, null, [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false
                ]);
                self::$pdo->exec('PRAGMA foreign_keys = ON; PRAGMA journal_mode = WAL;');
            }
        }
        return self::$pdo;
    }

    /**
     * Rewrite and execute a prepared SQL query
     */
    public static function query(string $sql, array $params = []): PDOStatement {
        // Rewrite queries for MySQL compatibility and table isolation
        if (self::isMysql()) {
            // Replace SQLite INSERT OR IGNORE with MySQL INSERT IGNORE
            $sql = preg_replace('/INSERT\s+OR\s+IGNORE\s+INTO/i', 'INSERT IGNORE INTO', $sql);
            // Prefix isolated tables if not already prefixed
            $sql = preg_replace('/(?<!bpsc_)\b(users|question_papers|notes|test_results|question_bank)\b/i', 'bpsc_$1', $sql);
        }

        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch all matching rows
     */
    public static function fetchAll(string $sql, array $params = []): array {
        return self::query($sql, $params)->fetchAll();
    }

    /**
     * Fetch a single row or null
     */
    public static function fetchOne(string $sql, array $params = []): ?array {
        $row = self::query($sql, $params)->fetch();
        return $row ?: null;
    }

    /**
     * Execute an INSERT/UPDATE/DELETE query
     */
    public static function execute(string $sql, array $params = []): bool {
        return self::query($sql, $params)->rowCount() >= 0;
    }
}
