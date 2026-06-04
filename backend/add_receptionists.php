<?php
require_once 'config/Database.php';

$database = new Database();
$db = $database->getConnection();
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "Adding Receptionists for each hospital...\n";

$hospitals = $db->query("SELECT id, name, slug FROM hospitals")->fetchAll(PDO::FETCH_ASSOC);
$hashed_password = password_hash('admin@123', PASSWORD_DEFAULT);

foreach ($hospitals as $hospital) {
    $email = "receptionist@" . $hospital['slug'] . ".com";
    $name = "Receptionist " . $hospital['name'];
    $phone = "0000000000";

    // Insert or update User
    $uStmt = $db->prepare("INSERT INTO users (name, phone, email, password, role) VALUES (:name, :phone, :email, :pass, 'receptionist') ON DUPLICATE KEY UPDATE name=:name, role='receptionist', password=:pass");
    $uStmt->execute([':name' => $name, ':phone' => $phone, ':email' => $email, ':pass' => $hashed_password]);

    $uid = $db->lastInsertId() ?: (function() use ($db, $email) {
        $s = $db->prepare("SELECT id FROM users WHERE email=:e AND role='receptionist'");
        $s->execute([':e' => $email]);
        return $s->fetchColumn();
    })();

    // Insert Affiliation
    $affStmt = $db->prepare("INSERT INTO receptionist_hospital_affiliations (receptionist_id, hospital_id) VALUES (:rid, :hid) ON DUPLICATE KEY UPDATE hospital_id=:hid");
    $affStmt->execute([':rid' => $uid, ':hid' => $hospital['id']]);

    echo "Hospital: {$hospital['name']} | Email: {$email} | Password: admin@123\n";
}

echo "Done.\n";
