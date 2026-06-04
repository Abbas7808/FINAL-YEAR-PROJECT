<?php include 'views/layouts/header.php'; ?>

<!-- Top Actions -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div style="position: relative;">
        <input type="text" placeholder="Search Regions" class="form-control" style="width: 300px; padding-left: 40px; background: white;">
        <i class="fas fa-search" style="position: absolute; left: 15px; top: 15px; color: #ccc;"></i>
    </div>
    <div style="display: flex; gap: 15px;">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRegionModal" style="background: var(--primary); color: white; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-plus"></i> New Region
        </button>
    </div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Region Name</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($regions) && count($regions) > 0): ?>
                <?php foreach($regions as $r): ?>
                <tr>
                    <td>#<?= $r['id'] ?></td>
                    <td style="font-weight: 600; color: #333;"><?= $r['name'] ?></td>
                    <td style="color: #666;"><?= $r['created_at'] ?? 'N/A' ?></td>
                    <td>
                         <a href="?route=admin/delete_region&id=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center p-4">No regions found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Region Modal -->
<div class="modal fade" id="addRegionModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content" style="border-radius: 20px; border: none;">
      <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 20px;">
        <h5 class="modal-title" style="font-weight: 700;">Add New Region</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="?route=admin/store_region" method="POST">
      <div class="modal-body" style="padding: 30px;">
            <div class="form-group">
                <label class="form-label">Region Name</label>
                <input type="text" name="name" class="form-control" required placeholder="e.g. Kohat City">
            </div>
      </div>
      <div class="modal-footer" style="border-top: none; padding: 0 30px 30px;">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Region</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'views/layouts/footer.php'; ?>
