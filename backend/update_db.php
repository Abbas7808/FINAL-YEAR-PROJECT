<?php
require_once 'config/Database.php';

$database = new Database();
$db = $database->getConnection();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$hashed_password = password_hash('12345', PASSWORD_DEFAULT);

// Update Patients
$patients = $db->query("SELECT id FROM users WHERE role='patient' ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$p_index = 1;
foreach ($patients as $patient) {
    $email = "pat{$p_index}@gmail.com";
    $db->prepare("UPDATE users SET email = :email, password = :pass WHERE id = :id")
       ->execute([':email' => $email, ':pass' => $hashed_password, ':id' => $patient['id']]);
    $p_index++;
}

// Update Receptionists
$hospitals = $db->query("SELECT id FROM hospitals ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$r_index = 1;
foreach ($hospitals as $hospital) {
    $email = "recep{$r_index}@gmail.com";
    
    $stmt = $db->prepare("SELECT receptionist_id FROM receptionist_hospital_affiliations WHERE hospital_id = :hid");
    $stmt->execute([':hid' => $hospital['id']]);
    $rid = $stmt->fetchColumn();

    if ($rid) {
        $db->prepare("UPDATE users SET email = :email, password = :pass WHERE id = :id")
           ->execute([':email' => $email, ':pass' => $hashed_password, ':id' => $rid]);
    }
    $r_index++;
}

echo "Database updated successfully.";
