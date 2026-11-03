<?php defined('APP') or exit;

/* Login form (runs before require_login) */
if ($action === 'login') {
    verify_csrf();
    $un = strtolower(p('username'));
    if (!valid_school_email($un)) {
        flash('danger', 'Sign in with your school email (ending in @' . SCHOOL_EMAIL_DOMAIN . ').');
        redirect_to('?page=login');
    }
    if (login_locked($pdo, $un)) {
        flash('danger', 'Too many failed sign-in attempts. Please wait ' . LOGIN_LOCK_MINUTES . ' minutes and try again.');
        redirect_to('?page=login');
    }
    $st = $pdo->prepare("SELECT * FROM users WHERE username=? AND status='active' LIMIT 1");
    $st->execute(array($un));
    $u = $st->fetch();
    $pw = (string)$_POST['password'];
    if ($u && password_verify($pw, $u['password'])) {
        clear_login_failures($pdo, $un);
        /* anyone still using the published default password must replace it */
        if ($pw === DEFAULT_ADMIN_PASSWORD) $pdo->prepare('UPDATE users SET must_change_password=1 WHERE id=?')->execute(array((int)$u['id']));
        session_regenerate_id(true);
        $_SESSION['user'] = array('id' => (int)$u['id'], 'name' => $u['full_name'], 'role' => $u['role']);
        $_SESSION['last_activity'] = time();
        start_secure_session();
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        flash('success', 'Welcome, ' . $u['full_name'] . '!');
        redirect_to('?page=dashboard');
    }
    record_login_failure($pdo, $un);
    flash('danger', 'Invalid email or password, or this account is inactive.');
    redirect_to('?page=login');
}
