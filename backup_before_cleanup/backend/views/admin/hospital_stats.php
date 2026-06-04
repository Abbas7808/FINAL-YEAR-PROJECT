<?php include 'views/layouts/header.php'; ?>

<div class="mb-4">
    <a href="?route=admin/hospitals" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back to Hospitals</a>
</div>

<div class="top-bar mb-4">
    <h2>Stats for: <?= $hospital['name'] ?></h2>
    <p class="text-muted"><?= $hospital['address'] ?></p>
</div>

<div class="stats-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
    <div class="stat-card colored blue" style="padding: 20px; border-radius: 12px; display: flex; align-items: center; gap: 20px;">
        <div class="icon-box" style="font-size: 24px;"><i class="fas fa-user-md"></i></div>
        <div>
            <p style="margin: 0; opacity: 0.8;">Total Doctors</p>
            <h3 style="margin: 0; font-size: 28px;"><?= $total_doctors ?></h3>
        </div>
    </div>
    <div class="stat-card colored green" style="padding: 20px; border-radius: 12px; display: flex; align-items: center; gap: 20px;">
        <div class="icon-box" style="font-size: 24px;"><i class="fas fa-calendar-check"></i></div>
        <div>
            <p style="margin: 0; opacity: 0.8;">Total Appointments</p>
            <h3 style="margin: 0; font-size: 28px;"><?= $total_appointments ?></h3>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
