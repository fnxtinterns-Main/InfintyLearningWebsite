<?php
declare(strict_types=1);

namespace App;

final class RateLimiter
{
    public static function hit(string $identifier, string $action, int $maxAttempts, int $windowSeconds): bool
    {
        $db = Database::connect();
        $stmt = $db->prepare("SELECT id, attempts, window_start FROM rate_limits WHERE identifier = ? AND action = ?");
        $stmt->execute([$identifier, $action]);
        $row = $stmt->fetch();
        $now = time();

        if (!$row) {
            $db->prepare("INSERT INTO rate_limits (identifier, action, attempts, window_start) VALUES (?, ?, 1, NOW())")
               ->execute([$identifier, $action]);
            return true;
        }
        $windowStart = strtotime($row['window_start']);
        if (($now - $windowStart) > $windowSeconds) {
            $db->prepare("UPDATE rate_limits SET attempts = 1, window_start = NOW() WHERE id = ?")
               ->execute([$row['id']]);
            return true;
        }
        if ((int)$row['attempts'] >= $maxAttempts) return false;
        $db->prepare("UPDATE rate_limits SET attempts = attempts + 1 WHERE id = ?")->execute([$row['id']]);
        return true;
    }

    public static function clear(string $identifier, string $action): void
    {
        Database::connect()
            ->prepare("DELETE FROM rate_limits WHERE identifier = ? AND action = ?")
            ->execute([$identifier, $action]);
    }
}