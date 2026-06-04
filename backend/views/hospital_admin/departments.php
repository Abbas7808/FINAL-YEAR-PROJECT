<?php include 'views/layouts/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h2>Departments</h2>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDeptModal">
        <i class="fas fa-plus"></i> Add Department
    </button>
</div>

<!-- Departments List -->
<div class="card p-20">
    <table class="table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>Description</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($departments) && count($departments) > 0): ?>
                <?php foreach($departments as $d): ?>
                <tr>
                    <td>#<?= $d['id'] ?></td>
                    <td style="font-weight: 600;"><?= $d['name'] ?></td>
                    <td><?= $d['description'] ?></td>
                    <td>
                         <a href="?route=hospital_admin/delete_department&id=<?= $d['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center">No departments found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Department Modal -->
<div class="modal fade" id="addDeptModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add New Department</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="?route=hospital_admin/store_department" method="POST">
      <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Department Name</label>
                <input type="text" name="name" class="form-control" required placeholder="e.g. Cardiology">
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Brief description..."></textarea>
            </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Department</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'views/layouts/footer.php'; ?>
