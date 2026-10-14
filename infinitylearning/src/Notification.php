<?php
declare(strict_types=1);

namespace App;

final class Notification
{
    /** Create a notification for a single user. */
    public static function push(
        int $userId,
        string $type,
        string $title,
        ?string $body = null,
        ?string $link = null
    ): void {
        try {
            Database::connect()->prepare("
                INSERT INTO notifications (user_id, type, title, body, link)
                VALUES (?, ?, ?, ?, ?)
            ")->execute([$userId, $type, $title, $body, $link]);
        } catch (\Throwable $e) {
            error_log('Notification push failed: ' . $e->getMessage());
        }
    }

    /** Create the same notification for many users at once. */
    public static function pushMany(
        array $userIds,
        string $type,
        string $title,
        ?string $body = null,
        ?string $link = null
    ): void {
        if (!$userIds) return;
        try {
            $db = Database::connect();
            $db->beginTransaction();
            $stmt = $db->prepare("
                INSERT INTO notifications (user_id, type, title, body, link)
                VALUES (?, ?, ?, ?, ?)
            ");
            foreach (array_unique($userIds) as $uid) {
                $stmt->execute([(int)$uid, $type, $title, $body, $link]);
            }
            $db->commit();
        } catch (\Throwable $e) {
            if (isset($db) && $db->inTransaction()) $db->rollBack();
            error_log('Notification pushMany failed: ' . $e->getMessage());
        }
    }

    /** Notify every user with a given role. */
    public static function pushToRole(string $role, string $type, string $title, ?string $body = null, ?string $link = null): void
    {
        try {
            $ids = Database::connect()
                ->query("SELECT id FROM users WHERE role = " . Database::connect()->quote($role) . " AND status='active'")
                ->fetchAll(\PDO::FETCH_COLUMN);
            self::pushMany($ids, $type, $title, $body, $link);
        } catch (\Throwable $e) {
            error_log('Notification pushToRole failed: ' . $e->getMessage());
        }
    }

    /** Notify every student in a batch. */
    public static function pushToBatch(int $batchId, string $type, string $title, ?string $body = null, ?string $link = null): void
    {
        try {
            $stmt = Database::connect()->prepare("
                SELECT student_user_id FROM batch_students WHERE batch_id = ?
            ");
            $stmt->execute([$batchId]);
            $ids = $stmt->fetchAll(\PDO::FETCH_COLUMN);
            self::pushMany($ids, $type, $title, $body, $link);
        } catch (\Throwable $e) {
            error_log('Notification pushToBatch failed: ' . $e->getMessage());
        }
    }

    /** Latest N for a user. */
    public static function latest(int $userId, int $limit = 12): array
    {
        $stmt = Database::connect()->prepare("
            SELECT * FROM notifications
            WHERE user_id = ?
            ORDER BY is_read ASC, created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $userId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function unreadCount(int $userId): int
    {
        $stmt = Database::connect()->prepare("
            SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0
        ");
        $stmt->execute([$userId]);
        return (int)$stmt->fetchColumn();
    }

    public static function markRead(int $notificationId, int $userId): void
    {
        Database::connect()->prepare("
            UPDATE notifications SET is_read = 1
            WHERE id = ? AND user_id = ?
        ")->execute([$notificationId, $userId]);
    }

    public static function markAllRead(int $userId): void
    {
        Database::connect()->prepare("
            UPDATE notifications SET is_read = 1 WHERE user_id = ? AND is_read = 0
        ")->execute([$userId]);
    }

    /** Pick an emoji icon for a given type. */
    public static function iconFor(string $type): string
    {
        $map = [
            'activity.assigned'      => '🎯',
            'activity.registered'    => '✍️',
            'activity.result'        => '🏅',
            'announcement'           => '📣',
            'teacher.approved'       => '✅',
            'teacher.rejected'       => '❌',
            'attendance.absent'      => '⚠️',
            'achievement.awarded'    => '🏆',
            'certificate.issued'     => '📜',
            'batch.added'            => '🏫',
            'registration.complete'  => '🎉',
            'password.changed'       => '🔑',
        ];
        return $map[$type] ?? '🔔';
    }
}