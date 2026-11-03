<?php defined('APP') or exit;
$title = 'User Management';
$users = $pdo->query('SELECT id,full_name,username,role,status,created_at FROM users ORDER BY role, full_name')->fetchAll();
$activeUsers = count(array_filter($users, function ($u) { return $u['status'] === 'active'; }));
$logs = $pdo->query('SELECT user_name,event,outcome,details,ip,created_at FROM audit_log ORDER BY id DESC LIMIT 100')->fetchAll();
$failedLogins = (int)$pdo->query("SELECT COUNT(*) FROM audit_log WHERE event='login' AND outcome<>'success' AND created_at > DATE_SUB(NOW(), INTERVAL 1 DAY)")->fetchColumn();
require $root . '/partials/header.php';
?>
<div class="page-header">
  <div class="page-header-main">
    <span class="page-icon tone-slate"><i class="bi bi-person-gear"></i></span>
    <div><h2>User Management</h2><p><?php echo $activeUsers; ?> of <?php echo count($users); ?> accounts active &middot; administrators only</p></div>
  </div>
  <div class="page-actions">
    <button class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#passwordModal"><i class="bi bi-key"></i> Change My Password</button>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#userModal"><i class="bi bi-person-plus"></i> Add User Account</button>
  </div>
</div>

<div class="card table-card"><div class="card-header"><span><span class="card-title-icon"><i class="bi bi-shield-lock"></i></span>System accounts</span></div>
  <div class="card-body p-0"><div class="table-responsive"><table class="table table-hover align-middle table-stack">
    <thead><tr><th>User</th><th>Role</th><th>Status</th><th>Created</th><th class="text-end"></th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): $parts = preg_split('/\s+/u', trim($u['full_name'])); $active = $u['status'] === 'active'; ?>
      <tr>
        <td><div class="person"><span class="avatar"><?php echo esc(initials($parts[0], count($parts) > 1 ? end($parts) : '')); ?></span><div class="min-w-0"><div class="cell-title"><?php echo esc($u['full_name']); ?></div><div class="cell-sub"><?php echo esc($u['username']); ?><?php if (!valid_school_email($u['username'])): ?> <span class="badge bg-warning" title="Cannot sign in until it has an @<?php echo esc(SCHOOL_EMAIL_DOMAIN); ?> email. Use the Email button.">Needs school email</span><?php endif; ?></div></div></div></td>
        <td><span class="badge bg-<?php echo $u['role'] === 'admin' ? 'primary' : 'info'; ?>"><i class="bi <?php echo $u['role'] === 'admin' ? 'bi-shield-check' : 'bi-heart-pulse'; ?>"></i><?php echo esc(ucfirst($u['role'])); ?></span></td>
        <td><span class="badge bg-<?php echo $active ? 'success' : 'secondary'; ?>"><i class="bi bi-circle-fill" style="font-size:.5em;vertical-align:middle"></i> <?php echo esc(ucfirst($u['status'])); ?></span></td>
        <td class="text-nowrap"><span class="cell-sub"><?php echo esc(date('M d, Y', strtotime($u['created_at']))); ?></span></td>
        <td class="text-end"><div class="row-actions">
          <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#userEmailModal" data-record="<?php echo json_attr(array('id' => (int)$u['id'], 'full_name' => $u['full_name'], 'username' => $u['username'])); ?>" title="Change login email"><i class="bi bi-envelope"></i> Email</button>
          <?php if ((int)$u['id'] !== (int)$_SESSION['user']['id']):
              $rec = array('id' => (int)$u['id'], 'status' => $u['status'], 'full_name' => $u['full_name'], 'verb' => $active ? 'deactivate' : 'activate'); ?>
            <button class="btn btn-sm <?php echo $active ? 'btn-outline-secondary' : 'btn-soft-primary'; ?>" data-bs-toggle="modal" data-bs-target="#userStatusModal" data-record="<?php echo json_attr($rec); ?>"><i class="bi <?php echo $active ? 'bi-person-dash' : 'bi-person-check'; ?>"></i> <?php echo $active ? 'Deactivate' : 'Activate'; ?></button>
          <?php else: ?><span class="badge bg-secondary align-self-center">You</span><?php endif; ?>
        </div></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table></div></div>
</div>

<div class="card table-card mt-4"><div class="card-header"><span><span class="card-title-icon tone-violet"><i class="bi bi-journal-text"></i></span>Activity log</span><span class="badge bg-<?php echo $failedLogins ? 'danger' : 'secondary'; ?>"><?php echo $failedLogins; ?> failed sign-ins (24h)</span></div>
  <div class="card-body p-0"><div class="table-responsive"><table class="table table-sm table-hover align-middle mb-0 table-stack">
    <thead><tr><th>When</th><th>User</th><th>Activity</th><th>Result</th><th>Details</th><th>IP</th></tr></thead>
    <tbody>
    <?php foreach ($logs as $l): $tone = $l['outcome'] === 'success' ? 'success' : ($l['outcome'] === 'failed' ? 'danger' : 'warning'); ?>
      <tr>
        <td class="text-nowrap"><span class="cell-sub"><?php echo esc(date('M d, g:i A', strtotime($l['created_at']))); ?></span></td>
        <td><?php echo esc($l['user_name'] !== null ? $l['user_name'] : '—'); ?></td>
        <td class="text-nowrap"><?php echo esc(ucfirst($l['event'])); ?></td>
        <td><span class="badge bg-<?php echo $tone; ?>"><?php echo esc(ucfirst($l['outcome'])); ?></span></td>
        <td class="small text-muted"><?php echo esc($l['details']); ?></td>
        <td class="text-nowrap"><span class="cell-sub"><?php echo esc($l['ip']); ?></span></td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$logs): ?><tr><td colspan="6"><div class="empty-state"><i class="bi bi-journal"></i>No activity recorded yet.</div></td></tr><?php endif; ?>
    </tbody>
  </table></div></div>
  <div class="card-footer small text-muted">Latest 100 entries. Sign-ins, sign-outs and every saved change are recorded; entries older than <?php echo AUDIT_KEEP_DAYS; ?> days are removed automatically.</div>
</div>

<?php
require $root . '/partials/modals/user_form.php';
require $root . '/partials/footer.php';
