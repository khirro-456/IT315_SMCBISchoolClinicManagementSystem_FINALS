<?php defined('APP') or exit;
$title = 'Reports';

$totalVisits = (int)$pdo->query('SELECT COUNT(*) FROM clinic_visits')->fetchColumn();
$monthVisits = (int)$pdo->query("SELECT COUNT(*) FROM clinic_visits WHERE DATE_FORMAT(visit_date,'%Y-%m')=DATE_FORMAT(CURDATE(),'%Y-%m')")->fetchColumn();
$totalPatients = (int)$pdo->query('SELECT COUNT(*) FROM patients')->fetchColumn();
$totalItems = (int)$pdo->query('SELECT COUNT(*) FROM inventory_items')->fetchColumn();
$needAction = (int)$pdo->query('SELECT COUNT(*) FROM inventory_items WHERE current_quantity<=minimum_stock OR (expiration_date IS NOT NULL AND expiration_date<=DATE_ADD(CURDATE(), INTERVAL 30 DAY))')->fetchColumn();

$monthly = $pdo->query("SELECT DATE_FORMAT(visit_date,'%Y-%m') AS ym, COUNT(*) AS total FROM clinic_visits GROUP BY ym ORDER BY ym DESC LIMIT 12")->fetchAll();
$complaints = $pdo->query('SELECT chief_complaint, COUNT(*) AS total FROM clinic_visits GROUP BY chief_complaint ORDER BY total DESC LIMIT 20')->fetchAll();
$staff = $pdo->query('SELECT u.full_name, u.role, COUNT(v.id) AS total_visits FROM clinic_visits v JOIN users u ON u.id=v.attended_by GROUP BY u.id,u.full_name,u.role ORDER BY total_visits DESC')->fetchAll();
$items = $pdo->query('SELECT * FROM inventory_items ORDER BY item_name')->fetchAll();
$usage = $pdo->query('SELECT i.item_name, i.unit, COALESCE(SUM(vi.quantity_used),0) AS total_used FROM inventory_items i LEFT JOIN visit_items vi ON vi.inventory_item_id=i.id GROUP BY i.id,i.item_name,i.unit ORDER BY total_used DESC LIMIT 20')->fetchAll();

/* modal id, title, value, caption, colour tone, icon */
$cards = array(
    array('reportVisitsModal', 'Clinic Visits', $totalVisits, $monthVisits . ' this month', 'blue', 'bi-calendar2-check'),
    array('reportComplaintsModal', 'Common Complaints', count($complaints), 'distinct complaints', 'teal', 'bi-thermometer-half'),
    array('reportStaffModal', 'Duty Staff Summary', count($staff), 'staff with recorded visits', 'green', 'bi-person-badge'),
    array('reportInventoryModal', 'Inventory Status', $totalItems, $needAction . ' need attention', 'amber', 'bi-box-seam'),
    array('reportUsageModal', 'Medicine Usage', count($usage), 'items tracked', 'violet', 'bi-capsule-pill')
);

require $root . '/partials/header.php';
?>
<div class="page-header">
  <div class="page-header-main">
    <span class="page-icon tone-teal"><i class="bi bi-bar-chart-line"></i></span>
    <div><h2>Reports</h2><p>Open a report to see the details, then print it &middot; <?php echo $totalPatients; ?> patients registered</p></div>
  </div>
</div>

<div class="row g-3 g-xl-4">
<?php foreach ($cards as $c): ?>
  <div class="col-sm-6 col-xl-4">
    <div class="card report-card">
      <div class="stat-top mb-2"><span class="stat-icon tone-<?php echo $c[4]; ?>"><i class="bi <?php echo $c[5]; ?>"></i></span><span class="badge bg-secondary"><i class="bi bi-printer"></i>Printable</span></div>
      <div class="stat-label mt-2"><?php echo $c[1]; ?></div>
      <div class="stat-value"><?php echo $c[2]; ?></div>
      <small class="text-muted mb-3"><?php echo esc($c[3]); ?></small>
      <button class="btn btn-outline-secondary mt-auto" data-bs-toggle="modal" data-bs-target="#<?php echo $c[0]; ?>">View report <i class="bi bi-arrow-right"></i></button>
    </div>
  </div>
<?php endforeach; ?>
</div>

<!-- Clinic visits per month -->
<div class="modal fade" id="reportVisitsModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title">Clinic Visits Report</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <h6>Clinic Visit Summary</h6><p class="mb-1">Total recorded visits: <strong><?php echo $totalVisits; ?></strong></p><p>Visits this month: <strong><?php echo $monthVisits; ?></strong></p>
    <table class="table table-sm"><thead><tr><th>Month</th><th>Visits</th></tr></thead><tbody>
    <?php foreach ($monthly as $m): ?><tr><td><?php echo esc($m['ym']); ?></td><td><?php echo (int)$m['total']; ?></td></tr><?php endforeach; ?>
    <?php if (!$monthly): ?><tr><td colspan="2" class="text-muted text-center">No visit data.</td></tr><?php endif; ?>
    </tbody></table>
  </div>
  <div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button><button class="btn btn-primary" onclick="printModal(this)">Print</button></div>
</div></div></div>

<!-- Common complaints -->
<div class="modal fade" id="reportComplaintsModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title">Most Common Complaints</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <table class="table table-sm"><thead><tr><th>Complaint</th><th>Total</th></tr></thead><tbody>
    <?php foreach ($complaints as $c): ?><tr><td><?php echo esc($c['chief_complaint']); ?></td><td><?php echo (int)$c['total']; ?></td></tr><?php endforeach; ?>
    <?php if (!$complaints): ?><tr><td colspan="2" class="text-muted text-center">No visit data.</td></tr><?php endif; ?>
    </tbody></table>
  </div>
  <div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button><button class="btn btn-primary" onclick="printModal(this)">Print</button></div>
</div></div></div>

<!-- Duty staff -->
<div class="modal fade" id="reportStaffModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title">Duty Staff Summary</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <table class="table table-sm table-stack"><thead><tr><th>Staff</th><th>Role</th><th>Visits</th></tr></thead><tbody>
    <?php foreach ($staff as $s): ?><tr><td><?php echo esc($s['full_name']); ?></td><td><?php echo esc(ucfirst($s['role'])); ?></td><td><?php echo (int)$s['total_visits']; ?></td></tr><?php endforeach; ?>
    <?php if (!$staff): ?><tr><td colspan="3" class="text-muted text-center">No visit data.</td></tr><?php endif; ?>
    </tbody></table>
  </div>
  <div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button><button class="btn btn-primary" onclick="printModal(this)">Print</button></div>
</div></div></div>

<!-- Inventory status -->
<div class="modal fade" id="reportInventoryModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title">Inventory Status Report</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <input type="search" class="form-control mb-3 no-print" data-filter=".rep-row" placeholder="Search medicine or supply...">
    <table class="table table-sm table-stack"><thead><tr><th>Item</th><th>Stock</th><th>Minimum</th><th>Stock Alert</th><th>Expiry</th></tr></thead><tbody>
    <?php foreach ($items as $it): $s = stock_status((int)$it['current_quantity'], (int)$it['minimum_stock']); $e = expiry_status($it['expiration_date']); ?>
      <tr class="rep-row"><td><?php echo esc($it['item_name']); ?></td><td><?php echo esc($it['current_quantity'] . ' ' . $it['unit']); ?></td><td><?php echo (int)$it['minimum_stock']; ?></td>
      <td><span class="badge bg-<?php echo $s['color']; ?> <?php echo $s['color'] === 'warning' ? 'text-dark' : ''; ?>"><?php echo esc($s['label']); ?></span></td>
      <td><span class="badge bg-<?php echo $e['color']; ?> <?php echo $e['color'] === 'warning' ? 'text-dark' : ''; ?>"><?php echo esc($e['label']); ?></span></td></tr>
    <?php endforeach; ?>
    <?php if (!$items): ?><tr><td colspan="5" class="text-muted text-center">No inventory data.</td></tr><?php endif; ?>
    </tbody></table>
  </div>
  <div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button><button class="btn btn-primary" onclick="printModal(this)">Print</button></div>
</div></div></div>

<!-- Medicine usage -->
<div class="modal fade" id="reportUsageModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title">Medicine &amp; Supply Usage</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <table class="table table-sm"><thead><tr><th>Item</th><th>Total Used</th></tr></thead><tbody>
    <?php foreach ($usage as $u): ?><tr><td><?php echo esc($u['item_name']); ?></td><td><?php echo esc($u['total_used'] . ' ' . $u['unit']); ?></td></tr><?php endforeach; ?>
    <?php if (!$usage): ?><tr><td colspan="2" class="text-muted text-center">No usage data.</td></tr><?php endif; ?>
    </tbody></table>
  </div>
  <div class="modal-footer"><button class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button><button class="btn btn-primary" onclick="printModal(this)">Print</button></div>
</div></div></div>

<?php require $root . '/partials/footer.php';
