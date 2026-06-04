<?php include 'views/layouts/header.php'; ?>

<?php
$pending_count = 0;
foreach ($diet_plans ?? [] as $dp) {
    if (($dp['status'] ?? 'pending') === 'pending') {
        $pending_count++;
    }
}
?>

<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;">
    <h3>Diet Plan Requests</h3>
    <span class="badge bg-warning text-dark fs-6"><?= $pending_count ?> Pending Requests</span>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Patient Details</th>
                <th>Contact Info</th>
                <th>Body Metrics</th>
                <th>Health Goal</th>
                <th>Medical Conditions</th>
                <th>Assigned Doctor</th>
                <th>Request Status</th>
                <th>Submitted Date</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($diet_plans)): ?>
                <tr>
                    <td colspan="9" class="text-center p-5">
                        <i class="fas fa-utensils fa-3x text-muted mb-3 d-block" style="opacity:0.3;"></i>
                        <div class="text-muted">No diet plan requests yet.</div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($diet_plans as $dp): ?>
                <tr>
                    <td>
                        <div class="fw-bold text-primary mb-1"><?= htmlspecialchars($dp['patient_name'] ?? 'N/A') ?></div>
                        <div class="small text-muted">ID: #<?= $dp['id'] ?? 'N/A' ?></div>
                    </td>
                    <td>
                        <div class="small">
                            <i class="fab fa-whatsapp text-success me-1"></i>
                            <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $dp['patient_phone'] ?? '') ?>?text=Hello%20Med%20Nova%2C%20regarding%20your%20diet%20plan%20request" 
                               class="text-decoration-none" target="_blank">
                                <?= htmlspecialchars($dp['patient_phone'] ?? 'N/A') ?>
                            </a>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex flex-column gap-1">
                            <?php if($dp['age']): ?>
                                <span class="badge bg-light text-dark small">
                                    <i class="fas fa-birthday-cake me-1"></i>Age: <?= $dp['age'] ?>
                                </span>
                            <?php endif; ?>
                            <?php if($dp['weight']): ?>
                                <span class="badge bg-light text-dark small">
                                    <i class="fas fa-weight me-1"></i>Wt: <?= $dp['weight'] ?>kg
                                </span>
                            <?php endif; ?>
                            <?php if($dp['height']): ?>
                                <span class="badge bg-light text-dark small">
                                    <i class="fas fa-ruler-vertical me-1"></i>Ht: <?= $dp['height'] ?>cm
                                </span>
                            <?php endif; ?>
                            <?php if(!$dp['age'] && !$dp['weight'] && !$dp['height']): ?>
                                <span class="text-muted small">Not provided</span>
                            <?php endif; ?>
                        </div>
                    </td>
                    <td>
                        <span class="badge bg-info text-dark px-2 py-1">
                            <i class="fas fa-bullseye me-1"></i>
                            <?= ucwords(str_replace('_', ' ', $dp['goal'] ?? 'N/A')) ?>
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-notes-medical text-muted me-2"></i>
                            <span class="small" title="<?= htmlspecialchars($dp['conditions'] ?? '') ?>" style="cursor: help;">
                                <?= htmlspecialchars(strlen($dp['conditions'] ?? '') > 25 ? substr($dp['conditions'], 0, 25).'...' : ($dp['conditions'] ?: 'None')) ?>
                            </span>
                        </div>
                    </td>
                    <td>
                        <?php if($dp['doctor_name']): ?>
                            <div class="d-flex align-items-center">
                                <i class="fas fa-user-md text-primary me-2"></i>
                                <span class="small fw-medium"><?= htmlspecialchars($dp['doctor_name']) ?></span>
                            </div>
                        <?php else: ?>
                            <span class="text-muted small">
                                <i class="fas fa-user-slash me-1"></i>Unassigned
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php 
                        $status = $dp['status'] ?? 'pending';
                        $statusConfig = [
                            'pending' => ['icon' => 'fas fa-clock', 'class' => 'warning', 'text' => 'Pending'],
                            'in_progress' => ['icon' => 'fas fa-spinner fa-spin', 'class' => 'info', 'text' => 'In Progress'],
                            'completed' => ['icon' => 'fas fa-check-circle', 'class' => 'success', 'text' => 'Completed']
                        ];
                        $config = $statusConfig[$status] ?? $statusConfig['pending'];
                        ?>
                        <span class="badge bg-<?= $config['class'] ?>-subtle text-<?= $config['class'] ?> px-2 py-1">
                            <i class="<?= $config['icon'] ?> me-1"></i>
                            <?= $config['text'] ?>
                        </span>
                    </td>
                    <td>
                        <div class="d-flex align-items-center">
                            <i class="fas fa-calendar-alt text-muted me-2"></i>
                            <div>
                                <div class="small fw-medium"><?= date('M d, Y', strtotime($dp['created_at'])) ?></div>
                                <div class="text-muted" style="font-size: 10px;"><?= date('H:i', strtotime($dp['created_at'])) ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="d-flex gap-1 flex-wrap">
                            <!-- Mark In Progress -->
                            <?php if ($status === 'pending'): ?>
                            <a href="?route=hospital_admin/update_diet_plan_status&id=<?= $dp['id'] ?>&status=in_progress"
                               class="btn btn-sm btn-outline-info" title="Mark In Progress">
                                <i class="fas fa-play"></i>
                            </a>
                            <?php endif; ?>
                            <!-- Mark Completed -->
                            <?php if ($status !== 'completed'): ?>
                            <a href="?route=hospital_admin/update_diet_plan_status&id=<?= $dp['id'] ?>&status=completed"
                               class="btn btn-sm btn-success" title="Mark Completed">
                                <i class="fas fa-check"></i>
                            </a>
                            <?php endif; ?>
                            <!-- View Details -->
                            <button class="btn btn-sm btn-outline-secondary" title="View Details" 
                                    onclick="alert('Patient: <?= htmlspecialchars($dp['patient_name']) ?>\nGoal: <?= ucwords(str_replace('_', ' ', $dp['goal'] ?? 'N/A')) ?>\nConditions: <?= htmlspecialchars($dp['conditions'] ?? 'None') ?>')">
                                <i class="fas fa-eye"></i>
                            </button>
                            <!-- Delete -->
                            <a href="?route=hospital_admin/delete_diet_plan&id=<?= $dp['id'] ?>"
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('Delete this diet plan request?')"
                               title="Delete">
                                <i class="fas fa-trash"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'views/layouts/footer.php'; ?>
