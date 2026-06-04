<?php include 'views/layouts/header.php'; ?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
    <h3>Doctor Schedules</h3>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addScheduleModal">
        <i class="fas fa-plus"></i> Add Schedule
    </button>
</div>

<div class="table-container">
    <table>
        <thead>
            <tr>
                <th>Doctor</th>
                <th>Day</th>
                <th>Time In</th>
                <th>Time Out</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php if(isset($schedules) && count($schedules) > 0): ?>
                <?php foreach($schedules as $s): ?>
                <tr>
                    <td style="font-weight: 600;"><?= $s['doctor_name'] ?></td>
                    <td><span class="badge bg-info text-dark"><?= $s['day_of_week'] ?></span></td>
                    <td><?= date('h:i A', strtotime($s['start_time'])) ?></td>
                    <td><?= date('h:i A', strtotime($s['end_time'])) ?></td>
                    <td>
                         <a href="?route=hospital_admin/delete_schedule&id=<?= $s['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Remove this schedule?')"><i class="fas fa-trash"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center p-4">No schedules found.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Add Schedule Modal -->
<div class="modal fade" id="addScheduleModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Schedule</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="?route=hospital_admin/store_schedule" method="POST">
      <div class="modal-body">
            <div class="mb-3">
                <label class="form-label">Doctor</label>
                <select name="doctor_id" class="form-control" required>
                    <option value="">Select Doctor</option>
                    <?php foreach($doctors as $doc): ?>
                        <option value="<?= $doc['id'] ?>"><?= $doc['name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label class="form-label">Day of Week</label>
                <select name="day_of_week" class="form-control" required>
                    <option value="Monday">Monday</option>
                    <option value="Tuesday">Tuesday</option>
                    <option value="Wednesday">Wednesday</option>
                    <option value="Thursday">Thursday</option>
                    <option value="Friday">Friday</option>
                    <option value="Saturday">Saturday</option>
                    <option value="Sunday">Sunday</option>
                </select>
            </div>
            <div class="row">
                <div class="col-6 mb-3">
                    <label class="form-label">Start Time</label>
                    <input type="time" name="start_time" class="form-control" required>
                </div>
                 <div class="col-6 mb-3">
                    <label class="form-label">End Time</label>
                    <input type="time" name="end_time" class="form-control" required>
                </div>
            </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="submit" class="btn btn-primary">Save</button>
      </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<?php include 'views/layouts/footer.php'; ?>
