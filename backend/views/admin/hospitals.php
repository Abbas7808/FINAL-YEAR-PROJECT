<?php include 'views/layouts/header.php'; ?>

<!-- Top Actions -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <div style="position: relative;">
        <input type="text" placeholder="Search Hospitals" class="form-control" style="width: 300px; padding-left: 40px; background: white;">
        <i class="fas fa-search" style="position: absolute; left: 15px; top: 15px; color: #ccc;"></i>
    </div>
    <div style="display: flex; gap: 15px;">
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addHospitalModal" style="background: var(--primary); color: white; display: flex; align-items: center; gap: 10px;">
            <i class="fas fa-plus"></i> New Hospital
        </button>
    </div>
</div>

<?php if(isset($_SESSION['success'])): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= $_SESSION['success'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['success']); ?>
<?php endif; ?>

<?php if(isset($_SESSION['error'])): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= $_SESSION['error'] ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Slug</th>
                <th>Image</th>
                <th>Hospital Name</th>
                <th>Region</th>
                <th>Address</th>
                <th>Contact</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($hospitals) && count($hospitals) > 0): ?>
                <?php foreach($hospitals as $h): ?>
                <tr>
                    <td>#<?= $h['id'] ?></td>
                    <td><a href="../../frontend/hospital_profile.html?slug=<?= $h['slug'] ?? '' ?>" target="_blank" style="color: #d63384; background: #fff0f6; padding: 2px 5px; border-radius: 4px; text-decoration: none; display: inline-block;">
                        <i class="fas fa-external-link-alt" style="font-size: 10px; margin-right: 3px;"></i> <?= $h['slug'] ?? '-' ?>
                    </a></td>
                    <td>
                        <?php 
                            $dummyHosp = [
                                '../frontend/assets/hospital-1.png',
                                '../frontend/assets/hospital-2.png',
                                '../frontend/assets/hospital-3.png',
                                '../frontend/assets/hospital-4.png',
                                '../frontend/assets/hospital-5.png'
                            ];
                            $imgName = $h['image'] ?? '';
                            $imgName = $imgName ? basename($imgName) : '';
                            $imgPath = 'assets/uploads/hospitals/' . $imgName;
                            $frontImgPath = '../frontend/assets/' . $imgName;
                            $idx = $h['id'] % count($dummyHosp);
                            $finalSrc = $dummyHosp[$idx];

                            if(!empty($h['image']) && file_exists($imgPath)): 
                                $finalSrc = $imgPath;
                            elseif(!empty($h['image']) && file_exists($frontImgPath)): 
                                $finalSrc = $frontImgPath;
                            endif;
                        ?>
                        <img src="<?= $finalSrc ?>?v=1775114100" alt="Hospital" style="width: 40px; height: 40px; border-radius: 8px; object-fit: cover;">
                    </td>
                    <td style="font-weight: 600; color: #333;"><?= $h['name'] ?></td>
                    <td><span class="badge" style="background: #eef6ff; color: var(--primary);"><?= $h['region_name'] ?? 'N/A' ?></span></td>
                    <td style="color: #666;"><?= $h['address'] ?></td>
                    <td style="color: #666;"><?= $h['contact_info'] ?></td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" onclick="copyLoginUrl('<?= $h['slug'] ?>')" title="Copy Login URL"><i class="fas fa-key"></i></button>
                        <?php 
                            $status = $h['subscription_status'] ?: 'inactive'; 
                            $badgeColor = ($status == 'active') ? '#e1f7e3' : '#fff0f1';
                            $textColor = ($status == 'active') ? '#34c759' : '#ff3b30';
                            $icon = ($status == 'active') ? 'fa-check-circle' : 'fa-times-circle';
                        ?>
                        <a href="?route=admin/toggle_status&id=<?= $h['id'] ?>" class="badge" style="text-decoration: none; background: <?= $badgeColor ?>; color: <?= $textColor ?>; display: inline-flex; align-items: center; gap: 5px;">
                            <i class="fas <?= $icon ?>"></i> <?= ucfirst($status) ?>
                        </a>
                    </td>
                    <td>
                         <button type="button" class="btn btn-sm btn-warning text-white" 
                            onclick="editHospital(<?= htmlspecialchars(json_encode($h)) ?>)"
                            title="Edit">
                            <i class="fas fa-edit"></i>
                         </button>
                         <a href="?route=admin/delete_hospital&id=<?= $h['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6" class="text-center p-4">No hospitals found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Hospital Modal -->
<div class="modal fade" id="addHospitalModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="border-radius: 20px; border: none;">
      <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 20px;">
        <h5 class="modal-title" style="font-weight: 700;">Add New Hospital</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="?route=admin/store_hospital" method="POST" enctype="multipart/form-data">
      <div class="modal-body" style="padding: 30px;">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Hospital Name</label>
                    <input type="text" name="name" class="form-control" required placeholder="e.g. KDA Teaching Hospital">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone Number (Login)</label>
                    <input type="tel" name="phone" class="form-control" required placeholder="+1234567890">
                </div>
                 <div class="col-md-6">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required placeholder="******">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Region</label>
                    <select name="region_id" class="form-control" required>
                        <option value="">Select Region</option>
                        <?php if(isset($regions)): foreach($regions as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= $r['name'] ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
                <input type="hidden" name="type" value="Hospital">
                <div class="col-md-6">
                    <label class="form-label">Contact Info</label>
                    <input type="text" name="contact_info" class="form-control" placeholder="Phone or Email">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" class="form-control" placeholder="Full Address">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="About the facility..."></textarea>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Cover Image</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
            </div>
      </div>
      <div class="modal-footer" style="border-top: none; padding: 0 30px 30px;">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Hospital</button>
      </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Hospital Modal -->
<div class="modal fade" id="editHospitalModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" style="border-radius: 20px; border: none;">
      <div class="modal-header" style="border-bottom: 1px solid #f0f0f0; padding: 20px;">
        <h5 class="modal-title" style="font-weight: 700;">Edit Hospital</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="?route=admin/update_hospital" method="POST" enctype="multipart/form-data">
        <input type="hidden" name="id" id="edit_id">
        <div class="modal-body" style="padding: 30px;">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Hospital Name</label>
                    <input type="text" name="name" id="edit_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Region</label>
                    <select name="region_id" id="edit_region_id" class="form-control" required>
                        <option value="">Select Region</option>
                        <?php if(isset($regions)): foreach($regions as $r): ?>
                            <option value="<?= $r['id'] ?>"><?= $r['name'] ?></option>
                        <?php endforeach; endif; ?>
                    </select>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Contact Info</label>
                    <input type="text" name="contact_info" id="edit_contact_info" class="form-control">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Address</label>
                    <input type="text" name="address" id="edit_address" class="form-control">
                </div>
                <div class="col-md-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="edit_description" class="form-control" rows="3"></textarea>
                </div>
                <div class="col-md-12">
                    <label class="form-label">Change Cover Image (Optional)</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>
            </div>
      </div>
      <div class="modal-footer" style="border-top: none; padding: 0 30px 30px;">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Update Hospital</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script>
function copyLoginUrl(slug) {
    let url = "http://" + window.location.host + window.location.pathname + "?route=auth/login";
    if(slug) {
        url += "&slug=" + slug;
    }
    navigator.clipboard.writeText(url).then(() => {
        alert("Login URL copied: " + url);
    });
}

function editHospital(data) {
    document.getElementById('edit_id').value = data.id;
    document.getElementById('edit_name').value = data.name;
    document.getElementById('edit_region_id').value = data.region_id;
    document.getElementById('edit_contact_info').value = data.contact_info;
    document.getElementById('edit_address').value = data.address;
    document.getElementById('edit_description').value = data.description || '';
    
    var myModal = new bootstrap.Modal(document.getElementById('editHospitalModal'));
    myModal.show();
}
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'views/layouts/footer.php'; ?>
