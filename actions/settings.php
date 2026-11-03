<?php defined('APP') or exit;

if ($action === 'save_theme') {
    require_admin($pdo);
    $themes = ui_themes();
    $theme = p('theme');
    if (!isset($themes[$theme])) {
        flash('danger', 'Please choose one of the listed themes.');
        redirect_to('?page=appearance');
    }
    meta_set($pdo, 'ui_theme', $theme);
    flash('success', 'Theme changed to ' . $themes[$theme]['name'] . ' for all users.');
    redirect_to('?page=appearance');
}
