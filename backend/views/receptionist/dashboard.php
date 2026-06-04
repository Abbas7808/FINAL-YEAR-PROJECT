<?php include 'views/layouts/header.php'; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-info">
            <h3><?= count($appointments) ?></h3>
            <p>Today's Patients</p>
        </div>
    </div>
    <!-- Add more stats -->
</div>

<div class="table-container">
    <div class="table-header">
        <h3>Today's Interface</h3>
        <a href="?route=receptionist/add_patient" class="btn btn-primary"><i class="fas fa-plus"></i> New Patient</a>
    </div>
    <table>
        <thead>
            <tr>
                <th>Token</th>
                <th>Patient Name</th>
                <th>Assigned Doctor</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($appointments as $appt): ?>
            <tr>
                <td><span style="font-weight: 800; font-size: 18px; color: var(--primary);">#<?= htmlspecialchars($appt['token_number']) ?></span></td>
                <td><?= htmlspecialchars($appt['patient_name']) ?></td>
                <td><?= htmlspecialchars($appt['doctor_name'] ? 'Dr. ' . $appt['doctor_name'] : '—') ?></td>
                <td>
                    <?php
                        $st = strtolower(trim($appt['status'] ?? 'pending'));
                        $styles = [
                            'pending'   => 'background:#fff3cd;color:#856404;',
                            'waiting'   => 'background:#e2e3e5;color:#383d41;',
                            'confirmed' => 'background:#d1e7dd;color:#0f5132;',
                            'completed' => 'background:#cff4fc;color:#055160;',
                            'cancelled' => 'background:#f8d7da;color:#842029;',
                        ];
                        $badgeStyle = $styles[$st] ?? 'background:#e2e3e5;color:#383d41;';
                    ?>
                    <span class="badge" style="<?= $badgeStyle ?>padding:5px 10px;border-radius:6px;font-weight:600;">
                        <?= ucfirst($st) ?>
                    </span>
                </td>
                <td>
                    <!-- If completed, show Print Prescription -->
                    <?php if($appt['status'] == 'completed'): ?>
                        <a href="?route=doctor/print_prescription&id=<?= $appt['id'] ?>" target="_blank" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px;"><i class="fas fa-print"></i> Print</a>
                    <?php else: ?>
                        <!-- Print Token -->
                        <a href="?route=receptionist/print_token&id=<?= $appt['id'] ?>" target="_blank" class="btn btn-secondary" style="padding: 5px 10px; font-size: 12px; background: #6c757d; color: white;"><i class="fas fa-ticket-alt"></i> Print Token</a>
                        <?php if($appt['status'] == 'pending'): ?>
                        <a href="?route=receptionist/mark_paid&id=<?= $appt['id'] ?>" onclick="return confirm('Confirm payment received and update status?');" class="btn btn-success" style="padding: 5px 10px; font-size: 12px; background: #198754; color: white; margin-left: 5px;"><i class="fas fa-check-circle"></i> Mark Paid</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'views/layouts/footer.php'; ?>
