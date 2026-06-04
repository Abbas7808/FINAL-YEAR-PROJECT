<?php include 'views/layouts/header.php'; ?>

<div class="table-container" style="max-width: 600px; margin: 0 auto;">
    <div class="table-header">
        <h3>Add New Receptionist</h3>
    </div>
    
    <form action="?route=admin/store_receptionist" method="POST">
        <div style="display: grid; gap: 20px;">
            <div class="form-group">
                <label class="form-label">Full Name</label>
                <input type="text" name="name" class="form-control" required placeholder="Alice">
            </div>
            <div class="form-group">
                <label class="form-label">Phone Number</label>
                <input type="tel" name="phone" class="form-control" required placeholder="+1234567890">
            </div>
            <div class="form-group">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required placeholder="******">
            </div>
        </div>

        <div style="margin-top: 20px; text-align: right;">
            <button type="submit" class="btn btn-primary">Add Receptionist</button>
        </div>
    </form>
</div>

<?php include 'views/layouts/footer.php'; ?>
