<?php defined('APP') or exit;

/* A "critical" item has 5 or fewer units left (but never above its own minimum). */
function critical_limit($minimum) { return min(5, max(0, (int)$minimum)); }

function stock_status($quantity, $minimum = 10) {
    $q = (int)$quantity; $m = max(1, (int)$minimum);
    if ($q <= 0) return array('label' => 'Out of Stock', 'color' => 'dark');
    if ($q <= critical_limit($m)) return array('label' => 'Critical: restock now', 'color' => 'danger');
    if ($q <= $m) return array('label' => 'Low: reorder at ' . $m, 'color' => 'warning');
    return array('label' => 'Available', 'color' => 'success');
}

function expiry_status($date) {
    if (empty($date)) return array('label' => 'No expiry date', 'color' => 'secondary', 'blocked' => false);
    $days = (int)(new DateTime(date('Y-m-d')))->diff(new DateTime($date))->format('%r%a');
    if ($days < 0) return array('label' => 'Expired', 'color' => 'danger', 'blocked' => true);
    if ($days <= 30) return array('label' => 'Near expiry: ' . $days . ' day(s)', 'color' => 'warning', 'blocked' => false);
    return array('label' => 'Valid', 'color' => 'success', 'blocked' => false);
}

/* ---------- notifications ---------- */
/* Creates alerts for out-of-stock, critical, low, near-expiry and expired items,
   and removes alerts that no longer apply (item restocked or date changed).
   Runs after every inventory change, plus once a day (see refresh_daily_notifications). */
function generate_inventory_notifications($pdo) {
    $items = $pdo->query('SELECT id,item_name,current_quantity,minimum_stock,unit,expiration_date FROM inventory_items')->fetchAll();
    $want = array();   /* key => array(type, priority, title, message, item id) */
    foreach ($items as $it) {
        $id = (int)$it['id']; $name = $it['item_name']; $q = (int)$it['current_quantity'];
        $min = max(1, (int)$it['minimum_stock']); $unit = $it['unit'];

        if ($q <= 0) {
            $want['out_of_stock_' . $id] = array('out_of_stock', 'critical', 'Out of Stock: ' . $name, $name . ' is out of stock. Restock immediately.', $id);
        } elseif ($q <= critical_limit($min)) {
            $want['critical_stock_' . $id] = array('critical_stock', 'critical', 'Critical Stock: ' . $name, $name . ' has only ' . $q . ' ' . $unit . ' remaining.', $id);
        } elseif ($q <= $min) {
            $want['low_stock_' . $id] = array('low_stock', 'warning', 'Low Stock: ' . $name, $name . ' has ' . $q . ' ' . $unit . ' remaining. Reorder level: ' . $min . '.', $id);
        }

        if (!empty($it['expiration_date'])) {
            $days = (int)(new DateTime(date('Y-m-d')))->diff(new DateTime($it['expiration_date']))->format('%r%a');
            if ($days < 0) {
                $want['expired_' . $id . '_' . $it['expiration_date']] = array('expired', 'critical', 'Expired Item: ' . $name, $name . ' expired on ' . $it['expiration_date'] . '. Remove it from clinic use.', $id);
            } elseif ($days <= 30) {
                $want['near_expiry_' . $id . '_' . $it['expiration_date']] = array('near_expiry', 'warning', 'Near Expiry: ' . $name, $name . ' will expire in ' . $days . ' day(s), on ' . $it['expiration_date'] . '.', $id);
            }
        }
    }

    /* existing alerts keep their read/unread state; only new ones are inserted */
    $have = $pdo->query('SELECT notification_key FROM notifications')->fetchAll(PDO::FETCH_COLUMN);
    $ins = $pdo->prepare('INSERT INTO notifications (notification_key,notification_type,priority,title,message,inventory_item_id) VALUES (?,?,?,?,?,?)');
    foreach (array_diff(array_keys($want), $have) as $k) {
        $n = $want[$k];
        $ins->execute(array($k, $n[0], $n[1], $n[2], $n[3], $n[4]));
    }
    foreach (array_chunk(array_values(array_diff($have, array_keys($want))), 200) as $stale) {
        $pdo->prepare('DELETE FROM notifications WHERE notification_key IN (' . implode(',', array_fill(0, count($stale), '?')) . ')')->execute($stale);
    }
    meta_set($pdo, 'notifications_date', date('Y-m-d'));
}

/* Expiry alerts depend on today's date, so rebuild them on the first request of each day. */
function refresh_daily_notifications($pdo) {
    if (meta_get($pdo, 'notifications_date') !== date('Y-m-d')) generate_inventory_notifications($pdo);
}

/* ---------- demo inventory: 30 medicines + 10 supplies ---------- */
function clinic_item_list() {
    return array(
        array('MED-001','Paracetamol 500mg Tablet','Medicine','tablet',100,20),
        array('MED-002','Paracetamol Syrup 250mg/5mL','Medicine','bottle',20,5),
        array('MED-003','Ibuprofen 200mg Tablet','Medicine','tablet',50,10),
        array('MED-004','Mefenamic Acid 500mg Capsule','Medicine','capsule',50,10),
        array('MED-005','Cetirizine 10mg Tablet','Medicine','tablet',50,10),
        array('MED-006','Loratadine 10mg Tablet','Medicine','tablet',50,10),
        array('MED-007','Oral Rehydration Salts (ORS)','Medicine','sachet',100,20),
        array('MED-008','Antacid Tablet','Medicine','tablet',50,10),
        array('MED-009','Dimenhydrinate 50mg Tablet','Medicine','tablet',30,10),
        array('MED-010','Loperamide 2mg Capsule','Medicine','capsule',30,10),
        array('MED-011','Ambroxol Syrup','Medicine','bottle',20,5),
        array('MED-012','Lagundi Tablet','Medicine','tablet',50,10),
        array('MED-013','Calamine Lotion','Medicine','bottle',10,3),
        array('MED-014','Hydrocortisone 1% Cream','Medicine','tube',10,3),
        array('MED-015','Clotrimazole 1% Cream','Medicine','tube',10,3),
        array('MED-016','Povidone-Iodine Solution','Medicine','bottle',10,3),
        array('MED-017','Hydrogen Peroxide 3% Solution','Medicine','bottle',10,3),
        array('MED-018','Normal Saline Solution','Medicine','bottle',20,5),
        array('MED-019','70% Isopropyl Alcohol','Medicine','bottle',15,5),
        array('MED-020','Burn Ointment','Medicine','tube',10,3),
        array('MED-021','Salbutamol Inhaler','Medicine','inhaler',5,2),
        array('MED-022','Glucose Powder','Medicine','sachet',20,5),
        array('MED-023','Eye Wash Solution','Medicine','bottle',10,3),
        array('MED-024','Activated Charcoal Tablet','Medicine','tablet',20,5),
        array('MED-025','Vitamin C 500mg Tablet','Medicine','tablet',100,20),
        array('MED-026','Zinc Sulfate Tablet','Medicine','tablet',30,10),
        array('MED-027','Bismuth Subsalicylate Tablet','Medicine','tablet',30,10),
        array('MED-028','Mentholated Topical Balm','Medicine','jar',10,3),
        array('MED-029','Antiseptic Wound Wash','Medicine','bottle',10,3),
        array('MED-030','Cold Compress Gel Pack','Medicine','pack',15,5),
        array('SUP-001','Adhesive Bandage Strip','First Aid Supply','piece',100,20),
        array('SUP-002','Sterile Gauze Pad','First Aid Supply','piece',100,20),
        array('SUP-003','Rolled Gauze Bandage','First Aid Supply','roll',30,10),
        array('SUP-004','Medical Adhesive Tape','First Aid Supply','roll',20,5),
        array('SUP-005','Cotton Balls','First Aid Supply','pack',20,5),
        array('SUP-006','Alcohol Swab','First Aid Supply','piece',100,20),
        array('SUP-007','Disposable Gloves','First Aid Supply','box',10,3),
        array('SUP-008','Surgical Mask','First Aid Supply','box',10,3),
        array('SUP-009','Digital Thermometer','Medical Equipment','piece',5,2),
        array('SUP-010','Tongue Depressor','Clinic Supply','piece',100,20)
    );
}

function load_sample_inventory($pdo, $userId) {
    $check = $pdo->prepare('SELECT id FROM inventory_items WHERE item_code=? OR item_name=? LIMIT 1');
    $ins = $pdo->prepare('INSERT INTO inventory_items (item_code,item_name,category,unit,current_quantity,minimum_stock,expiration_date,supplier,remarks) VALUES (?,?,?,?,?,?,?,?,?)');
    $tr = $pdo->prepare('INSERT INTO inventory_transactions (inventory_item_id,transaction_type,transaction_reason,quantity,description,processed_by) VALUES (?,?,?,?,?,?)');
    $added = 0;
    foreach (clinic_item_list() as $i) {
        $check->execute(array($i[0], $i[1]));
        if ($check->fetch()) continue;
        $ins->execute(array($i[0], $i[1], $i[2], $i[3], $i[4], $i[5], date('Y-m-d', strtotime('+2 years')), 'School Clinic Supplier', 'Demo inventory - confirm with the school nurse.'));
        $tr->execute(array((int)$pdo->lastInsertId(), 'Stock In', 'Initial Stock', $i[4], 'Initial clinic stock', $userId));
        $added++;
    }
    return $added;
}
