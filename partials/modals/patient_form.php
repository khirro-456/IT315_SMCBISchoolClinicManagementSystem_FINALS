<?php defined('APP') or exit; $np = name_pattern(); $ph = phone_attrs(); ?>
<!-- Add / Edit Patient -->
<div class="modal fade" id="patientModal" tabindex="-1" aria-hidden="true" data-editable="1" data-keep-draft>
  <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
    <div class="modal-content">
      <form method="post" data-fillable autocomplete="off">
        <input type="hidden" name="action" value="save_patient">
        <input type="hidden" name="id" value="">
        <input type="hidden" name="return" value="<?php echo esc($page); ?>">
        <?php csrf_field(); ?>
        <div class="modal-header">
          <h5 class="modal-title" data-add="Add Patient" data-edit="Edit Patient">Add Patient</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted small">Fields marked * are required. Names accept letters only; random letters (e.g. "bsrngobwljr", "paieghpansg") are rejected. Contact numbers must be exactly 11 digits starting with 09.</p>
          <div class="row g-3">
            <div class="col-md-4"><label class="form-label">School / Employee ID *</label>
              <input class="form-control" name="school_id" data-check="text" required minlength="2" maxlength="50" pattern="(?=.*[A-Za-z])(?=.*[0-9])[A-Za-z0-9\-]{2,50}" title="Must contain BOTH letters and numbers. Hyphen allowed. Example: STU-2026-001." placeholder="STU-2026-001"></div>
            <div class="col-md-4"><label class="form-label">Patient Type *</label>
              <select class="form-select" name="patient_type"><option value="student">Student</option><option value="personnel">Personnel</option></select></div>
            <div class="col-md-4"><label class="form-label">Grade / Section / Department</label>
              <input class="form-control" name="grade_department" data-check="text" maxlength="120"></div>
            <div class="col-md-4"><label class="form-label">First Name *</label>
              <input class="form-control name-input" name="first_name" data-check="name" required minlength="2" maxlength="80" pattern="<?php echo $np; ?>" title="Letters only."></div>
            <div class="col-md-4"><label class="form-label">Middle Name</label>
              <input class="form-control name-input" name="middle_name" data-check="name" maxlength="80" pattern="<?php echo $np; ?>" title="Letters only."></div>
            <div class="col-md-4"><label class="form-label">Last Name *</label>
              <input class="form-control name-input" name="last_name" data-check="name" required minlength="2" maxlength="80" pattern="<?php echo $np; ?>" title="Letters only."></div>
            <div class="col-md-4"><label class="form-label">Sex</label>
              <select class="form-select" name="sex"><option value="">Select</option><option value="Male">Male</option><option value="Female">Female</option></select></div>
            <div class="col-md-4"><label class="form-label">Birth Date</label>
              <input class="form-control" type="date" name="birth_date" max="<?php echo date('Y-m-d'); ?>"></div>
            <div class="col-md-4"><label class="form-label">Contact Number (11 digits)</label>
              <input class="form-control" name="contact_number" <?php echo $ph; ?>></div>
            <div class="col-md-6"><label class="form-label">Parent / Guardian Name</label>
              <input class="form-control name-input" name="guardian_name" data-check="name" maxlength="150" pattern="<?php echo $np; ?>" title="Letters only."></div>
            <div class="col-md-6"><label class="form-label">Parent / Guardian Contact (11 digits)</label>
              <input class="form-control" name="guardian_contact" <?php echo $ph; ?>></div>
            <div class="col-md-6"><label class="form-label">Allergies</label>
              <textarea class="form-control" rows="3" name="allergies" data-check="text" maxlength="1000" placeholder="Example: Allergic to penicillin"></textarea></div>
            <div class="col-md-6"><label class="form-label">Medical Conditions</label>
              <textarea class="form-control" rows="3" name="medical_conditions" data-check="text" maxlength="1000" placeholder="Example: Asthma"></textarea></div>
            <div class="col-md-6"><label class="form-label">Emergency Contact Name</label>
              <input class="form-control name-input" name="emergency_contact_name" data-check="name" maxlength="150" pattern="<?php echo $np; ?>" title="Letters only."></div>
            <div class="col-md-6"><label class="form-label">Emergency Contact Number (11 digits)</label>
              <input class="form-control" name="emergency_contact_number" <?php echo $ph; ?>></div>
            <div class="col-12"><label class="form-label">Remarks</label>
              <textarea class="form-control" rows="2" name="remarks" data-check="text" maxlength="1000"></textarea></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-danger me-auto" data-reset-form data-add="Clear Form" data-edit="Undo Changes">Clear Form</button>
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary">Save Patient</button>
        </div>
      </form>
    </div>
  </div>
</div>
