<?php
declare(strict_types=1);

namespace App;

final class ParentSummary
{
    /**
     * Build the summary data for a given student.
     * Returns everything the email template needs.
     */
    public static function buildForStudent(int $studentUserId, string $weekStart): ?array
    {
        $db = Database::connect();

        // ---- Student identity ----
        $stmt = $db->prepare("
            SELECT u.id, u.email, s.full_name, s.profile_image,
                   s.current_class, s.school_name,
                   sc.name AS school_display_name
            FROM users u
            LEFT JOIN students s ON s.user_id = u.id
            LEFT JOIN schools  sc ON sc.id = u.school_id
            WHERE u.id = ? AND u.role = 'student'
            LIMIT 1
        ");
        $stmt->execute([$studentUserId]);
        $student = $stmt->fetch();
        if (!$student) return null;

        // ---- Date window ----
        $weekStartDate = new \DateTime($weekStart);
        $weekEndDate   = (clone $weekStartDate)->modify('+6 days');
        $weekStartStr  = $weekStartDate->format('Y-m-d');
        $weekEndStr    = $weekEndDate->format('Y-m-d');

        // ---- Attendance in the window ----
        $stmt = $db->prepare("
            SELECT status, COUNT(*) AS cnt
            FROM attendance
            WHERE student_user_id = ?
              AND attendance_date BETWEEN ? AND ?
            GROUP BY status
        ");
        $stmt->execute([$studentUserId, $weekStartStr, $weekEndStr]);
        $att = ['present' => 0, 'absent' => 0, 'late' => 0, 'excused' => 0];
        foreach ($stmt->fetchAll() as $r) {
            $att[$r['status']] = (int)$r['cnt'];
        }
        $attTotal = array_sum($att);
        $presentPct = $attTotal > 0 ? (int)round($att['present'] / $attTotal * 100) : null;

        // ---- New certificates issued this week ----
        $stmt = $db->prepare("
            SELECT id, title, certificate_type, certificate_url, issued_at
            FROM certificates
            WHERE student_user_id = ?
              AND DATE(issued_at) BETWEEN ? AND ?
            ORDER BY issued_at DESC
        ");
        $stmt->execute([$studentUserId, $weekStartStr, $weekEndStr]);
        $certificates = $stmt->fetchAll();

        // ---- New activity results this week ----
        $stmt = $db->prepare("
            SELECT ar.id, ar.score, ar.position, ar.remarks, ar.recorded_at,
                   a.title AS activity_title, a.category AS activity_category
            FROM activity_results ar
            JOIN activities a ON a.id = ar.activity_id
            WHERE ar.student_user_id = ?
              AND DATE(ar.recorded_at) BETWEEN ? AND ?
            ORDER BY ar.recorded_at DESC
        ");
        $stmt->execute([$studentUserId, $weekStartStr, $weekEndStr]);
        $results = $stmt->fetchAll();

        // ---- Upcoming assignments (across all their batches) ----
        $stmt = $db->prepare("
            SELECT a.id, a.scheduled_date, a.scheduled_time, a.due_date, a.instructions,
                   act.title AS activity_title, act.category AS activity_category,
                   act.slug AS activity_slug, b.name AS batch_name
            FROM batch_activity_assignments a
            JOIN activities act ON act.id = a.activity_id
            JOIN batches b       ON b.id  = a.batch_id
            JOIN batch_students bs ON bs.batch_id = a.batch_id
            WHERE bs.student_user_id = ?
              AND a.status IN ('scheduled','in_progress')
              AND (a.due_date IS NULL OR a.due_date >= CURDATE())
            ORDER BY a.scheduled_date IS NULL, a.scheduled_date ASC
            LIMIT 5
        ");
        $stmt->execute([$studentUserId]);
        $assignments = $stmt->fetchAll();

        // ---- Upcoming activities (school + global, published, future) ----
        $stmt = $db->prepare("
            SELECT a.id, a.title, a.slug, a.category, a.event_date, a.venue
            FROM activities a
            WHERE a.status = 'published'
              AND (a.event_date IS NULL OR a.event_date >= CURDATE())
              AND (a.school_id IS NULL OR a.school_id = (SELECT school_id FROM users WHERE id = ?))
            ORDER BY a.featured DESC, a.event_date ASC
            LIMIT 5
        ");
        $stmt->execute([$studentUserId]);
        $upcomingActivities = $stmt->fetchAll();

        return [
            'student'             => $student,
            'week_start'          => $weekStartStr,
            'week_end'            => $weekEndStr,
            'attendance'          => $att,
            'attendance_total'    => $attTotal,
            'attendance_pct'      => $presentPct,
            'certificates'        => $certificates,
            'results'             => $results,
            'assignments'         => $assignments,
            'upcoming_activities' => $upcomingActivities,
        ];
    }

    /**
     * Find every parent with a valid email + their associated students.
     * Returns rows: [parent_id, parent_name, parent_email, student_user_id].
     */
    public static function eligibleParents(): array
    {
        $db = Database::connect();
        $stmt = $db->query("
            SELECT p.id AS parent_id, p.full_name AS parent_name, p.email AS parent_email,
                   sp.student_user_id,
                   u.status AS student_status
            FROM parents p
            JOIN student_parents sp ON sp.parent_id = p.id
            JOIN users u ON u.id = sp.student_user_id
            WHERE p.email IS NOT NULL
              AND p.email <> ''
              AND u.status = 'active'
              AND u.role = 'student'
            ORDER BY p.id
        ");
        return $stmt->fetchAll();
    }

    /**
     * Group eligible parents by parent_id → list of student IDs.
     * Returns: [ [parent_id, parent_name, parent_email, student_ids[]], ... ]
     */
    public static function groupedByParent(): array
    {
        $rows = self::eligibleParents();
        $byParent = [];
        foreach ($rows as $r) {
            $pid = (int)$r['parent_id'];
            if (!isset($byParent[$pid])) {
                $byParent[$pid] = [
                    'parent_id'    => $pid,
                    'parent_name'  => $r['parent_name'],
                    'parent_email' => $r['parent_email'],
                    'student_ids'  => [],
                ];
            }
            $byParent[$pid]['student_ids'][] = (int)$r['student_user_id'];
        }
        return array_values($byParent);
    }

    /**
     * Was this parent already sent a summary for this student + week?
     */
    public static function alreadySent(int $parentId, int $studentUserId, string $weekStart): bool
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT 1 FROM parent_email_log
            WHERE parent_id = ? AND student_user_id = ? AND week_start = ? AND status = 'sent'
            LIMIT 1
        ");
        $stmt->execute([$parentId, $studentUserId, $weekStart]);
        return (bool)$stmt->fetch();
    }

    /**
     * Record a send attempt. Uses ON DUPLICATE KEY UPDATE so a failed send
     * can be retried without creating a new row for the same week.
     */
    public static function recordSend(
        int $parentId,
        int $studentUserId,
        string $email,
        string $weekStart,
        bool $success,
        ?string $errorMessage = null
    ): void {
        $db = Database::connect();
        $db->prepare("
            INSERT INTO parent_email_log
                (parent_id, student_user_id, email, week_start, sent_at, status, error_message)
            VALUES (?, ?, ?, ?, NOW(), ?, ?)
            ON DUPLICATE KEY UPDATE
                sent_at = NOW(),
                status = VALUES(status),
                error_message = VALUES(error_message)
        ")->execute([
            $parentId,
            $studentUserId,
            $email,
            $weekStart,
            $success ? 'sent' : 'failed',
            $errorMessage ? mb_substr($errorMessage, 0, 500) : null,
        ]);
    }

    /**
     * Return the most recent completed Monday (the start of the current ISO week's prior week).
     * If today is Monday, returns today.
     */
    public static function defaultWeekStart(): string
    {
        $d = new \DateTime('monday this week');
        if ($d > new \DateTime('today')) {
            $d->modify('-7 days');
        }
        return $d->format('Y-m-d');
    }
}