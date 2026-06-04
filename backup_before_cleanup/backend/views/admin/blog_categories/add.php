<?php include 'views/layouts/header.php'; ?>

<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="?route=admin/blog_categories" class="text-decoration-none text-muted">
            <i class="fas fa-arrow-left me-1"></i> Back to Categories
        </a>
        <h2 class="h4 mt-2">Add New Category</h2>
    </div>

    <div class="card shadow-sm border-0" style="max-width: 600px;">
        <div class="card-body p-4">
            <form action="?route=admin/blog_category_store" method="POST">
                <div class="mb-3">
                    <label class="form-label fw-bold">Category Name</label>
                    <input type="text" name="name" class="form-control" placeholder="e.g. Health Tips" required autofocus>
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Add Category</button>
            </form>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
