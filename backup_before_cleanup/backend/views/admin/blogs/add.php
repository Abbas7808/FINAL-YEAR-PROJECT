<?php include 'views/layouts/header.php'; ?>

<!-- CKEditor CDN -->
<script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>

<div class="container-fluid py-4">
    <div class="mb-4">
        <a href="?route=admin/blogs" class="text-decoration-none text-muted">
            <i class="fas fa-arrow-left me-1"></i> Back to Blogs
        </a>
        <h2 class="h4 mt-2">Add New Blog Post</h2>
    </div>

    <form action="?route=admin/blog_store" method="POST" enctype="multipart/form-data">
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Blog Title</label>
                            <input type="text" name="title" class="form-control form-control-lg" placeholder="Enter post title" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Content</label>
                            <textarea name="content" id="editor"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body p-4">
                        <div class="mb-4">
                            <label class="form-label fw-bold">Category</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php if(!empty($categories)): foreach($categories as $cat): ?>
                                    <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Featured Image</label>
                            <div class="input-group">
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                            <small class="text-muted">Recommended size: 1200x800px</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Excerpt</label>
                            <textarea name="excerpt" class="form-control" rows="3" placeholder="Brief summary (optional)"></textarea>
                        </div>

                        <hr>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">Publish Blog</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    ClassicEditor
        .create(document.querySelector('#editor'), {
            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo'],
        })
        .catch(error => {
            console.error(error);
        });
</script>

<style>
    .ck-editor__editable {
        min-height: 400px;
    }
</style>

<?php include 'views/layouts/footer.php'; ?>
