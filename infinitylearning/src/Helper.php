<?php
declare(strict_types=1);

namespace App;

final class Helper
{
    public static function e(?string $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function redirect(string $url): void
    {
        header("Location: $url");
        exit;
    }

    public static function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    public static function input(string $key, $default = null)
    {
        return $_POST[$key] ?? $_GET[$key] ?? $default;
    }

    public static function old(string $key, $default = ''): string
    {
        return self::e($_SESSION['_old'][$key] ?? $default);
    }

    public static function flashOld(array $data): void
    {
        unset($data['password'], $data['password_confirm']);
        $_SESSION['_old'] = $data;
    }

    public static function clearOld(): void
    {
        unset($_SESSION['_old']);
    }

    /**
     * Normalize any accepted phone format to canonical +91XXXXXXXXXX.
     * Returns the original trimmed string if it can't be normalized
     * (so validation will reject it downstream).
     */
    public static function normalizePhone(?string $phone): string
    {
        $raw = trim((string)$phone);
        if ($raw === '') return '';

        // Keep digits only
        $digits = preg_replace('/\D+/', '', $raw) ?? '';

        // Strip +91 / 91 / 0 prefixes to reach the 10-digit core
        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            $digits = substr($digits, 2);
        } elseif (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            $digits = substr($digits, 1);
        }

        // If it looks like a valid 10-digit Indian mobile, return +91 form
        if (preg_match('/^[6-9][0-9]{9}$/', $digits)) {
            return '+91' . $digits;
        }

        // Otherwise return as-is so validation catches it
        return $raw;
    }

    /** 10-digit display form (no country code). */
    public static function displayPhone(?string $phone): string
    {
        $n = self::normalizePhone($phone);
        return preg_replace('/^\+91/', '', $n) ?? $n;
    }

    /**
     * Resolve public asset URL with optional CDN edge acceleration.
     */
    public static function asset(string $path): string
    {
        $cleanPath = '/' . ltrim($path, '/');
        $config = require BASE_PATH . '/config/config.php';
        $cdn = $config['cdn'] ?? [];
        $cdnUrl = !empty($cdn['enabled']) && !empty($cdn['url']) ? rtrim($cdn['url'], '/') : '';
        return $cdnUrl . $cleanPath;
    }

    /**
     * Generate URL-friendly slug from text.
     */
    public static function slug(string $text): string
    {
        $slug = preg_replace('~[^\pL\d]+~u', '-', $text);
        $slug = trim($slug ?? '', '-');
        $slug = preg_replace('~-+~', '-', $slug);
        $slug = strtolower($slug ?? '');
        return $slug ?: 'n-a';
    }

    /**
     * Get accurate client IP supporting Cloudflare & Reverse Proxies.
     */
    public static function clientIp(): string
    {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return trim($_SERVER['HTTP_CF_CONNECTING_IP']);
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $parts = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($parts[0]);
        }
        return $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    }

    /**
     * Human-readable relative time (e.g. Just now, 5m ago, Yesterday).
     */
    public static function timeAgo(?string $datetime): string
    {
        if (empty($datetime)) return 'Just now';
        $time = strtotime($datetime);
        if (!$time) return 'Just now';
        $diff = time() - $time;
        if ($diff < 15) return 'Just now';
        if ($diff < 60) return $diff . 's ago';
        if ($diff < 3600) return (int)floor($diff / 60) . 'm ago';
        if ($diff < 86400) return (int)floor($diff / 3600) . 'h ago';
        if ($diff < 172800) return 'Yesterday, ' . date('h:i A', $time);
        if ($diff < 604800) return (int)floor($diff / 86400) . 'd ago';
        return date('d M Y, h:i A', $time);
    }

    /**
     * Automatically update student age every year based on profile registration date.
     */
    public static function calculateStudentAge(?int $baseAge, ?string $createdAt): ?int
    {
        if ($baseAge === null || $baseAge <= 0) return null;
        if (empty($createdAt)) return $baseAge;
        $createdTime = strtotime($createdAt);
        if (!$createdTime) return $baseAge;
        $createdYear = (int)date('Y', $createdTime);
        $currentYear = (int)date('Y');
        $yearsElapsed = max(0, $currentYear - $createdYear);
        return $baseAge + $yearsElapsed;
    }
}