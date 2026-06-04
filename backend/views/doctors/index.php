<?php include 'views/layouts/header.php'; ?>

<div class="container mt-4">
    <h2 class="mb-4">Find a Doctor</h2>
    
    <div class="card shadow">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>Image</th>
                            <th>Doctor Name</th>
                            <th>Specialization</th>
                            <th>Experience</th>
                            <th>Hospital</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($doctors)): ?>
                            <?php foreach ($doctors as $doctor): ?>
                                <tr>
                                    <td>
                                        <img src="assets/images/<?php echo htmlspecialchars($doctor['profile_image'] ?? 'default_doctor.png'); ?>" 
                                             class="rounded-circle" width="50" height="50" alt="Doctor">
                                    </td>
                                    <td>Dr. <?php echo htmlspecialchars($doctor['name']); ?></td>
                                    <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                                    <td><?php echo htmlspecialchars($doctor['experience_years']); ?> Years</td>
                                    <td><?php echo htmlspecialchars($doctor['hospital_name'] ?? 'Not Assigned'); ?></td>
                                    <td>
                                        <a href="?route=doctors/show&id=<?php echo $doctor['id']; ?>" class="btn btn-primary btn-sm">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">No doctors found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
