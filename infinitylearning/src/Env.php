<?php
declare(strict_types=1);

namespace App;

final class Env
{
    private static bool $loaded = false;

    /**
     * Load environment variables from a .env file into putenv, $_ENV, and $_SERVER.
     */
    public static function load(?string $dir = null): void
    {
        if (self::$loaded) {
            return;
        }

        $candidates = array_filter([
            $dir ? rtrim($dir, '/\\') . '/.env' : null,
            defined('BASE_PATH') ? BASE_PATH . '/.env' : null,
            defined('BASE_PATH') ? dirname(BASE_PATH) . '/.env' : null,
            defined('BASE_PATH') ? dirname(dirname(BASE_PATH)) . '/.env' : null,
            dirname(__DIR__) . '/.env',
            dirname(dirname(__DIR__)) . '/.env',
            !empty($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/.env' : null,
            !empty($_SERVER['DOCUMENT_ROOT']) ? rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . '/infinitylearning/.env' : null,
            !empty($_SERVER['DOCUMENT_ROOT']) ? dirname(rtrim($_SERVER['DOCUMENT_ROOT'], '/\\')) . '/.env' : null,
        ]);

        $filePath = null;
        foreach ($candidates as $candidate) {
            if (is_file($candidate)) {
                $filePath = $candidate;
                break;
            }
        }

        if (!$filePath) {
            self::$loaded = true;
            return;
        }

        $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            self::$loaded = true;
            return;
        }

        foreach ($lines as $line) {
            $line = trim($line);

            // Skip comments and empty lines
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            // Split on the first '='
            $parts = explode('=', $line, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $key = trim($parts[0]);
            $val = trim($parts[1]);

            // Strip surrounding quotes
            if (
                (str_starts_with($val, '"') && str_ends_with($val, '"')) ||
                (str_starts_with($val, "'") && str_ends_with($val, "'"))
            ) {
                $val = substr($val, 1, -1);
            }

            // Resolve inline comments if value was not quoted
            if (!str_contains($parts[1], '"') && !str_contains($parts[1], "'")) {
                if (str_contains($val, '#')) {
                    $val = trim(explode('#', $val)[0]);
                }
            }

            // Populate system environments
            putenv("{$key}={$val}");
            $_ENV[$key] = $val;
            $_SERVER[$key] = $val;
        }

        self::$loaded = true;
    }

    /**
     * Retrieve an environment variable with typed casting and default fallback.
     */
    public static function get(string $key, $default = null)
    {
        self::load();

        $val = $_ENV[$key] ?? $_SERVER[$key] ?? getenv($key);
        if ($val === false || $val === null || $val === '') {
            return $default;
        }

        $lower = strtolower((string)$val);
        if ($lower === 'true' || $lower === '(true)') return true;
        if ($lower === 'false' || $lower === '(false)') return false;
        if ($lower === 'null' || $lower === '(null)') return null;
        if ($lower === 'empty' || $lower === '(empty)') return '';

        return $val;
    }
}
