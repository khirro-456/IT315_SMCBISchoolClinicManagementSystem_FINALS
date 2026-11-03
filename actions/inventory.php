<?php defined('APP') or exit;

/* ---- load the 30 medicines + 10 supplies ---- */
if ($action === 'load_sample_inventory') {
    $n = load_sample_inventory($pdo, (int)$_SESSION['user']['id']);
    generate_inventory_notifications($pdo);
    flash($n ? 'success' : 'info', $n ? $n . ' clinic medicines and supplies were loaded.' : 'All essential items already exist. No duplicates were added.');
    redirect_to('?page=inventory');
}

/* ---- add / edit medicine or supply ---- */
if ($action === 'save_inventory') {
    $id = (int)p('id');
    $back = '?page=inventory';
    $code = strtoupper(p('item_code')); $name = p('item_name'); $unit = p('unit');
    $min = (int)p('minimum_stock'); $exp = p('expiration_date');
    $cat = p('category');
    if (!in_array($cat, array('Medicine', 'First Aid Supply', 'Medical Equipment', 'Clinic Supply'), true)) $cat = 'Medicine';

    $rules = array(
        array(valid_item_code($code), 'Item Code must use letters, numbers, and hyphens (example: MED-031).'),
        array(preg_match('/^[A-Za-z0-9\s().,%+\/-]{2,150}$/', $name) === 1, 'Item Name contains invalid characters.'),
        array(preg_match('/^[A-Za-z\s-]{2,50}$/', $unit) === 1, 'Unit must contain letters only (tablet, bottle, box...).'),
        array($min >= 1, 'Minimum stock must be at least 1.'),
        array($exp === '' || preg_match('/^\d{4}-\d{2}-\d{2}$/', $exp) === 1, 'Expiration date is invalid.'),
        array(max_len(p('supplier'), 150), 'Supplier must be 150 characters or fewer.'),
        array(max_len(p('remarks'), 1000), 'Remarks must be 1000 characters or fewer.')
    );
    foreach ($rules as $r) { if (!$r[0]) fail_modal($r[1], $back, 'inventoryModal'); }

    $data = array($code, $name, $cat, $unit, $min, $exp !== '' ? $exp : null, p('supplier'), p('remarks'));
    try {
        if ($id > 0) {
            $data[] = $id;
            $pdo->prepare('UPDATE inventory_items SET item_code=?,item_name=?,category=?,unit=?,minimum_stock=?,expiration_date=?,supplier=?,remarks=? WHERE id=?')->execute($data);
            flash('success', 'Item updated. Use Supply Transaction to change its quantity.');
        } else {
            $qty = max(0, (int)p('initial_quantity'));
            $ins = array_merge(array_slice($data, 0, 4), array($qty), array_slice($data, 4));
            $pdo->prepare('INSERT INTO inventory_items (item_code,item_name,category,unit,current_quantity,minimum_stock,expiration_date,supplier,remarks) VALUES (?,?,?,?,?,?,?,?,?)')->execute($ins);
            $iid = (int)$pdo->lastInsertId();
            if ($qty > 0) {
                $pdo->prepare('INSERT INTO inventory_transactions (inventory_item_id,transaction_type,transaction_reason,quantity,description,processed_by) VALUES (?,?,?,?,?,?)')
                    ->execute(array($iid, 'Stock In', 'Initial Stock', $qty, 'Initial stock added when item was created.', (int)$_SESSION['user']['id']));
            }
            flash('success', 'Medicine/supply item added.');
        }
        generate_inventory_notifications($pdo);
    } catch (PDOException $e) {
        fail_modal('Could not save item. Item Code may already exist.', $back, 'inventoryModal');
    }
    redirect_to($back);
}

/* ---- delivery, damage, expired/disposal, lost, correction ---- */
if ($action === 'supply_transaction') {
    $back = '?page=inventory';
    $itemId = (int)p('item_id'); $type = p('transaction_type'); $reason = p('transaction_reason');
    $qty = (int)p('quantity'); $desc = p('description');
    $reasons = array(
        'Stock In' => array('Delivery / Received Supply'),
        'Stock Out' => array('Damaged Item', 'Expired / Disposal', 'Lost / Missing Item'),
        'Adjustment' => array('Quantity Correction')
    );
    if (!isset($reasons[$type]) || !in_array($reason, $reasons[$type], true)) fail_modal('Please choose a valid transaction type and matching reason.', $back, 'supplyModal');
    if (strlen($desc) < 5 || !valid_clinical_text($desc)) fail_modal('Description must be at least 5 characters and use valid characters only.', $back, 'supplyModal');
    if (($type === 'Adjustment' && $qty < 0) || ($type !== 'Adjustment' && $qty < 1)) fail_modal('Enter a valid whole-number quantity.', $back, 'supplyModal');

    try {
        $pdo->beginTransaction();
        /* lock the row so a clinic visit saved at the same moment cannot be overwritten */
        $st = $pdo->prepare('SELECT * FROM inventory_items WHERE id=? FOR UPDATE');
        $st->execute(array($itemId));
        $item = $st->fetch();
        if (!$item) throw new RuntimeException('Inventory item not found.');
        $cur = (int)$item['current_quantity'];
        if ($type === 'Stock Out' && $qty > $cur) throw new RuntimeException('Quantity removed cannot exceed the available stock (' . $cur . ' ' . $item['unit'] . ').');

        if ($type === 'Stock In') $new = $cur + $qty;
        elseif ($type === 'Adjustment') $new = $qty;
        else $new = $cur - $qty;

        $pdo->prepare('UPDATE inventory_items SET current_quantity=? WHERE id=?')->execute(array($new, $itemId));
        $pdo->prepare('INSERT INTO inventory_transactions (inventory_item_id,transaction_type,transaction_reason,quantity,description,processed_by) VALUES (?,?,?,?,?,?)')
            ->execute(array($itemId, $type, $reason, $qty, $desc, (int)$_SESSION['user']['id']));
        $pdo->commit();
        generate_inventory_notifications($pdo);
        flash('success', 'Supply transaction saved. New quantity: ' . $new . ' ' . $item['unit'] . '.');
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        fail_modal('Unable to save the supply transaction.', $back, 'supplyModal');
    } catch (RuntimeException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        fail_modal($e->getMessage(), $back, 'supplyModal');
    }
    redirect_to($back);
}
