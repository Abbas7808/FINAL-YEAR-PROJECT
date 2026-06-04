<?php
/**
 * MedNova Login Diagnostics Tool
 * ================================
 * This file helps diagnose login issues.
 * Run it in browser: http://localhost/FinalFYP/backend/login_debug.php
 * DELETE THIS FILE after diagnosing the issue.
 */

// Only allow localhost access for security
$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
if (!in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
    die('Access denied. This tool is for local use only.');
}

require_once __DIR__ . '/config/Database.php';

$db = (new Database())->getConnection();

echo "<style>
body { font-family: 'Segoe UI', monospace; padding: 30px; background: #0f172a; color: #e2e8f0; }
h2 { color: #38bdf8; border-bottom: 1px solid #1e3a5f; padding-bottom: 10px; }
table { border-collapse: collapse; width: 100%; margin: 15px 0; }
th { background: #1e3a5f; color: #7dd3fc; padding: 10px; text-align: left; }
td { padding: 8px 10px; border-bottom: 1px solid #1e3a5f; }
.ok { color: #4ade80; }
.fail { color: #f87171; }
.warn { color: #fbbf24; }
.badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-size: 12px; }
.hash-ok { background: #14532d; color: #4ade80; }
.hash-fail { background: #450a0a; color: #f87171; }
.fix-box { background: #1e3a5f; border: 1px solid #38bdf8; border-radius: 8px; padding: 15px; margin: 10px 0; }
</style>";

echo "<h2>🔐 MedNova Login Diagnostics</h2>";

// ===== STEP 1: List all users =====
echo "<h2>1. All Users in Database</h2>";
try {
    $stmt = $db->query("SELECT id, name, email, phone, username, role, is_active, 
                        SUBSTRING(password, 1, 7) as hash_prefix,
                        LENGTH(password) as pw_len,
                        password as full_password
                        FROM users ORDER BY role, id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Phone</th><th>Role</th><th>Active</th><th>Hash Type</th><th>PW Length</th></tr>";
    foreach ($users as $u) {
        $prefix = $u['hash_prefix'];
        $isHashed = ($prefix === '$2y$10' || $prefix === '$2y$12' || substr($u['full_password'], 0, 4) === '$2y$');
        $hashClass = $isHashed ? 'hash-ok' : 'hash-fail';
        $hashLabel = $isHashed ? '✅ Bcrypt' : '❌ PLAIN TEXT';
        $activeLabel = $u['is_active'] ? '<span class="ok">Active</span>' : '<span class="fail">Inactive</span>';
        echo "<tr>
            <td>{$u['id']}</td>
            <td>{$u['name']}</td>
            <td>{$u['email']}</td>
            <td>{$u['phone']}</td>
            <td><strong>{$u['role']}</strong></td>
            <td>{$activeLabel}</td>
            <td><span class='badge {$hashClass}'>{$hashLabel}</span></td>
            <td>{$u['pw_len']}</td>
        </tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p class='fail'>Error: " . $e->getMessage() . "</p>";
}

// ===== STEP 2: Test common passwords =====
echo "<h2>2. Password Verification Test</h2>";
$testPasswords = ['admin123', 'admin', '123456', 'password', 'hospital', 'doctor123'];

try {
    $stmt = $db->query("SELECT id, name, email, role, password FROM users");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "<table>";
    echo "<tr><th>User</th><th>Role</th>";
    foreach ($testPasswords as $p) {
        echo "<th>\"$p\"</th>";
    }
    echo "</tr>";

    foreach ($users as $u) {
        echo "<tr><td>{$u['name']}<br><small style='color:#94a3b8'>{$u['email']}</small></td><td>{$u['role']}</td>";
        foreach ($testPasswords as $p) {
            $ok = password_verify($p, $u['password']);
            // Also check plain text match (in case DB has plain text)
            $plainOk = ($p === $u['password']);
            if ($ok) {
                echo "<td class='ok'>✅ MATCH</td>";
            } elseif ($plainOk) {
                echo "<td class='warn'>⚠️ PLAIN</td>";
            } else {
                echo "<td style='color:#475569'>✗</td>";
            }
        }
        echo "</tr>";
    }
    echo "</table>";
} catch (Exception $e) {
    echo "<p class='fail'>Error: " . $e->getMessage() . "</p>";
}

// ===== STEP 3: Fix Option =====
echo "<h2>3. Fix Passwords</h2>";

if (isset($_POST['fix_passwords'])) {
    $fixes = [
        ['email' => 'admin@admin.com', 'role' => 'admin', 'pass' => 'admin123'],
    ];

    // Also fix any hospital_admin, doctor accounts
    $stmt = $db->query("SELECT id, email, role, password FROM users WHERE role IN ('hospital_admin', 'doctor', 'receptionist')");
    $staffUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($staffUsers as $u) {
        // Only fix if password is NOT already bcrypt
        if (substr($u['password'], 0, 4) !== '$2y$') {
            $fixes[] = ['id' => $u['id'], 'email' => $u['email'], 'role' => $u['role'], 'pass' => 'admin123'];
        }
    }

    foreach ($fixes as $fix) {
        try {
            $hashed = password_hash($fix['pass'], PASSWORD_DEFAULT);
            if (isset($fix['id'])) {
                $stmt = $db->prepare("UPDATE users SET password = :pw WHERE id = :id");
                $stmt->execute([':pw' => $hashed, ':id' => $fix['id']]);
            } else {
                $stmt = $db->prepare("UPDATE users SET password = :pw WHERE email = :email");
                $stmt->execute([':pw' => $hashed, ':email' => $fix['email']]);
            }
            echo "<p class='ok'>✅ Fixed password for: {$fix['email']} ({$fix['role']}) → New password: <strong>{$fix['pass']}</strong></p>";
        } catch (Exception $e) {
            echo "<p class='fail'>❌ Failed: " . $e->getMessage() . "</p>";
        }
    }
}

echo "<div class='fix-box'>";
echo "<h3 style='color:#fbbf24;margin-top:0'>⚠️ Quick Fix Option</h3>";
echo "<p>If passwords are plain text or corrupted, click below to reset all staff passwords to <strong>admin123</strong>:</p>";
echo "<form method='POST'>
    <button type='submit' name='fix_passwords' value='1' 
            style='background:#2563eb;color:white;border:none;padding:12px 24px;border-radius:8px;font-size:15px;cursor:pointer;'>
        🔧 Reset All Staff Passwords to \"admin123\"
    </button>
</form>";
echo "</div>";

// ===== STEP 4: Manual test =====
echo "<h2>4. Manual Login Test</h2>";
if (isset($_POST['test_login'])) {
    $testEmail = $_POST['test_email'];
    $testPass  = $_POST['test_pass'];
    $stmt = $db->prepare("SELECT id, name, email, role, password, is_active FROM users WHERE email = :e OR phone = :e OR username = :e LIMIT 1");
    $stmt->execute([':e' => $testEmail]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($u) {
        echo "<p class='ok'>✅ User found: <strong>{$u['name']}</strong> | Role: <strong>{$u['role']}</strong> | Active: " . ($u['is_active'] ? 'Yes' : 'No') . "</p>";
        if (password_verify($testPass, $u['password'])) {
            echo "<p class='ok' style='font-size:18px'>✅ <strong>PASSWORD CORRECT — Login should work!</strong></p>";
        } else {
            echo "<p class='fail' style='font-size:18px'>❌ <strong>PASSWORD WRONG — hash mismatch!</strong></p>";
            echo "<p class='warn'>Try the Reset button above to fix passwords.</p>";
        }
    } else {
        echo "<p class='fail'>❌ No user found with that email/phone/username.</p>";
    }
}
echo "<form method='POST' style='background:#1e3a5f;padding:20px;border-radius:8px;'>
    <p style='margin-top:0'>Test a specific login:</p>
    <input type='text' name='test_email' placeholder='Email or Phone' required
           style='width:100%;padding:10px;margin:5px 0;border-radius:6px;border:1px solid #38bdf8;background:#0f172a;color:white;box-sizing:border-box;'>
    <input type='text' name='test_pass' placeholder='Password to test'
           style='width:100%;padding:10px;margin:5px 0;border-radius:6px;border:1px solid #38bdf8;background:#0f172a;color:white;box-sizing:border-box;'>
    <button type='submit' name='test_login' value='1'
            style='background:#0ea5e9;color:white;border:none;padding:10px 20px;border-radius:6px;cursor:pointer;margin-top:5px;'>
        Test Login
    </button>
</form>";

echo "<br><p style='color:#475569;font-size:12px;'>⚠️ Delete this file after diagnosing: <code>backend/login_debug.php</code></p>";
