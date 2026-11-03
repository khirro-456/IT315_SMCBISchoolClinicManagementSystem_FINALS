<?php defined('APP') or exit;
/* Optional: $extraScripts = array('assets/js/dashboard.js'); */
require __DIR__ . '/modals/password_form.php';
$reopen = take_reopen();
if (!$reopen && !empty($_SESSION['must_change_password'])) $reopen = array('modal' => 'passwordModal', 'data' => array());
?>
    <footer class="app-footer no-print">&copy; <?php echo date('Y'); ?> SMCBI School Clinic Management System &middot; Secure clinic records</footer>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>window.REOPEN = <?php echo json_encode($reopen, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE); ?>; window.RARE_PAIRS = <?php echo json_encode(RARE_LETTER_PAIRS); ?>;</script>
<script src="assets/js/app.js?v=11"></script>
<?php if (!empty($extraScripts)): foreach ($extraScripts as $s): ?>
<script src="<?php echo esc($s); ?>"></script>
<?php endforeach; endif; ?>
</body>
</html>
