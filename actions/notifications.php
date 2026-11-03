<?php defined('APP') or exit;

if ($action === 'mark_notifications_read') {
    $pdo->query('UPDATE notifications SET is_read=1, read_at=NOW() WHERE is_read=0');
    flash('success', 'All notifications were marked as read.');
    redirect_to(return_page('dashboard'));
}
