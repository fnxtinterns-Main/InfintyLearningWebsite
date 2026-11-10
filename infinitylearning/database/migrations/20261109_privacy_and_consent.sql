-- Migration: 20261109_privacy_and_consent.sql
-- Adds columns for DPDP/GDPR compliance, consent recording, and privacy requests.

USE u999011396_inflearning;

ALTER TABLE users 
ADD COLUMN IF NOT EXISTS terms_accepted_at DATETIME NULL AFTER email_verified_at,
ADD COLUMN IF NOT EXISTS terms_accepted_ip VARCHAR(45) NULL AFTER terms_accepted_at,
ADD COLUMN IF NOT EXISTS parent_consent_at DATETIME NULL AFTER terms_accepted_ip,
ADD COLUMN IF NOT EXISTS marketing_consent TINYINT(1) DEFAULT 0 AFTER parent_consent_at;

CREATE TABLE IF NOT EXISTS privacy_requests (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NULL,
    request_type ENUM('access_export', 'deletion_erasure', 'correction', 'marketing_optout', 'nomination') NOT NULL,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(30) NULL,
    relationship_to_student VARCHAR(50) NULL,
    details TEXT NULL,
    status ENUM('pending', 'in_progress', 'completed', 'rejected') DEFAULT 'pending',
    ip_address VARCHAR(45) NULL,
    admin_notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS email_unsubscribes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    reason VARCHAR(255) NULL,
    ip_address VARCHAR(45) NULL,
    unsubscribed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_email (email),
    INDEX idx_user (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
