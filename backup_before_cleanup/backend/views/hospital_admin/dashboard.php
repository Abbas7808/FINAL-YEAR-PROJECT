<?php include 'views/layouts/header.php'; ?>

<?php if(isset($db_error)): ?>
<div class="alert alert-warning py-2 px-3 rounded-3 mb-4" style="font-size:13px;">
    ⚠️ <?= $db_error ?>
</div>
<?php endif; ?>

<div class="mb-4">
    <h4 class="fw-bold mb-1">Good <?= (date('H') < 12) ? 'Morning' : ((date('H') < 18) ? 'Afternoon' : 'Evening') ?>, <?= htmlspecialchars(explode(' ', $_SESSION['name'] ?? 'Admin')[0]) ?> 👋</h4>
    <p class="text-muted mb-0" style="font-size:14px;">Here's what's happening at your facility today.</p>
</div>

<!-- ====== STAT CARDS ====== -->
<div class="dash-grid">

    <!-- Appointments — BLUE -->
    <a href="?route=hospital_admin/appointments" class="dash-card" style="--card-color:#2563eb; --card-light:#eff6ff;">
        <div class="dash-card-icon">
            <i class="fas fa-calendar-check"></i>
        </div>
        <div class="dash-card-body">
            <div class="dash-card-label">Appointments Today</div>
            <div class="dash-card-value"><?= $appointments_today ?? 0 ?></div>
            <div class="dash-card-sub">Pending: <?= $pending_appointments ?? 0 ?></div>
        </div>
        <div class="dash-card-arrow"><i class="fas fa-arrow-right"></i></div>
    </a>

    <!-- Diet Plans — GREEN -->
    <a href="?route=hospital_admin/diet_plans" class="dash-card" style="--card-color:#16a34a; --card-light:#f0fdf4;">
        <div class="dash-card-icon">
            <i class="fas fa-utensils"></i>
        </div>
        <div class="dash-card-body">
            <div class="dash-card-label">Diet Plans</div>
            <div class="dash-card-value"><?= $total_diet_plans ?? 0 ?></div>
            <div class="dash-card-sub">Total submitted</div>
        </div>
        <div class="dash-card-arrow"><i class="fas fa-arrow-right"></i></div>
    </a>

    <!-- Blood Donors — CRIMSON -->
    <a href="?route=hospital_admin/blood_donors" class="dash-card" style="--card-color:#be123c; --card-light:#fff1f2;">
        <div class="dash-card-icon">
            <i class="fas fa-hand-holding-heart"></i>
        </div>
        <div class="dash-card-body">
            <div class="dash-card-label">Blood Donors</div>
            <div class="dash-card-value"><?= $total_donors ?? 0 ?></div>
            <div class="dash-card-sub">Registered donors</div>
        </div>
        <div class="dash-card-arrow"><i class="fas fa-arrow-right"></i></div>
    </a>

    <!-- Blood Requests — ORANGE (Emergency) -->
    <a href="?route=hospital_admin/blood_requests" class="dash-card" style="--card-color:#c2410c; --card-light:#fff7ed;">
        <div class="dash-card-icon">
            <i class="fas fa-ambulance"></i>
        </div>
        <div class="dash-card-body">
            <div class="dash-card-label">Blood Requests</div>
            <div class="dash-card-value"><?= $pending_blood_requests ?? 0 ?></div>
            <div class="dash-card-sub">Pending requests</div>
        </div>
        <div class="dash-card-arrow"><i class="fas fa-arrow-right"></i></div>
    </a>

    <!-- Doctors — PURPLE -->
    <a href="?route=hospital_admin/doctors" class="dash-card" style="--card-color:#6d28d9; --card-light:#f5f3ff;">
        <div class="dash-card-icon">
            <i class="fas fa-user-md"></i>
        </div>
        <div class="dash-card-body">
            <div class="dash-card-label">My Doctors</div>
            <div class="dash-card-value"><?= $total_doctors ?? 0 ?></div>
            <div class="dash-card-sub">Active staff</div>
        </div>
        <div class="dash-card-arrow"><i class="fas fa-arrow-right"></i></div>
    </a>

</div>

<?php include 'views/layouts/footer.php'; ?>
