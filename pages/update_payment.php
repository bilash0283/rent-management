<?php
// ==================== CHECK ID ====================
if (!isset($_GET['pay_his_id']) || empty($_GET['pay_his_id'])) {
    echo "<script>alert('Payment History ID is required!'); window.history.back();</script>";
    exit();
}

$pay_his_id = (int) $_GET['pay_his_id'];

// ==================== FETCH EXISTING DATA ====================
// JOIN ব্যবহার করে ইনভয়েসের তথ্যও একবারে নিয়ে আসা হচ্ছে
$his_query = mysqli_query($db, "SELECT ph.*, inv.total_amount, inv.paid_amount as inv_paid_total, inv.status as inv_status 
                                FROM payment_history ph 
                                JOIN invoices inv ON ph.invoice_id = inv.id 
                                WHERE ph.id = '$pay_his_id' LIMIT 1");

if (mysqli_num_rows($his_query) == 0) {
    echo "<script>alert('Payment record not found!'); window.history.back();</script>";
    exit();
}

$history = mysqli_fetch_assoc($his_query);
$invoice_id = $history['invoice_id'];
$old_entry_amount = (float) $history['paid_amount']; // এই নির্দিষ্ট ট্রানজেকশনে আগে কত ছিল

// ==================== UPDATE LOGIC ====================
if (isset($_POST['update_payment'])) {
    $new_paid_amount = (float)$_POST['paid_amount'];
    $payment_method = mysqli_real_escape_string($db, $_POST['payment_method']);
    $note = mysqli_real_escape_string($db, $_POST['note']);
    $transaction_id = mysqli_real_escape_string($db, $_POST['transaction_id'] ?? '');
    $transaction_number = mysqli_real_escape_string($db, $_POST['transaction_number'] ?? '');
    
    // Manager logic
    $manager_paid_amount = (int)($_POST['manager_paid_amount'] ?? 0);
    $manager_payment_method = mysqli_real_escape_string($db, $_POST['manager_payment_method'] ?? '');

    $post_date = $_POST['payment_date'];
    $payment_date = date('Y-m-d H:i:s', strtotime($post_date));

    // ১. ইনভয়েস ক্যালকুলেশন (Adjustment)
    // ইনভয়েসের বর্তমান পেইড থেকে আগের এই এন্ট্রি বাদ দিয়ে নতুন এন্ট্রি যোগ করা
    $invoice_total_bill = (float)$history['total_amount'];
    $current_inv_paid = (float)$history['inv_paid_total'];
    
    $adjusted_paid = ($current_inv_paid - $old_entry_amount) + $new_paid_amount;
    $new_due = $invoice_total_bill - $adjusted_paid;

    // ভ্যালিডেশন
    if ($new_due < 0) {
        echo "<script>alert('Error: Total payment exceeds invoice amount!'); window.history.back();</script>";
        exit;
    }
    
    if ($payment_method === 'Manager' && $manager_paid_amount > $new_paid_amount) {
        echo "<script>alert('Error: Manager paid cannot be greater than Tenant paid!'); window.history.back();</script>";
        exit;
    }

    $status = ($new_due <= 0) ? 'Paid' : 'Partial';

    // ২. আপডেট ইনভয়েস টেবিল
    mysqli_query($db, "UPDATE invoices SET paid_amount = '$adjusted_paid', status = '$status' WHERE id = '$invoice_id'");

    // ৩. আপডেট পেমেন্ট হিস্ট্রি
    $update_sql = "UPDATE payment_history SET 
        paid_amount = '$new_paid_amount',
        payment_method = '$payment_method',
        note = '$note',
        payment_date = '$payment_date',
        manager_paid = '$manager_paid_amount',
        manager_payment_method = '$manager_payment_method',
        transaction_id = '$transaction_id',
        transaction_number = '$transaction_number'
        WHERE id = '$pay_his_id'";

    if (mysqli_query($db, $update_sql)) {
        echo "<script>alert('Payment Updated Successfully!'); window.location.href='admin.php?page=editbill&unit_id=" . ($_GET['unit_id'] ?? '') . "';</script>";
    } else {
        echo "Error: " . mysqli_error($db);
    }
}
?>

<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <form method="POST" id="paymentForm">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white d-flex justify-content-between">
                        <h5 class="mb-0">Update Payment History (#INV-<?= $invoice_id ?>)</h5>
                        <small>Total Bill: <?= number_format($history['total_amount'], 0) ?> ৳</small>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Amount Paid by Tenant *</label>
                                <input type="number" name="paid_amount" id="amount_input" class="form-control" value="<?= $history['paid_amount'] ?>" required>
                                <!-- Max limit calculations -->
                                <?php $max_allow = $history['total_amount'] - ($history['inv_paid_total'] - $history['paid_amount']); ?>
                                <input type="hidden" id="max_due_limit" value="<?= $max_allow ?>">
                                <small class="text-muted">Maximum allowable: <?= $max_allow ?> ৳</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Payment Date & Time *</label>
                                <input type="datetime-local" class="form-control" name="payment_date" value="<?= date('Y-m-d\TH:i', strtotime($history['payment_date'])) ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Payment Method *</label>
                                <select name="payment_method" id="payment_method" class="form-control form-select" required onchange="togglePaymentFields()">
                                    <?php 
                                    $methods = ['Cash', 'Bkash', 'Nagad', 'Rocket', 'Bank Transfer', 'Card', 'Manager'];
                                    foreach($methods as $m): ?>
                                        <option value="<?= $m ?>" <?= ($history['payment_method'] == $m) ? 'selected' : '' ?>><?= $m ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="fw-bold">Note</label>
                                <input type="text" name="note" class="form-control" value="<?= $history['note'] ?>" placeholder="Note for Payment">
                            </div>
                        </div>

                        <!-- Digital Payment Fields -->
                        <div id="digital_payment_fields" class="mt-2 p-3 border rounded bg-light" style="display: <?= in_array($history['payment_method'], ['Bkash', 'Nagad', 'Rocket', 'Bank Transfer', 'Card']) ? 'block' : 'none' ?>;">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Transaction ID</label>
                                    <input type="text" name="transaction_id" class="form-control" value="<?= $history['transaction_id'] ?>" placeholder="Txn ID">
                                </div>
                                <div class="col-md-6">
                                    <label>Transaction Number</label>
                                    <input type="text" name="transaction_number" class="form-control" value="<?= $history['transaction_number'] ?>" placeholder="Account Number">
                                </div>
                            </div>
                        </div>

                        <!-- Manager Payment Section -->
                        <div id="manager_fields" class="mt-2 p-3 border rounded bg-light" style="display: <?= ($history['payment_method'] == 'Manager') ? 'block' : 'none' ?>;">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="text-primary fw-bold">Manager paid to Admin</label>
                                    <input type="number" class="form-control" name="manager_paid_amount" id="manager_paid_amount" value="<?= $history['manager_paid'] ?>" placeholder="Manager Paid Amount">
                                </div>
                                <div class="col-md-6">
                                    <label class="text-primary fw-bold">Manager Payment Method</label>
                                    <select name="manager_payment_method" class="form-control form-select">
                                        <option value="" disabled>Select One</option>
                                        <?php 
                                        foreach(['Cash', 'Bkash', 'Nagad', 'Rocket', 'Bank Transfer', 'Card'] as $mm): ?>
                                            <option value="<?= $mm ?>" <?= ($history['manager_payment_method'] == $mm) ? 'selected' : '' ?>><?= $mm ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 row">
                            <div class="col-6">
                                <a href="admin.php?page=editbill&unit_id=<?= $_GET['unit_id'] ?? '' ?>" class="btn btn-secondary w-100">Cancel</a>
                            </div>
                            <div class="col-6">
                                <button type="submit" name="update_payment" class="btn btn-info w-100 text-white">Update Payment</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function togglePaymentFields() {
        const method = document.getElementById('payment_method').value;
        const digitalFields = document.getElementById('digital_payment_fields');
        const managerFields = document.getElementById('manager_fields');

        digitalFields.style.display = 'none';
        managerFields.style.display = 'none';

        if (['Bkash', 'Nagad', 'Rocket', 'Bank Transfer', 'Card'].includes(method)) {
            digitalFields.style.display = 'block';
        } else if (method === 'Manager') {
            managerFields.style.display = 'block';
        }
    }

    document.getElementById('paymentForm').onsubmit = function(e) {
        const paidAmount = parseFloat(document.getElementById('amount_input').value);
        const maxLimit = parseFloat(document.getElementById('max_due_limit').value);
        const method = document.getElementById('payment_method').value;

        if (paidAmount > maxLimit) {
            alert("Error: Total bill limit exceeded. Max allowed: " + maxLimit + " ৳");
            e.preventDefault();
            return false;
        }

        if (method === 'Manager') {
            const managerPaid = parseFloat(document.getElementById('manager_paid_amount').value || 0);
            if (managerPaid > paidAmount) {
                alert("Error: Manager payment to Admin cannot exceed Tenant's payment.");
                e.preventDefault();
                return false;
            }
        }
        return true;
    };
</script>