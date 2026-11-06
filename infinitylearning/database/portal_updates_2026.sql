-- ====================================================================
-- Infinity Learning - Teacher & Student Portal Updates 2026 Migration
-- ====================================================================

-- 1. Update Attendance Status to include 'leave'
ALTER TABLE `attendance` 
  MODIFY COLUMN `status` ENUM('present','absent','late','excused','leave') NOT NULL;

-- 2. Add teacher_user_id to lesson_doubts so students can select a specific teacher
ALTER TABLE `lesson_doubts` 
  ADD COLUMN `teacher_user_id` BIGINT UNSIGNED DEFAULT NULL AFTER `student_user_id`;

ALTER TABLE `lesson_doubts` 
  ADD KEY `idx_ld_teacher` (`teacher_user_id`);

-- 3. Create teacher_notes table (Visible to Admins & Teachers, strictly hidden from Students)
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. AI-Powered Doubt Portal Support: Allow 'ai' role and is_ai flag on replies
ALTER TABLE `lesson_doubt_replies` 
  MODIFY COLUMN `user_role` VARCHAR(20) NOT NULL DEFAULT 'student';

ALTER TABLE `lesson_doubt_replies` 
  ADD COLUMN `is_ai` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_teacher_endorsed`;

