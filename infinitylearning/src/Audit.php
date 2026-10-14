<?php
declare(strict_types=1);

namespace App;

final class Audit
{
    public static function log(string $action, ?string $entityType = null, ?int $entityId = null, array $metadata = []): void
    {
        try {
            $db = Database::connect();
            $stmt = $db->prepare("
                INSERT INTO audit_logs (user_id, action, entity_type, entity_id, metadata, ip_address)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $_SESSION['user_id'] ?? null,
                $action, $entityType, $entityId,
                $metadata ? json_encode($metadata) : null,
                $_SERVER['REMOTE_ADDR'] ?? null,
            ]);
        } catch (\Throwable $e) {
            error_log('Audit log failed: ' . $e->getMessage());
        }
    }
}