<?php
// ১. প্রয়োজনীয় প্যারামিটার চেক করা
if (!isset($_GET['pay_his_id']) || !isset($_GET['unit_id'])) {
    echo "<script>alert('Invalid request. Missing parameters.'); window.history.back();</script>";
    exit;
}

$pay_his_id = mysqli_real_escape_string($db, $_GET['pay_his_id']);
$unit_id    = mysqli_real_escape_string($db, $_GET['unit_id']);

// ২. পেমেন্ট হিস্ট্রি রেকর্ডটি আগে খুঁজে বের করা (ডিলিট করার আগে তথ্য প্রয়োজন)
$his_query = mysqli_query($db, "SELECT * FROM payment_history WHERE id = '$pay_his_id' LIMIT 1");

if (mysqli_num_rows($his_query) === 0) {
    echo "<script>alert('Payment record not found.'); window.history.back();</script>";
    exit;
}

$history_data = mysqli_fetch_assoc($his_query);
$tenant_id    = $history_data['tenant_id'];
$bill_month   = $history_data['bill_month'];
$paid_to_undo = (float)$history_data['paid_amount']; // যে পরিমাণ টাকা ডিলিট করা হচ্ছে

// ৩. সংশ্লিষ্ট ইনভয়েস খুঁজে বের করা
$inv_query = mysqli_query($db, "SELECT * FROM invoices 
                                WHERE billing_month = '$bill_month' 
                                AND tenant_id = '$tenant_id' 
                                LIMIT 1");

if (mysqli_num_rows($inv_query) > 0) {
    $invoice_data = mysqli_fetch_assoc($inv_query);
    $invoice_id   = $invoice_data['id'];
    
    // নতুন ক্যালকুলেশন
    $current_paid_total = (float)$invoice_data['paid_amount'];
    $total_bill_amount  = (float)$invoice_data['total_amount'];

    // ইনভয়েস থেকে পেইড অ্যামাউন্ট কমিয়ে দেয়া
    $new_invoice_paid = $current_paid_total - $paid_to_undo;
    $new_invoice_due  = $total_bill_amount - $new_invoice_paid;

    // স্ট্যাটাস আপডেট (যদি পেইড ০ হয় তবে Unpaid, না হলে Partial)
    if ($new_invoice_paid <= 0) {
        $new_status = 'Unpaid';
        $new_invoice_paid = 0; // Negative এ যেন না যায়
    } elseif ($new_invoice_due > 0) {
        $new_status = 'Partial';
    } else {
        $new_status = 'Paid';
    }

    // ৪. ইনভয়েস টেবিল আপডেট করা
    $update_inv = mysqli_query($db, "UPDATE invoices SET 
                                    paid_amount = '$new_invoice_paid', 
                                    due_amount = '$new_invoice_due', 
                                    status = '$new_status' 
                                    WHERE id = '$invoice_id'");

    if (!$update_inv) {
        die("Error updating invoice: " . mysqli_error($db));
    }
}

// ৫. এখন পেমেন্ট হিস্ট্রি থেকে রেকর্ডটি ডিলিট করা
$delete_sql = mysqli_query($db, "DELETE FROM payment_history WHERE id = '$pay_his_id'");

if ($delete_sql) {
    echo "<script>
        alert('Payment record deleted and invoice adjusted successfully.');
        window.location.href='admin.php?page=editbill&unit_id=$unit_id';
    </script>";
    exit();
} else {
    die("Delete Error: " . mysqli_error($db));
}
?>