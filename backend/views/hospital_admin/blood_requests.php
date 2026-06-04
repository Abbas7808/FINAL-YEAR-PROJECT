<?php include 'views/layouts/header.php'; ?>

<?php if (isset($_SESSION['success'])): ?>
<div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div class="card shadow-sm border-0 rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase fw-bold">
                    <tr>
                        <th class="px-4 py-3">Patient</th>
                        <th class="py-3">Blood Group</th>
                        <th class="py-3 text-center">Urgency</th>
                        <th class="py-3">Contact</th>
                        <th class="py-3 text-center">Status</th>
                        <th class="py-3 text-center">Requested</th>
                        <th class="py-3 text-center">Action</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="7" class="text-center py-5">
                                <i class="fas fa-ambulance fa-3x text-light mb-3 d-block"></i>
                                <div class="text-muted">No blood requests at the moment.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $req): ?>
                            <?php $isFulfilled = $req['status'] == 'fulfilled'; ?>
                            <tr>
                                <td class="px-4 py-3 fw-bold text-dark"><?= htmlspecialchars($req['patient_name']) ?></td>
                                <td class="py-3"><span class="badge bg-danger rounded-pill px-3"><?= htmlspecialchars($req['blood_group']) ?></span></td>
                                <td class="py-3 text-center">
                                    <?php
                                        $u_class = 'bg-secondary';
                                        if ($req['urgency'] == 'Urgent') $u_class = 'bg-warning text-dark';
                                        if ($req['urgency'] == 'Critical') $u_class = 'bg-danger text-white';
                                    ?>
                                    <span class="badge <?= $u_class ?> rounded-pill"><?= htmlspecialchars($req['urgency']) ?></span>
                                </td>
                                <td class="py-3"><i class="fas fa-phone-alt me-2 text-primary small"></i><?= htmlspecialchars($req['phone']) ?></td>
                                <td class="py-3 text-center">
                                    <span class="badge bg-<?= $isFulfilled ? 'success' : 'warning text-dark' ?> rounded-pill">
                                        <?= $isFulfilled ? 'Fulfilled' : 'Pending' ?>
                                    </span>
                                </td>
                                <td class="py-3 text-center text-muted small"><?= date('M d, Y', strtotime($req['created_at'])) ?></td>
                                <td class="py-3 text-center">
                                    <?php if (!$isFulfilled): ?>
                                        <a href="?route=hospital_admin/update_blood_request&id=<?= $req['id'] ?>&status=fulfilled"
                                           class="btn btn-sm btn-success rounded-pill px-3"
                                           onclick="return confirm('Mark this request as fulfilled?')">
                                            <i class="fas fa-check me-1"></i> Fulfill
                                        </a>
                                    <?php else: ?>
                                        <a href="?route=hospital_admin/update_blood_request&id=<?= $req['id'] ?>&status=pending"
                                           class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                            <i class="fas fa-undo me-1"></i> Reopen
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
