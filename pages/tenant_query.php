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

?>