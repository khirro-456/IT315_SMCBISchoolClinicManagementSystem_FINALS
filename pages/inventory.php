<?php defined('APP') or exit;
$title = 'Inventory & Supplies';

/* ---- filter ---- */
$filters = array(
    'all' => array('All Items', ''),
    'medicine' => array('Medicines', " WHERE category='Medicine'"),
    'supplies' => array('Supplies', " WHERE category<>'Medicine'"),
    'low' => array('Low Stock', ' WHERE current_quantity>LEAST(5,minimum_stock) AND current_quantity>0 AND current_quantity<=minimum_stock'),
    'critical' => array('Critical', ' WHERE current_quantity BETWEEN 1 AND LEAST(5,minimum_stock)'),
    'out' => array('Out of Stock', ' WHERE current_quantity=0'),
    'expiry' => array('Near Expiry', ' WHERE expiration_date IS NOT NULL AND expiration_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)'),
    'expired' => array('Expired', ' WHERE expiration_date IS NOT NULL AND expiration_date<CURDATE()')
);
$filter = (isset($_GET['filter']) && isset($filters[$_GET['filter']])) ? $_GET['filter'] : 'all';

$items = $pdo->query('SELECT * FROM inventory_items' . $filters[$filter][1] . ' ORDER BY item_name')->fetchAll();
$trans = $pdo->query('SELECT t.*, i.item_name, i.unit, u.full_name FROM inventory_transactions t JOIN inventory_items i ON i.id=t.inventory_item_id LEFT JOIN users u ON u.id=t.processed_by ORDER BY t.transaction_date DESC, t.id DESC LIMIT 100')->fetchAll();
$nMeds = (int)$pdo->query("SELECT COUNT(*) FROM inventory_items WHERE category='Medicine'")->fetchColumn();

$stockIcons = array('success' => 'bi-check-circle', 'warning' => 'bi-exclamation-triangle', 'danger' => 'bi-exclamation-octagon', 'dark' => 'bi-x-circle');
$typeStyle = array(
    'Stock In' => array('success', 'bi-box-arrow-in-down'),
    'Stock Out' => array('danger', 'bi-box-arrow-up'),
    'Visit Usage' => array('primary', 'bi-clipboard2-pulse'),
    'Adjustment' => array('warning', 'bi-sliders')
);

require $root . '/partials/header.php';
?>
<div class="page-header">
  <div class="page-header-main">
    <span class="page-icon tone-violet"><i class="bi bi-capsule"></i></span>
    <div><h2>Inventory &amp; Supplies</h2><p><?php echo $nMeds; ?> medicines registered &middot; deliveries, usage, disposal and corrections</p></div>
  </div>
  <div class="page-actions">
    <form method="post"><input type="hidden" name="action" value="load_sample_inventory"><?php csrf_field(); ?><button class="btn btn-outline-secondary"><i class="bi bi-download"></i> Load 30 Clinic Medicines + Supplies</button></form>
    <button class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#supplyModal"><i class="bi bi-arrow-left-right"></i> Supply Transaction</button>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#inventoryModal"><i class="bi bi-plus-lg"></i> Add Medicine / Supply</button>
  </div>
</div>

<div class="card table-card mb-4"><div class="card-body">
  <div class="d-flex flex-column flex-lg-row align-items-lg-start gap-2 mb-3 no-print">
    <nav class="filter-pills flex-grow-1" aria-label="Filter inventory">
      <?php foreach ($filters as $key => $f): ?>
        <a class="filter-pill <?php echo $filter === $key ? 'active' : ''; ?>" href="?page=inventory&filter=<?php echo $key; ?>"<?php echo $filter === $key ? ' aria-current="page"' : ''; ?>><?php echo $f[0]; ?></a>
      <?php endforeach; ?>
    </nav>
    <div class="input-icon search-input flex-shrink-0"><i class="bi bi-search"></i><input type="search" class="form-control" data-filter=".inv-row" placeholder="Search item..." aria-label="Search items"></div>
  </div>
  <div class="scroll-box">
    <table class="table table-hover align-middle table-stack">
      <thead><tr><th>Medicine / Item</th><th>Category</th><th>Stock</th><th>Expiration</th><th>Supplier</th><th class="text-end"></th></tr></thead>
      <tbody>
      <?php foreach ($items as $it):
          $s = stock_status((int)$it['current_quantity'], (int)$it['minimum_stock']);
          $e = expiry_status($it['expiration_date']);
          $rec = $it; $rec['quantity_label'] = $it['current_quantity'] . ' ' . $it['unit'];
          /* bar is full at twice the reorder level */
          $pct = max(0, min(100, (int)round((int)$it['current_quantity'] / max(1, 2 * (int)$it['minimum_stock']) * 100)));
      ?>
        <tr class="inv-row">
          <td><div class="person"><span class="avatar tone-violet"><i class="bi <?php echo $it['category'] === 'Medicine' ? 'bi-capsule-pill' : 'bi-bandaid'; ?>"></i></span><div class="min-w-0"><div class="cell-title"><?php echo esc($it['item_name']); ?></div><div class="cell-sub"><?php echo esc($it['item_code']); ?> &middot; reorder at <?php echo (int)$it['minimum_stock']; ?></div></div></div></td>
          <td><span class="badge bg-secondary"><?php echo esc($it['category']); ?></span></td>
          <td>
            <div class="stock-cell">
              <div class="d-flex justify-content-between gap-2"><strong><?php echo esc($it['current_quantity'] . ' ' . $it['unit']); ?></strong><span class="badge bg-<?php echo $s['color']; ?>"><i class="bi <?php echo $stockIcons[$s['color']]; ?>"></i><?php echo esc($s['label']); ?></span></div>
              <div class="stock-bar"><span class="is-<?php echo $s['color']; ?>" style="width:<?php echo $pct; ?>%"></span></div>
            </div>
          </td>
          <td><?php if ($it['expiration_date']): ?><div class="cell-title"><?php echo esc(date('M d, Y', strtotime($it['expiration_date']))); ?></div><span class="badge bg-<?php echo $e['color']; ?>"><?php echo esc($e['label']); ?></span><?php else: ?><span class="cell-sub">No expiry date</span><?php endif; ?></td>
          <td><span class="cell-sub"><?php echo esc($it['supplier'] ?: '—'); ?></span></td>
          <td class="text-end"><div class="row-actions">
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#inventoryModal" data-record="<?php echo json_attr($rec); ?>" title="Edit item"><i class="bi bi-pencil"></i><span class="d-md-none d-xxl-inline">Edit</span></button>
            <button class="btn btn-sm btn-soft-primary" data-bs-toggle="modal" data-bs-target="#supplyModal" data-record="<?php echo json_attr(array('item_id' => (int)$it['id'])); ?>"><i class="bi bi-arrow-left-right"></i> Stock</button>
          </div></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$items): ?><tr><td colspan="6"><div class="empty-state"><i class="bi bi-inbox"></i>No items found<?php echo $filter === 'all' ? '. Click "Load 30 Clinic Medicines + Supplies" to get started.' : ' for this filter.'; ?></div></td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div></div>

<div class="card table-card">
  <div class="card-header"><span><span class="card-title-icon"><i class="bi bi-clock-history"></i></span>Recent supply transactions</span><span class="badge bg-secondary">Last 100</span></div>
  <div class="card-body p-0"><div class="scroll-box" style="border:0;border-radius:0">
    <table class="table table-hover align-middle table-stack">
      <thead><tr><th>Item</th><th>Type</th><th>Reason</th><th>Qty</th><th>Description</th><th>By</th><th>Date</th></tr></thead>
      <tbody>
      <?php foreach ($trans as $t): $ts = isset($typeStyle[$t['transaction_type']]) ? $typeStyle[$t['transaction_type']] : array('secondary', 'bi-dot'); ?>
        <tr>
          <td><span class="cell-title"><?php echo esc($t['item_name']); ?></span></td>
          <td><span class="badge bg-<?php echo $ts[0]; ?>"><i class="bi <?php echo $ts[1]; ?>"></i><?php echo esc($t['transaction_type']); ?></span></td>
          <td><?php echo esc($t['transaction_reason']); ?></td>
          <td class="text-nowrap"><strong><?php echo esc($t['quantity']); ?></strong> <span class="cell-sub"><?php echo esc($t['unit']); ?></span></td>
          <td><span class="cell-sub"><?php echo esc($t['description']); ?></span></td>
          <td><?php echo esc($t['full_name'] ?: 'System'); ?></td>
          <td class="text-nowrap"><span class="cell-sub"><?php echo esc(visit_date_format($t['transaction_date'])); ?></span></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$trans): ?><tr><td colspan="7"><div class="empty-state"><i class="bi bi-arrow-left-right"></i>No supply transactions yet.</div></td></tr><?php endif; ?>
      </tbody>
    </table>
  </div></div>
</div>

<?php
require $root . '/partials/modals/inventory_form.php';
require $root . '/partials/modals/supply_transaction.php';
require $root . '/partials/footer.php';
