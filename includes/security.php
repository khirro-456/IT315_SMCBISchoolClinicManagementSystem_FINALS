<?php defined('APP') or exit;

define('SESSION_MAX_HOURS', 10);          /* sign in again after a full shift, even when active */
define('AUDIT_KEEP_DAYS', 180);

function is_https() {
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
}

/* ---------- browser security headers ---------- */
function send_security_headers() {
    header_remove('X-Powered-By');
    header('X-Frame-Options: DENY');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
    header("Content-Security-Policy: default-src 'self'; "
        . "script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net; "
        . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; "
        . "font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net; "
        . "img-src 'self' data: https:; connect-src 'self' https://cdn.jsdelivr.net; "
        . "object-src 'none'; base-uri 'self'; form-action 'self'; frame-ancestors 'none'");
    if (is_https()) header('Strict-Transport-Security: max-age=31536000');
    /* patient records must not stay in the browser cache (Back button after sign-out) */
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    header('Pragma: no-cache');
}

/* ---------- session hardening (called from require_login) ---------- */
function browser_fingerprint() {
    return hash('sha256', isset($_SERVER['HTTP_USER_AGENT']) ? (string)$_SERVER['HTTP_USER_AGENT'] : '');
}
function start_secure_session() {
    $_SESSION['fingerprint'] = browser_fingerprint();
    $_SESSION['signed_in_at'] = time();
}
function check_secure_session() {
    /* a stolen session cookie used from a different browser is rejected */
    if (isset($_SESSION['fingerprint']) && !hash_equals($_SESSION['fingerprint'], browser_fingerprint())) {
        audit('session rejected', 'blocked', 'Browser changed during session');
        logout_user('Your session has ended for security reasons. Please sign in again.');
    }
    if (isset($_SESSION['signed_in_at']) && time() - $_SESSION['signed_in_at'] > SESSION_MAX_HOURS * 3600) {
        logout_user('For security, please sign in again (sessions last up to ' . SESSION_MAX_HOURS . ' hours).');
    }
}

/* ---------- password rules: returns an error message, or '' when the password is acceptable ---------- */
function password_problem($pw, $username = '') {
    if (strlen($pw) < 8) return 'Password must have at least 8 characters.';
    if (strlen($pw) > 72) return 'Password must be 72 characters or fewer.';
    if (!preg_match('/[A-Za-z]/', $pw) || !preg_match('/[0-9]/', $pw)) return 'Password must contain both letters and numbers.';
    $name = strtolower(strstr($username, '@', true) ?: $username);   /* clinic@smcbi.edu.ph -> clinic */
    if (strlen($name) >= 3 && stripos($pw, $name) !== false) return 'Password must not contain your email name (' . $name . ').';
    if ($pw === DEFAULT_ADMIN_PASSWORD) return 'Choose a password different from the default password.';
    return '';
}
function password_pattern_attrs() {
    return 'minlength="8" maxlength="72" pattern="(?=.*[A-Za-z])(?=.*[0-9]).{8,72}" title="At least 8 characters with letters and numbers."';
}

/* ---------- activity log ---------- */
function audit($event, $outcome = 'success', $details = '') {
    global $pdo;
    if (!$pdo) return;
    try {
        $pdo->prepare('INSERT INTO audit_log (user_id, user_name, event, outcome, details, ip) VALUES (?,?,?,?,?,?)')->execute(array(
            isset($_SESSION['user']['id']) ? (int)$_SESSION['user']['id'] : null,
            isset($_SESSION['user']['name']) ? mb_substr($_SESSION['user']['name'], 0, 150) : null,
            mb_substr($event, 0, 60), mb_substr($outcome, 0, 20), mb_substr((string)$details, 0, 255), client_ip()
        ));
        if (mt_rand(1, 100) === 1) $pdo->exec('DELETE FROM audit_log WHERE created_at < DATE_SUB(NOW(), INTERVAL ' . AUDIT_KEEP_DAYS . ' DAY)');
    } catch (PDOException $e) { /* logging must never break the page */ }
}

/* Logs every form action once the request finishes; the flash message tells whether it worked. */
function audit_post_action($action) {
    unset($_SESSION['flash']);
    register_shutdown_function(function () use ($action) {
        $f = isset($_SESSION['flash']) ? $_SESSION['flash'] : null;
        if (http_response_code() === 403) { $outcome = 'blocked'; $msg = 'Invalid request token'; }
        elseif (!in_array($action, array('login', 'portal_login', 'portal_logout'), true) && !is_logged_in()) { $outcome = 'blocked'; $msg = 'Not signed in'; }
        elseif ($f) { $outcome = $f['type'] === 'danger' ? 'failed' : ($f['type'] === 'warning' ? 'blocked' : 'success'); $msg = $f['message']; }
        else { $outcome = 'success'; $msg = ''; }
        $id = isset($_POST['id']) ? ' #' . (int)$_POST['id'] : '';
        if ($action === 'login') $msg = 'Email: ' . mb_substr(p('username'), 0, 80) . ($msg !== '' ? ' - ' . $msg : '');
        if ($action === 'portal_login') $msg = 'School ID: ' . mb_substr(strtoupper(p('school_id')), 0, 50) . ($msg !== '' ? ' - ' . $msg : '');
        $msg = preg_replace('/\b[0-9]{6}\b/', '******', $msg);   /* access codes never go into the log */
        audit(str_replace('_', ' ', $action) . $id, $outcome, strip_tags($msg));
    });
}
