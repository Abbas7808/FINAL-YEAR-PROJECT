<?php include 'views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Blog Categories</h2>
        <a href="?route=admin/blog_category_add" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add New Category</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($categories)): foreach($categories as $cat): ?>
                            <tr>
                                <td class="ps-4"><?= $cat['id'] ?></td>
                                <td class="fw-bold"><?= $cat['name'] ?></td>
                                <td><code><?= $cat['slug'] ?></code></td>
                                <td class="text-end pe-4">
                                    <a href="?route=admin/blog_category_delete&id=<?= $cat['id'] ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('Are you sure you want to delete this category?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">No categories found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
