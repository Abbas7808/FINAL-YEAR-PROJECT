<?php include 'views/layouts/header.php'; ?>

<style>
    .settings-card {
        background: white;
        border-radius: 16px;
        padding: 30px;
        margin-bottom: 24px;
        box-shadow: 0 2px 12px rgba(0,0,0,.06);
        border: 1px solid #eef2f7;
    }
    .settings-card h5 {
        font-weight: 700;
        margin-bottom: 6px;
        color: #1a1a2e;
    }
    .settings-card .card-desc {
        color: #6b7280;
        font-size: 0.88rem;
        margin-bottom: 20px;
    }
    .settings-card .icon-box {
        width: 48px; height: 48px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.2rem;
        flex-shrink: 0;
    }
    .settings-card .icon-box.blue { background: #dbeafe; color: #2563eb; }
    .settings-card .icon-box.purple { background: #ede9fe; color: #7c3aed; }
    .settings-card .icon-box.green { background: #dcfce7; color: #16a34a; }

    .user-row {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 12px 16px;
        border-radius: 12px;
        background: #f8fafc;
        margin-bottom: 8px;
        transition: background .15s;
    }
    .user-row:hover { background: #eef2ff; }
    .user-row .avatar {
        width: 40px; height: 40px;
        border-radius: 50%;
        background: #dbeafe;
        color: #2563eb;
        display: flex; align-items: center; justify-content: center;
        font-weight: 700;
        font-size: 0.95rem;
        flex-shrink: 0;
    }
    .user-row .info { flex-grow: 1; }
    .user-row .info .name { font-weight: 600; color: #1a1a2e; font-size: 0.92rem; }
    .user-row .info .meta { font-size: 0.78rem; color: #9ca3af; }
    .role-badge {
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 0.72rem;
        font-weight: 600;
        text-transform: uppercase;
    }
    .role-badge.admin { background: #fee2e2; color: #dc2626; }
    .role-badge.hospital_admin { background: #dbeafe; color: #2563eb; }
    .role-badge.doctor { background: #dcfce7; color: #16a34a; }
    .role-badge.receptionist { background: #fef3c7; color: #d97706; }
    .role-badge.patient { background: #ede9fe; color: #7c3aed; }
    .search-input-wrap { position: relative; }
    .search-input-wrap i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #9ca3af; }
    .search-input-wrap input { padding-left: 40px; border: 1px solid #e5e7eb; border-radius: 10px; height: 42px; width: 100%; }
</style>

<!-- Page Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h4 style="font-weight: 700; margin: 0; color: #1a1a2e;">🛡️ Users & Access</h4>
        <p style="color: #6b7280; font-size: 0.9rem; margin: 4px 0 0;">Manage users, assign roles, and handle passwords</p>
    </div>
</div>

<?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert" style="border-radius:12px;">
        <?= $_SESSION['success']; unset($_SESSION['success']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>
<?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert" style="border-radius:12px;">
        <?= $_SESSION['error']; unset($_SESSION['error']); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row g-4">
    <!-- LEFT COLUMN: ADD USER -->
    <div class="col-lg-5">
        <div class="settings-card">
            <div class="d-flex align-items-start gap-3 mb-3">
                <div class="icon-box green"><i class="fas fa-user-plus"></i></div>
                <div>
                    <h5>Add New User</h5>
                    <p class="card-desc">Create a new user account and directly assign their access role.</p>
                </div>
            </div>
            <form action="?route=admin/add_user" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold small">Full Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="Ex: John Doe">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Phone / User ID</label>
                    <input type="text" name="phone" class="form-control" required placeholder="03001234567">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Email (Optional)</label>
                    <input type="email" name="email" class="form-control" placeholder="john@example.com">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="••••••••">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold small">Assign Role</label>
                    <select name="role" id="add_user_role_select" class="form-select" required>
                        <option value="patient" selected>Patient</option>
                        <option value="doctor">Doctor</option>
                        <option value="receptionist">Receptionist</option>
                        <option value="hospital_admin">Hospital / Clinic Admin</option>
                        <option value="admin">Super Admin</option>
                    </select>
                </div>
                <div class="mb-3" id="add_user_hospital_group" style="display: none;">
                    <label class="form-label fw-bold small">Link to Hospital / Clinic</label>
                    <select name="hospital_id" class="form-select">
                        <option value="">-- Select Hospital/Clinic --</option>
                        <?php if(isset($hospitals)): ?>
                            <?php foreach($hospitals as $h): ?>
                                <option value="<?= $h['id'] ?>"><?= htmlspecialchars($h['name']) ?> (<?= htmlspecialchars($h['type']) ?>)</option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success w-100 rounded-pill"><i class="fas fa-plus me-2"></i>Create User</button>
            </form>
        </div>
    </div>

    <!-- RIGHT COLUMN: USER MANAGEMENT -->
    <div class="col-lg-7">
        <div class="settings-card">
            <div class="d-flex align-items-start gap-3 mb-3">
                <div class="icon-box blue"><i class="fas fa-users-cog"></i></div>
                <div>
                    <h5>Manage Users & Roles</h5>
                    <p class="card-desc">Assign roles or reset passwords for existing users.</p>
                </div>
            </div>

            <!-- Search Users -->
            <div class="search-input-wrap mb-3">
                <i class="fas fa-search"></i>
                <input type="text" id="userSearch" placeholder="Search by name, phone, role..." class="form-control" onkeyup="filterUsers()">
            </div>

            <!-- Users List -->
            <div style="max-height: 520px; overflow-y: auto; padding-right:5px;" id="usersListContainer">
                <?php if(isset($users) && count($users) > 0): ?>
                    <?php foreach($users as $u): ?>
                        <div class="user-row" data-search="<?= strtolower($u['name'] . ' ' . ($u['phone'] ?? '') . ' ' . $u['role'] . ' ' . ($u['hospital_name'] ?? '')) ?>">
                            <div class="avatar"><?= strtoupper(substr($u['name'], 0, 1)) ?></div>
                            <div class="info">
                                <div class="name"><?= htmlspecialchars($u['name']) ?></div>
                                <div class="meta">
                                    <?= htmlspecialchars($u['phone'] ?? 'No phone') ?> · <?= htmlspecialchars($u['email'] ?? 'No email') ?>
                                    <?php if($u['role'] === 'hospital_admin' && !empty($u['hospital_name'])): ?>
                                        <br><span class="text-primary fw-bold"><i class="fas fa-hospital me-1"></i>Admin of: <?= htmlspecialchars($u['hospital_name']) ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="role-badge <?= $u['role'] ?>"><?= str_replace('_', ' ', $u['role']) ?></span>
                            
                            <div class="d-flex gap-2 ms-2">
                                <button class="btn btn-sm btn-outline-info rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#changeRoleModal"
                                    onclick="setRoleUser(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['name'])) ?>', '<?= $u['role'] ?>', '<?= $u['hospital_id'] ?? '' ?>')">
                                    <i class="fas fa-user-shield"></i> Role
                                </button>
                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#resetPasswordModal"
                                    onclick="setResetUser(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['name'])) ?>', '<?= $u['role'] ?>')">
                                    <i class="fas fa-key"></i> Key
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="text-center text-muted py-4"><i class="fas fa-users fa-2x mb-2 d-block opacity-50"></i>No users found.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Change Role Modal -->
<div class="modal fade" id="changeRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 20px;">
                <h5 class="modal-title" style="font-weight: 700;"><i class="fas fa-user-shield me-2 text-info"></i>Change User Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="?route=admin/update_user_role" method="POST">
                <div class="modal-body" style="padding: 30px;">
                    <input type="hidden" name="user_id" id="role_user_id">
                    <div class="mb-3 p-3 rounded-3" style="background:#f0f4ff;">
                        <div class="fw-bold" id="role_user_name">-</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Select New Role</label>
                        <select name="role" id="role_select" class="form-select" required>
                            <option value="patient">Patient</option>
                            <option value="doctor">Doctor</option>
                            <option value="receptionist">Receptionist</option>
                            <option value="hospital_admin">Hospital Admin</option>
                            <option value="admin">Super Admin</option>
                        </select>
                    </div>
                    <div class="mb-3" id="change_role_hospital_group" style="display: none;">
                        <label class="form-label fw-bold">Link to Hospital / Clinic</label>
                        <select name="hospital_id" id="change_role_hospital_select" class="form-select">
                            <option value="">-- Select Hospital/Clinic --</option>
                            <?php if(isset($hospitals)): ?>
                                <?php foreach($hospitals as $h): ?>
                                    <option value="<?= $h['id'] ?>"><?= htmlspecialchars($h['name']) ?> (<?= htmlspecialchars($h['type']) ?>)</option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: none; padding: 0 30px 30px;">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-info text-white rounded-pill px-4">
                        <i class="fas fa-save me-1"></i>Update Role
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 20px;">
                <h5 class="modal-title" style="font-weight: 700;"><i class="fas fa-key me-2 text-primary"></i>Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="?route=admin/reset_password" method="POST">
                <div class="modal-body" style="padding: 30px;">
                    <input type="hidden" name="user_id" id="reset_user_id">
                    <div class="mb-3 p-3 rounded-3" style="background:#f0f4ff;">
                        <div class="fw-bold" id="reset_user_name">-</div>
                        <div class="small text-muted" id="reset_user_role">-</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">New Password</label>
                        <div class="input-group">
                            <input type="text" name="new_password" id="new_password_field" class="form-control" required minlength="4" placeholder="Enter new password" style="border-radius: 10px 0 0 10px;">
                            <button type="button" class="btn btn-outline-secondary" onclick="generatePassword()" style="border-radius: 0 10px 10px 0;" title="Generate random password">
                                <i class="fas fa-random"></i>
                            </button>
                        </div>
                        <div class="form-text">Min 4 characters. Click <i class="fas fa-random"></i> to auto-generate.</div>
                    </div>
                </div>
                <div class="modal-footer" style="border-top: none; padding: 0 30px 30px;">
                    <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4" onclick="return confirm('Are you sure you want to reset this user\'s password?')">
                        <i class="fas fa-save me-1"></i>Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function setResetUser(id, name, role) {
    document.getElementById('reset_user_id').value = id;
    document.getElementById('reset_user_name').textContent = name;
    document.getElementById('reset_user_role').textContent = role.replace('_', ' ').toUpperCase();
}

function setRoleUser(id, name, currentRole, hospitalId) {
    document.getElementById('role_user_id').value = id;
    document.getElementById('role_user_name').textContent = name;
    document.getElementById('role_select').value = currentRole;
    
    const group = document.getElementById('change_role_hospital_group');
    const select = document.getElementById('change_role_hospital_select');
    select.value = hospitalId || '';
    
    if (currentRole === 'hospital_admin') {
        group.style.display = 'block';
    } else {
        group.style.display = 'none';
    }
}

// Add event listeners for dynamic role changes
document.addEventListener('DOMContentLoaded', function() {
    const addRoleSelect = document.getElementById('add_user_role_select');
    if (addRoleSelect) {
        addRoleSelect.addEventListener('change', function() {
            const group = document.getElementById('add_user_hospital_group');
            if (this.value === 'hospital_admin') {
                group.style.display = 'block';
            } else {
                group.style.display = 'none';
                group.querySelector('select').value = '';
            }
        });
    }

    const changeRoleSelect = document.getElementById('role_select');
    if (changeRoleSelect) {
        changeRoleSelect.addEventListener('change', function() {
            const group = document.getElementById('change_role_hospital_group');
            if (this.value === 'hospital_admin') {
                group.style.display = 'block';
            } else {
                group.style.display = 'none';
                document.getElementById('change_role_hospital_select').value = '';
            }
        });
    }
});

function generatePassword() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789!@#$';
    let pass = '';
    for (let i = 0; i < 12; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('new_password_field').value = pass;
}

function filterUsers() {
    const query = document.getElementById('userSearch').value.toLowerCase();
    document.querySelectorAll('.user-row').forEach(row => {
        const searchData = row.getAttribute('data-search');
        row.style.display = searchData.includes(query) ? '' : 'none';
    });
}
</script>

<?php include 'views/layouts/footer.php'; ?>
