<?php
// ==================== CHECK PAYMENT HISTORY ID ====================
if (!isset($_GET['pay_his_id']) || empty($_GET['pay_his_id'])) {
    echo "<script>alert('Payment History ID is required!'); window.history.back();</script>";
    exit();
}

if(isset($_GET['unit_id'])) {
    $unit_id = (int) $_GET['unit_id'];
} else {
    echo "<script>alert('Unit ID is required!'); window.history.back();</script>";
    exit();
}

$pay_his_id = (int) $_GET['pay_his_id'];

// ==================== FETCH EXISTING PAYMENT HISTORY ====================
$his_query = mysqli_query($db, "SELECT * FROM payment_history WHERE id = '$pay_his_id' LIMIT 1");

if (mysqli_num_rows($his_query) == 0) {
    echo "<script>alert('Payment record not found!'); window.history.back();</script>";
    exit();
}

$history = mysqli_fetch_assoc($his_query);

$old_history_paid_amount = (float) $history['paid_amount']; 
$billing_month           = $history['bill_month'];
$tenant_id               = $history['tenant_id'];

// বর্তমান ইনভয়েস ডাটা আনা
$invoice_q = mysqli_query($db, "SELECT * FROM invoices WHERE billing_month = '$billing_month' AND tenant_id = '$tenant_id' LIMIT 1");
$invoice_data = mysqli_fetch_assoc($invoice_q);

if (!$invoice_data) {
    echo "<script>alert('Main invoice not found!'); window.history.back();</script>";
    exit();
}

$invoice_id = $invoice_data['id'];

// ==================== UPDATE PAYMENT LOGIC ====================
if (isset($_POST['update_payment'])) {

    $new_paid_amount        = (float) ($_POST['paid_amount'] ?? 0); 
    $payment_method         = trim($_POST['payment_method'] ?? '');
    $note                   = trim($_POST['note'] ?? '');
    $expense                = (float) ($_POST['expense'] ?? 0);
    $expense_note           = trim($_POST['expense_note'] ?? '');
    $manager_payment        = (float) ($_POST['manager_payment'] ?? 0);
    $manager_payment_method = trim($_POST['manager_payment_method'] ?? '');
    $manager_transaction_id = trim($_POST['manager_transaction_id'] ?? '');
    $transaction_id         = trim($_POST['transaction_id'] ?? '');
    $transaction_number     = trim($_POST['transaction_number'] ?? '');
    $post_date              = $_POST['transaction_date'];
    $transaction_date       = $post_date ? date('Y-m-d H:i:s', strtotime($post_date)) : date('Y-m-d H:i:s');

    // --- নতুন কন্ডিশন: ম্যানেজার পেমেন্ট কি টেন্যান্ট থেকে প্রাপ্ত পেমেন্টের চেয়ে বেশি? ---
    if ($payment_method === 'Manager' && $manager_payment > $new_paid_amount) {
        echo "<script>
            alert('Error: Manager cannot pay more than the amount received from the tenant ($new_paid_amount)!');
            window.history.back();
        </script>";
        exit();
    }

    // ১. ইনভয়েস টেবিল আপডেট করার ক্যালকুলেশন
    $invoice_paid_total = (float)$invoice_data['paid_amount'];
    $invoice_total_bill = (float)$invoice_data['total_amount'];
    
    $adjusted_invoice_paid = ($invoice_paid_total - $old_history_paid_amount) + $new_paid_amount;

    if ($adjusted_invoice_paid > $invoice_total_bill) {
        $max_allowed = $invoice_total_bill - ($invoice_paid_total - $old_history_paid_amount);
        echo "<script>
            alert('Error: Total paid amount cannot exceed the Total Bill ($invoice_total_bill)! You can pay maximum $max_allowed.');
            window.history.back();
        </script>";
        exit();
    }

    $new_due = $invoice_total_bill - $adjusted_invoice_paid;

    if ($new_due <= 0) { $new_status = 'Paid'; } 
    elseif ($adjusted_invoice_paid > 0) { $new_status = 'Partial'; } 
    else { $new_status = 'Unpaid'; }

    // ২. ম্যানেজার সেলফ ক্যালকুলেশন
    $manager_self_new = 0;
    if($payment_method === 'Manager') {
        $manager_self_new = $new_paid_amount - $manager_payment;
    }

    // ৩. ডাটাবেজ আপডেট
    $up_inv = mysqli_query($db, "UPDATE invoices SET 
        paid_amount = '$adjusted_invoice_paid', 
        due_amount = '$new_due', 
        status = '$new_status' 
        WHERE id = '$invoice_id'");

    if ($up_inv) {
        $update_sql = "UPDATE payment_history SET 
            paid_amount             = '$new_paid_amount',
            paid                    = '$adjusted_invoice_paid',
            due                     = '$new_due',
            payment_method          = '" . mysqli_real_escape_string($db, $payment_method) . "',
            manager_self            = '$manager_self_new',
            expense                 = '$expense',
            expense_note            = '" . mysqli_real_escape_string($db, $expense_note) . "',
            manager_payment_method  = " . (empty($manager_payment_method) ? "NULL" : "'" . mysqli_real_escape_string($db, $manager_payment_method) . "'") . ",
            manager_transaction_id  = " . (empty($manager_transaction_id) ? "NULL" : "'" . mysqli_real_escape_string($db, $manager_transaction_id) . "'") . ",
            transaction_id          = " . (empty($transaction_id) ? "NULL" : "'" . mysqli_real_escape_string($db, $transaction_id) . "'") . ",
            transaction_number      = " . (empty($transaction_number) ? "NULL" : "'" . mysqli_real_escape_string($db, $transaction_number) . "'") . ",
            transaction_date        = '$transaction_date',
            note                    = '" . mysqli_real_escape_string($db, $note) . "'
        WHERE id = '$pay_his_id'";

        if (mysqli_query($db, $update_sql)) {
            echo "<script>
                alert('Payment updated successfully! Manager Self: $manager_self_new');
                window.location.href='admin.php?page=update_payment&pay_his_id=$pay_his_id&unit_id=$unit_id';
            </script>";
            exit();
        }
    } else {
        die("Update Error: " . mysqli_error($db));
    }
}

$transaction_date_ui = date('Y-m-d\TH:i', strtotime($history['transaction_date']));
?>

<div class="nxl-content mx-3">
    <div class="card mt-4 shadow-sm">
        <div class="card-header bg-light d-flex justify-content-between align-items-center">
            <h6 class="mb-0 text-black">Edit Payment (Total Bill: <?= $invoice_data['total_amount'] ?>)</h6>
            <a href="admin.php?page=editbill&unit_id=<?= $unit_id ?>" class="btn btn-secondary px-4">Back</a>
        </div>
        <div class="card-body">
            <form method="POST" onsubmit="return validateManagerPayment()">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="fw-bold">Payment Amount (From Tenant) *</label>
                        <input type="number" step="any" name="paid_amount" id="paid_amount" class="form-control border-primary" value="<?= $old_history_paid_amount ?>" required>
                    </div>
                    
                    <div class="col-md-4">
                        <label class="fw-bold">Transaction Date *</label>
                        <input type="datetime-local" class="form-control" name="transaction_date" value="<?= $transaction_date_ui ?>" required>
                    </div>

                    <div class="col-md-4">
                        <label class="fw-bold">Payment Method *</label>
                        <select name="payment_method" id="payment_method" class="form-control form-select" required onchange="togglePaymentFields()">
                            <option value="Cash" <?= ($history['payment_method'] == 'Cash') ? 'selected' : '' ?>>Cash</option>
                            <option value="Bkash" <?= ($history['payment_method'] == 'Bkash') ? 'selected' : '' ?>>Bkash</option>
                            <option value="Nagad" <?= ($history['payment_method'] == 'Nagad') ? 'selected' : '' ?>>Nagad</option>
                            <option value="Manager" <?= ($history['payment_method'] == 'Manager') ? 'selected' : '' ?>>Manager</option>
                            <option value="Bank Transfer" <?= ($history['payment_method'] == 'Bank Transfer') ? 'selected' : '' ?>>Bank Transfer</option>
                        </select>
                    </div>
                </div>

                <div id="payment_fields" class="mt-3">
                    <div id="manager_section" style="display: none;" class="p-3 border rounded bg-light mb-3 shadow-sm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="text-primary fw-bold">Manager Paid to Admin</label>
                                <?php $display_manager_paid = $old_history_paid_amount - (float)$history['manager_self']; ?>
                                <input type="number" step="any" name="manager_payment" id="manager_payment" class="form-control" value="<?= $display_manager_paid ?>">
                                <small class="text-muted">Amount deposited by the manager to the Admin</small>
                            </div>
                            <div class="col-md-6">
                                <label class="text-primary fw-bold">Manager Pay Method</label>
                                <select name="manager_payment_method" id="manager_payment_method" class="form-control form-select" onchange="toggleManagerTransaction()">
                                    <option value="Cash" <?= ($history['manager_payment_method'] == 'Cash') ? 'selected' : '' ?>>Cash</option>
                                    <option value="Bkash" <?= ($history['manager_payment_method'] == 'Bkash') ? 'selected' : '' ?>>Bkash</option>
                                    <option value="Nagad" <?= ($history['manager_payment_method'] == 'Nagad') ? 'selected' : '' ?>>Nagad</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mt-1" id="manager_transaction_id_div">
                            <div class="col-md-6">
                                <label>Manager Transaction ID</label>
                                <input type="text" name="manager_transaction_id" class="form-control" value="<?= htmlspecialchars($history['manager_transaction_id']) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3" id="transaction_id_div">
                        <div class="col-md-6">
                            <label class="fw-bold">Transaction ID</label>
                            <input type="text" name="transaction_id" class="form-control" value="<?= htmlspecialchars($history['transaction_id'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">Sender Phone Number</label>
                            <input type="text" name="transaction_number" class="form-control" value="<?= htmlspecialchars($history['transaction_number'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row g-3 mt-3 border-top pt-3">
                        <div class="col-md-4">
                            <label class="text-danger fw-bold">Expense Amount</label>
                            <input type="number" step="any" name="expense" class="form-control border-danger" value="<?= (float)$history['expense'] ?>">
                        </div>
                        <div class="col-md-8">
                            <label class="fw-bold">Expense Note</label>
                            <input type="text" name="expense_note" class="form-control" value="<?= htmlspecialchars($history['expense_note']) ?>" placeholder="কি বাবদ খরচ হয়েছে?">
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="fw-bold">General Note</label>
                    <textarea name="note" class="form-control" rows="2"><?= htmlspecialchars($history['note']) ?></textarea>
                </div>

                <div class="mt-4">
                    <button type="submit" name="update_payment" class="btn btn-success px-5 fw-bold">Update Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function validateManagerPayment() {
        const method = document.getElementById('payment_method').value;
        const tenantPaid = parseFloat(document.getElementById('paid_amount').value) || 0;
        const managerPaid = parseFloat(document.getElementById('manager_payment').value) || 0;

        if (method === "Manager") {
            if (managerPaid > tenantPaid) {
                alert("Error: Manager Paid to Office (" + managerPaid + ") cannot be greater than Tenant Payment (" + tenantPaid + ")!");
                return false;
            }
        }
        return true;
    }

    function togglePaymentFields() {
        const method = document.getElementById('payment_method').value;
        const managerSection = document.getElementById('manager_section');
        const transactionIdDiv = document.getElementById('transaction_id_div');

        managerSection.style.display = 'none';
        transactionIdDiv.style.display = 'none';

        if (method === "Manager") {
            managerSection.style.display = 'block';
            toggleManagerTransaction(); 
        } else if (method !== "Cash" && method !== "") {
            transactionIdDiv.style.display = 'flex';
        }
    }

    function toggleManagerTransaction() {
        const managerMethod = document.getElementById('manager_payment_method').value;
        const managerTransactionDiv = document.getElementById('manager_transaction_id_div');
        if (managerTransactionDiv) {
            managerTransactionDiv.style.display = (managerMethod === "Cash" || managerMethod === "") ? 'none' : 'flex';
        }
    }

    window.onload = togglePaymentFields;
</script>