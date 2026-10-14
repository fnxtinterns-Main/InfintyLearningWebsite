<?php
declare(strict_types=1);

namespace App;

use Throwable;

final class Cache
{
    /** @var object|null */
    private static ?object $redis = null;
    private static bool $attemptedConnection = false;
    private static array $memoryCache = [];

    /**
     * Get the Redis client or null if disabled/unavailable.
     */
    public static function client(): ?object
    {
        if (self::$attemptedConnection) {
            return self::$redis;
        }

        self::$attemptedConnection = true;

        $config = require BASE_PATH . '/config/config.php';
        $redisConf = $config['redis'] ?? [];

        if (empty($redisConf['enabled']) || !class_exists('Redis')) {
            return null;
        }

        try {
            $redisClass = 'Redis';
            $redis = new $redisClass();
            $host = $redisConf['host'] ?? '127.0.0.1';
            $port = (int)($redisConf['port'] ?? 6379);
            $timeout = (float)($redisConf['timeout'] ?? 1.5);

            $connected = @$redis->connect($host, $port, $timeout);
            if (!$connected) {
                return null;
            }

            if (!empty($redisConf['password'])) {
                $redis->auth($redisConf['password']);
            }

            if (isset($redisConf['db'])) {
                $redis->select((int)$redisConf['db']);
            }

            $prefix = $redisConf['prefix'] ?? 'il:';
            $redis->setOption(\Redis::OPT_PREFIX, $prefix);
            $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);

            self::$redis = $redis;
        } catch (Throwable $e) {
            error_log('Redis Cache TCP connection error: ' . $e->getMessage());
            self::$redis = null;
        }

        return self::$redis;
    }

    /**
     * Fallback REST API for Upstash (works without any PHP extensions via standard HTTPS).
     */
    private static function restCommand(string $command, array $args = [])
    {
        $config = require BASE_PATH . '/config/config.php';
        $redisConf = $config['redis'] ?? [];

        if (empty($redisConf['enabled']) || empty($redisConf['rest_url']) || empty($redisConf['rest_token'])) {
            return null;
        }

        $url = rtrim($redisConf['rest_url'], '/') . '/' . strtolower($command);
        foreach ($args as $arg) {
            $url .= '/' . rawurlencode((string)$arg);
        }

        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => "Authorization: Bearer " . $redisConf['rest_token'] . "\r\n" .
                            "Accept: application/json\r\n",
                'timeout' => 2.0,
                'ignore_errors' => true,
            ]
        ];

        try {
            $ctx = stream_context_create($opts);
            $resp = @file_get_contents($url, false, $ctx);
            if ($resp !== false) {
                $json = json_decode($resp, true);
                if (isset($json['result'])) {
                    return $json['result'];
                }
            }
        } catch (Throwable $e) {
            error_log('Redis REST error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Retrieve an item from cache.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        $redis = self::client();
        if ($redis) {
            try {
                $val = $redis->get($key);
                return $val !== false ? $val : $default;
            } catch (Throwable $e) {
                error_log('Redis TCP get error: ' . $e->getMessage());
            }
        }

        // Try Upstash REST fallback
        $config = require BASE_PATH . '/config/config.php';
        $prefix = $config['redis']['prefix'] ?? 'il:';
        $restVal = self::restCommand('get', [$prefix . $key]);
        if ($restVal !== null) {
            $unserialized = @unserialize($restVal);
            return $unserialized !== false ? $unserialized : $restVal;
        }

        return self::$memoryCache[$key] ?? $default;
    }

    /**
     * Store an item in the cache with a time-to-live in seconds.
     */
    public static function set(string $key, mixed $value, int $ttl = 3600): bool
    {
        self::$memoryCache[$key] = $value;

        $redis = self::client();
        if ($redis) {
            try {
                return (bool)$redis->setex($key, $ttl, $value);
            } catch (Throwable $e) {
                error_log('Redis TCP set error: ' . $e->getMessage());
            }
        }

        // Try Upstash REST fallback
        $config = require BASE_PATH . '/config/config.php';
        $prefix = $config['redis']['prefix'] ?? 'il:';
        $serialized = is_scalar($value) ? (string)$value : serialize($value);
        $res = self::restCommand('set', [$prefix . $key, $serialized, 'ex', (string)$ttl]);
        if ($res === 'OK') {
            return true;
        }

        return true;
    }

    /**
     * Get an item from the cache, or execute the given Closure and store the result.
     */
    public static function remember(string $key, int $ttl, callable $callback)
    {
        $val = self::get($key);
        if ($val !== null) {
            return $val;
        }

        $computed = $callback();
        self::set($key, $computed, $ttl);
        return $computed;
    }

    /**
     * Delete an item from the cache.
     */
    public static function forget(string $key): bool
    {
        unset(self::$memoryCache[$key]);

        $redis = self::client();
        if ($redis) {
            try {
                return (bool)$redis->del($key);
            } catch (Throwable $e) {
                error_log('Redis del error: ' . $e->getMessage());
            }
        }

        $config = require BASE_PATH . '/config/config.php';
        $prefix = $config['redis']['prefix'] ?? 'il:';
        self::restCommand('del', [$prefix . $key]);

        return true;
    }

    /**
     * Clear all cached keys.
     */
    public static function flush(): bool
    {
        self::$memoryCache = [];

        $redis = self::client();
        if ($redis) {
            try {
                return $redis->flushDB();
            } catch (Throwable $e) {
                error_log('Redis flush error: ' . $e->getMessage());
            }
        }

        return true;
    }
}
