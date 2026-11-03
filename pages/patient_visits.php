<?php defined('APP') or exit;
/* JSON: one patient's clinic visit history, loaded by the patient profile modal when it opens. */
$pid = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$st = $pdo->prepare('SELECT v.*, u.full_name AS duty_staff, u.role AS staff_role FROM clinic_visits v JOIN users u ON u.id=v.attended_by WHERE v.patient_id=? ORDER BY v.visit_date DESC');
$st->execute(array($pid));
$visits = $st->fetchAll();
$meds = fetch_meds_by_visit($pdo, array_column($visits, 'id'));
foreach ($visits as $i => $v) {
    $visits[$i]['meds'] = isset($meds[$v['id']]) ? $meds[$v['id']] : array();
}

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
header('X-Content-Type-Options: nosniff');
echo json_encode($visits, JSON_UNESCAPED_UNICODE);
exit;
