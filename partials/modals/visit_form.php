<?php defined('APP') or exit;
$vmPatients = fetch_patients_for_select($pdo);
$vmItems = fetch_issuable_items($pdo);
?>
<!-- Record Clinic Visit -->
<div class="modal fade" id="visitModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
    <div class="modal-content">
      <form method="post" data-fillable autocomplete="off">
        <input type="hidden" name="action" value="save_visit">
        <input type="hidden" name="return" value="<?php echo esc($page); ?>">
        <?php csrf_field(); ?>
        <div class="modal-header">
          <h5 class="modal-title">Record Clinic Visit</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info py-2"><strong>Current duty staff:</strong> <?php echo esc($_SESSION['user']['name']); ?> &mdash; <?php echo esc(ucfirst(current_role())); ?></div>
          <div class="form-section-title"><i class="bi bi-person-vcard"></i> Patient and vital signs</div>
          <div class="row g-3 mb-3">
            <div class="col-md-6"><label class="form-label">Patient *</label>
              <select class="form-select" name="patient_id" required><option value="">Select patient</option>
              <?php foreach ($vmPatients as $pt): ?><option value="<?php echo (int)$pt['id']; ?>"><?php echo esc($pt['last_name'] . ', ' . $pt['first_name'] . ' - ' . $pt['school_id'] . ' - ' . $pt['grade_department']); ?></option><?php endforeach; ?>
              </select></div>
            <div class="col-md-6"><label class="form-label">Chief Complaint *</label>
              <input class="form-control" name="chief_complaint" required minlength="2" maxlength="255" placeholder="Example: Headache"></div>
            <div class="col-md-4"><label class="form-label">Temperature (&deg;C)</label>
              <input class="form-control" type="number" name="temperature" min="35" max="42" step="0.1" placeholder="35.0 - 42.0 (example 37.2)"></div>
            <div class="col-md-4"><label class="form-label">Blood Pressure (mmHg)</label>
              <input class="form-control" name="blood_pressure" inputmode="numeric" maxlength="7" pattern="[0-9]{2,3}/[0-9]{2,3}" title="Use the format 120/80" placeholder="Example: 120/80"></div>
            <div class="col-md-4"><label class="form-label">Pulse Rate (bpm)</label>
              <input class="form-control" type="number" name="pulse_rate" min="30" max="250" step="1" placeholder="30 - 250 (example 72)"></div>
          </div>
          <div class="form-section-title"><i class="bi bi-clipboard2-check"></i> Assessment and action taken</div>
          <div class="row g-3 mb-3">
            <div class="col-md-6"><label class="form-label">Assessment *</label>
              <textarea class="form-control" rows="3" name="assessment" required minlength="2" maxlength="1000" placeholder="Example: Mild headache, no fever"></textarea></div>
            <div class="col-md-6"><label class="form-label">Treatment Given / Action Taken *</label>
              <textarea class="form-control" rows="3" name="treatment_given" required minlength="2" maxlength="1000" placeholder="Example: Rest for 30 minutes and drink water"></textarea></div>
            <div class="col-md-6"><label class="form-label">Recommendation</label>
              <textarea class="form-control" rows="2" name="recommendation" maxlength="1000"></textarea></div>
            <div class="col-md-3"><label class="form-label">Referred to Hospital?</label>
              <select class="form-select" name="referred_to_hospital"><option>No</option><option>Yes</option></select></div>
            <div class="col-md-3"><label class="form-label">Guardian Notified?</label>
              <select class="form-select" name="guardian_notified"><option>No</option><option>Yes</option></select></div>
            <div class="col-12"><label class="form-label">Additional Notes</label>
              <textarea class="form-control" rows="2" name="notes" maxlength="1000"></textarea></div>
          </div>
          <div class="form-section-title"><i class="bi bi-capsule"></i> Medicine / supply used <span class="text-lowercase fw-normal">(only available, non-expired items)</span></div>
          <div class="row g-3">
            <div class="col-md-8">
              <input class="form-control mb-2" type="text" data-filter-options="#medSelect" placeholder="Search medicine or supply...">
              <select class="form-select" id="medSelect" name="item_id" size="6"><option value="">No item selected</option>
              <?php foreach ($vmItems as $it): ?><option value="<?php echo (int)$it['id']; ?>"><?php echo esc($it['item_name'] . ' - Available: ' . $it['current_quantity'] . ' ' . $it['unit']); ?></option><?php endforeach; ?>
              </select></div>
            <div class="col-md-4"><label class="form-label">Quantity Used</label>
              <input class="form-control" type="number" min="0" step="1" name="used_quantity" value="0"></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary">Save Complete Clinic Visit</button>
        </div>
      </form>
    </div>
  </div>
</div>
