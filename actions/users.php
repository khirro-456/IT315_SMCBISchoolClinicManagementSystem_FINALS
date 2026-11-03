<?php defined('APP') or exit;

if ($action === 'save_user') {
    require_admin($pdo);
    $back = '?page=users';
    $name = p('full_name'); $un = strtolower(p('username')); $pw = (string)$_POST['password'];
    $role = (p('role') === 'admin') ? 'admin' : 'nurse';

    if (!valid_person_name($name)) fail_modal('Full Name must contain letters only. Numbers and symbols are not allowed.', $back, 'userModal');
    if (!valid_school_email($un)) fail_modal('Use a school email address ending in @' . SCHOOL_EMAIL_DOMAIN . ' (e.g. ' . CLINIC_ADMIN_EMAIL . ').', $back, 'userModal');
    if (($err = password_problem($pw, $un)) !== '') fail_modal($err, $back, 'userModal');

    try {
        /* the admin chose this password, so the new user must replace it at first sign-in */
        $pdo->prepare('INSERT INTO users (full_name,username,password,role,status,must_change_password) VALUES (?,?,?,?,?,1)')
            ->execute(array($name, $un, password_hash($pw, PASSWORD_DEFAULT), $role, 'active'));
        flash('success', 'User account created. The user will be asked to set a new password at first sign-in.');
    } catch (PDOException $e) {
        fail_modal('Could not create user. That email is already used by another account.', $back, 'userModal');
    }
    redirect_to($back);
}

/* give an account a new school email to sign in with (e.g. old usernames such as "nurse1") */
if ($action === 'set_user_email') {
    require_admin($pdo);
    $back = '?page=users';
    $id = (int)p('id'); $email = strtolower(p('username'));
    if (!valid_school_email($email)) fail_modal('Use a school email address ending in @' . SCHOOL_EMAIL_DOMAIN . '.', $back, 'userEmailModal');
    try {
        $pdo->prepare('UPDATE users SET username=? WHERE id=?')->execute(array($email, $id));
        flash('success', 'Login email updated. This account now signs in with ' . $email . '.');
    } catch (PDOException $e) {
        fail_modal('That email is already used by another account.', $back, 'userEmailModal');
    }
    redirect_to($back);
}

if ($action === 'change_password') {
    $back = return_page('dashboard');
    $cur = (string)$_POST['current_password']; $new = (string)$_POST['new_password'];
    $st = $pdo->prepare('SELECT username, password FROM users WHERE id=?');
    $st->execute(array((int)$_SESSION['user']['id']));
    $acc = $st->fetch();
    if (!$acc || !password_verify($cur, $acc['password'])) fail_modal('Current password is incorrect.', $back, 'passwordModal');
    if (($err = password_problem($new, $acc['username'])) !== '') fail_modal($err, $back, 'passwordModal');
    if ($new === $cur) fail_modal('Choose a new password that is different from the current password.', $back, 'passwordModal');
    $pdo->prepare('UPDATE users SET password=?, must_change_password=0 WHERE id=?')->execute(array(password_hash($new, PASSWORD_DEFAULT), (int)$_SESSION['user']['id']));
    session_regenerate_id(true);
    $_SESSION['must_change_password'] = false;
    flash('success', 'Your password was changed successfully.');
    redirect_to($back);
}

if ($action === 'toggle_user_status') {
    require_admin($pdo);
    $id = (int)p('id');
    $new = (p('status') === 'active') ? 'inactive' : 'active';
    if ($id === (int)$_SESSION['user']['id']) {
        flash('danger', 'You cannot deactivate your own account.');
    } else {
        $pdo->prepare('UPDATE users SET status=? WHERE id=?')->execute(array($new, $id));
        flash('success', 'User account status updated.');
    }
    redirect_to('?page=users');
}
