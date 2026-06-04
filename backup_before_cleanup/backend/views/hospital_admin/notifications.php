<?php include 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>All Notifications</h3>
</div>

<div class="card border-0 shadow-sm rounded-4 overflow-hidden">
    <div class="list-group list-group-flush">
        <?php if (empty($notifications_all)): ?>
            <div class="text-center p-5 text-muted">
                <i class="fas fa-bell-slash fa-3x mb-3 opacity-25"></i>
                <p>No notifications yet.</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications_all as $n): ?>
                <div class="list-group-item p-4 border-start border-4 border-<?= $n['color'] ?? 'secondary' ?>">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="d-flex gap-3">
                            <div class="notif-icon-wrap notif-icon-<?= $n['color'] ?? 'secondary' ?> flex-shrink-0">
                                <i class="<?= $n['icon'] ?? 'fas fa-info-circle' ?>"></i>
                            </div>
                            <div>
                                <h6 class="mb-1 fw-bold"><?= htmlspecialchars($n['title']) ?></h6>
                                <p class="mb-2 text-muted"><?= htmlspecialchars($n['message']) ?></p>
                                <div class="d-flex align-items-center gap-3">
                                    <span class="small text-muted">
                                        <i class="far fa-clock me-1"></i>
                                        <?= date('M d, Y h:i A', strtotime($n['time'])) ?>
                                    </span>
                                    <?php if (!empty($n['link'])): 
                                        $readRoute = "?route=hospital_admin/read_notification&id=" . ($n['id'] ?? 0);
                                    ?>
                                        <a href="<?= $readRoute ?>" class="btn btn-sm btn-light rounded-pill px-3">
                                            View Details <i class="fas fa-chevron-right ms-1" style="font-size:10px;"></i>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
