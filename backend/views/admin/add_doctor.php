<?php include 'views/layouts/header.php'; ?>

<div class="table-container" style="max-width: 900px; margin: 0 auto;">
    <div class="table-header">
        <h3>Add New Doctor</h3>
    </div>
    
    <form action="?route=admin/store_doctor" method="POST">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required placeholder="Dr. John Doe">
            </div>
            <div class="form-group">
                <label class="form-label">Email (Login Username)</label>
                <input type="email" name="email" class="form-control" required placeholder="doctor@example.com">
            </div>
            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="tel" name="phone" class="form-control" required placeholder="+1234567890">
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required placeholder="******">
            </div>
            <div class="form-group">
                <label class="form-label">Specialization</label>
                <select name="specialization" class="form-control">
                    <option value="General Physician">General Physician</option>
                    <option value="Dentist">Dentist</option>
                    <option value="Cardiologist">Cardiologist</option>
                    <option value="Gynecologist">Gynecologist</option>
                    <option value="Neurologist">Neurologist</option>
                    <option value="Orthopedic">Orthopedic</option>
                    <option value="Dietitian">Dietitian/Nutritionist</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">Assign Hospital</label>
                <select name="hospital_id" class="form-control" required>
                    <option value="">Select Hospital</option>
                    <?php if(isset($hospitals)): foreach($hospitals as $h): ?>
                        <option value="<?= $h['id'] ?>"><?= $h['name'] ?></option>
                    <?php endforeach; endif; ?>
                </select>
            </div>
             <div class="form-group">
                <label class="form-label">Phone Details</label>
                <input type="text" name="phone_details" class="form-control" placeholder="+1 234 567 890">
            </div>
             <div class="form-group">
                <label class="form-label">Initial Rating</label>
                <input type="number" step="0.1" max="5" name="rating" class="form-control" value="5.0">
            </div>
            
            <div class="form-group" style="grid-column: span 2;">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" placeholder="123 Medical Plaza">
            </div>

            <div class="form-group" style="grid-column: span 2;">
                <label class="form-label">Short Biography</label>
                <textarea name="biography" class="form-control" rows="4" placeholder="Brief description of the doctor's experience..."></textarea>
            </div>
        </div>

        <div style="margin-top: 20px; text-align: right;">
            <button type="submit" class="btn btn-primary">Add Doctor</button>
        </div>
    </form>
</div>

<?php include 'views/layouts/footer.php'; ?>
