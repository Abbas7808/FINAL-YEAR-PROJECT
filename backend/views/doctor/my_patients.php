<?php include 'views/layouts/header.php'; ?>

<div class="table-container">
    <div class="table-header">
        <h3>My Patients</h3>
    </div>
    <table>
        <thead>
            <tr>
                <th>Patient Name</th>
                <th>Age / Gender</th>
                <th>Contact</th>
                <th>Last Visit</th>
            </tr>
        </thead>
        <tbody>
            <?php if(empty($patients)): ?>
            <tr>
                <td colspan="4" style="text-align: center; padding: 20px;">No patients found.</td>
            </tr>
            <?php else: ?>
                <?php foreach($patients as $patient): ?>
                <tr>
                    <td><span style="font-weight: 800; font-size: 16px; color: var(--primary);"><?= htmlspecialchars($patient['name']) ?></span></td>
                    <td><?= htmlspecialchars($patient['age']) ?> Y / <?= ucfirst(htmlspecialchars($patient['gender'])) ?></td>
                    <td><?= htmlspecialchars($patient['phone']) ?></td>
                    <td><?= htmlspecialchars($patient['last_visit']) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'views/layouts/footer.php'; ?>
