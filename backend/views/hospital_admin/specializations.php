<?php include 'views/layouts/header.php'; ?>

<div class="container-fluid">
    <div class="row">
        <!-- Add Specialization Form -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm" style="border-radius:15px;">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0 fw-bold">Add Specialization</h5>
                </div>
                <div class="card-body">
                    <form action="?route=hospital_admin/store_specialization" method="POST">
                        <div class="mb-3">
                            <label class="form-label">Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Cardiology">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Description..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Add Specialization</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Specializations List -->
        <div class="col-md-8">
            <div class="card border-0 shadow-sm" style="border-radius:15px;">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Specializations List</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Name</th>
                                    <th>Description</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($specializations)): ?>
                                    <?php foreach($specializations as $spec): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold"><?= htmlspecialchars($spec['name']) ?></td>
                                        <td><?= htmlspecialchars($spec['description']) ?></td>
                                        <td class="text-end pe-4">
                                            <a href="?route=hospital_admin/delete_specialization&id=<?= $spec['id'] ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Delete this specialization?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="3" class="text-center py-4">No specializations found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
