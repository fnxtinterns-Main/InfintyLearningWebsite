-- ============================================================
-- FutureNxt Corporate Management Portal Schema
-- Database: u999011396_futurenxt
-- ============================================================

CREATE TABLE IF NOT EXISTS fnx_employees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    emp_code VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(150) NOT NULL,
    role ENUM('admin', 'ceo', 'cto', 'office_manager', 'manager', 'employee', 'intern') NOT NULL DEFAULT 'employee',
    designation VARCHAR(150) NOT NULL,
    department VARCHAR(100) NOT NULL DEFAULT 'General',
    phone VARCHAR(25) NULL,
    emergency_contact_name VARCHAR(150) NULL,
    emergency_contact_phone VARCHAR(25) NULL,
    emergency_contact_relation VARCHAR(50) NULL,
    dob DATE NULL,
    date_of_joining DATE NULL,
    permanent_address TEXT NULL,
    current_address TEXT NULL,
    photo_path VARCHAR(255) NULL,
    aadhar_number VARCHAR(30) NULL,
    aadhar_doc_path VARCHAR(255) NULL,
    pan_number VARCHAR(30) NULL,
    pan_doc_path VARCHAR(255) NULL,
    bank_name VARCHAR(150) NULL,
    bank_account_no VARCHAR(60) NULL,
    bank_ifsc VARCHAR(30) NULL,
    bank_account_name VARCHAR(150) NULL,
    upi_id VARCHAR(100) NULL,
    resume_doc_path VARCHAR(255) NULL,
    highest_qualification VARCHAR(150) NULL,
    offer_letter_doc_path VARCHAR(255) NULL,
    status ENUM('active', 'suspended', 'pip', 'resigned', 'terminated') NOT NULL DEFAULT 'active',
    pip_reason TEXT NULL,
    pip_end_date DATE NULL,
    created_by BIGINT UNSIGNED NULL,
    last_login_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_role (role),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fnx_tasks (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_code VARCHAR(50) NOT NULL UNIQUE,
    title VARCHAR(255) NOT NULL,
    description LONGTEXT NULL,
    priority ENUM('low', 'medium', 'high', 'urgent') NOT NULL DEFAULT 'medium',
    due_date DATE NOT NULL,
    status ENUM('pending', 'in_progress', 'submitted', 'approved', 'rejected', 'reopened') NOT NULL DEFAULT 'pending',
    created_by BIGINT UNSIGNED NOT NULL,
    doc_path VARCHAR(255) NULL,
    doc_submitted_at DATETIME NULL,
    doc_notes TEXT NULL,
    approved_by BIGINT UNSIGNED NULL,
    approved_at DATETIME NULL,
    review_feedback TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_status (status),
    INDEX idx_due_date (due_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fnx_task_assignees (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    task_id BIGINT UNSIGNED NOT NULL,
    employee_id BIGINT UNSIGNED NOT NULL,
    assigned_by BIGINT UNSIGNED NOT NULL,
    assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    reassigned_to BIGINT UNSIGNED NULL,
    reassigned_at DATETIME NULL,
    reassign_reason TEXT NULL,
    INDEX idx_task_emp (task_id, employee_id),
    INDEX idx_emp_active (employee_id, is_active),
    FOREIGN KEY (task_id) REFERENCES fnx_tasks(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES fnx_employees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fnx_leaves (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    leave_type ENUM('casual', 'sick', 'emergency', 'paid', 'unpaid') NOT NULL DEFAULT 'casual',
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    reviewed_by BIGINT UNSIGNED NULL,
    reviewed_at DATETIME NULL,
    reviewer_remarks TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_emp_leave (employee_id),
    INDEX idx_dates (start_date, end_date),
    FOREIGN KEY (employee_id) REFERENCES fnx_employees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fnx_wfh_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected') NOT NULL DEFAULT 'pending',
    reviewed_by BIGINT UNSIGNED NULL,
    reviewed_at DATETIME NULL,
    reviewer_remarks TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_emp_wfh (employee_id),
    FOREIGN KEY (employee_id) REFERENCES fnx_employees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS fnx_audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    employee_id BIGINT UNSIGNED NULL,
    action VARCHAR(100) NOT NULL,
    target_type VARCHAR(50) NOT NULL,
    target_id BIGINT UNSIGNED NULL,
    details TEXT NULL,
    ip_address VARCHAR(50) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_emp_audit (employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Seed Initial Executive Accounts (Password: Inflearning@99)
-- ============================================================

INSERT INTO fnx_employees (emp_code, email, password_hash, full_name, role, designation, department, status, created_at)
VALUES 
('FNX-ADMIN', 'fnxt.interns@gmail.com', '$2y$10$PyElMHXj9iOldcQRoRCqr.RvjTTI.rT66s2tDDAsQ40Gk.8/.ZsQi', 'FutureNxt Administrator', 'admin', 'System Administrator', 'Operations', 'active', NOW())
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), role = VALUES(role);

INSERT INTO fnx_employees (emp_code, email, password_hash, full_name, role, designation, department, status, created_at)
VALUES 
('FNX-CEO', 'india.futurenxt@gmail.com', '$2y$10$PyElMHXj9iOldcQRoRCqr.RvjTTI.rT66s2tDDAsQ40Gk.8/.ZsQi', 'FutureNxt CEO', 'ceo', 'Chief Executive Officer', 'Executive', 'active', NOW())
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), role = VALUES(role);

INSERT INTO fnx_employees (emp_code, email, password_hash, full_name, role, designation, department, status, created_at)
VALUES 
('FNX-CTO', 'kumaraviraj682@gmail.com', '$2y$10$PyElMHXj9iOldcQRoRCqr.RvjTTI.rT66s2tDDAsQ40Gk.8/.ZsQi', 'Kumar Aviraj', 'cto', 'Chief Technology Officer', 'Engineering', 'active', NOW())
ON DUPLICATE KEY UPDATE full_name = VALUES(full_name), role = VALUES(role);
