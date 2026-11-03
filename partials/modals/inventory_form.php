<?php defined('APP') or exit; ?>
<!-- Add / Edit Medicine or Supply -->
<div class="modal fade" id="inventoryModal" tabindex="-1" aria-hidden="true" data-editable="1">
  <div class="modal-dialog modal-lg modal-dialog-scrollable modal-fullscreen-sm-down">
    <div class="modal-content">
      <form method="post" data-fillable autocomplete="off">
        <input type="hidden" name="action" value="save_inventory">
        <input type="hidden" name="id" value="">
        <input type="hidden" name="return" value="<?php echo esc($page); ?>">
        <?php csrf_field(); ?>
        <div class="modal-header">
          <h5 class="modal-title" data-add="Add Medicine / Supply" data-edit="Edit Medicine / Supply">Add Medicine / Supply</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-4"><label class="form-label">Item Code *</label>
              <input class="form-control" name="item_code" required minlength="3" maxlength="50" pattern="[A-Za-z0-9\-]+" title="Letters, numbers, hyphen. Example: MED-031"></div>
            <div class="col-md-5"><label class="form-label">Medicine / Item Name *</label>
              <input class="form-control" name="item_name" required minlength="2" maxlength="150"></div>
            <div class="col-md-3"><label class="form-label">Unit *</label>
              <input class="form-control" name="unit" required minlength="2" maxlength="50" pattern="[A-Za-z \-]+" title="Letters only." value="tablet"></div>
            <div class="col-md-4"><label class="form-label">Category *</label>
              <select class="form-select" name="category"><option>Medicine</option><option>First Aid Supply</option><option>Medical Equipment</option><option>Clinic Supply</option></select></div>
            <div class="col-md-4 add-only"><label class="form-label">Initial Quantity *</label>
              <input class="form-control" type="number" min="0" step="1" name="initial_quantity" value="0" required></div>
            <div class="col-md-4 edit-only d-none"><label class="form-label">Current Quantity</label>
              <input class="form-control" readonly data-fill="quantity_label" disabled>
              <div class="form-text">Change it with Supply Transaction.</div></div>
            <div class="col-md-4"><label class="form-label">Minimum Stock Level *</label>
              <input class="form-control" type="number" min="1" step="1" name="minimum_stock" value="10" required></div>
            <div class="col-md-6"><label class="form-label">Expiration Date</label>
              <input class="form-control" type="date" name="expiration_date"></div>
            <div class="col-md-6"><label class="form-label">Supplier</label>
              <input class="form-control" name="supplier" maxlength="150"></div>
            <div class="col-12"><label class="form-label">Remarks</label>
              <textarea class="form-control" rows="2" name="remarks"></textarea></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-primary">Save Medicine / Supply</button>
        </div>
      </form>
    </div>
  </div>
</div>
