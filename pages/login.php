<?php defined('APP') or exit;
$title = 'Sign In';
/* Login banner: web link from config, otherwise the first image found in assets/img (clinic-login.jpg preferred).
   It is shown in full across the top of the left side, so a wide banner looks best. */
$loginImage = trim($config['login_image_url']);
$loginSize = '';
if ($loginImage === '') {
    $dir = $root . '/assets/img';
    if (is_dir($dir)) {
        foreach (scandir($dir) as $f) {
            if (preg_match('/\.(jpe?g|png|webp|gif)$/i', $f) && ($loginImage === '' || strtolower($f) === 'clinic-login.jpg')) {
                $loginImage = 'assets/img/' . rawurlencode($f) . '?v=' . filemtime($dir . '/' . $f); /* new picture shows at once */
                $size = getimagesize($dir . '/' . $f);
                $loginSize = $size ? ' width="' . $size[0] . '" height="' . $size[1] . '"' : '';  /* no jump while it loads */
            }
        }
    }
}
?>
<!doctype html>
<html lang="en" data-ui-theme="<?php echo esc(current_ui_theme($pdo)); ?>">
<head>
<?php require $root . '/partials/head.php'; ?>
</head>
<body class="login-body">
<div class="login-page<?php echo $loginImage !== '' ? ' has-banner' : ''; ?>">
  <section class="login-visual">
    <?php if ($loginImage !== ''): ?><img class="login-banner" src="<?php echo esc($loginImage); ?>"<?php echo $loginSize; ?> alt="St. Mary's College of Bansalan, Inc." fetchpriority="high"><?php endif; ?>
    <div class="login-visual-content">
      <div class="login-brand">SMCBI School Clinic Management System</div>
      <span class="hero-eyebrow"><i class="bi bi-shield-check"></i> Trusted by your school clinic team</span>
      <h1>Better records.<br>Safer supplies.<br>Faster care.</h1>
      <p class="lead mt-3">One secure place for patient records, clinic visits, inventory alerts, and reports.</p>
      <div class="login-features">
        <div class="login-feature"><i class="bi bi-person-vcard"></i>Patient records</div>
        <div class="login-feature"><i class="bi bi-bell"></i>Inventory alerts</div>
        <div class="login-feature"><i class="bi bi-graph-up-arrow"></i>Live analytics</div>
      </div>
    </div>
  </section>

  <section class="login-form-side">
    <button type="button" class="btn icon-btn login-theme" data-theme-toggle aria-label="Switch light or dark mode" title="Switch light / dark mode">
      <i class="bi bi-moon-stars theme-icon-dark"></i><i class="bi bi-sun theme-icon-light"></i>
    </button>
    <div class="login-form-card">
      <div class="login-mark has-logo"><img src="assets/img/school-logo.png" alt="SMCBI logo"></div>
      <h2 class="mb-1">Welcome back</h2>
      <p class="text-muted mb-4">Sign in to continue to the SMCBI School Clinic Management System.</p>
      <?php show_flash(); ?>
      <form method="post" autocomplete="off" id="loginForm">
        <input type="hidden" name="action" value="login">
        <?php csrf_field(); ?>
        <div class="mb-3">
          <label class="form-label" for="loginUser">School email</label>
          <div class="input-icon"><i class="bi bi-envelope"></i>
            <input class="form-control form-control-lg" id="loginUser" name="username" required <?php echo school_email_attrs(); ?> autocomplete="username" autofocus></div>
        </div>
        <div class="mb-4">
          <label class="form-label" for="loginPass">Password</label>
          <div class="input-icon position-relative"><i class="bi bi-lock"></i>
            <input class="form-control form-control-lg pe-5" id="loginPass" type="password" name="password" required minlength="8" autocomplete="current-password" placeholder="Enter your password">
            <button type="button" class="password-toggle" data-password-toggle="#loginPass" aria-label="Show password"><i class="bi bi-eye"></i></button>
          </div>
        </div>
        <button class="btn btn-primary btn-lg w-100" id="loginBtn"><span>Sign in securely</span><i class="bi bi-arrow-right"></i></button>
      </form>
      <div class="login-secure"><i class="bi bi-shield-lock"></i>Access is restricted to authorized clinic personnel with an @<?php echo esc(SCHOOL_EMAIL_DOMAIN); ?> school email. Menus depend on your assigned role.</div>
      <a class="login-alt" href="?page=student"><i class="bi bi-person-vcard"></i>Student or employee? View your clinic record</a>
    </div>
  </section>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
  /* show / hide password */
  document.querySelectorAll('[data-password-toggle]').forEach(function (b) {
    b.addEventListener('click', function () {
      var input = document.querySelector(b.getAttribute('data-password-toggle')), show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      b.innerHTML = show ? '<i class="bi bi-eye-slash"></i>' : '<i class="bi bi-eye"></i>';
      b.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
  });
  /* school emails are typed in lowercase without spaces */
  var user = document.getElementById('loginUser');
  user.addEventListener('input', function () { var v = user.value.replace(/\s/g, '').toLowerCase(); if (v !== user.value) user.value = v; });
  /* loading state so nobody double-submits */
  document.getElementById('loginForm').addEventListener('submit', function () {
    var btn = document.getElementById('loginBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span><span>Signing in...</span>';
  });
  /* light / dark switch */
  document.querySelectorAll('[data-theme-toggle]').forEach(function (b) {
    b.addEventListener('click', function () {
      var next = document.documentElement.getAttribute('data-bs-theme') === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-bs-theme', next);
      try { localStorage.setItem('clinic-theme', next); } catch (e) {}
    });
  });
})();
</script>
</body>
</html>
