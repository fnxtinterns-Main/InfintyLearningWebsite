<?php
declare(strict_types=1);

namespace App;

use PDO;

final class DoubtFeed
{
    private static bool $bootstrapped = false;

    public static function ensureTable(): void
    {
        if (self::$bootstrapped) return;
        try {
            $db = Database::connect();
            $db->exec("
                CREATE TABLE IF NOT EXISTS `lesson_doubts` (
                  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `uuid` char(36) NOT NULL,
                  `lesson_id` bigint(20) UNSIGNED NOT NULL,
                  `student_user_id` bigint(20) UNSIGNED NOT NULL,
                  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
                  `batch_id` bigint(20) UNSIGNED DEFAULT NULL,
                  `title` varchar(255) NOT NULL,
                  `body` text NOT NULL,
                  `attachment_url` varchar(500) DEFAULT NULL,
                  `status` enum('open','answered','resolved') NOT NULL DEFAULT 'open',
                  `replies_count` int(10) UNSIGNED NOT NULL DEFAULT 0,
                  `created_at` datetime DEFAULT current_timestamp(),
                  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                  PRIMARY KEY (`id`),
                  UNIQUE KEY `uk_ld_uuid` (`uuid`),
                  KEY `idx_ld_lesson` (`lesson_id`),
                  KEY `idx_ld_student` (`student_user_id`),
                  KEY `idx_ld_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            $db->exec("
                CREATE TABLE IF NOT EXISTS `lesson_doubt_replies` (
                  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
                  `doubt_id` bigint(20) UNSIGNED NOT NULL,
                  `user_id` bigint(20) UNSIGNED NOT NULL,
                  `user_role` enum('student','teacher','admin') NOT NULL,
                  `reply_text` text NOT NULL,
                  `is_teacher_endorsed` tinyint(1) NOT NULL DEFAULT 0,
                  `created_at` datetime DEFAULT current_timestamp(),
                  PRIMARY KEY (`id`),
                  KEY `idx_ldr_doubt` (`doubt_id`),
                  KEY `idx_ldr_user` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");
            // Automatically backfill any existing doubts with missing school_id
            $db->exec("
                UPDATE lesson_doubts d
                JOIN lessons l ON l.id = d.lesson_id
                SET d.school_id = l.school_id
                WHERE (d.school_id IS NULL OR d.school_id = 0) AND l.school_id IS NOT NULL AND l.school_id > 0
            ");
            $db->exec("
                UPDATE lesson_doubts d
                JOIN users u ON u.id = d.student_user_id
                SET d.school_id = u.school_id
                WHERE (d.school_id IS NULL OR d.school_id = 0) AND u.school_id IS NOT NULL AND u.school_id > 0
            ");
            self::$bootstrapped = true;
        } catch (\Throwable $e) {
            error_log('DoubtFeed ensureTable error: ' . $e->getMessage());
        }
    }

    public static function askDoubt(int $lessonId, int $studentId, string $title, string $body, ?string $attachmentUrl = null, ?int $schoolId = null, ?int $batchId = null): int
    {
        self::ensureTable();
        $db = Database::connect();

        // 1. Auto-resolve school_id if empty
        if (empty($schoolId) && $lessonId > 0) {
            $lStmt = $db->prepare("SELECT school_id FROM lessons WHERE id = ?");
            $lStmt->execute([$lessonId]);
            $sCol = $lStmt->fetchColumn();
            if ($sCol) {
                $schoolId = (int)$sCol;
            }
        }
        if (empty($schoolId) && $studentId > 0) {
            $uStmt = $db->prepare("SELECT school_id FROM users WHERE id = ?");
            $uStmt->execute([$studentId]);
            $sCol = $uStmt->fetchColumn();
            if ($sCol) {
                $schoolId = (int)$sCol;
            }
        }

        // 2. Auto-resolve batch_id if empty
        if (empty($batchId) && $studentId > 0) {
            if ($lessonId > 0) {
                $lbStmt = $db->prepare("SELECT batch_id FROM lessons WHERE id = ?");
                $lbStmt->execute([$lessonId]);
                $lbCol = $lbStmt->fetchColumn();
                if ($lbCol) {
                    $batchId = (int)$lbCol;
                }
            }
            if (empty($batchId)) {
                $bsStmt = $db->prepare("
                    SELECT bs.batch_id 
                    FROM batch_students bs
                    WHERE bs.student_user_id = ?
                    ORDER BY bs.id ASC LIMIT 1
                ");
                $bsStmt->execute([$studentId]);
                $bsCol = $bsStmt->fetchColumn();
                if ($bsCol) {
                    $batchId = (int)$bsCol;
                }
            }
        }

        $uuid = Auth::uuid();
        $stmt = $db->prepare("
            INSERT INTO lesson_doubts
            (uuid, lesson_id, student_user_id, school_id, batch_id, title, body, attachment_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $uuid,
            $lessonId,
            $studentId,
            $schoolId,
            $batchId,
            trim($title),
            trim($body),
            $attachmentUrl
        ]);

        Cache::forget("doubts:lesson:{$lessonId}");
        return (int)$db->lastInsertId();
    }

    public static function addReply(int $doubtId, int $userId, string $role, string $text): int
    {
        self::ensureTable();
        $db = Database::connect();
        $isEndorsed = ($role === 'teacher' || $role === 'admin') ? 1 : 0;

        $stmt = $db->prepare("
            INSERT INTO lesson_doubt_replies (doubt_id, user_id, user_role, reply_text, is_teacher_endorsed)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$doubtId, $userId, $role, trim($text), $isEndorsed]);
        $replyId = (int)$db->lastInsertId();

        $status = ($role === 'teacher' || $role === 'admin') ? 'answered' : 'open';
        $db->prepare("
            UPDATE lesson_doubts
            SET replies_count = replies_count + 1,
                status = CASE WHEN ? = 'answered' THEN 'answered' ELSE status END
            WHERE id = ?
        ")->execute([$status, $doubtId]);

        // Invalidate lesson doubts cache
        $lQ = $db->prepare("SELECT lesson_id FROM lesson_doubts WHERE id = ? LIMIT 1");
        $lQ->execute([$doubtId]);
        $lessonId = $lQ->fetchColumn();
        if ($lessonId) {
            Cache::forget("doubts:lesson:{$lessonId}");
        }

        return $replyId;
    }

    public static function listForLesson(int $lessonId): array
    {
        return Cache::remember("doubts:lesson:{$lessonId}", 600, function () use ($lessonId) {
            self::ensureTable();
            $db = Database::connect();
            $stmt = $db->prepare("
                SELECT d.*, st.full_name AS student_name, st.profile_image AS student_avatar
                FROM lesson_doubts d
                LEFT JOIN students st ON st.user_id = d.student_user_id
                WHERE d.lesson_id = ?
                ORDER BY d.created_at DESC
            ");
            $stmt->execute([$lessonId]);
            $doubts = $stmt->fetchAll();

            foreach ($doubts as &$d) {
                $replyStmt = $db->prepare("
                    SELECT r.*,
                           COALESCE(st.full_name, tc.full_name, 'Staff') AS author_name,
                           COALESCE(st.profile_image, tc.profile_image) AS author_avatar
                    FROM lesson_doubt_replies r
                    LEFT JOIN students st ON st.user_id = r.user_id AND r.user_role = 'student'
                    LEFT JOIN teachers tc ON tc.user_id = r.user_id AND r.user_role = 'teacher'
                    WHERE r.doubt_id = ?
                    ORDER BY r.created_at ASC
                ");
                $replyStmt->execute([(int)$d['id']]);
                $d['replies'] = $replyStmt->fetchAll();
            }

            return $doubts;
        });
    }

    public static function listForTeacher(int $teacherUserId, ?int $schoolId = null): array
    {
        self::ensureTable();
        $db = Database::connect();

        if ($schoolId === null) {
            $uStmt = $db->prepare("SELECT school_id FROM users WHERE id = ?");
            $uStmt->execute([$teacherUserId]);
            $sVal = $uStmt->fetchColumn();
            if ($sVal) {
                $schoolId = (int)$sVal;
            }
        }

        $whereConditions = [];
        $params = [];

        // 1. Lessons created by this teacher
        $whereConditions[] = 'l.created_by = ?';
        $params[] = $teacherUserId;

        // 2. Doubts in batches assigned to teacher (teacher_id as user_id or teacher record id)
        $whereConditions[] = 'd.batch_id IN (
            SELECT b.id FROM batches b 
            WHERE b.teacher_id = ? 
               OR b.teacher_id IN (SELECT t.id FROM teachers t WHERE t.user_id = ?)
        )';
        $params[] = $teacherUserId;
        $params[] = $teacherUserId;

        // 3. Doubts asked by students in batches taught by this teacher
        $whereConditions[] = 'd.student_user_id IN (
            SELECT bs.student_user_id 
            FROM batch_students bs
            JOIN batches b ON b.id = bs.batch_id
            WHERE b.teacher_id = ? 
               OR b.teacher_id IN (SELECT t.id FROM teachers t WHERE t.user_id = ?)
        )';
        $params[] = $teacherUserId;
        $params[] = $teacherUserId;

        // 4. Doubts or lessons belonging to the teacher\'s school
        if ($schoolId !== null && $schoolId > 0) {
            $whereConditions[] = 'd.school_id = ?';
            $params[] = $schoolId;
            $whereConditions[] = 'l.school_id = ?';
            $params[] = $schoolId;
        }

        $whereClause = '(' . implode(' OR ', $whereConditions) . ')';

        $stmt = $db->prepare("
            SELECT d.*, 
                   l.title AS lesson_title, 
                   l.created_by AS lesson_created_by,
                   sub.name AS subject_name,
                   st.full_name AS student_name, 
                   st.current_class,
                   st.profile_image AS student_avatar
            FROM lesson_doubts d
            JOIN lessons l ON l.id = d.lesson_id
            LEFT JOIN subjects sub ON sub.id = l.subject_id
            LEFT JOIN students st ON st.user_id = d.student_user_id
            WHERE {$whereClause}
            ORDER BY FIELD(d.status, 'open', 'answered', 'resolved'), d.created_at DESC
            LIMIT 100
        ");
        $stmt->execute($params);
        $doubts = $stmt->fetchAll();

        foreach ($doubts as &$d) {
            $replyStmt = $db->prepare("
                SELECT r.*,
                       COALESCE(st.full_name, tc.full_name, 'Staff') AS author_name,
                       COALESCE(st.profile_image, tc.profile_image) AS author_avatar
                FROM lesson_doubt_replies r
                LEFT JOIN students st ON st.user_id = r.user_id AND r.user_role = 'student'
                LEFT JOIN teachers tc ON tc.user_id = r.user_id AND r.user_role = 'teacher'
                WHERE r.doubt_id = ?
                ORDER BY r.created_at ASC
            ");
            $replyStmt->execute([(int)$d['id']]);
            $d['replies'] = $replyStmt->fetchAll();
        }

        return $doubts;
    }

    public static function listForStudent(int $studentUserId, array $filters = []): array
    {
        try {
            self::ensureTable();
            $db = Database::connect();

            $where = ['d.student_user_id = ?'];
            $params = [$studentUserId];

            if (!empty($filters['status'])) {
                $where[] = 'd.status = ?';
                $params[] = $filters['status'];
            }

            $sql = "
                SELECT d.*, l.title AS lesson_title, l.slug AS lesson_slug,
                       sub.name AS subject_name
                FROM lesson_doubts d
                JOIN lessons l ON l.id = d.lesson_id
                LEFT JOIN subjects sub ON sub.id = l.subject_id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY d.created_at DESC
                LIMIT 50
            ";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $doubts = $stmt->fetchAll();

            foreach ($doubts as &$d) {
                $replyStmt = $db->prepare("
                    SELECT r.*,
                           COALESCE(st.full_name, tc.full_name, 'Staff') AS author_name,
                           COALESCE(st.profile_image, tc.profile_image) AS author_avatar
                    FROM lesson_doubt_replies r
                    LEFT JOIN students st ON st.user_id = r.user_id AND r.user_role = 'student'
                    LEFT JOIN teachers tc ON tc.user_id = r.user_id AND r.user_role = 'teacher'
                    WHERE r.doubt_id = ?
                    ORDER BY r.created_at ASC
                ");
                $replyStmt->execute([(int)$d['id']]);
                $d['replies'] = $replyStmt->fetchAll();
            }

            return $doubts;
        } catch (\Throwable $e) {
            error_log('DoubtFeed listForStudent error: ' . $e->getMessage());
            return [];
        }
    }

    public static function getStudentStats(int $studentUserId): array
    {
        try {
            self::ensureTable();
            $db = Database::connect();

            $stmt = $db->prepare("
                SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 'answered' THEN 1 ELSE 0 END) AS answered,
                    SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) AS open
                FROM lesson_doubts
                WHERE student_user_id = ?
            ");
            $stmt->execute([$studentUserId]);
            $row = $stmt->fetch() ?: [];
            return [
                'total'    => (int)($row['total'] ?? 0),
                'answered' => (int)($row['answered'] ?? 0),
                'open'     => (int)($row['open'] ?? 0),
            ];
        } catch (\Throwable $e) {
            error_log('DoubtFeed getStudentStats error: ' . $e->getMessage());
            return [
                'total'    => 0,
                'answered' => 0,
                'open'     => 0,
            ];
        }
    }
}
