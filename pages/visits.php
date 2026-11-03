<?php defined('APP') or exit;
$title = 'Clinic Visits';

$visits = $pdo->query("SELECT v.*, CONCAT(p.last_name, ', ', p.first_name) AS patient_name, p.school_id, p.patient_type, p.grade_department, p.allergies, p.medical_conditions, u.full_name AS duty_staff, u.role AS staff_role FROM clinic_visits v JOIN patients p ON p.id=v.patient_id JOIN users u ON u.id=v.attended_by ORDER BY v.visit_date DESC LIMIT 300")->fetchAll();
$medsByVisit = fetch_meds_by_visit($pdo, array_column($visits, 'id'));

require $root . '/partials/header.php';
?>
<div class="page-header">
  <div class="page-header-main">
    <span class="page-icon tone-green"><i class="bi bi-clipboard2-pulse"></i></span>
    <div><h2>Clinic Visits</h2><p>Consultations, treatments, and issued supplies</p></div>
  </div>
  <div class="page-actions"><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#visitModal"><i class="bi bi-clipboard2-plus"></i> Record Clinic Visit</button></div>
</div>

<div class="card table-card"><div class="card-body">
  <div class="input-icon search-input mb-3"><i class="bi bi-search"></i><input type="search" class="form-control" data-filter=".vs-row" placeholder="Search patient, complaint, or staff..." aria-label="Search visits"></div>
  <div class="scroll-box">
    <table class="table table-hover align-middle table-stack">
      <thead><tr><th>Patient</th><th>Chief Complaint</th><th>Vital Signs</th><th>Consulted By</th><th>Date &amp; Time</th><th class="text-end"></th></tr></thead>
      <tbody>
      <?php foreach ($visits as $v):
          $rec = $v;
          $rec['meds'] = isset($medsByVisit[$v['id']]) ? $medsByVisit[$v['id']] : array();
          $nameParts = explode(', ', $v['patient_name'], 2);
      ?>
        <tr class="vs-row">
          <td><div class="person"><span class="avatar"><?php echo esc(initials(isset($nameParts[1]) ? $nameParts[1] : '', $nameParts[0])); ?></span><div class="min-w-0"><div class="cell-title"><?php echo esc($v['patient_name']); ?></div><div class="cell-sub"><?php echo esc($v['school_id']); ?></div></div></div></td>
          <td><?php echo esc($v['chief_complaint']); ?></td>
          <td><div class="vitals">
            <span class="vital" title="Temperature"><i class="bi bi-thermometer-half"></i><?php echo esc($v['temperature'] ? $v['temperature'] . '°' : '—'); ?></span>
            <span class="vital" title="Blood pressure"><i class="bi bi-heart-pulse"></i><?php echo esc($v['blood_pressure'] ?: '—'); ?></span>
            <span class="vital" title="Pulse rate"><i class="bi bi-activity"></i><?php echo esc($v['pulse_rate'] ?: '—'); ?></span>
          </div></td>
          <td><div class="cell-title"><?php echo esc($v['duty_staff']); ?></div><div class="cell-sub"><?php echo esc(ucfirst($v['staff_role'])); ?></div></td>
          <td class="text-nowrap"><span class="cell-sub"><?php echo esc(visit_date_format($v['visit_date'])); ?></span></td>
          <td class="text-end"><div class="row-actions"><button class="btn btn-sm btn-soft-primary" data-bs-toggle="modal" data-bs-target="#visitViewModal" data-record="<?php echo json_attr($rec); ?>"><i class="bi bi-file-earmark-medical"></i> Full record</button></div></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$visits): ?><tr><td colspan="6"><div class="empty-state"><i class="bi bi-clipboard2"></i>No clinic visits recorded yet.</div></td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div></div>

<?php
require $root . '/partials/modals/visit_form.php';
require $root . '/partials/modals/visit_view.php';
require $root . '/partials/footer.php';
