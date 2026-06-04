<?php include 'views/layouts/header.php'; ?>

<div class="table-container" style="max-width: 800px; margin: 0 auto;">
    <h3 class="mb-4">Add New Doctor to Hospital</h3>
    
    <form action="?route=hospital_admin/store_doctor" method="POST">
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required placeholder="Dr. Name">
            </div>
            <div class="col-md-6">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" required placeholder="email@example.com">
            </div>
            <div class="col-md-6">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required placeholder="******">
            </div>
            <?php if(isset($_SESSION['error'])): ?>
            <div class="col-12">
                <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
            </div>
            <?php endif; ?>
            
            <div class="col-md-6">
                <label class="form-label">Specialization</label>
                <select name="specialization_id" class="form-control" required>
                    <option value="">Select Specialization</option>
                    <?php if(!empty($specializations)): ?>
                        <?php foreach($specializations as $spec): ?>
                            <option value="<?= $spec['id'] ?>"><?= htmlspecialchars($spec['name']) ?></option>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <option value="" disabled>No Specializations Found</option>
                    <?php endif; ?>
                </select>
                <?php if(empty($specializations)): ?>
                    <small class="text-danger">Please add specializations in <a href="?route=hospital_admin/specializations">Settings</a> first.</small>
                <?php endif; ?>
            </div>
            <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" placeholder="Phone Number">
            </div>
            <div class="col-md-12">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" placeholder="Address">
            </div>
             <div class="col-md-12">
                <label class="form-label">Biography</label>
                <textarea name="biography" class="form-control" rows="3"></textarea>
            </div>
        </div>
        <div class="mt-4 text-end">
            <button type="submit" class="btn btn-primary">Save Doctor</button>
        </div>
    </form>
</div>

<?php include 'views/layouts/footer.php'; ?>
