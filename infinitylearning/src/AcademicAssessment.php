<?php
declare(strict_types=1);

namespace App;

use PDO;

final class AcademicAssessment
{
    private static bool $bootstrapped = false;

    public static function ensureTable(): void
    {
        if (self::$bootstrapped) return;
        $db = Database::connect();
        $db->exec("
            CREATE TABLE IF NOT EXISTS `academic_assessments` (
              `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
              `uuid` char(36) NOT NULL,
              `teacher_user_id` bigint(20) UNSIGNED NOT NULL,
              `school_id` bigint(20) UNSIGNED DEFAULT NULL,
              `batch_id` bigint(20) UNSIGNED DEFAULT NULL,
              `class_level` varchar(50) DEFAULT NULL,
              `subject_id` bigint(20) UNSIGNED NOT NULL,
              `title` varchar(220) NOT NULL,
              `assessment_type` enum('homework','unit_test','mid_term','final_term','practice_quiz') NOT NULL DEFAULT 'homework',
              `total_marks` decimal(5,1) NOT NULL DEFAULT 20.0,
              `passing_marks` decimal(5,1) NOT NULL DEFAULT 8.0,
              `duration_minutes` int(10) UNSIGNED DEFAULT 0,
              `instructions` text DEFAULT NULL,
              `questions_json` longtext NOT NULL,
              `start_date` datetime DEFAULT NULL,
              `due_date` datetime DEFAULT NULL,
              `status` enum('draft','published','closed') NOT NULL DEFAULT 'published',
              `created_at` datetime DEFAULT current_timestamp(),
              `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`id`),
              UNIQUE KEY `uk_aa_uuid` (`uuid`),
              KEY `idx_aa_teacher` (`teacher_user_id`),
              KEY `idx_aa_batch` (`batch_id`),
              KEY `idx_aa_type` (`assessment_type`),
              KEY `idx_aa_due` (`due_date`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `assessment_submissions` (
              `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
              `uuid` char(36) NOT NULL,
              `assessment_id` bigint(20) UNSIGNED NOT NULL,
              `student_user_id` bigint(20) UNSIGNED NOT NULL,
              `answers_json` longtext DEFAULT NULL,
              `score_obtained` decimal(5,1) DEFAULT NULL,
              `is_evaluated` tinyint(1) NOT NULL DEFAULT 0,
              `teacher_feedback` text DEFAULT NULL,
              `status` enum('submitted','evaluated','late') NOT NULL DEFAULT 'submitted',
              `submitted_at` datetime DEFAULT current_timestamp(),
              `evaluated_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `uk_as_uuid` (`uuid`),
              UNIQUE KEY `uk_as_assessment_student` (`assessment_id`, `student_user_id`),
              KEY `idx_as_student` (`student_user_id`),
              KEY `idx_as_assessment` (`assessment_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        self::$bootstrapped = true;
    }

    public static function create(array $d, int $teacherId, ?int $schoolId = null): int
    {
        self::ensureTable();
        $db = Database::connect();
        $uuid = Auth::uuid();

        $questionsJson = is_array($d['questions'] ?? null)
            ? json_encode($d['questions'])
            : ($d['questions_json'] ?? '[]');

        $stmt = $db->prepare("
            INSERT INTO academic_assessments
            (uuid, teacher_user_id, school_id, batch_id, class_level, subject_id,
             title, assessment_type, total_marks, passing_marks, duration_minutes,
             instructions, questions_json, start_date, due_date, status)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $uuid,
            $teacherId,
            $schoolId,
            !empty($d['batch_id']) ? (int)$d['batch_id'] : null,
            trim($d['class_level'] ?? ''),
            (int)$d['subject_id'],
            trim($d['title']),
            $d['assessment_type'] ?? 'homework',
            (float)($d['total_marks'] ?? 20.0),
            (float)($d['passing_marks'] ?? 8.0),
            (int)($d['duration_minutes'] ?? 0),
            trim($d['instructions'] ?? ''),
            $questionsJson,
            !empty($d['start_date']) ? $d['start_date'] : null,
            !empty($d['due_date']) ? $d['due_date'] : null,
            $d['status'] ?? 'published'
        ]);

        return (int)$db->lastInsertId();
    }

    public static function find(int $id): ?array
    {
        self::ensureTable();
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT a.*, s.name AS subject_name, b.name AS batch_name, t.full_name AS teacher_name
            FROM academic_assessments a
            LEFT JOIN subjects s ON s.id = a.subject_id
            LEFT JOIN batches b ON b.id = a.batch_id
            LEFT JOIN teachers t ON t.user_id = a.teacher_user_id
            WHERE a.id = ? LIMIT 1
        ");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row && !empty($row['questions_json'])) {
            $row['questions'] = json_decode($row['questions_json'], true) ?: [];
        }
        return $row ?: null;
    }

    public static function listForTeacher(int $teacherId, array $filters = []): array
    {
        self::ensureTable();
        $db = Database::connect();

        $where = ['a.teacher_user_id = ?'];
        $params = [$teacherId];

        if (!empty($filters['assessment_type'])) {
            $where[] = 'a.assessment_type = ?';
            $params[] = $filters['assessment_type'];
        }

        if (!empty($filters['batch_id'])) {
            $where[] = 'a.batch_id = ?';
            $params[] = (int)$filters['batch_id'];
        }

        $sql = "
            SELECT a.*, s.name AS subject_name, b.name AS batch_name,
                   COUNT(sub.id) AS submissions_count
            FROM academic_assessments a
            LEFT JOIN subjects s ON s.id = a.subject_id
            LEFT JOIN batches b ON b.id = a.batch_id
            LEFT JOIN assessment_submissions sub ON sub.assessment_id = a.id
            WHERE " . implode(' AND ', $where) . "
            GROUP BY a.id
            ORDER BY a.created_at DESC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function listForStudent(int $studentId, array $filters = []): array
    {
        self::ensureTable();
        $db = Database::connect();

        // Find student batches
        $batchIds = $db->query("
            SELECT batch_id FROM batch_students WHERE student_user_id = " . (int)$studentId
        )->fetchAll(PDO::FETCH_COLUMN) ?: [];

        $where = ['a.status = "published"'];
        $params = [];

        if (!empty($batchIds)) {
            $inBatches = implode(',', array_map('intval', $batchIds));
            $where[] = "(a.batch_id IN ($inBatches) OR a.batch_id IS NULL)";
        } else {
            $where[] = "a.batch_id IS NULL";
        }

        $sql = "
            SELECT a.*, s.name AS subject_name, b.name AS batch_name,
                   sub.id AS submission_id, sub.score_obtained, sub.is_evaluated, sub.status AS submission_status, sub.submitted_at
            FROM academic_assessments a
            LEFT JOIN subjects s ON s.id = a.subject_id
            LEFT JOIN batches b ON b.id = a.batch_id
            LEFT JOIN assessment_submissions sub ON sub.assessment_id = a.id AND sub.student_user_id = ?
            WHERE " . implode(' AND ', $where) . "
            ORDER BY a.due_date ASC, a.created_at DESC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute([$studentId]);
        return $stmt->fetchAll();
    }

    public static function submitAssessment(int $assessmentId, int $studentId, array $answers): array
    {
        self::ensureTable();
        $assessment = self::find($assessmentId);
        if (!$assessment) {
            throw new \RuntimeException("Assessment not found");
        }

        $db = Database::connect();
        $uuid = Auth::uuid();

        // Auto-grade MCQs
        $score = 0.0;
        $totalAutoMarks = 0.0;
        $questions = $assessment['questions'] ?? [];

        foreach ($questions as $q) {
            $qId = (string)($q['id'] ?? '');
            $qType = $q['question_type'] ?? 'mcq';
            $marks = (float)($q['marks'] ?? 1.0);
            $userAns = trim((string)($answers[$qId] ?? ''));

            if ($qType === 'mcq' || $qType === 'true_false') {
                $totalAutoMarks += $marks;
                $correctAns = trim((string)($q['correct_answer'] ?? ''));
                if (strcasecmp($userAns, $correctAns) === 0) {
                    $score += $marks;
                }
            }
        }

        // Check if there are descriptive questions
        $hasManualGrading = false;
        foreach ($questions as $q) {
            if (in_array($q['question_type'] ?? '', ['short_answer', 'descriptive', 'numerical'])) {
                $hasManualGrading = true;
                break;
            }
        }

        $isEvaluated = $hasManualGrading ? 0 : 1;
        $finalScore = $hasManualGrading ? null : $score;

        $stmt = $db->prepare("
            INSERT INTO assessment_submissions
            (uuid, assessment_id, student_user_id, answers_json, score_obtained, is_evaluated, status, submitted_at)
            VALUES (?, ?, ?, ?, ?, ?, 'submitted', NOW())
            ON DUPLICATE KEY UPDATE
                answers_json = VALUES(answers_json),
                score_obtained = VALUES(score_obtained),
                is_evaluated = VALUES(is_evaluated),
                status = VALUES(status),
                submitted_at = NOW()
        ");

        $stmt->execute([
            $uuid,
            $assessmentId,
            $studentId,
            json_encode($answers),
            $finalScore,
            $isEvaluated
        ]);

        return [
            'success' => true,
            'is_evaluated' => $isEvaluated,
            'score' => $score,
            'total_auto_marks' => $totalAutoMarks
        ];
    }

    public static function getSubmissions(int $assessmentId): array
    {
        self::ensureTable();
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT sub.*, st.full_name AS student_name, u.email AS student_email, st.current_class
            FROM assessment_submissions sub
            JOIN users u ON u.id = sub.student_user_id
            LEFT JOIN students st ON st.user_id = sub.student_user_id
            WHERE sub.assessment_id = ?
            ORDER BY sub.submitted_at DESC
        ");
        $stmt->execute([$assessmentId]);
        return $stmt->fetchAll();
    }
}
