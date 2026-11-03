<?php defined('APP') or exit; ?>
<!-- View Patient Profile + Clinic History -->
<div class="modal fade" id="patientViewModal" tabindex="-1" aria-hidden="true" data-viewer="patient">
  <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Patient Profile: <span data-view="full_name"></span></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-4">
          <div class="col-lg-4">
            <h6 class="text-uppercase text-muted">Health Profile</h6>
            <p class="mb-2"><span class="detail-label">School / Employee ID</span><br><span data-view="school_id"></span></p>
            <p class="mb-2"><span class="detail-label">Type / Sex</span><br><span data-view="type_sex"></span></p>
            <p class="mb-2"><span class="detail-label">Grade / Department</span><br><span data-view="grade_department"></span></p>
            <p class="mb-2"><span class="detail-label">Birth Date</span><br><span data-view="birth_date"></span></p>
            <p class="mb-2"><span class="detail-label">Contact</span><br><span data-view="contact_number"></span></p>
            <p class="mb-2"><span class="detail-label">Guardian</span><br><span data-view="guardian"></span></p>
            <p class="mb-2"><span class="detail-label">Allergies</span><br><span class="detail-value" data-view="allergies" data-empty="None recorded"></span></p>
            <p class="mb-2"><span class="detail-label">Medical Conditions</span><br><span class="detail-value" data-view="medical_conditions" data-empty="None recorded"></span></p>
            <p class="mb-2"><span class="detail-label">Emergency Contact</span><br><span data-view="emergency"></span></p>
            <p class="mb-0"><span class="detail-label">Remarks</span><br><span class="detail-value" data-view="remarks"></span></p>
            <?php $portalUrl = (is_https() ? 'https://' : 'http://') . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost') . strtok($_SERVER['REQUEST_URI'], '?') . '?page=student'; ?>
            <div class="portal-code-box mt-3">
              <span class="detail-label">My Clinic Record access code</span>
              <div class="portal-code" data-view="portal_code" data-empty="Not set"></div>
              <div class="small text-muted">Give this code to the patient with their ID. They open <span class="text-break"><?php echo esc($portalUrl); ?></span></div>
              <form method="post" class="no-print mt-2" id="pvResetCode" onsubmit="return confirm('Create a new access code? The old code will stop working.');">
                <input type="hidden" name="action" value="reset_portal_code">
                <input type="hidden" name="id" value="">
                <input type="hidden" name="return" value="<?php echo esc($page); ?>">
                <?php csrf_field(); ?>
                <button class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-repeat"></i> Reset code</button>
              </form>
            </div>
          </div>
          <div class="col-lg-8">
            <h6 class="text-uppercase text-muted">Clinic Visit History</h6>
            <div class="scroll-box"><table class="table table-hover align-middle mb-0 table-stack">
              <thead><tr><th>Date</th><th>Complaint</th><th>Vital Signs</th><th>Duty Staff</th><th></th></tr></thead>
              <tbody data-view-visits></tbody>
            </table></div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-outline-primary" onclick="printModal(this)">Print</button>
        <button type="button" class="btn btn-primary" id="pvRecordVisit">+ Record Visit</button>
      </div>
    </div>
  </div>
</div>
