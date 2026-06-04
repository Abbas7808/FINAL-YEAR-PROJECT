<?php include 'views/layouts/header.php'; ?>

<div class="table-container">
    <div class="table-header">
        <h3>Patient Checkup: <?= $appointment['name'] ?></h3>
        <div>
            <span class="badge waiting">Age: <?= $appointment['age'] ?></span>
            <span class="badge waiting">Gender: <?= $appointment['gender'] ?></span>
        </div>
    </div>

    <form action="?route=doctor/store_diagnosis" method="POST">
        <input type="hidden" name="appointment_id" value="<?= $appointment['id'] ?>">
        
        <div class="form-group">
            <label class="form-label">Diagnosis / Symptoms</label>
            <textarea name="diagnosis" class="form-control" rows="3" placeholder="Fever, Cough..." required></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Medicines (Rx)</label>
            <textarea name="medicines" class="form-control" rows="5" placeholder="1. Paracetamol 500mg - 1+0+1 (3 days)&#10;2. Cough Syrup..." required></textarea>
        </div>

        <div class="form-group">
            <label class="form-label">Advice / Tests</label>
            <textarea name="advice" class="form-control" rows="3" placeholder="Drink warm water, CBC Test..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Save & Complete</button>
    </form>
</div>

<?php include 'views/layouts/footer.php'; ?>
