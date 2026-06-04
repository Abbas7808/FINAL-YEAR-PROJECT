<?php include 'views/layouts/header.php'; ?>

<div class="container-fluid">
    <h3 class="mb-4">Settings & Staff</h3>

    <?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['success'] ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['success']); endif; ?>
    
    <?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['error'] ?> <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php unset($_SESSION['error']); endif; ?>

    <div class="row g-4">
        <!-- Hospital Settings -->
        <div class="col-md-5">
            <div class="card border-0 shadow-sm" style="border-radius:15px;">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Hospital Details</h5>
                </div>
                <div class="card-body">
                    <form action="?route=hospital_admin/update_settings" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Hospital Name</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($hospital['name'] ?? '') ?>" readonly disabled>
                            <small class="text-muted">Contact Super Admin to change name</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" rows="2" readonly disabled><?= htmlspecialchars($hospital['address'] ?? '') ?></textarea>
                        </div>
                         <div class="mb-3">
                            <label class="form-label">Type</label>
                            <input type="text" class="form-control" value="<?= htmlspecialchars($hospital['type'] ?? '') ?>" readonly disabled>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notifications (Email)</label>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="email_notifications" name="email_notifications" value="1" <?= (!empty($email_notifications) && $email_notifications==1) ? 'checked' : '' ?>>
                                <label class="form-check-label" for="email_notifications">Send email notifications to hospital admin</label>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary btn-sm">Save Settings</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Staff Management -->
        <div class="col-md-7">
            <div class="card border-0 shadow-sm" style="border-radius:15px;">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Staff Management (Receptionists)</h5>
                    <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
                        <i class="fas fa-plus"></i> Add Staff
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                         <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Name</th>
                                    <th>Email</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                             <tbody>
                                <!-- We need to pass staff data here from controller -->
                                <!-- Since we didn't implement fully fetching staff in `settings()` in controller yet, 
                                     I'll add the fetch logic instructions to update controller next. -->
                                <!-- For now assume $staff variable exists -->
                                <?php if(isset($staff) && count($staff) > 0): ?>
                                    <?php foreach($staff as $s): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= htmlspecialchars($s['name']) ?></td>
                                        <td><?= htmlspecialchars($s['email']) ?></td>
                                        <td class="text-end pe-4">
                                            <!-- Delete Staff -->
                                            <a href="#" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center py-4">No staff found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius:15px;">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">Add Receptionist</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="?route=hospital_admin/store_staff" method="POST">
      <div class="modal-body">
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Add Staff</button>
      </div>
      </form>
    </div>
  </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
