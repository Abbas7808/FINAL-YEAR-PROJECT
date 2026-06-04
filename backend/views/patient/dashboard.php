<?php include 'views/layouts/header.php'; ?>

<!-- Patient Dashboard Custom Premium Styles -->
<style>
    :root {
        --dash-primary: #3b82f6;
        --dash-success: #10b981;
        --dash-warning: #f59e0b;
        --dash-danger: #ef4444;
        --dash-purple: #8b5cf6;
        --dash-info: #06b6d4;
        --card-border-radius: 16px;
        --glass-bg: rgba(255, 255, 255, 0.7);
        --glass-border: rgba(255, 255, 255, 0.4);
    }

    /* Light/Dark mode adaptations for dashboard */
    body.dark-mode {
        --glass-bg: rgba(30, 41, 59, 0.7);
        --glass-border: rgba(255, 255, 255, 0.05);
    }

    .welcome-banner {
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        border-radius: var(--card-border-radius);
        padding: 2.5rem;
        color: white;
        margin-bottom: 2rem;
        position: relative;
        overflow: hidden;
        box-shadow: 0 10px 30px rgba(59, 130, 246, 0.2);
    }

    .welcome-banner::after {
        content: '';
        position: absolute;
        width: 300px;
        height: 300px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        top: -100px;
        right: -100px;
        pointer-events: none;
    }

    .welcome-banner::before {
        content: '';
        position: absolute;
        width: 150px;
        height: 150px;
        background: rgba(255, 255, 255, 0.03);
        border-radius: 50%;
        bottom: -50px;
        left: 20%;
        pointer-events: none;
    }

    .glass-card {
        background: var(--glass-bg);
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
        border: 1px solid var(--glass-border);
        border-radius: var(--card-border-radius);
        box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.04);
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        overflow: hidden;
    }

    .glass-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 40px 0 rgba(31, 38, 135, 0.08);
    }

    .stat-mini-card {
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }

    .stat-mini-icon {
        width: 52px;
        height: 52px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        color: white;
    }

    .stat-mini-icon.blue { background: linear-gradient(135deg, #3b82f6, #1d4ed8); }
    .stat-mini-icon.green { background: linear-gradient(135deg, #10b981, #047857); }
    .stat-mini-icon.purple { background: linear-gradient(135deg, #8b5cf6, #6d28d9); }
    .stat-mini-icon.orange { background: linear-gradient(135deg, #f59e0b, #b45309); }
    .stat-mini-icon.cyan { background: linear-gradient(135deg, #06b6d4, #0891b2); }

    /* Custom Dashboard Tabs */
    .dash-tabs {
        display: flex;
        gap: 0.5rem;
        padding: 0.5rem;
        background: rgba(0, 0, 0, 0.03);
        border-radius: 12px;
        margin-bottom: 2rem;
        overflow-x: auto;
        white-space: nowrap;
    }

    body.dark-mode .dash-tabs {
        background: rgba(255, 255, 255, 0.03);
    }

    .dash-tab-btn {
        border: none;
        background: transparent;
        padding: 0.75rem 1.5rem;
        border-radius: 8px;
        font-weight: 500;
        color: #6b7280;
        transition: all 0.2s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    body.dark-mode .dash-tab-btn {
        color: #9ca3af;
    }

    .dash-tab-btn.active {
        background: white;
        color: var(--dash-primary);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    body.dark-mode .dash-tab-btn.active {
        background: #1e293b;
        color: #60a5fa;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }

    /* Badges */
    .badge-status {
        padding: 0.4em 0.8em;
        border-radius: 9999px;
        font-weight: 600;
        font-size: 0.75rem;
        text-transform: uppercase;
        display: inline-flex;
        align-items: center;
        gap: 0.25rem;
    }

    .badge-status.pending { background-color: rgba(245, 158, 11, 0.15); color: #d97706; }
    .badge-status.confirmed { background-color: rgba(59, 130, 246, 0.15); color: #2563eb; }
    .badge-status.completed { background-color: rgba(16, 185, 129, 0.15); color: #059669; }
    .badge-status.cancelled { background-color: rgba(239, 68, 68, 0.15); color: #dc2626; }

    /* Prescription rx-card styles */
    .rx-card {
        border-left: 4px solid var(--dash-primary);
        position: relative;
    }
    .rx-header {
        background-color: rgba(59, 130, 246, 0.03);
    }
    body.dark-mode .rx-header {
        background-color: rgba(255, 255, 255, 0.02);
    }

    /* Health Assessment Gauges */
    .health-val {
        font-size: 2.25rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .health-unit {
        font-size: 1rem;
        font-weight: 500;
        color: #9ca3af;
    }

    /* Table styling tweaks */
    .table-responsive {
        border-radius: 12px;
        overflow: hidden;
    }

    .table-custom {
        margin-bottom: 0;
    }

    .table-custom th {
        background-color: rgba(0, 0, 0, 0.02);
        color: #4b5563;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom-width: 1px;
    }

    body.dark-mode .table-custom th {
        background-color: rgba(255, 255, 255, 0.02);
        color: #9ca3af;
    }

    .table-custom td {
        vertical-align: middle;
        font-size: 0.9rem;
    }

    /* Info Field Helper */
    .info-label {
        font-size: 0.75rem;
        text-transform: uppercase;
        color: #9ca3af;
        font-weight: 600;
        letter-spacing: 0.05em;
        margin-bottom: 0.25rem;
    }
    .info-value {
        font-weight: 500;
        font-size: 0.95rem;
    }

    /* Micro-Animations */
    .tab-pane-fade {
        animation: fadeIn 0.4s ease forwards;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

<!-- Dashboard Welcome Banner -->
<div class="welcome-banner">
    <div class="row align-items-center">
        <div class="col-md-8">
            <h1 class="display-6 fw-bold mb-2">Welcome Back, <?= htmlspecialchars($profile['name']) ?>!</h1>
            <p class="mb-0 opacity-75">Access your personal healthcare dashboard, view medical records, and track your clinical interactions.</p>
        </div>
        <div class="col-md-4 text-md-end mt-3 mt-md-0">
            <a href="../frontend/appointment.html" class="btn btn-light text-primary rounded-pill px-4 py-2 fw-bold shadow-sm">
                <i class="fas fa-calendar-plus me-2"></i>Book New Appointment
            </a>
        </div>
    </div>
</div>

<!-- Quick Statistics Grid -->
<div class="row g-4 mb-4">
    <div class="col-md-6 col-lg-3">
        <div class="glass-card stat-mini-card">
            <div class="stat-mini-icon blue">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0"><?= count($upcoming_appointments) ?></h3>
                <p class="text-muted small mb-0">Upcoming Bookings</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="glass-card stat-mini-card">
            <div class="stat-mini-icon green">
                <i class="fas fa-file-prescription"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0"><?= count($prescriptions) ?></h3>
                <p class="text-muted small mb-0">Medical Prescriptions</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="glass-card stat-mini-card">
            <div class="stat-mini-icon purple">
                <i class="fas fa-apple-alt"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0"><?= count($diet_plans) ?></h3>
                <p class="text-muted small mb-0">Active Diet Plans</p>
            </div>
        </div>
    </div>
    
    <div class="col-md-6 col-lg-3">
        <div class="glass-card stat-mini-card">
            <div class="stat-mini-icon orange">
                <i class="fas fa-heartbeat"></i>
            </div>
            <div>
                <h3 class="fw-bold mb-0"><?= $health_assessment ? htmlspecialchars($health_assessment['bmi_score']) : '—' ?></h3>
                <p class="text-muted small mb-0">BMI Calculator Score</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- LEFT SIDEBAR: Personal Information Card -->
    <div class="col-lg-4">
        <div class="glass-card p-4 h-100">
            <div class="text-center pb-4 border-bottom mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle fw-bold mb-3 shadow-sm" style="width: 72px; height: 72px; font-size: 1.8rem;">
                    <?= strtoupper(substr($profile['name'], 0, 1)) ?>
                </div>
                <h4 class="fw-bold mb-1"><?= htmlspecialchars($profile['name']) ?></h4>
                <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1">Registered Patient</span>
            </div>

            <h5 class="fw-bold mb-3 text-dark"><i class="fas fa-user-circle text-primary me-2"></i>Personal Info</h5>
            
            <div class="row g-3">
                <div class="col-6">
                    <div class="info-label">Username</div>
                    <div class="info-value">@<?= htmlspecialchars($profile['username'] ?? '—') ?></div>
                </div>
                <div class="col-6">
                    <div class="info-label">Blood Group</div>
                    <div class="info-value">
                        <span class="badge bg-danger text-white rounded-pill px-2 py-1"><i class="fas fa-tint me-1"></i><?= htmlspecialchars($profile['blood_group'] ?? '—') ?></span>
                    </div>
                </div>
                <div class="col-12">
                    <div class="info-label">Phone Number</div>
                    <div class="info-value"><?= htmlspecialchars($profile['phone'] ?? '—') ?></div>
                </div>
                <div class="col-12">
                    <div class="info-label">Email Address</div>
                    <div class="info-value"><?= htmlspecialchars($profile['email'] ?? '—') ?></div>
                </div>
                <div class="col-6">
                    <div class="info-label">Age</div>
                    <div class="info-value"><?= htmlspecialchars($profile['age'] ?? '—') ?> Yrs</div>
                </div>
                <div class="col-6">
                    <div class="info-label">Gender</div>
                    <div class="info-value"><?= htmlspecialchars($profile['gender'] ?? '—') ?></div>
                </div>
                <div class="col-12">
                    <div class="info-label">Home Address</div>
                    <div class="info-value"><?= htmlspecialchars($profile['address'] ?? '—') ?></div>
                </div>
                <div class="col-12 border-top pt-3 mt-3">
                    <div class="info-label">Account Created</div>
                    <div class="info-value text-muted small"><i class="fas fa-calendar-alt me-1"></i><?= date('F d, Y', strtotime($profile['registration_date'])) ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT SECTION: Interactive Tabs Content -->
    <div class="col-lg-8">
        <!-- Interactive Navigation Tabs -->
        <div class="dash-tabs">
            <button class="dash-tab-btn active" data-tab="appointments">
                <i class="fas fa-calendar-check"></i> Appointments
            </button>
            <button class="dash-tab-btn" data-tab="prescriptions">
                <i class="fas fa-file-prescription"></i> Medical Records
            </button>
            <button class="dash-tab-btn" data-tab="diet-plans">
                <i class="fas fa-apple-alt"></i> Diet Plans
            </button>
            <button class="dash-tab-btn" data-tab="health-assessment">
                <i class="fas fa-heartbeat"></i> Health Assessment
            </button>
            <button class="dash-tab-btn" data-tab="blood-donation">
                <i class="fas fa-tint"></i> Blood Donation
            </button>
        </div>

        <!-- TAB CONTENT PANES -->
        <div class="tab-content">
            
            <!-- 1. APPOINTMENTS TAB -->
            <div class="tab-pane-fade" id="tab-appointments">
                <!-- Upcoming Appointments -->
                <div class="glass-card p-4 mb-4">
                    <h5 class="fw-bold mb-3 text-primary"><i class="fas fa-clock me-2"></i>Upcoming Bookings</h5>
                    <?php if (empty($upcoming_appointments)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="far fa-calendar-times fa-2x mb-2 opacity-50"></i>
                            <p class="mb-0 small">No upcoming appointments scheduled.</p>
                            <a href="../frontend/appointment.html" class="btn btn-sm btn-outline-primary mt-2 rounded-pill">Book Now</a>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-custom">
                                <thead>
                                    <tr>
                                        <th>Date &amp; Time</th>
                                        <th>Hospital</th>
                                        <th>Doctor</th>
                                        <th>Token</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($upcoming_appointments as $app): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold"><?= date('M d, Y', strtotime($app['date'])) ?></div>
                                            <small class="text-muted"><?= htmlspecialchars(ucfirst($app['time_slot'])) ?></small>
                                        </td>
                                        <td><?= htmlspecialchars($app['hospital_name'] ?? '—') ?></td>
                                        <td>Dr. <?= htmlspecialchars($app['doctor_name'] ?? 'General Practitioner') ?></td>
                                        <td><span class="badge bg-light text-dark border">#<?= $app['token_number'] ?></span></td>
                                        <td>
                                            <span class="badge-status <?= $app['status'] ?>">
                                                <i class="fas fa-circle" style="font-size: 6px;"></i> <?= htmlspecialchars(ucfirst($app['status'])) ?>
                                            </span>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Previous Appointments -->
                <div class="glass-card p-4">
                    <h5 class="fw-bold mb-3 text-muted"><i class="fas fa-history me-2"></i>Past Appointment History</h5>
                    <?php if (empty($previous_appointments)): ?>
                        <div class="text-center py-3 text-muted small">No past appointments recorded.</div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover table-custom">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Hospital</th>
                                        <th>Doctor</th>
                                        <th>Token</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($previous_appointments as $app): ?>
                                    <tr>
                                        <td><?= date('M d, Y', strtotime($app['date'])) ?></td>
                                        <td><?= htmlspecialchars($app['hospital_name'] ?? '—') ?></td>
                                        <td>Dr. <?= htmlspecialchars($app['doctor_name'] ?? '—') ?></td>
                                        <td>#<?= $app['token_number'] ?></td>
                                        <td>
                                            <span class="badge-status <?= $app['status'] ?>">
                                                <?= htmlspecialchars(ucfirst($app['status'])) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($app['status'] == 'completed'): ?>
                                                <a href="?route=doctor/print_prescription&amp;id=<?= $app['id'] ?>" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3">
                                                    <i class="fas fa-print me-1"></i> Rx
                                                </a>
                                            <?php else: ?>
                                                —
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 2. MEDICAL RECORDS / PRESCRIPTIONS TAB -->
            <div class="tab-pane-fade d-none" id="tab-prescriptions">
                <div class="glass-card p-4">
                    <h5 class="fw-bold mb-4 text-primary"><i class="fas fa-file-prescription me-2"></i>Prescriptions &amp; Doctor Recommendations</h5>
                    <?php if (empty($prescriptions)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-file-prescription fa-3x mb-3 opacity-50"></i>
                            <p class="mb-0">No active medical prescriptions or recommendations found.</p>
                            <small>Prescriptions will appear here once finalized by your consulting doctor.</small>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-4">
                            <?php foreach ($prescriptions as $rx): ?>
                                <div class="card glass-card rx-card overflow-hidden">
                                    <div class="rx-header p-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                                        <div>
                                            <span class="fw-bold text-dark">Consultation on <?= date('F d, Y', strtotime($rx['appointment_date'])) ?></span>
                                            <div class="small text-muted"><?= htmlspecialchars($rx['hospital_name']) ?> — Dr. <?= htmlspecialchars($rx['doctor_name']) ?></div>
                                        </div>
                                        <a href="?route=doctor/print_prescription&amp;id=<?= $rx['appointment_id'] ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                            <i class="fas fa-print me-1"></i>Print Prescription
                                        </a>
                                    </div>
                                    <div class="card-body p-4">
                                        <div class="row g-3">
                                            <?php if (!empty($rx['symptoms'])): ?>
                                                <div class="col-md-6">
                                                    <div class="fw-bold text-dark small mb-1"><i class="fas fa-diagnoses me-1 text-primary"></i>Symptoms</div>
                                                    <div class="bg-light p-2 rounded small"><?= nl2br(htmlspecialchars($rx['symptoms'])) ?></div>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <?php if (!empty($rx['diagnosis'])): ?>
                                                <div class="col-md-6">
                                                    <div class="fw-bold text-dark small mb-1"><i class="fas fa-poll-h me-1 text-primary"></i>Diagnosis</div>
                                                    <div class="bg-light p-2 rounded small"><?= nl2br(htmlspecialchars($rx['diagnosis'])) ?></div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($rx['medicines'])): ?>
                                                <div class="col-12">
                                                    <div class="fw-bold text-dark small mb-1"><i class="fas fa-pills me-1 text-primary"></i>Prescribed Medicines</div>
                                                    <div class="bg-light p-3 rounded small font-monospace"><?= nl2br(htmlspecialchars($rx['medicines'])) ?></div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($rx['tests_suggested'])): ?>
                                                <div class="col-md-6">
                                                    <div class="fw-bold text-dark small mb-1"><i class="fas fa-vial me-1 text-primary"></i>Suggested Tests</div>
                                                    <div class="bg-light p-2 rounded small text-danger"><?= nl2br(htmlspecialchars($rx['tests_suggested'])) ?></div>
                                                </div>
                                            <?php endif; ?>

                                            <?php if (!empty($rx['advice'])): ?>
                                                <div class="col-md-6">
                                                    <div class="fw-bold text-dark small mb-1"><i class="fas fa-comment-medical me-1 text-primary"></i>Doctor's Advice / Recommendations</div>
                                                    <div class="bg-light p-2 rounded small text-success"><?= nl2br(htmlspecialchars($rx['advice'])) ?></div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 3. DIET PLANS TAB -->
            <div class="tab-pane-fade d-none" id="tab-diet-plans">
                <div class="glass-card p-4">
                    <h5 class="fw-bold mb-4 text-primary"><i class="fas fa-apple-alt me-2"></i>My Diet Plans &amp; Goal History</h5>
                    <?php if (empty($diet_plans)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-utensils fa-3x mb-3 opacity-50"></i>
                            <p class="mb-0">No smart diet plans have been requested or assigned.</p>
                            <a href="../frontend/diet_plan.html" class="btn btn-primary rounded-pill px-4 mt-3">Request Smart Diet Plan</a>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($diet_plans as $dp): ?>
                                <div class="card glass-card p-4">
                                    <div class="d-flex justify-content-between align-items-start border-bottom pb-2 mb-3 flex-wrap gap-2">
                                        <div>
                                            <h6 class="fw-bold text-dark mb-0">Goal: <?= str_replace('_', ' ', ucfirst(htmlspecialchars($dp['goal']))) ?></h6>
                                            <small class="text-muted">Requested on <?= date('F d, Y', strtotime($dp['created_at'])) ?></small>
                                        </div>
                                        <div>
                                            <span class="badge bg-primary text-white rounded-pill px-3 py-1"><?= ucfirst(htmlspecialchars($dp['status'])) ?></span>
                                        </div>
                                    </div>
                                    <div class="row g-3 small">
                                        <div class="col-md-4 col-6">
                                            <div class="text-muted">Height &amp; Weight</div>
                                            <div class="fw-bold"><?= htmlspecialchars($dp['height'] ?? '—') ?> cm | <?= htmlspecialchars($dp['weight'] ?? '—') ?> kg</div>
                                        </div>
                                        <div class="col-md-4 col-6">
                                            <div class="text-muted">Hospital / Facility</div>
                                            <div class="fw-bold"><?= htmlspecialchars($dp['hospital_name'] ?? 'General') ?></div>
                                        </div>
                                        <div class="col-md-4 col-12">
                                            <div class="text-muted">Consultant Doctor</div>
                                            <div class="fw-bold"><?= $dp['doctor_name'] ? 'Dr. ' . htmlspecialchars($dp['doctor_name']) : 'Assigned Doctor' ?></div>
                                        </div>
                                        <?php if (!empty($dp['conditions'])): ?>
                                            <div class="col-12 mt-2">
                                                <div class="text-muted">Medical Conditions / Notes</div>
                                                <div class="bg-light p-2 rounded mt-1"><?= nl2br(htmlspecialchars($dp['conditions'])) ?></div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 4. HEALTH ASSESSMENT TAB -->
            <div class="tab-pane-fade d-none" id="tab-health-assessment">
                <div class="glass-card p-4">
                    <h5 class="fw-bold mb-4 text-primary"><i class="fas fa-heartbeat me-2"></i>My Health Calculator Results</h5>
                    <?php if (!$health_assessment): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-calculator fa-3x mb-3 opacity-50"></i>
                            <p class="mb-0">No health assessment data has been saved to your profile yet.</p>
                            <p class="small text-muted">Calculate your BMI, daily calorie requirements, and water target using our smart calculators, and click "Save to Profile".</p>
                            <a href="../frontend/health-assessment.html" class="btn btn-primary rounded-pill px-4 mt-2">Start Health Assessment</a>
                        </div>
                    <?php else: ?>
                        <div class="row g-4">
                            <!-- BMI CARD -->
                            <div class="col-md-4">
                                <div class="card bg-primary text-white p-3 text-center border-0 rounded-4 shadow-sm">
                                    <div class="small opacity-75 mb-1 text-white">BODY MASS INDEX (BMI)</div>
                                    <div class="health-val text-white"><?= htmlspecialchars($health_assessment['bmi_score']) ?></div>
                                    <div class="badge bg-white text-primary rounded-pill px-2 py-1 mt-2 d-inline-block fw-bold"><?= htmlspecialchars($health_assessment['bmi_category']) ?></div>
                                </div>
                            </div>
                            
                            <!-- CALORIES CARD -->
                            <div class="col-md-4">
                                <div class="card bg-warning text-white p-3 text-center border-0 rounded-4 shadow-sm">
                                    <div class="small opacity-75 mb-1 text-white">DAILY CALORIES TARGET</div>
                                    <div class="health-val text-white"><?= htmlspecialchars($health_assessment['calories_target']) ?> <span class="health-unit text-white opacity-75">kcal</span></div>
                                    <div class="small opacity-75 mt-2 text-white">To maintain weight goal</div>
                                </div>
                            </div>

                            <!-- WATER CARD -->
                            <div class="col-md-4">
                                <div class="card bg-info text-white p-3 text-center border-0 rounded-4 shadow-sm">
                                    <div class="small opacity-75 mb-1 text-white">WATER INTAKE TARGET</div>
                                    <div class="health-val text-white"><?= htmlspecialchars($health_assessment['water_target']) ?> <span class="health-unit text-white opacity-75">Liters</span></div>
                                    <div class="small opacity-75 mt-2 text-white">Daily hydration requirement</div>
                                </div>
                            </div>

                            <div class="col-12 mt-3 text-center">
                                <div class="p-3 bg-light rounded-4 text-muted small">
                                    <i class="fas fa-info-circle me-1"></i> These targets are generated based on your calculated variables. You can update these results at any time by running the <a href="../frontend/health-assessment.html" class="text-primary fw-bold text-decoration-none">Health Assessment Calculators</a> again and clicking "Save to Profile".
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- 5. BLOOD DONATION TAB -->
            <div class="tab-pane-fade d-none" id="tab-blood-donation">
                <div class="row g-4">
                    <!-- Donor Profile Status -->
                    <div class="col-md-5">
                        <div class="glass-card p-4 h-100">
                            <h5 class="fw-bold mb-3 text-primary"><i class="fas fa-hand-holding-heart me-2"></i>Donor Status</h5>
                            <?php if (!$donor_status): ?>
                                <div class="text-center py-4 text-muted small">
                                    <i class="fas fa-tint-slash fa-2x mb-2 opacity-50 text-danger"></i>
                                    <p class="mb-0">You are not registered as an active blood donor yet.</p>
                                    <a href="../frontend/blood-donation.html" class="btn btn-sm btn-outline-danger mt-2 rounded-pill">Become a Donor</a>
                                </div>
                            <?php else: ?>
                                <div class="text-center p-3 bg-danger-subtle rounded-4 mb-3 border border-danger-subtle">
                                    <i class="fas fa-heart text-danger fa-2x mb-2"></i>
                                    <h6 class="fw-bold text-danger mb-0">Active Blood Donor</h6>
                                    <small class="text-muted">Registered in MedNova Network</small>
                                </div>
                                <div class="row g-3 small">
                                    <div class="col-6">
                                        <span class="text-muted">Blood Group</span>
                                        <div class="fw-bold fs-5 text-danger"><i class="fas fa-tint me-1"></i><?= htmlspecialchars($donor_status['blood_group']) ?></div>
                                    </div>
                                    <div class="col-6">
                                        <span class="text-muted">Registered Age</span>
                                        <div class="fw-bold fs-5"><?= htmlspecialchars($donor_status['age'] ?? '—') ?> yrs</div>
                                    </div>
                                    <div class="col-12">
                                        <span class="text-muted">Donor Location</span>
                                        <div class="fw-bold"><?= htmlspecialchars($donor_status['location'] ?? '—') ?></div>
                                    </div>
                                    <div class="col-12">
                                        <span class="text-muted">Preferred Center</span>
                                        <div class="fw-bold"><?= htmlspecialchars($donor_status['hospital_name'] ?? 'Any Center') ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Blood Requests -->
                    <div class="col-md-7">
                        <div class="glass-card p-4 h-100">
                            <h5 class="fw-bold mb-3 text-primary"><i class="fas fa-ambulance me-2"></i>My Blood Requests</h5>
                            <?php if (empty($blood_requests)): ?>
                                <div class="text-center py-4 text-muted small">
                                    <i class="fas fa-heartbeat fa-2x mb-2 opacity-50"></i>
                                    <p class="mb-0">You haven't requested any emergency blood broadcasts.</p>
                                    <a href="../frontend/blood-donation.html#request-tab" class="btn btn-sm btn-outline-primary mt-2 rounded-pill">Create Request</a>
                                </div>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-hover table-custom small">
                                        <thead>
                                            <tr>
                                                <th>Blood</th>
                                                <th>Recipient</th>
                                                <th>Urgency</th>
                                                <th>Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($blood_requests as $req): ?>
                                            <tr>
                                                <td><span class="badge bg-danger text-white rounded-pill px-2 py-1">Type <?= htmlspecialchars($req['blood_group']) ?></span></td>
                                                <td><?= htmlspecialchars($req['patient_name']) ?></td>
                                                <td>
                                                    <span class="badge <?= $req['urgency'] == 'Critical' ? 'bg-danger' : 'bg-warning text-dark' ?> rounded-pill text-xs px-2 py-0.5">
                                                        <?= htmlspecialchars($req['urgency']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-light text-dark border rounded-pill px-2 py-0.5"><?= ucfirst(htmlspecialchars($req['status'])) ?></span>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Simple Tab Switching Script -->
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const tabBtns = document.querySelectorAll('.dash-tab-btn');
        const tabPanes = document.querySelectorAll('.tab-content > div');

        tabBtns.forEach(btn => {
            btn.addEventListener('click', function () {
                // Remove active classes
                tabBtns.forEach(b => b.classList.remove('active'));
                tabPanes.forEach(p => p.classList.add('d-none'));

                // Add active to clicked
                this.classList.add('active');
                
                const targetTab = this.getAttribute('data-tab');
                const targetPane = document.getElementById('tab-' + targetTab);
                if (targetPane) {
                    targetPane.classList.remove('d-none');
                }
            });
        });
    });
</script>

<?php include 'views/layouts/footer.php'; ?>
