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
    .settings-card .icon-box.green { background: #dcfce7; color: #16a34a; }
    .settings-card .icon-box.amber { background: #fef3c7; color: #d97706; }
    .settings-card .icon-box.red { background: #fee2e2; color: #dc2626; }
    .settings-card .icon-box.purple { background: #ede9fe; color: #7c3aed; }

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
    .search-input-wrap {
        position: relative;
    }
    .search-input-wrap i {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #9ca3af;
    }
    .search-input-wrap input {
        padding-left: 40px;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        height: 42px;
        width: 100%;
    }
</style>

<!-- Page Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div>
        <h4 style="font-weight: 700; margin: 0; color: #1a1a2e;">⚙️ Settings</h4>
        <p style="color: #6b7280; font-size: 0.9rem; margin: 4px 0 0;">Manage backups, data import/export, and user passwords</p>
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
    <!-- LEFT COLUMN -->
    <div class="col-lg-7">

        <!-- EXPORT / BACKUP -->
        <div class="settings-card">
            <div class="d-flex align-items-start gap-3 mb-3">
                <div class="icon-box blue"><i class="fas fa-download"></i></div>
                <div>
                    <h5>Export Data / Backup</h5>
                    <p class="card-desc">Download a full JSON backup of all your platform data. Use this to restore later or migrate to a new server.</p>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="?route=admin/export_data&type=full" class="btn btn-primary rounded-pill px-4">
                    <i class="fas fa-database me-2"></i>Full Backup (JSON)
                </a>
                <a href="?route=admin/export_data&type=hospitals" class="btn btn-outline-primary rounded-pill px-4">
                    <i class="fas fa-hospital me-2"></i>Hospitals Only
                </a>
                <a href="?route=admin/export_data&type=doctors" class="btn btn-outline-primary rounded-pill px-4">
                    <i class="fas fa-user-md me-2"></i>Doctors Only
                </a>
                <a href="?route=admin/export_data&type=users" class="btn btn-outline-primary rounded-pill px-4">
                    <i class="fas fa-users me-2"></i>Users Only
                </a>
            </div>
        </div>

        <!-- IMPORT DATA -->
        <div class="settings-card">
            <div class="d-flex align-items-start gap-3 mb-3">
                <div class="icon-box green"><i class="fas fa-upload"></i></div>
                <div>
                    <h5>Import Data</h5>
                    <p class="card-desc">Restore data from a previously exported JSON backup file. This will merge with your existing data.</p>
                </div>
            </div>
            <form action="?route=admin/import_data" method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size:0.88rem;">Select Backup File (JSON)</label>
                    <input type="file" name="backup_file" class="form-control" accept=".json" required style="border-radius:10px;">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold" style="font-size:0.88rem;">What to Import</label>
                    <div class="d-flex flex-wrap gap-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="import_regions" id="import_regions" checked>
                            <label class="form-check-label" for="import_regions">Regions</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="import_hospitals" id="import_hospitals" checked>
                            <label class="form-check-label" for="import_hospitals">Hospitals</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="import_doctors" id="import_doctors" checked>
                            <label class="form-check-label" for="import_doctors">Doctors</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="import_users" id="import_users">
                            <label class="form-check-label" for="import_users">Users</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="import_appointments" id="import_appointments">
                            <label class="form-check-label" for="import_appointments">Appointments</label>
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn btn-success rounded-pill px-4" onclick="return confirm('Are you sure? This will merge imported data with existing records.')">
                    <i class="fas fa-file-import me-2"></i>Import Backup
                </button>
            </form>
        </div>

        <!-- DANGER ZONE -->
        <div class="settings-card" style="border-color: #fecaca;">
            <div class="d-flex align-items-start gap-3 mb-3">
                <div class="icon-box red"><i class="fas fa-exclamation-triangle"></i></div>
                <div>
                    <h5 style="color:#dc2626;">Danger Zone</h5>
                    <p class="card-desc">These actions are irreversible. Please export a backup before proceeding.</p>
                </div>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="?route=admin/reset_all_data" class="btn btn-outline-danger rounded-pill px-4" onclick="return confirm('⚠️ WARNING: This will DELETE ALL DATA except admin accounts. Are you absolutely sure?')">
                    <i class="fas fa-skull-crossbones me-2"></i>Reset All Data
                </a>
            </div>
        </div>
    </div>


        <!-- Quick Stats -->
        <div class="settings-card">
            <div class="d-flex align-items-start gap-3 mb-3">
                <div class="icon-box amber"><i class="fas fa-chart-pie"></i></div>
                <div>
                    <h5>Database Summary</h5>
                    <p class="card-desc">Quick overview of all records in the system.</p>
                </div>
            </div>
            <div class="row g-2">
                <?php 
                $stats_items = [
                    ['label' => 'Users', 'count' => $stats['users'] ?? 0, 'icon' => 'fa-users', 'color' => '#2563eb'],
                    ['label' => 'Hospitals', 'count' => $stats['hospitals'] ?? 0, 'icon' => 'fa-hospital', 'color' => '#16a34a'],
                    ['label' => 'Doctors', 'count' => $stats['doctors'] ?? 0, 'icon' => 'fa-user-md', 'color' => '#7c3aed'],
                    ['label' => 'Appointments', 'count' => $stats['appointments'] ?? 0, 'icon' => 'fa-calendar-check', 'color' => '#d97706'],
                    ['label' => 'Regions', 'count' => $stats['regions'] ?? 0, 'icon' => 'fa-map', 'color' => '#0891b2'],
                    ['label' => 'Diet Plans', 'count' => $stats['diet_plans'] ?? 0, 'icon' => 'fa-carrot', 'color' => '#059669'],
                ];
                foreach($stats_items as $si): ?>
                <div class="col-6">
                    <div style="background:#f8fafc; border-radius:10px; padding:12px 16px; display:flex; align-items:center; gap:10px;">
                        <i class="fas <?= $si['icon'] ?>" style="color:<?= $si['color'] ?>; font-size:1.1rem;"></i>
                        <div>
                            <div style="font-size:1.1rem; font-weight:700; color:#1a1a2e;"><?= $si['count'] ?></div>
                            <div style="font-size:0.75rem; color:#6b7280;"><?= $si['label'] ?></div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<?php include 'views/layouts/footer.php'; ?>
