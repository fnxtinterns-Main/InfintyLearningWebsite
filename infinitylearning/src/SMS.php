<?php
declare(strict_types=1);

namespace App;

final class SMS
{
    public static function sendOTP(string $phone, string $otp): bool
    {
        $config = require BASE_PATH . '/config/config.php';
        if ($config['app']['env'] === 'development') {
            error_log("[OTP] Phone=$phone OTP=$otp");
            return true;
        }
        // TODO: integrate real SMS provider (MSG91, Twilio, etc.)
        return true;
    }
}