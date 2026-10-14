<?php
declare(strict_types=1);

namespace App;

final class Batch
{
    public static function all(?int $teacherUserId = null): array
    {
        $db = Database::connect();
        if ($teacherUserId) {
            $stmt = $db->prepare("
                SELECT b.*, u.email AS teacher_email, t.full_name AS teacher_name,
                       (SELECT COUNT(*) FROM batch_students bs WHERE bs.batch_id=b.id) AS student_count
                FROM batches b
                LEFT JOIN users u ON u.id = b.teacher_id
                LEFT JOIN teachers t ON t.user_id = u.id
                WHERE b.teacher_id = ? AND b.status='active'
                ORDER BY b.created_at DESC
            ");
            $stmt->execute([$teacherUserId]);
        } else {
            $stmt = $db->query("
                SELECT b.*, u.email AS teacher_email, t.full_name AS teacher_name,
                       (SELECT COUNT(*) FROM batch_students bs WHERE bs.batch_id=b.id) AS student_count
                FROM batches b
                LEFT JOIN users u ON u.id = b.teacher_id
                LEFT JOIN teachers t ON t.user_id = u.id
                WHERE b.status='active'
                ORDER BY b.created_at DESC
            ");
        }
        return $stmt->fetchAll();
    }

    /** All batches regardless of status — admin view. */
    public static function allWithArchived(): array
    {
        return Database::connect()->query("
            SELECT b.*, u.email AS teacher_email, t.full_name AS teacher_name,
                   (SELECT COUNT(*) FROM batch_students bs WHERE bs.batch_id=b.id) AS student_count
            FROM batches b
            LEFT JOIN users u ON u.id = b.teacher_id
            LEFT JOIN teachers t ON t.user_id = u.id
            ORDER BY b.created_at DESC
        ")->fetchAll();
    }

    public static function find(int $id): ?array
    {
        $stmt = Database::connect()->prepare("
            SELECT b.*, t.full_name AS teacher_name, u.email AS teacher_email
            FROM batches b
            LEFT JOIN users u ON u.id = b.teacher_id
            LEFT JOIN teachers t ON t.user_id = u.id
            WHERE b.id = ? LIMIT 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch() ?: null;
    }

    public static function create(array $d, int $createdBy): int
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            INSERT INTO batches
                (uuid, name, description, subject, class_level, teacher_id, capacity, created_by)
            VALUES (?,?,?,?,?,?,?,?)
        ");
        $stmt->execute([
            Auth::uuid(),
            trim($d['name']),
            trim($d['description'] ?? '') ?: null,
            trim($d['subject'] ?? '') ?: null,
            trim($d['class_level'] ?? '') ?: null,
            !empty($d['teacher_id']) ? (int)$d['teacher_id'] : null,
            !empty($d['capacity'])   ? (int)$d['capacity']   : 50,
            $createdBy,
        ]);
        $id = (int)$db->lastInsertId();
        Audit::log('batch.created', 'batch', $id, ['name' => $d['name']]);
        return $id;
    }

    public static function update(int $id, array $d): void
    {
        Database::connect()->prepare("
            UPDATE batches SET name=?, description=?, subject=?, class_level=?, teacher_id=?, capacity=?
            WHERE id=?
        ")->execute([
            trim($d['name']),
            trim($d['description'] ?? '') ?: null,
            trim($d['subject'] ?? '') ?: null,
            trim($d['class_level'] ?? '') ?: null,
            !empty($d['teacher_id']) ? (int)$d['teacher_id'] : null,
            !empty($d['capacity'])   ? (int)$d['capacity']   : 50,
            $id,
        ]);
        Audit::log('batch.updated', 'batch', $id);
    }

    public static function archive(int $id): void
    {
        Database::connect()->prepare("UPDATE batches SET status='archived' WHERE id=?")->execute([$id]);
        Audit::log('batch.archived', 'batch', $id);
    }

    public static function restore(int $id): void
    {
        Database::connect()->prepare("UPDATE batches SET status='active' WHERE id=?")->execute([$id]);
        Audit::log('batch.restored', 'batch', $id);
    }

    /** Hard-delete (admin only). */
    public static function delete(int $id): void
    {
        Database::connect()->prepare("DELETE FROM batches WHERE id=?")->execute([$id]);
        Audit::log('batch.deleted', 'batch', $id);
    }

    public static function forStudent(int $studentUserId): array
    {
        $stmt = Database::connect()->prepare("
            SELECT b.*, t.full_name AS teacher_name
            FROM batches b
            JOIN batch_students bs ON bs.batch_id = b.id
            LEFT JOIN users u ON u.id = b.teacher_id
            LEFT JOIN teachers t ON t.user_id = u.id
            WHERE bs.student_user_id = ? AND b.status='active'
            ORDER BY b.created_at DESC
        ");
        $stmt->execute([$studentUserId]);
        return $stmt->fetchAll();
    }

    public static function members(int $batchId): array
    {
        $stmt = Database::connect()->prepare("
            SELECT u.id AS user_id, u.email, u.phone, s.full_name, s.profile_image,
                   s.current_class, bs.joined_at
            FROM batch_students bs
            JOIN users u ON u.id = bs.student_user_id
            LEFT JOIN students s ON s.user_id = u.id
            WHERE bs.batch_id = ?
            ORDER BY s.full_name
        ");
        $stmt->execute([$batchId]);
        return $stmt->fetchAll();
    }

    public static function addStudent(int $batchId, int $studentUserId): bool
    {
        try {
            Database::connect()
                ->prepare("INSERT INTO batch_students (batch_id, student_user_id) VALUES (?,?)")
                ->execute([$batchId, $studentUserId]);
            Audit::log('batch.student_added', 'batch', $batchId, ['student' => $studentUserId]);
            return true;
        } catch (\PDOException $e) {
            return false;
        }
    }

    public static function removeStudent(int $batchId, int $studentUserId): bool
    {
        $stmt = Database::connect()
            ->prepare("DELETE FROM batch_students WHERE batch_id = ? AND student_user_id = ?");
        $stmt->execute([$batchId, $studentUserId]);
        Audit::log('batch.student_removed', 'batch', $batchId, ['student' => $studentUserId]);
        return $stmt->rowCount() > 0;
    }

    public static function searchStudents(int $batchId, string $q): array
    {
        $db = Database::connect();
        $sql = "
            SELECT u.id AS user_id, u.email, u.phone, s.full_name, s.current_class, s.school_name
            FROM users u
            JOIN students s ON s.user_id = u.id
            WHERE u.role = 'student' AND u.status = 'active'
              AND u.id NOT IN (SELECT student_user_id FROM batch_students WHERE batch_id = ?)
        ";
        $params = [$batchId];
        if ($q !== '') {
            $sql .= " AND (s.full_name LIKE ? OR u.email LIKE ? OR u.phone LIKE ?)";
            $like = '%' . $q . '%';
            $params[] = $like; $params[] = $like; $params[] = $like;
        }
        $sql .= " ORDER BY s.full_name LIMIT 25";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function stats(int $batchId): array
    {
        $db = Database::connect();
        return [
            'students' => (int)$db->query("SELECT COUNT(*) FROM batch_students WHERE batch_id = " . (int)$batchId)->fetchColumn(),
            'lessons'  => (int)$db->query("SELECT COUNT(*) FROM lessons       WHERE batch_id = " . (int)$batchId)->fetchColumn(),
        ];
    }

    /** Approved teachers available to assign. */
    public static function availableTeachers(): array
    {
        return Database::connect()->query("
            SELECT u.id, t.full_name, u.email, t.expert_field
            FROM users u
            LEFT JOIN teachers t ON t.user_id = u.id
            WHERE u.role = 'teacher' AND u.status = 'active'
            ORDER BY t.full_name, u.email
        ")->fetchAll();
    }
}