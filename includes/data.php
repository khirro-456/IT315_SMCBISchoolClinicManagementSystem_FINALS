<?php defined('APP') or exit;

/* Small query helpers shared by pages and modals. */
function fetch_patients_for_select($pdo) {
    return $pdo->query('SELECT id,school_id,first_name,last_name,grade_department FROM patients ORDER BY last_name,first_name')->fetchAll();
}
function fetch_issuable_items($pdo) {
    return $pdo->query('SELECT * FROM inventory_items WHERE current_quantity>0 AND (expiration_date IS NULL OR expiration_date>=CURDATE()) ORDER BY item_name')->fetchAll();
}
/* Medicines/supplies issued in the given visits, grouped by visit id. */
function fetch_meds_by_visit($pdo, $visitIds) {
    $out = array();
    if (!$visitIds) return $out;
    $st = $pdo->prepare('SELECT vi.clinic_visit_id, vi.quantity_used, i.item_code, i.item_name, i.unit, i.category FROM visit_items vi JOIN inventory_items i ON i.id=vi.inventory_item_id WHERE vi.clinic_visit_id IN (' . implode(',', array_fill(0, count($visitIds), '?')) . ')');
    $st->execute(array_values(array_map('intval', $visitIds)));
    foreach ($st->fetchAll() as $m) { $out[$m['clinic_visit_id']][] = $m; }
    return $out;
}
function fetch_all_items_short($pdo) {
    return $pdo->query('SELECT id,item_name,current_quantity,unit FROM inventory_items ORDER BY item_name')->fetchAll();
}
