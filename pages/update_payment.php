<?php
// ==================== CHECK PAYMENT HISTORY ID ====================
if (!isset($_GET['pay_his_id']) || empty($_GET['pay_his_id'])) {
    echo "<script>alert('Payment History ID is required!'); window.history.back();</script>";
    exit();
}

$pay_his_id = (int) $_GET['pay_his_id'];
$this_month = date('Y-m');

// ==================== FETCH EXISTING PAYMENT HISTORY ====================
$his_query = mysqli_query($db, "SELECT * FROM payment_history WHERE id = '$pay_his_id' LIMIT 1");

if (mysqli_num_rows($his_query) == 0) {
    echo "<script>alert('Payment record not found!'); window.history.back();</script>";
    exit();
}

$history = mysqli_fetch_assoc($his_query);

$pay_slip_id            = $history['id'];
$manager_self           = (float) $history['manager_self'];
$expense                = (float) $history['expense'];
$expense_note           = $history['expense_note'];
$manager_payment_method = $history['manager_payment_method'];
$manager_transaction_id = $history['manager_transaction_id'];
$transaction_id         = $history['transaction_id'];
$transaction_number     = $history['transaction_number'];
$transaction_date       = $history['transaction_date'];
$note                   = $history['note'];
$tenant_id              = $history['tenant_id'];

// Fetch Tenant & Unit Info
$tenant_q = mysqli_query($db, "SELECT t.name, t.building_id, u.unit_name, u.building_name 
                               FROM tenants t 
                               JOIN unit u ON t.unit_id = u.id 
                               WHERE t.id = '$tenant_id' LIMIT 1");

$tenant = mysqli_fetch_assoc($tenant_q);

$tent_name       = $tenant['name'] ?? 'N/A';
$unit_name       = $tenant['unit_name'] ?? 'N/A';
$building_name   = $tenant['building_name'] ?? '';

$building_name_db = '';
if ($building_name) {
    $b_row = mysqli_fetch_assoc(mysqli_query($db, "SELECT name FROM building WHERE id = '$building_name'"));
    $building_name_db = $b_row['name'] ?? '';
}

// ==================== UPDATE PAYMENT LOGIC ====================
if (isset($_POST['save_bill'])) {

    $note                   = trim($_POST['note'] ?? '');
    $expense                = (float) ($_POST['expense'] ?? 0);
    $expense_note           = trim($_POST['expense_note'] ?? '');
    $manager_payment        = (float) ($_POST['manager_payment'] ?? 0);
    $manager_payment_method = trim($_POST['manager_payment_method'] ?? '');
    $manager_transaction_id = trim($_POST['manager_transaction_id'] ?? '');
    $transaction_id         = trim($_POST['transaction_id'] ?? '');
    $transaction_number     = trim($_POST['transaction_number'] ?? '');
    $transaction_date       = date('Y-m-d H:i:s');

    // Manager Self Update Logic (Fixed)
    $manager_self_update = $manager_self;
    if ($manager_payment > 0) {
        $manager_self_update = $manager_self - $manager_payment;
    }

    // Prevent negative value
    if ($manager_self_update < 0) {
        $manager_self_update = 0;
    }

    // Update Query with proper escaping
    $update_sql = "UPDATE payment_history SET 
        manager_self            = '$manager_self_update',
        expense                 = '$expense',
        expense_note            = '" . mysqli_real_escape_string($db, $expense_note) . "',
        manager_payment_method  = " . (empty($manager_payment_method) ? "NULL" : "'" . mysqli_real_escape_string($db, $manager_payment_method) . "'") . ",
        manager_transaction_id  = " . (empty($manager_transaction_id) ? "NULL" : "'" . mysqli_real_escape_string($db, $manager_transaction_id) . "'") . ",
        transaction_id          = " . (empty($transaction_id) ? "NULL" : "'" . mysqli_real_escape_string($db, $transaction_id) . "'") . ",
        transaction_number      = " . (empty($transaction_number) ? "NULL" : "'" . mysqli_real_escape_string($db, $transaction_number) . "'") . ",
        transaction_date        = '" . mysqli_real_escape_string($db, $transaction_date) . "',
        note                    = '" . mysqli_real_escape_string($db, $note) . "'
    WHERE id = '$pay_his_id'";

    if (mysqli_query($db, $update_sql)) {
        echo "<script>
            alert('Payment history updated successfully!');
            window.location.href='admin.php?page=update_payment&pay_his_id=$pay_slip_id';
        </script>";
        exit();
    } else {
        die("Update Error: " . mysqli_error($db));
    }
}
?>

<div class="nxl-content mx-3">
    <div class="page-header d-flex justify-content-between align-items-center mb-3">
        <h5>
            <?= htmlspecialchars($building_name_db) ?> /
            <?= htmlspecialchars($unit_name) ?> /
            <?= htmlspecialchars($tent_name) ?>
            <small class="text-muted">(Edit Payment History)</small>
        </h5>
    </div>

    <div class="card">
        <div class="card-header">
            <h6 class="fw-bold">Edit Payment History</h6>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label>Manager Payable Amount (Current Payment)</label>
                        <input type="number" name="manager_payment" id="manager_payment" 
                               class="form-control" value="0" min="0">
                        <small class="text-muted">Current remaining: <?= number_format($manager_self, 2) ?></small>
                    </div>
                    <div class="col-md-6">
                        <label>Manager Payment Method</label>
                        <select name="manager_payment_method" class="form-control form-select">
                            <option value="">Select One</option>
                            <option value="Cash" <?= ($manager_payment_method == 'Cash') ? 'selected' : '' ?>>Cash</option>
                            <option value="Bkash" <?= ($manager_payment_method == 'Bkash') ? 'selected' : '' ?>>Bkash</option>
                            <option value="Nagad" <?= ($manager_payment_method == 'Nagad') ? 'selected' : '' ?>>Nagad</option>
                            <option value="Rocket" <?= ($manager_payment_method == 'Rocket') ? 'selected' : '' ?>>Rocket</option>
                            <option value="Bank Transfer" <?= ($manager_payment_method == 'Bank Transfer') ? 'selected' : '' ?>>Bank Transfer</option>
                            <option value="Card" <?= ($manager_payment_method == 'Card') ? 'selected' : '' ?>>Card</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <label>Manager Transaction ID</label>
                        <input type="text" name="manager_transaction_id" class="form-control"
                            value="<?= htmlspecialchars($manager_transaction_id ?? '') ?>">
                    </div>
                    <div class="col-md-6">
                        <label>Transaction Number</label>
                        <input type="text" name="transaction_number" class="form-control"
                            value="<?= htmlspecialchars($transaction_number ?? '') ?>">
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <label>Expense Amount</label>
                        <input type="number" name="expense" class="form-control" 
                               value="<?= $expense ?>">
                    </div>
                    <div class="col-md-6">
                        <label>Expense Note</label>
                        <input type="text" name="expense_note" class="form-control"
                            value="<?= htmlspecialchars($expense_note ?? '') ?>">
                    </div>
                </div>

                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <label>Other Transaction ID</label>
                        <input type="text" name="transaction_id" class="form-control"
                            value="<?= htmlspecialchars($transaction_id ?? '') ?>">
                    </div>
                </div>

                <div class="mt-3">
                    <label>Note</label>
                    <input type="text" name="note" class="form-control" 
                           value="<?= htmlspecialchars($note ?? '') ?>">
                </div>

                <button type="submit" name="save_bill" class="btn btn-success mt-4">
                    Update Payment History
                </button>
            </form>
        </div>
    </div>
</div>