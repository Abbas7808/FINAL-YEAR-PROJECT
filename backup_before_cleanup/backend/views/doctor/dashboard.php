<?php include 'views/layouts/header.php'; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon purple"><i class="fas fa-user-injured"></i></div>
        <div class="stat-info">
            <h3><?= count($appointments) ?></h3>
            <p>Waiting Patients</p>
        </div>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h3>Patient Queue</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Token</th>
                <th>Patient Name</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($appointments as $appt): ?>
            <tr>
                <td><span style="font-weight: 800; font-size: 18px; color: var(--primary);">#<?= $appt['token_number'] ?></span></td>
                <td><?= $appt['patient_name'] ?></td>
                <td>
                     <?php if($appt['status'] == 'waiting'): ?>
                        <span class="badge waiting">Waiting</span>
                    <?php elseif($appt['status'] == 'completed'): ?>
                        <span class="badge completed">Completed</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($appt['status'] == 'waiting'): ?>
                        <a href="?route=doctor/diagnose&id=<?= $appt['id'] ?>" class="btn btn-primary" style="padding: 8px 15px;">Checkup</a>
                    <?php else: ?>
                        <a href="?route=doctor/print_prescription&id=<?= $appt['id'] ?>" target="_blank" class="btn btn-primary" style="padding: 8px 15px; background: #333;"><i class="fas fa-print"></i> Print</a>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'views/layouts/footer.php'; ?>
