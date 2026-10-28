-- ============================================================================
-- INFINITY LEARNING: 1 MILLION USERS SCALABILITY & PERFORMANCE INDEX MIGRATION
-- ============================================================================
-- Execute these statements in phpMyAdmin or the Hostinger MySQL Query Console.
-- Exact table and column names matching the Infinity Learning database schema.
-- ============================================================================

-- 1. ATTENDANCE (Table: attendance)
-- Optimizes student attendance history and parent pass date queries:
ALTER TABLE `attendance` 
    ADD INDEX `idx_att_student_date` (`student_user_id`, `attendance_date`);

ALTER TABLE `attendance` 
    ADD INDEX `idx_att_batch_date` (`batch_id`, `attendance_date`);


-- 2. STUDENT FEES & INVOICES (Table: student_fees)
-- Speeds up student invoice loading, overdue checks, and receipt lookups:
ALTER TABLE `student_fees` 
    ADD INDEX `idx_fees_student_status` (`student_user_id`, `status`);

ALTER TABLE `student_fees` 
    ADD INDEX `idx_fees_school_month` (`school_id`, `fee_month`, `academic_year`);


-- 3. ASSESSMENTS & SUBMISSIONS (Table: assessment_submissions)
-- Speeds up student mark sheets, development profile, and grade analytics:
ALTER TABLE `assessment_submissions` 
    ADD INDEX `idx_sub_student_date` (`student_user_id`, `submitted_at`);

ALTER TABLE `assessment_submissions` 
    ADD INDEX `idx_sub_assessment_student` (`assessment_id`, `student_user_id`);


-- 4. TIMETABLES (Table: timetables)
-- Instant timetable rendering for student routines and school class schedules:
ALTER TABLE `timetables` 
    ADD INDEX `idx_tt_school_class_sec` (`school_id`, `class_level`, `section`);


-- 5. NOTIFICATIONS (Table: notifications)
-- Instant unread notifications badge calculation:
ALTER TABLE `notifications` 
    ADD INDEX `idx_notif_user_unread` (`user_id`, `is_read`, `created_at`);


-- 6. AUDIT LOGS (Table: audit_logs)
-- Speeds up security checks and admin activity logs:
ALTER TABLE `audit_logs` 
    ADD INDEX `idx_audit_user_created` (`user_id`, `created_at`);


-- 7. USERS (Table: users)
-- Speeds up multi-school user authentication, role checks, and approval lists:
ALTER TABLE `users` 
    ADD INDEX `idx_users_role_status` (`role`, `status`);

ALTER TABLE `users` 
    ADD INDEX `idx_users_school_role` (`school_id`, `role`, `status`);
