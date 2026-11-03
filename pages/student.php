<?php defined('APP') or exit;
/* My Clinic Record: a student or employee views their own profile and visit history (read-only).
   Sign-in is the School / Employee ID + 6-digit access code from the clinic (actions/portal.php).
   Internal staff notes on visits are not shown here. */
$me = portal_patient($pdo);
$title = 'My Clinic Record';
$orNone = function ($v) { return ($v === null || trim((string)$v) === '') ? '—' : $v; };

if ($me) {
    $st = $pdo->prepare('SELECT v.id, v.visit_date, v.chief_complaint, v.temperature, v.blood_pressure, v.pulse_rate, v.assessment, v.treatment_given, v.recommendation, v.referred_to_hospital, v.guardian_notified, u.full_name AS duty_staff FROM clinic_visits v JOIN users u ON u.id=v.attended_by WHERE v.patient_id=? ORDER BY v.visit_date DESC');
    $st->execute(array((int)$me['id']));
    $visits = $st->fetchAll();
    $medsByVisit = fetch_meds_by_visit($pdo, array_column($visits, 'id'));
    $fullName = preg_replace('/\s+/u', ' ', trim($me['first_name'] . ' ' . $me['middle_name'] . ' ' . $me['last_name']));
    $details = array(
        'School / Employee ID' => $me['school_id'],
        'Type' => ucfirst($me['patient_type']),
        'Grade / Department' => $me['grade_department'],
        'Sex' => $me['sex'],
        'Birth Date' => $me['birth_date'] ? date('F j, Y', strtotime($me['birth_date'])) : '',
        'Contact Number' => $me['contact_number'],
        'Parent / Guardian' => implode(' — ', array_filter(array($me['guardian_name'], $me['guardian_contact']))),
        'Emergency Contact' => implode(' — ', array_filter(array($me['emergency_contact_name'], $me['emergency_contact_number'])))
    );
}
?>
<!doctype html>
<html lang="en" data-ui-theme="<?php echo esc(current_ui_theme($pdo)); ?>">
<head>
<?php require $root . '/partials/head.php'; ?>
</head>
<body class="login-body">
<?php if (!$me): ?>
<div class="login-page">
  <section class="login-form-side">
    <button type="button" class="btn icon-btn login-theme" data-theme-toggle aria-label="Switch light or dark mode" title="Switch light / dark mode">
      <i class="bi bi-moon-stars theme-icon-dark"></i><i class="bi bi-sun theme-icon-light"></i>
    </button>
    <div class="login-form-card">
      <div class="login-mark has-logo"><img src="assets/img/school-logo.png" alt="SMCBI logo"></div>
      <h2 class="mb-1">My Clinic Record</h2>
      <p class="text-muted mb-4">Students and school personnel can view their own clinic record here.</p>
      <?php show_flash(); ?>
      <form method="post" autocomplete="off" id="portalForm">
        <input type="hidden" name="action" value="portal_login">
        <?php csrf_field(); ?>
        <div class="mb-3">
          <label class="form-label" for="portalId">School / Employee ID</label>
          <div class="input-icon"><i class="bi bi-person-vcard"></i>
            <input class="form-control form-control-lg" id="portalId" name="school_id" required minlength="2" maxlength="50" pattern="(?=.*[A-Za-z])(?=.*[0-9])[A-Za-z0-9\-]{2,50}" title="Your ID as printed on your school ID, e.g. STU-2026-001" placeholder="STU-2026-001" autocapitalize="characters" spellcheck="false" autofocus></div>
        </div>
        <div class="mb-4">
          <label class="form-label" for="portalCode">Access code</label>
          <div class="input-icon"><i class="bi bi-key"></i>
            <input class="form-control form-control-lg" id="portalCode" name="access_code" type="password" inputmode="numeric" required minlength="6" maxlength="6" pattern="[0-9]{6}" title="The 6-digit code from the school clinic" placeholder="6-digit code" autocomplete="off"></div>
        </div>
        <button class="btn btn-primary btn-lg w-100"><span>View my record</span><i class="bi bi-arrow-right"></i></button>
      </form>
      <div class="login-secure"><i class="bi bi-shield-lock"></i>Get your 6-digit access code from the school clinic. Keep it private: anyone with your ID and code can see your health record.</div>
      <a class="login-alt" href="?page=login"><i class="bi bi-hospital"></i>Clinic staff sign in</a>
    </div>
  </section>
</div>
<?php else: ?>
<header class="portal-bar no-print">
  <div class="portal-brand"><img src="assets/img/school-logo.png" alt="SMCBI logo"><div>SMCBI School Clinic<small>My Clinic Record</small></div></div>
  <div class="d-flex align-items-center gap-2">
    <button type="button" class="btn icon-btn" data-theme-toggle aria-label="Switch light or dark mode" title="Switch light / dark mode">
      <i class="bi bi-moon-stars theme-icon-dark"></i><i class="bi bi-sun theme-icon-light"></i>
    </button>
    <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="bi bi-printer"></i><span class="d-none d-sm-inline"> Print</span></button>
    <form method="post" class="m-0">
      <input type="hidden" name="action" value="portal_logout">
      <?php csrf_field(); ?>
      <button class="btn btn-primary" title="Sign out" aria-label="Sign out"><i class="bi bi-box-arrow-right"></i><span class="d-none d-sm-inline"> Sign out</span></button>
    </form>
  </div>
</header>
<main class="portal-main">
  <?php show_flash(); ?>
  <section class="card mb-4">
    <div class="card-body">
      <div class="d-flex align-items-center gap-3 mb-4">
        <span class="avatar avatar-lg"><?php echo esc(initials($me['first_name'], $me['last_name'])); ?></span>
        <div class="min-w-0">
          <h1 class="h4 mb-0"><?php echo esc($fullName); ?></h1>
          <span class="text-muted small">Clinic record as of <?php echo date('F j, Y, g:i A'); ?></span>
        </div>
      </div>
      <div class="portal-grid">
        <?php foreach ($details as $label => $value): ?>
          <div><div class="detail-label"><?php echo esc($label); ?></div><div><?php echo esc($orNone($value)); ?></div></div>
        <?php endforeach; ?>
      </div>
      <div class="row g-3 mt-2">
        <div class="col-md-6"><div class="portal-health"><div class="detail-label"><i class="bi bi-exclamation-triangle"></i> Allergies</div><div class="detail-value"><?php echo esc($me['allergies'] ?: 'None recorded'); ?></div></div></div>
        <div class="col-md-6"><div class="portal-health"><div class="detail-label"><i class="bi bi-heart-pulse"></i> Medical Conditions</div><div class="detail-value"><?php echo esc($me['medical_conditions'] ?: 'None recorded'); ?></div></div></div>
        <?php if ($me['remarks']): ?>
        <div class="col-12"><div class="portal-health"><div class="detail-label">Remarks</div><div class="detail-value"><?php echo esc($me['remarks']); ?></div></div></div>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="card table-card">
    <div class="card-header"><span><span class="card-title-icon"><i class="bi bi-clock-history"></i></span>Clinic visit history</span><span class="badge bg-secondary"><?php echo count($visits); ?> <?php echo count($visits) === 1 ? 'visit' : 'visits'; ?></span></div>
    <?php foreach ($visits as $v):
        $items = array();
        foreach (isset($medsByVisit[$v['id']]) ? $medsByVisit[$v['id']] : array() as $m) { $items[] = $m['item_name'] . ' (' . $m['quantity_used'] . ' ' . $m['unit'] . ')'; }
    ?>
      <article class="visit-entry">
        <div class="visit-entry-head">
          <div class="min-w-0">
            <div class="cell-title"><?php echo esc($v['chief_complaint']); ?></div>
            <div class="cell-sub"><?php echo esc(visit_date_format($v['visit_date'])); ?> &middot; Attended by <?php echo esc($v['duty_staff']); ?></div>
          </div>
          <div class="vitals">
            <?php if ($v['temperature'] !== '' && $v['temperature'] !== null): ?><span class="vital" title="Temperature"><i class="bi bi-thermometer-half"></i><?php echo esc($v['temperature']); ?> &deg;C</span><?php endif; ?>
            <?php if ($v['blood_pressure']): ?><span class="vital" title="Blood pressure"><i class="bi bi-heart-pulse"></i><?php echo esc($v['blood_pressure']); ?> mmHg</span><?php endif; ?>
            <?php if ($v['pulse_rate']): ?><span class="vital" title="Pulse rate"><i class="bi bi-activity"></i><?php echo esc($v['pulse_rate']); ?> bpm</span><?php endif; ?>
          </div>
        </div>
        <div class="row g-3">
          <div class="col-md-6"><div class="detail-label">Assessment</div><div class="detail-value"><?php echo esc($orNone($v['assessment'])); ?></div></div>
          <div class="col-md-6"><div class="detail-label">Treatment Given</div><div class="detail-value"><?php echo esc($orNone($v['treatment_given'])); ?></div></div>
          <div class="col-md-6"><div class="detail-label">Medicine / Supply Given</div><div><?php echo esc($items ? implode(', ', $items) : 'None'); ?></div></div>
          <div class="col-md-6"><div class="detail-label">Recommendation</div><div class="detail-value"><?php echo esc($orNone($v['recommendation'])); ?></div></div>
        </div>
        <?php if ($v['referred_to_hospital'] === 'Yes' || $v['guardian_notified'] === 'Yes'): ?>
        <div class="d-flex flex-wrap gap-2 mt-3">
          <?php if ($v['referred_to_hospital'] === 'Yes'): ?><span class="badge bg-danger"><i class="bi bi-hospital"></i> Referred to hospital</span><?php endif; ?>
          <?php if ($v['guardian_notified'] === 'Yes'): ?><span class="badge bg-info"><i class="bi bi-telephone"></i> Guardian notified</span><?php endif; ?>
        </div>
        <?php endif; ?>
      </article>
    <?php endforeach; ?>
    <?php if (!$visits): ?><div class="empty-state"><i class="bi bi-clipboard2-check"></i>No clinic visits recorded yet.</div><?php endif; ?>
  </section>
  <p class="portal-note"><i class="bi bi-shield-lock"></i>This record is confidential. Sign out when you are done; it also closes by itself after <?php echo PORTAL_IDLE_MINUTES; ?> minutes. If any information is wrong, please tell the school clinic.</p>
</main>
<script>
/* shared computers: close the record automatically once the session has expired */
setTimeout(function () { location.href = '?page=student'; }, <?php echo (PORTAL_IDLE_MINUTES * 60 + 5) * 1000; ?>);
</script>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>window.REOPEN = null; window.RARE_PAIRS = <?php echo json_encode(RARE_LETTER_PAIRS); ?>;</script>
<script src="assets/js/app.js?v=11"></script>
</body>
</html>
