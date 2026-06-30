<?php
// tenant info 
$tenant_id = $_SESSION['id'];
$tenant_q = mysqli_query($db, "SELECT * FROM tenants WHERE id = '$tenant_id'");
$tenant_info = mysqli_fetch_assoc($tenant_q);
$buill_id = $tenant_info['building_id'];
$unit_id = $tenant_info['unit_id'];
$status = $tenant_info['status'];
$tenant_name = $tenant_info['name'];

//building info 
$buliding_q = mysqli_query($db, "SELECT * FROM building WHERE id = '$buill_id'");
$building_info = mysqli_fetch_assoc($buliding_q);
$building_name = $building_info['name'];

//unit info
$unit_q = mysqli_query($db, "SELECT * FROM unit WHERE id = '$unit_id' AND building_name = '$buill_id'");
$unit_info = mysqli_fetch_assoc($unit_q);
$unit_name = $unit_info['unit_name'];

//advance info
$advance_q = mysqli_

?>