<?php include 'views/layouts/header.php'; ?>

<!-- Top Actions -->
<div style="display: flex; justify-content: flex-end; align-items: center; margin-bottom: 30px;">
    <div style="display: flex; gap: 15px;">
        <div style="position: relative;">
            <input type="text" placeholder="Search here" class="form-control" style="width: 250px; padding-left: 40px; background: white;">
            <i class="fas fa-search" style="position: absolute; left: 15px; top: 12px; color: #ccc;"></i>
        </div>
        <button class="btn" style="background: white; border: 1px solid #ddd; color: #666;"><i class="fas fa-sliders-h"></i> Filter</button>
        <button class="btn" style="background: white; border: 1px solid #ddd; color: #666;">Newest <i class="fas fa-chevron-down"></i></button>
    </div>
</div>

<!-- Doctors Grid -->
<?php
// Group doctors by first letter for the "A", "B" headers
$grouped_doctors = [];
if (!empty($doctors)) {
    foreach ($doctors as $d) {
        $firstLetter = strtoupper(substr($d['name'], 0, 1));
        $grouped_doctors[$firstLetter][] = $d;
    }
    ksort($grouped_doctors);
}
?>

<?php if(empty($grouped_doctors)): ?>
    <div style="text-align: center; color: #888; margin-top: 50px;">No Doctors Found.</div>
<?php else: ?>
    <?php foreach($grouped_doctors as $letter => $docs): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="font-weight: 700; font-size: 20px;"><?= $letter ?></h3>
            <span style="color: #888; font-size: 12px;"><?= count($docs) ?> Doctors <i class="fas fa-chevron-down"></i></span>
        </div>
        <hr style="border: 0; border-top: 1px solid #eee; margin-bottom: 20px;">

        <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); gap: 30px; margin-bottom: 40px;">
            <?php foreach($docs as $doc): ?>
            <div class="card p-20" style="display: flex; align-items: flex-start; gap: 15px; border-radius: 12px;">
                <div style="width: 80px; height: 80px; background: #ddd; border-radius: 12px; overflow: hidden; position: relative;">
                    <?php 
                        $dummyDocs = [
                            '../frontend/assets/doctor-fatima.jpg',
                            '../frontend/assets/doctor-male-1.jpg',
                            '../frontend/assets/doctor-male-2.jpg',
                            '../frontend/assets/doctor-female-1.jpg',
                            '../frontend/assets/default-doctor.jpg'
                        ];
                        $imgName = $doc['profile_image'] ?? '';
                        $imgName = $imgName ? basename($imgName) : '';
                        $imgPath = 'assets/uploads/' . $imgName;
                        $frontImgPath = '../frontend/assets/' . $imgName;
                        $idx = $doc['id'] % count($dummyDocs);
                        $finalSrc = $dummyDocs[$idx];

                        if(!empty($doc['profile_image']) && file_exists($imgPath)): 
                            $finalSrc = $imgPath;
                        elseif(!empty($doc['profile_image']) && file_exists($frontImgPath)): 
                            $finalSrc = $frontImgPath;
                        endif;
                    ?>
                    <img src="<?= $finalSrc ?>?v=1775114101" alt="<?= $doc['name'] ?>" style="width: 100%; height: 100%; object-fit: cover;">
                </div>
                <div style="flex: 1;">
                    <a href="?route=admin/doctor_details&id=<?= $doc['id'] ?>" style="text-decoration: none; color: inherit;">
                        <h4 style="margin: 0; font-size: 16px;"><?= $doc['name'] ?></h4>
                    </a>
                    <p style="margin: 5px 0; font-size: 12px; color: #666;"><?= $doc['specialization'] ?> <span style="color: #ddd;">|</span> <span style="color: #ccc;">#D-<?= str_pad($doc['id'], 5, '0', STR_PAD_LEFT) ?></span></p>
                    <div style="display: flex; align-items: center; gap: 5px; color: #ffb800; font-size: 12px;">
                        <?php 
                        $rating = $doc['rating'] ?? 5.0;
                        for($i=0; $i<5; $i++) echo ($i < floor($rating)) ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                        ?>
                        <span style="color: #888; margin-left: 5px;"><?= $doc['reviews_count'] ?? 0 ?> reviews</span>
                    </div>
                </div>
                <!-- Social Icons -->
                <div style="display: flex; gap: 10px; color: #2d2558;">
                    <a href="#" class="btn-icon-small"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="btn-icon-small"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="btn-icon-small"><i class="fab fa-twitter"></i></a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<div class="card p-20">
    <!-- Filters -->
    <form method="GET" action="" style="display: flex; gap: 15px; margin-bottom: 20px; align-items: flex-end;">
        <input type="hidden" name="route" value="admin/doctors">
        <div style="flex: 1;">
            <label class="form-label">Filter by Type</label>
            <select name="type" class="form-control" onchange="this.form.submit()">
                <option value="">All Types</option>
                <option value="Hospital" <?= ($filter_type ?? '') == 'Hospital' ? 'selected' : '' ?>>Hospitals Only</option>
                <option value="Clinic" <?= ($filter_type ?? '') == 'Clinic' ? 'selected' : '' ?>>Clinics Only</option>
            </select>
        </div>
        <div style="flex: 1;">
            <label class="form-label">Filter by Facility</label>
            <select name="hospital_id" class="form-control" onchange="this.form.submit()">
                <option value="">All Facilities</option>
                <?php if(isset($hospitals_list)): foreach($hospitals_list as $h): ?>
                    <option value="<?= $h['id'] ?>" <?= ($filter_hospital_id ?? '') == $h['id'] ? 'selected' : '' ?>>
                        <?= $h['name'] ?> (<?= $h['type'] ?>)
                    </option>
                <?php endforeach; endif; ?>
            </select>
        </div>
        <div>
             <a href="?route=admin/doctors" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <table class="table">
        <thead>
            <tr>
                <th>Doctor Name</th>
                <th>Specialization</th>
                <th>Affiliation</th>
                <th>Phone</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($doctors) && count($doctors) > 0): ?>
                <?php foreach($doctors as $d): ?>
                <tr>
                    <td>
                        <div style="font-weight: 600;"><?= $d['name'] ?></div>
                        <small style="color: grey;"><?= $d['phone'] ?? 'No Phone' ?></small>
                    </td>
                    <td><?= $d['specialization'] ?></td>
                    <td>
                        <?php if(!empty($d['hospital_name'])): ?>
                            <span class="badge" style="background: #eef6ff; color: var(--primary);">
                                <?= $d['hospital_name'] ?> 
                                <span style="font-size: 0.8em; opacity: 0.7;">(<?= $d['hospital_type'] ?>)</span>
                            </span>
                        <?php else: ?>
                            <span class="text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td><?= $d['phone'] ?></td>
                    <td>
                        <div class="d-flex gap-1 justify-content-start">
                            <a href="?route=admin/doctor_details&id=<?= $d['id'] ?>" class="btn btn-sm btn-info text-white" title="View Details"><i class="fas fa-eye"></i></a>
                            <a href="?route=admin/delete_doctor&id=<?= $d['id'] ?>" onclick="return confirm('Are you sure you want to permanently delete this doctor?');" class="btn btn-sm btn-danger text-white" title="Delete Doctor"><i class="fas fa-trash"></i></a>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center">No doctors found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<style>
.btn-icon-small { 
    width: 25px; height: 25px; border: 1px solid #eee; border-radius: 50%; 
    display: flex; align-items: center; justify-content: center; 
    color: #2d2558; text-decoration: none; font-size: 10px;
}
.btn-icon-small:hover { background: #2d2558; color: white; }
</style>

<?php include 'views/layouts/footer.php'; ?>
