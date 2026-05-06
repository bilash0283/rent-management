<?php
// ==================== CHECK ID ====================
if (!isset($_GET['pay_his_id']) || empty($_GET['pay_his_id'])) {
    echo "<script>alert('Payment History ID is required!'); window.history.back();</script>";
    exit();
}

$pay_his_id = (int) $_GET['pay_his_id'];
$unit_id = $_GET['unit_id'] ?? '';
$success_msg = false;
$error_msg = false;

// ==================== FETCH EXISTING DATA ====================
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
$old_entry_amount = (float) $history['paid_amount'];

// ইনভয়েসের বাকি অংশ ক্যালকুলেশন
$other_payments_total = (float)$history['inv_paid_total'] - $old_entry_amount;
$max_allowable = (float)$history['total_amount'] - $other_payments_total;

// ==================== UPDATE LOGIC ====================
if (isset($_POST['update_payment'])) {
    $new_paid_amount = (float)$_POST['paid_amount'];
    $payment_method = mysqli_real_escape_string($db, $_POST['payment_method']);
    $note = mysqli_real_escape_string($db, $_POST['note']);
    $transaction_id = mysqli_real_escape_string($db, $_POST['transaction_id'] ?? '');
    $transaction_number = mysqli_real_escape_string($db, $_POST['transaction_number'] ?? '');
    
    // --- কন্ডিশন: পেমেন্ট মেথড Manager না হলে ভ্যালু ০ করে দেওয়া ---
    if ($payment_method === 'Manager') {
        $manager_paid_amount = (float)($_POST['manager_paid_amount'] ?? 0);
        $manager_payment_method = mysqli_real_escape_string($db, $_POST['manager_payment_method'] ?? '');
    } else {
        $manager_paid_amount = 0; // মেথড চেঞ্জ করলে অটো ০ হবে
        $manager_payment_method = ''; 
    }

    $post_date = $_POST['payment_date'];
    $payment_date = date('Y-m-d H:i:s', strtotime($post_date));

    // ১. ভ্যালিডেশন: ইনভয়েসের লিমিট ক্রস করছে কিনা
    if ($new_paid_amount > $max_allowable) {
        $error_msg = "Error: Amount exceeds the remaining bill balance! Max allowed: $max_allowable ৳";
    } 
    // ২. ভ্যালিডেশন: ম্যানেজার পেইড টেন্যান্ট পেইড এর চেয়ে বেশি কিনা
    elseif ($payment_method === 'Manager' && $manager_paid_amount > $new_paid_amount) {
        $error_msg = "Error: Manager payment to Admin cannot exceed Tenant's payment ($new_paid_amount ৳)!";
    } 
    else {
        // ৩. ইনভয়েস টেবিল আপডেট
        $adjusted_inv_paid = $other_payments_total + $new_paid_amount;
        $new_status = ($adjusted_inv_paid >= (float)$history['total_amount']) ? 'Paid' : (($adjusted_inv_paid > 0) ? 'Partial' : 'Unpaid');

        mysqli_query($db, "UPDATE invoices SET paid_amount = '$adjusted_inv_paid', status = '$new_status' WHERE id = '$invoice_id'");

        // ৪. পেমেন্ট হিস্ট্রি আপডেট
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
            $success_msg = "Payment Updated Successfully!";
            // ডাটা রিফ্রেশ
            $his_query = mysqli_query($db, "SELECT ph.*, inv.total_amount, inv.paid_amount as inv_paid_total FROM payment_history ph JOIN invoices inv ON ph.invoice_id = inv.id WHERE ph.id = '$pay_his_id' LIMIT 1");
            $history = mysqli_fetch_assoc($his_query);
            $max_allowable = (float)$history['total_amount'] - ((float)$history['inv_paid_total'] - (float)$history['paid_amount']);
        } else {
            $error_msg = "Database Error: " . mysqli_error($db);
        }
    }
}
?>

<!-- UI পার্ট আগের মতোই থাকবে, শুধু স্ক্রিপ্ট একটু আপডেট হয়েছে -->
<div class="container-fluid mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <?php if($success_msg): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <strong>Success!</strong> <?= $success_msg ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if($error_msg): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <strong>Failed!</strong> <?= $error_msg ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form method="POST" id="paymentForm">
                <div class="card shadow-sm">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0">Update Payment History (#INV-<?= $invoice_id ?>)</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="fw-bold">Amount Paid by Tenant *</label>
                                <input type="number" name="paid_amount" id="amount_input" class="form-control" value="<?= $history['paid_amount'] ?>" step="any" required>
                                <small class="text-danger fw-bold">Max: <?= $max_allowable ?> ৳</small>
                                <input type="hidden" id="max_due_limit" value="<?= $max_allowable ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold">Payment Date *</label>
                                <input type="datetime-local" class="form-control" name="payment_date" value="<?= date('Y-m-d\TH:i', strtotime($history['payment_date'])) ?>" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="fw-bold">Payment Method *</label>
                                <select name="payment_method" id="payment_method" class="form-select" required onchange="togglePaymentFields()">
                                    <?php 
                                    $methods = ['Cash', 'Bkash', 'Nagad', 'Rocket', 'Bank Transfer', 'Card', 'Manager'];
                                    foreach($methods as $m): ?>
                                        <option value="<?= $m ?>" <?= ($history['payment_method'] == $m) ? 'selected' : '' ?>><?= $m ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="fw-bold">Note</label>
                                <input type="text" name="note" class="form-control" value="<?= $history['note'] ?>">
                            </div>
                        </div>

                        <div id="digital_payment_fields" class="p-3 border rounded bg-light mb-3" style="display: <?= in_array($history['payment_method'], ['Bkash', 'Nagad', 'Rocket', 'Bank Transfer', 'Card']) ? 'block' : 'none' ?>;">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Transaction ID</label>
                                    <input type="text" name="transaction_id" class="form-control" value="<?= $history['transaction_id'] ?>">
                                </div>
                                <div class="col-md-6">
                                    <label>Account Number</label>
                                    <input type="text" name="transaction_number" class="form-control" value="<?= $history['transaction_number'] ?>">
                                </div>
                            </div>
                        </div>

                        <div id="manager_fields" class="p-3 border rounded bg-light mb-3" style="display: <?= ($history['payment_method'] == 'Manager') ? 'block' : 'none' ?>;">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="text-primary fw-bold">Manager paid to Admin</label>
                                    <input type="number" class="form-control" name="manager_paid_amount" id="manager_paid_amount" value="<?= $history['manager_paid'] ?>" step="any">
                                </div>
                                <div class="col-md-6">
                                    <label class="text-primary fw-bold">Manager Payment Method</label>
                                    <select name="manager_payment_method" id="manager_payment_method" class="form-select">
                                        <option value="">Select One</option>
                                        <?php foreach(['Cash', 'Bkash', 'Nagad', 'Rocket', 'Bank Transfer', 'Card'] as $mm): ?>
                                            <option value="<?= $mm ?>" <?= ($history['manager_payment_method'] == $mm) ? 'selected' : '' ?>><?= $mm ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <button type="submit" name="update_payment" class="btn btn-info w-100 text-white">Update Payment</button>
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
        const managerInput = document.getElementById('manager_paid_amount');
        const managerMethod = document.getElementById('manager_payment_method');

        digitalFields.style.display = 'none';
        managerFields.style.display = 'none';

        if (['Bkash', 'Nagad', 'Rocket', 'Bank Transfer', 'Card'].includes(method)) {
            digitalFields.style.display = 'block';
        } else if (method === 'Manager') {
            managerFields.style.display = 'block';
        } else {
            // যদি Manager থেকে সরিয়ে অন্য কিছু সিলেক্ট করে, তবে ইনপুট ভ্যালু ০ করে দেওয়া হবে
            managerInput.value = 0;
            managerMethod.value = "";
        }
    }

    document.getElementById('paymentForm').onsubmit = function(e) {
        const paidAmount = parseFloat(document.getElementById('amount_input').value || 0);
        const maxLimit = parseFloat(document.getElementById('max_due_limit').value || 0);
        const method = document.getElementById('payment_method').value;

        if (paidAmount > maxLimit) {
            alert("Error: Amount exceeds remaining bill (" + maxLimit + " ৳)");
            e.preventDefault();
            return false;
        }

        if (method === 'Manager') {
            const managerPaid = parseFloat(document.getElementById('manager_paid_amount').value || 0);
            if (managerPaid > paidAmount) {
                alert("Error: Manager payment to Admin cannot exceed Tenant's payment!");
                e.preventDefault();
                return false;
            }
        }
        return true;
    };
</script>