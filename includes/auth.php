<?php defined('APP') or exit;

define('SESSION_IDLE_MINUTES', 30);
define('LOGIN_LOCK_MINUTES', 15);
define('LOGIN_MAX_FAILS_PER_USER', 5);
define('LOGIN_MAX_FAILS_PER_IP', 20);   /* higher: a whole school can share one IP */

function is_logged_in() { return !empty($_SESSION['user']['id']); }
function current_role() { return isset($_SESSION['user']['role']) ? $_SESSION['user']['role'] : ''; }
function is_admin() { return current_role() === 'admin'; }

function logout_user($message = 'You have been logged out.') {
    $_SESSION = array();
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
    session_start();
    flash('success', $message);
    redirect_to('?page=login');
}

function require_login($pdo) {
    if (!is_logged_in()) redirect_to('?page=login');
    if (isset($_SESSION['last_activity']) && time() - $_SESSION['last_activity'] > SESSION_IDLE_MINUTES * 60) {
        audit('session timeout', 'success', 'Idle for ' . SESSION_IDLE_MINUTES . ' minutes');
        logout_user('You were signed out after ' . SESSION_IDLE_MINUTES . ' minutes of inactivity.');
    }
    $_SESSION['last_activity'] = time();
    check_secure_session();
    $s = $pdo->prepare('SELECT id, full_name, role, status, must_change_password FROM users WHERE id=? LIMIT 1');
    $s->execute(array((int)$_SESSION['user']['id']));
    $u = $s->fetch();
    if (!$u || $u['status'] !== 'active') logout_user('Your session has ended. Please sign in again.');
    $_SESSION['user'] = array('id' => (int)$u['id'], 'name' => $u['full_name'], 'role' => $u['role']);
    $_SESSION['must_change_password'] = (int)$u['must_change_password'] === 1;
}

/* ---------- login throttling ---------- */
function client_ip() { return isset($_SERVER['REMOTE_ADDR']) ? substr((string)$_SERVER['REMOTE_ADDR'], 0, 45) : ''; }
function login_locked($pdo, $username) {
    $s = $pdo->prepare('SELECT SUM(username=?), SUM(ip=?) FROM login_attempts WHERE attempted_at > DATE_SUB(NOW(), INTERVAL ' . LOGIN_LOCK_MINUTES . ' MINUTE)');
    $s->execute(array($username, client_ip()));
    $r = $s->fetch(PDO::FETCH_NUM);
    return (int)$r[0] >= LOGIN_MAX_FAILS_PER_USER || (int)$r[1] >= LOGIN_MAX_FAILS_PER_IP;
}
function record_login_failure($pdo, $username) {
    $pdo->prepare('INSERT INTO login_attempts (username, ip) VALUES (?,?)')->execute(array(substr($username, 0, 80), client_ip()));
    $pdo->exec('DELETE FROM login_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 1 DAY)');
}
function clear_login_failures($pdo, $username) {
    $pdo->prepare('DELETE FROM login_attempts WHERE username=?')->execute(array($username));
}

function require_admin($pdo) {
    require_login($pdo);
    if (!is_admin()) {
        flash('danger', 'Only administrators can open that page.');
        redirect_to('?page=dashboard');
    }
}
