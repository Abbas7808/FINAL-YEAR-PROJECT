<?php
require 'config/database.php';
$db = (new Database())->getConnection();
$stmt = $db->query('SELECT * FROM appointments');
echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
