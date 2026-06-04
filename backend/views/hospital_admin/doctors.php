<?php include 'views/layouts/header.php'; ?>

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

<!-- Top Bar -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h3>My Doctors</h3>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDoctorModal">
        <i class="fas fa-plus"></i> Add Doctor
    </button>
</div>

<!-- Doctors Table -->
<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Name</th>
                <th>Department</th>
                <th>Specialization</th>
                <th>Email</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($doctors) && count($doctors) > 0): ?>
                <?php foreach($doctors as $d): ?>
                <tr>
                    <td style="font-weight:600;"><?= htmlspecialchars($d['name']) ?></td>
                    <td><span class="badge bg-soft-info text-info"><?= htmlspecialchars($d['department_name'] ?? 'N/A') ?></span></td>
                    <td><span class="badge bg-light text-dark"><?= htmlspecialchars($d['specialization_name'] ?? 'N/A') ?></span></td>
                    <td><?= htmlspecialchars($d['email']) ?></td>
                    <td>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" 
                                   onchange="toggleDoctorStatus(<?= $d['doctor_user_id'] ?>, this.checked)" 
                                   <?= ($d['is_active'] ?? 1) ? 'checked' : '' ?>>
                            <label class="form-check-label"><?= ($d['is_active'] ?? 1) ? 'Active' : 'Inactive' ?></label>
                        </div>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-warning text-white"
                            onclick='editDoctor(<?= json_encode($d) ?>)'
                            title="Edit"><i class="fas fa-edit"></i></button>
                        <a href="?route=hospital_admin/delete_doctor&id=<?= $d['doctor_user_id'] ?>"
                           class="btn btn-sm btn-danger"
                           onclick="return confirm('Remove this doctor from your hospital?')"
                           title="Delete"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center p-4">No doctors found. Add one to get started.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- ADD DOCTOR MODAL -->
<div class="modal fade" id="addDoctorModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="border-radius:20px;border:none;">
      <div class="modal-header" style="border-bottom:1px solid #f0f0f0;padding:20px;">
        <h5 class="modal-title fw-bold">Add New Doctor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="?route=hospital_admin/store_doctor" method="POST">
      <div class="modal-body" style="padding:30px;">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required placeholder="Dr. Name">
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required placeholder="email@hospital.com">
            </div>
            <div class="col-md-6">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required placeholder="Min 6 characters">
            </div>
            
            <!-- Dynamic Departments -->
            <div class="col-md-6">
                <label class="form-label">Department</label>
                <select name="department_id" class="form-control" required>
                    <option value="">Select Department</option>
                    <?php if(!empty($departments)): ?>
                        <?php foreach($departments as $dept): ?>
                            <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php if(empty($departments)): ?>
                    <small class="text-danger">Add <a href="?route=hospital_admin/departments">Departments</a> first.</small>
                <?php endif; ?>
            </div>

            <!-- Dynamic Specializations -->
            <div class="col-md-6">
                <label class="form-label">Specialization</label>
                <select name="specialization_id" class="form-control" required>
                    <option value="">Select Specialization</option>
                    <?php if(!empty($specializations)): ?>
                        <?php foreach($specializations as $spec): ?>
                            <option value="<?= $spec['id'] ?>"><?= htmlspecialchars($spec['name']) ?></option>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </select>
                <?php if(empty($specializations)): ?>
                    <small class="text-danger">Add <a href="?route=hospital_admin/specializations">Specializations</a> first.</small>
                <?php endif; ?>
            </div>

            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control">
            </div>
            <div class="col-md-12">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control">
            </div>
            <div class="col-md-12">
                <label class="form-label">Biography</label>
                <textarea name="biography" class="form-control" rows="2"></textarea>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Add Doctor</button>
      </div>
      </form>
    </div>
  </div>
</div>

<!-- EDIT DOCTOR MODAL -->
<div class="modal fade" id="editDoctorModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="border-radius:20px;border:none;">
      <div class="modal-header" style="border-bottom:1px solid #f0f0f0;padding:20px;">
        <h5 class="modal-title fw-bold">Edit Doctor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="?route=hospital_admin/update_doctor" method="POST">
      <input type="hidden" name="user_id" id="edit_user_id">
      <div class="modal-body" style="padding:30px;">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" id="edit_name" class="form-control" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" id="edit_email" class="form-control" required>
            </div>

            <!-- Dynamic Departments (Edit) -->
            <div class="col-md-6">
                <label class="form-label">Department</label>
                <select name="department_id" id="edit_department_id" class="form-control" required>
                    <?php foreach($departments as $dept): ?>
                        <option value="<?= $dept['id'] ?>"><?= htmlspecialchars($dept['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Dynamic Specializations (Edit) -->
            <div class="col-md-6">
                <label class="form-label">Specialization</label>
                <select name="specialization_id" id="edit_specialization_id" class="form-control" required>
                    <?php foreach($specializations as $spec): ?>
                        <option value="<?= $spec['id'] ?>"><?= htmlspecialchars($spec['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" id="edit_phone" class="form-control">
            </div>
            <div class="col-md-12">
                <label class="form-label">Address</label>
                <input type="text" name="address" id="edit_address" class="form-control">
            </div>
            <div class="col-md-12">
                <label class="form-label">Biography</label>
                <textarea name="biography" id="edit_biography" class="form-control" rows="2"></textarea>
            </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-warning text-white">Update Doctor</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script>
function toggleDoctorStatus(id, is_active) {
    const status = is_active ? 1 : 0;
    window.location.href = `?route=hospital_admin/toggle_doctor_status&id=${id}&status=${status}`;
}

function editDoctor(d) {
    document.getElementById('edit_user_id').value = d.doctor_user_id;
    document.getElementById('edit_name').value = d.name;
    document.getElementById('edit_email').value = d.email;
    document.getElementById('edit_phone').value = d.phone || '';
    document.getElementById('edit_address').value = d.address || '';
    document.getElementById('edit_biography').value = d.biography || '';
    
    // Set active options for dynamic selects
    document.getElementById('edit_department_id').value = d.department_id || '';
    document.getElementById('edit_specialization_id').value = d.specialization_id || '';
    
    new bootstrap.Modal(document.getElementById('editDoctorModal')).show();
}
</script>

<?php include 'views/layouts/footer.php'; ?>
