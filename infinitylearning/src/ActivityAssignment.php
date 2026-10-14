<?php
declare(strict_types=1);

namespace App;

final class ActivityAssignment
{
    public static function assign(array $d, int $assignedBy): int
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            INSERT INTO batch_activity_assignments
                (batch_id, activity_id, assigned_by, scheduled_date, scheduled_time,
                 duration_minutes, instructions, due_date, status)
            VALUES (?,?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            (int)$d['batch_id'],
            (int)$d['activity_id'],
            $assignedBy,
            $d['scheduled_date'] ?: null,
            $d['scheduled_time'] ?: null,
            !empty($d['duration_minutes']) ? (int)$d['duration_minutes'] : null,
            trim($d['instructions'] ?? '') ?: null,
            $d['due_date'] ?: null,
            $d['status'] ?? 'scheduled',
        ]);
        $id = (int)$db->lastInsertId();
        Audit::log('activity.assigned', 'batch', (int)$d['batch_id'], [
            'activity_id' => (int)$d['activity_id'],
            'assignment_id' => $id,
        ]);
        return $id;
    }

    public static function update(int $id, array $d): void
    {
        Database::connect()->prepare("
            UPDATE batch_activity_assignments SET
                scheduled_date=?, scheduled_time=?, duration_minutes=?,
                instructions=?, due_date=?, status=?
            WHERE id=?
        ")->execute([
            $d['scheduled_date'] ?: null,
            $d['scheduled_time'] ?: null,
            !empty($d['duration_minutes']) ? (int)$d['duration_minutes'] : null,
            trim($d['instructions'] ?? '') ?: null,
            $d['due_date'] ?: null,
            $d['status'] ?? 'scheduled',
            $id,
        ]);
        Audit::log('activity.assignment_updated', 'assignment', $id);
    }

    public static function cancel(int $id): void
    {
        Database::connect()
            ->prepare("UPDATE batch_activity_assignments SET status='cancelled' WHERE id=?")
            ->execute([$id]);
        Audit::log('activity.assignment_cancelled', 'assignment', $id);
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connect()->prepare("
            SELECT a.*, act.title AS activity_title, act.slug AS activity_slug,
                   act.category AS activity_category, act.cover_image AS activity_cover,
                   b.name AS batch_name, b.subject AS batch_subject,
                   t.full_name AS assigned_by_name
            FROM batch_activity_assignments a
            JOIN activities act ON act.id = a.activity_id
            JOIN batches b       ON b.id  = a.batch_id
            LEFT JOIN users u    ON u.id  = a.assigned_by
            LEFT JOIN teachers t ON t.user_id = u.id
            WHERE a.id = ?
            LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    /** All assignments for a batch (teacher view). */
    public static function forBatch(int $batchId): array
    {
        $stmt = Database::connect()->prepare("
            SELECT a.*, act.title AS activity_title, act.slug AS activity_slug,
                   act.category AS activity_category, act.cover_image AS activity_cover,
                   t.full_name AS assigned_by_name,
                   (SELECT COUNT(*) FROM batch_students bs WHERE bs.batch_id = a.batch_id) AS student_count,
                   (SELECT COUNT(*) FROM batch_assignment_acknowledgments ack WHERE ack.assignment_id = a.id) AS acknowledged_count
            FROM batch_activity_assignments a
            JOIN activities act ON act.id = a.activity_id
            LEFT JOIN users u    ON u.id  = a.assigned_by
            LEFT JOIN teachers t ON t.user_id = u.id
            WHERE a.batch_id = ?
            ORDER BY a.scheduled_date DESC, a.created_at DESC
        ");
        $stmt->execute([$batchId]);
        return $stmt->fetchAll();
    }

    /**
     * Upcoming assignments for a student (across all their batches).
     * Sorted with the nearest upcoming first.
     */
    public static function upcomingForStudent(int $studentUserId, int $limit = 5): array
    {
        $stmt = Database::connect()->prepare("
            SELECT a.*, act.title AS activity_title, act.slug AS activity_slug,
                   act.category AS activity_category, act.cover_image AS activity_cover,
                   act.short_description AS activity_short,
                   b.name AS batch_name,
                   t.full_name AS assigned_by_name,
                   (SELECT 1 FROM batch_assignment_acknowledgments ack
                    WHERE ack.assignment_id = a.id AND ack.student_user_id = ?) AS acknowledged
            FROM batch_activity_assignments a
            JOIN activities act ON act.id = a.activity_id
            JOIN batches b       ON b.id  = a.batch_id
            JOIN batch_students bs ON bs.batch_id = a.batch_id
            LEFT JOIN users u    ON u.id  = a.assigned_by
            LEFT JOIN teachers t ON t.user_id = u.id
            WHERE bs.student_user_id = ?
              AND a.status IN ('scheduled','in_progress')
              AND (a.due_date IS NULL OR a.due_date >= CURDATE())
            ORDER BY
              a.scheduled_date IS NULL,
              a.scheduled_date ASC,
              a.created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $studentUserId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $studentUserId, \PDO::PARAM_INT);
        $stmt->bindValue(3, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Past assignments (completed or past due) for a student. */
    public static function pastForStudent(int $studentUserId, int $limit = 5): array
    {
        $stmt = Database::connect()->prepare("
            SELECT a.*, act.title AS activity_title, act.slug AS activity_slug,
                   act.category AS activity_category, b.name AS batch_name
            FROM batch_activity_assignments a
            JOIN activities act ON act.id = a.activity_id
            JOIN batches b       ON b.id  = a.batch_id
            JOIN batch_students bs ON bs.batch_id = a.batch_id
            WHERE bs.student_user_id = ?
              AND (a.status IN ('completed','cancelled')
                   OR (a.due_date IS NOT NULL AND a.due_date < CURDATE()))
            ORDER BY a.due_date DESC, a.created_at DESC
            LIMIT ?
        ");
        $stmt->bindValue(1, $studentUserId, \PDO::PARAM_INT);
        $stmt->bindValue(2, $limit, \PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function acknowledge(int $assignmentId, int $studentUserId): bool
    {
        try {
            Database::connect()->prepare("
                INSERT INTO batch_assignment_acknowledgments (assignment_id, student_user_id)
                VALUES (?, ?)
            ")->execute([$assignmentId, $studentUserId]);
            return true;
        } catch (\PDOException $e) {
            return false; // already acknowledged
        }
    }

    /** Students in the batch who haven't acknowledged yet. */
    public static function notAcknowledged(int $assignmentId, int $batchId): array
    {
        $stmt = Database::connect()->prepare("
            SELECT u.id, s.full_name, u.email
            FROM batch_students bs
            JOIN users u ON u.id = bs.student_user_id
            LEFT JOIN students s ON s.user_id = u.id
            WHERE bs.batch_id = ?
              AND u.id NOT IN (
                  SELECT student_user_id FROM batch_assignment_acknowledgments
                  WHERE assignment_id = ?
              )
            ORDER BY s.full_name
        ");
        $stmt->execute([$batchId, $assignmentId]);
        return $stmt->fetchAll();
    }
}