<?php
    if(isset($_GET['id'])){
        $building_id = $_GET['id'];
          // Fetch all units
        $query  = "SELECT * FROM unit WHERE building_name = '$building_id' AND status = 'Rented' ";
        $result = mysqli_query($db, $query);
        $totla_unit = mysqli_num_rows($result);
        if (!$result) {
            die("Query Failed: " . mysqli_error($db));
        }

        // fetch building 
        $buil_sql = mysqli_query($db,"SELECT * FROM `building` WHERE id = '$building_id' ");
        while($buli_name = mysqli_fetch_assoc($buil_sql)){
            $building_name_db = $buli_name['name'];
        }
    }
?>

<div class="nxl-content">
    <!-- Page Header -->
    <div class="page-header d-flex align-items-center justify-content-between">
       
        <h5 class="mb-0">
            <?= $building_name_db ?? ''; ?> <span style="background:#28a745;color:#fff;padding:6px 14px;border-radius:50px;font-size:13px;font-weight:500;display:inline-block;box-shadow:0 2px 6px rgba(0,0,0,0.15);"><?= $totla_unit ?? ''; ?></span> / Bill Month (<?php echo date('M - Y') ?>)
        </h5>
       
        <!-- <a href="admin.php?page=CreateTenant" class="btn btn-primary">
            <i class="feather-plus me-1"></i> Create Bill
        </a> -->
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="card shadow-sm">

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>SL</th>
                                <th>Unit</th>
                                <th>Tenant</th>
                                <th>Bill Amount</th>
                                <th>Status</th>
                                <th>Payment Method</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = '';
                            while ($row = mysqli_fetch_assoc($result)) {
                                $i++;
                                $unit_id = $row['id'];
                                $advance = $row['advance'];
                                $rent = $row['rent'];
                                $unit_name = $row['unit_name'];
                                $building_name = $row['building_name'];
                                $size = $row['size'];
                                ?>
                                <tr>
                                    <td><?= $i; ?></td>
                                    <td><?= $unit_name; ?></td>
                                    <td>
                                        <?php
                                        $sql_tenant = mysqli_query($db, "SELECT * FROM tenants WHERE building_id = '$building_name' AND unit_id = '$unit_id' ");
                                        while ($tent_row = mysqli_fetch_assoc($sql_tenant)) {
                                            $name = $tent_row['name'];
                                            $tent_id = $tent_row['id'];
                                            $image = !empty($tent_row['tenant_image'])
                                            ? "public/uploads/tenants/" . $tent_row['tenant_image']
                                            : "public/uploads/tenants/no-image.png";
                                        }
                                        ?>
                                        <div class="d-flex flex-column align-items-center text-center col-span">
                                            <img src="<?= htmlspecialchars($image) ?>"
                                                width="50" height="50"
                                                style="object-fit:cover;border-radius:50%;"
                                                class="mb-2">

                                            <a href="admin.php?page=view_tenant&id=<?= $tent_id ?>" 
                                            class="text-secondary fw-bold" style="font-size:12px;" >
                                                <?= $name; ?>
                                            </a>
                                            <?php if (!empty($size)): ?>
                                                <small class="text-muted">Ele.M.N : <?= $size; ?></small>
                                            <?php endif; ?>
                                        </div>                                        
                                    </td>

                                    <td style="font-size: 10px; line-height: 1.4;">
                                        <?php
                                        // Total Advance Paid
                                        $total_paid = 0;
                                        $advance_sql = mysqli_query($db, "SELECT * FROM `advance` WHERE tenant_id = '$tent_id' AND unit_id = '$unit_id'");
                                        while ($advance_his = mysqli_fetch_assoc($advance_sql)) {
                                            $total_paid += $advance_his['paid_amount'];
                                        }

                                        // Remaining Payable Amount
                                        $payable = max($advance - $total_paid, 0); // avoid negative
                                    
                                        // Show Advance and Paid
                                        // echo '<span style="color: #0d6efd; font-weight: 600;">Advance = ৳ ' . $advance . '</span><br>';  // Blue
                                        // echo '<span style="color: #198754; font-weight: 600;">Paid    = ৳ ' . $total_paid . '</span><br>'; // Green
                                    
                                        // Show Due only if payable > 0
                                        if ($payable > 0) {
                                            echo '<span style="color: #dc3545; font-weight: 600;">Advance     = <small> ৳ </small>' . $payable . '</span><br>'; // Red
                                        }
                                        ?>

                                        <?php 
                                            if(!empty($rent)){ ?>
                                                <span class="fw-semibold text-primary">
                                            Rent = ৳ <?= number_format($rent, 0) ?? '' ?>
                                            </span><br>
                                        <?php } ?> 

                                        <?php
                                        $pay_info = mysqli_query($db, "SELECT * FROM invoices WHERE tenant_id = '$tent_id' AND unit_id = '$unit_id' AND billing_month = '$this_month' ");
                                        if (!mysqli_num_rows($pay_info) > 0) {
                                            echo '<span class="fw-bold text-danger">';
                                            echo 'Due = ৳ ' . number_format($rent, 2);
                                            echo '</span>';
                                        } else {
                                            mysqli_data_seek($pay_info, 0); // rewind result to loop again
                                            while ($pay_info_sh = mysqli_fetch_assoc($pay_info)) {
                                                $billing_month_db = $pay_info_sh['billing_month'];
                                                $paid_amount_db = $pay_info_sh['paid_amount'];
                                                $due_amount_db = $pay_info_sh['due_amount'];
                                                $created_at = $pay_info_sh['created_at'];
                                                $status = $pay_info_sh['status'];

                                                $Gas = $pay_info_sh['Gas'];
                                                $Water = $pay_info_sh['Water'];
                                                $Electricity = $pay_info_sh['Electricity'];
                                                $Others = $pay_info_sh['Others'];

                                                $total_bill = $rent+$Gas+$Water+$Electricity+$Others;
                                                ?>     
                                                
                                                <?php 
                                                    if(!empty($Water)){ ?>
                                                        <span class="fw-semibold text-primary">
                                                    Water = <small>৳ </small> <?= number_format($Water, 0) ?? '' ?>
                                                    </span><br>
                                                <?php } ?> 

                                                <?php 
                                                    if(!empty($Gas)){ ?>
                                                        <span class="fw-semibold text-primary">
                                                    Gas = <small>৳ </small> <?= number_format($Gas, 0) ?? '' ?>
                                                    </span><br>
                                                <?php } ?> 

                                                <?php 
                                                    if(!empty($Electricity)){ ?>
                                                        <span class="fw-semibold text-primary">
                                                    Electricity = <small>৳ </small> <?= number_format($Electricity, 0) ?? '' ?>
                                                    </span><br>
                                                <?php } ?>

                                                <?php 
                                                    if(!empty($Others)){ ?>
                                                        <span class="fw-semibold text-primary">
                                                    Others = <small>৳ </small> <?= number_format($Others, 0) ?? '' ?>
                                                    </span><br>
                                                <?php } ?>

                                                <span class="fw-semibold text-primary">
                                                    Total = <small>৳ </small> <?= number_format($total_bill, 0) ?? '' ?>
                                                </span><br>

                                                <?php if (!empty($paid_amount_db)) { ?>
                                                    <span class="fw-semibold text-success">
                                                        Paid = <small>৳ </small> <?= number_format($paid_amount_db, 0) ?? '' ?>
                                                    </span><br>
                                                <?php } ?>

                                                <?php if (!empty($due_amount_db)) { ?>
                                                    <span class="fw-semibold text-danger">
                                                        Due = <small>৳ </small> <?= number_format($due_amount_db, 0) ?? '' ?>
                                                    </span><br>
                                                <?php }
                                            }
                                        } ?>
                                    </td>

                                    <td>
                                        <?php
                                        if (!mysqli_num_rows($pay_info) > 0) {
                                            echo "<button class='btn btn-sm btn-primary'>Pending</button>";
                                        } else {
                                            ?>
                                            <button
                                                class="btn btn-sm btn-<?php if ($status == 'Paid') {
                                                    echo 'success';
                                                } else if ($status == 'Unpaid') {
                                                    echo 'danger';
                                                } else if ($status == 'Partial') {
                                                    echo 'warning';
                                                } ?>">
                                                <?= htmlspecialchars($status); ?>
                                            </button>
                                        <?php } ?>
                                    </td>

                                    <td>
                                        <?php
                                        $history_sql = mysqli_query($db, "SELECT * FROM `payment_history` WHERE `tenant_id` = '$tent_id' AND bill_month = '$this_month' ");
                                        
                                        if (mysqli_num_rows($history_sql) == 0) {
                                            echo '<span class="fw-bold text-warning">Not Found</span>';
                                        } else {
                                            $manager_self_total = 0;
                                            $expense_total = 0;
                                            
                                            // তথ্য সংগ্রহের লুপ
                                            while ($pay_history = mysqli_fetch_assoc($history_sql)) {
                                                $pay_method_his = $pay_history['payment_method'];
                                                $expense_note = $pay_history['expense_note'];
                                                $transaction_id_db = $pay_history['transaction_id'];
                                                $manager_payment_method = $pay_history['manager_payment_method'];
                                                $manager_transaction_id = $pay_history['manager_transaction_id'];
                                                $transaction_date = $pay_history['transaction_date'];
                                                $transaction_number = $pay_history['transaction_number'];

                                                $manager_self_total += (float)$pay_history['manager_self'];  
                                                $expense_total      += (float)$pay_history['expense'];
                                            }

                                            // কন্টেইনার শুরু (গ্যাপ কমানোর জন্য CSS ব্যবহার করা হয়েছে)
                                            echo '<div style="display: flex; flex-direction: column; line-height: 1.2; gap: 1px;">';

                                                // পেমেন্ট মেথড
                                                echo "<small class='text-success fw-bold' style='font-size: 11px;'>$pay_method_his</small>";

                                                if ($manager_self_total > 0) {
                                                    echo "<small class='text-dark' style='font-size: 9px;'><b>Manager (Self):</b> " . number_format($manager_self_total, 0) . "</small>";
                                                }

                                                if ($expense_total > 0) {
                                                    echo "<small class='text-danger' style='font-size: 9px;'><b>Expense:</b> " . number_format($expense_total, 0) . "</small>";
                                                }

                                                if (!empty($expense_note)) {
                                                    echo "<small class='text-muted' style='font-size: 9px; font-style: italic;'>Note: " . htmlspecialchars($expense_note) . "</small>";
                                                }

                                                // ট্রানজেকশন ডাটা এরে
                                                $details = [
                                                    ['Txn ID', $transaction_id_db],
                                                    ['M. Txn ID', $manager_transaction_id],
                                                    ['M. Method', $manager_payment_method],
                                                    ['Date', $transaction_date],
                                                    ['Number', $transaction_number]
                                                ];

                                                foreach ($details as $detail) {
                                                    if (!empty($detail[1])) {
                                                        echo "<small style='font-size: 8.5px; color: #666;'>{$detail[0]}: {$detail[1]}</small>";
                                                    }
                                                }

                                            echo "</div>"; // কন্টেইনার শেষ
                                        }
                                        ?> 
                                    </td>

                                    <td>
                                        <div class="btn-group align-items-center">
                                            <!-- <button class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button> -->
                                            <a href="admin.php?page=editbill&unit_id=<?= $unit_id ?>"
                                                class="text-end btn btn-sm btn-info" title="Invoice Create & Payment">
                                                Details
                                            </a>
                                            <a href="admin.php?page=bill&unit_id=<?= $unit_id ?>&id=<?= $building_name ?>" onclick="sendWhatsApp()" class="btn btn-sm btn-success" title="Message Send with Copy">
                                                <i class="bi bi-send"></i>
                                            </a>

                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- whatsapp message send code  -->
<?php
if(isset($_GET['unit_id'])) {
    $unit_id = $_GET['unit_id'];

    $query = "SELECT * FROM unit wHERE id = '$unit_id'";
    $result = mysqli_query($db, $query);
    $building_name = $building_name_db ?? 0;
    while ($row = mysqli_fetch_assoc($result)) {
        $unit_id = $row['id'];
        $unit_name = $row['unit_name'];
        $advance = $row['advance'];
        $size = $row['size'];
        $rent_mess = $row['rent'];
        $water = $row['water'];
        $gas = $row['gas'];
        $building_name = $row['building_name'];
        $unit_type = $row['unit_type'];
        $Electricity_meter_no = $row['size'];
    }

    $building = mysqli_query($db, "SELECT name FROM building WHERE id = '$building_name' ");
    $building_row = mysqli_fetch_assoc($building);
    $building_name_db = $building_row['name'] ?? '';

    $tent_sql = mysqli_query($db, "SELECT id,name,phone FROM tenants WHERE building_id = '$building_name' AND unit_id = '$unit_id'");
    while ($tent_row = mysqli_fetch_assoc($tent_sql)) {
        $tent_name = $tent_row['name'];
        $tent_id = $tent_row['id'];
        $tent_phone = $tent_row['phone'];
    }

    $tent_id = $tent_id ?? 0; // default to 0 if not set
    $pay_info = mysqli_query($db, "SELECT * FROM invoices WHERE tenant_id = '$tent_id' AND unit_id = '$unit_id' AND billing_month = '$this_month' ");

    while ($pay_info_sh = mysqli_fetch_assoc($pay_info)) {
        $billing_month_db = $pay_info_sh['billing_month'];
        $paid_amount_db = $pay_info_sh['paid_amount'];
        $due_amount_db = $pay_info_sh['due_amount'];
        $created_at = $pay_info_sh['created_at'];
        $status = $pay_info_sh['status'];

        $Gas_mess = $pay_info_sh['Gas'];
        $Water_mess = $pay_info_sh['Water'];
        $Electricity_mess = $pay_info_sh['Electricity'];
        $Others_mess = $pay_info_sh['Others'];

        $Gas_month_db_mess = $pay_info_sh['Gas_month'];
        $Water_month_db_mess = $pay_info_sh['Water_month'];
        $Electricity_month_db_mess = $pay_info_sh['Electricity_month'];
        $Others_month_db_mess = $pay_info_sh['Others_month'];

        $total_bill_mess = $rent_mess+$Gas_mess+$Water_mess+$Electricity_mess+$Others_mess;
    }

    function formatMonth($date){
        return date("M-y", strtotime($date));
    }
}
?>
<script>
function sendWhatsApp() 
{
    <?php 
        $message = "$tent_name          INVOICE\n";
        $message .= "Flat No : $unit_name\n";
        $message .= "$building_name_db\n\n";

        // Rent
        if (!empty($rent_mess)) {
            $message .= "Rent (" . formatMonth($billing_month_db) . ")          ={$rent_mess}/-\n";
        }

        // Gas
        if (!empty($Gas_mess)) {
            $message .= "Gash (" . formatMonth($Gas_month_db_mess) . ")        ={$Gas_mess}/-\n";
        }

        // Electricity
        if (!empty($Electricity_mess)) {
            $message .= "Current (" . formatMonth($Electricity_month_db_mess) . ")    ={$Electricity_mess}/-\n";
        }

        // Water
        if (!empty($Water_mess)) {
            $message .= "Washa (" . formatMonth($Water_month_db_mess) . ")     ={$Water_mess}/-\n";
        }

        // Others
        if (!empty($Others_mess)) {
            $message .= "Others (" . formatMonth($Others_month_db_mess) . ")    ={$Others_mess}/-\n";
        }

        // Total
        $total_display = !empty($total_bill_mess) ? $total_bill_mess : $rent_mess;
        $message .= "TOTAL                   =" . $total_display . "/-";
    ?>
    let message = <?php echo json_encode($message); ?>;
    let phone = <?php echo json_encode($tent_phone); ?>;
    let redirectUrl = "admin.php?page=bill&unit_id=<?= $unit_id ?>&id=<?= $building_name ?>";

    // Copy
    navigator.clipboard.writeText(message);

    // Encode
    let encodedMessage = encodeURIComponent(message);

    // WhatsApp open
    let url = `https://wa.me/${phone}?text=${encodedMessage}`;
    window.open(url, '_blank');

    // ⏳ 1 second পরে redirect (important)
    setTimeout(function() {
        window.location.href = redirectUrl;
    }, 10);
}
</script>