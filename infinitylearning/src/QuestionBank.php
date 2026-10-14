<?php
declare(strict_types=1);

namespace App;

use PDO;

final class QuestionBank
{
    private static bool $bootstrapped = false;

    public static function ensureTable(bool $force = false): void
    {
        if (self::$bootstrapped && !$force) return;
        self::$bootstrapped = true;

        if ($force) {
            $db = Database::connect();
            $db->exec("
                CREATE TABLE IF NOT EXISTS `question_bank` (
                  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `uuid` char(36) NOT NULL,
                  `teacher_user_id` bigint(20) UNSIGNED NOT NULL,
                  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
                  `subject_id` bigint(20) UNSIGNED NOT NULL,
                  `chapter_id` bigint(20) UNSIGNED DEFAULT NULL,
                  `topic_id` bigint(20) UNSIGNED DEFAULT NULL,
                  `class_level` varchar(50) DEFAULT NULL,
                  `question_text` longtext NOT NULL,
                  `question_type` enum('mcq','short_answer','descriptive','numerical','true_false') NOT NULL DEFAULT 'mcq',
                  `options_json` longtext DEFAULT NULL,
                  `correct_answer` text DEFAULT NULL,
                  `explanation` text DEFAULT NULL,
                  `marks` decimal(4,1) NOT NULL DEFAULT 1.0,
                  `difficulty` enum('easy','medium','hard') NOT NULL DEFAULT 'medium',
                  `created_at` datetime DEFAULT current_timestamp(),
                  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `uk_qb_uuid` (`uuid`),
                  KEY `idx_qb_teacher` (`teacher_user_id`),
                  KEY `idx_qb_subject` (`subject_id`),
                  KEY `idx_qb_chapter` (`chapter_id`),
                  KEY `idx_qb_topic` (`topic_id`),
                  KEY `idx_qb_school` (`school_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        }
    }

    private static bool $imageColChecked = false;

    public static function ensureImageColumn(): void
    {
        if (self::$imageColChecked) return;
        self::$imageColChecked = true;
        try {
            $db = Database::connect();
            $db->query("SELECT image_url FROM question_bank LIMIT 0");
        } catch (\Throwable $e) {
            try {
                $db = Database::connect();
                $db->exec("ALTER TABLE question_bank ADD COLUMN `image_url` varchar(255) DEFAULT NULL AFTER `options_json`");
            } catch (\Throwable $ex) {}
        }
    }

    public static function create(array $d, int $teacherId, ?int $schoolId = null): int
    {
        $db = Database::connect();
        $uuid = Auth::uuid();

        self::ensureImageColumn();

        $optionsJson = null;
        if (!empty($d['options']) && is_array($d['options'])) {
            $optionsJson = json_encode(array_values(array_filter($d['options'])));
        } elseif (!empty($d['options_json'])) {
            $optionsJson = is_string($d['options_json']) ? $d['options_json'] : json_encode($d['options_json']);
        }

        $imageUrl = !empty($d['image_url']) ? trim((string)$d['image_url']) : null;

        $stmt = $db->prepare("
            INSERT INTO question_bank
            (uuid, teacher_user_id, school_id, subject_id, chapter_id, topic_id,
             class_level, question_text, question_type, options_json, image_url, correct_answer,
             explanation, marks, difficulty)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $uuid,
            $teacherId,
            $schoolId,
            (int)$d['subject_id'],
            !empty($d['chapter_id']) ? (int)$d['chapter_id'] : null,
            !empty($d['topic_id']) ? (int)$d['topic_id'] : null,
            trim($d['class_level'] ?? ''),
            trim($d['question_text']),
            $d['question_type'] ?? 'mcq',
            $optionsJson,
            $imageUrl,
            trim($d['correct_answer'] ?? ''),
            trim($d['explanation'] ?? ''),
            (float)($d['marks'] ?? 1.0),
            $d['difficulty'] ?? 'medium'
        ]);

        return (int)$db->lastInsertId();
    }

    public static function update(int $id, array $d, int $teacherId): bool
    {
        $db = Database::connect();

        self::ensureImageColumn();

        $optionsJson = null;
        if (!empty($d['options']) && is_array($d['options'])) {
            $optionsJson = json_encode(array_values(array_filter($d['options'])));
        } elseif (!empty($d['options_json'])) {
            $optionsJson = is_string($d['options_json']) ? $d['options_json'] : json_encode($d['options_json']);
        }

        $imageUrl = !empty($d['image_url']) ? trim((string)$d['image_url']) : null;

        $isSuperAdmin = (Auth::check() && Auth::isSuperAdmin()) ? 1 : 0;
        $schoolId = (Auth::check() && Auth::schoolId()) ? (int)Auth::schoolId() : 0;

        $stmt = $db->prepare("
            UPDATE question_bank SET
                subject_id = ?,
                chapter_id = ?,
                topic_id = ?,
                class_level = ?,
                question_text = ?,
                question_type = ?,
                options_json = ?,
                image_url = ?,
                correct_answer = ?,
                explanation = ?,
                marks = ?,
                difficulty = ?
            WHERE id = ? AND (teacher_user_id = ? OR (? > 0 AND school_id = ?) OR ? = 1)
        ");

        return $stmt->execute([
            (int)$d['subject_id'],
            !empty($d['chapter_id']) ? (int)$d['chapter_id'] : null,
            !empty($d['topic_id']) ? (int)$d['topic_id'] : null,
            trim($d['class_level'] ?? ''),
            trim($d['question_text']),
            $d['question_type'] ?? 'mcq',
            $optionsJson,
            $imageUrl,
            trim($d['correct_answer'] ?? ''),
            trim($d['explanation'] ?? ''),
            (float)($d['marks'] ?? 1.0),
            $d['difficulty'] ?? 'medium',
            $id,
            $teacherId,
            $schoolId,
            $schoolId,
            $isSuperAdmin
        ]);
    }

    public static function delete(int $id, int $teacherId): bool
    {
        $db = Database::connect();
        $isSuperAdmin = (Auth::check() && Auth::isSuperAdmin()) ? 1 : 0;
        $schoolId = (Auth::check() && Auth::schoolId()) ? (int)Auth::schoolId() : 0;

        $stmt = $db->prepare("
            DELETE FROM question_bank 
            WHERE id = ? 
              AND (teacher_user_id = ? OR (? > 0 AND school_id = ?) OR ? = 1)
        ");
        return $stmt->execute([$id, $teacherId, $schoolId, $schoolId, $isSuperAdmin]);
    }

    public static function find(int $id): ?array
    {
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT q.*, s.name AS subject_name, c.name AS chapter_name, t.name AS topic_name,
                   COALESCE(tch.full_name, u.email, 'Teacher') AS teacher_name
            FROM question_bank q
            LEFT JOIN subjects s ON s.id = q.subject_id
            LEFT JOIN chapters c ON c.id = q.chapter_id
            LEFT JOIN topics t ON t.id = q.topic_id
            LEFT JOIN users u ON u.id = q.teacher_user_id
            LEFT JOIN teachers tch ON tch.user_id = u.id
            WHERE q.id = ? LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function list(array $filters = []): array
    {
        $db = Database::connect();

        $where = ['1=1'];
        $params = [];

        if (!empty($filters['exact_teacher_id'])) {
            $where[] = 'q.teacher_user_id = ?';
            $params[] = (int)$filters['exact_teacher_id'];
        } elseif (!empty($filters['teacher_user_id'])) {
            $where[] = '(q.teacher_user_id = ? OR q.school_id = ?)';
            $params[] = (int)$filters['teacher_user_id'];
            $params[] = (int)($filters['school_id'] ?? 0);
        } elseif (!empty($filters['school_id'])) {
            $where[] = 'q.school_id = ?';
            $params[] = (int)$filters['school_id'];
        }

        if (!empty($filters['subject_id'])) {
            $where[] = 'q.subject_id = ?';
            $params[] = (int)$filters['subject_id'];
        }

        if (!empty($filters['chapter_id'])) {
            $where[] = 'q.chapter_id = ?';
            $params[] = (int)$filters['chapter_id'];
        }

        if (!empty($filters['class_level'])) {
            $where[] = 'q.class_level = ?';
            $params[] = $filters['class_level'];
        }

        if (!empty($filters['difficulty'])) {
            $where[] = 'q.difficulty = ?';
            $params[] = $filters['difficulty'];
        }

        if (!empty($filters['question_type'])) {
            $where[] = 'q.question_type = ?';
            $params[] = $filters['question_type'];
        }

        if (!empty($filters['search'])) {
            $where[] = '(q.question_text LIKE ? OR q.explanation LIKE ?)';
            $term = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
        }

        $sql = "
            SELECT q.*, s.name AS subject_name, c.name AS chapter_name, t.name AS topic_name,
                   COALESCE(tch.full_name, u.email, 'Teacher') AS teacher_name
            FROM question_bank q
            LEFT JOIN subjects s ON s.id = q.subject_id
            LEFT JOIN chapters c ON c.id = q.chapter_id
            LEFT JOIN topics t ON t.id = q.topic_id
            LEFT JOIN users u ON u.id = q.teacher_user_id
            LEFT JOIN teachers tch ON tch.user_id = u.id
            WHERE " . implode(' AND ', $where) . "
            ORDER BY q.created_at DESC
        ";

        if (!empty($filters['limit'])) {
            $sql .= ' LIMIT ' . (int)$filters['limit'];
        }

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
