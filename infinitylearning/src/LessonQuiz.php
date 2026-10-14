<?php
declare(strict_types=1);

namespace App;

use PDO;

final class LessonQuiz
{
    private static bool $bootstrapped = false;

    public static function ensureTable(): void
    {
        if (self::$bootstrapped) return;
        $db = Database::connect();
        $db->exec("
            CREATE TABLE IF NOT EXISTS `lesson_quizzes` (
              `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
              `lesson_id` bigint(20) UNSIGNED NOT NULL,
              `questions_json` longtext NOT NULL,
              `created_at` datetime DEFAULT current_timestamp(),
              `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
              PRIMARY KEY (`id`),
              UNIQUE KEY `uk_lq_lesson` (`lesson_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

            CREATE TABLE IF NOT EXISTS `lesson_quiz_attempts` (
              `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
              `lesson_id` bigint(20) UNSIGNED NOT NULL,
              `student_user_id` bigint(20) UNSIGNED NOT NULL,
              `score` tinyint(3) UNSIGNED NOT NULL,
              `total_questions` tinyint(3) UNSIGNED NOT NULL DEFAULT 10,
              `passed` tinyint(1) NOT NULL DEFAULT 0,
              `xp_awarded` int(10) UNSIGNED NOT NULL DEFAULT 0,
              `answers_json` longtext DEFAULT NULL,
              `attempted_at` datetime DEFAULT current_timestamp(),
              PRIMARY KEY (`id`),
              KEY `idx_lqa_student` (`student_user_id`),
              KEY `idx_lqa_lesson` (`lesson_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        self::$bootstrapped = true;
    }

    public static function getOrCreateQuiz(int $lessonId): array
    {
        self::ensureTable();
        $db = Database::connect();

        $stmt = $db->prepare("SELECT * FROM lesson_quizzes WHERE lesson_id = ? LIMIT 1");
        $stmt->execute([$lessonId]);
        $row = $stmt->fetch();

        if ($row && !empty($row['questions_json'])) {
            $questions = json_decode($row['questions_json'], true);
            if (is_array($questions) && count($questions) >= 5) {
                return $questions;
            }
        }

        // Generate 10 contextual questions for this lesson
        $lessonStmt = $db->prepare("
            SELECT l.*, s.name AS subject_name, c.name AS chapter_name, t.name AS topic_name
            FROM lessons l
            LEFT JOIN subjects s ON s.id = l.subject_id
            LEFT JOIN chapters c ON c.id = l.chapter_id
            LEFT JOIN topics t ON t.id = l.topic_id
            WHERE l.id = ? LIMIT 1
        ");
        $lessonStmt->execute([$lessonId]);
        $lesson = $lessonStmt->fetch();

        $generated = self::generateDefault10Questions($lesson ?: ['title' => 'Core Concept Mastery']);

        $saveStmt = $db->prepare("
            INSERT INTO lesson_quizzes (lesson_id, questions_json)
            VALUES (?, ?)
            ON DUPLICATE KEY UPDATE questions_json = VALUES(questions_json)
        ");
        $saveStmt->execute([$lessonId, json_encode($generated)]);

        return $generated;
    }

    public static function submitAttempt(int $lessonId, int $studentId, array $answers): array
    {
        self::ensureTable();
        $questions = self::getOrCreateQuiz($lessonId);
        $total = count($questions);

        $correctCount = 0;
        $evaluations = [];

        foreach ($questions as $idx => $q) {
            $userChoice = (int)($answers[$idx] ?? -1);
            $correctChoice = (int)($q['correct_index'] ?? 0);
            $isCorrect = ($userChoice === $correctChoice);

            if ($isCorrect) {
                $correctCount++;
            }

            $evaluations[] = [
                'question_index' => $idx,
                'user_choice' => $userChoice,
                'correct_choice' => $correctChoice,
                'is_correct' => $isCorrect,
                'explanation' => $q['explanation'] ?? ''
            ];
        }

        $percentage = $total > 0 ? (int)round(($correctCount / $total) * 100) : 0;
        $passed = ($percentage >= 60);
        $xp = $passed ? 25 : 5;

        $db = Database::connect();
        $stmt = $db->prepare("
            INSERT INTO lesson_quiz_attempts
            (lesson_id, student_user_id, score, total_questions, passed, xp_awarded, answers_json, attempted_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $lessonId,
            $studentId,
            $correctCount,
            $total,
            $passed ? 1 : 0,
            $xp,
            json_encode($answers)
        ]);

        // If passed, mark lesson progress as completed
        if ($passed) {
            try {
                $db->prepare("
                    INSERT INTO lesson_progress (lesson_id, student_user_id, status, progress_percent, completed_at, last_viewed_at)
                    VALUES (?, ?, 'completed', 100, NOW(), NOW())
                    ON DUPLICATE KEY UPDATE
                        status = 'completed',
                        progress_percent = 100,
                        completed_at = NOW()
                ")->execute([$lessonId, $studentId]);
            } catch (\Throwable $e) {}
        }

        return [
            'score' => $correctCount,
            'total' => $total,
            'percentage' => $percentage,
            'passed' => $passed,
            'xp_awarded' => $xp,
            'evaluations' => $evaluations
        ];
    }

    public static function getLatestAttempt(int $lessonId, int $studentId): ?array
    {
        self::ensureTable();
        $db = Database::connect();
        $stmt = $db->prepare("
            SELECT * FROM lesson_quiz_attempts
            WHERE lesson_id = ? AND student_user_id = ?
            ORDER BY attempted_at DESC LIMIT 1
        ");
        $stmt->execute([$lessonId, $studentId]);
        return $stmt->fetch() ?: null;
    }

    private static function generateDefault10Questions(array $l): array
    {
        $title = $l['title'] ?? 'The Topic';
        $sub = $l['subject_name'] ?? 'General Studies';
        $chap = $l['chapter_name'] ?? 'Fundamental Principles';

        return [
            [
                'question' => "What is the primary core objective taught in \"{$title}\"?",
                'options' => [
                    "To understand foundational mechanisms and practical applications",
                    "To memorize historical dates without context",
                    "To ignore theoretical frameworks completely",
                    "To bypass standard analytical reasoning"
                ],
                'correct_index' => 0,
                'explanation' => "The primary focus of this lesson is mastering foundational principles and their practical applications."
            ],
            [
                'question' => "In {$sub}, which methodology is most appropriate for analyzing {$title}?",
                'options' => [
                    "Unchecked assumption",
                    "Structured empirical reasoning and step-by-step verification",
                    "Arbitrary guesswork",
                    "Skipping preliminary checks"
                ],
                'correct_index' => 1,
                'explanation' => "{$sub} relies upon structured empirical evaluation and verifiable analytical deduction."
            ],
            [
                'question' => "What is a common misconception when studying \"{$title}\"?",
                'options' => [
                    "That variables can be defined with precision",
                    "That complex problems can be simplified into basic components",
                    "That intuition alone replaces rigorous problem solving",
                    "That formulas require units of measurement"
                ],
                'correct_index' => 2,
                'explanation' => "A frequent trap for students is relying solely on superficial intuition rather than systematic proof."
            ],
            [
                'question' => "How does \"{$title}\" directly relate to the broader chapter on \"{$chap}\"?",
                'options' => [
                    "It serves as a critical stepping stone linking basic theory to advanced problem-solving",
                    "It has zero connection to the chapter curriculum",
                    "It completely contradicts established rules",
                    "It only applies to theoretical simulations and never real scenarios"
                ],
                'correct_index' => 0,
                'explanation' => "This concept forms the cornerstone connecting fundamental definitions with advanced problem sets."
            ],
            [
                'question' => "When solving problems in this lesson, what is the recommended first step?",
                'options' => [
                    "Jump straight to guessing the answer",
                    "Identify the given parameters, constraints, and target unknowns",
                    "Ignore units and notation",
                    "Assume all constants are equal to zero"
                ],
                'correct_index' => 1,
                'explanation' => "The cardinal rule of analytical problem solving is clearly noting knowns, constraints, and target values first."
            ],
            [
                'question' => "Which of the following conditions would invalidate a solution in \"{$title}\"?",
                'options' => [
                    "Matching dimensional consistency",
                    "Violating fundamental conservation laws or constraint limits",
                    "Verifying calculations with an alternative method",
                    "Documenting intermediate steps"
                ],
                'correct_index' => 1,
                'explanation' => "Any outcome that breaches boundary constraints or foundational conservation laws is physically or mathematically invalid."
            ],
            [
                'question' => "What key skill is reinforced through regular practice of \"{$title}\"?",
                'options' => [
                    "Passive observation",
                    "Critical analysis, pattern recognition, and problem decomposition",
                    "Rote memorization without understanding",
                    "Trial and error without reflection"
                ],
                'correct_index' => 1,
                'explanation' => "Working through variations of this topic sharpens logical decomposition and pattern recognition."
            ],
            [
                'question' => "In real-world applications of {$sub}, where is \"{$title}\" most frequently utilized?",
                'options' => [
                    "Predictive modeling, engineering systems, and decision analysis",
                    "Random number generation only",
                    "Nowhere outside classroom exams",
                    "Exclusively in historical trivia"
                ],
                'correct_index' => 0,
                'explanation' => "This concept is heavily applied in practical industry models, systems engineering, and strategic decisions."
            ],
            [
                'question' => "If an edge case produces unexpected outputs in this topic, what should you verify first?",
                'options' => [
                    "Boundary conditions, zero denominators, and domain limits",
                    "Conclude the entire theory is flawed",
                    "Discard the test data immediately",
                    "Increase calculation speed without checking inputs"
                ],
                'correct_index' => 0,
                'explanation' => "Edge cases almost always trace back to extreme boundaries, domain restrictions, or division by zero."
            ],
            [
                'question' => "What is the best way to demonstrate true mastery of \"{$title}\"?",
                'options' => [
                    "Being able to explain the core concept simply to a peer and solve non-routine challenge problems",
                    "Memorizing one specific textbook question word-for-word",
                    "Avoiding practice exercises",
                    "Depending on answer keys without independent attempts"
                ],
                'correct_index' => 0,
                'explanation' => "True mastery is reflected in the Feynman technique: the ability to explain the principle lucidly and tackle novel challenges."
            ]
        ];
    }
}
