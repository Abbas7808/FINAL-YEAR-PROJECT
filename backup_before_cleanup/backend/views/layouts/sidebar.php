<div class="sidebar collapse d-md-block" id="sidebarCollapse">
    <div class="logo">
        <i class="fas fa-plus-square" style="color: var(--primary);"></i>
        <span>Mid-Nova</span>
    </div>

    <?php 
    $role = $_SESSION['role'] ?? 'guest';
    ?>

    <?php if($role == 'admin'): ?>
        <a href="?route=admin/dashboard" class="menu-item <?= ($_GET['route'] ?? '') == 'admin/dashboard' ? 'active' : '' ?>"><i class="fas fa-th-large"></i> Dashboard</a>
        
        <div class="menu-label mt-2 px-3 small text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Content</div>
        <a href="?route=admin/blogs" class="menu-item <?= (strpos($_GET['route'] ?? '', 'admin/blog') !== false && strpos($_GET['route'] ?? '', 'category') === false) ? 'active' : '' ?>"><i class="fas fa-blog"></i> Manage Blogs</a>
        <a href="?route=admin/blog_categories" class="menu-item <?= (strpos($_GET['route'] ?? '', 'category') !== false) ? 'active' : '' ?>"><i class="fas fa-tags"></i> Manage Categories</a>
        
        <div class="menu-label mt-2 px-3 small text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Personnel</div>
        <a href="?route=admin/doctors" class="menu-item <?= ($_GET['route'] ?? '') == 'admin/doctors' || strpos($_GET['route'] ?? '', 'admin/doctor') !== false ? 'active' : '' ?>"><i class="fas fa-user-md"></i> Doctors</a>
        <a href="?route=admin/receptionists" class="menu-item <?= ($_GET['route'] ?? '') == 'admin/receptionists' ? 'active' : '' ?>"><i class="fas fa-desktop"></i> Receptionists</a>

        <div class="menu-label mt-2 px-3 small text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Infrastructure</div>
        <a href="?route=admin/regions" class="menu-item <?= ($_GET['route'] ?? '') == 'admin/regions' ? 'active' : '' ?>"><i class="fas fa-map-marked-alt"></i> Regions</a>
        <a href="?route=admin/hospitals" class="menu-item <?= ($_GET['route'] ?? '') == 'admin/hospitals' ? 'active' : '' ?>"><i class="fas fa-hospital"></i> Hospitals</a>
        <a href="?route=admin/clinics" class="menu-item <?= ($_GET['route'] ?? '') == 'admin/clinics' ? 'active' : '' ?>"><i class="fas fa-clinic-medical"></i> Clinics</a>
        <a href="?route=admin/users" class="menu-item <?= ($_GET['route'] ?? '') == 'admin/users' ? 'active' : '' ?>"><i class="fas fa-users-cog"></i> Users & Access</a>
        <a href="?route=admin/settings" class="menu-item <?= ($_GET['route'] ?? '') == 'admin/settings' ? 'active' : '' ?>"><i class="fas fa-cog"></i> Settings</a>
    <?php endif; ?>

    <!-- Hospital Admin Menu -->
    <?php if($role == 'hospital_admin'): ?>
        <?php $current_route = $_GET['route'] ?? ''; ?>
        <a href="?route=hospital_admin/dashboard" class="menu-item <?= $current_route == 'hospital_admin/dashboard' || $current_route == '' ? 'active' : '' ?>"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="?route=hospital_admin/doctors" class="menu-item <?= $current_route == 'hospital_admin/doctors' || strpos($current_route, 'hospital_admin/doctor') !== false ? 'active' : '' ?>"><i class="fas fa-user-md"></i> My Doctors</a>
        <a href="?route=hospital_admin/departments" class="menu-item <?= $current_route == 'hospital_admin/departments' ? 'active' : '' ?>"><i class="fas fa-clinic-medical"></i> Departments</a>
        <a href="?route=hospital_admin/schedules" class="menu-item <?= $current_route == 'hospital_admin/schedules' ? 'active' : '' ?>"><i class="fas fa-clock"></i> Schedules</a>

        <a href="?route=hospital_admin/appointments" class="menu-item <?= $current_route == 'hospital_admin/appointments' ? 'active' : '' ?>"><i class="fas fa-calendar-check"></i> Appointments</a>
        <a href="?route=hospital_admin/diet_plans" class="menu-item <?= $current_route == 'hospital_admin/diet_plans' ? 'active' : '' ?>"><i class="fas fa-apple-alt"></i> Diet Plans</a>
        <div class="menu-label mt-2 px-3 small text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">Blood Bank</div>
        <a href="?route=hospital_admin/blood_donors" class="menu-item <?= $current_route == 'hospital_admin/blood_donors' ? 'active' : '' ?>"><i class="fas fa-hand-holding-heart"></i> Blood Donors</a>
        <a href="?route=hospital_admin/blood_requests" class="menu-item <?= $current_route == 'hospital_admin/blood_requests' ? 'active' : '' ?>"><i class="fas fa-ambulance"></i> Blood Requests</a>
        <a href="?route=hospital_admin/specializations" class="menu-item <?= $current_route == 'hospital_admin/specializations' ? 'active' : '' ?>"><i class="fas fa-stethoscope"></i> Specializations</a>
        <a href="?route=hospital_admin/settings" class="menu-item <?= $current_route == 'hospital_admin/settings' ? 'active' : '' ?>"><i class="fas fa-cog"></i> Settings</a>
    <?php endif; ?>

    <!-- Doctor Menu -->
    <?php if($role == 'doctor'): ?>
        <?php $current_route = $_GET['route'] ?? ''; ?>
        <a href="?route=doctor/dashboard" class="menu-item <?= $current_route == 'doctor/dashboard' || $current_route == '' ? 'active' : '' ?>"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="#" class="menu-item"><i class="fas fa-user-injured"></i> My Patients</a>
        <a href="#" class="menu-item"><i class="fas fa-history"></i> History</a>
    <?php endif; ?>

    <!-- Patient Menu -->
    <?php if($role == 'patient'): ?>
        <?php $current_route = $_GET['route'] ?? ''; ?>
        <a href="?route=patient/dashboard" class="menu-item <?= $current_route == 'patient/dashboard' || $current_route == '' ? 'active' : '' ?>"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="#" class="menu-item"><i class="fas fa-file-prescription"></i> Prescriptions</a>
    <?php endif; ?>

    <div class="sidebar-footer mt-auto p-3">
        <div class="menu-item theme-toggle" onclick="toggleTheme()" style="cursor: pointer;">
            <i class="fas fa-moon"></i> <span>Dark Mode</span>
        </div>
        <a href="?route=auth/logout" class="menu-item text-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

</div>
