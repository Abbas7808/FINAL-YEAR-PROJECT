-- MedNova Relational Database Schema
-- Compatible with MySQL 8.0+ / MariaDB 10.4+
-- --------------------------------------------------------

-- 1. Users Table (Core Authentication)
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `username` varchar(100) DEFAULT NULL UNIQUE,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin', 'doctor', 'receptionist', 'patient', 'hospital_admin') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email_role` (`email`, `role`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- 2. Regions Table
CREATE TABLE IF NOT EXISTS `regions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL UNIQUE,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- 3. Hospitals & Clinics (Locations)
CREATE TABLE IF NOT EXISTS `hospitals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `slug` varchar(150) NOT NULL UNIQUE,
  `address` text NOT NULL,
  `contact_info` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `type` enum('Hospital', 'Clinic') DEFAULT 'Hospital',
  `region_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `subscription_status` varchar(20) DEFAULT 'active',
  `plan_type` varchar(20) DEFAULT 'monthly',
  `plan_expires_at` datetime DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`region_id`) REFERENCES `regions`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- 4. Departments (Specialties / Hospital Sections)
CREATE TABLE IF NOT EXISTS `departments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `hospital_id` int(11) DEFAULT NULL,
  `description` text,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`hospital_id`) REFERENCES `hospitals`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- 5. Specializations Table
CREATE TABLE IF NOT EXISTS `specializations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL UNIQUE,
  `description` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- 6. Doctors Details (Profile)
CREATE TABLE IF NOT EXISTS `doctors_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `specialization_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `specialization` varchar(100) NOT NULL,
  `qualification` varchar(150) DEFAULT NULL,
  `experience_years` int(11) DEFAULT 0,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `bio` text DEFAULT NULL,
  `biography` text DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `profile_image` varchar(255) DEFAULT 'default_doctor.png',
  `joining_date` date DEFAULT NULL,
  `rating` decimal(2,1) DEFAULT 5.0,
  `reviews_count` int(11) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`specialization_id`) REFERENCES `specializations`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- 7. Doctor-Hospital Affiliations (Where doctors work)
CREATE TABLE IF NOT EXISTS `doctor_hospital_affiliations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doctor_id` int(11) NOT NULL,
  `hospital_id` int(11) NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `consultation_fee` decimal(10, 2) DEFAULT 0.00,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`doctor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`hospital_id`) REFERENCES `hospitals`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- 8. Doctor Availability (Schedule - for compatibility)
CREATE TABLE IF NOT EXISTS `doctor_availability` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doctor_id` int(11) NOT NULL,
  `hospital_id` int(11) NOT NULL,
  `day_of_week` enum('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`doctor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`hospital_id`) REFERENCES `hospitals`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- 9. Schedules Table (Active schedules used by HospitalAdminController)
CREATE TABLE IF NOT EXISTS `schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `doctor_id` int(11) NOT NULL,
  `day_of_week` enum('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`doctor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- 10. Patients Details (Profile)
CREATE TABLE IF NOT EXISTS `patients_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `age` int(11) NOT NULL,
  `gender` enum('Male', 'Female', 'Other') NOT NULL,
  `blood_group` varchar(5) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- 11. Appointments Table
CREATE TABLE IF NOT EXISTS `appointments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `hospital_id` int(11) DEFAULT NULL,
  `token_number` int(11) DEFAULT 0,
  `date` date NOT NULL,
  `time_slot` varchar(50) DEFAULT NULL,
  `type` varchar(50) DEFAULT 'consultation',
  `status` enum('pending', 'waiting', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`patient_id`) REFERENCES `patients_details`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`doctor_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`hospital_id`) REFERENCES `hospitals`(`id`) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- 12. Prescriptions & Medical Records
CREATE TABLE IF NOT EXISTS `prescriptions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appointment_id` int(11) NOT NULL,
  `symptoms` text,
  `diagnosis` text,
  `medicines` text,
  `tests_suggested` text,
  `advice` text,
  `next_visit_date` date DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`appointment_id`) REFERENCES `appointments`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- 13. Medical Reports (File Uploads)
CREATE TABLE IF NOT EXISTS `medical_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `title` varchar(150) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`patient_id`) REFERENCES `patients_details`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- 14. Payments Table
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `appointment_id` int(11) NOT NULL,
  `amount` decimal(10, 2) NOT NULL,
  `status` enum('pending', 'paid', 'failed') DEFAULT 'pending',
  `transaction_id` varchar(100) DEFAULT NULL,
  `payment_date` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`appointment_id`) REFERENCES `appointments`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- 15. Contact Messages
CREATE TABLE IF NOT EXISTS `contact_messages` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `subject` varchar(255) DEFAULT 'General Inquiry',
  `message` text NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- 16. Blog Categories
CREATE TABLE IF NOT EXISTS `blog_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL UNIQUE,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- 17. Blogs Table
CREATE TABLE IF NOT EXISTS `blogs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL UNIQUE,
  `content` text NOT NULL,
  `excerpt` text DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `author_id` int(11) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`category_id`) REFERENCES `blog_categories`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`author_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- 18. Diet Plans (Health Goals)
CREATE TABLE IF NOT EXISTS `diet_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hospital_id` int(11) DEFAULT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `patient_name` varchar(100) NOT NULL,
  `patient_phone` varchar(20) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `weight` float DEFAULT NULL,
  `height` float DEFAULT NULL,
  `goal` varchar(50) NOT NULL,
  `conditions` text,
  `status` enum('pending', 'active', 'completed') DEFAULT 'pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`hospital_id`) REFERENCES `hospitals`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`doctor_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- 19. Blood Donors Table
CREATE TABLE IF NOT EXISTS `blood_donors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `blood_group` varchar(5) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `hospital_id` int(11) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`hospital_id`) REFERENCES `hospitals`(`id`) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- 20. Blood Requests Table
CREATE TABLE IF NOT EXISTS `blood_requests` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `patient_name` varchar(100) NOT NULL,
  `blood_group` varchar(5) NOT NULL,
  `urgency` varchar(50) DEFAULT 'Normal',
  `phone` varchar(20) NOT NULL,
  `hospital_id` int(11) DEFAULT NULL,
  `status` enum('pending', 'fulfilled', 'cancelled') DEFAULT 'pending',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`hospital_id`) REFERENCES `hospitals`(`id`) ON DELETE SET NULL
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- 21. Notifications Table
CREATE TABLE IF NOT EXISTS `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `hospital_id` int(11) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `type` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `link` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`hospital_id`) REFERENCES `hospitals`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- 22. Receptionist Hospital Affiliations Table
CREATE TABLE IF NOT EXISTS `receptionist_hospital_affiliations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `receptionist_id` int(11) NOT NULL,
  `hospital_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`receptionist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`hospital_id`) REFERENCES `hospitals`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- 24. Health Assessments Table
CREATE TABLE IF NOT EXISTS `health_assessments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `bmi_score` decimal(5,2) DEFAULT NULL,
  `bmi_category` varchar(50) DEFAULT NULL,
  `calories_target` int(11) DEFAULT NULL,
  `water_target` decimal(5,2) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- 23. Hospital Settings Table
CREATE TABLE IF NOT EXISTS `hospital_settings` (
  `hospital_id` int(11) NOT NULL,
  `email_notifications` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`hospital_id`),
  FOREIGN KEY (`hospital_id`) REFERENCES `hospitals`(`id`) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;

-- --------------------------------------------------------
-- SEED DATA (For Testing)
-- --------------------------------------------------------

-- Default Super Admin (password: admin123)
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`)
VALUES (
    'Super Admin',
    'admin@admin.com',
    '0300-0000000',
    '$2y$10$oDdYwEfg9/hcgM0Dl5qpNuRiwLUhQ7tmwMu0e7CNItR1RlJ05Pb3u',
    'admin'
  ) ON DUPLICATE KEY UPDATE name = 'Super Admin', password = VALUES(password), phone = VALUES(phone);

-- Default Patient (password: admin)
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`)
VALUES (
    'Patient User',
    'patient@patient.com',
    '0300-0000001',
    '$2y$10$Bi2J8K.ATRAhOcN8cdxez.vFvh2VgKJfjcAv5.ZZUxPu8s7GZtkM2',
    'patient'
  ) ON DUPLICATE KEY UPDATE name = 'Patient User', password = VALUES(password), phone = VALUES(phone);

-- Default Doctor (password: admin123)
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`)
VALUES (
    'Doctor User',
    'doctor@doctor.com',
    '0300-0000002',
    '$2y$10$oDdYwEfg9/hcgM0Dl5qpNuRiwLUhQ7tmwMu0e7CNItR1RlJ05Pb3u',
    'doctor'
  ) ON DUPLICATE KEY UPDATE name = 'Doctor User', password = VALUES(password), phone = VALUES(phone);

-- Default Doctor (Alternative Spelling) (password: admin123)
INSERT INTO `users` (`name`, `email`, `phone`, `password`, `role`)
VALUES (
    'Docotor User',
    'docotor@doctor.com',
    '0300-0000003',
    '$2y$10$oDdYwEfg9/hcgM0Dl5qpNuRiwLUhQ7tmwMu0e7CNItR1RlJ05Pb3u',
    'doctor'
  ) ON DUPLICATE KEY UPDATE name = 'Docotor User', password = VALUES(password), phone = VALUES(phone);

-- Default Specializations
INSERT INTO `specializations` (`name`, `description`)
VALUES 
  ('Cardiology', 'Heart and cardiovascular diseases'),
  ('Neurology', 'Brain and nervous system treatment'),
  ('Pediatrics', 'Medical care of infants, children, and adolescents'),
  ('General Medicine', 'Adult diseases and primary healthcare'),
  ('Orthopedics', 'Musculoskeletal system surgeries & care'),
  ('Gynecology', 'Female reproductive health and pregnancy'),
  ('ENT', 'Ear, Nose, Throat medical disorders'),
  ('Dermatology', 'Skin, hair, nails treatment'),
  ('Psychiatry', 'Mental health and cognitive therapies'),
  ('Surgery', 'General and laparoscopic surgeries')
  ON DUPLICATE KEY UPDATE description = VALUES(description);