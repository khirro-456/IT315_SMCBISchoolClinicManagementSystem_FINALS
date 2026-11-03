<?php defined('APP') or exit; ?>
<!-- View Full Clinic Visit Record -->
<div class="modal fade" id="visitViewModal" tabindex="-1" aria-hidden="true" data-viewer="visit">
  <div class="modal-dialog modal-xl modal-dialog-scrollable modal-fullscreen-sm-down">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Clinic Visit Record <small class="text-muted" data-view="visit_date_fmt"></small></h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-4">
          <div class="col-lg-4">
            <h6 class="text-uppercase text-muted">Patient</h6>
            <p class="mb-2"><span class="detail-label">Name</span><br><strong data-view="patient_name"></strong></p>
            <p class="mb-2"><span class="detail-label">School / Employee ID</span><br><span data-view="school_id"></span></p>
            <p class="mb-2"><span class="detail-label">Type / Grade</span><br><span data-view="type_grade"></span></p>
            <p class="mb-2"><span class="detail-label">Allergies</span><br><span class="detail-value" data-view="allergies" data-empty="None recorded"></span></p>
            <p class="mb-0"><span class="detail-label">Medical Conditions</span><br><span class="detail-value" data-view="medical_conditions" data-empty="None recorded"></span></p>
          </div>
          <div class="col-lg-8">
            <div class="d-flex justify-content-between flex-wrap gap-2 mb-3">
              <div><span class="badge bg-primary" data-view="duty_staff"></span> <small class="text-muted" data-view="staff_role"></small></div>
              <div><span class="badge bg-secondary" data-view="guardian_badge"></span> <span class="badge bg-secondary" data-view="referred_badge"></span></div>
            </div>
            <div class="row g-3">
              <div class="col-md-4"><div class="detail-label">Temperature</div><div data-view="temperature_fmt"></div></div>
              <div class="col-md-4"><div class="detail-label">Blood Pressure</div><div data-view="bp_fmt"></div></div>
              <div class="col-md-4"><div class="detail-label">Pulse Rate</div><div data-view="pulse_fmt"></div></div>
              <div class="col-12"><div class="detail-label">Chief Complaint</div><div class="detail-value" data-view="chief_complaint"></div></div>
              <div class="col-md-6"><div class="detail-label">Assessment</div><div class="detail-value" data-view="assessment" data-empty="Not recorded"></div></div>
              <div class="col-md-6"><div class="detail-label">Treatment Given</div><div class="detail-value" data-view="treatment_given" data-empty="Not recorded"></div></div>
              <div class="col-md-6"><div class="detail-label">Recommendation</div><div class="detail-value" data-view="recommendation" data-empty="Not recorded"></div></div>
              <div class="col-md-6"><div class="detail-label">Additional Notes</div><div class="detail-value" data-view="notes" data-empty="None"></div></div>
            </div>
            <h6 class="text-uppercase text-muted mt-4">Medicine / Supply Issued</h6>
            <table class="table table-sm mb-0 table-stack">
              <thead><tr><th>Code</th><th>Item</th><th>Category</th><th>Quantity</th></tr></thead>
              <tbody data-view-meds></tbody>
            </table>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="printModal(this)">Print</button>
      </div>
    </div>
  </div>
</div>
