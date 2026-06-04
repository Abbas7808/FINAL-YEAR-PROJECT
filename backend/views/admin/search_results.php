<?php include 'views/layouts/header.php'; ?>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <h4 class="fw-bold mb-4">Search Results for "<?= htmlspecialchars($query) ?>"</h4>

        <?php if(empty($results)): ?>
            <div class="alert alert-info border-0 shadow-sm text-center p-5 rounded-4">
                <i class="fas fa-search fa-3x mb-3 text-muted opacity-50"></i>
                <h5>No results found</h5>
                <p class="mb-0">Try adjusting your search terms.</p>
            </div>
        <?php else: ?>
            <div class="list-group list-group-flush border-top">
                <?php foreach($results as $res): ?>
                    <div class="list-group-item px-0 py-3 d-flex align-items-center justify-content-between border-bottom">
                        <div class="d-flex align-items-center gap-3">
                            <?php if($res['category'] == 'hospital'): ?>
                                <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width:45px;height:45px;">
                                    <i class="fas <?= $res['type'] == 'Clinic' ? 'fa-clinic-medical' : 'fa-hospital' ?>"></i>
                                </div>
                            <?php else: ?>
                                <div class="bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center" style="width:45px;height:45px;">
                                    <i class="fas fa-user-md"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div>
                                <h6 class="mb-1 fw-bold"><?= htmlspecialchars($res['name']) ?></h6>
                                <span class="text-muted small text-capitalize"><?= htmlspecialchars($res['type']) ?></span>
                            </div>
                        </div>
                        
                        <?php if($res['category'] == 'hospital'): ?>
                            <a href="?route=admin/view_hospital_stats&id=<?= $res['id'] ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">View Details</a>
                        <?php else: ?>
                            <a href="?route=admin/doctor_details&id=<?= $res['id'] ?>" class="btn btn-sm btn-outline-info rounded-pill px-3">View Doctor</a>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
