<?php
// tenant info 
$tenant_id = $_SESSION['id'];
$tenant_q = mysqli_query($db, "SELECT * FROM tenants WHERE id = '$tenant_id'");
$tenant_info = mysqli_fetch_assoc($tenant_q);
$buill_id = $tenant_info['building_id'] ?? '';
$unit_id = $tenant_info['unit_id'] ?? '';
$status = $tenant_info['status'] ?? '';
$tenant_name = $tenant_info['name'] ?? '';

//advance paid info
$advance_q = mysqli_query($db,"SELECT * FROM advance WHERE tenant_id  = '$tenant_id' AND unit_id = '$unit_id'");
$paid_amount = 0;
while($advance_info = mysqli_fetch_assoc($advance_q)){
    $paid_amount += $advance_info['paid_amount'] ?? '';
    $advance_paid_date = $advance_info['date'];
}

//invoice info 
$invoice_q = mysqli_query($db,"SELECT * FROM invoices WHERE tenant_id = '$tenant_id' AND unit_id = '$unit_id' AND billing_month = '$this_month' ");
mysqli_num_rows($invoice_q) > 0 ? $invoice_info = mysqli_fetch_assoc($invoice_q) : $invoice_info = [];
$total_rent = $invoice_info['total_amount'] ?? '';
$bill_month = $invoice_info['billing_month'] ?? '';

//building info 
$buliding_q = mysqli_query($db, "SELECT * FROM building WHERE id = '$buill_id'");
$building_info = mysqli_fetch_assoc($buliding_q);
$building_name = $building_info['name'] ?? '';

//unit info
$unit_q = mysqli_query($db, "SELECT * FROM unit WHERE id = '$unit_id' AND building_name = '$buill_id'");
$unit_info = mysqli_fetch_assoc($unit_q);
$unit_name = $unit_info['unit_name'] ?? '';
$unit_rent = $unit_info['rent'] ?? '';
$unit_advance = $unit_info['advance'] ?? '';
$unit_type = $unit_info['unit_type'] ?? '';

// tenent chart start
    $current_year = date('Y');
    // ১. ইনভয়েস থেকে মাস ভিত্তিক মোট বিল (Total Bill) বের করা
    $invoice_chart_sql = mysqli_query($db, "SELECT billing_month, SUM(total_amount) as monthly_total 
        FROM invoices 
        WHERE tenant_id = '$tenant_id' 
        AND unit_id = '$unit_id' 
        AND billing_month LIKE '$current_year-%' 
        GROUP BY billing_month");

    $invoice_chart_data = array_fill(1, 12, 0); // ১২ মাসের জন্য ডিফল্ট ০ সেট করা
    while ($inv_row = mysqli_fetch_assoc($invoice_chart_sql)) {
        // billing_month যদি '2026-05' বা '05' ফর্মে থাকে, সেখান থেকে মাসের নম্বর নেওয়া
        $month_num = (int)date('m', strtotime($inv_row['billing_month']));
        $invoice_chart_data[$month_num] = (float)$inv_row['monthly_total'];
    }

    // ২. পেমেন্ট হিস্ট্রি থেকে মাস ভিত্তিক মোট পেইড (Total Paid) বের করা
    $payment_chart_sql = mysqli_query($db, "SELECT inv.billing_month, SUM(ph.paid_amount) as monthly_paid 
        FROM payment_history ph 
        JOIN invoices inv ON ph.invoice_id = inv.id 
        WHERE ph.tenant_id = '$tenant_id' 
        AND inv.billing_month LIKE '$current_year-%' 
        GROUP BY inv.billing_month");

    $payment_chart_data = array_fill(1, 12, 0); // ১২ মাসের জন্য ডিফল্ট ০ সেট করা
    while ($pay_row = mysqli_fetch_assoc($payment_chart_sql)) {
        $month_num = (int)date('m', strtotime($pay_row['billing_month']));
        $payment_chart_data[$month_num] = (float)$pay_row['monthly_paid'];
    }

    // জাভাস্ক্রিপ্ট এ ব্যবহারের জন্য ডাটাকে কমা সেপারেটেড স্ট্রিং-এ কনভার্ট করা
    $chart_bills = implode(',', $invoice_chart_data);
    $chart_paids = implode(',', $payment_chart_data);
// tenent chart end



?>