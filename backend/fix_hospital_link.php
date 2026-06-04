<?php
/**
 * MedNova — Hospital Admin Link Fix Tool
 * =========================================
 * Diagnoses and fixes: "No Hospital Profile Linked to this Account"
 * 
 * Run at: http://localhost/FinalFYP/backend/fix_hospital_link.php
 * DELETE after use.
 */

// Localhost only
$ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
if (!in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
    die('Access denied. Local use only.');
}

require_once __DIR__ . '/config/Database.php';
$db = (new Database())->getConnection();

$msg = '';
$msgType = '';

// ===== ACTION: Link admin to hospital =====
if (isset($_POST['action']) && $_POST['action'] === 'link') {
    $uid = (int)$_POST['user_id'];
    $hid = (int)$_POST['hospital_id'];
    if ($uid && $hid) {
        // Unlink any existing admin from that hospital
        $db->prepare("UPDATE hospitals SET user_id = NULL WHERE user_id = :uid AND id != :hid")
           ->execute([':uid' => $uid, ':hid' => $hid]);
        // Link
        $db->prepare("UPDATE hospitals SET user_id = :uid WHERE id = :hid")
           ->execute([':uid' => $uid, ':hid' => $hid]);
        // Also ensure user has hospital_admin role
        $db->prepare("UPDATE users SET role = 'hospital_admin' WHERE id = :uid")
           ->execute([':uid' => $uid]);
        $msg = "✅ Successfully linked! Login now at: <a href='?route=auth/login' style='color:#7dd3fc'>Login Page</a>";
        $msgType = 'ok';
    }
}

// ===== ACTION: Reset password =====
if (isset($_POST['action']) && $_POST['action'] === 'reset_pass') {
    $uid  = (int)$_POST['reset_uid'];
    $pass = trim($_POST['new_pass']);
    if ($uid && strlen($pass) >= 4) {
        $hashed = password_hash($pass, PASSWORD_DEFAULT);
        $db->prepare("UPDATE users SET password = :pw WHERE id = :id")->execute([':pw' => $hashed, ':id' => $uid]);
        $msg = "✅ Password reset! Use: <strong>$pass</strong> to login.";
        $msgType = 'ok';
    } else {
        $msg = "❌ Password must be at least 4 characters.";
        $msgType = 'fail';
    }
}

// ===== ACTION: Create new hospital + link existing admin =====
if (isset($_POST['action']) && $_POST['action'] === 'create_hospital') {
    $uid   = (int)$_POST['admin_uid'];
    $hname = trim($_POST['hospital_name']);
    $haddr = trim($_POST['hospital_addr'] ?: 'N/A');
    $rid   = (int)($_POST['region_id'] ?: 0) ?: null;
    
    if ($uid && $hname) {
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $hname))) . '-' . time();
        $exp  = date('Y-m-d H:i:s', strtotime('+30 days'));
        try {
            $db->prepare(
                "INSERT INTO hospitals (name, slug, address, user_id, region_id, subscription_status, plan_type, plan_expires_at)
                 VALUES (:n, :s, :a, :uid, :rid, 'active', 'monthly', :exp)"
            )->execute([':n'=>$hname,':s'=>$slug,':a'=>$haddr,':uid'=>$uid,':rid'=>$rid,':exp'=>$exp]);
            $db->prepare("UPDATE users SET role = 'hospital_admin' WHERE id = :uid")->execute([':uid' => $uid]);
            $msg = "✅ Hospital '<strong>$hname</strong>' created and linked to admin!";
            $msgType = 'ok';
        } catch (Exception $e) {
            $msg = "❌ Error: " . $e->getMessage();
            $msgType = 'fail';
        }
    } else {
        $msg = "❌ Admin and Hospital Name are required.";
        $msgType = 'fail';
    }
}

// ===== Load data =====
$admins   = $db->query("SELECT u.id, u.name, u.email, u.phone, u.role, h.id as hospital_id, h.name as hospital_name
                         FROM users u
                         LEFT JOIN hospitals h ON u.id = h.user_id
                         WHERE u.role IN ('hospital_admin')
                         ORDER BY u.id ASC")->fetchAll(PDO::FETCH_ASSOC);

$allAdmins = $db->query("SELECT id, name, email, phone, role FROM users ORDER BY role, name")->fetchAll(PDO::FETCH_ASSOC);
$hospitals  = $db->query("SELECT h.id, h.name, h.type, h.user_id, u.name as admin_name
                           FROM hospitals h LEFT JOIN users u ON h.user_id = u.id
                           ORDER BY h.name ASC")->fetchAll(PDO::FETCH_ASSOC);
$regions    = $db->query("SELECT id, name FROM regions ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
$unlinkedHospitals = array_filter($hospitals, fn($h) => empty($h['user_id']));
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>MedNova — Hospital Link Fix</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Segoe UI',sans-serif;background:#0b1120;color:#e2e8f0;min-height:100vh;padding:30px}
h1{color:#38bdf8;margin-bottom:5px;font-size:26px}
.sub{color:#64748b;margin-bottom:30px;font-size:14px}
h2{color:#7dd3fc;font-size:16px;margin:25px 0 12px;display:flex;align-items:center;gap:8px}
h2::before{content:'';display:inline-block;width:4px;height:16px;background:#38bdf8;border-radius:2px}
.card{background:#111827;border:1px solid #1e3a5f;border-radius:12px;padding:20px;margin-bottom:20px}
.msg{padding:14px 18px;border-radius:8px;margin-bottom:20px;font-size:15px}
.msg.ok{background:#052e16;color:#4ade80;border:1px solid #166534}
.msg.fail{background:#450a0a;color:#f87171;border:1px solid #991b1b}
table{width:100%;border-collapse:collapse}
th{background:#1e3a5f;color:#7dd3fc;padding:10px 12px;text-align:left;font-size:13px}
td{padding:9px 12px;border-bottom:1px solid #1e3a5f;font-size:13px;vertical-align:middle}
tr:hover td{background:#111f35}
.badge{display:inline-block;padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600}
.linked{background:#052e16;color:#4ade80;border:1px solid #166534}
.unlinked{background:#450a0a;color:#f87171;border:1px solid #991b1b}
select,input[type=text],input[type=password]{width:100%;padding:9px 12px;background:#0f172a;border:1px solid #1e3a5f;color:#e2e8f0;border-radius:6px;font-size:14px;margin-bottom:10px}
select:focus,input:focus{outline:none;border-color:#38bdf8}
.btn{padding:9px 18px;border:none;border-radius:6px;font-size:14px;cursor:pointer;font-weight:600}
.btn-primary{background:#2563eb;color:white}
.btn-primary:hover{background:#1d4ed8}
.btn-danger{background:#dc2626;color:white}
.btn-sm{padding:5px 12px;font-size:12px}
.grid-2{display:grid;grid-template-columns:1fr 1fr;gap:20px}
@media(max-width:768px){.grid-2{grid-template-columns:1fr}}
label{display:block;font-size:12px;color:#94a3b8;margin-bottom:4px;font-weight:600}
.divider{border:none;border-top:1px solid #1e3a5f;margin:20px 0}
</style>
</head>
<body>

<h1>🏥 MedNova — Hospital Admin Link Fix</h1>
<p class="sub">Diagnose and fix: <em>"No Hospital Profile Linked to this Account"</em></p>

<?php if ($msg): ?>
<div class="msg <?= $msgType ?>"><?= $msg ?></div>
<?php endif; ?>

<!-- ===== SECTION 1: Current hospital_admin accounts ===== -->
<div class="card">
<h2>Hospital Admin Accounts & Their Linked Hospitals</h2>
<?php if (empty($admins)): ?>
    <p style="color:#f87171">⚠️ No users with role <strong>hospital_admin</strong> found in the database!</p>
<?php else: ?>
<table>
    <tr>
        <th>ID</th><th>Name</th><th>Email / Phone</th><th>Linked Hospital</th><th>Status</th>
    </tr>
    <?php foreach ($admins as $a): ?>
    <tr>
        <td><?= $a['id'] ?></td>
        <td><strong><?= htmlspecialchars($a['name']) ?></strong></td>
        <td><?= htmlspecialchars($a['email'] ?: $a['phone']) ?></td>
        <td><?= $a['hospital_name'] ? htmlspecialchars($a['hospital_name']) : '<em style="color:#64748b">None</em>' ?></td>
        <td>
            <?php if ($a['hospital_id']): ?>
                <span class="badge linked">✅ Linked</span>
            <?php else: ?>
                <span class="badge unlinked">❌ NOT LINKED</span>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
</div>

<!-- ===== SECTION 2: All hospitals ===== -->
<div class="card">
<h2>All Hospitals in Database</h2>
<?php if (empty($hospitals)): ?>
    <p style="color:#f87171">⚠️ No hospitals found! You need to create one below.</p>
<?php else: ?>
<table>
    <tr><th>ID</th><th>Name</th><th>Type</th><th>Linked Admin</th><th>Status</th></tr>
    <?php foreach ($hospitals as $h): ?>
    <tr>
        <td><?= $h['id'] ?></td>
        <td><strong><?= htmlspecialchars($h['name']) ?></strong></td>
        <td><?= $h['type'] ?></td>
        <td><?= $h['admin_name'] ? htmlspecialchars($h['admin_name']) : '<em style="color:#64748b">None</em>' ?></td>
        <td>
            <?php if ($h['user_id']): ?>
                <span class="badge linked">✅ Has Admin</span>
            <?php else: ?>
                <span class="badge unlinked">⚠️ No Admin</span>
            <?php endif; ?>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
<?php endif; ?>
</div>

<!-- ===== SECTION 3: Fix Actions ===== -->
<div class="grid-2">

<!-- FIX A: Link existing admin to existing hospital -->
<div class="card">
<h2>🔗 Link Admin to Hospital</h2>
<p style="color:#94a3b8;font-size:13px;margin-bottom:15px">Select an existing user and link them to a hospital. Sets role to hospital_admin.</p>
<form method="POST">
    <input type="hidden" name="action" value="link">
    <label>Select User (Admin)</label>
    <select name="user_id" required>
        <option value="">-- Select User --</option>
        <?php foreach ($allAdmins as $u): ?>
        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= $u['role'] ?>) — <?= htmlspecialchars($u['email'] ?: $u['phone']) ?></option>
        <?php endforeach; ?>
    </select>
    <label>Select Hospital</label>
    <select name="hospital_id" required>
        <option value="">-- Select Hospital --</option>
        <?php foreach ($hospitals as $h): ?>
        <option value="<?= $h['id'] ?>"><?= htmlspecialchars($h['name']) ?> <?= $h['user_id'] ? '(has admin)' : '' ?></option>
        <?php endforeach; ?>
    </select>
    <button type="submit" class="btn btn-primary">🔗 Link Now</button>
</form>
</div>

<!-- FIX B: Reset password -->
<div class="card">
<h2>🔑 Reset User Password</h2>
<p style="color:#94a3b8;font-size:13px;margin-bottom:15px">If login shows "invalid password", reset it here.</p>
<form method="POST">
    <input type="hidden" name="action" value="reset_pass">
    <label>Select User</label>
    <select name="reset_uid" required>
        <option value="">-- Select User --</option>
        <?php foreach ($allAdmins as $u): ?>
        <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= $u['role'] ?>)</option>
        <?php endforeach; ?>
    </select>
    <label>New Password</label>
    <input type="text" name="new_pass" placeholder="e.g. admin123" value="admin123">
    <button type="submit" class="btn btn-primary">🔑 Reset Password</button>
</form>
</div>

</div>

<!-- FIX C: Create hospital if none exists -->
<?php if (empty($hospitals) || !empty($unlinkedHospitals)): ?>
<div class="card">
<h2>➕ Create New Hospital & Link Admin</h2>
<p style="color:#94a3b8;font-size:13px;margin-bottom:15px">No hospital found? Create one and link it immediately.</p>
<form method="POST">
    <input type="hidden" name="action" value="create_hospital">
    <div class="grid-2">
        <div>
            <label>Admin User to Link</label>
            <select name="admin_uid" required>
                <option value="">-- Select User --</option>
                <?php foreach ($allAdmins as $u): ?>
                <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?> (<?= $u['role'] ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Region (optional)</label>
            <select name="region_id">
                <option value="">-- No Region --</option>
                <?php foreach ($regions as $r): ?>
                <option value="<?= $r['id'] ?>"><?= htmlspecialchars($r['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Hospital Name *</label>
            <input type="text" name="hospital_name" placeholder="e.g. City General Hospital" required>
        </div>
        <div>
            <label>Hospital Address</label>
            <input type="text" name="hospital_addr" placeholder="e.g. Main Street, Karachi">
        </div>
    </div>
    <button type="submit" class="btn btn-primary">➕ Create & Link Hospital</button>
</form>
</div>
<?php endif; ?>

<hr class="divider">
<p style="color:#475569;font-size:12px;text-align:center">
    ⚠️ <strong>Delete this file after use:</strong> <code>backend/fix_hospital_link.php</code>
    &nbsp;|&nbsp; 
    <a href="?route=auth/login" style="color:#38bdf8">→ Go to Login</a>
</p>

</body>
</html>
