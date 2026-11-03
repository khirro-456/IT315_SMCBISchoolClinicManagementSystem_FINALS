<?php defined('APP') or exit;

if ($action === 'save_visit') {
    $back = return_page('visits');
    $pid = (int)p('patient_id'); $itemId = (int)p('item_id'); $used = (int)p('used_quantity');
    $cc = p('chief_complaint'); $temp = p('temperature'); $bp = p('blood_pressure'); $pr = p('pulse_rate');
    $as = p('assessment'); $tg = p('treatment_given'); $rc = p('recommendation'); $nt = p('notes');

    $rules = array(
        array($pid > 0, 'Please select a patient.'),
        array(strlen($cc) >= 2 && valid_clinical_text($cc), 'Chief Complaint is required and must use valid characters.'),
        array(valid_temperature($temp), 'Temperature must be between 35.0 and 42.0 °C. Example: 37.2'),
        array(valid_blood_pressure($bp), 'Blood Pressure must use the format 120/80 with realistic values.'),
        array(valid_pulse_rate($pr), 'Pulse Rate must be a number from 30 to 250 bpm.'),
        array($as !== '' && valid_clinical_text($as), 'Assessment is required and must use valid characters.'),
        array($tg !== '' && valid_clinical_text($tg), 'Treatment Given (action taken) is required and must use valid characters.'),
        array(valid_clinical_text($rc), 'Recommendation contains invalid characters.'),
        array(valid_clinical_text($nt), 'Additional Notes contains invalid characters.'),
        array($itemId < 1 || $used >= 1, 'Enter a quantity of 1 or more for the selected medicine/supply.')
    );
    foreach ($rules as $r) { if (!$r[0]) fail_modal($r[1], $back, 'visitModal'); }

    try {
        $pdo->beginTransaction();
        $chk = $pdo->prepare('SELECT id FROM patients WHERE id=?');
        $chk->execute(array($pid));
        if (!$chk->fetch()) throw new RuntimeException('Selected patient was not found.');

        $pdo->prepare('INSERT INTO clinic_visits (patient_id,attended_by,chief_complaint,temperature,blood_pressure,pulse_rate,assessment,treatment_given,recommendation,referred_to_hospital,guardian_notified,notes) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)')
            ->execute(array($pid, (int)$_SESSION['user']['id'], $cc, $temp, $bp, $pr, $as, $tg, $rc, p('referred_to_hospital') === 'Yes' ? 'Yes' : 'No', p('guardian_notified') === 'Yes' ? 'Yes' : 'No', $nt));
        $vid = (int)$pdo->lastInsertId();

        if ($itemId > 0 && $used > 0) {
            $st = $pdo->prepare('SELECT * FROM inventory_items WHERE id=? FOR UPDATE');
            $st->execute(array($itemId));
            $item = $st->fetch();
            if (!$item) throw new RuntimeException('Selected medicine/supply was not found.');
            if ($used > (int)$item['current_quantity']) throw new RuntimeException('Insufficient stock. Only ' . $item['current_quantity'] . ' ' . $item['unit'] . ' available.');
            $ex = expiry_status($item['expiration_date']);
            if ($ex['blocked']) throw new RuntimeException('Expired medicine/supply cannot be issued.');

            $pdo->prepare('UPDATE inventory_items SET current_quantity=current_quantity-? WHERE id=?')->execute(array($used, $itemId));
            $pdo->prepare('INSERT INTO visit_items (clinic_visit_id,inventory_item_id,quantity_used) VALUES (?,?,?)')->execute(array($vid, $itemId, $used));
            $pdo->prepare('INSERT INTO inventory_transactions (inventory_item_id,transaction_type,transaction_reason,quantity,description,processed_by) VALUES (?,?,?,?,?,?)')
                ->execute(array($itemId, 'Visit Usage', 'Used During Clinic Visit', $used, 'Used in clinic visit #' . $vid, (int)$_SESSION['user']['id']));
        }
        $pdo->commit();
        generate_inventory_notifications($pdo);
        flash('success', 'Clinic visit saved. Inventory was updated if an item was issued.');
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        fail_modal('Unable to save the clinic visit. Please try again.', $back, 'visitModal');
    } catch (RuntimeException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        fail_modal($e->getMessage(), $back, 'visitModal');
    }
    redirect_to($back);
}
