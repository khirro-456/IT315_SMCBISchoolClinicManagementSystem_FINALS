<?php defined('APP') or exit;
/* Expects: $pdo, $page, $title */
$unread = (int)$pdo->query('SELECT COUNT(*) FROM notifications WHERE is_read=0')->fetchColumn();
$alerts = $pdo->query("SELECT * FROM notifications ORDER BY is_read ASC, FIELD(priority,'critical','warning','info'), created_at DESC LIMIT 10")->fetchAll();
$navItems = array(
    array('dashboard', 'bi-grid-1x2', 'Dashboard'),
    array('patients', 'bi-people', 'Patients'),
    array('inventory', 'bi-capsule', 'Inventory &amp; Supplies'),
    array('visits', 'bi-clipboard2-pulse', 'Clinic Visits'),
    array('reports', 'bi-bar-chart-line', 'Reports')
);
if (is_admin()) {
    $navItems[] = array('users', 'bi-person-gear', 'User Management');
    $navItems[] = array('appearance', 'bi-palette', 'Appearance');
}

$userName = $_SESSION['user']['name'];
$initials = '';
foreach (array_slice(preg_split('/\s+/u', trim($userName)), 0, 2) as $w) { $initials .= mb_strtoupper(mb_substr($w, 0, 1)); }
?>
<!doctype html>
<html lang="en" data-ui-theme="<?php echo esc(current_ui_theme($pdo)); ?>">
<head>
<?php require __DIR__ . '/head.php'; ?>
</head>
<body class="app-body">

<!-- Sidebar: fixed on large screens, slide-out menu on phones and tablets -->
<aside class="sidebar offcanvas-lg offcanvas-start no-print" tabindex="-1" id="appSidebar" aria-labelledby="appSidebarLabel">
  <div class="sidebar-brand">
    <a href="?page=dashboard" class="brand-link" id="appSidebarLabel">
      <span class="brand-mark has-logo"><img src="assets/img/school-logo.png" alt="SMCBI logo"></span>
      <span class="brand-text"><strong>SMCBI School Clinic</strong><small>Management System</small></span>
    </a>
    <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#appSidebar" aria-label="Close menu"></button>
  </div>

  <nav class="sidebar-nav">
    <div class="sidebar-title">Main menu</div>
    <?php foreach ($navItems as $n): ?>
      <a class="sidebar-link <?php echo $page === $n[0] ? 'active' : ''; ?>" href="?page=<?php echo $n[0]; ?>"<?php echo $page === $n[0] ? ' aria-current="page"' : ''; ?>>
        <i class="bi <?php echo $n[1]; ?>"></i><span><?php echo $n[2]; ?></span>
        <?php if ($n[0] === 'inventory' && $unread > 0): ?><span class="sidebar-badge"><?php echo $unread > 99 ? '99+' : $unread; ?></span><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </nav>

  <div class="sidebar-user">
    <div class="d-flex align-items-center gap-2 mb-3">
      <span class="avatar avatar-light"><?php echo esc($initials); ?></span>
      <div class="min-w-0">
        <div class="sidebar-user-name text-truncate"><?php echo esc($userName); ?></div>
        <div class="sidebar-user-role"><i class="bi bi-shield-check"></i> <?php echo esc(ucfirst(current_role())); ?></div>
      </div>
    </div>
    <a class="btn btn-sm sidebar-logout w-100" href="?page=logout"><i class="bi bi-box-arrow-right me-1"></i> Sign out</a>
  </div>
</aside>

<div class="app-main">
  <header class="topbar no-print">
    <button class="btn icon-btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#appSidebar" aria-controls="appSidebar" aria-label="Open menu"><i class="bi bi-list"></i></button>
    <div class="topbar-title">
      <div class="topbar-crumb d-none d-sm-block"><i class="bi bi-heart-pulse"></i> SMCBI School Clinic</div>
      <div class="topbar-page"><?php echo esc($title); ?></div>
    </div>

    <div class="topbar-actions">
      <button type="button" class="btn icon-btn" data-theme-toggle aria-label="Switch light or dark mode" title="Switch light / dark mode">
        <i class="bi bi-moon-stars theme-icon-dark"></i><i class="bi bi-sun theme-icon-light"></i>
      </button>

      <div class="dropdown">
        <button class="btn icon-btn position-relative" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="Notifications">
          <i class="bi bi-bell"></i>
          <?php if ($unread > 0): ?><span class="notif-dot"><?php echo $unread > 99 ? '99+' : $unread; ?></span><?php endif; ?>
        </button>
        <div class="dropdown-menu dropdown-menu-end p-0 notification-menu">
          <div class="notification-head">
            <div><strong>Inventory alerts</strong><div class="small text-body-secondary"><?php echo $unread; ?> unread</div></div>
            <?php if ($unread > 0): ?>
            <form method="post"><input type="hidden" name="action" value="mark_notifications_read"><input type="hidden" name="return" value="<?php echo esc($page); ?>"><?php csrf_field(); ?><button class="btn btn-sm btn-soft-primary"><i class="bi bi-check2-all"></i> Mark all read</button></form>
            <?php endif; ?>
          </div>
          <div class="notification-list">
            <?php foreach ($alerts as $a): $crit = $a['priority'] === 'critical'; ?>
              <a class="notification-item <?php echo $a['is_read'] ? 'read' : ''; ?>" href="?page=inventory">
                <span class="notif-icon <?php echo $crit ? 'is-critical' : 'is-warning'; ?>"><i class="bi <?php echo $crit ? 'bi-exclamation-octagon' : 'bi-exclamation-triangle'; ?>"></i></span>
                <span class="min-w-0">
                  <strong class="d-block text-truncate"><?php echo esc($a['title']); ?></strong>
                  <small class="text-body-secondary"><?php echo esc($a['message']); ?></small>
                </span>
              </a>
            <?php endforeach; ?>
            <?php if (!$alerts): ?><div class="empty-state py-4"><i class="bi bi-check2-circle"></i><div>All clear. No inventory alerts.</div></div><?php endif; ?>
          </div>
          <a class="notification-foot" href="?page=inventory">Open inventory <i class="bi bi-arrow-right"></i></a>
        </div>
      </div>

      <div class="dropdown">
        <button class="btn user-chip" type="button" data-bs-toggle="dropdown" aria-expanded="false">
          <span class="avatar"><?php echo esc($initials); ?></span>
          <span class="d-none d-md-block text-start lh-sm"><span class="d-block fw-semibold"><?php echo esc($userName); ?></span><small class="text-body-secondary"><?php echo esc(ucfirst(current_role())); ?></small></span>
          <i class="bi bi-chevron-down small d-none d-md-inline"></i>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-lg">
          <li><button type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#passwordModal"><i class="bi bi-key me-2"></i>Change password</button></li>
          <li><hr class="dropdown-divider"></li>
          <li><a class="dropdown-item text-danger" href="?page=logout"><i class="bi bi-box-arrow-right me-2"></i>Sign out</a></li>
        </ul>
      </div>
    </div>
  </header>

  <main class="content-area">
    <?php show_flash(); ?>
    <?php if (!empty($_SESSION['must_change_password'])): ?>
    <div class="alert alert-warning d-flex flex-wrap gap-2 justify-content-between align-items-center">
      <div><i class="bi bi-shield-lock me-1"></i><strong>Action required:</strong> set a new password for your account. Saving and editing are locked until you do.</div>
      <button type="button" class="btn btn-sm btn-dark" data-bs-toggle="modal" data-bs-target="#passwordModal">Change Password</button>
    </div>
    <?php endif; ?>
