<?php defined('APP') or exit;
$title = 'Dashboard';

/* ---- counters ---- */
$nPatients  = (int)$pdo->query('SELECT COUNT(*) FROM patients')->fetchColumn();
$todayVisits = (int)$pdo->query('SELECT COUNT(*) FROM clinic_visits WHERE DATE(visit_date)=CURDATE()')->fetchColumn();
$low      = (int)$pdo->query('SELECT COUNT(*) FROM inventory_items WHERE current_quantity>LEAST(5,minimum_stock) AND current_quantity>0 AND current_quantity<=minimum_stock')->fetchColumn();
$critical = (int)$pdo->query('SELECT COUNT(*) FROM inventory_items WHERE current_quantity BETWEEN 1 AND LEAST(5,minimum_stock)')->fetchColumn();
$out      = (int)$pdo->query('SELECT COUNT(*) FROM inventory_items WHERE current_quantity=0')->fetchColumn();
$near     = (int)$pdo->query('SELECT COUNT(*) FROM inventory_items WHERE expiration_date IS NOT NULL AND expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)')->fetchColumn();
$expired  = (int)$pdo->query('SELECT COUNT(*) FROM inventory_items WHERE expiration_date IS NOT NULL AND expiration_date<CURDATE()')->fetchColumn();
$totalItems = (int)$pdo->query('SELECT COUNT(*) FROM inventory_items')->fetchColumn();

/* Status chart: each item counted once, in its most urgent group
   (order matches the chart: Available, Low, Critical, Out, Near Expiry, Expired) */
$statusCounts = array(0, 0, 0, 0, 0, 0);
foreach ($pdo->query('SELECT current_quantity,minimum_stock,expiration_date FROM inventory_items') as $it) {
    $s = stock_status($it['current_quantity'], $it['minimum_stock']);
    $e = expiry_status($it['expiration_date']);
    if ($e['blocked']) $statusCounts[5]++;
    elseif ($s['color'] === 'dark') $statusCounts[3]++;
    elseif ($s['color'] === 'danger') $statusCounts[2]++;
    elseif ($e['color'] === 'warning') $statusCounts[4]++;
    elseif ($s['color'] === 'warning') $statusCounts[1]++;
    else $statusCounts[0]++;
}

/* ---- tables and chart data ---- */
$recent = $pdo->query("SELECT v.visit_date, CONCAT(p.last_name, ', ', p.first_name) AS patient_name, p.first_name, p.last_name, v.chief_complaint FROM clinic_visits v JOIN patients p ON p.id=v.patient_id ORDER BY v.visit_date DESC LIMIT 5")->fetchAll();
$trend = $pdo->query('SELECT DATE(visit_date) AS d, COUNT(*) AS total FROM clinic_visits WHERE visit_date>=DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(visit_date) ORDER BY d')->fetchAll();
$complaints = $pdo->query('SELECT chief_complaint, COUNT(*) AS total FROM clinic_visits GROUP BY chief_complaint ORDER BY total DESC LIMIT 5')->fetchAll();
$usage = $pdo->query('SELECT i.item_name, COALESCE(SUM(vi.quantity_used),0) AS total_used FROM inventory_items i LEFT JOIN visit_items vi ON vi.inventory_item_id=i.id GROUP BY i.id,i.item_name ORDER BY total_used DESC LIMIT 5')->fetchAll();
$alertItems = $pdo->query('SELECT item_name,current_quantity,unit FROM inventory_items WHERE current_quantity<=minimum_stock OR (expiration_date IS NOT NULL AND expiration_date<=DATE_ADD(CURDATE(), INTERVAL 30 DAY)) ORDER BY current_quantity ASC LIMIT 5')->fetchAll();

/* every one of the last 7 days appears on the trend chart, including days with no visits */
$byDay = array_column($trend, 'total', 'd');
$trendLabels = array(); $trendValues = array();
for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-$i day"));
    $trendLabels[] = $day;
    $trendValues[] = isset($byDay[$day]) ? (int)$byDay[$day] : 0;
}
$weekVisits = array_sum($trendValues);

$dash = array(
    'trendLabels' => $trendLabels,
    'trendValues' => $trendValues,
    'complaintLabels' => array_column($complaints, 'chief_complaint'),
    'complaintValues' => array_map('intval', array_column($complaints, 'total')),
    'usageLabels' => array_column($usage, 'item_name'),
    'usageValues' => array_map('intval', array_column($usage, 'total_used')),
    'status' => $statusCounts
);

$hour = (int)date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 18 ? 'Good afternoon' : 'Good evening');
$greetIcon = $hour < 12 ? 'bi-sunrise' : ($hour < 18 ? 'bi-sun' : 'bi-moon-stars');
$firstName = preg_split('/\s+/u', trim($_SESSION['user']['name']));

/* status panel: who is signed in and the most recent visit */
$lastVisit = $recent ? $recent[0] : null;
if ($lastVisit) {
    $lvTime = strtotime($lastVisit['visit_date']);
    $lastVisitWhen = date('Y-m-d', $lvTime) === date('Y-m-d') ? date('g:i A', $lvTime) : date('M j, g:i A', $lvTime);
    $lastVisitWho = $lastVisit['first_name'] . ' ' . mb_substr($lastVisit['last_name'], 0, 1) . '.';
}

require $root . '/partials/header.php';
?>
<section class="hero-card welcome-card mb-4">
  <div class="welcome-main">
    <div class="welcome-head">
      <span class="welcome-icon" aria-hidden="true"><i class="bi <?php echo $greetIcon; ?>"></i></span>
      <div>
        <h2><?php echo esc($greeting . ', ' . $firstName[0]); ?></h2>
        <p class="mb-0 muted"><?php echo date('l, F j, Y'); ?></p>
      </div>
    </div>
    <div class="quick-actions">
      <button class="quick-action" data-bs-toggle="modal" data-bs-target="#visitModal"><i class="bi bi-clipboard2-plus"></i>Record Visit</button>
      <button class="quick-action" data-bs-toggle="modal" data-bs-target="#patientModal"><i class="bi bi-person-plus"></i>Add Patient</button>
      <button class="quick-action" data-bs-toggle="modal" data-bs-target="#inventoryModal"><i class="bi bi-capsule"></i>Add Item</button>
    </div>
  </div>
  <div class="status-panel">
    <div class="status-title">Today at the clinic</div>
    <div class="status-row"><i class="bi bi-clock"></i><span><strong id="liveClock"><?php echo date('g:i A'); ?></strong></span></div>
    <div class="status-row"><i class="bi bi-person-badge"></i><span>On duty: <strong><?php echo esc($_SESSION['user']['name']); ?></strong> <small>(<?php echo esc(ucfirst($_SESSION['user']['role'])); ?>)</small></span></div>
    <div class="status-row"><i class="bi bi-clipboard2-pulse"></i>
      <?php if ($lastVisit): ?>
      <span>Last visit: <strong><?php echo esc($lastVisitWhen); ?></strong><small class="d-block"><?php echo esc($lastVisitWho . ' · ' . $lastVisit['chief_complaint']); ?></small></span>
      <?php else: ?>
      <span>No visits recorded yet</span>
      <?php endif; ?>
    </div>
  </div>
</section>

<div class="row g-3 g-xl-4 mb-4">
  <div class="col-6 col-xl-3"><a href="?page=patients" class="card-link"><div class="card stat-card text-tone-blue">
    <div class="stat-top"><div><div class="stat-label">Registered Patients</div><div class="stat-value"><?php echo $nPatients; ?></div></div><span class="stat-icon tone-blue"><i class="bi bi-people"></i></span></div>
    <span class="stat-foot">Patient records <i class="bi bi-arrow-right"></i></span></div></a></div>
  <div class="col-6 col-xl-3"><a href="?page=visits" class="card-link"><div class="card stat-card text-tone-green">
    <div class="stat-top"><div><div class="stat-label">Today's Visits</div><div class="stat-value"><?php echo $todayVisits; ?></div></div><span class="stat-icon tone-green"><i class="bi bi-clipboard2-pulse"></i></span></div>
    <span class="stat-foot">Consultations <i class="bi bi-arrow-right"></i></span></div></a></div>
  <div class="col-6 col-xl-3"><a href="?page=inventory&filter=low" class="card-link"><div class="card stat-card text-tone-amber">
    <div class="stat-top"><div><div class="stat-label">Low Stock</div><div class="stat-value"><?php echo $low; ?></div></div><span class="stat-icon tone-amber"><i class="bi bi-box-seam"></i></span></div>
    <span class="stat-foot">Reorder soon <i class="bi bi-arrow-right"></i></span></div></a></div>
  <div class="col-6 col-xl-3"><a href="?page=inventory&filter=critical" class="card-link"><div class="card stat-card text-tone-red">
    <div class="stat-top"><div><div class="stat-label">Critical / Out / Expired</div><div class="stat-value"><?php echo $critical + $out + $expired; ?></div></div><span class="stat-icon tone-red"><i class="bi bi-exclamation-diamond"></i></span></div>
    <span class="stat-foot">Act now <i class="bi bi-arrow-right"></i></span></div></a></div>
</div>

<div class="row g-3 g-xl-4 mb-4">
  <div class="col-xl-8">
    <div class="card h-100">
      <div class="card-header"><span><span class="card-title-icon"><i class="bi bi-graph-up-arrow"></i></span>Clinic visits, last 7 days</span><span class="badge bg-primary"><?php echo $weekVisits; ?> total</span></div>
      <div class="card-body"><div class="chart-box chart-box-lg"><canvas id="chartTrend" aria-label="Clinic visits over the last 7 days" role="img"></canvas></div></div>
    </div>
  </div>
  <div class="col-md-6 col-xl-4">
    <div class="card h-100">
      <div class="card-header"><span><span class="card-title-icon tone-teal"><i class="bi bi-pie-chart"></i></span>Inventory status</span><span class="badge bg-secondary"><?php echo $totalItems; ?> items</span></div>
      <div class="card-body"><div class="chart-box chart-box-lg"><canvas id="chartStatus" aria-label="Inventory status breakdown" role="img"></canvas></div></div>
    </div>
  </div>
  <div class="col-md-6 col-xl-6">
    <div class="card h-100">
      <div class="card-header"><span><span class="card-title-icon tone-teal"><i class="bi bi-thermometer-half"></i></span>Top chief complaints</span></div>
      <div class="card-body"><div class="chart-box"><canvas id="chartComplaints" aria-label="Top chief complaints" role="img"></canvas></div></div>
    </div>
  </div>
  <div class="col-xl-6">
    <div class="card h-100">
      <div class="card-header"><span><span class="card-title-icon tone-violet"><i class="bi bi-capsule-pill"></i></span>Most used medicines &amp; supplies</span></div>
      <div class="card-body"><div class="chart-box"><canvas id="chartUsage" aria-label="Most used medicines and supplies" role="img"></canvas></div></div>
    </div>
  </div>
</div>

<div class="row g-3 g-xl-4">
  <div class="col-xl-7">
    <div class="card table-card h-100">
      <div class="card-header"><span><span class="card-title-icon"><i class="bi bi-clock-history"></i></span>Recent clinic visits</span><a href="?page=visits" class="btn btn-sm btn-soft-primary">View all <i class="bi bi-arrow-right"></i></a></div>
      <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover table-stack">
        <thead><tr><th>Patient</th><th>Chief Complaint</th><th>Date</th></tr></thead>
        <tbody>
        <?php foreach ($recent as $r): ?>
          <tr>
            <td><div class="person"><span class="avatar"><?php echo esc(mb_strtoupper(mb_substr($r['first_name'], 0, 1) . mb_substr($r['last_name'], 0, 1))); ?></span><span class="cell-title"><?php echo esc($r['patient_name']); ?></span></div></td>
            <td><?php echo esc($r['chief_complaint']); ?></td>
            <td class="text-nowrap"><span class="cell-sub"><?php echo esc(visit_date_format($r['visit_date'])); ?></span></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$recent): ?><tr><td colspan="3"><div class="empty-state"><i class="bi bi-clipboard2"></i>No clinic visits recorded yet.</div></td></tr><?php endif; ?>
        </tbody></table></div></div>
    </div>
  </div>
  <div class="col-xl-5">
    <div class="card h-100"><div class="card-header"><span><span class="card-title-icon tone-amber"><i class="bi bi-bell"></i></span>Inventory attention</span><a class="btn btn-sm btn-soft-primary" href="?page=inventory">Manage <i class="bi bi-arrow-right"></i></a></div>
      <div class="card-body">
        <div class="attention-row"><span class="attention-label"><span class="attention-dot" style="background:#475569"></span>Out of stock</span><a href="?page=inventory&filter=out" class="attention-count tone-slate"><?php echo $out; ?></a></div>
        <div class="attention-row"><span class="attention-label"><span class="attention-dot" style="background:#ef4444"></span>Critical stock</span><a href="?page=inventory&filter=critical" class="attention-count tone-red"><?php echo $critical; ?></a></div>
        <div class="attention-row"><span class="attention-label"><span class="attention-dot" style="background:#f59e0b"></span>Low stock</span><a href="?page=inventory&filter=low" class="attention-count tone-amber"><?php echo $low; ?></a></div>
        <div class="attention-row"><span class="attention-label"><span class="attention-dot" style="background:#eab308"></span>Near expiry (30 days)</span><a href="?page=inventory&filter=expiry" class="attention-count tone-amber"><?php echo $near; ?></a></div>
        <div class="attention-row"><span class="attention-label"><span class="attention-dot" style="background:#991b1b"></span>Expired</span><a href="?page=inventory&filter=expired" class="attention-count tone-red"><?php echo $expired; ?></a></div>
        <?php if ($alertItems): ?><div class="detail-label mt-3 mb-1">Items to check</div><?php endif; ?>
        <?php foreach ($alertItems as $ai): ?><div class="attention-item"><i class="bi bi-exclamation-triangle"></i><span class="flex-grow-1 text-truncate"><?php echo esc($ai['item_name']); ?></span><strong><?php echo esc($ai['current_quantity'] . ' ' . $ai['unit']); ?></strong></div><?php endforeach; ?>
        <?php if (!$alertItems): ?><div class="empty-state py-3"><i class="bi bi-check2-circle"></i>All supplies are in good shape.</div><?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?php
require $root . '/partials/modals/patient_form.php';
require $root . '/partials/modals/inventory_form.php';
require $root . '/partials/modals/visit_form.php';
?>
<script>window.DASH = <?php echo json_encode($dash, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?>;</script>
<?php
$extraScripts = array('https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js', 'assets/js/dashboard.js?v=4');
require $root . '/partials/footer.php';
