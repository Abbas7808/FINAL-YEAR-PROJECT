<?php include 'views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="h4 mb-0">Manage Blogs</h2>
        <div>
            <a href="?route=admin/blog_categories" class="btn btn-outline-primary me-2"><i class="fas fa-tags me-2"></i>Categories</a>
            <a href="?route=admin/blog_add" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add New Blog</a>
        </div>
    </div>

    <?php if(isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Title</th>
                            <th>Category</th>
                            <th>Author</th>
                            <th>Date</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($blogs)): foreach($blogs as $blog): ?>
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center">
                                        <?php 
                                            $imgSrc = !empty($blog['image']) ? $blog['image'] : 'https://placehold.co/600x400/eef6ff/2f7bff?text=No+Image';
                                        ?>
                                        <img src="<?= $imgSrc ?>" 
                                             onerror="this.src='https://placehold.co/600x400/eef6ff/2f7bff?text=Error'"
                                             class="rounded me-3" 
                                             style="width: 40px; height: 40px; object-fit: cover;">
                                        <span class="fw-bold"><?= htmlspecialchars($blog['title']) ?></span>
                                    </div>
                                </td>
                                <td><span class="badge bg-soft-primary text-primary"><?= $blog['category_name'] ?? 'Uncategorized' ?></span></td>
                                <td><?= $blog['author_name'] ?></td>
                                <td class="text-muted"><?= date('M d, Y', strtotime($blog['created_at'])) ?></td>
                                <td class="text-end pe-4">
                                    <a href="?route=admin/blog_edit&id=<?= $blog['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                    <a href="?route=admin/blog_delete&id=<?= $blog['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this blog?')"><i class="fas fa-trash"></i></a>
                                </td>
                            </tr>
                        <?php endforeach; else: ?>
                            <tr><td colspan="5" class="text-center py-4 text-muted">No blogs found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
