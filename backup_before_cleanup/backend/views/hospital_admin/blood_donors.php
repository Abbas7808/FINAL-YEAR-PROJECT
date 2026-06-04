<?php include 'views/layouts/header.php'; ?>

<div class="card shadow-sm border-0 rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light text-muted small text-uppercase fw-bold">
                    <tr>
                        <th class="px-4 py-3">Donor Name</th>
                        <th class="py-3">Blood Group</th>
                        <th class="py-3">Contact</th>
                        <th class="py-3">Location</th>
                        <th class="py-3 text-center">Status</th>
                        <th class="py-3 text-center">Registered</th>
                    </tr>
                </thead>
                <tbody class="border-top-0">
                    <?php if (empty($donors)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="fas fa-hand-holding-heart fa-3x text-light mb-3 d-block"></i>
                                <div class="text-muted">No donors registered with your hospital yet.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($donors as $donor): ?>
                            <tr>
                                <td class="px-4 py-3">
                                    <div class="fw-bold text-dark"><?= htmlspecialchars($donor['name']) ?></div>
                                    <div class="small text-muted"><?= htmlspecialchars($donor['age']) ?> years old</div>
                                </td>
                                <td class="py-3"><span class="badge bg-danger rounded-pill px-3"><?= htmlspecialchars($donor['blood_group']) ?></span></td>
                                <td class="py-3">
                                    <div class="text-dark py-1"><i class="fas fa-phone-alt me-2 text-primary small"></i><?= htmlspecialchars($donor['phone']) ?></div>
                                </td>
                                <td class="py-3 text-muted small"><?= htmlspecialchars($donor['location']) ?></td>
                                <td class="py-3 text-center">
                                    <span class="badge bg-<?= $donor['status'] == 'active' ? 'success' : 'secondary' ?>-subtle text-<?= $donor['status'] == 'active' ? 'success' : 'secondary' ?> rounded-pill">
                                        <?= ucfirst($donor['status']) ?>
                                    </span>
                                </td>
                                <td class="py-3 text-center text-muted small"><?= date('M d, Y', strtotime($donor['created_at'])) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
