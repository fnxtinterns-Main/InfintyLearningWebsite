<?php
declare(strict_types=1);

namespace App;

use PDO;

final class DoubtFeed
{
    private static bool $bootstrapped = false;

    public static function ensureReplyColumns(): void
    {
        try {
            $db = Database::connect();
            $db->exec("ALTER TABLE `lesson_doubt_replies` MODIFY COLUMN `user_role` VARCHAR(20) NOT NULL DEFAULT 'student'");
        } catch (\Throwable $e) {}

        try {
            $db = Database::connect();
            $cols = $db->query("SHOW COLUMNS FROM `lesson_doubt_replies` LIKE 'is_ai'")->fetchAll();
            if (empty($cols)) {
                $db->exec("ALTER TABLE `lesson_doubt_replies` ADD COLUMN `is_ai` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_teacher_endorsed`");
            }
        } catch (\Throwable $e) {}
    }

    public static function ensureTable(): void
    {
        self::ensureReplyColumns();
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
                  `user_role` varchar(20) NOT NULL DEFAULT 'student',
                  `reply_text` text NOT NULL,
                  `is_teacher_endorsed` tinyint(1) NOT NULL DEFAULT 0,
                  `is_ai` tinyint(1) NOT NULL DEFAULT 0,
                  `created_at` datetime DEFAULT current_timestamp(),
                  PRIMARY KEY (`id`),
                  KEY `idx_ldr_doubt` (`doubt_id`),
                  KEY `idx_ldr_user` (`user_id`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
            ");

            try {
                $db->exec("
                    UPDATE lesson_doubts d
                    JOIN lessons l ON l.id = d.lesson_id
                    SET d.school_id = l.school_id
                    WHERE (d.school_id IS NULL OR d.school_id = 0) AND l.school_id IS NOT NULL AND l.school_id > 0
                ");
            } catch (\Throwable $ex) {}

            try {
                $db->exec("
                    UPDATE lesson_doubts d
                    JOIN users u ON u.id = d.student_user_id
                    SET d.school_id = u.school_id
                    WHERE (d.school_id IS NULL OR d.school_id = 0) AND u.school_id IS NOT NULL AND u.school_id > 0
                ");
            } catch (\Throwable $ex) {}

            try {
                $db->exec("ALTER TABLE lesson_doubts ADD COLUMN teacher_user_id BIGINT UNSIGNED DEFAULT NULL AFTER student_user_id");
                $db->exec("ALTER TABLE lesson_doubts ADD KEY idx_ld_teacher (teacher_user_id)");
            } catch (\Throwable $ex) {}

            self::$bootstrapped = true;
        } catch (\Throwable $e) {
            error_log('DoubtFeed ensureTable error: ' . $e->getMessage());
        }
    }

    public static function askDoubt(int $lessonId, int $studentId, string $title, string $body, ?string $attachmentUrl = null, ?int $schoolId = null, ?int $batchId = null, ?int $teacherUserId = null): int
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
            (uuid, lesson_id, student_user_id, teacher_user_id, school_id, batch_id, title, body, attachment_url)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $uuid,
            $lessonId,
            $studentId,
            $teacherUserId ?: null,
            $schoolId,
            $batchId,
            trim($title),
            trim($body),
            $attachmentUrl
        ]);

        Cache::forget("doubts:lesson:{$lessonId}");
        return (int)$db->lastInsertId();
    }

    public static function addReply(int $doubtId, int $userId, string $role, string $text, int $isAi = 0): int
    {
        $db = Database::connect();
        $isEndorsed = ($role === 'teacher' || $role === 'admin') ? 1 : 0;

        if ($isAi) {
            if (strpos($text, '<!-- AI_ANSWER -->') === false) {
                $text = "<!-- AI_ANSWER -->\n" . trim($text);
            }
        }

        $replyId = 0;
        $inserted = false;

        if ($isAi) {
            // 1. Try modern insert with user_role='ai' and is_ai=1
            try {
                $stmt = $db->prepare("
                    INSERT INTO lesson_doubt_replies (doubt_id, user_id, user_role, reply_text, is_teacher_endorsed, is_ai)
                    VALUES (?, ?, 'ai', ?, 0, 1)
                ");
                $stmt->execute([$doubtId, $userId, trim($text)]);
                $replyId = (int)$db->lastInsertId();
                $inserted = ($replyId > 0);
            } catch (\Throwable $e) {}

            // 2. If modern insert failed, attempt self-healing schema upgrade
            if (!$inserted) {
                try {
                    $db->exec("ALTER TABLE `lesson_doubt_replies` MODIFY COLUMN `user_role` VARCHAR(20) NOT NULL DEFAULT 'student'");
                    $db->exec("ALTER TABLE `lesson_doubt_replies` ADD COLUMN `is_ai` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_teacher_endorsed`");
                    $stmt = $db->prepare("
                        INSERT INTO lesson_doubt_replies (doubt_id, user_id, user_role, reply_text, is_teacher_endorsed, is_ai)
                        VALUES (?, ?, 'ai', ?, 0, 1)
                    ");
                    $stmt->execute([$doubtId, $userId, trim($text)]);
                    $replyId = (int)$db->lastInsertId();
                    $inserted = ($replyId > 0);
                } catch (\Throwable $e2) {}
            }

            // 3. Fallback safe insert using standard ENUM 'admin' with invisible AI tag
            if (!$inserted) {
                try {
                    $stmt = $db->prepare("
                        INSERT INTO lesson_doubt_replies (doubt_id, user_id, user_role, reply_text, is_teacher_endorsed)
                        VALUES (?, ?, 'admin', ?, 0)
                    ");
                    $stmt->execute([$doubtId, $userId, trim($text)]);
                    $replyId = (int)$db->lastInsertId();
                    $inserted = ($replyId > 0);
                } catch (\Throwable $e3) {
                    error_log('DoubtFeed AI reply fallback insert failed: ' . $e3->getMessage());
                }
            }
        } else {
            // Human reply
            try {
                $stmt = $db->prepare("
                    INSERT INTO lesson_doubt_replies (doubt_id, user_id, user_role, reply_text, is_teacher_endorsed)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$doubtId, $userId, $role, trim($text), $isEndorsed]);
                $replyId = (int)$db->lastInsertId();
                $inserted = ($replyId > 0);
            } catch (\Throwable $e) {
                error_log('DoubtFeed human reply insert failed: ' . $e->getMessage());
            }
        }

        if ($inserted) {
            if ($replyId <= 0) {
                try {
                    $q = $db->prepare("SELECT id FROM lesson_doubt_replies WHERE doubt_id = ? ORDER BY id DESC LIMIT 1");
                    $q->execute([$doubtId]);
                    $replyId = (int)$q->fetchColumn();
                } catch (\Throwable $e) {}
            }

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
        }

        return $replyId;
    }

    public static function answerWithAI(int $doubtId): ?int
    {
        try {
            self::ensureTable();
            $db = Database::connect();

            // 1. Check if doubt exists using simple, robust query
            $stmt = $db->prepare("SELECT * FROM lesson_doubts WHERE id = ? LIMIT 1");
            $stmt->execute([$doubtId]);
            $doubt = $stmt->fetch();
            if (!$doubt) {
                return null;
            }

            // 2. Check if AI reply already exists for this doubt
            $existingId = 0;
            try {
                $checkStmt = $db->prepare("
                    SELECT id FROM lesson_doubt_replies 
                    WHERE doubt_id = ? AND (user_role = 'ai' OR reply_text LIKE '%<!-- AI_ANSWER -->%') 
                    LIMIT 1
                ");
                $checkStmt->execute([$doubtId]);
                $existingId = (int)$checkStmt->fetchColumn();
            } catch (\Throwable $chkE) {}

            if ($existingId > 0) {
                return $existingId;
            }

            $title = trim($doubt['title'] ?? '');
            $body = trim($doubt['body'] ?? '');

            // Optional context lookup
            $lessonTitle = '';
            $subjectName = '';
            $studentClass = '';
            if (!empty($doubt['lesson_id'])) {
                try {
                    $lStmt = $db->prepare("
                        SELECT l.title, s.name AS subject_name 
                        FROM lessons l 
                        LEFT JOIN subjects s ON s.id = l.subject_id 
                        WHERE l.id = ? LIMIT 1
                    ");
                    $lStmt->execute([(int)$doubt['lesson_id']]);
                    $lRow = $lStmt->fetch();
                    if ($lRow) {
                        $lessonTitle = trim($lRow['title'] ?? '');
                        $subjectName = trim($lRow['subject_name'] ?? '');
                    }
                } catch (\Throwable $e) {}
            }

            // 3. Generate AI Answer using Gemini, with academic fallback
            $aiReplyText = self::callGeminiForDoubt($title, $body, $subjectName, $lessonTitle, $studentClass);
            if (empty(trim($aiReplyText))) {
                $aiReplyText = self::generateFallbackDoubtAnswer($title, $body, $subjectName, $lessonTitle);
            }

            // 4. Save AI Reply
            $newReplyId = self::addReply($doubtId, 0, 'ai', $aiReplyText, 1);
            if ($newReplyId <= 0) {
                try {
                    $q = $db->prepare("SELECT id FROM lesson_doubt_replies WHERE doubt_id = ? ORDER BY id DESC LIMIT 1");
                    $q->execute([$doubtId]);
                    $newReplyId = (int)$q->fetchColumn();
                } catch (\Throwable $e) {}
            }

            return $newReplyId > 0 ? $newReplyId : 1;
        } catch (\Throwable $e) {
            error_log('DoubtFeed answerWithAI error: ' . $e->getMessage());
            return null;
        }
    }

    private static function callGeminiForDoubt(string $title, string $body, string $subject, string $lesson, string $grade): string
    {
        $config = [];
        if (defined('APP_ROOT') && file_exists(APP_ROOT . '/config/config.php')) {
            $config = require APP_ROOT . '/config/config.php';
        }
        
        $apiKey = Env::get('GEMINI_API_KEY') 
            ?: (getenv('GEMINI_API_KEY') 
            ?: ($config['gemini_api_key'] 
            ?? $config['ai_agent']['api_key'] 
            ?? ''));

        if (empty($apiKey)) {
            return '';
        }

        $prompt = "You are Infinity Learning's AI Academic Tutor. A student has submitted a doubt/question.
Student Class/Grade: " . ($grade ? "Class {$grade}" : "Secondary School") . "
Subject: " . ($subject ?: "General Academics") . "
Lesson/Topic: " . ($lesson ?: "General Study") . "

Student Question Title: {$title}
Student Question Details: {$body}

Provide a comprehensive, encouraging, clear, and structured response:
1. 📌 Core Concept: Clearly state the primary concept or rule in 1-2 lines.
2. 📝 Step-by-Step Explanation: Solve or explain the problem step by step with clear reasoning, formulas, or examples.
3. 💡 Key Takeaway & Study Tip: A practical tip or memory aid to help the student remember this.

Write in a friendly, pedagogical tone suitable for school students. Use clean paragraphs and bullet points. Do not output raw JSON or code formatting markers around the entire response.";

        $models = ['gemini-1.5-flash', 'gemini-2.0-flash', 'gemini-1.5-pro'];
        foreach ($models as $model) {
            try {
                $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . urlencode($apiKey);
                $payload = json_encode([
                    'contents' => [
                        ['parts' => [['text' => $prompt]]]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.3,
                        'maxOutputTokens' => 1024
                    ]
                ]);

                $ch = curl_init($url);
                curl_setopt_array($ch, [
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_POST => true,
                    CURLOPT_POSTFIELDS => $payload,
                    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                    CURLOPT_TIMEOUT => 10,
                    CURLOPT_SSL_VERIFYPEER => false,
                ]);
                $response = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                curl_close($ch);

                if ($httpCode === 200 && $response) {
                    $respData = json_decode($response, true);
                    $text = $respData['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    if (!empty(trim($text))) {
                        return trim($text);
                    }
                }
            } catch (\Throwable $e) {
                error_log("Gemini doubt call error: " . $e->getMessage());
            }
        }

        return '';
    }

    private static function generateFallbackDoubtAnswer(string $title, string $body, string $subject, string $lesson): string
    {
        $combined = strtolower($title . ' ' . $body . ' ' . $subject . ' ' . $lesson);

        if (strpos($combined, 'math') !== false || strpos($combined, 'equation') !== false || strpos($combined, 'triangle') !== false || strpos($combined, 'algebra') !== false || strpos($combined, 'geometry') !== false || strpos($combined, 'solve') !== false) {
            return "📌 **Core Mathematical Principle:**\nTo solve this question systematically, identify the given variables, select the governing theorem or formula, and isolate the unknown step by step.\n\n📝 **Step-by-Step Breakdown:**\n1. **Identify the Given Information:** Extract all numerical values, boundary conditions, or geometric relationships from your question.\n2. **Select the Governing Formula:** Match the problem with standard theorems (e.g. Pythagoras Theorem, Quadratic Formula, or Algebraic Identities).\n3. **Substitute & Simplify:** Substitute known values and maintain mathematical balance by applying operations equally to both sides.\n4. **Verify Units & Arithmetic:** Double check arithmetic signs (+/-) and verify that the result makes logical sense.\n\n💡 **Study Tip:** Always write down the general formula first before plugging in numbers. Your teacher can also review your specific solution!";
        }

        if (strpos($combined, 'physic') !== false || strpos($combined, 'force') !== false || strpos($combined, 'motion') !== false || strpos($combined, 'circuit') !== false || strpos($combined, 'light') !== false || strpos($combined, 'velocity') !== false) {
            return "📌 **Fundamental Physics Concept:**\nThis question centers on physical interaction and governing physical laws (Newton's Laws, Work-Energy, or Electricity Principles).\n\n📝 **Step-by-Step Explanation:**\n1. **Define the Physical System:** List all known values (initial velocity, acceleration, time, mass, resistance, or voltage).\n2. **Apply the Governing Law:** Choose the appropriate equation (e.g., v = u + at, F = ma, or Ohm's Law V = IR).\n3. **Check SI Units:** Ensure all values are converted into standard units (meters, seconds, kilograms) before calculating.\n4. **Physical Interpretation:** Check whether the magnitude and direction make practical sense in real world conditions.\n\n💡 **Study Tip:** Draw a quick diagram or free-body sketch first. Your teacher will also verify your specific numerical steps!";
        }

        if (strpos($combined, 'chem') !== false || strpos($combined, 'reaction') !== false || strpos($combined, 'acid') !== false || strpos($combined, 'base') !== false || strpos($combined, 'atom') !== false) {
            return "📌 **Key Chemical Principle:**\nChemical reactions and phenomena depend on electron arrangements, chemical bonds, and the law of conservation of mass.\n\n📝 **Step-by-Step Explanation:**\n1. **Identify Reactants & Products:** Write out the unbalanced chemical equation for the substances involved.\n2. **Balance the Equation:** Ensure the number of atoms of each element is equal on both the left and right sides.\n3. **Note Reaction Conditions:** Review catalysts, temperature, and physical states (s, l, g, aq).\n4. **Mechanisms:** Analyze electron exchange, proton transfer, or molecular rearrangements.\n\n💡 **Study Tip:** Balancing atoms in the order metals -> non-metals -> hydrogen -> oxygen makes reaction balancing fast and reliable!";
        }

        if (strpos($combined, 'bio') !== false || strpos($combined, 'cell') !== false || strpos($combined, 'plant') !== false || strpos($combined, 'human') !== false || strpos($combined, 'organ') !== false) {
            return "📌 **Biological Concept:**\nBiological structures are intimately linked to their functional roles, whether at the cellular, tissue, or systemic level.\n\n📝 **Step-by-Step Explanation:**\n1. **Structure Identification:** Pinpoint the exact cell type, organelle, or organ system involved.\n2. **Process Pathway:** Trace the biological sequence (input -> enzymatic or hormonal process -> physiological outcome).\n3. **Adaptive Importance:** Consider how this mechanism supports homeostasis and organism survival.\n\n💡 **Study Tip:** Pairing structural diagrams with bulleted functions is the most effective way to remember biological processes!";
        }

        return "📌 **Core Academic Concept:**\nThank you for asking! Clarifying questions is the best way to strengthen your understanding of this topic.\n\n📝 **Step-by-Step Academic Guidance:**\n1. **Break Down the Question:** Divide \"{$title}\" into fundamental definitions and core concepts covered in this chapter.\n2. **Review Class Material:** Check the related lesson slides and textbook formulas to identify the standard methodology.\n3. **Apply the Principle:** Follow each procedural step carefully to arrive at the solution.\n\n💡 **Study Tip:** Note this down in your revision book. Your assigned teacher has also been notified and can provide further classroom feedback!";
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
                           CASE 
                               WHEN r.user_role = 'ai' OR r.reply_text LIKE '%<!-- AI_ANSWER -->%' THEN 'Infinity AI Tutor'
                               ELSE COALESCE(st.full_name, tc.full_name, 'Staff') 
                           END AS author_name,
                           CASE 
                               WHEN r.user_role = 'ai' OR r.reply_text LIKE '%<!-- AI_ANSWER -->%' THEN ''
                               ELSE COALESCE(st.profile_image, tc.profile_image) 
                           END AS author_avatar
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

        // 0. Doubts specifically directed to this teacher
        $whereConditions[] = 'd.teacher_user_id = ?';
        $params[] = $teacherUserId;

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
                       CASE 
                           WHEN r.user_role = 'ai' OR r.reply_text LIKE '%<!-- AI_ANSWER -->%' THEN 'Infinity AI Tutor'
                           ELSE COALESCE(st.full_name, tc.full_name, 'Staff') 
                       END AS author_name,
                       CASE 
                           WHEN r.user_role = 'ai' OR r.reply_text LIKE '%<!-- AI_ANSWER -->%' THEN ''
                           ELSE COALESCE(st.profile_image, tc.profile_image) 
                       END AS author_avatar
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

            $ids = [$studentUserId];
            try {
                $s = $db->prepare("SELECT user_id, id FROM students WHERE user_id = ? OR id = ? LIMIT 1");
                $s->execute([$studentUserId, $studentUserId]);
                $sRow = $s->fetch();
                if ($sRow) {
                    if (!empty($sRow['user_id'])) $ids[] = (int)$sRow['user_id'];
                    if (!empty($sRow['id'])) $ids[] = (int)$sRow['id'];
                }
            } catch (\Throwable $e) {}
            $ids = array_values(array_unique(array_filter($ids)));
            $inClause = implode(',', array_fill(0, count($ids), '?'));

            $where = ["d.student_user_id IN ($inClause)"];
            $params = $ids;

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
                           CASE 
                               WHEN r.user_role = 'ai' OR r.reply_text LIKE '%<!-- AI_ANSWER -->%' THEN 'Infinity AI Tutor'
                               ELSE COALESCE(st.full_name, tc.full_name, 'Staff') 
                           END AS author_name,
                           CASE 
                               WHEN r.user_role = 'ai' OR r.reply_text LIKE '%<!-- AI_ANSWER -->%' THEN ''
                               ELSE COALESCE(st.profile_image, tc.profile_image) 
                           END AS author_avatar
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

            $ids = [$studentUserId];
            try {
                $s = $db->prepare("SELECT user_id, id FROM students WHERE user_id = ? OR id = ? LIMIT 1");
                $s->execute([$studentUserId, $studentUserId]);
                $sRow = $s->fetch();
                if ($sRow) {
                    if (!empty($sRow['user_id'])) $ids[] = (int)$sRow['user_id'];
                    if (!empty($sRow['id'])) $ids[] = (int)$sRow['id'];
                }
            } catch (\Throwable $e) {}
            $ids = array_values(array_unique(array_filter($ids)));
            $inClause = implode(',', array_fill(0, count($ids), '?'));

            $stmt = $db->prepare("
                SELECT 
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 'answered' THEN 1 ELSE 0 END) AS answered,
                    SUM(CASE WHEN status = 'open' THEN 1 ELSE 0 END) AS open
                FROM lesson_doubts
                WHERE student_user_id IN ($inClause)
            ");
            $stmt->execute($ids);
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
