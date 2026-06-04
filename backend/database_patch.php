<?php
require_once 'config/Database.php';

try {
    $database = new Database();
    $db = $database->getConnection();
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "<h2>MedNova Database Schema Patcher</h2>";
    echo "Running checks and applying updates...<br/><br/>";

    // 1. Modify users role ENUM and add is_active
    echo "Updating 'users' table structure... ";
    $db->exec("ALTER TABLE `users` MODIFY COLUMN `role` ENUM('admin', 'doctor', 'receptionist', 'patient', 'hospital_admin') NOT NULL");
    
    $checkActive = $db->query("SHOW COLUMNS FROM `users` LIKE 'is_active'");
    if ($checkActive->rowCount() == 0) {
        $db->exec("ALTER TABLE `users` ADD COLUMN `is_active` TINYINT(1) DEFAULT 1 AFTER `role`");
        echo "Added 'is_active' column. ";
    }
    echo "Done.<br/>";

    // 2. Create regions table if not exists
    echo "Checking 'regions' table... ";
    $db->exec("CREATE TABLE IF NOT EXISTS `regions` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(100) NOT NULL UNIQUE,
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Done.<br/>";

    // 3. Update hospitals table columns
    echo "Updating 'hospitals' table structure... ";
    $hospitalsCols = [
        'slug' => "VARCHAR(150) NOT NULL UNIQUE AFTER `name`",
        'region_id' => "INT(11) DEFAULT NULL AFTER `type`",
        'user_id' => "INT(11) DEFAULT NULL AFTER `region_id`",
        'subscription_status' => "VARCHAR(20) DEFAULT 'active' AFTER `contact_info`",
        'plan_type' => "VARCHAR(20) DEFAULT 'monthly' AFTER `subscription_status`"
    ];

    foreach ($hospitalsCols as $col => $definition) {
        $checkCol = $db->query("SHOW COLUMNS FROM `hospitals` LIKE '$col'");
        if ($checkCol->rowCount() == 0) {
            // Need to handle UNIQUE constraints or temporary columns. To be safe, add column
            if ($col === 'slug') {
                // If it is slug, it must not be UNIQUE right away if there is existing data without slugs.
                // We'll add it as nullable, populate slugs, and then make it UNIQUE.
                $db->exec("ALTER TABLE `hospitals` ADD COLUMN `slug` VARCHAR(150) DEFAULT NULL AFTER `name`");
                
                // Populate slugs for existing hospitals
                $stmt = $db->query("SELECT id, name FROM hospitals");
                $hospitals = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $upd = $db->prepare("UPDATE hospitals SET slug = :slug WHERE id = :id");
                foreach ($hospitals as $h) {
                    $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $h['name']));
                    $upd->execute([':slug' => $slug, ':id' => $h['id']]);
                }
                
                // Now modify it to be NOT NULL and UNIQUE
                $db->exec("ALTER TABLE `hospitals` MODIFY COLUMN `slug` VARCHAR(150) NOT NULL UNIQUE");
            } else {
                $db->exec("ALTER TABLE `hospitals` ADD COLUMN `$col` $definition");
            }
            echo "Added '$col' column. ";
        }
    }
    echo "Done.<br/>";

    // Add Foreign Key Constraints on hospitals if missing
    try {
        $db->exec("ALTER TABLE `hospitals` ADD CONSTRAINT `fk_hospital_region` FOREIGN KEY (`region_id`) REFERENCES `regions`(`id`) ON DELETE SET NULL");
        $db->exec("ALTER TABLE `hospitals` ADD CONSTRAINT `fk_hospital_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL");
        echo "Added hospital foreign keys.<br/>";
    } catch (Exception $e) {
        // Constraints might already exist
    }

    // 3b. Update departments table structure (add hospital_id, drop unique name constraint)
    echo "Updating 'departments' table structure... ";
    try {
        // Drop unique key 'name' if exists to allow multiple hospitals to have same department name
        $db->exec("ALTER TABLE `departments` DROP INDEX `name`");
    } catch (Exception $e) { /* ignore if index doesn't exist */ }

    $checkDeptHosp = $db->query("SHOW COLUMNS FROM `departments` LIKE 'hospital_id'");
    if ($checkDeptHosp->rowCount() == 0) {
        try {
            $db->exec("ALTER TABLE `departments` ADD COLUMN `hospital_id` INT(11) DEFAULT NULL AFTER `name`");
            $db->exec("ALTER TABLE `departments` ADD CONSTRAINT `fk_department_hospital` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals`(`id`) ON DELETE CASCADE");
            echo "Added 'hospital_id' column and constraint. ";
        } catch (Exception $e) {
            echo "Error patching departments: " . $e->getMessage() . " ";
        }
    }
    echo "Done.<br/>";

    // 4. Create specializations table if not exists
    echo "Checking 'specializations' table... ";
    $db->exec("CREATE TABLE IF NOT EXISTS `specializations` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `name` varchar(100) NOT NULL UNIQUE,
      `description` text,
      `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
      PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Done.<br/>";

    // 5. Update doctors_details table columns
    echo "Updating 'doctors_details' table structure... ";
    $doctorsCols = [
        'specialization_id' => "INT(11) DEFAULT NULL AFTER `user_id`",
        'department_id' => "INT(11) DEFAULT NULL AFTER `specialization_id`",
        'phone' => "VARCHAR(20) DEFAULT NULL AFTER `specialization`",
        'address' => "TEXT DEFAULT NULL AFTER `phone`",
        'biography' => "TEXT DEFAULT NULL AFTER `address`",
        'joining_date' => "DATE DEFAULT NULL AFTER `biography`",
        'rating' => "DECIMAL(2,1) DEFAULT 5.0 AFTER `joining_date`",
        'reviews_count' => "INT(11) DEFAULT 0 AFTER `rating`"
    ];

    foreach ($doctorsCols as $col => $definition) {
        $checkCol = $db->query("SHOW COLUMNS FROM `doctors_details` LIKE '$col'");
        if ($checkCol->rowCount() == 0) {
            $db->exec("ALTER TABLE `doctors_details` ADD COLUMN `$col` $definition");
            echo "Added '$col' column. ";
        }
    }
    echo "Done.<br/>";

    // Add Foreign Key Constraints on doctors_details if missing
    try {
        $db->exec("ALTER TABLE `doctors_details` ADD CONSTRAINT `fk_doctor_specialization` FOREIGN KEY (`specialization_id`) REFERENCES `specializations`(`id`) ON DELETE SET NULL");
        $db->exec("ALTER TABLE `doctors_details` ADD CONSTRAINT `fk_doctor_department` FOREIGN KEY (`department_id`) REFERENCES `departments`(`id`) ON DELETE SET NULL");
        echo "Added doctor foreign keys.<br/>";
    } catch (Exception $e) {
        // Constraints might already exist
    }

    // 6. Create schedules table if not exists (replaces doctor_availability)
    echo "Checking 'schedules' table... ";
    $db->exec("CREATE TABLE IF NOT EXISTS `schedules` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `doctor_id` int(11) NOT NULL,
      `day_of_week` enum('Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday') NOT NULL,
      `start_time` time NOT NULL,
      `end_time` time NOT NULL,
      PRIMARY KEY (`id`),
      FOREIGN KEY (`doctor_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Done.<br/>";

    // 7. Create receptionist_hospital_affiliations table if not exists
    echo "Checking 'receptionist_hospital_affiliations' table... ";
    $db->exec("CREATE TABLE IF NOT EXISTS `receptionist_hospital_affiliations` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `receptionist_id` int(11) NOT NULL,
      `hospital_id` int(11) NOT NULL,
      PRIMARY KEY (`id`),
      FOREIGN KEY (`receptionist_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
      FOREIGN KEY (`hospital_id`) REFERENCES `hospitals`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Done.<br/>";

    // 8. Create hospital_settings table if not exists
    echo "Checking 'hospital_settings' table... ";
    $db->exec("CREATE TABLE IF NOT EXISTS `hospital_settings` (
      `hospital_id` int(11) NOT NULL,
      `email_notifications` tinyint(1) DEFAULT 1,
      PRIMARY KEY (`hospital_id`),
      FOREIGN KEY (`hospital_id`) REFERENCES `hospitals`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "Done.<br/>";

    // 8b. Check and add type column to appointments table
    echo "Checking 'appointments' table structure... ";
    $checkAppType = $db->query("SHOW COLUMNS FROM `appointments` LIKE 'type'");
    if ($checkAppType->rowCount() == 0) {
        $db->exec("ALTER TABLE `appointments` ADD COLUMN `type` VARCHAR(50) DEFAULT 'consultation' AFTER `time_slot`");
        echo "Added 'type' column. ";
    }
    echo "Done.<br/>";

    // 9. Fix users table constraints & seed admin correctly
    echo "Repairing admin user settings... ";
    // Check if there is an admin with email admin@admin.com
    $checkAdmin = $db->query("SELECT id FROM users WHERE email = 'admin@admin.com'");
    $hashed = password_hash('admin123', PASSWORD_DEFAULT);
    if ($checkAdmin->rowCount() == 0) {
        $db->exec("INSERT INTO users (name, email, phone, password, role) VALUES ('Super Admin', 'admin@admin.com', '+1234567890', '$hashed', 'admin')");
    } else {
        $db->exec("UPDATE users SET password = '$hashed' WHERE email = 'admin@admin.com'");
    }
    
    // Clean up empty email admin rows if they exist
    $db->exec("DELETE FROM users WHERE email = '' AND role = 'admin'");
    echo "Done.<br/>";

    // 10. Update any empty roles for hospital admin users to 'hospital_admin'
    echo "Aligning hospital administrator roles... ";
    $db->exec("UPDATE users SET role = 'hospital_admin' WHERE email LIKE '%@mednova.pk' AND (role = '' OR role IS NULL)");
    echo "Done.<br/>";

    echo "<h3>🎉 Database successfully patched and aligned!</h3>";
    echo "<p><a href='index.php'>Go to Portal Login</a></p>";

} catch (Exception $e) {
    echo "<h3>❌ Error executing patches:</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
