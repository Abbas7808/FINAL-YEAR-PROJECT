<?php include 'views/layouts/header.php'; ?>

<!-- Top Actions -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div style="position: relative;">
        <input type="text" placeholder="Search here" class="form-control" style="width: 300px; padding-left: 40px; background: white;">
        <i class="fas fa-search" style="position: absolute; left: 15px; top: 15px; color: #ccc;"></i>
    </div>
    <div style="display: flex; gap: 15px;">
    <div style="display: flex; gap: 15px;">
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#addPatientModal" style="background: #ff4757; color: white; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-plus"></i> New Patient
        </button>
        <button class="btn" style="background: white; border: 1px solid #ddd; color: #666; display: flex; align-items: center; gap: 10px;">
            <i class="far fa-calendar-alt"></i> Filter Period <i class="fas fa-chevron-down"></i>
        </button>
    </div>
    </div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th><input type="checkbox"> Patient ID</th>
                <th>Date Check In</th>
                <th>Patient Name</th>
                <th>Doctor Assigned</th>
                <th>Disease</th>
                <th>Status</th>
                <th>Room No</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($patients as $p): ?>
            <tr>
                <td><input type="checkbox"> <span style="font-weight: 600; color: #666;">#P-<?= str_pad($p['id'], 5, '0', STR_PAD_LEFT) ?></span></td>
                <td style="color: #888;"><?= date('d/m/Y, h:i A', strtotime($p['created_at'])) ?></td>
                <td style="font-weight: 600; color: #333;"><?= $p['name'] ?></td>
                <td style="color: #666;">Dr. Samantha</td>
                <td style="color: #666;">Sleep Problem</td>
                <td><span class="badge in-treatment" style="background: #ffe0e6; color: #ff4757;">New Patient</span></td>
                <td style="color: #666;">AB-004</td>
                <td>
                <td style="position: relative;">
                    <button class="action-btn" onclick="toggleDropdown(this)"><i class="fas fa-ellipsis-h"></i></button>
                    <div class="action-dropdown">
                        <a href="#" class="dropdown-item success"><i class="far fa-check-circle"></i> Accept Patient</a>
                        <a href="#" class="dropdown-item danger"><i class="far fa-times-circle"></i> Reject Order</a>
                        <a href="?route=admin/patient_details&id=<?= $p['id'] ?>" class="dropdown-item info"><i class="fas fa-info-circle"></i> View Details</a>
                    </div>
                </td>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <!-- Pagination Logic (Simple) -->
    <?php if(count($patients) > 0): ?>
    <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 20px; color: #888; font-size: 14px;">
        <div>Showing <?= count($patients) ?> data</div>
        <div style="display: flex; gap: 10px;">
            <button class="btn" style="padding: 5px 15px; background: white; border: 1px solid #eee;"><i class="fas fa-chevron-left"></i></button>
            <button class="btn" style="padding: 5px 15px; background: var(--primary); color: white;">1</button>
            <button class="btn" style="padding: 5px 15px; background: white; border: 1px solid #eee;"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>
    <?php else: ?>
        <div style="text-align: center; padding: 50px; color: #ccc;">No patients found.</div>
    <?php endif; ?>
</div>

<!-- Add Patient Modal -->
<div class="modal fade" id="addPatientModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="border-radius: 20px; border: none;">
      <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 20px;">
        <h5 class="modal-title" style="font-weight: 700;">Add New Patient</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="?route=admin/store_patient" method="POST">
      <div class="modal-body" style="padding: 30px;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="John Doe">
                </div>
                <div class="form-group">
                    <label class="form-label">Phone Number</label>
                    <input type="number" name="phone" class="form-control" placeholder="1234567890">
                </div>
                <div class="form-group">
                    <label class="form-label">Age</label>
                    <input type="number" name="age" class="form-control" required placeholder="25">
                </div>
                <div class="form-group">
                    <label class="form-label">Gender</label>
                    <select name="gender" class="form-control">
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" placeholder="Full Address">
                </div>
                
                <div class="form-group" style="grid-column: span 2;">
                    <label class="form-label">Assign Doctor</label>
                    <select name="doctor_id" class="form-control" required>
                        <option value="">Select Doctor</option>
                        <?php 
                        // We need doctors available here. Controller should pass it.
                        // Assuming $doctors_list is passed from controller, or we fetch if not.
                        // For now, if $doctors variable isn't set (it was only in add_patient), we might need to adjust controller.
                        if(isset($doctors_list)): 
                            foreach($doctors_list as $doc): ?>
                            <option value="<?= $doc['user_id'] ?>">Dr. <?= $doc['name'] ?> (<?= $doc['specialization'] ?>)</option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
            </div>
      </div>
      <div class="modal-footer" style="border-top: none; padding: 0 30px 30px;">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Patient</button>
      </div>
      </form>
    </div>
  </div>
</div>

<!-- Bootstrap Bundle (includes Popper) for Modals if not already included in header -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function toggleDropdown(btn) {
    // Close others
    document.querySelectorAll('.action-dropdown').forEach(d => {
        if (d !== btn.nextElementSibling) d.classList.remove('show');
    });
    // Toggle current
    btn.nextElementSibling.classList.toggle('show');
}

// Close on click outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('td')) {
        document.querySelectorAll('.action-dropdown').forEach(d => d.classList.remove('show'));
    }
});
</script>
<?php include 'views/layouts/footer.php'; ?>
