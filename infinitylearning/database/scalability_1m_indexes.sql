-- ============================================================================
-- INFINITY LEARNING: 1 MILLION USERS SCALABILITY & PERFORMANCE INDEX MIGRATION
-- ============================================================================
-- Execute these statements in phpMyAdmin or the Hostinger MySQL Query Console.
-- These composite and targeted indexes prevent full-table scans when database
-- rows scale past 1,000,000+ entries across schools, students, and attendances.
-- ============================================================================

-- 1. ATTENDANCES (Projected: 20M - 50M rows/year)
-- Optimizes daily attendance query by student and month/date range:
-- SELECT * FROM attendances WHERE student_id = ? AND date BETWEEN ? AND ?
CREATE INDEX IF NOT EXISTS idx_attendances_student_date 
    ON attendances (student_id, date);

-- Optimizes school-wide and class-wide attendance lookup for teachers/admins:
CREATE INDEX IF NOT EXISTS idx_attendances_school_class_date 
    ON attendances (school_id, class_level, date);

-- 2. ACADEMIC ASSESSMENTS & MARKS (Projected: 5M - 15M rows)
-- Optimizes marks fetch for student development profile and parent pass:
CREATE INDEX IF NOT EXISTS idx_assessment_sub_student_date 
    ON assessment_submissions (student_id, submitted_at);

-- Optimizes assessment questions and submissions join:
CREATE INDEX IF NOT EXISTS idx_assessment_sub_assessment_student 
    ON assessment_submissions (assessment_id, student_id);

-- 3. FEE PAYMENTS & INVOICES (Projected: 2M - 5M rows)
-- Speeds up student fee history and pending dues lookup:
CREATE INDEX IF NOT EXISTS idx_fees_student_status 
    ON fee_payments (student_id, status);

CREATE INDEX IF NOT EXISTS idx_fees_school_month 
    ON fee_payments (school_id, fee_month, academic_year);

-- 4. TIMETABLE & CLASS SCHEDULE
-- Eliminates latency when students load their 6-day timetable:
CREATE INDEX IF NOT EXISTS idx_timetable_school_class_sec 
    ON timetable_entries (school_id, class_level, section);

-- 5. NOTIFICATIONS (Projected: 10M+ rows)
-- Speeds up unread badge query: SELECT COUNT(*) FROM notifications WHERE user_id = ? AND is_read = 0
CREATE INDEX IF NOT EXISTS idx_notif_user_unread_created 
    ON notifications (user_id, is_read, created_at);

-- 6. AUDIT LOGS & USER SESSIONS
-- Speeds up security audit log inspections and admin activity audits:
CREATE INDEX IF NOT EXISTS idx_audit_user_created 
    ON audit_logs (user_id, created_at);

CREATE INDEX IF NOT EXISTS idx_audit_school_action 
    ON audit_logs (school_id, action, created_at);

-- 7. USERS & PROFILES
-- Ensures instant lookup on email, mobile, role, and approval status:
CREATE INDEX IF NOT EXISTS idx_users_role_status 
    ON users (role, status);

CREATE INDEX IF NOT EXISTS idx_users_school_role 
    ON users (school_id, role, status);

-- ============================================================================
-- NOTE ON SERVER CONFIGURATION (php.ini & my.cnf for 1M Users):
-- 1. innodb_buffer_pool_size: Set to 70-80% of available server RAM.
-- 2. innodb_log_file_size: 256M or 512M to handle bulk attendance commits.
-- 3. max_connections: Increase to 500 or 1000 with ProxySQL connection pooling.
-- ============================================================================
