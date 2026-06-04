<?php
require_once 'config/Database.php';
$db = (new Database())->getConnection();
$stmt = $db->query("SELECT id, name, email, phone, role FROM users WHERE role='admin'");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($admins as $a) {
    echo "ID:" . $a['id'] . " NAME:" . $a['name'] . " EMAIL:" . $a['email'] . " PHONE:" . $a['phone'] . " ROLE:" . $a['role'] . "\n";
}
