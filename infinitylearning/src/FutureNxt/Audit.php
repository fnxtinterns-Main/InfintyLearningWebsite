<?php
declare(strict_types=1);

namespace App\FutureNxt;

final class Audit
{
    public static function log(string $action, string $targetType, ?int $targetId = null, ?string $details = null, ?int $employeeId = null): void
    {
        try {
            $db = Database::connect();
            if ($employeeId === null) {
                $employeeId = Auth::id();
            }
            $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            $stmt = $db->prepare("
                INSERT INTO fnx_audit_logs (employee_id, action, target_type, target_id, details, ip_address, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([$employeeId, $action, $targetType, $targetId, $details, $ip]);
        } catch (\Throwable $e) {
            error_log('FutureNxt Audit log error: ' . $e->getMessage());
        }
    }

    public static function recent(int $limit = 20): array
    {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                SELECT a.*, e.full_name AS employee_name, e.emp_code
                FROM fnx_audit_logs a
                LEFT JOIN fnx_employees e ON e.id = a.employee_id
                ORDER BY a.created_at DESC
                LIMIT ?
            ");
            $stmt->bindValue(1, $limit, \PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll() ?: [];
        } catch (\Throwable $e) {
            return [];
        }
    }

    public static function getRecent(int $limit = 20): array
    {
        return self::recent($limit);
    }

    /**
     * Format a UTC timestamp into Indian Standard Time (IST, UTC+05:30)
     */
    public static function formatIst(?string $utcTimestamp, string $format = 'd M, h:i A'): string
    {
        if (!$utcTimestamp) {
            return '—';
        }
        try {
            // Assume UTC if no timezone offset is in the string
            $dt = new \DateTime($utcTimestamp, new \DateTimeZone('UTC'));
            $dt->setTimezone(new \DateTimeZone('Asia/Kolkata'));
            return $dt->format($format) . ' IST';
        } catch (\Throwable $e) {
            return date($format, strtotime($utcTimestamp));
        }
    }
}
