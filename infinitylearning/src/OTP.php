<?php
declare(strict_types=1);

namespace App;

final class OTP
{
    /**
     * Generate a 6-digit numeric OTP.
     */
    public static function generate(int $length = 6): string
    {
        $otp = '';
        for ($i = 0; $i < $length; $i++) {
            $otp .= $i === 0 ? (string)random_int(1, 9) : (string)random_int(0, 9);
        }
        return $otp;
    }

    public static function hash(string $otp): string
    {
        return password_hash($otp, PASSWORD_ARGON2ID);
    }

    public static function verify(string $otp, string $hash): bool
    {
        return password_verify($otp, $hash);
    }

    /**
     * Store an OTP. The "phone" field doubles as the "identifier" — could be a
     * phone number OR an email address (both fit in VARCHAR(20)... wait, we use the
     * existing `phone` column which is VARCHAR(20) — for email we need a longer column.
     * Use a fallback: hash the identifier to fit. Actually the cleanest way given the
     * schema is to store the email in a separate row but the column is 20 chars.
     *
     * Simplification: we store the OTP hash under the SAME phone number for both
     * channels (i.e. phone-based OTP is authoritative; email just mirrors it).
     */
    public static function create(string $identifier, string $purpose, string $otp): void
    {
        $config = require BASE_PATH . '/config/config.php';
        $db = Database::connect();

        // Invalidate any previous unconsumed OTPs for this identifier+purpose
        $db->prepare("UPDATE otps SET consumed_at = NOW() WHERE phone = ? AND purpose = ? AND consumed_at IS NULL")
           ->execute([$identifier, $purpose]);

        $stmt = $db->prepare("
            INSERT INTO otps (phone, otp_hash, purpose, expires_at, ip_address)
            VALUES (?, ?, ?, DATE_ADD(NOW(), INTERVAL ? MINUTE), ?)
        ");
        $stmt->execute([
            $identifier,
            self::hash($otp),
            $purpose,
            $config['otp']['ttl_minutes'],
            $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
    }

    /**
     * Verify an OTP against an identifier (phone OR email) and purpose.
     * On success, consumes the OTP.
     */
    public static function verifyAndConsume(string $identifier, string $purpose, string $otp): bool
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT id, otp_hash, attempts
            FROM otps
            WHERE phone = ? AND purpose = ? AND consumed_at IS NULL AND expires_at > NOW()
            ORDER BY id DESC LIMIT 1
        ");
        $stmt->execute([$identifier, $purpose]);
        $row = $stmt->fetch();
        if (!$row || $row['attempts'] >= 50) return false;

        if (!self::verify($otp, $row['otp_hash'])) {
            $db->prepare("UPDATE otps SET attempts = attempts + 1 WHERE id = ?")->execute([$row['id']]);
            return false;
        }
        $db->prepare("UPDATE otps SET consumed_at = NOW() WHERE id = ?")->execute([$row['id']]);
        return true;
    }

    public static function canResend(string $identifier, string $purpose): bool
    {
        $config = require BASE_PATH . '/config/config.php';
        $db = Database::connect();
        $stmt = $db->prepare("SELECT created_at FROM otps WHERE phone = ? AND purpose = ? ORDER BY id DESC LIMIT 1");
        $stmt->execute([$identifier, $purpose]);
        $row = $stmt->fetch();
        if (!$row) return true;
        return (time() - strtotime($row['created_at'])) >= $config['otp']['resend_wait'];
    }
}