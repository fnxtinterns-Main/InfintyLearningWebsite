<?php
declare(strict_types=1);

namespace App;

use PDO;

final class TeacherNote
{
    private static bool $bootstrapped = false;

    public static function ensureTable(): void
    {
        if (self::$bootstrapped) return;
        try {
            $db = Database::connect();
            $db->exec("
                CREATE TABLE IF NOT EXISTS `teacher_notes` (
                  `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `teacher_user_id` BIGINT(20) UNSIGNED NOT NULL,
                  `school_id` BIGINT(20) UNSIGNED DEFAULT NULL,
                  `student_user_id` BIGINT(20) UNSIGNED DEFAULT NULL,
                  `batch_id` BIGINT(20) UNSIGNED DEFAULT NULL,
                  `title` VARCHAR(255) NOT NULL,
                  `content` TEXT NOT NULL,
                  `category` VARCHAR(50) DEFAULT 'general',
                  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `idx_tn_teacher` (`teacher_user_id`),
                  KEY `idx_tn_school` (`school_id`),
                  KEY `idx_tn_student` (`student_user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            self::$bootstrapped = true;
        } catch (\Throwable $e) {
            error_log('TeacherNote::ensureTable error: ' . $e->getMessage());
        }
    }

    public static function create(int $teacherUserId, ?int $schoolId, string $title, string $content, ?int $studentUserId = null, ?int $batchId = null, string $category = 'general'): int
    {
        self::ensureTable();
        $db = Database::connect();
        $stmt = $db->prepare("
            INSERT INTO teacher_notes (teacher_user_id, school_id, student_user_id, batch_id, title, content, category)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $teacherUserId,
            $schoolId ?: null,
            $studentUserId ?: null,
            $batchId ?: null,
            trim($title),
            trim($content),
            trim($category) ?: 'general'
        ]);
        return (int)$db->lastInsertId();
    }

    public static function listForTeacher(int $teacherUserId): array
    {
        self::ensureTable();
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT tn.*, 
                   COALESCE(st.full_name, 'General Note') AS student_name,
                   st.current_class,
                   b.name AS batch_name
            FROM teacher_notes tn
            LEFT JOIN students st ON st.user_id = tn.student_user_id
            LEFT JOIN batches b ON b.id = tn.batch_id
            WHERE tn.teacher_user_id = ?
            ORDER BY tn.created_at DESC
        ");
        $stmt->execute([$teacherUserId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function listForSchool(int $schoolId): array
    {
        self::ensureTable();
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT tn.*, 
                   COALESCE(tc.full_name, u.email, 'Faculty') AS teacher_name,
                   tc.expert_field,
                   COALESCE(st.full_name, 'General Note') AS student_name,
                   st.current_class,
                   b.name AS batch_name
            FROM teacher_notes tn
            JOIN users u ON u.id = tn.teacher_user_id
            LEFT JOIN teachers tc ON tc.user_id = tn.teacher_user_id
            LEFT JOIN students st ON st.user_id = tn.student_user_id
            LEFT JOIN batches b ON b.id = tn.batch_id
            WHERE tn.school_id = ? OR u.school_id = ?
            ORDER BY tn.created_at DESC
        ");
        $stmt->execute([$schoolId, $schoolId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function delete(int $noteId, int $userId, bool $isAdmin = false): bool
    {
        self::ensureTable();
        $db = Database::connect();
        if ($isAdmin) {
            $stmt = $db->prepare("DELETE FROM teacher_notes WHERE id = ?");
            return $stmt->execute([$noteId]);
        }
        $stmt = $db->prepare("DELETE FROM teacher_notes WHERE id = ? AND teacher_user_id = ?");
        return $stmt->execute([$noteId, $userId]);
    }
}
