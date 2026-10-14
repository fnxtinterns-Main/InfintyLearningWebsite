<?php
declare(strict_types=1);

namespace App;

final class Admin
{
    public static function stats(): array
    {
        $db = Database::connect();
        return [
            'students'         => (int)$db->query("SELECT COUNT(*) FROM users WHERE role='student' AND status='active'")->fetchColumn(),
            'teachers'         => (int)$db->query("SELECT COUNT(*) FROM users WHERE role='teacher' AND status='active'")->fetchColumn(),
            'pending_teachers' => (int)$db->query("SELECT COUNT(*) FROM users WHERE role='teacher' AND status='pending'")->fetchColumn(),
            'batches'          => (int)$db->query("SELECT COUNT(*) FROM batches WHERE status='active'")->fetchColumn(),
            'activities'       => (int)$db->query("SELECT COUNT(*) FROM activities WHERE status='published'")->fetchColumn(),
            'lessons'          => (int)$db->query("SELECT COUNT(*) FROM lessons WHERE published=1")->fetchColumn(),
            'registrations'    => (int)$db->query("SELECT COUNT(*) FROM activity_registrations")->fetchColumn(),
        ];
    }

    public static function pendingTeachers(): array
    {
        $db = Database::connect();
        return $db->query("
            SELECT u.id, u.email, u.phone, u.created_at, t.full_name, t.school_name,
                   t.expert_field, t.years_experience
            FROM users u JOIN teachers t ON t.user_id = u.id
            WHERE u.role='teacher' AND u.status='pending'
            ORDER BY u.created_at ASC
        ")->fetchAll();
    }

    public static function allUsers(?string $role = null, ?string $status = null): array
    {
        $db = Database::connect();
        $sql = "SELECT id, email, phone, role, status, last_login_at, created_at FROM users WHERE 1=1";
        $params = [];
        if ($role)   { $sql .= " AND role = ?";   $params[] = $role; }
        if ($status) { $sql .= " AND status = ?"; $params[] = $status; }
        $sql .= " ORDER BY created_at DESC LIMIT 200";
        $stmt = $db->prepare($sql); $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function approveTeacher(int $userId, int $adminId): void
    {
        $db = Database::connect();
        $db->beginTransaction();
        try {
            $db->prepare("UPDATE users SET status='active' WHERE id=? AND role='teacher'")->execute([$userId]);
            $db->prepare("UPDATE teachers SET approved_by=?, approved_at=NOW() WHERE user_id=?")->execute([$adminId, $userId]);
            $db->commit();
            Audit::log('teacher.approved', 'user', $userId, ['by' => $adminId]);
        } catch (\Throwable $e) { $db->rollBack(); throw $e; }
    }

    public static function rejectTeacher(int $userId, string $reason = ''): void
    {
        Database::connect()->prepare("UPDATE users SET status='rejected' WHERE id=? AND role='teacher'")->execute([$userId]);
        Audit::log('teacher.rejected', 'user', $userId, ['reason' => $reason]);
    }

    public static function suspendUser(int $userId): void
    {
        Database::connect()->prepare("UPDATE users SET status='suspended' WHERE id=?")->execute([$userId]);
        Audit::log('user.suspended', 'user', $userId);
    }

    public static function reactivateUser(int $userId): void
    {
        Database::connect()->prepare("UPDATE users SET status='active' WHERE id=?")->execute([$userId]);
        Audit::log('user.reactivated', 'user', $userId);
    }
}