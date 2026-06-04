<?php include 'views/layouts/header.php'; ?>

<?php if(isset($db_error)): ?>
<div style="background:#fff3cd;color:#856404;padding:10px 15px;border-radius:8px;margin-bottom:15px;font-size:13px;">⚠️ <?= $db_error ?></div>
<?php endif; ?>

<div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2 mb-4">
    <h3 class="mb-0 fw-bold">Appointments</h3>
    <span class="badge bg-primary fs-6 py-2 px-3 rounded-pill"><?= $total_count ?? count($appointments ?? []) ?> Total</span>
</div>

<!-- Desktop Table Layout (Visible on medium and larger screens) -->
<div class="table-container d-none d-md-block">
    <table>
        <thead>
            <tr>
                <th>Token</th>
                <th>Patient</th>
                <th>Phone</th>
                <th>Doctor</th>
                <th>Type</th>
                <th>Date</th>
                <th>Time</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($appointments)): ?>
                <tr>
                    <td colspan="8" class="text-center p-5">
                        <i class="fas fa-calendar-times fa-3x text-muted d-block mb-3" style="opacity:0.3;"></i>
                        <div class="text-muted">No appointments found.</div>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($appointments as $a): ?>
                <?php
                    $st = strtolower(trim($a['status'] ?? 'pending'));
                    $styles = [
                        'pending'   => 'background:#fff3cd;color:#856404;',
                        'confirmed' => 'background:#d1e7dd;color:#0f5132;',
                        'completed' => 'background:#cff4fc;color:#055160;',
                        'cancelled' => 'background:#f8d7da;color:#842029;',
                    ];
                    $badgeStyle = $styles[$st] ?? 'background:#e2e3e5;color:#383d41;';
                ?>
                <tr>
                    <td><span class="badge bg-secondary">#<?= htmlspecialchars($a['token_number'] ?? '—') ?></span></td>
                    <td><strong><?= htmlspecialchars($a['patient_name'] ?? 'Unknown') ?></strong></td>
                    <td><small class="text-muted"><?= htmlspecialchars($a['patient_phone'] ?? '—') ?></small></td>
                    <td><small><?= htmlspecialchars($a['doctor_name'] ?? '—') ?></small></td>
                    <td>
                        <span class="badge" style="background:#cff4fc;color:#055160;">
                            <?= ucfirst($a['type'] ?? 'Consultation') ?>
                        </span>
                    </td>
                    <td><small><?= $a['date'] ? date('M d, Y', strtotime($a['date'])) : '—' ?></small></td>
                    <td>
                        <?php
                            $ts = $a['time_slot'] ?? '';
                            $timeLabel = '—';
                            if ($ts === 'morning')   $timeLabel = '9 AM – 12 PM';
                            elseif ($ts === 'afternoon') $timeLabel = '12 PM – 5 PM';
                            elseif ($ts === 'evening')   $timeLabel = '5 PM – 9 PM';
                            elseif (!empty($ts))         $timeLabel = htmlspecialchars($ts);
                        ?>
                        <small><?= $timeLabel ?></small>
                    </td>
                    <td>
                        <span class="badge" style="<?= $badgeStyle ?>padding:5px 10px;border-radius:6px;font-weight:600;">
                            <?= ucfirst($st) ?>
                        </span>
                    </td>
                    <td>
                        <div style="display:flex;gap:5px;flex-wrap:wrap;">
                            <?php if ($st === 'pending' || $st === ''): ?>
                                <a href="?route=hospital_admin/update_appointment&id=<?= $a['id'] ?>&status=confirmed"
                                   class="btn btn-sm btn-success" title="Confirm">
                                    <i class="fas fa-check"></i> Confirm
                                </a>
                                <a href="?route=hospital_admin/update_appointment&id=<?= $a['id'] ?>&status=cancelled"
                                   class="btn btn-sm btn-danger" title="Cancel"
                                   onclick="return confirm('Cancel this appointment?')">
                                    <i class="fas fa-times"></i>
                                </a>
                            <?php elseif ($st === 'confirmed'): ?>
                                <a href="?route=hospital_admin/update_appointment&id=<?= $a['id'] ?>&status=completed"
                                   class="btn btn-sm btn-primary" title="Mark Done">
                                    <i class="fas fa-check-double"></i> Done
                                </a>
                                <a href="?route=hospital_admin/update_appointment&id=<?= $a['id'] ?>&status=cancelled"
                                   class="btn btn-sm btn-outline-danger" title="Cancel"
                                   onclick="return confirm('Cancel this appointment?')">
                                    <i class="fas fa-times"></i>
                                </a>
                            <?php else: ?>
                                <div class="d-flex align-items-center gap-1">
                                    <span class="text-muted small">—</span>
                                    <?php if ($st !== 'completed' && $st !== 'cancelled'): ?>
                                        <a href="?route=hospital_admin/update_appointment&id=<?= $a['id'] ?>&status=cancelled" 
                                           class="btn btn-sm btn-link text-danger p-0" title="Force Cancel">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Mobile Cards Layout (Visible on screens smaller than 768px) -->
<div class="d-md-none">
    <?php if (empty($appointments)): ?>
        <div class="card p-5 text-center border-0 shadow-sm rounded-4" style="background: var(--bg-card);">
            <i class="fas fa-calendar-times fa-3x text-muted d-block mb-3" style="opacity:0.3;"></i>
            <div class="text-muted">No appointments found.</div>
        </div>
    <?php else: ?>
        <?php foreach ($appointments as $a): ?>
            <?php
                $st = strtolower(trim($a['status'] ?? 'pending'));
                $styles = [
                    'pending'   => 'background:#fff3cd;color:#856404;',
                    'confirmed' => 'background:#d1e7dd;color:#0f5132;',
                    'completed' => 'background:#cff4fc;color:#055160;',
                    'cancelled' => 'background:#f8d7da;color:#842029;',
                ];
                $badgeStyle = $styles[$st] ?? 'background:#e2e3e5;color:#383d41;';
                
                // Card border color indicator based on status
                $cardBorderColor = '#e2e3e5';
                if ($st === 'confirmed') $cardBorderColor = '#198754';
                elseif ($st === 'cancelled') $cardBorderColor = '#dc3545';
                elseif ($st === 'completed') $cardBorderColor = '#0dcaf0';
                elseif ($st === 'pending') $cardBorderColor = '#ffc107';
            ?>
            <div class="card appointment-card mb-3 border-0" style="background: var(--bg-card); border-left: 5px solid <?= $cardBorderColor ?> !important;">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-secondary">#<?= htmlspecialchars($a['token_number'] ?? '—') ?></span>
                        <span class="badge" style="<?= $badgeStyle ?>padding:4px 8px;border-radius:6px;font-weight:600;font-size:11px;">
                            <?= ucfirst($st) ?>
                        </span>
                    </div>
                    
                    <h5 class="mb-1 text-dark fw-bold" style="font-size:16px;"><?= htmlspecialchars($a['patient_name'] ?? 'Unknown') ?></h5>
                    <div class="small text-muted mb-3">
                        <i class="fas fa-phone-alt me-1"></i> <?= htmlspecialchars($a['patient_phone'] ?? '—') ?>
                    </div>
                    
                    <div class="row g-2 mb-3 bg-light p-2 rounded-3 mx-0 border" style="background-color: var(--bg-light) !important; border-color: var(--border-color) !important;">
                        <div class="col-6">
                            <div class="text-muted small" style="font-size:10px;text-transform:uppercase;">Doctor</div>
                            <div class="fw-semibold text-dark small text-truncate"><?= htmlspecialchars($a['doctor_name'] ?? '—') ?></div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small" style="font-size:10px;text-transform:uppercase;">Type</div>
                            <div>
                                <span class="badge" style="background:#cff4fc;color:#055160;font-size:10px;padding:2px 6px;">
                                    <?= ucfirst($a['type'] ?? 'Consultation') ?>
                                </span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small" style="font-size:10px;text-transform:uppercase;">Date</div>
                            <div class="fw-semibold text-dark small">
                                <i class="far fa-calendar-alt me-1 text-primary"></i> <?= $a['date'] ? date('M d, Y', strtotime($a['date'])) : '—' ?>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="text-muted small" style="font-size:10px;text-transform:uppercase;">Time</div>
                            <div class="fw-semibold text-dark small">
                                <?php
                                    $ts = $a['time_slot'] ?? '';
                                    $timeLabel = '—';
                                    if ($ts === 'morning')   $timeLabel = '9 AM – 12 PM';
                                    elseif ($ts === 'afternoon') $timeLabel = '12 PM – 5 PM';
                                    elseif ($ts === 'evening')   $timeLabel = '5 PM – 9 PM';
                                    elseif (!empty($ts))         $timeLabel = htmlspecialchars($ts);
                                ?>
                                <i class="far fa-clock me-1 text-info"></i> <?= $timeLabel ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <?php if ($st === 'pending' || $st === ''): ?>
                            <a href="?route=hospital_admin/update_appointment&id=<?= $a['id'] ?>&status=confirmed"
                               class="btn btn-success btn-sm flex-grow-1 py-2 rounded-3">
                                <i class="fas fa-check me-1"></i> Confirm
                            </a>
                            <a href="?route=hospital_admin/update_appointment&id=<?= $a['id'] ?>&status=cancelled"
                               class="btn btn-danger btn-sm py-2 rounded-3" title="Cancel"
                               onclick="return confirm('Cancel this appointment?')">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php elseif ($st === 'confirmed'): ?>
                            <a href="?route=hospital_admin/update_appointment&id=<?= $a['id'] ?>&status=completed"
                               class="btn btn-primary btn-sm flex-grow-1 py-2 rounded-3">
                                <i class="fas fa-check-double me-1"></i> Mark Done
                            </a>
                            <a href="?route=hospital_admin/update_appointment&id=<?= $a['id'] ?>&status=cancelled"
                               class="btn btn-outline-danger btn-sm py-2 rounded-3" title="Cancel"
                               onclick="return confirm('Cancel this appointment?')">
                                <i class="fas fa-times"></i>
                            </a>
                        <?php else: ?>
                            <div class="text-center text-muted small py-1 w-100 bg-light rounded-3" style="font-size:11px;">
                                No actions available
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php
$totalPages  = $total_pages ?? 1;
$currentPage = $current_page ?? 1;
if ($totalPages > 1):
?>
<nav class="mt-4 d-flex justify-content-center">
    <ul class="pagination flex-wrap justify-content-center">
        <?php if ($currentPage > 1): ?>
        <li class="page-item">
            <a class="page-link" href="?route=hospital_admin/appointments&page=<?= $currentPage - 1 ?>">‹ Prev</a>
        </li>
        <?php endif; ?>

        <?php for ($p = 1; $p <= $totalPages; $p++): ?>
        <li class="page-item <?= $p === $currentPage ? 'active' : '' ?>">
            <a class="page-link" href="?route=hospital_admin/appointments&page=<?= $p ?>"><?= $p ?></a>
        </li>
        <?php endfor; ?>

        <?php if ($currentPage < $totalPages): ?>
        <li class="page-item">
            <a class="page-link" href="?route=hospital_admin/appointments&page=<?= $currentPage + 1 ?>">Next ›</a>
        </li>
        <?php endif; ?>
    </ul>
</nav>
<?php endif; ?>

<?php include 'views/layouts/footer.php'; ?>
