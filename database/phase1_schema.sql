-- SPVAI Phase 1: Database Foundation Schema
-- This script creates the new request-centric database structure while preserving legacy tables.

SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- 1. USERS TABLE
-- Handles both students and administrators
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `user_id` INT(11) NOT NULL AUTO_INCREMENT,
  `student_id` VARCHAR(50) DEFAULT NULL,
  `first_name` VARCHAR(100) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `role` VARCHAR(20) NOT NULL DEFAULT 'student', -- 'student', 'admin'
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `uk_student_id` (`student_id`),
  UNIQUE KEY `uk_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 2. DOCUMENT_TYPES TABLE
-- Defines documents available for request (SF10, COE, etc.)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `document_types` (
  `document_id` INT(11) NOT NULL AUTO_INCREMENT,
  `document_name` VARCHAR(100) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `processing_days` INT(11) DEFAULT 0,
  `fee` DECIMAL(10,2) DEFAULT 0.00,
  `active` TINYINT(1) DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`document_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Seed Document Types from Audit
INSERT IGNORE INTO `document_types` (`document_name`, `description`, `fee`) VALUES
('SF10', 'Student Permanent Record', 0.00),
('COE', 'Certificate of Enrollment', 0.00),
('COG', 'Certificate of Graduation', 0.00),
('EMOI', 'English Medium of Instructions', 0.00);

-- --------------------------------------------------------
-- 3. REQUESTS TABLE
-- The core entity representing a student's request for a document
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `requests` (
  `request_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `document_id` INT(11) NOT NULL,
  `purpose` TEXT DEFAULT NULL,
  `copies` INT(11) DEFAULT 1,
  `status` VARCHAR(30) DEFAULT 'Pending', -- 'Pending', 'Approved', 'Rejected', 'Processing', 'Ready', 'Completed', 'Cancelled'
  `remarks` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`request_id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_document_id` (`document_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_requests_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_requests_doc` FOREIGN KEY (`document_id`) REFERENCES `document_types` (`document_id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 4. APPOINTMENTS TABLE
-- Scheduling for request pickup or processing
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `appointments` (
  `appointment_id` INT(11) NOT NULL AUTO_INCREMENT,
  `request_id` INT(11) NOT NULL,
  `appointment_date` DATE NOT NULL,
  `appointment_time` TIME NOT NULL,
  `status` VARCHAR(30) DEFAULT 'Scheduled', -- 'Scheduled', 'Confirmed', 'Completed', 'Cancelled', 'No Show'
  `remarks` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`appointment_id`),
  KEY `idx_request_id` (`request_id`),
  KEY `idx_app_date` (`appointment_date`),
  CONSTRAINT `fk_appointments_request` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 5. PAYMENTS TABLE
-- Tracking of payments associated with a specific request
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `payments` (
  `payment_id` INT(11) NOT NULL AUTO_INCREMENT,
  `request_id` INT(11) NOT NULL,
  `amount` DECIMAL(10,2) NOT NULL,
  `payment_method` VARCHAR(50) DEFAULT NULL, -- 'Cash', 'GCash', 'Bank Transfer', 'Other'
  `reference_number` VARCHAR(100) DEFAULT NULL,
  `payment_status` VARCHAR(30) DEFAULT 'Unpaid', -- 'Unpaid', 'Pending Verification', 'Paid', 'Rejected', 'Refunded'
  `payment_date` DATETIME DEFAULT NULL,
  `verified_by` INT(11) DEFAULT NULL,
  `verified_at` DATETIME DEFAULT NULL,
  `remarks` TEXT DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`payment_id`),
  KEY `idx_payment_request` (`request_id`),
  KEY `idx_payment_status` (`payment_status`),
  CONSTRAINT `fk_payments_request` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_payments_verifier` FOREIGN KEY (`verified_by`) REFERENCES `users` (`user_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- 6. NOTIFICATIONS TABLE
-- Alerts sent to users regarding their requests
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `notifications` (
  `notification_id` INT(11) NOT NULL AUTO_INCREMENT,
  `user_id` INT(11) NOT NULL,
  `request_id` INT(11) NOT NULL,
  `type` VARCHAR(50) DEFAULT NULL, -- 'Request Update', 'Payment Update', 'Appointment Update', 'Document Ready'
  `message` TEXT NOT NULL,
  `is_read` TINYINT(1) DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`notification_id`),
  KEY `idx_notif_user` (`user_id`),
  KEY `idx_notif_request` (`request_id`),
  CONSTRAINT `fk_notifications_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `fk_notifications_request` FOREIGN KEY (`request_id`) REFERENCES `requests` (`request_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
