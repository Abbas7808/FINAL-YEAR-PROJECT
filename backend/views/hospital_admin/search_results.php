<?php include 'views/layouts/header.php'; ?>

<div class="mb-4">
    <h3>Search Results for "<?= htmlspecialchars($search_query) ?>"</h3>
    <p class="text-muted">Found matches across appointments, doctors, and diet plans.</p>
</div>

<?php if (empty($results['appointments']) && empty($results['doctors']) && empty($results['diet_plans'])): ?>
    <div class="text-center p-5 bg-white rounded-4 shadow-sm">
        <i class="fas fa-search-minus fa-4xl text-muted opacity-25 mb-3"></i>
        <h4>No matches found</h4>
        <p class="text-muted">Try searching for a different name, token number, or email.</p>
    </div>
<?php else: ?>

    <!-- Appointments Results -->
    <?php if (!empty($results['appointments'])): ?>
    <div class="mb-5">
        <h5 class="mb-3 d-flex align-items-center">
            <i class="fas fa-calendar-alt me-2 text-primary"></i> Appointments
            <span class="badge bg-primary ms-2 rounded-pill"><?= count($results['appointments']) ?></span>
        </h5>
        <div class="table-container shadow-sm border-0 rounded-4">
            <table>
                <thead>
                    <tr>
                        <th>Token</th>
                        <th>Patient</th>
                        <th>Doctor</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results['appointments'] as $a): ?>
                    <tr>
                        <td><span class="badge bg-secondary">#<?= $a['token_number'] ?></span></td>
                        <td><strong><?= htmlspecialchars($a['patient_name']) ?></strong></td>
                        <td><?= htmlspecialchars($a['doctor_name']) ?></td>
                        <td><span class="badge bg-info"><?= ucfirst($a['status']) ?></span></td>
                        <td>
                            <a href="?route=hospital_admin/appointments" class="btn btn-sm btn-light border">View list</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- Doctors Results -->
    <?php if (!empty($results['doctors'])): ?>
    <div class="mb-5">
        <h5 class="mb-3 d-flex align-items-center">
            <i class="fas fa-user-md me-2 text-success"></i> Doctors
            <span class="badge bg-success ms-2 rounded-pill"><?= count($results['doctors']) ?></span>
        </h5>
        <div class="row g-3">
            <?php foreach ($results['doctors'] as $d): ?>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm rounded-4 p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="user-avatar" style="width:45px;height:45px;font-size:18px;">
                            <?= strtoupper(substr($d['name'], 0, 1)) ?>
                        </div>
                        <div>
                            <div class="fw-bold"><?= htmlspecialchars($d['name']) ?></div>
                            <div class="small text-muted"><?= htmlspecialchars($d['specialization'] ?? 'General Physician') ?></div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="?route=hospital_admin/doctors" class="btn btn-sm btn-outline-primary w-100 rounded-pill">Manage Doctor</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- Diet Plan Results -->
    <?php if (!empty($results['diet_plans'])): ?>
    <div class="mb-5">
        <h5 class="mb-3 d-flex align-items-center">
            <i class="fas fa-utensils me-2 text-warning"></i> Diet Plans
            <span class="badge bg-warning text-dark ms-2 rounded-pill"><?= count($results['diet_plans']) ?></span>
        </h5>
        <div class="table-container shadow-sm border-0 rounded-4">
            <table>
                <thead>
                    <tr>
                        <th>Patient Name</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results['diet_plans'] as $dp): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($dp['patient_name']) ?></strong></td>
                        <td><?= htmlspecialchars($dp['patient_email']) ?></td>
                        <td><span class="badge bg-light text-dark"><?= ucfirst($dp['status']) ?></span></td>
                        <td>
                            <a href="?route=hospital_admin/diet_plans" class="btn btn-sm btn-light border">View Details</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

<?php endif; ?>

<?php include 'views/layouts/footer.php'; ?>
