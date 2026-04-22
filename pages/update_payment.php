<?php
// ==================== CHECK PAYMENT HISTORY ID ====================
if (!isset($_GET['pay_his_id']) || empty($_GET['pay_his_id'])) {
    echo "<script>alert('Payment History ID is required!'); window.history.back();</script>";
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

$old_history_paid_amount = (float) $history['paid_amount']; // এই পেমেন্টে আগে কত টাকা ছিল
$billing_month           = $history['bill_month'];
$tenant_id               = $history['tenant_id'];

// বর্তমান ইনভয়েস ডাটা আনা (আপডেট করার জন্য লাগবে)
$invoice_q = mysqli_query($db, "SELECT * FROM invoices WHERE billing_month = '$billing_month' AND tenant_id = '$tenant_id' LIMIT 1");
$invoice_data = mysqli_fetch_assoc($invoice_q);

if (!$invoice_data) {
    echo "<script>alert('Main invoice not found!'); window.history.back();</script>";
    exit();
}

$invoice_id = $invoice_data['id'];

// ==================== UPDATE PAYMENT LOGIC ====================
if (isset($_POST['update_payment'])) {

    $new_paid_amount        = (float) ($_POST['paid_amount'] ?? 0); // বর্তমান পেমেন্টের নতুন ফিগার
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

    // ১. ইনভয়েস টেবিল আপডেট করার ক্যালকুলেশন
    $invoice_paid_total = (float)$invoice_data['paid_amount'];
    $invoice_total_bill = (float)$invoice_data['total_amount'];
    
    // মেইন ইনভয়েসের টোটাল জমা আপডেট করার হিসাব: (পুরানো টোটাল - এই পেমেন্টের পুরানো অংশ) + এই পেমেন্টের নতুন অংশ
    $adjusted_invoice_paid = ($invoice_paid_total - $old_history_paid_amount) + $new_paid_amount;
    $new_due = $invoice_total_bill - $adjusted_invoice_paid;

    // স্ট্যাটাস নির্ধারণ
    if ($new_due <= 0) { $new_status = 'Paid'; } 
    elseif ($adjusted_invoice_paid > 0) { $new_status = 'Partial'; } 
    else { $new_status = 'Unpaid'; }

    // ২. ম্যানেজার সেলফ ক্যালকুলেশন (যদি মেথড ম্যানেজার হয়)
    $manager_self_new = 0;
    if($payment_method === 'Manager') {
        $manager_self_new = $new_paid_amount - $manager_payment - $expense;
    }

    // ৩. ডাটাবেজ আপডেট শুরু
    // ইনভয়েস আপডেট
    $up_inv = mysqli_query($db, "UPDATE invoices SET 
        paid_amount = '$adjusted_invoice_paid', 
        due_amount = '$new_due', 
        status = '$new_status' 
        WHERE id = '$invoice_id'");

    if ($up_inv) {
        // পেমেন্ট হিস্ট্রি আপডেট (এখানে 'paid' কলামে নতুন টোটাল জমা পাঠানো হচ্ছে)
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
                alert('Payment History and Main Invoice updated successfully!');
                window.location.href='admin.php?page=update_payment&pay_his_id=$pay_his_id';
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
        <div class="card-header bg-primary text-white">
            <h6 class="mb-0 text-white">Edit Payment Transaction</h6>
        </div>
        <div class="card-body">
            <form method="POST">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="fw-bold">Payment Amount *</label>
                        <input type="number" name="paid_amount" class="form-control border-primary" value="<?= $old_history_paid_amount ?>" required>
                        <!-- <small class="text-danger">এটি পরিবর্তন করলে ইনভয়েসের 'পেইড' এবং 'ডিউ' অটোমেটিক ঠিক হয়ে যাবে।</small> -->
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
                            <option value="Rocket" <?= ($history['payment_method'] == 'Rocket') ? 'selected' : '' ?>>Rocket</option> 
                            <option value="Bank Transfer" <?= ($history['payment_method'] == 'Bank Transfer') ? 'selected' : '' ?>>Bank Transfer</option>
                            <option value="Card" <?= ($history['payment_method'] == 'Card') ? 'selected' : '' ?>>Card</option>
                            <option value="Manager" <?= ($history['payment_method'] == 'Manager') ? 'selected' : '' ?>>Manager</option>
                        </select>
                    </div>
                </div>

                <div id="payment_fields" class="mt-3">
                    <div id="manager_section" style="display: none;" class="p-3 border rounded bg-light mb-3 shadow-sm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="text-primary fw-bold">Manager Received Amount</label>
                                <?php 
                                    // বর্তমান পেমেন্ট থেকে ম্যানেজারকে কত দেওয়া হয়েছে তার হিসাব
                                    $current_manager_payment = $old_history_paid_amount - (float)$history['manager_self'] - (float)$history['expense']; 
                                ?>
                                <input type="number" name="manager_payment" id="manager_payment" class="form-control" value="<?= $current_manager_payment ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="text-primary fw-bold">Manager Pay Method</label>
                                <select name="manager_payment_method" id="manager_payment_method" class="form-control form-select" onchange="toggleManagerTransaction()">
                                    <option value="" disabled>Select One</option>
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

                        <div class="row g-3 mt-1" id="expense_row">
                            <div class="col-md-6">
                                <label>Expense Amount</label>
                                <input type="number" name="expense" class="form-control" value="<?= (float)$history['expense'] ?>">
                            </div>
                            <div class="col-md-6">
                                <label>Expense Note</label>
                                <input type="text" name="expense_note" class="form-control" value="<?= htmlspecialchars($history['expense_note']) ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3" id="transaction_id_div">
                        <div class="col-md-6">
                            <label class="fw-bold">Transaction ID</label>
                            <input type="text" name="transaction_id" class="form-control" value="<?= htmlspecialchars($history['transaction_id']) ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold">Sender / Phone Number</label>
                            <input type="text" name="transaction_number" class="form-control" value="<?= htmlspecialchars($history['transaction_number']) ?>">
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="fw-bold">Note</label>
                    <textarea name="note" class="form-control" rows="2"><?= htmlspecialchars($history['note']) ?></textarea>
                </div>

                <div class="mt-4">
                    <button type="submit" name="update_payment" class="btn btn-success px-5 fw-bold">Update Record</button>
                    <a href="admin.php?page=payment_history" class="btn btn-secondary px-4">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function togglePaymentFields() {
        const method = document.getElementById('payment_method').value;
        const managerSection = document.getElementById('manager_section');
        const transactionIdDiv = document.getElementById('transaction_id_div');

        managerSection.style.display = 'none';
        transactionIdDiv.style.display = 'none';

        if (method === "Manager") {
            managerSection.style.display = 'block';
            toggleManagerTransaction(); 
        } 
        else if (method !== "Cash" && method !== "") {
            transactionIdDiv.style.display = 'flex';
        }
    }

    function toggleManagerTransaction() {
        const managerMethod = document.getElementById('manager_payment_method').value;
        const managerTransactionDiv = document.getElementById('manager_transaction_id_div');
        if (managerMethod === "Cash" || managerMethod === "") {
            managerTransactionDiv.style.display = 'none';
        } else {
            managerTransactionDiv.style.display = 'flex';
        }
    }

    window.onload = togglePaymentFields;
</script>