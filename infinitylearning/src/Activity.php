<?php
declare(strict_types=1);

namespace App;

final class Activity
{
    public static function create(array $d, int $creatorId): int
    {
        $db = Database::connect();
        $slug = self::slug($d['title']);
        $uuid = Auth::uuid();

        $stmt = $db->prepare("
            INSERT INTO activities
            (uuid, title, slug, category, short_description, description, cover_image, gallery,
             event_date, start_time, end_time, venue, organizer, eligibility,
             registration_deadline, registration_url, fee, max_participants,
             contact_email, contact_phone, tags, featured, status, created_by, school_id)
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            $uuid, $d['title'], $slug, $d['category'],
            $d['short_description'] ?? null, $d['description'] ?? null,
            $d['cover_image'] ?? null,
            isset($d['gallery']) ? json_encode($d['gallery']) : null,
            $d['event_date'] ?: null, $d['start_time'] ?: null, $d['end_time'] ?: null,
            $d['venue'] ?? null, $d['organizer'] ?? null, $d['eligibility'] ?? null,
            $d['registration_deadline'] ?: null, $d['registration_url'] ?? null,
            (float)($d['fee'] ?? 0), $d['max_participants'] ?: null,
            $d['contact_email'] ?? null, $d['contact_phone'] ?? null,
            $d['tags'] ?? null, !empty($d['featured']) ? 1 : 0,
            $d['status'] ?? 'published', $creatorId,
            !empty($d['school_id']) ? (int)$d['school_id'] : null,
        ]);
        $id = (int)$db->lastInsertId();
        Audit::log('activity.created', 'activity', $id, [
            'title'     => $d['title'],
            'school_id' => $d['school_id'] ?? null,
        ]);
        return $id;
    }

    public static function update(int $id, array $d): void
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            UPDATE activities SET
                title=?, category=?, short_description=?, description=?,
                cover_image=COALESCE(?, cover_image),
                event_date=?, start_time=?, end_time=?, venue=?, organizer=?,
                eligibility=?, registration_deadline=?, registration_url=?,
                fee=?, max_participants=?, contact_email=?, contact_phone=?,
                tags=?, featured=?, status=?
            WHERE id=?
        ");
        $stmt->execute([
            $d['title'], $d['category'], $d['short_description'] ?? null, $d['description'] ?? null,
            $d['cover_image'] ?? null,
            $d['event_date'] ?: null, $d['start_time'] ?: null, $d['end_time'] ?: null,
            $d['venue'] ?? null, $d['organizer'] ?? null, $d['eligibility'] ?? null,
            $d['registration_deadline'] ?: null, $d['registration_url'] ?? null,
            (float)($d['fee'] ?? 0), $d['max_participants'] ?: null,
            $d['contact_email'] ?? null, $d['contact_phone'] ?? null,
            $d['tags'] ?? null, !empty($d['featured']) ? 1 : 0,
            $d['status'] ?? 'published', $id,
        ]);
        Audit::log('activity.updated', 'activity', $id);
    }

    public static function delete(int $id): void
    {
        Database::connect()->prepare("DELETE FROM activities WHERE id=?")->execute([$id]);
        Audit::log('activity.deleted', 'activity', $id);
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connect()->prepare("
            SELECT a.*, s.name AS school_name
            FROM activities a
            LEFT JOIN schools s ON s.id = a.school_id
            WHERE a.id = ? LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function findBySlug(string $slug): ?array
    {
        $stmt = Database::connect()->prepare("
            SELECT a.*, s.name AS school_name
            FROM activities a
            LEFT JOIN schools s ON s.id = a.school_id
            WHERE a.slug = ? LIMIT 1
        ");
        $stmt->execute([$slug]);
        return $stmt->fetch() ?: null;
    }

    /**
     * List activities visible to a viewer.
     *
     * @param array $opts  Supports: 'category', 'status', 'q', 'limit', 'featured'
     * @param int|null $viewerSchoolId  The school whose activities should be included.
     *                                  NULL = show only global activities.
     *                                  -1   = show everything (super admin).
     */
    public static function list(array $opts = [], ?int $viewerSchoolId = null): array
    {
        $db = Database::connect();
        $sql = "SELECT a.*, s.name AS school_name
                FROM activities a
                LEFT JOIN schools s ON s.id = a.school_id
                WHERE 1=1";
        $params = [];

        // Scope filter
        if ($viewerSchoolId === -1) {
            // super admin → no scope restriction
        } elseif (!empty($viewerSchoolId)) {
            $sql .= " AND (a.school_id IS NULL OR a.school_id = ?)";
            $params[] = (int)$viewerSchoolId;
        } else {
            $sql .= " AND a.school_id IS NULL";
        }

        if (!empty($opts['category'])) { $sql .= " AND a.category=?"; $params[] = $opts['category']; }
        if (!empty($opts['status']))   { $sql .= " AND a.status=?";   $params[] = $opts['status']; }
        if (!empty($opts['featured'])) { $sql .= " AND a.featured=1"; }
        if (!empty($opts['q'])) {
            $sql .= " AND (a.title LIKE ? OR a.short_description LIKE ? OR a.tags LIKE ?)";
            $q = '%' . $opts['q'] . '%';
            $params[] = $q; $params[] = $q; $params[] = $q;
        }
        $sql .= " ORDER BY a.featured DESC, a.event_date IS NULL, a.event_date ASC, a.created_at DESC";
        if (!empty($opts['limit'])) $sql .= " LIMIT " . (int)$opts['limit'];

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Convenience: pick scope automatically based on the currently logged-in user.
     * - super admin  → -1 (everything)
     * - school user  → their school_id
     * - guest        → NULL (only global)
     */
    public static function listForViewer(array $opts = []): array
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
        return self::list($opts, $scope);
    }

    /** Upcoming activities visible to the viewer. */
    public static function upcoming(int $limit = 6, ?int $viewerSchoolId = null): array
    {
        $db = Database::connect();
        $sql = "SELECT a.*, s.name AS school_name
                FROM activities a
                LEFT JOIN schools s ON s.id = a.school_id
                WHERE a.status='published'
                  AND (a.event_date IS NULL OR a.event_date >= CURDATE())";
        $params = [];

        if ($viewerSchoolId === -1) {
            // no scope
        } elseif (!empty($viewerSchoolId)) {
            $sql .= " AND (a.school_id IS NULL OR a.school_id = ?)";
            $params[] = (int)$viewerSchoolId;
        } else {
            $sql .= " AND a.school_id IS NULL";
        }
        $sql .= " ORDER BY a.featured DESC, a.event_date ASC LIMIT ?";
        $params[] = $limit;

        $stmt = $db->prepare($sql);
        foreach ($params as $i => $p) {
            $stmt->bindValue($i + 1, $p, is_int($p) ? \PDO::PARAM_INT : \PDO::PARAM_STR);
        }
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Can this viewer see the given activity? */
    public static function canView(array $activity, ?array $viewer): bool
    {
        if (empty($activity['school_id'])) return true;   // global
        if (!$viewer) return false;                       // guest can't see school activities
        if (Auth::isSuperAdmin()) return true;
        if (!empty($viewer['school_id']) && (int)$viewer['school_id'] === (int)$activity['school_id']) return true;
        return false;
    }

    public static function register(int $activityId, int $userId, string $notes = ''): bool
    {
        try {
            Database::connect()
                ->prepare("INSERT INTO activity_registrations (activity_id, user_id, notes) VALUES (?,?,?)")
                ->execute([$activityId, $userId, $notes ?: null]);
            Audit::log('activity.registered', 'activity', $activityId);
            return true;
        } catch (\PDOException $e) { return false; }
    }

    public static function slug(string $title): string
    {
        $s = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        $db = Database::connect();
        $base = $s; $i = 1;
        while (true) {
            $stmt = $db->prepare("SELECT id FROM activities WHERE slug=? LIMIT 1");
            $stmt->execute([$s]);
            if (!$stmt->fetch()) break;
            $s = $base . '-' . (++$i);
        }
        return $s;
    }
        /**
     * Record a result for a student in an activity and auto-generate the
     * correct certificate (winner / runner-up / participant).
     *
     * @param string $type  'winner' | 'runner_up' | 'participant'
     */
    public static function recordResult(
        int $activityId,
        int $studentUserId,
        string $type,
        ?float $score = null,
        ?int $position = null,
        ?string $remarks = null,
        ?int $recordedBy = null
    ): array {
        $db = Database::connect();

        // 1. Upsert activity_results
        $db->prepare("
            INSERT INTO activity_results
                (activity_id, student_user_id, score, position, remarks, recorded_by, recorded_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE
                score = VALUES(score),
                position = VALUES(position),
                remarks = VALUES(remarks),
                recorded_by = VALUES(recorded_by),
                recorded_at = NOW()
        ")->execute([
            $activityId, $studentUserId, $score, $position, $remarks, $recordedBy,
        ]);

        // 2. Update the registration status
        $db->prepare("
            UPDATE activity_registrations
            SET status = 'attended'
            WHERE activity_id = ? AND user_id = ?
        ")->execute([$activityId, $studentUserId]);

        // 3. Load the student + activity data for the certificate
        $activity = self::find($activityId);
        $student = Auth::findStudentByUserId($studentUserId);
        if (!$activity || !$student) {
            return ['certificate' => null];
        }

        // 4. Determine certificate type
        //    If caller passed 'winner' but no rank, default based on position
        if ($type === 'winner' && $position && $position > 1) {
            $type = 'runner_up';
        }
        if (!in_array($type, ['winner','runner_up','participant'], true)) {
            $type = 'participant';
        }

        // 5. Determine rank label
        $rank = null;
        if ($type === 'winner' && $position) {
            $rank = match ($position) {
                1 => '1st Place',
                2 => '2nd Place',
                3 => '3rd Place',
                default => $position . 'th Place',
            };
        } elseif ($type === 'runner_up') {
            $rank = $position === 2 ? '2nd Place (Runner-up)' : ($position ? $position . 'th Place' : 'Runner-up');
        }

        // 6. School name (for the certificate body)
        $schoolName = null;
        $userRow = Auth::findById($studentUserId);
        if ($userRow && !empty($userRow['school_id'])) {
            $s = School::find((int)$userRow['school_id']);
            $schoolName = $s['name'] ?? null;
        }

        // 7. Generate the certificate
        try {
            $cert = Certificate::generate(
                studentUserId:   $studentUserId,
                studentName:     $student['full_name'],
                type:            $type,
                eventTitle:      $activity['title'],
                issuerLine:      'Infinity Learning',
                rank:            $rank,
                dateCompleted:   date('d M Y'),
                schoolName:      $schoolName
            );
        } catch (\Throwable $e) {
            error_log('Certificate generation failed: ' . $e->getMessage());
            $cert = null;
        }

        // 8. Notify the student
        Notification::push(
            $studentUserId,
            'certificate.issued',
            'Your certificate is ready! 🏅',
            'You received a certificate for: ' . $activity['title'],
            '/my-certificates'
        );

        return [
            'certificate' => $cert,
            'type'        => $type,
            'rank'        => $rank,
        ];
    }
}