<?php defined('APP') or exit;
$title = 'Appearance';
$themes = ui_themes();
$current = current_ui_theme($pdo);
require $root . '/partials/header.php';
?>
<div class="page-header">
  <div class="page-header-main">
    <span class="page-icon tone-violet"><i class="bi bi-palette"></i></span>
    <div><h2>Appearance</h2><p>Current theme: <strong><?php echo esc($themes[$current]['name']); ?></strong> &middot; applies to every user &middot; administrators only</p></div>
  </div>
  <div class="page-actions"><button class="btn btn-primary" form="themeForm"><i class="bi bi-check2-circle"></i> Save Theme</button></div>
</div>

<div class="alert alert-info"><i class="bi bi-eye me-1"></i> Click a theme to preview it on this page. Press <strong>Save Theme</strong> to apply it for everyone. Each user can still switch light / dark mode with the <i class="bi bi-moon-stars"></i> button, and every theme supports both.</div>

<form method="post" id="themeForm">
  <input type="hidden" name="action" value="save_theme">
  <?php csrf_field(); ?>
  <div class="row g-3">
  <?php foreach ($themes as $key => $t): $c = $t['preview']; ?>
    <div class="col-sm-6 col-xl-4">
      <input type="radio" class="btn-check" name="theme" id="theme-<?php echo esc($key); ?>" value="<?php echo esc($key); ?>" data-theme-preview<?php echo $key === $current ? ' checked' : ''; ?>>
      <label class="theme-option" for="theme-<?php echo esc($key); ?>">
        <span class="theme-mock" style="<?php echo esc('--m-bg:' . $c[0] . ';--m-side:' . $c[1] . ';--m-card:' . $c[2] . ';--m-brand:' . $c[3] . ';--m-accent:' . $c[4]); ?>" aria-hidden="true">
          <span class="theme-mock-side"></span>
          <span class="theme-mock-main"><span class="theme-mock-bar"></span><span class="theme-mock-cards"><span></span><span></span></span><span class="theme-mock-btn"></span></span>
        </span>
        <i class="bi bi-check-circle-fill theme-option-check" aria-hidden="true"></i>
        <span class="theme-option-name"><?php echo esc($t['name']); ?><?php if ($key === $current): ?> <span class="badge bg-success">In use</span><?php endif; ?></span>
        <span class="theme-option-desc"><?php echo esc($t['desc']); ?></span>
      </label>
    </div>
  <?php endforeach; ?>
  </div>
</form>

<?php
require $root . '/partials/footer.php';
