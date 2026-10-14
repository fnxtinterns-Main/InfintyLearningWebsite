-- =========================================================
-- INFINITY LEARNING — Full Database Schema
-- Aligned with Platform Architecture & Development Roadmap
-- Roles: student, teacher, admin
-- Phases covered: 1 (auth), 2 (batches/schools), 3 (learning),
--                 4 (AI), 5 (activities), 6 (attendance),
--                 7 (analytics-ready), 8 (portfolio-ready)
-- =========================================================

DROP DATABASE IF EXISTS infinitylearning;

CREATE DATABASE infinitylearning
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE infinitylearning;

-- =========================================================
-- 1. USERS (central identity — phone-OTP verified, email login)
-- =========================================================
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    phone VARCHAR(20) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('student','teacher','admin') NOT NULL,
    status ENUM('pending','active','suspended','rejected') DEFAULT 'pending',
    phone_verified_at DATETIME NULL,
    email_verified_at DATETIME NULL,
    last_login_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_status (status),
    INDEX idx_email (email),
    INDEX idx_phone (phone)
) ENGINE=InnoDB;

-- =========================================================
-- 2. STUDENTS (profile) — extended per doc §5
-- =========================================================
CREATE TABLE students (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    profile_image VARCHAR(255) NULL,
    gender ENUM('male','female','other','prefer_not_to_say') NULL,
    age TINYINT UNSIGNED NULL,
    school_name VARCHAR(200) NULL,
    school_board VARCHAR(100) NULL,        -- e.g. CBSE, ICSE, State Board
    current_class VARCHAR(50) NULL,
    academic_year VARCHAR(20) NULL,        -- e.g. 2025-2026
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    interests TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_school (school_name),
    INDEX idx_city (city),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 3. TEACHERS (profile) — extended per doc §6
-- =========================================================
CREATE TABLE teachers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL UNIQUE,
    full_name VARCHAR(150) NOT NULL,
    profile_image VARCHAR(255) NULL,
    gender ENUM('male','female','other','prefer_not_to_say') NULL,
    school_name VARCHAR(200) NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    expert_field VARCHAR(150) NULL,
    years_experience TINYINT UNSIGNED NULL,
    additional_expertise TEXT NULL,
    bio TEXT NULL,
    approved_by BIGINT UNSIGNED NULL,
    approved_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_school (school_name),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================================
-- 4. PARENTS / GUARDIANS (doc §16 lists 'parents' as core)
-- =========================================================
CREATE TABLE parents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,          -- reserved for future parent login
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NULL,
    phone VARCHAR(20) NOT NULL,
    relationship ENUM('father','mother','guardian','other') DEFAULT 'guardian',
    occupation VARCHAR(150) NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_phone (phone),
    INDEX idx_email (email),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Link table: which parent belongs to which student
CREATE TABLE student_parents (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_user_id BIGINT UNSIGNED NOT NULL,
    parent_id BIGINT UNSIGNED NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_student_parent (student_user_id, parent_id),
    FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES parents(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 5. OTPs (registration, password reset, phone change)
-- =========================================================
CREATE TABLE otps (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    phone VARCHAR(20) NOT NULL,
    otp_hash VARCHAR(255) NOT NULL,
    purpose ENUM('registration','password_reset','phone_change') NOT NULL,
    attempts TINYINT UNSIGNED DEFAULT 0,
    expires_at DATETIME NOT NULL,
    consumed_at DATETIME NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_phone_purpose (phone, purpose),
    INDEX idx_expires (expires_at)
) ENGINE=InnoDB;

-- =========================================================
-- 6. PASSWORD RESETS
-- =========================================================
CREATE TABLE password_resets (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    consumed_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 7. SESSIONS (server-side session tracking / remember-me)
-- =========================================================
CREATE TABLE sessions (
    id CHAR(64) PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    last_activity DATETIME NOT NULL,
    expires_at DATETIME NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 8. AUDIT LOGS (security — doc §18)
-- =========================================================
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    entity_type VARCHAR(50) NULL,
    entity_id BIGINT UNSIGNED NULL,
    metadata JSON NULL,
    ip_address VARCHAR(45) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
) ENGINE=InnoDB;

-- =========================================================
-- 9. RATE LIMITS
-- =========================================================
CREATE TABLE rate_limits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    identifier VARCHAR(150) NOT NULL,
    action VARCHAR(50) NOT NULL,
    attempts INT UNSIGNED DEFAULT 0,
    window_start DATETIME NOT NULL,
    UNIQUE KEY uk_identifier_action (identifier, action)
) ENGINE=InnoDB;

-- =========================================================
-- 10. SCHOOLS (doc §16 — separate entity)
-- =========================================================
CREATE TABLE schools (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    name VARCHAR(200) NOT NULL,
    board VARCHAR(100) NULL,              -- CBSE, ICSE, State, IB, etc.
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    address TEXT NULL,
    contact_email VARCHAR(255) NULL,
    contact_phone VARCHAR(20) NULL,
    website VARCHAR(255) NULL,
    logo VARCHAR(255) NULL,
    status ENUM('active','archived') DEFAULT 'active',
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_city (city),
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================================
-- 11. CLASSES (doc §16 — classes and sections per school)
-- =========================================================
CREATE TABLE classes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    school_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(50) NOT NULL,             -- e.g. "Grade 8"
    section VARCHAR(10) NULL,              -- e.g. "A"
    academic_year VARCHAR(20) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_school (school_id),
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 12. BATCHES (doc §11 — teacher-managed groups)
-- =========================================================
CREATE TABLE batches (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    school_id BIGINT UNSIGNED NULL,
    class_id BIGINT UNSIGNED NULL,
    name VARCHAR(150) NOT NULL,
    description TEXT NULL,
    subject VARCHAR(100) NULL,
    class_level VARCHAR(50) NULL,
    teacher_id BIGINT UNSIGNED NULL,
    cover_image VARCHAR(255) NULL,
    capacity INT UNSIGNED DEFAULT 50,
    status ENUM('active','archived') DEFAULT 'active',
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE SET NULL,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_teacher (teacher_id),
    INDEX idx_class (class_id)
) ENGINE=InnoDB;

CREATE TABLE batch_students (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    batch_id BIGINT UNSIGNED NOT NULL,
    student_user_id BIGINT UNSIGNED NOT NULL,
    joined_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_batch_student (batch_id, student_user_id),
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE CASCADE,
    FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 13. SUBJECTS + LESSONS (doc §7 — data-driven hierarchy)
-- Hierarchy: Subject -> Class -> Chapter -> Topic -> Concept
-- =========================================================
CREATE TABLE subjects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL UNIQUE,
    slug VARCHAR(120) NOT NULL UNIQUE,
    icon VARCHAR(60) NULL,
    color VARCHAR(20) NULL,
    description TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE chapters (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subject_id BIGINT UNSIGNED NOT NULL,
    class_level VARCHAR(50) NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT NULL,
    sort_order INT UNSIGNED DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_subject (subject_id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE topics (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    chapter_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(200) NOT NULL,
    description TEXT NULL,
    sort_order INT UNSIGNED DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_chapter (chapter_id),
    FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE lessons (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subject_id BIGINT UNSIGNED NOT NULL,
    chapter_id BIGINT UNSIGNED NULL,
    topic_id BIGINT UNSIGNED NULL,
    batch_id BIGINT UNSIGNED NULL,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(200) NOT NULL,
    summary TEXT NULL,
    content LONGTEXT NULL,
    video_url VARCHAR(500) NULL,
    attachment VARCHAR(255) NULL,
    is_ai_generated TINYINT(1) DEFAULT 0,
    difficulty ENUM('easy','medium','hard') DEFAULT 'medium',
    estimated_minutes INT UNSIGNED DEFAULT 15,
    published TINYINT(1) DEFAULT 1,
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (chapter_id) REFERENCES chapters(id) ON DELETE SET NULL,
    FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE SET NULL,
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_subject (subject_id),
    INDEX idx_batch (batch_id)
) ENGINE=InnoDB;

CREATE TABLE lesson_progress (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lesson_id BIGINT UNSIGNED NOT NULL,
    student_user_id BIGINT UNSIGNED NOT NULL,
    status ENUM('not_started','in_progress','completed') DEFAULT 'not_started',
    progress_percent TINYINT UNSIGNED DEFAULT 0,
    last_viewed_at DATETIME NULL,
    completed_at DATETIME NULL,
    UNIQUE KEY uk_lesson_student (lesson_id, student_user_id),
    FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 14. AI LEARNING (doc §8, §16 — reserved for Phase 4)
-- =========================================================
CREATE TABLE ai_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    student_user_id BIGINT UNSIGNED NOT NULL,
    subject_id BIGINT UNSIGNED NULL,
    topic_id BIGINT UNSIGNED NULL,
    mode ENUM('explain','practice','evaluate','recommend','chat') DEFAULT 'practice',
    context JSON NULL,
    started_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    ended_at DATETIME NULL,
    FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE SET NULL,
    FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE SET NULL,
    INDEX idx_student (student_user_id)
) ENGINE=InnoDB;

CREATE TABLE ai_questions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ai_session_id BIGINT UNSIGNED NOT NULL,
    question_text TEXT NOT NULL,
    expected_answer TEXT NULL,
    difficulty ENUM('easy','medium','hard') DEFAULT 'medium',
    generated_by ENUM('ai','teacher','imported') DEFAULT 'ai',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ai_session_id) REFERENCES ai_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE ai_answers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ai_question_id BIGINT UNSIGNED NOT NULL,
    student_user_id BIGINT UNSIGNED NOT NULL,
    answer_text TEXT NULL,
    is_correct TINYINT(1) NULL,
    ai_feedback TEXT NULL,
    score DECIMAL(5,2) NULL,
    answered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (ai_question_id) REFERENCES ai_questions(id) ON DELETE CASCADE,
    FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE student_knowledge_profile (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_user_id BIGINT UNSIGNED NOT NULL,
    subject_id BIGINT UNSIGNED NOT NULL,
    topic_id BIGINT UNSIGNED NULL,
    mastery_percent TINYINT UNSIGNED DEFAULT 0,
    strength_level ENUM('weak','developing','strong','mastered') DEFAULT 'developing',
    last_evaluated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_student_topic (student_user_id, subject_id, topic_id),
    FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (topic_id) REFERENCES topics(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =========================================================
-- 15. ACTIVITIES (doc §9, §10 — configurable, admin-managed)
-- =========================================================
CREATE TABLE activities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    category ENUM('creative','academic','sports','cultural','technology','communication','competition') NOT NULL,
    short_description VARCHAR(300) NULL,
    description LONGTEXT NULL,
    cover_image VARCHAR(255) NULL,
    gallery JSON NULL,
    event_date DATE NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    venue VARCHAR(200) NULL,
    organizer VARCHAR(150) NULL,
    eligibility VARCHAR(200) NULL,
    eligible_classes VARCHAR(300) NULL,   -- doc §10: "Select Eligible Classes/Batches"
    batch_id BIGINT UNSIGNED NULL,        -- doc §12: teacher 'Next Activity' for a batch
    registration_deadline DATE NULL,
    registration_url VARCHAR(500) NULL,
    fee DECIMAL(10,2) DEFAULT 0.00,
    max_participants INT UNSIGNED NULL,
    contact_email VARCHAR(150) NULL,
    contact_phone VARCHAR(20) NULL,
    tags VARCHAR(300) NULL,
    featured TINYINT(1) DEFAULT 0,
    status ENUM('draft','published','closed','archived') DEFAULT 'published',
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE SET NULL,
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_event_date (event_date)
) ENGINE=InnoDB;

CREATE TABLE activity_registrations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    activity_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NOT NULL,
    notes TEXT NULL,
    status ENUM('registered','approved','rejected','attended','absent') DEFAULT 'registered',
    registered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_activity_user (activity_id, user_id),
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE activity_results (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    activity_id BIGINT UNSIGNED NOT NULL,
    student_user_id BIGINT UNSIGNED NOT NULL,
    score DECIMAL(7,2) NULL,
    position INT UNSIGNED NULL,          -- 1st, 2nd, 3rd, etc.
    remarks TEXT NULL,
    recorded_by BIGINT UNSIGNED NULL,
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_activity_student (activity_id, student_user_id),
    FOREIGN KEY (activity_id) REFERENCES activities(id) ON DELETE CASCADE,
    FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================================
-- 16. COMPETITIONS (doc §9, §10 — a specialized activity type)
-- =========================================================
CREATE TABLE competitions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    title VARCHAR(200) NOT NULL,
    slug VARCHAR(220) NOT NULL UNIQUE,
    category VARCHAR(100) NULL,           -- e.g. "Abacus", "Spelling", "Debate"
    level ENUM('school','district','state','national','international','online') DEFAULT 'school',
    description LONGTEXT NULL,
    event_date DATE NULL,
    venue VARCHAR(200) NULL,
    registration_deadline DATE NULL,
    fee DECIMAL(10,2) DEFAULT 0.00,
    cover_image VARCHAR(255) NULL,
    status ENUM('draft','published','closed','archived') DEFAULT 'published',
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE competition_participants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    competition_id BIGINT UNSIGNED NOT NULL,
    student_user_id BIGINT UNSIGNED NOT NULL,
    status ENUM('registered','participated','qualified','disqualified') DEFAULT 'registered',
    registered_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_comp_student (competition_id, student_user_id),
    FOREIGN KEY (competition_id) REFERENCES competitions(id) ON DELETE CASCADE,
    FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE competition_results (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    competition_id BIGINT UNSIGNED NOT NULL,
    student_user_id BIGINT UNSIGNED NOT NULL,
    rank_position INT UNSIGNED NULL,
    score DECIMAL(7,2) NULL,
    certificate_url VARCHAR(500) NULL,
    remarks TEXT NULL,
    recorded_by BIGINT UNSIGNED NULL,
    recorded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_comp_student_result (competition_id, student_user_id),
    FOREIGN KEY (competition_id) REFERENCES competitions(id) ON DELETE CASCADE,
    FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (recorded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================================
-- 17. ATTENDANCE (doc §13)
-- =========================================================
CREATE TABLE attendance_sessions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    batch_id BIGINT UNSIGNED NOT NULL,
    session_date DATE NOT NULL,
    session_label VARCHAR(100) NULL,
    marked_by BIGINT UNSIGNED NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_batch_session (batch_id, session_date, session_label),
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE attendance (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    batch_id BIGINT UNSIGNED NOT NULL,
    student_user_id BIGINT UNSIGNED NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present','absent','late','excused') NOT NULL,
    marked_by BIGINT UNSIGNED NULL,
    remarks VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_batch_student_date (batch_id, student_user_id, attendance_date),
    FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE CASCADE,
    FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_date (attendance_date),
    INDEX idx_student (student_user_id)
) ENGINE=InnoDB;

-- =========================================================
-- 18. CERTIFICATES + ACHIEVEMENTS (doc §14, §16)
-- =========================================================
CREATE TABLE certificates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    student_user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    type ENUM('participation','achievement','completion','winner','merit') DEFAULT 'participation',
    issued_for ENUM('activity','competition','course','lesson','other') NULL,
    reference_id BIGINT UNSIGNED NULL,   -- activity_id / competition_id / lesson_id
    certificate_url VARCHAR(500) NULL,
    issued_by BIGINT UNSIGNED NULL,
    issued_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (issued_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_student (student_user_id)
) ENGINE=InnoDB;

CREATE TABLE achievements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_user_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NULL,
    category ENUM('academic','sports','creative','communication','problem_solving','competition','other') DEFAULT 'other',
    badge_icon VARCHAR(255) NULL,
    earned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    awarded_by BIGINT UNSIGNED NULL,
    FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (awarded_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_student (student_user_id),
    INDEX idx_category (category)
) ENGINE=InnoDB;

-- =========================================================
-- 19. NOTIFICATIONS + ANNOUNCEMENTS (doc §20)
-- =========================================================
CREATE TABLE notifications (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    type VARCHAR(60) NOT NULL,           -- e.g. 'activity.assigned', 'result.published'
    title VARCHAR(200) NOT NULL,
    body TEXT NULL,
    link VARCHAR(500) NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_unread (user_id, is_read)
) ENGINE=InnoDB;

CREATE TABLE announcements (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    body TEXT NOT NULL,
    audience ENUM('all','students','teachers','admins') DEFAULT 'all',
    pinned TINYINT(1) DEFAULT 0,
    created_by BIGINT UNSIGNED NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- =========================================================
-- 20. FILES (doc §19 — store metadata, not blobs)
-- =========================================================
CREATE TABLE files (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    uuid CHAR(36) NOT NULL UNIQUE,
    owner_user_id BIGINT UNSIGNED NULL,
    original_name VARCHAR(255) NOT NULL,
    stored_path VARCHAR(500) NOT NULL,
    mime_type VARCHAR(100) NULL,
    size_bytes BIGINT UNSIGNED NULL,
    purpose ENUM('profile_image','lesson_attachment','activity_cover','certificate','submission','other') DEFAULT 'other',
    related_table VARCHAR(50) NULL,
    related_id BIGINT UNSIGNED NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (owner_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_owner (owner_user_id),
    INDEX idx_purpose (purpose)
) ENGINE=InnoDB;

-- =========================================================
-- 21. SEED DATA — Subjects (doc §7)
-- =========================================================
INSERT INTO subjects (name, slug, icon, color) VALUES
('Mathematics',     'mathematics',     '➗', '#FF7A18'),
('Physics',         'physics',         '⚛️', '#2563EB'),
('Chemistry',       'chemistry',       '🧪', '#16A34A'),
('Biology',         'biology',         '🧬', '#10B981'),
('English',         'english',         '📚', '#3B82F6'),
('Hindi',           'hindi',           '🕉️', '#F97316'),
('Sanskrit',        'sanskrit',        '📜', '#D97706'),
('Computer Science','computer-science','💻', '#EC4899'),
('Social Science',  'social-science',  '🌍', '#A855F7'),
('AI & Innovation', 'ai-innovation',   '🤖', '#F59E0B');

-- =========================================================
-- END OF SCHEMA
-- =========================================================