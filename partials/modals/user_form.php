<?php defined('APP') or exit; ?>
<!-- Add User Account (admin only) -->
<div class="modal fade" id="userModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" data-fillable autocomplete="off">
        <input type="hidden" name="action" value="save_user">
        <?php csrf_field(); ?>
        <div class="modal-header"><h5 class="modal-title">Add User Account</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <div class="mb-3"><label class="form-label">Full Name *</label>
            <input class="form-control name-input" name="full_name" required minlength="2" maxlength="150" pattern="<?php echo name_pattern(); ?>" title="Letters only."></div>
          <div class="mb-3"><label class="form-label">School Email *</label>
            <input class="form-control" name="username" required <?php echo school_email_attrs(); ?>>
            <div class="form-text">The user signs in with this email. Only @<?php echo esc(SCHOOL_EMAIL_DOMAIN); ?> addresses are accepted.</div></div>
          <div class="mb-3"><label class="form-label">Password *</label>
            <input class="form-control" type="password" name="password" required autocomplete="new-password" <?php echo password_pattern_attrs(); ?>>
            <div class="form-text">At least 8 characters, with letters and numbers. Must not contain the email name (the part before @).</div></div>
          <div class="mb-0"><label class="form-label">Role *</label>
            <select class="form-select" name="role"><option value="nurse">Nurse</option><option value="admin">Admin</option></select></div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Create Account</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Change an account's login email (admin only) -->
<div class="modal fade" id="userEmailModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" data-fillable autocomplete="off">
        <input type="hidden" name="action" value="set_user_email">
        <input type="hidden" name="id" value="">
        <input type="hidden" name="full_name" value="">
        <?php csrf_field(); ?>
        <div class="modal-header"><h5 class="modal-title">Change Login Email</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body">
          <p class="text-muted small">Account: <strong data-fill-text="full_name"></strong></p>
          <label class="form-label">School Email *</label>
          <input class="form-control" name="username" required <?php echo school_email_attrs(); ?>>
          <div class="form-text">This account signs in with the new email from now on. The password stays the same.</div>
        </div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button><button class="btn btn-primary">Save Email</button></div>
      </form>
    </div>
  </div>
</div>

<!-- Activate / Deactivate confirmation -->
<div class="modal fade" id="userStatusModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <form method="post" data-fillable>
        <input type="hidden" name="action" value="toggle_user_status">
        <input type="hidden" name="id" value="">
        <input type="hidden" name="status" value="">
        <?php csrf_field(); ?>
        <div class="modal-header"><h5 class="modal-title">Change Account Status</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
        <div class="modal-body"><p class="mb-0">Are you sure you want to <strong data-fill-text="verb">change</strong> the account of <strong data-fill-text="full_name"></strong>?</p></div>
        <div class="modal-footer"><button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">No</button><button class="btn btn-primary">Yes, continue</button></div>
      </form>
    </div>
  </div>
</div>
