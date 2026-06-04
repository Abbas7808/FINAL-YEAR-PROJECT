<?php include 'views/layouts/header.php'; ?>

<div class="container mt-5">
    <div class="row">
        <!-- Sidebar / Profile Image -->
        <div class="col-md-4">
            <div class="card shadow border-0 text-center p-4">
                <img src="assets/images/<?php echo htmlspecialchars($doctor['profile_image'] ?? 'default_doctor.png'); ?>" 
                     class="rounded-circle mx-auto mb-3" width="150" height="150" style="object-fit: cover;" alt="Doctor Image">
                <h4>Dr. <?php echo htmlspecialchars($doctor['name']); ?></h4>
                <p class="text-muted"><?php echo htmlspecialchars($doctor['specialization']); ?></p>
                
                <hr>
                
                <div class="text-start">
                    <p><strong>Experience:</strong> <?php echo htmlspecialchars($doctor['experience_years']); ?> Years</p>
                    <p><strong>Contact:</strong> <?php echo htmlspecialchars($doctor['contact_number'] ?? 'N/A'); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($doctor['email']); ?></p>
                </div>

                <a href="?route=patient/book_appointment&doctor_id=<?php echo $doctor['user_id']; ?>" class="btn btn-success w-100 mt-3">Book Appointment</a>
            </div>
        </div>

        <!-- Details Section -->
        <div class="col-md-8">
            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-white border-bottom-0">
                    <h5 class="mb-0 text-primary">About Doctor</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        <?php echo nl2br(htmlspecialchars($doctor['bio'] ?? 'No biography available for this doctor.')); ?>
                    </p>
                </div>
            </div>

            <div class="card shadow border-0">
                <div class="card-header bg-white border-bottom-0">
                    <h5 class="mb-0 text-primary">Professional Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Qualification:</div>
                        <div class="col-sm-8"><?php echo htmlspecialchars($doctor['qualification'] ?? 'N/A'); ?></div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-sm-4 fw-bold">Joined:</div>
                        <div class="col-sm-8"><?php echo htmlspecialchars(date('F Y', strtotime($doctor['created_at']))); ?></div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4 text-end">
                <a href="?route=doctors/index" class="btn btn-secondary">Back to List</a>
            </div>
        </div>
    </div>
</div>

<?php include 'views/layouts/footer.php'; ?>
