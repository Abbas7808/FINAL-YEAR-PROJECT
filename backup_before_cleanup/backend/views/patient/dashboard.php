<?php include 'views/layouts/header.php'; ?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon pink"><i class="fas fa-file-medical"></i></div>
        <div class="stat-info">
            <h3><?= count($history) ?></h3>
            <p>Total Visits</p>
        </div>
    </div>
</div>

<div class="table-container">
    <div class="table-header">
        <h3>My Medical History</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Date</th>
                <th>Hospital</th>
                <th>Doctor</th>
                <th>Token</th>
                <th>Status</th>
                <th>Prescription</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($history as $vist): ?>
            <tr>
                <td><?= $vist['date'] ?></td>
                <td><?= htmlspecialchars($vist['hospital_name'] ?? '—') ?></td>
                <td><?= htmlspecialchars($vist['doctor_name'] ? 'Dr. ' . $vist['doctor_name'] : '—') ?></td>
                <td>#<?= $vist['token_number'] ?></td>
                <td>
                    <?php if($vist['status'] == 'completed'): ?>
                        <span class="badge completed">Completed</span>
                    <?php else: ?>
                        <span class="badge waiting">Scheduled</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if($vist['status'] == 'completed'): ?>
                        <a href="?route=doctor/print_prescription&id=<?= $vist['id'] ?>" target="_blank" class="btn btn-primary" style="padding: 5px 10px; font-size: 12px;">View Rx</a>
                    <?php else: ?>
                        -
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<?php include 'views/layouts/footer.php'; ?>
