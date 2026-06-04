<?php include 'views/layouts/header.php'; ?>

<!-- Top Actions -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <a href="?route=admin/add_receptionist" class="btn btn-danger" style="background: #ff4757; color: white; display: flex; align-items: center; gap: 10px;">
        <i class="fas fa-plus"></i> New Receptionist
    </a>
    <div style="display: flex; gap: 15px;">
        <div style="position: relative;">
            <input type="text" placeholder="Search here" class="form-control" style="width: 250px; padding-left: 40px; background: white;">
            <i class="fas fa-search" style="position: absolute; left: 15px; top: 12px; color: #ccc;"></i>
        </div>
    </div>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th><input type="checkbox"> ID</th>
                <th>Name</th>
                <th>Phone</th>
                <th>Created At</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($receptionists as $r): ?>
            <tr>
                <td><input type="checkbox"> <span style="font-weight: 600; color: #666;">#R-<?= str_pad($r['id'], 5, '0', STR_PAD_LEFT) ?></span></td>
                <td style="font-weight: 600; color: #333;"><?= $r['name'] ?></td>
                <td style="color: #666;"><?= $r['phone'] ?? 'No Phone' ?></td>
                <td style="color: #666;"><?= date('d F Y', strtotime($r['created_at'])) ?></td>
                <td>
                    <a href="?route=admin/delete_receptionist&id=<?= $r['id'] ?>" onclick="return confirm('Are you sure you want to permanently delete this receptionist?');" class="btn btn-sm btn-danger text-white" title="Delete Receptionist"><i class="fas fa-trash"></i> Delete</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <?php if(count($receptionists) == 0): ?>
        <div style="text-align: center; padding: 50px; color: #ccc;">No receptionists found.</div>
    <?php endif; ?>
</div>

<?php include 'views/layouts/footer.php'; ?>
