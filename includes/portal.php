<?php defined('APP') or exit;

/* "My Clinic Record" (?page=student): a student or employee views their own record using their
   School / Employee ID and the 6-digit access code the clinic gives them (Patients > View > Reset code). */
define('PORTAL_IDLE_MINUTES', 10);       /* shared school computers: the record closes quickly */
define('PORTAL_LOCK_MINUTES', 15);
define('PORTAL_MAX_FAILS_PER_ID', 5);
define('PORTAL_MAX_FAILS_PER_IP', 30);   /* the whole school can share one IP */

function new_portal_code() { return str_pad((string)random_int(0, 999999), 6, '0', STR_PAD_LEFT); }

/* ---------- wrong-code throttling (kept apart from staff sign-in so students never lock out the clinic) ---------- */
function portal_locked($pdo, $schoolId) {
    $s = $pdo->prepare('SELECT SUM(school_id=?), SUM(ip=?) FROM portal_attempts WHERE attempted_at > DATE_SUB(NOW(), INTERVAL ' . PORTAL_LOCK_MINUTES . ' MINUTE)');
    $s->execute(array($schoolId, client_ip()));
    $r = $s->fetch(PDO::FETCH_NUM);
    return (int)$r[0] >= PORTAL_MAX_FAILS_PER_ID || (int)$r[1] >= PORTAL_MAX_FAILS_PER_IP;
}
function record_portal_failure($pdo, $schoolId) {
    $pdo->prepare('INSERT INTO portal_attempts (school_id, ip) VALUES (?,?)')->execute(array(substr($schoolId, 0, 50), client_ip()));
    $pdo->exec('DELETE FROM portal_attempts WHERE attempted_at < DATE_SUB(NOW(), INTERVAL 1 DAY)');
}
function clear_portal_failures($pdo, $schoolId) {
    $pdo->prepare('DELETE FROM portal_attempts WHERE school_id=?')->execute(array($schoolId));
}

/* The patient whose record is open, or null. Closes after inactivity or when the clinic resets the code. */
function portal_patient($pdo) {
    if (empty($_SESSION['portal']['id'])) return null;
    $p = $_SESSION['portal'];
    if (time() - $p['last'] > PORTAL_IDLE_MINUTES * 60) {
        portal_end('Your record was closed after ' . PORTAL_IDLE_MINUTES . ' minutes of inactivity.');
        return null;
    }
    $s = $pdo->prepare('SELECT * FROM patients WHERE id=? LIMIT 1');
    $s->execute(array((int)$p['id']));
    $row = $s->fetch();
    if (!$row || $row['portal_code'] === null || !hash_equals((string)$row['portal_code'], (string)$p['code'])) {
        portal_end('Your access code was changed by the clinic. Please sign in with the new code.');
        return null;
    }
    $_SESSION['portal']['last'] = time();
    return $row;
}
function portal_end($message = '') {
    unset($_SESSION['portal']);
    if ($message !== '') flash('warning', $message);
}
