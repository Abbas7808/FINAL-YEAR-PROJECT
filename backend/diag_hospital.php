<?php
session_start();
require_once __DIR__ . '/config/Database.php';
$db = (new Database())->getConnection();

echo "<pre style='font-family:monospace; background:#1a1a2e; color:#00ff88; padding:20px; border-radius:8px;'>";
echo "<h2 style='color:#FFD700;'>🔍 Hospital Admin Diagnostic</h2>";

// 1. Session state
echo "<b style='color:#00bfff;'>SESSION STATE:</b>\n";
echo "user_id = " . ($_SESSION['user_id'] ?? '❌ NOT SET') . "\n";
echo "role    = " . ($_SESSION['role'] ?? '❌ NOT SET') . "\n";
echo "name    = " . ($_SESSION['name'] ?? '—') . "\n\n";

// 2. All users with role = hospital_admin
echo "<b style='color:#00bfff;'>USERS WITH role='hospital_admin':</b>\n";
$stmt = $db->query("SELECT id, name, email, role FROM users WHERE role = 'hospital_admin'");
$admins = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($admins) {
    foreach ($admins as $a) {
        echo "  id={$a['id']}, name={$a['name']}, email={$a['email']}\n";
    }
} else {
    echo "  ❌ NONE FOUND\n";
}
echo "\n";

// 3. All hospitals and their linked user_id
echo "<b style='color:#00bfff;'>HOSPITALS TABLE (id, name, user_id, subscription_status):</b>\n";
$stmt = $db->query("SELECT id, name, user_id, subscription_status FROM hospitals");
$hospitals = $stmt->fetchAll(PDO::FETCH_ASSOC);
if ($hospitals) {
    foreach ($hospitals as $h) {
        echo "  hospital_id={$h['id']}, user_id={$h['user_id']}, status={$h['subscription_status']}, name={$h['name']}\n";
    }
} else {
    echo "  ❌ NO HOSPITALS IN DATABASE\n";
}
echo "\n";

// 4. If session has user_id, check if they have a hospital linked
if (isset($_SESSION['user_id'])) {
    $uid = $_SESSION['user_id'];
    echo "<b style='color:#00bfff;'>HOSPITAL LINKED TO SESSION user_id=$uid:</b>\n";
    $stmt = $db->prepare("SELECT id, name, user_id, subscription_status FROM hospitals WHERE user_id = :uid");
    $stmt->execute([':uid' => $uid]);
    $h = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($h) {
        echo "  ✅ Found: hospital_id={$h['id']}, name={$h['name']}, status={$h['subscription_status']}\n";
    } else {
        echo "  ❌ NO HOSPITAL LINKED TO THIS USER\n";
        echo "  → FIX: Run this SQL to link user $uid to an existing hospital:\n";
        echo "    UPDATE hospitals SET user_id = $uid WHERE id = <hospital_id>;\n";
        echo "  OR create a new hospital for them via Super Admin.\n";
    }
} else {
    echo "<b style='color:#ff6b6b;'>❌ Not logged in — session has no user_id</b>\n";
}

echo "\n<b style='color:#FFD700;'>Done.</b></pre>";
