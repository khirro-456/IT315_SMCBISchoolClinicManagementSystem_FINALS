<?php defined('APP') or exit; ?>
<!-- Change My Password (all roles) -->
<div class="modal fade" id="passwordModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" autocomplete="off">
        <input type="hidden" name="action" value="change_password">
        <input type="hidden" name="return" value="<?php echo esc($page); ?>">
        <?php csrf_field(); ?>
        <div class="modal-header"><h5 class="modal-title">Change My Password</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Current Password</label><input class="form-control" type="password" name="current_password" required autocomplete="current-password"></div>
          <div class="mb-0"><label class="form-label">New Password</label><input class="form-control" type="password" name="new_password" required autocomplete="new-password" <?php echo password_pattern_attrs(); ?>>
            <div class="form-text">At least 8 characters, with letters and numbers. Must not contain your email name (the part before @).</div></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Change Password</button></div>
      </form>
    </div>
  </div>
</div>
