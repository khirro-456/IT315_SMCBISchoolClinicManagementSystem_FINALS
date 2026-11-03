<?php defined('APP') or exit;

/* "My Clinic Record" sign-in for students and personnel (runs before require_login) */
if ($action === 'portal_login') {
    verify_csrf();
    $sid = strtoupper(p('school_id')); $code = p('access_code');
    if (!valid_school_or_employee_id($sid) || !preg_match('/^[0-9]{6}$/', $code)) {
        flash('danger', 'Enter your School / Employee ID and the 6-digit access code from the clinic.');
        redirect_to('?page=student');
    }
    if (portal_locked($pdo, $sid)) {
        flash('danger', 'Too many wrong attempts. Please wait ' . PORTAL_LOCK_MINUTES . ' minutes and try again.');
        redirect_to('?page=student');
    }
    $st = $pdo->prepare('SELECT id, portal_code FROM patients WHERE school_id=? LIMIT 1');
    $st->execute(array($sid));
    $pt = $st->fetch();
    if ($pt && $pt['portal_code'] !== null && hash_equals((string)$pt['portal_code'], $code)) {
        clear_portal_failures($pdo, $sid);
        session_regenerate_id(true);
        $_SESSION['portal'] = array('id' => (int)$pt['id'], 'code' => $code, 'last' => time());
        redirect_to('?page=student');
    }
    /* same message whether the ID exists or not, so IDs cannot be guessed from it */
    record_portal_failure($pdo, $sid);
    flash('danger', 'School ID or access code is incorrect.');
    redirect_to('?page=student');
}

if ($action === 'portal_logout') {
    verify_csrf();
    portal_end();
    session_regenerate_id(true);
    flash('success', 'You have signed out of your clinic record.');
    redirect_to('?page=student');
}
