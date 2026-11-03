<?php defined('APP') or exit; $stItems = fetch_all_items_short($pdo); ?>
<!-- Supply Transaction: delivery, damage, expired/disposal, lost, correction -->
<div class="modal fade" id="supplyModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable modal-fullscreen-sm-down">
    <div class="modal-content">
      <form method="post" data-fillable autocomplete="off">
        <input type="hidden" name="action" value="supply_transaction">
        <input type="hidden" name="return" value="<?php echo esc($page); ?>">
        <?php csrf_field(); ?>
        <div class="modal-header">
          <h5 class="modal-title">Supply Transaction</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <p class="text-muted small">Record deliveries, damaged items, expired/disposed items, missing items, or quantity corrections.</p>
          <div class="mb-3"><label class="form-label">Medicine / Supply *</label>
            <select class="form-select" name="item_id" required><option value="">Select an item</option>
            <?php foreach ($stItems as $a): ?><option value="<?php echo (int)$a['id']; ?>"><?php echo esc($a['item_name'] . ' - Available: ' . $a['current_quantity'] . ' ' . $a['unit']); ?></option><?php endforeach; ?>
            </select></div>
          <div class="mb-3"><label class="form-label">Transaction Type *</label>
            <select class="form-select" name="transaction_type" data-reason-target="#trReason" required>
              <option value="Stock In">Delivery (Stock In)</option>
              <option value="Stock Out">Remove Stock (Damage / Expired / Lost)</option>
              <option value="Adjustment">Quantity Correction</option>
            </select></div>
          <div class="mb-3"><label class="form-label">Reason *</label>
            <select class="form-select" name="transaction_reason" id="trReason" required></select></div>
          <div class="mb-3"><label class="form-label">Quantity *</label>
            <input class="form-control" type="number" name="quantity" min="0" step="1" required>
            <small class="text-muted">For a correction, enter the corrected final quantity.</small></div>
          <div class="mb-0"><label class="form-label">Description *</label>
            <textarea class="form-control" name="description" rows="3" required minlength="5" maxlength="255" placeholder="Example: 10 boxes received from supplier."></textarea></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary">Save Supply Transaction</button>
        </div>
      </form>
    </div>
  </div>
</div>
