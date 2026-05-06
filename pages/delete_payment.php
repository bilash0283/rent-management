<?php
// ১. প্রয়োজনীয় প্যারামিটার চেক (unit_id, pay_his_id/id, invoice_id)
// আপনার স্লিপ পেজে 'id' হিসেবে পাস করা হয়েছে, তাই এখানে 'id' ধরা হয়েছে
if (!isset($_GET['pay_his_id']) || !isset($_GET['unit_id']) || !isset($_GET['invoice_id'])) {
    echo "<script>alert('Invalid request. Missing parameters.'); window.history.back();</script>";
    exit;
}

$pay_his_id = mysqli_real_escape_string($db, $_GET['pay_his_id']);
$invoice_id = mysqli_real_escape_string($db, $_GET['invoice_id']);
$unit_id    = mysqli_real_escape_string($db, $_GET['unit_id']);

// ২. পেমেন্ট রেকর্ডটি খুঁজে বের করা (ডিলিট করার আগে এর অ্যামাউন্ট জানা প্রয়োজন)
$his_query = mysqli_query($db, "SELECT paid_amount FROM payment_history WHERE id = '$pay_his_id' LIMIT 1");

if (mysqli_num_rows($his_query) === 0) {
    echo "<script>alert('Payment record not found.'); window.history.back();</script>";
    exit;
}

$history_data = mysqli_fetch_assoc($his_query);
$paid_to_undo = (float)$history_data['paid_amount']; // যে টাকাটি আমরা ডিলিট করছি

// ৩. ইনভয়েস তথ্য সংগ্রহ করা
$inv_query = mysqli_query($db, "SELECT total_amount, paid_amount FROM invoices WHERE id = '$invoice_id' LIMIT 1");

if (mysqli_num_rows($inv_query) > 0) {
    $invoice_data = mysqli_fetch_assoc($inv_query);
    
    $current_paid_total = (float)$invoice_data['paid_amount'];
    $total_bill_amount  = (float)$invoice_data['total_amount'];

    // নতুন ক্যালকুলেশন
    $new_invoice_paid = $current_paid_total - $paid_to_undo;
    
    // সেফটি চেক: পেইড অ্যামাউন্ট যেন ০ এর নিচে না যায়
    if ($new_invoice_paid < 0) $new_invoice_paid = 0;
    
    $new_invoice_due = $total_bill_amount - $new_invoice_paid;

    // ৪. স্ট্যাটাস আপডেট লজিক
    // যদি ডিলিট করার পর পেইড ০ হয় তবে Unpaid, না হলে Partial
    if ($new_invoice_paid <= 0) {
        $new_status = 'Unpaid';
    } elseif ($new_invoice_due > 0) {
        $new_status = 'Partial';
    } else {
        $new_status = 'Paid';
    }

    // ৫. ইনভয়েস টেবিল আপডেট করা
    $update_inv = mysqli_query($db, "UPDATE invoices SET 
                                    paid_amount = '$new_invoice_due', 
                                    status = '$new_status' 
                                    WHERE id = '$invoice_id'");

    if (!$update_inv) {
        die("Error updating invoice: " . mysqli_error($db));
    }
}

// ৬. পেমেন্ট হিস্ট্রি থেকে রেকর্ডটি ডিলিট করা
$delete_sql = mysqli_query($db, "DELETE FROM payment_history WHERE id = '$pay_his_id'");

if ($delete_sql) {
    echo "<script>
        alert('Success! Payment deleted and Invoice adjusted.');
        window.location.href='admin.php?page=editbill&unit_id=$unit_id';
    </script>";
    exit();
} else {
    die("Delete Error: " . mysqli_error($db));
}
?>