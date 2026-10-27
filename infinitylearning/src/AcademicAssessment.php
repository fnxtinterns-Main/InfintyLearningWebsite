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
              `assessment_type` varchar(50) NOT NULL DEFAULT 'unit_test',
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
        try {
            $db->exec("ALTER TABLE `academic_assessments` MODIFY COLUMN `assessment_type` VARCHAR(50) NOT NULL DEFAULT 'unit_test'");
        } catch (\Throwable $e) {}
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

    public static function getExamTypes(): array
    {
        return [
            'unit_test'  => 'Unit Test (UT)',
            'pen_paper'  => 'Pen & Paper Test (PPT)',
            'mid_term'   => 'Mid-Term Exam',
            'final_term' => 'Final Term Exam',
            'homework'   => 'Homework / Assignment',
            'practice_quiz' => 'Practice Quiz',
        ];
    }

    /**
     * Get all students in a batch along with their existing marks for an assessment (if any).
     */
    public static function getStudentsForMarksEntry(int $batchId, int $assessmentId = 0): array
    {
        self::ensureTable();
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT u.id AS student_user_id, u.email, s.full_name, s.current_class, s.profile_image,
                   sub.id AS submission_id, sub.score_obtained, sub.is_evaluated, sub.teacher_feedback, sub.status AS submission_status
            FROM batch_students bs
            JOIN users u ON u.id = bs.student_user_id
            LEFT JOIN students s ON s.user_id = u.id
            LEFT JOIN assessment_submissions sub ON sub.assessment_id = ? AND sub.student_user_id = u.id
            WHERE bs.batch_id = ? AND u.status = 'active'
            ORDER BY s.full_name ASC, u.email ASC
        ");
        $stmt->execute([$assessmentId, $batchId]);
        return $stmt->fetchAll();
    }

    /**
     * Save/update batch marks entered by teacher in 1-click.
     */
    public static function saveBatchMarks(int $assessmentId, array $studentScores, ?string $defaultFeedback = null): int
    {
        self::ensureTable();
        $assessment = self::find($assessmentId);
        if (!$assessment) {
            throw new \RuntimeException("Assessment not found.");
        }

        $db = Database::connect();
        $savedCount = 0;

        $stmt = $db->prepare("
            INSERT INTO assessment_submissions
            (uuid, assessment_id, student_user_id, score_obtained, is_evaluated, teacher_feedback, status, submitted_at, evaluated_at)
            VALUES (?, ?, ?, ?, 1, ?, 'evaluated', NOW(), NOW())
            ON DUPLICATE KEY UPDATE
                score_obtained = VALUES(score_obtained),
                is_evaluated = 1,
                teacher_feedback = VALUES(teacher_feedback),
                status = 'evaluated',
                evaluated_at = NOW()
        ");

        foreach ($studentScores as $studentUserId => $item) {
            $score = null;
            $feedback = $defaultFeedback;
            if (is_array($item)) {
                $score = isset($item['score']) && $item['score'] !== '' ? (float)$item['score'] : null;
                $feedback = !empty($item['feedback']) ? trim($item['feedback']) : $defaultFeedback;
            } elseif ($item !== '' && $item !== null) {
                $score = (float)$item;
            }

            if ($score === null) {
                continue; // Skip if no mark entered
            }

            $uuid = Auth::uuid();
            $stmt->execute([
                $uuid,
                $assessmentId,
                (int)$studentUserId,
                $score,
                $feedback
            ]);
            $savedCount++;
        }

        return $savedCount;
    }

    /**
     * Get categorized exam history and trend analytics for a student.
     * Categorizes into unit_test, pen_paper, mid_term, final_term.
     */
    public static function getStudentExamHistory(int $studentUserId): array
    {
        self::ensureTable();
        $db = Database::connect();

        $ids = [$studentUserId];
        try {
            $s = $db->prepare("SELECT user_id, id FROM students WHERE user_id = ? OR id = ? LIMIT 1");
            $s->execute([$studentUserId, $studentUserId]);
            $row = $s->fetch();
            if ($row) {
                if (!empty($row['user_id'])) $ids[] = (int)$row['user_id'];
                if (!empty($row['id'])) $ids[] = (int)$row['id'];
            }
        } catch (\Throwable $e) {}
        $ids = array_values(array_unique(array_filter($ids)));
        $inClause = implode(',', array_fill(0, count($ids), '?'));

        $stmt = $db->prepare("
            SELECT sub.id AS submission_id, sub.score_obtained, sub.is_evaluated, sub.teacher_feedback,
                   sub.status AS submission_status, sub.submitted_at, sub.evaluated_at,
                   a.id AS assessment_id, a.title, a.assessment_type, a.total_marks, a.passing_marks,
                   a.due_date, a.created_at AS assessment_date,
                   s.name AS subject_name, s.color AS subject_color, s.icon AS subject_icon,
                   b.name AS batch_name, t.full_name AS teacher_name
            FROM assessment_submissions sub
            JOIN academic_assessments a ON a.id = sub.assessment_id
            LEFT JOIN subjects s ON s.id = a.subject_id
            LEFT JOIN batches b ON b.id = a.batch_id
            LEFT JOIN teachers t ON t.user_id = a.teacher_user_id
            WHERE sub.student_user_id IN ($inClause) AND sub.score_obtained IS NOT NULL
            ORDER BY COALESCE(a.due_date, a.created_at) ASC, sub.submitted_at ASC
        ");
        $stmt->execute($ids);
        $rows = $stmt->fetchAll();

        $categories = [
            'unit_test'  => ['label' => 'Unit Tests',         'icon' => '📝', 'color' => '#2563EB', 'records' => [], 'total_obtained' => 0.0, 'total_max' => 0.0, 'avg_pct' => 0],
            'pen_paper'  => ['label' => 'Pen & Paper Tests',  'icon' => '📄', 'color' => '#7C3AED', 'records' => [], 'total_obtained' => 0.0, 'total_max' => 0.0, 'avg_pct' => 0],
            'mid_term'   => ['label' => 'Mid-Term Exams',     'icon' => '📊', 'color' => '#059669', 'records' => [], 'total_obtained' => 0.0, 'total_max' => 0.0, 'avg_pct' => 0],
            'final_term' => ['label' => 'Final Term Exams',   'icon' => '🎓', 'color' => '#D97706', 'records' => [], 'total_obtained' => 0.0, 'total_max' => 0.0, 'avg_pct' => 0],
        ];

        $allTests = [];
        $totalObtainedAll = 0.0;
        $totalMaxAll = 0.0;

        foreach ($rows as $r) {
            $type = $r['assessment_type'];
            if (!isset($categories[$type])) {
                if ($type === 'homework' || $type === 'practice_quiz') {
                    $type = 'unit_test'; // Group homework/quiz with formative unit test
                } else {
                    $type = 'unit_test';
                }
            }

            $score = (float)$r['score_obtained'];
            $maxMarks = (float)($r['total_marks'] ?: 1.0);
            $pct = round(($score / $maxMarks) * 100, 1);
            $passed = $score >= (float)$r['passing_marks'];

            $rec = array_merge($r, [
                'percentage' => $pct,
                'is_passed'  => $passed,
                'date_str'   => date('d M Y', strtotime((string)($r['due_date'] ?: $r['assessment_date']))),
            ]);

            $categories[$type]['records'][] = $rec;
            $categories[$type]['total_obtained'] += $score;
            $categories[$type]['total_max'] += $maxMarks;

            $totalObtainedAll += $score;
            $totalMaxAll += $maxMarks;
            $allTests[] = $rec;
        }

        foreach ($categories as $k => &$cat) {
            if ($cat['total_max'] > 0) {
                $cat['avg_pct'] = round(($cat['total_obtained'] / $cat['total_max']) * 100, 1);
            }
        }

        $overallPct = $totalMaxAll > 0 ? round(($totalObtainedAll / $totalMaxAll) * 100, 1) : 0.0;

        return [
            'categories' => $categories,
            'all_tests'  => array_reverse($allTests), // Newest first for table
            'summary'    => [
                'total_tests'    => count($allTests),
                'total_obtained' => round($totalObtainedAll, 1),
                'total_max'      => round($totalMaxAll, 1),
                'average_pct'    => $overallPct,
            ]
        ];
    }

    /**
     * Get School-wide Results & Analytics for School Admin.
     * Includes Class Averages, Class Toppers, Subject Averages, and Detailed Breakdown.
     */
    public static function getSchoolAnalytics(int $schoolId, array $filters = []): array
    {
        self::ensureTable();
        $db = Database::connect();

        $where = ['(a.school_id = ? OR b.school_id = ? OR sch_u.school_id = ?)'];
        $params = [$schoolId, $schoolId, $schoolId];

        if (!empty($filters['exam_type'])) {
            $where[] = 'a.assessment_type = ?';
            $params[] = $filters['exam_type'];
        }
        if (!empty($filters['batch_id'])) {
            $where[] = 'a.batch_id = ?';
            $params[] = (int)$filters['batch_id'];
        }
        if (!empty($filters['subject_id'])) {
            $where[] = 'a.subject_id = ?';
            $params[] = (int)$filters['subject_id'];
        }

        $sql = "
            SELECT sub.id AS submission_id, sub.score_obtained, sub.is_evaluated, sub.status AS submission_status, sub.submitted_at,
                   a.id AS assessment_id, a.title AS assessment_title, a.assessment_type, a.total_marks, a.passing_marks,
                   a.created_at AS assessment_date,
                   COALESCE(b.class_level, st.current_class, 'Grade 8') AS class_level,
                   s.id AS subject_id, s.name AS subject_name,
                   b.id AS batch_id, b.name AS batch_name,
                   u.id AS student_user_id, u.email AS student_email,
                   COALESCE(st.full_name, u.email) AS student_name, st.profile_image
            FROM assessment_submissions sub
            JOIN academic_assessments a ON a.id = sub.assessment_id
            JOIN subjects s ON s.id = a.subject_id
            JOIN users u ON u.id = sub.student_user_id
            LEFT JOIN students st ON st.user_id = u.id
            LEFT JOIN users sch_u ON sch_u.id = a.teacher_user_id
            LEFT JOIN batches b ON b.id = a.batch_id
            WHERE sub.score_obtained IS NOT NULL AND " . implode(' AND ', $where) . "
            ORDER BY a.created_at DESC, sub.score_obtained DESC
        ";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();

        // 1. Overall stats
        $totalSubmissions = count($rows);
        $totalScoreObtained = 0.0;
        $totalMaxMarks = 0.0;
        $passCount = 0;

        // 2. Class aggregates
        $classStats = [];
        // 3. Subject aggregates
        $subjectStats = [];
        // 4. Student aggregates (for class toppers)
        $studentTotals = [];

        foreach ($rows as $r) {
            $score = (float)$r['score_obtained'];
            $max = (float)($r['total_marks'] ?: 1.0);
            $pct = ($score / $max) * 100.0;
            $passed = $score >= (float)$r['passing_marks'];

            $totalScoreObtained += $score;
            $totalMaxMarks += $max;
            if ($passed) $passCount++;

            $cLevel = trim($r['class_level']) ?: 'General';
            if (!isset($classStats[$cLevel])) {
                $classStats[$cLevel] = ['class' => $cLevel, 'obtained' => 0.0, 'max' => 0.0, 'count' => 0];
            }
            $classStats[$cLevel]['obtained'] += $score;
            $classStats[$cLevel]['max'] += $max;
            $classStats[$cLevel]['count']++;

            $sName = $r['subject_name'];
            if (!isset($subjectStats[$sName])) {
                $subjectStats[$sName] = ['subject' => $sName, 'obtained' => 0.0, 'max' => 0.0, 'count' => 0];
            }
            $subjectStats[$sName]['obtained'] += $score;
            $subjectStats[$sName]['max'] += $max;
            $subjectStats[$sName]['count']++;

            $stuId = (int)$r['student_user_id'];
            if (!isset($studentTotals[$cLevel][$stuId])) {
                $studentTotals[$cLevel][$stuId] = [
                    'student_id'   => $stuId,
                    'name'         => $r['student_name'],
                    'email'        => $r['student_email'],
                    'profile_image'=> $r['profile_image'],
                    'class'        => $cLevel,
                    'obtained'     => 0.0,
                    'max'          => 0.0,
                    'tests'        => 0
                ];
            }
            $studentTotals[$cLevel][$stuId]['obtained'] += $score;
            $studentTotals[$cLevel][$stuId]['max'] += $max;
            $studentTotals[$cLevel][$stuId]['tests']++;
        }

        // Calculate class toppers
        $classToppers = [];
        foreach ($studentTotals as $cLevel => $students) {
            $stuList = array_values($students);
            foreach ($stuList as &$s) {
                $s['avg_pct'] = $s['max'] > 0 ? round(($s['obtained'] / $s['max']) * 100, 1) : 0;
            }
            usort($stuList, fn($a, $b) => $b['avg_pct'] <=> $a['avg_pct']);
            if (!empty($stuList[0])) {
                $classToppers[$cLevel] = [
                    'topper' => $stuList[0],
                    'runner_up' => $stuList[1] ?? null,
                    'third' => $stuList[2] ?? null,
                ];
            }
        }

        // Calculate Subject Averages
        foreach ($subjectStats as &$s) {
            $s['avg_pct'] = $s['max'] > 0 ? round(($s['obtained'] / $s['max']) * 100, 1) : 0;
        }
        usort($subjectStats, fn($a, $b) => $b['avg_pct'] <=> $a['avg_pct']);

        // Calculate Class Averages
        foreach ($classStats as &$c) {
            $c['avg_pct'] = $c['max'] > 0 ? round(($c['obtained'] / $c['max']) * 100, 1) : 0;
        }
        ksort($classStats);

        $schoolAvgPct = $totalMaxMarks > 0 ? round(($totalScoreObtained / $totalMaxMarks) * 100, 1) : 0;
        $passRatePct = $totalSubmissions > 0 ? round(($passCount / $totalSubmissions) * 100, 1) : 0;

        return [
            'summary' => [
                'total_evaluations' => $totalSubmissions,
                'overall_average'   => $schoolAvgPct,
                'pass_rate'         => $passRatePct,
                'classes_count'     => count($classStats),
                'subjects_count'    => count($subjectStats),
            ],
            'class_toppers' => $classToppers,
            'class_stats'   => array_values($classStats),
            'subject_stats' => array_values($subjectStats),
            'records'       => $rows,
        ];
    }
}
