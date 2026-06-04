<?php include 'views/layouts/header.php'; ?>

<div class="dashboard-home">
    <div class="dashboard-hero">
        <div class="dashboard-hero-copy">
            <div class="dashboard-eyebrow">Admin overview</div>
            <h2 class="dashboard-title">Welcome to Mednoa!</h2>
            <p class="dashboard-subtitle">Hospital Admin Dashboard Template</p>
            <div class="dashboard-meta-row">
                <span class="dashboard-meta-pill"><i class="fas fa-circle text-success"></i> Live overview</span>
                <span class="dashboard-meta-pill"><i class="fas fa-shield-halved text-primary"></i> Secure admin workspace</span>
            </div>
        </div>
        <div class="dashboard-hero-tools">
            <div class="dashboard-search-shell">
                <input type="text" placeholder="Search here..." class="form-control dashboard-search-input" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
                <button class="btn btn-primary dashboard-search-btn" type="button"><i class="fas fa-cog"></i></button>
            </div>
        </div>
    </div>

    <!-- 4 Stats Cards -->
    <div class="stats-grid dashboard-stats-grid">
        <div class="stat-card colored red">
            <div class="icon-box"><i class="fas fa-hospital"></i></div>
            <div>
                <span class="stat-kicker">Facilities</span>
                <p>Total Hospitals</p>
                <h3><?= $total_hospitals ?? 0 ?></h3>
            </div>
        </div>
        <div class="stat-card colored blue">
            <div class="icon-box"><i class="fas fa-clinic-medical"></i></div>
            <div>
                <span class="stat-kicker">Network</span>
                <p>Total Clinics</p>
                <h3><?= $total_clinics ?? 0 ?></h3>
            </div>
        </div>
        <div class="stat-card colored green">
            <div class="icon-box"><i class="fas fa-check-circle"></i></div>
            <div>
                <span class="stat-kicker">Status</span>
                <p>Active Subscriptions</p>
                <h3><?= $active_subscriptions ?? 0 ?></h3>
            </div>
        </div>
        <div class="stat-card colored purple">
            <div class="icon-box"><i class="fas fa-user-md"></i></div>
            <div>
                <span class="stat-kicker">Medical staff</span>
                <p>Total Doctors</p>
                <h3><?= $total_doctors ?? 0 ?></h3>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="dashboard-panels">
    
    <!-- Recent Hospitals -->
    <div class="card p-20 dashboard-panel-card">
        <div class="card-header dashboard-card-header">
            <h5>Recent Hospitals / Clinics</h5>
            <a href="?route=admin/hospitals" class="btn btn-sm btn-light rounded-pill">View All</a>
        </div>
        <div class="table-responsive">
        <table class="table table-sm align-middle dashboard-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if(isset($recent_hospitals) && count($recent_hospitals) > 0): ?>
                    <?php foreach($recent_hospitals as $h): ?>
                    <tr>
                        <td><?= $h['name'] ?></td>
                        <td><small><?= $h['type'] ?? 'Hospital' ?></small></td>
                        <td>
                            <?php if($h['subscription_status'] == 'active'): ?>
                                <span class="badge" style="background: #e1f7e3; color: #34c759;">Active</span>
                            <?php else: ?>
                                <span class="badge" style="background: #fff0f1; color: #ff3b30;"><?= $h['subscription_status'] ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="3" class="text-center text-muted">No hospitals yet.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- Recent Doctors -->
    <div class="card p-20 dashboard-panel-card">
        <div class="card-header dashboard-card-header">
            <h5>New Doctors</h5>
            <a href="?route=admin/doctors" class="btn btn-sm btn-light rounded-pill">View All</a>
        </div>
        <div class="table-responsive">
        <table class="table table-sm align-middle dashboard-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Specialization</th>
                </tr>
            </thead>
            <tbody>
                <?php if(isset($recent_doctors) && count($recent_doctors) > 0): ?>
                    <?php foreach($recent_doctors as $d): ?>
                    <tr>
                        <td>
                            <div style="font-weight: 600;"><?= $d['name'] ?></div>
                            <small class="text-muted"><?= $d['phone'] ?? 'No Phone' ?></small>
                        </td>
                        <td><?= $d['specialization'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="2" class="text-center text-muted">No doctors found.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        </div>
    </div>

</div>
</div>

<?php include 'views/layouts/footer.php'; ?>
