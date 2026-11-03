<?php defined('APP') or exit;

function esc($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function redirect_to($url) { header('Location: ' . $url); exit; }
function p($key) { return isset($_POST[$key]) ? trim((string)$_POST[$key]) : ''; }
function json_attr($data) { return esc(json_encode($data, JSON_UNESCAPED_UNICODE)); }
function visit_date_format($d) { return date('M d, Y h:i A', strtotime($d)); }
/* Two-letter avatar text, e.g. initials('Ana', 'Cruz') -> "AC" */
function initials($first, $last = '') { return mb_strtoupper(mb_substr(trim($first), 0, 1) . mb_substr(trim($last), 0, 1)); }

/* ---------- flash messages ---------- */
function flash($type, $message) { $_SESSION['flash'] = array('type' => $type, 'message' => $message); }
function show_flash() {
    if (empty($_SESSION['flash'])) return;
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    echo '<div class="alert alert-' . esc($f['type']) . ' alert-dismissible fade show" role="alert">' . esc($f['message']) . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
}

/* ---------- CSRF ---------- */
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function csrf_field() { echo '<input type="hidden" name="csrf_token" value="' . esc(csrf_token()) . '">'; }
function verify_csrf() {
    if (empty($_POST['csrf_token']) || empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], (string)$_POST['csrf_token'])) {
        http_response_code(403);
        exit('Invalid request token. Refresh the page and try again.');
    }
}

/* ---------- modal helpers: forms live in modals, errors reopen the modal ---------- */
function return_page($default) {
    $r = p('return');
    $ok = array('dashboard', 'patients', 'inventory', 'visits', 'reports', 'users', 'appearance');
    return '?page=' . (in_array($r, $ok, true) ? $r : $default);
}
function fail_modal($message, $redirect, $modalId) {
    $data = $_POST;
    unset($data['csrf_token'], $data['action']);
    foreach (array_keys($data) as $k) { if (stripos($k, 'password') !== false) unset($data[$k]); }
    $_SESSION['reopen'] = array('modal' => $modalId, 'data' => $data);
    flash('danger', $message);
    redirect_to($redirect);
}
function take_reopen() {
    if (empty($_SESSION['reopen'])) return null;
    $r = $_SESSION['reopen'];
    unset($_SESSION['reopen']);
    return $r;
}

/* ---------- reusable HTML attributes for strict inputs ---------- */
function name_pattern() { return "[A-Za-zÀ-ÿ.' \\-]+"; }
function school_email_attrs() {
    return 'type="email" maxlength="80" pattern="[A-Za-z0-9._%+\-]+@' . str_replace('.', '\.', SCHOOL_EMAIL_DOMAIN) . '" title="School email ending in @' . SCHOOL_EMAIL_DOMAIN . ', e.g. clinic@' . SCHOOL_EMAIL_DOMAIN . '." placeholder="name@' . SCHOOL_EMAIL_DOMAIN . '" autocapitalize="off" spellcheck="false"';
}
function phone_attrs() {
    return 'inputmode="numeric" minlength="11" maxlength="11" pattern="09[0-9]{9}" title="Exactly 11 digits starting with 09. Example: 09123456789." placeholder="09123456789"';
}
