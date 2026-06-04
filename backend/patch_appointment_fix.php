<?php
/**
 * MedNova - Appointment Module Database Patch
 * ============================================
 * This patch fixes the appointments table to allow NULL doctor_id
 * (for walk-in / hospital-level bookings without a specific doctor).
 * 
 * Run this once after deploying the appointment module fix.
 * Safe to run multiple times (idempotent).
 */

require_once __DIR__ . '/config/database.php';

$db = (new Database())->getConnection();

echo "<pre style='font-family:monospace; padding:20px;'>";
echo "=== MedNova Appointment Module DB Patch ===\n\n";

$patches = [];

// 1. Make doctor_id nullable in appointments table
try {
    $db->exec("ALTER TABLE appointments MODIFY COLUMN doctor_id INT(11) DEFAULT NULL");
    $patches[] = "[OK] appointments.doctor_id: Changed to nullable (INT DEFAULT NULL)";
} catch (Exception $e) {
    $msg = $e->getMessage();
    if (strpos($msg, "doesn't exist") !== false) {
        $patches[] = "[SKIP] appointments table does not exist yet — run schema.sql first";
    } elseif (strpos($msg, 'already') !== false || strpos($msg, 'same') !== false) {
        $patches[] = "[OK] appointments.doctor_id: Already nullable — no change needed";
    } else {
        $patches[] = "[WARN] appointments.doctor_id: " . $msg;
    }
}

// 2. Verify appointments table has time_slot column
try {
    $stmt = $db->query("SHOW COLUMNS FROM appointments LIKE 'time_slot'");
    if ($stmt->rowCount() === 0) {
        $db->exec("ALTER TABLE appointments ADD COLUMN time_slot VARCHAR(50) DEFAULT NULL AFTER date");
        $patches[] = "[OK] appointments.time_slot: Column added";
    } else {
        $patches[] = "[OK] appointments.time_slot: Already exists — no change needed";
    }
} catch (Exception $e) {
    $patches[] = "[WARN] appointments.time_slot: " . $e->getMessage();
}

// 3. Ensure hospitals table exists and appointments.hospital_id FK is correct
try {
    $stmt = $db->query("SELECT COUNT(*) FROM hospitals");
    $count = $stmt->fetchColumn();
    $patches[] = "[OK] hospitals table: Found with $count records";
} catch (Exception $e) {
    $patches[] = "[WARN] hospitals table: " . $e->getMessage();
}

// 4. Ensure notifications table has is_read column
try {
    $stmt = $db->query("SHOW COLUMNS FROM notifications LIKE 'is_read'");
    if ($stmt->rowCount() === 0) {
        $db->exec("ALTER TABLE notifications ADD COLUMN is_read TINYINT(1) DEFAULT 0 AFTER link");
        $patches[] = "[OK] notifications.is_read: Column added";
    } else {
        $patches[] = "[OK] notifications.is_read: Already exists — no change needed";
    }
} catch (Exception $e) {
    $patches[] = "[WARN] notifications.is_read: " . $e->getMessage();
}

// 5. Verify patients_details has phone column
try {
    $stmt = $db->query("SHOW COLUMNS FROM patients_details LIKE 'phone'");
    if ($stmt->rowCount() === 0) {
        $db->exec("ALTER TABLE patients_details ADD COLUMN phone VARCHAR(20) DEFAULT NULL AFTER name");
        $patches[] = "[OK] patients_details.phone: Column added";
    } else {
        $patches[] = "[OK] patients_details.phone: Already exists — no change needed";
    }
} catch (Exception $e) {
    $patches[] = "[WARN] patients_details.phone: " . $e->getMessage();
}

// 6. Ensure hospital_settings table exists
try {
    $db->exec("CREATE TABLE IF NOT EXISTS hospital_settings (hospital_id INT PRIMARY KEY, email_notifications TINYINT(1) DEFAULT 1, FOREIGN KEY (hospital_id) REFERENCES hospitals(id) ON DELETE CASCADE)");
    $patches[] = "[OK] hospital_settings table: Ensured exists";
} catch (Exception $e) {
    $patches[] = "[WARN] hospital_settings: " . $e->getMessage();
}

// Output results
foreach ($patches as $p) {
    $prefix = substr($p, 0, 4);
    $color = $prefix === '[OK]' ? 'green' : ($prefix === '[WAR' ? 'orange' : 'red');
    echo "<span style='color:$color'>$p</span>\n";
}

echo "\n=== Patch Complete ===\n";
echo "You can safely delete this file after running.\n";
echo "</pre>";
