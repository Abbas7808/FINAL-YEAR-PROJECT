INSERT IGNORE INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`) VALUES
(101, 'Dr. Fatima Zahra', 'fatima@example.com', '+923001234567', 'doc123', 'doctor'),
(102, 'Dr. Muhammad Bilal', 'bilal@example.com', '+923001234568', 'doc123', 'doctor'),
(103, 'Dr. Aisha Noor', 'aisha@example.com', '+923001234569', 'doc123', 'doctor');

INSERT IGNORE INTO `doctors_details` (`user_id`, `specialization`, `qualification`, `experience_years`, `bio`, `contact_number`, `profile_image`) VALUES
(101, 'Cardiology', 'MBBS, FCPS', 10, 'Expert cardiologist with 10 years of experience.', '+923001234567', 'default_doctor.png'),
(102, 'Neurology', 'MBBS, MD', 8, 'Specialist in brain and nervous system disorders.', '+923001234568', 'default_doctor.png'),
(103, 'Pediatrics', 'MBBS, DCH', 5, 'Dedicated pediatrician for infant care.', '+923001234569', 'default_doctor.png');

INSERT IGNORE INTO `doctor_hospital_affiliations` (`doctor_id`, `hospital_id`, `department_id`, `consultation_fee`) VALUES
(101, 2, 1, 1500.00),
(102, 2, 2, 2000.00),
(103, 4, 3, 1000.00);
