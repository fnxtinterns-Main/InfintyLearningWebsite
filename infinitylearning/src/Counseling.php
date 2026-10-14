<?php
declare(strict_types=1);

namespace App;

final class Counseling
{
    /** Human-readable labels for the 4 types. */
    public static function types(): array
    {
        return [
            'academics'    => [
                'label' => 'Academics',
                'icon'  => '📘',
                'color' => '#FF7A18',
                'desc'  => 'Subject-wise guidance, study strategies, and personalised learning plans.',
            ],
            'career'       => [
                'label' => 'Career',
                'icon'  => '🎯',
                'color' => '#2563EB',
                'desc'  => 'Discover careers that match your strengths and interests.',
            ],
            'admission'    => [
                'label' => 'Admission',
                'icon'  => '🎓',
                'color' => '#7C2D5C',
                'desc'  => 'Choose the right schools, colleges, and courses with confidence.',
            ],
            'psychometric' => [
                'label' => 'Psychometric',
                'icon'  => '🧠',
                'color' => '#16A34A',
                'desc'  => 'Understand your personality, aptitude, and learning style.',
            ],
        ];
    }

    public static function typeLabel(string $type): string
    {
        return self::types()[$type]['label'] ?? ucfirst($type);
    }

    public static function typeIcon(string $type): string
    {
        return self::types()[$type]['icon'] ?? '📋';
    }

    /**
     * Create a new counseling request.
     * Returns the new ID.
     */
    public static function create(array $d, int $studentUserId, ?int $schoolId): int
    {
        $db = Database::connect();
        $uuid = Auth::uuid();

        $stmt = $db->prepare("
            INSERT INTO counseling_requests
                (uuid, student_user_id, school_id, counseling_type,
                 rating_concentration, rating_cognitive, rating_comprehension,
                 rating_creativity, rating_confidence, rating_communication,
                 full_name, email, phone, current_class, city, state,
                 reason, preferred_time, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([
            $uuid,
            $studentUserId,
            $schoolId,
            $d['counseling_type'],
            (int)$d['rating_concentration'],
            (int)$d['rating_cognitive'],
            (int)$d['rating_comprehension'],
            (int)$d['rating_creativity'],
            (int)$d['rating_confidence'],
            (int)$d['rating_communication'],
            $d['full_name'],
            $d['email'] ?: null,
            $d['phone'] ?: null,
            $d['current_class'] ?: null,
            $d['city'] ?: null,
            $d['state'] ?: null,
            $d['reason'] ?: null,
            $d['preferred_time'] ?: null,
        ]);
        $id = (int)$db->lastInsertId();

        self::log($id, $studentUserId, 'submitted', 'Request created');

        Audit::log('counseling.created', 'counseling', $id, [
            'type'   => $d['counseling_type'],
            'school' => $schoolId,
        ]);

        return $id;
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connect()->prepare("
            SELECT cr.*,
                   s.full_name AS student_name_full,
                   s.profile_image AS student_avatar,
                   sc.name AS school_name,
                   t.full_name AS teacher_name,
                   u_t.email AS teacher_email
            FROM counseling_requests cr
            LEFT JOIN students s ON s.user_id = cr.student_user_id
            LEFT JOIN schools  sc ON sc.id = cr.school_id
            LEFT JOIN users u_t ON u_t.id = cr.assigned_teacher_id
            LEFT JOIN teachers t ON t.user_id = u_t.id
            WHERE cr.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

        public static function forStudent(int $studentUserId): array
    {
        $stmt = Database::connect()->prepare("
            SELECT cr.*, sc.name AS school_name,
                   t.full_name AS teacher_name
            FROM counseling_requests cr
            LEFT JOIN schools  sc ON sc.id = cr.school_id
            LEFT JOIN users u_t ON u_t.id = cr.assigned_teacher_id
            LEFT JOIN teachers t ON t.user_id = u_t.id
            WHERE cr.student_user_id = ?
            ORDER BY cr.created_at DESC
        ");
        $stmt->execute([$studentUserId]);
        return $stmt->fetchAll();
    }

    /** School admin's queue — every request for their school. */
    public static function forSchool(int $schoolId, ?string $status = null): array
    {
        $db = Database::connect();
        $sql = "
            SELECT cr.*,
                   s.full_name AS student_name_full,
                   s.profile_image AS student_avatar,
                   t.full_name AS teacher_name
            FROM counseling_requests cr
            LEFT JOIN students s ON s.user_id = cr.student_user_id
            LEFT JOIN users u_t ON u_t.id = cr.assigned_teacher_id
            LEFT JOIN teachers t ON t.user_id = u_t.id
            WHERE cr.school_id = ?
        ";
        $params = [$schoolId];
        if ($status && in_array($status, ['pending','assigned','in_progress','completed','cancelled'], true)) {
            $sql .= " AND cr.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY 
                    CASE cr.status
                        WHEN 'pending'     THEN 1
                        WHEN 'assigned'    THEN 2
                        WHEN 'in_progress' THEN 3
                        WHEN 'completed'   THEN 4
                        ELSE 5
                    END,
                    cr.created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Teacher's queue — only requests assigned to them. */
    public static function forTeacher(int $teacherUserId, ?string $status = null): array
    {
        $db = Database::connect();
        $sql = "
            SELECT cr.*,
                   s.full_name AS student_name_full,
                   s.profile_image AS student_avatar,
                   sc.name AS school_name
            FROM counseling_requests cr
            LEFT JOIN students s ON s.user_id = cr.student_user_id
            LEFT JOIN schools  sc ON sc.id = cr.school_id
            WHERE cr.assigned_teacher_id = ?
        ";
        $params = [$teacherUserId];
        if ($status && in_array($status, ['pending','assigned','in_progress','completed','cancelled'], true)) {
            $sql .= " AND cr.status = ?";
            $params[] = $status;
        }
        $sql .= " ORDER BY 
                    CASE cr.status
                        WHEN 'assigned'    THEN 1
                        WHEN 'in_progress' THEN 2
                        WHEN 'completed'   THEN 3
                        ELSE 4
                    END,
                    cr.created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /** Assign a teacher to a request. */
    public static function assignTeacher(int $requestId, int $teacherUserId, int $assignedBy): bool
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            UPDATE counseling_requests
            SET assigned_teacher_id = ?,
                assigned_at = NOW(),
                assigned_by = ?,
                status = 'assigned'
            WHERE id = ? AND status IN ('pending','assigned')
        ");
        $stmt->execute([$teacherUserId, $assignedBy, $requestId]);

        if ($stmt->rowCount() > 0) {
            self::log($requestId, $assignedBy, 'assigned', 'Teacher #' . $teacherUserId . ' assigned');
            Audit::log('counseling.assigned', 'counseling', $requestId, ['teacher' => $teacherUserId]);
            return true;
        }
        return false;
    }

    /** Mark as in-progress. */
    public static function markInProgress(int $requestId, int $byUserId): void
    {
        $db = Database::connect();
        $db->prepare("UPDATE counseling_requests SET status='in_progress' WHERE id=?")
           ->execute([$requestId]);
        self::log($requestId, $byUserId, 'in_progress', null);
    }

    /** Mark as complete with notes. */
    public static function complete(int $requestId, string $notes, int $byUserId): void
    {
        $db = Database::connect();
        $db->prepare("
            UPDATE counseling_requests
            SET status='completed', teacher_notes=?, completed_at=NOW()
            WHERE id=?
        ")->execute([$notes ?: null, $requestId]);
        self::log($requestId, $byUserId, 'completed', $notes);
    }

    /** Cancel a request. */
    public static function cancel(int $requestId, int $byUserId): void
    {
        $db = Database::connect();
        $db->prepare("UPDATE counseling_requests SET status='cancelled' WHERE id=?")
           ->execute([$requestId]);
        self::log($requestId, $byUserId, 'cancelled', null);
    }

    private static function log(int $requestId, int $userId, string $action, ?string $note): void
    {
        try {
            Database::connect()->prepare("
                INSERT INTO counseling_logs (request_id, user_id, action, note)
                VALUES (?, ?, ?, ?)
            ")->execute([$requestId, $userId, $action, $note]);
        } catch (\Throwable $e) {
            error_log('Counseling log failed: ' . $e->getMessage());
        }
    }
}