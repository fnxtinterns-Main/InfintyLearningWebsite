<?php
declare(strict_types=1);

namespace App;

use PDO;
use PDOException;
use Throwable;

final class Database
{
    private static ?PDO $writerInstance = null;
    private static ?PDO $readerInstance = null;
    private static bool $shutdownRegistered = false;

    /**
     * Default connection is the Primary (Writer) connection.
     * Backwards-compatible with all existing Database::connect() calls.
     */
    public static function connect(): PDO
    {
        return self::write();
    }

    /**
     * Get the Primary / Writer PDO instance.
     * Use for INSERT, UPDATE, DELETE, transactions, or critical consistency reads.
     */
    public static function write(): PDO
    {
        if (self::$writerInstance === null) {
            $config = require BASE_PATH . '/config/config.php';
            $dbConf = $config['db'];

            self::$writerInstance = self::createConnection($dbConf);

            if (!self::$shutdownRegistered) {
                self::$shutdownRegistered = true;
                register_shutdown_function([self::class, 'shutdownRollback']);
            }
        }
        return self::$writerInstance;
    }

    /**
     * Get a Read Replica PDO instance.
     * Use for SELECT queries across lessons, feeds, timetable, leaderboards.
     * Automatically falls back to Writer if no replica is defined or if replica is unreachable.
     */
    public static function read(): PDO
    {
        if (self::$readerInstance !== null) {
            return self::$readerInstance;
        }

        $config = require BASE_PATH . '/config/config.php';
        $readConf = $config['db']['read'] ?? null;

        // If no read replicas configured, use the Primary (Writer)
        if (empty($readConf)) {
            return self::write();
        }

        // Support array of multiple read replicas (random load-balancing)
        $selectedConf = isset($readConf[0]) ? $readConf[array_rand($readConf)] : $readConf;

        // Inherit default db settings from master if omitted
        $masterConf = $config['db'];
        $mergedConf = array_merge([
            'host'         => $masterConf['host'],
            'port'         => $masterConf['port'],
            'name'         => $masterConf['name'],
            'user'         => $masterConf['user'],
            'pass'         => $masterConf['pass'],
            'charset'      => $masterConf['charset'] ?? 'utf8mb4',
            'lock_timeout' => $masterConf['lock_timeout'] ?? 5,
        ], $selectedConf);

        try {
            self::$readerInstance = self::createConnection($mergedConf);
            return self::$readerInstance;
        } catch (Throwable $e) {
            error_log('Read replica connection failed, falling back to Primary: ' . $e->getMessage());
            return self::write();
        }
    }

    /**
     * Factory method to create a properly tuned PDO connection.
     */
    private static function createConnection(array $db): PDO
    {
        $port = $db['port'] ?? '3306';
        $dsn = "mysql:host={$db['host']};port={$port};dbname={$db['name']}"
             . ";charset={$db['charset']};collation=utf8mb4_unicode_ci";

        $timeout = (int)($db['lock_timeout'] ?? 5);

        try {
            return new PDO($dsn, $db['user'], $db['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_TIMEOUT            => $timeout,
                PDO::MYSQL_ATTR_INIT_COMMAND =>
                    "SET SESSION time_zone = '+05:30', " .
                    "SESSION innodb_lock_wait_timeout = $timeout, " .
                    "SESSION lock_wait_timeout = $timeout, " .
                    "SESSION sql_mode = 'STRICT_TRANS_TABLES,NO_ENGINE_SUBSTITUTION', " .
                    "SESSION collation_connection = 'utf8mb4_unicode_ci', " .
                    "SESSION character_set_client = 'utf8mb4', " .
                    "SESSION character_set_connection = 'utf8mb4', " .
                    "SESSION character_set_results = 'utf8mb4'",
            ]);
        } catch (PDOException $e) {
            error_log('DB connection failed (' . ($db['host'] ?? 'localhost') . '): ' . $e->getMessage());
            throw new \RuntimeException('Database connection failed: ' . $e->getMessage());
        }
    }

    public static function shutdownRollback(): void
    {
        if (self::$writerInstance && self::$writerInstance->inTransaction()) {
            try {
                self::$writerInstance->rollBack();
            } catch (Throwable $e) {
                error_log('Shutdown rollback failed: ' . $e->getMessage());
            }
        }
    }
}