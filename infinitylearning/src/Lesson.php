<?php
declare(strict_types=1);

namespace App;

final class Lesson
{
    public static function create(array $d, int $userId): int
    {
        $db = Database::connect();
        $slug = strtolower(preg_replace('/[^A-Za-z0-9-]+/', '-', $d['title']));
        $slug = $slug . '-' . bin2hex(random_bytes(2));

        // Build the attachments JSON from an array of Uploader::document() results
        $attachmentsJson = null;
        if (!empty($d['attachments']) && is_array($d['attachments'])) {
            $clean = [];
            foreach ($d['attachments'] as $f) {
                if (!is_array($f) || empty($f['path'])) continue;
                $clean[] = [
                    'path' => $f['path'],
                    'mime' => $f['mime'] ?? null,
                    'ext'  => $f['ext']  ?? null,
                    'name' => $f['name'] ?? basename($f['path']),
                    'size' => $f['size'] ?? null,
                ];
            }
            if ($clean) $attachmentsJson = json_encode($clean);
        }

        $stmt = $db->prepare("
            INSERT INTO lessons
                (subject_id, batch_id, title, slug, summary, content, video_url,
                 attachment, attachments, is_ai_generated, difficulty,
                 estimated_minutes, published, created_by, school_id)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            (int)$d['subject_id'],
            !empty($d['batch_id']) ? (int)$d['batch_id'] : null,
            trim($d['title']),
            $slug,
            trim($d['summary'] ?? '') ?: null,
            trim($d['content'] ?? '') ?: null,
            trim($d['video_url'] ?? '') ?: null,
            null,                       // legacy single-attachment column — kept null for new lessons
            $attachmentsJson,
            !empty($d['is_ai_generated']) ? 1 : 0,
            $d['difficulty'] ?? 'medium',
            (int)($d['estimated_minutes'] ?? 15),
            isset($d['published']) ? (int)$d['published'] : 1,
            $userId,
            !empty($d['school_id']) ? (int)$d['school_id'] : null,
        ]);

        $id = (int)$db->lastInsertId();
        Audit::log('lesson.created', 'lesson', $id, [
            'title'       => $d['title'],
            'school_id'   => $d['school_id'] ?? null,
            'batch_id'    => $d['batch_id'] ?? null,
            'attachments' => is_array($d['attachments'] ?? null) ? count($d['attachments']) : 0,
        ]);
        return $id;
    }

    public static function update(int $id, array $d): void
    {
        $db = Database::connect();

        // Merge existing attachments with new ones
        $existing = self::find($id);
        $current  = $existing['attachments_array'] ?? [];

        // Keep only attachments the user chose to keep
        $keep = $d['keep_attachments'] ?? [];
        $kept = [];
        foreach ($current as $a) {
            if (in_array($a['path'], $keep, true)) $kept[] = $a;
        }

        // Add newly uploaded ones
        if (!empty($d['attachments']) && is_array($d['attachments'])) {
            foreach ($d['attachments'] as $f) {
                if (!is_array($f) || empty($f['path'])) continue;
                $kept[] = [
                    'path' => $f['path'],
                    'mime' => $f['mime'] ?? null,
                    'ext'  => $f['ext']  ?? null,
                    'name' => $f['name'] ?? basename($f['path']),
                    'size' => $f['size'] ?? null,
                ];
            }
        }

        $attachmentsJson = $kept ? json_encode($kept) : null;

        $db->prepare("
            UPDATE lessons SET
                subject_id=?, batch_id=?, title=?, summary=?, content=?, video_url=?,
                attachments=?, difficulty=?, estimated_minutes=?, published=?
            WHERE id=?
        ")->execute([
            (int)$d['subject_id'],
            !empty($d['batch_id']) ? (int)$d['batch_id'] : null,
            trim($d['title']),
            trim($d['summary'] ?? '') ?: null,
            trim($d['content'] ?? '') ?: null,
            trim($d['video_url'] ?? '') ?: null,
            $attachmentsJson,
            $d['difficulty'] ?? 'medium',
            (int)($d['estimated_minutes'] ?? 15),
            isset($d['published']) ? (int)$d['published'] : 1,
            $id,
        ]);

        Audit::log('lesson.updated', 'lesson', $id);
        Cache::forget("lesson:find:{$id}");
    }

    public static function delete(int $id): void
    {
        Database::connect()->prepare("DELETE FROM lessons WHERE id=?")->execute([$id]);
        Audit::log('lesson.deleted', 'lesson', $id);
        Cache::forget("lesson:find:{$id}");
    }

    public static function find(int $id): ?array
    {
        return Cache::remember("lesson:find:{$id}", 3600, function () use ($id) {
            $stmt = Database::connect()->prepare("
                SELECT l.*, s.name AS subject_name, s.color AS subject_color,
                       sc.name AS school_name, b.name AS batch_name,
                       u.email AS creator_email, t.full_name AS creator_name
                FROM lessons l
                JOIN subjects s ON s.id = l.subject_id
                LEFT JOIN schools sc ON sc.id = l.school_id
                LEFT JOIN batches b  ON b.id  = l.batch_id
                LEFT JOIN users u    ON u.id  = l.created_by
                LEFT JOIN teachers t ON t.user_id = u.id
                WHERE l.id = ? LIMIT 1
            ");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row) return null;

            // Decode JSON attachments + provide a normalized list (used by views)
            $row['attachments_array'] = self::decodeAttachments($row);
            return $row;
        });
    }

    /**
     * List lessons visible to a viewer.
     *
     * @param array $opts  'subject_id', 'batch_id', 'limit', 'created_by'
     * @param int|null $viewerSchoolId   null = global only | int = that school + global | -1 = super admin (all)
     * @param int|null $viewerUserId     when set, lessons scoped to a batch are only shown if
     *                                   the viewer is the creator OR is enrolled in that batch.
     */
    public static function list(array $opts = [], ?int $viewerSchoolId = null, ?int $viewerUserId = null, ?string $viewerRole = null): array
    {
        $db = Database::connect();
        $sql = "SELECT l.*, s.name AS subject_name, s.color AS subject_color,
                       sc.name AS school_name, b.name AS batch_name, b.class_level AS batch_class_level
                FROM lessons l
                JOIN subjects s ON s.id = l.subject_id
                LEFT JOIN schools sc ON sc.id = l.school_id
                LEFT JOIN batches b  ON b.id  = l.batch_id
                WHERE l.published = 1";
        $params = [];

        // ---- Scope: school ----
        if ($viewerSchoolId === -1) {
            // super admin — no restriction
        } elseif (!empty($viewerSchoolId)) {
            $sql .= " AND (l.school_id IS NULL OR l.school_id = ?)";
            $params[] = (int)$viewerSchoolId;
        } else {
            $sql .= " AND l.school_id IS NULL";
        }

        // ---- Scope: batch & audience ----
        if ($viewerUserId) {
            if ($viewerRole === 'student') {
                // Students see:
                // 1) All open lessons (no specific batch required)
                // 2) Lessons in batches they are enrolled in
                // 3) Lessons in batches matching their class level
                $sql .= " AND (
                    l.batch_id IS NULL
                    OR l.created_by = ?
                    OR l.batch_id IN (
                        SELECT batch_id FROM batch_students 
                        WHERE student_user_id = ? 
                           OR student_user_id IN (SELECT id FROM students WHERE user_id = ?)
                    )
                    OR EXISTS (
                        SELECT 1 FROM batches b2
                        JOIN students st2 ON (st2.current_class = b2.class_level OR st2.current_class LIKE CONCAT('%', b2.class_level, '%'))
                        WHERE b2.id = l.batch_id AND (st2.user_id = ? OR st2.id = ?)
                    )
                )";
                $params[] = $viewerUserId;
                $params[] = $viewerUserId;
                $params[] = $viewerUserId;
                $params[] = $viewerUserId;
                $params[] = $viewerUserId;
            } elseif ($viewerRole === 'teacher') {
                // Teachers see all lessons in their school, plus lessons they created
                // (no restrictive batch filter)
            } elseif ($viewerRole === 'admin' || $viewerRole === 'school_admin') {
                // School admins see all lessons in their school
            } else {
                // Generic / other
                $sql .= " AND (l.batch_id IS NULL OR l.created_by = ?)";
                $params[] = $viewerUserId;
            }
        }

        if (!empty($opts['subject_id'])) { $sql .= " AND l.subject_id = ?"; $params[] = (int)$opts['subject_id']; }
        if (!empty($opts['batch_id']))   { $sql .= " AND l.batch_id = ?";   $params[] = (int)$opts['batch_id']; }
        if (!empty($opts['created_by'])) { $sql .= " AND l.created_by = ?"; $params[] = (int)$opts['created_by']; }

        $sql .= " ORDER BY l.created_at DESC";
        if (!empty($opts['limit'])) $sql .= " LIMIT " . (int)$opts['limit'];

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) {
            $r['attachments_array'] = self::decodeAttachments($r);
        }
        return $rows;
    }

    public static function listForViewer(array $opts = []): array
    {
        $scope = null;
        $userId = null;
        $role = null;
        if (Auth::check()) {
            $u = Auth::user();
            $userId = (int)$u['id'];
            $role = $u['role'] ?? Auth::role();
            if (Auth::isSuperAdmin()) {
                $scope = -1;
            } elseif (!empty($u['school_id'])) {
                $scope = (int)$u['school_id'];
            } else {
                // Resolve school_id from student/teacher profile if not set in users
                try {
                    $db = Database::connect();
                    if ($role === 'student') {
                        $sQ = $db->prepare("
                            SELECT sc.id AS school_id
                            FROM students s
                            JOIN schools sc ON sc.name = s.school_name
                            WHERE s.user_id = ? LIMIT 1
                        ");
                        $sQ->execute([$userId]);
                        $foundId = $sQ->fetchColumn();
                        if ($foundId) $scope = (int)$foundId;
                    } elseif ($role === 'teacher') {
                        $tQ = $db->prepare("
                            SELECT sc.id AS school_id
                            FROM teachers t
                            JOIN schools sc ON sc.name = t.school_name
                            WHERE t.user_id = ? LIMIT 1
                        ");
                        $tQ->execute([$userId]);
                        $foundId = $tQ->fetchColumn();
                        if ($foundId) $scope = (int)$foundId;
                    }
                } catch (\Throwable $e) {}
            }
        }
        return self::list($opts, $scope, $userId, $role);
    }

    /** Lessons a teacher has created (own page). */
    public static function listByCreator(int $userId, int $limit = 200): array
    {
        return self::list(['created_by' => $userId, 'limit' => $limit], -1);
    }

    public static function canView(array $lesson, ?array $viewer): bool
    {
        // Global lesson with no batch → always visible
        if (empty($lesson['school_id']) && empty($lesson['batch_id'])) {
            return true;
        }

        if (!$viewer) return empty($lesson['school_id']) && empty($lesson['batch_id']);
        if (Auth::isSuperAdmin()) return true;

        $viewerRole = $viewer['role'] ?? '';
        $viewerId   = (int)$viewer['id'];

        // If creator
        if ((int)($lesson['created_by'] ?? 0) === $viewerId) {
            return true;
        }

        $viewerSchoolId = !empty($viewer['school_id']) ? (int)$viewer['school_id'] : null;
        if (!$viewerSchoolId) {
            try {
                $db = Database::connect();
                $sQ = $db->prepare("
                    SELECT sc.id FROM students s
                    JOIN schools sc ON sc.name = s.school_name
                    WHERE s.user_id = ? LIMIT 1
                ");
                $sQ->execute([$viewerId]);
                $viewerSchoolId = (int)$sQ->fetchColumn() ?: null;
            } catch (\Throwable $e) {}
        }

        // Check school match if lesson is school-specific
        if (!empty($lesson['school_id'])) {
            if (!$viewerSchoolId || $viewerSchoolId !== (int)$lesson['school_id']) {
                return false;
            }
        }

        // If school admin or teacher of this school
        if ($viewerRole === 'admin' || $viewerRole === 'school_admin' || $viewerRole === 'teacher') {
            return true;
        }

        // If batch is specified, check enrollment or matching class
        if (!empty($lesson['batch_id'])) {
            $db = Database::connect();
            $q = $db->prepare("
                SELECT 1 FROM batch_students bs
                WHERE bs.batch_id = ? AND (
                    bs.student_user_id = ? 
                    OR bs.student_user_id IN (SELECT id FROM students WHERE user_id = ?)
                )
                LIMIT 1
            ");
            $q->execute([(int)$lesson['batch_id'], $viewerId, $viewerId]);
            if ($q->fetch()) return true;

            $q2 = $db->prepare("
                SELECT 1 FROM batches b
                JOIN students s ON (s.current_class = b.class_level OR s.current_class LIKE CONCAT('%', b.class_level, '%'))
                WHERE b.id = ? AND (s.user_id = ? OR s.id = ?)
                LIMIT 1
            ");
            $q2->execute([(int)$lesson['batch_id'], $viewerId, $viewerId]);
            if ($q2->fetch()) return true;

            return false;
        }

        return true;
    }

    public static function subjects(): array
    {
        return Database::connect()->query("SELECT * FROM subjects ORDER BY name")->fetchAll();
    }

    public static function markProgress(int $lessonId, int $userId, int $percent, string $status = 'in_progress'): void
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            INSERT INTO lesson_progress (lesson_id, student_user_id, status, progress_percent, last_viewed_at, completed_at)
            VALUES (?,?,?,?,NOW(), IF(?='completed', NOW(), NULL))
            ON DUPLICATE KEY UPDATE
              status=VALUES(status),
              progress_percent=GREATEST(progress_percent, VALUES(progress_percent)),
              last_viewed_at=NOW(),
              completed_at=IF(VALUES(status)='completed', NOW(), completed_at)
        ");
        $stmt->execute([$lessonId, $userId, $status, min(100, max(0, $percent)), $status]);
    }

    // =========================================================
    // HELPERS
    // =========================================================

    /**
     * Return a normalized array of attachments for a lesson row.
     * Handles both the old single `attachment` column and the new JSON array.
     */
    public static function decodeAttachments(array $lesson): array
    {
        $out = [];

        // New: JSON array
        if (!empty($lesson['attachments'])) {
            $decoded = is_array($lesson['attachments'])
                ? $lesson['attachments']
                : (json_decode($lesson['attachments'], true) ?: []);
            foreach ($decoded as $f) {
                if (empty($f['path'])) continue;
                $out[] = [
                    'path' => $f['path'],
                    'mime' => $f['mime'] ?? null,
                    'ext'  => $f['ext']  ?? strtolower(pathinfo($f['path'], PATHINFO_EXTENSION)),
                    'name' => $f['name'] ?? basename($f['path']),
                    'size' => $f['size'] ?? null,
                ];
            }
        }

        // Legacy: single attachment column
        if (empty($out) && !empty($lesson['attachment'])) {
            $ext = strtolower(pathinfo($lesson['attachment'], PATHINFO_EXTENSION));
            $out[] = [
                'path' => $lesson['attachment'],
                'mime' => null,
                'ext'  => $ext,
                'name' => basename($lesson['attachment']),
                'size' => null,
            ];
        }

        return $out;
    }

    /** Friendly file-type icon for a given extension. */
    public static function fileIcon(string $ext): string
    {
        return match (strtolower($ext)) {
            'pdf'         => '📄',
            'doc','docx'  => '📝',
            'xls','xlsx'  => '📊',
            'ppt','pptx'  => '📽️',
            'jpg','jpeg','png','webp','gif' => '🖼️',
            default       => '📎',
        };
    }

    /** Can this extension be previewed inline in an iframe? */
    public static function isInline(string $ext): bool
    {
        $config = require BASE_PATH . '/config/config.php';
        return in_array(strtolower($ext), $config['uploads']['inline_extensions'], true);
    }
}