-- Gender Desk App System Database Schema
-- IEEE 830-1998 Compliant
-- MySQL 8.x / MariaDB 10.x

-- Create Database
CREATE DATABASE IF NOT EXISTS `gender_desk_db`;
USE `gender_desk_db`;

-- Set default character set
ALTER DATABASE `gender_desk_db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- 1. USERS TABLE (Authentication & RBAC)
CREATE TABLE IF NOT EXISTS `users` (
    `user_id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `email` VARCHAR(100) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL,
    `role_id` INT NOT NULL,
    `is_active` BOOLEAN DEFAULT TRUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_username` (`username`),
    INDEX `idx_email` (`email`),
    INDEX `idx_role_id` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. ROLES TABLE (Role-Based Access Control)
CREATE TABLE IF NOT EXISTS `roles` (
    `role_id` INT AUTO_INCREMENT PRIMARY KEY,
    `role_name` VARCHAR(50) NOT NULL UNIQUE,
    `description` TEXT,
    `permissions` JSON,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. INCIDENTS TABLE (GBV Report Data)
CREATE TABLE IF NOT EXISTS `incidents` (
    `incident_id` INT AUTO_INCREMENT PRIMARY KEY,
    `tracking_id` VARCHAR(16) NOT NULL UNIQUE,
    `incident_type` VARCHAR(100) NOT NULL,
    `incident_date` DATETIME NOT NULL,
    `incident_location` VARCHAR(255) NOT NULL,
    `narrative_description` LONGTEXT NOT NULL,
    `reporter_user_id` INT,
    `is_anonymous` BOOLEAN DEFAULT TRUE,
    `status` ENUM('Pending', 'Under Investigation', 'Resolved', 'Closed') DEFAULT 'Pending',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`reporter_user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
    INDEX `idx_tracking_id` (`tracking_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_reporter_user_id` (`reporter_user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. FILES TABLE (Uploaded Evidence)
CREATE TABLE IF NOT EXISTS `files` (
    `file_id` INT AUTO_INCREMENT PRIMARY KEY,
    `incident_id` INT NOT NULL,
    `original_filename` VARCHAR(255) NOT NULL,
    `stored_filename` VARCHAR(255) NOT NULL,
    `file_path` VARCHAR(500) NOT NULL,
    `mime_type` VARCHAR(100) NOT NULL,
    `file_size` INT NOT NULL,
    `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`incident_id`) REFERENCES `incidents`(`incident_id`) ON DELETE CASCADE,
    INDEX `idx_incident_id` (`incident_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. CASE MANAGEMENT TABLE (Officer Notes & Status Updates)
CREATE TABLE IF NOT EXISTS `case_management` (
    `case_id` INT AUTO_INCREMENT PRIMARY KEY,
    `incident_id` INT NOT NULL UNIQUE,
    `assigned_officer_id` INT,
    `investigation_notes` LONGTEXT,
    `internal_remarks` TEXT,
    `previous_status` VARCHAR(50),
    `current_status` ENUM('Pending', 'Under Investigation', 'Resolved', 'Closed') DEFAULT 'Pending',
    `status_updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`incident_id`) REFERENCES `incidents`(`incident_id`) ON DELETE CASCADE,
    FOREIGN KEY (`assigned_officer_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
    INDEX `idx_incident_id` (`incident_id`),
    INDEX `idx_assigned_officer_id` (`assigned_officer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. AUDIT LOGS TABLE (Immutable Transaction Records)
CREATE TABLE IF NOT EXISTS `audit_logs` (
    `log_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT,
    `action` VARCHAR(100) NOT NULL,
    `target_table` VARCHAR(50),
    `target_record_id` INT,
    `old_value` LONGTEXT,
    `new_value` LONGTEXT,
    `ip_address` VARCHAR(45),
    `user_agent` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE SET NULL,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. CSRF TOKENS TABLE (Cross-Site Request Forgery Protection)
CREATE TABLE IF NOT EXISTS `csrf_tokens` (
    `token_id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT,
    `token` VARCHAR(255) NOT NULL UNIQUE,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `expires_at` TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`user_id`) ON DELETE CASCADE,
    INDEX `idx_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert Default Roles
INSERT INTO `roles` (`role_name`, `description`, `permissions`) VALUES
('System Administrator', 'Technical maintenance and system configuration', '["create_staff_accounts", "assign_roles", "view_system_logs", "manage_database"]'),
('Gender Desk Officer', 'Case management and incident tracking', '["view_incidents", "update_status", "add_notes", "download_evidence", "generate_reports"]'),
('End User', 'Report submission and case tracking', '["submit_report", "track_case", "view_own_case"]');

-- Insert Default Admin User (username: admin, password: Admin@123)
INSERT INTO `users` (`username`, `email`, `password_hash`, `role_id`, `is_active`) VALUES
('admin', 'admin@genderdesk.local', '$2y$12$N9qo8uLOickgx2ZMRZoMyeIjZAgcg7b3XeKeUxWdeS86E36gZvQm2', 1, TRUE);

-- Create Uploads Directory (ensure it exists outside htdocs in production)
-- Note: This SQL doesn't create directories. Use PHP or manual folder creation.