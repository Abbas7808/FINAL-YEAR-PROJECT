<?php
/**
 * MedNova System Module Checker
 * Run via: http://localhost/FinalFYP/backend/module_check.php
 */

// --- DB Config ---
// Match the actual Database.php config
$dbname = 'u230645419_fyp_mediqu';
$pdo = null;
foreach (['3306','3308','3307'] as $port) {
    try {
        $pdo = new PDO("mysql:host=127.0.0.1;port=$port;dbname=$dbname;charset=utf8mb4", 'root', '', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT=>2]);
        break;
    } catch (PDOException $e) {
        if ($e->getCode() == 1049) {
            // DB doesn't exist on this port — create it
            try {
                $tmp = new PDO("mysql:host=127.0.0.1;port=$port", 'root', '');
                $tmp->exec("CREATE DATABASE IF NOT EXISTS `$dbname`");
                $pdo = new PDO("mysql:host=127.0.0.1;port=$port;dbname=$dbname;charset=utf8mb4", 'root', '', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION]);
                // Import schema
                if (file_exists(__DIR__.'/schema.sql')) {
                    $pdo->exec(file_get_contents(__DIR__.'/schema.sql'));
                }
                break;
            } catch (Exception $e2) { continue; }
        }
    }
}

function ok($msg)  { echo "<tr><td>✅</td><td>$msg</td></tr>"; }
function fail($msg){ echo "<tr><td>❌</td><td>$msg</td></tr>"; }
function info($msg){ echo "<tr><td>ℹ️</td><td>$msg</td></tr>"; }
function section($t){ echo "</table><h3 style='margin:20px 0 6px;color:#2563eb'>$t</h3><table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse;width:100%;font-size:14px'>"; }

echo "<!DOCTYPE html><html><head><title>Module Check</title></head><body style='font-family:Poppins,sans-serif;padding:24px;background:#f8fafc'>
<h2 style='color:#0f172a'>MedNova – System Module Check</h2>";

if (!$pdo) {
    echo "<p style='color:red'>❌ Cannot connect to DB '<b>$dbname</b>'. Please ensure XAMPP MySQL is running.</p></body></html>";
    exit;
}

echo "Connected to DB: <b>$dbname</b> ✅<br><br>";
echo "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse;width:100%;font-size:14px'>";

// ============================
// 1. DATABASE TABLES
// ============================
section("1. Database Tables");
$required_tables = [
    'users','hospitals','departments','specializations','doctors_details',
    'doctor_hospital_affiliations','schedules','patients_details',
    'appointments','prescriptions','medical_reports','payments',
    'contact_messages','blog_categories','blogs','diet_plans',
    'blood_donors','blood_requests','notifications',
    'receptionist_hospital_affiliations','hospital_settings'
];
$existing = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
foreach ($required_tables as $t) {
    if (in_array($t, $existing)) ok("Table exists: <b>$t</b>");
    else fail("Missing table: <b>$t</b>");
}

// ============================
// 2. HOSPITALS TABLE COLUMNS
// ============================
section("2. Hospitals Table Columns");
$cols = $pdo->query("SHOW COLUMNS FROM hospitals")->fetchAll(PDO::FETCH_COLUMN);
foreach (['id','name','slug','address','description','image','type','region_id','user_id','subscription_status','plan_type','plan_expires_at'] as $c) {
    if (in_array($c, $cols)) ok("Column hospitals.$c exists");
    else fail("Missing column: hospitals.$c");
}

// ============================
// 3. USERS / CREDENTIALS
// ============================
section("3. Seeded User Accounts");
$accounts = [
    ['admin@admin.com',   'admin123', 'admin'],
    ['patient@patient.com','admin',   'patient'],
    ['doctor@doctor.com', 'admin123', 'doctor'],
    ['docotor@doctor.com','admin123', 'doctor'],
];
foreach ($accounts as [$email, $pwd, $role]) {
    $stmt = $pdo->prepare("SELECT id, name, password, role, is_active FROM users WHERE email=:e AND role=:r");
    $stmt->execute([':e'=>$email, ':r'=>$role]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$u) {
        fail("User not found: <b>$email</b> (role: $role)");
    } elseif (!$u['is_active']) {
        fail("User inactive: <b>$email</b> ($role)");
    } elseif (!password_verify($pwd, $u['password'])) {
        fail("Password mismatch for: <b>$email</b> (expected: $pwd)");
    } else {
        ok("Login OK: <b>$email</b> | role=$role | id={$u['id']}");
    }
}

// ============================
// 4. HOSPITALS
// ============================
section("4. Hospitals");
$hospitals = $pdo->query("SELECT h.id, h.name, h.type, h.user_id, u.email as admin_email, h.subscription_status FROM hospitals h LEFT JOIN users u ON h.user_id=u.id ORDER BY h.id")->fetchAll(PDO::FETCH_ASSOC);
if (empty($hospitals)) {
    fail("No hospitals found in the database");
} else {
    foreach ($hospitals as $h) {
        $adminEmail = $h['admin_email'] ?? 'No admin linked';
        $status = $h['subscription_status'];
        if ($status === 'active') {
            ok("Hospital #{$h['id']}: <b>{$h['name']}</b> ({$h['type']}) | Admin: $adminEmail | Status: $status");
        } else {
            fail("Hospital #{$h['id']}: <b>{$h['name']}</b> | Status: $status (inactive)");
        }
    }
}

// ============================
// 5. DOCTOR AFFILIATIONS
// ============================
section("5. Doctor → Hospital Affiliations");
$stmt = $pdo->query("
    SELECT u.name as doctor, u.email, h.name as hospital, dd.specialization
    FROM doctor_hospital_affiliations dha
    JOIN users u ON dha.doctor_id = u.id
    JOIN hospitals h ON dha.hospital_id = h.id
    LEFT JOIN doctors_details dd ON u.id = dd.user_id
    ORDER BY h.name
");
$affiliations = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($affiliations)) {
    fail("No doctor-hospital affiliations found");
} else {
    foreach ($affiliations as $a) {
        ok("Doctor: <b>{$a['doctor']}</b> ({$a['email']}) → Hospital: <b>{$a['hospital']}</b> | Spec: {$a['specialization']}");
    }
}

// ============================
// 6. APPOINTMENTS
// ============================
section("6. Appointments");
$total = $pdo->query("SELECT COUNT(*) FROM appointments")->fetchColumn();
info("Total appointments in DB: <b>$total</b>");
$pending = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status='pending'")->fetchColumn();
info("Pending: $pending");
$confirmed = $pdo->query("SELECT COUNT(*) FROM appointments WHERE status='confirmed'")->fetchColumn();
info("Confirmed: $confirmed");

// Check appointment table columns
$apptCols = $pdo->query("SHOW COLUMNS FROM appointments")->fetchAll(PDO::FETCH_COLUMN);
foreach (['id','patient_id','doctor_id','hospital_id','token_number','date','time_slot','status','type'] as $c) {
    if (in_array($c, $apptCols)) ok("Column appointments.$c exists");
    else fail("Missing column: appointments.$c");
}

// Recent appointment
$recent = $pdo->query("SELECT a.*, pd.name as patient_name, u.name as doctor_name FROM appointments a LEFT JOIN patients_details pd ON a.patient_id=pd.id LEFT JOIN users u ON a.doctor_id=u.id ORDER BY a.id DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
if ($recent) {
    foreach ($recent as $r) {
        info("Recent appt: Patient=<b>{$r['patient_name']}</b> | Doctor=<b>{$r['doctor_name']}</b> | Date={$r['date']} | Status={$r['status']} | Token=#{$r['token_number']}");
    }
}

// ============================
// 7. API ENDPOINTS
// ============================
section("7. API Endpoint Test (via HTTP)");
$baseApi = "http://localhost/FinalFYP/backend/api.php?route=api/";
$endpoints = [
    'hospitals' => $baseApi . 'hospitals',
    'departments' => $baseApi . 'departments',
    'doctors' => $baseApi . 'doctors',
];
foreach ($endpoints as $name => $url) {
    $ctx = stream_context_create(['http'=>['timeout'=>5, 'ignore_errors'=>true]]);
    $resp = @file_get_contents($url, false, $ctx);
    if ($resp === false) {
        fail("API endpoint unreachable: <b>$name</b> ($url)");
    } else {
        $json = json_decode($resp, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $count = is_array($json) ? count($json) : '?';
            ok("API <b>$name</b>: OK | Items returned: $count");
        } else {
            fail("API <b>$name</b>: Invalid JSON response — " . substr($resp, 0, 100));
        }
    }
}

// ============================
// 8. APPOINTMENT BOOKING SIMULATION
// ============================
section("8. Appointment Booking Simulation");
// Grab first active hospital
$h = $pdo->query("SELECT id, name FROM hospitals WHERE subscription_status='active' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
if ($h) {
    $hid = $h['id'];
    $hname = $h['name'];
    // Get a doctor for that hospital
    $doc = $pdo->prepare("SELECT u.id, u.name FROM users u JOIN doctor_hospital_affiliations dha ON u.id=dha.doctor_id WHERE dha.hospital_id=:hid AND u.role='doctor' LIMIT 1");
    $doc->execute([':hid'=>$hid]);
    $doctor = $doc->fetch(PDO::FETCH_ASSOC);
    
    // Simulate appointment insert
    try {
        $pdo->beginTransaction();
        $testPhone = '0300-TEST-'.time();
        $testEmail = 'test_'.time().'@mednova.pk';
        $pdo->prepare("INSERT INTO users (name, phone, email, password, role) VALUES ('Test Patient', :p, :e, :pw, 'patient')")
            ->execute([':p'=>$testPhone, ':e'=>$testEmail, ':pw'=>password_hash('test', PASSWORD_DEFAULT)]);
        $uid = $pdo->lastInsertId();
        $pdo->prepare("INSERT INTO patients_details (user_id, name, phone, age, gender) VALUES (:uid, 'Test Patient', :p, 25, 'Other')")
            ->execute([':uid'=>$uid, ':p'=>$testPhone]);
        $pid = $pdo->lastInsertId();
        $did = $doctor ? $doctor['id'] : 0;
        $pdo->prepare("INSERT INTO appointments (patient_id, doctor_id, hospital_id, token_number, date, time_slot, status, type) VALUES (:pid, :did, :hid, 999, CURDATE(), '10:00', 'pending', 'consultation')")
            ->execute([':pid'=>$pid, ':did'=>$did, ':hid'=>$hid]);
        $aid = $pdo->lastInsertId();
        $pdo->rollBack(); // Rollback — this was just a test
        ok("Booking simulation: SUCCESS (rolled back) | Hospital: <b>$hname</b> | Doctor: <b>" . ($doctor['name'] ?? 'N/A') . "</b>");
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        fail("Booking simulation FAILED: " . $e->getMessage());
    }
} else {
    fail("No active hospital found — cannot simulate booking");
}

// ============================
// 9. NOTIFICATIONS TABLE
// ============================
section("9. Notifications");
$notifCount = $pdo->query("SELECT COUNT(*) FROM notifications")->fetchColumn();
info("Total notifications: <b>$notifCount</b>");
$unread = $pdo->query("SELECT COUNT(*) FROM notifications WHERE is_read=0")->fetchColumn();
info("Unread notifications: <b>$unread</b>");

// ============================
// 10. ROLE-BASED ROUTING CHECK
// ============================
section("10. Role-Based Access Summary");
$roles = [
    'admin'         => ['Super Admin',    'admin@admin.com',    '?route=admin/dashboard'],
    'hospital_admin'=> ['Hospital Admin', '(any hospital admin)', '?route=hospital_admin/dashboard'],
    'doctor'        => ['Doctor',         'doctor@doctor.com',  '?route=doctor/dashboard'],
    'receptionist'  => ['Receptionist',   '(any receptionist)', '?route=receptionist/dashboard'],
    'patient'       => ['Patient',        'patient@patient.com','?route=patient/dashboard'],
];
foreach ($roles as $role => [$label, $email, $route]) {
    ok("Role: <b>$role</b> ($label) → Login as $email → Redirect to $route");
}

// ============================
// 11. BLOG MODULE
// ============================
section("11. Blog Module");
$blogCount = $pdo->query("SELECT COUNT(*) FROM blogs")->fetchColumn();
$catCount = $pdo->query("SELECT COUNT(*) FROM blog_categories")->fetchColumn();
info("Blog categories: <b>$catCount</b> | Blog posts: <b>$blogCount</b>");
if ($blogCount > 0) ok("Blog content available");
else fail("No blog posts found — seed blogs first");

// ============================
// 12. BLOOD BANK
// ============================
section("12. Blood Bank");
$donors = $pdo->query("SELECT COUNT(*) FROM blood_donors")->fetchColumn();
$reqs = $pdo->query("SELECT COUNT(*) FROM blood_requests")->fetchColumn();
info("Blood donors: <b>$donors</b> | Blood requests: <b>$reqs</b>");

// ============================
// 13. DIET PLANS
// ============================
section("13. Diet Plans");
$dp = $pdo->query("SELECT COUNT(*) FROM diet_plans")->fetchColumn();
info("Diet plan requests: <b>$dp</b>");

// ============================
// SUMMARY
// ============================
section("DONE");
ok("Module check complete. Review any ❌ items above.");
echo "</table>";

echo "<br><p style='color:#64748b;font-size:13px'>Generated at: " . date('Y-m-d H:i:s') . "</p></body></html>";
