<?php defined('APP') or exit;

if ($action === 'save_patient') {
    $id = (int)p('id');
    $back = return_page('patients');
    $sid = p('school_id'); $fn = p('first_name'); $mn = p('middle_name'); $ln = p('last_name');
    $sex = p('sex'); $bd = p('birth_date'); $gd = p('grade_department');
    $cn = p('contact_number'); $gn = p('guardian_name'); $gc = p('guardian_contact');
    $al = p('allergies'); $mc = p('medical_conditions');
    $en = p('emergency_contact_name'); $ec = p('emergency_contact_number'); $rm = p('remarks');
    $type = (p('patient_type') === 'personnel') ? 'personnel' : 'student';

    $rules = array(
        /* required fields: delete any line you want to keep optional */
        array($sex !== '', 'Sex is required.'),
        array($bd !== '', 'Birth Date is required.'),
        array($gd !== '', 'Grade / Section / Department is required.'),
        array($cn !== '', 'Contact Number is required.'),
        array($type === 'personnel' || $gn !== '', 'Parent/Guardian Name is required for students.'),
        array($type === 'personnel' || $gc !== '', 'Guardian Contact is required for students.'),
        array($en !== '', 'Emergency Contact Name is required.'),
        array($ec !== '', 'Emergency Contact Number is required.'),

        array(valid_school_or_employee_id($sid), 'School/Employee ID must contain BOTH letters and numbers (hyphen allowed). Letters-only or numbers-only IDs are not accepted. Example: STU-2026-001.'),
        array(valid_person_name($fn), 'First Name must contain letters only.'),
        array(valid_optional_person_name($mn), 'Middle Name must contain letters only.'),
        array(valid_person_name($ln), 'Last Name must contain letters only.'),
        array($sex === '' || in_array($sex, array('Male', 'Female'), true), 'Sex must be Male or Female.'),
        array($bd === '' || (preg_match('/^\d{4}-\d{2}-\d{2}$/', $bd) && $bd <= date('Y-m-d')), 'Birth Date is invalid or in the future.'),
        array(max_len($gd, 120), 'Grade / Section / Department must be 120 characters or fewer.'),
        array(valid_contact_number($cn), 'Contact Number must be exactly 11 digits starting with 09. Example: 09123456789.'),
        array(valid_optional_person_name($gn), 'Parent/Guardian Name must contain letters only.'),
        array(valid_contact_number($gc), 'Guardian Contact must be exactly 11 digits starting with 09.'),
        array(valid_optional_person_name($en), 'Emergency Contact Name must contain letters only.'),
        array(valid_contact_number($ec), 'Emergency Contact Number must be exactly 11 digits starting with 09.'),
        array(valid_medical_note($al), 'Allergies contains invalid characters.'),
        array(valid_medical_note($mc), 'Medical Conditions contains invalid characters.'),
        array(valid_medical_note($rm), 'Remarks contains invalid characters.'),
        /* reject random letters like "bsrngobwljrowbrwjl" */
        array(!looks_like_gibberish($sid, true), 'School/Employee ID looks like random letters. Please enter the real ID.'),
        array(!looks_like_gibberish($fn), 'First Name does not look like a real name. Please check the spelling.'),
        array(!looks_like_gibberish($mn), 'Middle Name does not look like a real name. Please check the spelling.'),
        array(!looks_like_gibberish($ln), 'Last Name does not look like a real name. Please check the spelling.'),
        array(!looks_like_gibberish($gd, true), 'Grade / Section / Department looks like random letters. Please check the spelling.'),
        array(!looks_like_gibberish($gn), 'Parent/Guardian Name does not look like a real name. Please check the spelling.'),
        array(!looks_like_gibberish($en), 'Emergency Contact Name does not look like a real name. Please check the spelling.'),
        array(!looks_like_gibberish($al, true), 'Allergies looks like random letters. Please check the spelling.'),
        array(!looks_like_gibberish($mc, true), 'Medical Conditions looks like random letters. Please check the spelling.'),
        array(!looks_like_gibberish($rm, true), 'Remarks looks like random letters. Please check the spelling.')
    );
    foreach ($rules as $r) { if (!$r[0]) fail_modal($r[1], $back, 'patientModal'); }

    $data = array($sid, $type, $fn, $mn, $ln, $sex !== '' ? $sex : null, $bd !== '' ? $bd : null, $gd, $cn, $gn, $gc, $al, $mc, $en, $ec, $rm);
    try {
        if ($id > 0) {
            $data[] = $id;
            $pdo->prepare('UPDATE patients SET school_id=?,patient_type=?,first_name=?,middle_name=?,last_name=?,sex=?,birth_date=?,grade_department=?,contact_number=?,guardian_name=?,guardian_contact=?,allergies=?,medical_conditions=?,emergency_contact_name=?,emergency_contact_number=?,remarks=? WHERE id=?')->execute($data);
            flash('success', 'Patient record updated.');
        } else {
            $code = new_portal_code();
            $data[] = $code;
            $pdo->prepare('INSERT INTO patients (school_id,patient_type,first_name,middle_name,last_name,sex,birth_date,grade_department,contact_number,guardian_name,guardian_contact,allergies,medical_conditions,emergency_contact_name,emergency_contact_number,remarks,portal_code) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)')->execute($data);
            flash('success', 'Patient profile added. My Clinic Record access code: ' . $code . ' (give it to the patient together with their ID).');
        }
    } catch (PDOException $e) {
        fail_modal('Could not save patient. The School/Employee ID may already exist.', $back, 'patientModal');
    }
    redirect_to($back);
}

/* new My Clinic Record access code (the patient forgot it, or someone else may have seen it) */
if ($action === 'reset_portal_code') {
    $back = return_page('patients');
    $id = (int)p('id');
    $st = $pdo->prepare('SELECT first_name, last_name FROM patients WHERE id=?');
    $st->execute(array($id));
    $pt = $st->fetch();
    if (!$pt) {
        flash('danger', 'Patient not found.');
        redirect_to($back);
    }
    $code = new_portal_code();
    $pdo->prepare('UPDATE patients SET portal_code=? WHERE id=?')->execute(array($code, $id));
    flash('success', 'New My Clinic Record access code for ' . $pt['last_name'] . ', ' . $pt['first_name'] . ': ' . $code . '. The old code no longer works.');
    redirect_to($back);
}