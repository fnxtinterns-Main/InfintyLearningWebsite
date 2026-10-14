<?php
declare(strict_types=1);

namespace App;

final class Announcement
{
    /**
     * Create an announcement.
     * $schoolId = NULL → global (super admin)
     * $schoolId = int  → scoped to that school
     */
    public static function create(
        string $title,
        string $body,
        string $audience,
        bool $pinned,
        int $byUserId,
        ?int $schoolId = null
    ): int {
        $db = Database::connect();
        $stmt = $db->prepare("
            INSERT INTO announcements (title, body, audience, pinned, created_by, school_id)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $title, $body, $audience, $pinned ? 1 : 0, $byUserId,
            $schoolId ? (int)$schoolId : null,
        ]);
        $id = (int)$db->lastInsertId();
        Audit::log('announcement.created', 'announcement', $id, [
            'audience'  => $audience,
            'school_id' => $schoolId,
        ]);
        return $id;
    }

    /**
     * Latest announcements visible to a specific viewer.
     * - super admin → everything
     * - school user → global + their school
     * - guest       → only global (audience='all')
     */
    public static function latestForViewer(string $audienceForRole, int $limit = 5): array
    {
        $scope = null;
        if (Auth::check()) {
            $u = Auth::user();
            if (Auth::isSuperAdmin()) {
                $scope = -1;
            } elseif (!empty($u['school_id'])) {
                $scope = (int)$u['school_id'];
            }
        }
        return self::list($audienceForRole, $scope, $limit);
    }

    /**
     * Latest announcements.
     *
     * @param string    $audience  'all' | 'students' | 'teachers' | 'admins'
     * @param int|null  $schoolId  null = global only, int = that school's + global, -1 = all (super admin)
     */
    public static function list(string $audience = 'all', ?int $schoolId = null, int $limit = 5): array
    {
        $db = Database::connect();
        $sql = "
            SELECT a.*, u.email AS creator_email, sc.name AS school_name
            FROM announcements a
            LEFT JOIN users   u  ON u.id = a.created_by
            LEFT JOIN schools sc ON sc.id = a.school_id
            WHERE a.audience IN ('all', ?)
        ";
        $params = [$audience];

        if ($schoolId === -1) {
            // no scope restriction
        } elseif (!empty($schoolId)) {
            $sql .= " AND (a.school_id IS NULL OR a.school_id = ?)";
            $params[] = (int)$schoolId;
        } else {
            $sql .= " AND a.school_id IS NULL";
        }

        $sql .= " ORDER BY a.pinned DESC, a.created_at DESC LIMIT ?";
        $params[] = $limit;

        $stmt = $db->prepare($sql);
        foreach ($params as $i => $p) {
            $stmt->bindValue($i + 1, $p, is_int($p) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Admin / school admin list view. */
    public static function listForAdmin(?int $schoolId = null, int $limit = 200): array
    {
        $db = Database::connect();
        $sql = "
            SELECT a.*, u.email AS creator_email, sc.name AS school_name
            FROM announcements a
            LEFT JOIN users   u  ON u.id = a.created_by
            LEFT JOIN schools sc ON sc.id = a.school_id
            WHERE 1=1
        ";
        $params = [];
        if ($schoolId === -1) {
            // super admin → all
        } elseif (!empty($schoolId)) {
            $sql .= " AND (a.school_id IS NULL OR a.school_id = ?)";
            $params[] = (int)$schoolId;
        }
        $sql .= " ORDER BY a.pinned DESC, a.created_at DESC LIMIT " . (int)$limit;
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function delete(int $id): void
    {
        Database::connect()->prepare("DELETE FROM announcements WHERE id=?")->execute([$id]);
        Audit::log('announcement.deleted', 'announcement', $id);
    }
    /**
     * Backwards-compatible alias for list().
     * @deprecated Use list() or latestForViewer() instead.
     */
    public static function latest(string $audience = 'all', int $limit = 5): array
    {
        return self::list($audience, -1, $limit);
    }
}