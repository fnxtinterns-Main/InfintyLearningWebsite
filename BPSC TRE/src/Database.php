<?php
/**
 * Infinity Learning - SQL Database Connection & Migration Engine
 * Powered by PDO SQL (SQLite / MySQL) with prepared statements
 */

if (!defined('BPSC_PORTAL')) {
    define('BPSC_PORTAL', true);
}

class Database {
    private static ?PDO $pdo = null;
    private static string $dbFile = __DIR__ . '/../database/bpsc_portal.sqlite';
    private static string $schemaFile = __DIR__ . '/../database/schema.sql';

    /**
     * Get active PDO database connection with auto-initialization
     */
    public static function getConnection(): PDO {
        if (self::$pdo === null) {
            $driver = defined('DB_DRIVER') ? strtolower(DB_DRIVER) : 'sqlite';

            if ($driver === 'mysql') {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                    defined('DB_HOST') ? DB_HOST : '127.0.0.1',
                    defined('DB_PORT') ? DB_PORT : '3306',
                    defined('DB_NAME') ? DB_NAME : 'infinity_bpsc'
                );
                $user = defined('DB_USER') ? DB_USER : 'root';
                $pass = defined('DB_PASS') ? DB_PASS : '';

                self::$pdo = new PDO($dsn, $user, $pass, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]);
            } else {
                $isNew = !file_exists(self::$dbFile);
                
                self::$pdo = new PDO('sqlite:' . self::$dbFile, null, null, [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]);

                self::$pdo->exec('PRAGMA foreign_keys = ON; PRAGMA journal_mode = WAL;');

                if ($isNew || filesize(self::$dbFile) === 0) {
                    self::migrateSchema();
                }
            }
        }
        return self::$pdo;
    }

    /**
     * Execute SQL migration from database/schema.sql
     */
    public static function migrateSchema(): void {
        if (file_exists(self::$schemaFile)) {
            $sql = file_get_contents(self::$schemaFile);
            if (!empty($sql)) {
                self::getConnection()->exec($sql);
            }
        }
    }

    /**
     * Execute a prepared SQL query and return the statement
     */
    public static function query(string $sql, array $params = []): PDOStatement {
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

