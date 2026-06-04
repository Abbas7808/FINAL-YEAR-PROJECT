<?php include 'views/layouts/header.php'; ?>

<div class="table-container" style="max-width: 800px; margin: 0 auto;">
    <div class="table-header">
        <h3>Add New Patient</h3>
    </div>
    
    <form action="?route=receptionist/store_patient" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required placeholder="John Doe">
            </div>
            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="text" name="phone" class="form-control" placeholder="+1 234 567 890">
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
                <label class="form-label">Assign Doctor</label>
                <select name="doctor_id" class="form-control" required>
                    <option value="">Select Doctor</option>
                    <?php foreach($doctors as $doc): ?>
                        <option value="<?= $doc['id'] ?>">Dr. <?= $doc['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="margin-top: 20px; text-align: right;">
            <button type="submit" class="btn btn-primary">Generate Token</button>
        </div>
    </form>
</div>

<?php include 'views/layouts/footer.php'; ?>
