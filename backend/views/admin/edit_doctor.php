<?php include 'views/layouts/header.php'; ?>

<div class="table-container" style="max-width: 900px; margin: 0 auto;">
    <div class="table-header">
        <h3>Edit Doctor Profile</h3>
    </div>
    
    <form action="?route=admin/update_doctor" method="POST">
        <input type="hidden" name="id" value="<?= $doctor['id'] ?>">
        <input type="hidden" name="user_id" value="<?= $doctor['user_id'] ?>">
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($doctor['user_name']) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Email (Login Username)</label>
                <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($doctor['email'] ?? '') ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Auth Phone Number</label>
                <input type="tel" name="phone" class="form-control" required value="<?= htmlspecialchars($doctor['auth_phone']) ?>">
            </div>
            <div class="form-group">
                <label class="form-label">Change Password (leave empty to keep current)</label>
                <input type="password" name="password" class="form-control" placeholder="******">
            </div>
            <div class="form-group">
                <label class="form-label">Specialization</label>
                <select name="specialization" class="form-control">
                    <?php 
                    $specs = ['General Physician', 'Dentist', 'Cardiologist', 'Gynecologist', 'Neurologist', 'Orthopedic', 'Dietitian/Nutritionist'];
                    foreach($specs as $spec) {
                        $sel = ($doctor['specialization'] == $spec) ? 'selected' : '';
                        echo "<option value=\"$spec\" $sel>$spec</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Assign / Transfer Hospital</label>
                <select name="hospital_id" class="form-control" required>
                    <option value="">Select Hospital</option>
                    <?php if(isset($hospitals)): foreach($hospitals as $h): ?>
                        <option value="<?= $h['id'] ?>" <?= ($doctor['hospital_id'] == $h['id']) ? 'selected' : '' ?>><?= $h['name'] ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
             <div class="form-group">
                <label class="form-label">Contact Phone Details</label>
                <input type="text" name="phone_details" class="form-control" value="<?= htmlspecialchars($doctor['phone']) ?>">
            </div>
             <div class="form-group">
                <label class="form-label">Rating</label>
                <input type="number" step="0.1" max="5" name="rating" class="form-control" value="<?= htmlspecialchars($doctor['rating']) ?>">
            </div>
            
            <div class="form-group" style="grid-column: span 2;">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" value="<?= htmlspecialchars($doctor['address'] ?? '') ?>">
            </div>

            <div class="form-group" style="grid-column: span 2;">
                <label class="form-label">Biography</label>
                <textarea name="biography" class="form-control" rows="4"><?= htmlspecialchars($doctor['biography'] ?? '') ?></textarea>
            </div>
        </div>

        <div style="margin-top: 20px; text-align: right;">
            <a href="?route=admin/doctors" class="btn btn-secondary">Cancel</a>
            <button type="submit" class="btn btn-primary">Update Doctor</button>
        </div>
    </form>
</div>

<?php include 'views/layouts/footer.php'; ?>
