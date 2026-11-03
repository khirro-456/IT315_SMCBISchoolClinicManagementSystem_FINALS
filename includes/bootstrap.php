<?php defined('APP') or exit;

require dirname(__DIR__) . '/includes/security.php';

/* ---------- errors: show on this computer only, never to visitors online ---------- */
$isLocal = in_array(isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '', array('127.0.0.1', '::1'), true);
ini_set('display_errors', $isLocal ? '1' : '0');
ini_set('log_errors', '1');
send_security_headers();

/* ---------- session ---------- */
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.use_trans_sid', '0');
session_name('SMCBI_SESSION');
session_set_cookie_params(array(
    'lifetime' => 0,
    'path' => '/',
    'secure' => is_https(),
    'httponly' => true,
    'samesite' => 'Lax'
));
session_start();

/* ---------- core files ---------- */
$root = dirname(__DIR__);
require $root . '/includes/helpers.php';
require $root . '/includes/auth.php';
require $root . '/includes/validation.php';
require $root . '/includes/inventory_helpers.php';
require $root . '/includes/schema.php';
require $root . '/includes/data.php';
require $root . '/includes/themes.php';
require $root . '/includes/portal.php';

/* ---------- database ---------- */
$config = require $root . '/config/config.php';
date_default_timezone_set(!empty($config['timezone']) ? $config['timezone'] : 'Asia/Manila');
try {
    $pdo = new PDO(
        'mysql:host=' . $config['db_host'] . ';dbname=' . $config['db_name'] . ';charset=utf8mb4',
        $config['db_user'],
        $config['db_pass'],
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        )
    );
} catch (PDOException $e) {
    exit('<h2>Database connection failed</h2><p>Open <code>config/config.php</code> and check the database settings. Make sure MySQL is running and the database exists.</p>');
}
/* keep MySQL NOW()/CURDATE() in step with PHP (offset form works without MySQL tz tables) */
$pdo->exec("SET time_zone = '" . date('P') . "'");
ensure_schema($pdo);
refresh_daily_notifications($pdo);

/* ---------- form actions (POST) ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = (string)$_POST['action'];
    audit_post_action($action);
    require $root . '/actions/auth.php';          /* login (exits when handled) */
    require $root . '/actions/portal.php';        /* My Clinic Record sign-in / sign-out (exits when handled) */
    require_login($pdo);
    verify_csrf();
    if (!empty($_SESSION['must_change_password']) && $action !== 'change_password') {
        flash('warning', 'Please change your password before doing anything else.');
        redirect_to(return_page('dashboard'));
    }
    require $root . '/actions/notifications.php';
    require $root . '/actions/users.php';
    require $root . '/actions/patients.php';
    require $root . '/actions/inventory.php';
    require $root . '/actions/visits.php';
    require $root . '/actions/settings.php';
}

/* ---------- routing (GET) ---------- */
$routes = array('login', 'logout', 'dashboard', 'patients', 'patient_visits', 'inventory', 'visits', 'reports', 'users', 'appearance', 'student');
$page = isset($_GET['page']) ? $_GET['page'] : (is_logged_in() ? 'dashboard' : 'login');
if (!in_array($page, $routes, true)) $page = is_logged_in() ? 'dashboard' : 'login';
if ($page === 'logout') { if (is_logged_in()) audit('logout'); logout_user(); }
if ($page === 'login') {
    if (is_logged_in()) redirect_to('?page=dashboard');
} elseif ($page !== 'student') {   /* My Clinic Record has its own sign-in (includes/portal.php) */
    require_login($pdo);
}
if ($page === 'users' || $page === 'appearance') require_admin($pdo);

require $root . '/pages/' . $page . '.php';
