<?php
declare(strict_types=1);

namespace App;

use Throwable;

final class Session
{
    public static function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) return;
        $config = require BASE_PATH . '/config/config.php';

        // Configure Redis for sessions if enabled and extension is present
        $redisConf = $config['redis'] ?? [];
        if (!empty($redisConf['enabled']) && !empty($redisConf['sessions']) && extension_loaded('redis')) {
            self::configureRedisSession($redisConf);
        }

        session_name($config['session']['name'] ?? 'il_session');
        session_set_cookie_params([
            'lifetime' => (int)($config['session']['lifetime'] ?? 7200),
            'path'     => '/',
            'httponly' => true,
            'secure'   => ($_SERVER['HTTPS'] ?? '') === 'on',
            'samesite' => 'Lax',
        ]);

        try {
            @session_start();
        } catch (Throwable $e) {
            error_log('Session start with Redis failed, falling back to files: ' . $e->getMessage());
            ini_set('session.save_handler', 'files');
            session_start();
        }
    }

    private static function configureRedisSession(array $redisConf): void
    {
        try {
            $host = $redisConf['host'] ?? '127.0.0.1';
            $port = (int)($redisConf['port'] ?? 6379);
            $pass = $redisConf['password'] ?? '';
            $timeout = (float)($redisConf['timeout'] ?? 1.5);

            // Fast connection probe before setting session save_handler
            if (!class_exists('Redis')) {
                return;
            }
            $redisClass = 'Redis';
            $test = new $redisClass();
            if (@$test->connect($host, $port, $timeout)) {
                if ($pass !== '') {
                    $test->auth($pass);
                }
                $test->ping();
                $test->close();

                ini_set('session.save_handler', 'redis');
                $endpoint = str_starts_with($host, 'tls://') || str_starts_with($host, 'tcp://')
                    ? "{$host}:{$port}"
                    : "tcp://{$host}:{$port}";
                $savePath = "{$endpoint}?timeout={$timeout}&prefix=il_sess_";
                if ($pass !== '') {
                    $savePath .= "&auth=" . urlencode($pass);
                }
                ini_set('session.save_path', $savePath);
            }
        } catch (Throwable $e) {
            error_log('Redis session setup error, falling back to files: ' . $e->getMessage());
            ini_set('session.save_handler', 'files');
        }
    }

    public static function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public static function flash(string $key, ?string $message = null): ?string
    {
        if ($message !== null) {
            $_SESSION['_flash'][$key] = $message;
            return null;
        }
        $value = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    public static function csrfToken(): string
    {
        if (empty($_SESSION['_csrf'])) {
            $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['_csrf'];
    }

    public static function verifyCsrf(?string $token): bool
    {
        return !empty($_SESSION['_csrf']) && is_string($token) && hash_equals($_SESSION['_csrf'], $token);
    }
}