<?php
require_once 'config/Database.php';
$db = (new Database())->getConnection();

echo "=== departments ===\n";
$cols = $db->query("DESCRIBE departments")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) echo "  " . $c['Field'] . " (" . $c['Type'] . ")\n";
$rows = $db->query("SELECT * FROM departments LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo json_encode($r) . "\n";

echo "\n=== specializations ===\n";
$cols = $db->query("DESCRIBE specializations")->fetchAll(PDO::FETCH_ASSOC);
foreach ($cols as $c) echo "  " . $c['Field'] . " (" . $c['Type'] . ")\n";
$rows = $db->query("SELECT * FROM specializations LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) echo json_encode($r) . "\n";
