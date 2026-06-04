<?php
require_once 'config/Database.php';

$database = new Database();
$db = $database->getConnection();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$hashed_password = password_hash('12345', PASSWORD_DEFAULT);

$data = [
    'patients' => [],
    'receptionists' => []
];

$patients = $db->query("SELECT id, name FROM users WHERE role='patient' ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$p_index = 1;
foreach ($patients as $patient) {
    $email = "pat{$p_index}@gmail.com";
    $db->prepare("UPDATE users SET email = :email, password = :pass WHERE id = :id")
       ->execute([':email' => $email, ':pass' => $hashed_password, ':id' => $patient['id']]);
    $data['patients'][] = "Name: {$patient['name']} | Email: {$email} | Password: 12345";
    $p_index++;
}

$hospitals = $db->query("SELECT id, name FROM hospitals ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
$r_index = 1;
foreach ($hospitals as $hospital) {
    $email = "recep{$r_index}@gmail.com";
    $name = "Receptionist " . $hospital['name'];
    
    $stmt = $db->prepare("SELECT receptionist_id FROM receptionist_hospital_affiliations WHERE hospital_id = :hid");
    $stmt->execute([':hid' => $hospital['id']]);
    $rid = $stmt->fetchColumn();

    if ($rid) {
        $db->prepare("UPDATE users SET email = :email, password = :pass WHERE id = :id")
           ->execute([':email' => $email, ':pass' => $hashed_password, ':id' => $rid]);
    } else {
        $uStmt = $db->prepare("INSERT INTO users (name, phone, email, password, role) VALUES (:name, '000000', :email, :pass, 'receptionist')");
        $uStmt->execute([':name' => $name, ':email' => $email, ':pass' => $hashed_password]);
        $uid = $db->lastInsertId();

        $affStmt = $db->prepare("INSERT INTO receptionist_hospital_affiliations (receptionist_id, hospital_id) VALUES (:rid, :hid)");
        $affStmt->execute([':rid' => $uid, ':hid' => $hospital['id']]);
    }

    $data['receptionists'][] = "Hospital: {$hospital['name']} | Email: {$email} | Password: 12345";
    $r_index++;
}

header('Content-Type: application/json');
echo json_encode($data, JSON_PRETTY_PRINT);
