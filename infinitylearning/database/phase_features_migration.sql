-- =========================================================
-- INFINITY LEARNING — Phase 1, 2 & 3 Upgrade Migration
-- Features:
-- 1. Question Bank & Assessment Manager (Homework, Unit Test, Mid/Final Terms)
-- 2. Student Monthly Fee Management & Receipts
-- 3. School Class Routine & Timetable Planner
-- 4. 10 MCQ Quizzes per Lesson
-- 5. School White-Labeling & Branding Customizer
-- 6. Interactive Whiteboard Integration
-- 7. Cloneable Curriculum Modules
-- 8. "Ask a Doubt" Discussion Feed
-- =========================================================

-- 1. QUESTION BANK
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

-- 2. ACADEMIC ASSESSMENTS (Homework, Unit Test, Mid Terms, Final Terms)
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

-- 3. ASSESSMENT SUBMISSIONS
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

-- 4. STUDENT FEES
CREATE TABLE IF NOT EXISTS `student_fees` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) UNSIGNED DEFAULT NULL,
  `batch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `student_user_id` bigint(20) UNSIGNED NOT NULL,
  `academic_year` varchar(20) NOT NULL DEFAULT '2026-2027',
  `fee_month` varchar(20) NOT NULL,
  `fee_type` enum('tuition','admission','transport','exam','chess_academy','activity') NOT NULL DEFAULT 'tuition',
  `amount_due` decimal(10,2) NOT NULL DEFAULT 0.00,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `due_date` date DEFAULT NULL,
  `status` enum('pending','partial','paid','overdue','waived') NOT NULL DEFAULT 'pending',
  `payment_mode` enum('upi','cash','bank_transfer','card','cheque') DEFAULT NULL,
  `receipt_number` varchar(50) DEFAULT NULL,
  `paid_at` datetime DEFAULT NULL,
  `recorded_by` bigint(20) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_sf_student` (`student_user_id`),
  KEY `idx_sf_school` (`school_id`),
  KEY `idx_sf_batch` (`batch_id`),
  KEY `idx_sf_month` (`fee_month`),
  KEY `idx_sf_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. SCHOOL TIMETABLES
CREATE TABLE IF NOT EXISTS `timetables` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `school_id` bigint(20) UNSIGNED NOT NULL,
  `class_level` varchar(50) NOT NULL,
  `section` varchar(10) DEFAULT 'A',
  `day_of_week` enum('monday','tuesday','wednesday','thursday','friday','saturday') NOT NULL,
  `period_number` tinyint(3) UNSIGNED NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `subject_id` bigint(20) UNSIGNED NOT NULL,
  `teacher_user_id` bigint(20) UNSIGNED NOT NULL,
  `room_number` varchar(30) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_timetable_slot` (`school_id`, `class_level`, `section`, `day_of_week`, `period_number`),
  KEY `idx_tt_teacher` (`teacher_user_id`),
  KEY `idx_tt_school` (`school_id`),
  KEY `idx_tt_day` (`day_of_week`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. LESSON QUIZZES (10 MCQs per lesson)
CREATE TABLE IF NOT EXISTS `lesson_quizzes` (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `lesson_id` bigint(20) UNSIGNED NOT NULL,
  `questions_json` longtext NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_lq_lesson` (`lesson_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. LESSON QUIZ ATTEMPTS
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

-- 8. ASK A DOUBT (Threaded Discussion Feed)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. SCHOOL WHITE-LABELING COLUMNS
ALTER TABLE `schools`
  ADD COLUMN IF NOT EXISTS `brand_primary_color` varchar(20) DEFAULT '#0A192F',
  ADD COLUMN IF NOT EXISTS `brand_secondary_color` varchar(20) DEFAULT '#BE9420',
  ADD COLUMN IF NOT EXISTS `brand_tagline` varchar(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `crest_logo` varchar(500) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `principal_signature` varchar(500) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `official_seal` varchar(500) DEFAULT NULL;
