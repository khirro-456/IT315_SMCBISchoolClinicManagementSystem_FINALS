<?php defined('APP') or exit;
$title = 'Patients';

/* visit history is loaded per patient when the profile opens (?page=patient_visits) */
$patients = $pdo->query('SELECT * FROM patients ORDER BY last_name, first_name')->fetchAll();

require $root . '/partials/header.php';
?>
<div class="page-header">
  <div class="page-header-main">
    <span class="page-icon"><i class="bi bi-people"></i></span>
    <div><h2>Patients</h2><p><?php echo count($patients); ?> registered &middot; profiles and clinic history</p></div>
  </div>
  <div class="page-actions"><button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#patientModal"><i class="bi bi-person-plus"></i> Add Patient</button></div>
</div>

<div class="card table-card"><div class="card-body">
  <div class="input-icon search-input mb-3"><i class="bi bi-search"></i><input type="search" class="form-control" data-filter=".pt-row" placeholder="Search ID, name, grade, or department..." aria-label="Search patients"></div>
  <div class="scroll-box">
    <table class="table table-hover align-middle table-stack">
      <thead><tr><th>Patient</th><th>ID</th><th>Type</th><th>Sex</th><th>Grade / Department</th><th>Contact</th><th class="text-end"></th></tr></thead>
      <tbody>
      <?php foreach ($patients as $pt):
          $rec = $pt;
          $rec['full_name'] = $pt['last_name'] . ', ' . $pt['first_name'] . ' ' . $pt['middle_name'];
          $view = $rec;
      ?>
        <tr class="pt-row">
          <td><a href="#" class="person text-decoration-none" data-bs-toggle="modal" data-bs-target="#patientViewModal" data-record="<?php echo json_attr($view); ?>"><span class="avatar"><?php echo esc(initials($pt['first_name'], $pt['last_name'])); ?></span><span class="cell-title text-body"><?php echo esc($rec['full_name']); ?></span></a></td>
          <td><span class="cell-sub"><?php echo esc($pt['school_id']); ?></span></td>
          <td><span class="badge <?php echo $pt['patient_type'] === 'student' ? 'bg-primary' : 'bg-info'; ?>"><?php echo esc(ucfirst($pt['patient_type'])); ?></span></td>
          <td><?php echo esc($pt['sex'] ?: '—'); ?></td>
          <td><?php echo esc($pt['grade_department'] ?: '—'); ?></td>
          <td class="text-nowrap"><?php echo esc($pt['contact_number'] ?: '—'); ?></td>
          <td class="text-end"><div class="row-actions">
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#patientViewModal" data-record="<?php echo json_attr($view); ?>" title="View profile"><i class="bi bi-eye"></i><span class="d-md-none d-xxl-inline">View</span></button>
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#patientModal" data-record="<?php echo json_attr($pt); ?>" title="Edit patient"><i class="bi bi-pencil"></i><span class="d-md-none d-xxl-inline">Edit</span></button>
            <button class="btn btn-sm btn-soft-primary" data-bs-toggle="modal" data-bs-target="#visitModal" data-record="<?php echo json_attr(array('patient_id' => (int)$pt['id'])); ?>"><i class="bi bi-clipboard2-plus"></i> Visit</button>
          </div></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$patients): ?><tr><td colspan="7"><div class="empty-state"><i class="bi bi-people"></i>No patient records yet. Click "Add Patient" to create the first one.</div></td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div></div>

<?php
require $root . '/partials/modals/patient_form.php';
require $root . '/partials/modals/patient_view.php';
require $root . '/partials/modals/visit_form.php';
require $root . '/partials/modals/visit_view.php';
require $root . '/partials/footer.php';
