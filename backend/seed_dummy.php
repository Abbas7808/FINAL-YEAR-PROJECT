<?php
require_once 'config/Database.php';

$database = new Database();
$db = $database->getConnection();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "<pre>";
echo "=== Starting MedNova Database Seeding ===\n\n";

// ================================================
// 1. REGIONS
// ================================================
echo "Seeding regions...\n";
$regions = ['Kohat', 'Peshawar', 'Hangu', 'Karak', 'Orakzai'];
$regionIds = [];

foreach ($regions as $region) {
    $stmt = $db->prepare("INSERT IGNORE INTO regions (name) VALUES (:name)");
    $stmt->execute([':name' => $region]);
    $stmt2 = $db->prepare("SELECT id FROM regions WHERE name = :name");
    $stmt2->execute([':name' => $region]);
    $regionIds[$region] = $stmt2->fetchColumn();
    echo "  Region: $region (id={$regionIds[$region]})\n";
}

// ================================================
// 2. HOSPITALS & CLINICS
// ================================================
echo "\nSeeding hospitals & clinics...\n";
$facilities = [
    ['name' => 'DHQ Teaching Hospital Kohat',    'type' => 'Hospital', 'region' => 'Kohat',    'address' => 'Main GT Road, Kohat',           'contact' => '0922-510001'],
    ['name' => 'Al-Shifa Medical Centre',         'type' => 'Hospital', 'region' => 'Kohat',    'address' => 'Bannu Road, Kohat',             'contact' => '0922-511002'],
    ['name' => 'Hayatabad Medical Complex',       'type' => 'Hospital', 'region' => 'Peshawar', 'address' => 'Phase-7, Hayatabad, Peshawar',  'contact' => '091-9217300'],
    ['name' => 'Lady Reading Hospital',           'type' => 'Hospital', 'region' => 'Peshawar', 'address' => 'Peshawar Cantonment',           'contact' => '091-9210420'],
    ['name' => 'Hangu District Hospital',         'type' => 'Hospital', 'region' => 'Hangu',    'address' => 'Civil Lines, Hangu',            'contact' => '0923-630201'],
    ['name' => 'Rahmat Clinic Kohat',             'type' => 'Clinic',   'region' => 'Kohat',    'address' => 'Saddar Bazaar, Kohat',          'contact' => '0922-512345'],
    ['name' => 'Bismillah Medical Clinic',        'type' => 'Clinic',   'region' => 'Kohat',    'address' => 'University Road, Kohat',        'contact' => '0300-9876543'],
    ['name' => 'Noor-e-Shifa Clinic',             'type' => 'Clinic',   'region' => 'Peshawar', 'address' => 'Qissa Khwani Bazaar, Peshawar', 'contact' => '0333-1122334'],
    ['name' => 'Al-Madina Clinic',                'type' => 'Clinic',   'region' => 'Hangu',    'address' => 'Main Bazaar, Hangu',            'contact' => '0923-631001'],
    ['name' => 'Karak General Hospital',          'type' => 'Hospital', 'region' => 'Karak',    'address' => 'Karak City, KPK',              'contact' => '0927-810001'],
];

$facilityIds = [];
foreach ($facilities as $fac) {
    // FIX: lowercase FIRST then do preg_replace to avoid stripping uppercase characters
    $slug = preg_replace('/[^a-z0-9]+/', '-', strtolower($fac['name']));
    $slug = trim($slug, '-');
    
    $rid = $regionIds[$fac['region']] ?? null;

    $hashed = password_hash('admin@123', PASSWORD_DEFAULT);
    $email = ($slug === 'dhq-teaching-hospital-kohat') ? 'user@user.com' : ($slug . '@mednova.pk');
    
    // Check if user exists
    $uStmt = $db->prepare("INSERT INTO users (name, phone, email, password, role) VALUES (:name, :phone, :email, :pass, 'hospital_admin') ON DUPLICATE KEY UPDATE name=:name, role='hospital_admin', password=:pass");
    $uStmt->execute([':name' => $fac['name'], ':phone' => $fac['contact'], ':email' => $email, ':pass' => $hashed]);
    
    $uid = $db->lastInsertId() ?: (function() use ($db, $email) {
        $s = $db->prepare("SELECT id FROM users WHERE email=:e AND role='hospital_admin'"); 
        $s->execute([':e'=>$email]); 
        return $s->fetchColumn();
    })();

    $stmt = $db->prepare("INSERT INTO hospitals (name, slug, type, region_id, address, contact_info, user_id, subscription_status, plan_type)
        VALUES (:name, :slug, :type, :rid, :addr, :contact, :uid, 'active', 'monthly')
        ON DUPLICATE KEY UPDATE name=:name, slug=:slug, region_id=:rid, user_id=:uid");
    $stmt->execute([':name'=>$fac['name'],':slug'=>$slug,':type'=>$fac['type'],':rid'=>$rid,':addr'=>$fac['address'],':contact'=>$fac['contact'],':uid'=>$uid]);

    $s2 = $db->prepare("SELECT id FROM hospitals WHERE slug=:slug"); 
    $s2->execute([':slug'=>$slug]);
    $fid = $s2->fetchColumn();
    $facilityIds[$fac['name']] = $fid;
    echo "  Facility: {$fac['name']} ({$fac['type']}, id=$fid, admin_email=$email)\n";
}

// ================================================
// 3. DEPARTMENTS
// ================================================
echo "\nSeeding departments for hospitals...\n";
$depts = ['Cardiology','Neurology','Pediatrics','General Medicine','Orthopedics','Gynecology','ENT','Dermatology','Psychiatry','Surgery'];
$deptIds = []; // Mapped by hospital_id -> dept_name -> dept_id

foreach ($facilityIds as $hospName => $hospId) {
    $deptIds[$hospId] = [];
    foreach ($depts as $d) {
        $db->prepare("INSERT INTO departments (name, hospital_id, description) VALUES (:n, :hid, :desc) ON DUPLICATE KEY UPDATE name=name")
           ->execute([':n' => $d, ':hid' => $hospId, ':desc' => "$d department at $hospName"]);
           
        $s = $db->prepare("SELECT id FROM departments WHERE name=:n AND hospital_id=:hid"); 
        $s->execute([':n'=>$d, ':hid'=>$hospId]);
        $deptIds[$hospId][$d] = $s->fetchColumn();
    }
}
echo "  Departments populated for all hospitals.\n";

// ================================================
// 4. SPECIALIZATIONS
// ================================================
echo "\nSeeding doctor specializations...\n";
$specializations = [
    'Cardiology' => 'Heart and cardiovascular system details',
    'Neurology' => 'Brain and nervous system treatment',
    'Pediatrics' => 'Medical care of infants and children',
    'General Medicine' => 'Adult diseases and primary medicine',
    'Orthopedics' => 'Bones and skeletal system care',
    'Gynecology' => 'Women reproductive health',
    'ENT' => 'Ear, nose and throat care',
    'Dermatology' => 'Skin, hair and nail treatments',
    'Psychiatry' => 'Mental health and behavior disorders',
    'Surgery' => 'General and laparoscopic procedures'
];
$specIds = [];
foreach ($specializations as $sName => $sDesc) {
    $db->prepare("INSERT INTO specializations (name, description) VALUES (:name, :desc) ON DUPLICATE KEY UPDATE description=:desc")
       ->execute([':name' => $sName, ':desc' => $sDesc]);
    $stmt = $db->prepare("SELECT id FROM specializations WHERE name = :name");
    $stmt->execute([':name' => $sName]);
    $specIds[$sName] = $stmt->fetchColumn();
    echo "  Specialization: $sName (id={$specIds[$sName]})\n";
}

// ================================================
// 5. DOCTORS
// ================================================
echo "\nSeeding doctor records...\n";
$doctors = [
    ['name'=>'Dr. Abdul Rahman Qureshi',  'spec'=>'Cardiology',      'hospital'=>'DHQ Teaching Hospital Kohat',  'bio'=>'MBBS, FCPS. 12 years of experience in interventional cardiology.',         'phone'=>'0300-1234567','email'=>'user@user.com'],
    ['name'=>'Dr. Fatima Zahra Hussain',  'spec'=>'Gynecology',       'hospital'=>'DHQ Teaching Hospital Kohat',  'bio'=>'MBBS, FCPS (Obs & Gynae). Specialist in high-risk pregnancies.',           'phone'=>'0301-2345678','email'=>'dr.fatimazahra@mednova.pk'],
    ['name'=>'Dr. Muhammad Bilal Khan',   'spec'=>'Neurology',        'hospital'=>'Al-Shifa Medical Centre',       'bio'=>'MBBS, MRCP. Expert in stroke management and neurological disorders.',       'phone'=>'0302-3456789','email'=>'dr.bilalkhan@mednova.pk'],
    ['name'=>'Dr. Aisha Noor Siddiqui',   'spec'=>'Pediatrics',       'hospital'=>'Lady Reading Hospital',         'bio'=>'MBBS, DCH. Dedicated to the health and wellness of children under 14.',    'phone'=>'0303-4567890','email'=>'dr.aishanoor@mednova.pk'],
    ['name'=>'Dr. Usman Tariq',            'spec'=>'Orthopedics',     'hospital'=>'Hayatabad Medical Complex',     'bio'=>'MBBS, FRCS. Joint replacement and sports injury specialist.',               'phone'=>'0304-5678901','email'=>'dr.usmantariq@mednova.pk'],
    ['name'=>'Dr. Zainab Malik',           'spec'=>'Dermatology',     'hospital'=>'Rahmat Clinic Kohat',           'bio'=>'MBBS, MCPS (Derm). Skin, hair and aesthetic medicine specialist.',         'phone'=>'0305-6789012','email'=>'dr.zainabmalik@mednova.pk'],
    ['name'=>'Dr. Muhammad Hamza',         'spec'=>'General Medicine','hospital'=>'Bismillah Medical Clinic',      'bio'=>'MBBS. General physician with 8 years experience in primary care.',          'phone'=>'0306-7890123','email'=>'dr.hamza@mednova.pk'],
    ['name'=>'Dr. Hafsa Nawaz',            'spec'=>'ENT',             'hospital'=>'Noor-e-Shifa Clinic',           'bio'=>'MBBS, FCPS (ENT). Ear, Nose & Throat surgery and allergy specialist.',    'phone'=>'0307-8901234','email'=>'dr.hafsa@mednova.pk'],
    ['name'=>'Dr. Imran Akhtar',           'spec'=>'Surgery',         'hospital'=>'Karak General Hospital',        'bio'=>'MBBS, FCPS (Surgery). Laparoscopic and general surgery expert.',           'phone'=>'0308-9012345','email'=>'dr.imran@mednova.pk'],
    ['name'=>'Dr. Maryam Anwar',           'spec'=>'Psychiatry',      'hospital'=>'Hangu District Hospital',       'bio'=>'MBBS, MRCPsych. Mental health and cognitive therapy specialist.',           'phone'=>'0309-0123456','email'=>'dr.maryam@mednova.pk'],
];

// Doctor image pool
$imgPool = [
    'assets/uploads/doctor-female-1.jpg',
    'assets/uploads/doctor-male-1.jpg',
    'assets/uploads/doctor-male-2.jpg',
    'assets/uploads/doctor-fatima.jpg',
];

$doctorUserIds = [];

foreach ($doctors as $idx => $doc) {
    $hashed = password_hash('admin@123', PASSWORD_DEFAULT);
    
    // Create user
    $uStmt = $db->prepare("INSERT INTO users (name, phone, email, password, role) VALUES (:name,:phone,:email,:pass,'doctor') ON DUPLICATE KEY UPDATE name=:name, role='doctor', password=:pass");
    $uStmt->execute([':name'=>$doc['name'],':phone'=>$doc['phone'],':email'=>$doc['email'],':pass'=>$hashed]);
    
    $uid = $db->lastInsertId() ?: (function() use ($db,$doc) {
        $s=$db->prepare("SELECT id FROM users WHERE email=:e AND role='doctor'");
        $s->execute([':e'=>$doc['email']]);
        return $s->fetchColumn();
    })();

    $doctorUserIds[] = $uid;
    $img = $imgPool[$idx % count($imgPool)];
    
    $hid = $facilityIds[$doc['hospital']] ?? null;
    $sId = $specIds[$doc['spec']] ?? null;
    $dId = $hid ? ($deptIds[$hid][$doc['spec']] ?? null) : null;

    // Create doctor details with specialization_id and department_id
    $dStmt = $db->prepare("INSERT INTO doctors_details (user_id, specialization_id, department_id, specialization, phone, biography, rating, reviews_count, joining_date, profile_image) 
        VALUES (:uid,:spec_id,:dept_id,:spec,:phone,:bio,:rat,:rev,NOW(),:img) 
        ON DUPLICATE KEY UPDATE specialization=:spec, specialization_id=:spec_id, department_id=:dept_id");
    $dStmt->execute([
        ':uid'=>$uid,
        ':spec_id'=>$sId,
        ':dept_id'=>$dId,
        ':spec'=>$doc['spec'],
        ':phone'=>$doc['phone'],
        ':bio'=>$doc['bio'],
        ':rat'=>rand(40,50)/10,
        ':rev'=>rand(10,200),
        ':img'=>$img
    ]);

    // Affiliate to hospital
    if ($hid) {
        $affStmt = $db->prepare("INSERT INTO doctor_hospital_affiliations (doctor_id, hospital_id, department_id, consultation_fee) 
            VALUES (:did,:hid,:dept,:fee) ON DUPLICATE KEY UPDATE consultation_fee=:fee");
        $affStmt->execute([':did'=>$uid,':hid'=>$hid,':dept'=>$dId,':fee'=>rand(5,20)*100]);
        
        // Seed availability in schedules table
        $schedStmt = $db->prepare("INSERT INTO schedules (doctor_id, day_of_week, start_time, end_time) VALUES (:did, :day, :start, :end)");
        $schedStmt->execute([':did'=>$uid, ':day'=>'Monday', ':start'=>'09:00:00', ':end'=>'13:00:00']);
        $schedStmt->execute([':did'=>$uid, ':day'=>'Wednesday', ':start'=>'14:00:00', ':end'=>'18:00:00']);
    }
    echo "  Doctor: {$doc['name']} ({$doc['spec']}) → {$doc['hospital']} (specialization_id=$sId)\n";
}

// ================================================
// 6. PATIENTS
// ================================================
echo "\nSeeding patient records...\n";
$patients = [
    ['name' => 'Muhammad Ali',   'age' => 32, 'gender' => 'Male',   'blood' => 'A+',  'phone' => '0300-1111111', 'email' => 'user@user.com',    'address' => 'KDA Sector 8, Kohat'],
    ['name' => 'Sana Khan',      'age' => 27, 'gender' => 'Female', 'blood' => 'B+',  'phone' => '0301-2222222', 'email' => 'sana@gmail.com',   'address' => 'Hayatabad Phase 4, Peshawar'],
    ['name' => 'Zia-ur-Rehman',  'age' => 45, 'gender' => 'Male',   'blood' => 'O-',  'phone' => '0302-3333333', 'email' => 'zia@gmail.com',    'address' => 'Civil Lines, Hangu'],
    ['name' => 'Aisha Bibi',     'age' => 60, 'gender' => 'Female', 'blood' => 'AB+', 'phone' => '0303-4444444', 'email' => 'aisha@gmail.com',  'address' => 'Main Bazaar, Karak'],
    ['name' => 'Tariq Mahmood',  'age' => 19, 'gender' => 'Male',   'blood' => 'A-',  'phone' => '0304-5555555', 'email' => 'tariq@gmail.com',  'address' => 'University Road, Kohat'],
];

$patientIds = [];
foreach ($patients as $pat) {
    $hashed = password_hash('admin@123', PASSWORD_DEFAULT);
    
    // Create patient user
    $uStmt = $db->prepare("INSERT INTO users (name, phone, email, password, role) VALUES (:name, :phone, :email, :pass, 'patient') ON DUPLICATE KEY UPDATE name=:name, role='patient', password=:pass");
    $uStmt->execute([':name' => $pat['name'], ':phone' => $pat['phone'], ':email' => $pat['email'], ':pass' => $hashed]);
    
    $uid = $db->lastInsertId() ?: (function() use ($db, $pat) {
        $s = $db->prepare("SELECT id FROM users WHERE email=:e AND role='patient'");
        $s->execute([':e'=>$pat['email']]);
        return $s->fetchColumn();
    })();

    // Insert patient details
    $pStmt = $db->prepare("INSERT INTO patients_details (user_id, name, age, gender, blood_group, phone, address) 
        VALUES (:uid, :name, :age, :gen, :bg, :phone, :addr) ON DUPLICATE KEY UPDATE name=:name, age=:age, gender=:gen, blood_group=:bg");
    $pStmt->execute([
        ':uid' => $uid,
        ':name' => $pat['name'],
        ':age' => $pat['age'],
        ':gen' => $pat['gender'],
        ':bg' => $pat['blood'],
        ':phone' => $pat['phone'],
        ':addr' => $pat['address']
    ]);

    $pId = $db->lastInsertId() ?: (function() use ($db, $uid) {
        $s = $db->prepare("SELECT id FROM patients_details WHERE user_id=:uid");
        $s->execute([':uid'=>$uid]);
        return $s->fetchColumn();
    })();
    
    $patientIds[] = $pId;
    echo "  Patient: {$pat['name']} (id=$pId, email={$pat['email']})\n";
}

// ================================================
// 7. APPOINTMENTS
// ================================================
echo "\nSeeding appointment records...\n";
$statuses = ['pending', 'confirmed', 'completed', 'cancelled'];
$types = ['consultation', 'follow_up', 'emergency'];
$slots = ['10:00 AM - 10:30 AM', '11:00 AM - 11:30 AM', '02:00 PM - 02:30 PM', '04:00 PM - 04:30 PM'];

for ($i = 0; $i < 15; $i++) {
    $patientId = $patientIds[$i % count($patientIds)];
    $docUserId = $doctorUserIds[$i % count($doctorUserIds)];
    
    // Fetch affiliated hospital
    $affStmt = $db->prepare("SELECT hospital_id FROM doctor_hospital_affiliations WHERE doctor_id = :did LIMIT 1");
    $affStmt->execute([':did' => $docUserId]);
    $hospitalId = $affStmt->fetchColumn() ?: null;

    $date = date('Y-m-d', strtotime("+" . ($i - 5) . " days")); // spans past and future
    $status = $statuses[$i % count($statuses)];
    $type = $types[$i % count($types)];
    $slot = $slots[$i % count($slots)];
    $token = ($i % 3) + 1;

    $stmt = $db->prepare("INSERT INTO appointments (patient_id, doctor_id, hospital_id, token_number, date, time_slot, type, status) 
        VALUES (:pid, :did, :hid, :token, :date, :slot, :type, :status)");
    $stmt->execute([
        ':pid' => $patientId,
        ':did' => $docUserId,
        ':hid' => $hospitalId,
        ':token' => $token,
        ':date' => $date,
        ':slot' => $slot,
        ':type' => $type,
        ':status' => $status
    ]);
    
    $appId = $db->lastInsertId();
    
    // Seed a payment if confirmed or completed
    if ($status === 'confirmed' || $status === 'completed') {
        $pStatus = ($status === 'completed') ? 'paid' : 'pending';
        $payStmt = $db->prepare("INSERT INTO payments (appointment_id, amount, status, transaction_id) VALUES (:aid, :amt, :status, :tid)");
        $payStmt->execute([
            ':aid' => $appId,
            ':amt' => rand(5, 15) * 100,
            ':status' => $pStatus,
            ':tid' => 'TXN-' . rand(100000, 999999)
        ]);
    }

    // Seed a prescription if completed
    if ($status === 'completed') {
        $prescStmt = $db->prepare("INSERT INTO prescriptions (appointment_id, symptoms, diagnosis, medicines, advice) 
            VALUES (:aid, 'Fever, sore throat, fatigue', 'Viral Upper Respiratory Infection', 'Panadol 500mg 3x daily, Surbex-Z 1x daily', 'Take rest and drink plenty of fluids.')");
        $prescStmt->execute([':aid' => $appId]);
    }
}
echo "  15 realistic appointment records, payments, and prescriptions seeded.\n";

// ================================================
// 8. BLOOD DONORS
// ================================================
echo "\nSeeding blood donors...\n";
$donors = [
    ['name' => 'Ahmad Shah',      'age' => 24, 'bg' => 'O+',  'phone' => '0333-9876541', 'loc' => 'Kohat Cantt'],
    ['name' => 'Bilal Afridi',     'age' => 29, 'bg' => 'A+',  'phone' => '0334-9876542', 'loc' => 'Jungle Khel, Kohat'],
    ['name' => 'Maria Gul',        'age' => 31, 'bg' => 'B+',  'phone' => '0335-9876543', 'loc' => 'Saddar, Peshawar'],
    ['name' => 'Hamza Bangash',    'age' => 22, 'bg' => 'AB-', 'phone' => '0336-9876544', 'loc' => 'Hangu City'],
    ['name' => 'Kiran Shahzadi',   'age' => 28, 'bg' => 'O-',  'phone' => '0337-9876545', 'loc' => 'Karak Cantonment'],
];

foreach ($donors as $idx => $don) {
    // Distribute among seeded hospitals
    $hospId = array_values($facilityIds)[$idx % count($facilityIds)];
    $stmt = $db->prepare("INSERT INTO blood_donors (name, age, blood_group, phone, location, hospital_id) 
        VALUES (:name, :age, :bg, :phone, :loc, :hid)");
    $stmt->execute([
        ':name' => $don['name'],
        ':age' => $don['age'],
        ':bg' => $don['bg'],
        ':phone' => $don['phone'],
        ':loc' => $don['loc'],
        ':hid' => $hospId
    ]);
}
echo "  5 blood donors seeded.\n";

// ================================================
// 9. BLOOD REQUESTS
// ================================================
echo "\nSeeding blood requests...\n";
$requests = [
    ['name' => 'Zahid Khan',   'bg' => 'A+',  'urgency' => 'Emergency', 'phone' => '0345-1234561', 'status' => 'pending'],
    ['name' => 'Noreen Gul',   'bg' => 'O-',  'urgency' => 'Urgent',    'phone' => '0346-1234562', 'status' => 'pending'],
    ['name' => 'Asad Mahmood', 'bg' => 'B+',  'urgency' => 'Normal',    'phone' => '0347-1234563', 'status' => 'fulfilled'],
    ['name' => 'Farhana Bibi', 'bg' => 'AB+', 'urgency' => 'Emergency', 'phone' => '0348-1234564', 'status' => 'pending'],
];

foreach ($requests as $idx => $req) {
    $hospId = array_values($facilityIds)[$idx % count($facilityIds)];
    $stmt = $db->prepare("INSERT INTO blood_requests (patient_name, blood_group, urgency, phone, hospital_id, status) 
        VALUES (:name, :bg, :urgency, :phone, :hid, :status)");
    $stmt->execute([
        ':name' => $req['name'],
        ':bg' => $req['bg'],
        ':urgency' => $req['urgency'],
        ':phone' => $req['phone'],
        ':hid' => $hospId,
        ':status' => $req['status']
    ]);
}
echo "  4 blood requests seeded.\n";

// ================================================
// 10. DIET PLANS
// ================================================
echo "\nSeeding diet plan requests...\n";
$dietPlans = [
    ['name' => 'Muhammad Ali',  'phone' => '0300-1111111', 'age' => 32, 'w' => 84.5, 'h' => 176, 'goal' => 'weight_loss', 'cond' => 'Borderline high blood pressure'],
    ['name' => 'Sana Khan',     'phone' => '0301-2222222', 'age' => 27, 'w' => 61.2, 'h' => 162, 'goal' => 'muscle_gain', 'cond' => 'None'],
    ['name' => 'Zia-ur-Rehman', 'phone' => '0302-3333333', 'age' => 45, 'w' => 95.0, 'h' => 180, 'goal' => 'diabetic_diet', 'cond' => 'Type 2 Diabetes'],
    ['name' => 'Aisha Bibi',    'phone' => '0303-4444444', 'age' => 60, 'w' => 70.0, 'h' => 155, 'goal' => 'heart_healthy', 'cond' => 'High Cholesterol'],
];

foreach ($dietPlans as $idx => $plan) {
    $hospId = array_values($facilityIds)[$idx % count($facilityIds)];
    $docUserId = $doctorUserIds[$idx % count($doctorUserIds)];
    
    $stmt = $db->prepare("INSERT INTO diet_plans (hospital_id, doctor_id, patient_name, patient_phone, age, weight, height, goal, conditions, status) 
        VALUES (:hid, :did, :name, :phone, :age, :w, :h, :goal, :cond, 'pending')");
    $stmt->execute([
        ':hid' => $hospId,
        ':did' => $docUserId,
        ':name' => $plan['name'],
        ':phone' => $plan['phone'],
        ':age' => $plan['age'],
        ':w' => $plan['w'],
        ':h' => $plan['h'],
        ':goal' => $plan['goal'],
        ':cond' => $plan['cond']
    ]);
}
echo "  4 diet plan requests seeded.\n";

echo "\n✅ Seeding process successfully complete!\n";
echo "</pre>";
