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

    function formatMonth($date){
        return !empty($date) ? date("M-y", strtotime($date)) : '';
    }
?>

<div class="nxl-content">
    <div class="page-header d-flex align-items-center justify-content-between">
       
        <strong class="mb-0 text-black">
            <?= $building_name_db ?? ''; ?> <span class="bg-info" style="color:#fff;padding:4px 5px;border-radius:50px;font-size:8px;font-weight:500;display:inline-block;box-shadow:0 2px 6px rgba(0,0,0,0.15);"><?= $totla_unit ?? ''; ?></span> / Bill Month <small>(<?php echo date('M - Y') ?>)</small>
        </strong>
       
        </div>

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
                            $i = 0;
                            while ($row = mysqli_fetch_assoc($result)) {
                                $i++;
                                $unit_id = $row['id'];
                                $advance = $row['advance'];
                                $rent = $row['rent'];
                                $unit_name = $row['unit_name'];
                                $building_name = $row['building_name'];
                                $size = $row['size'];
                                
                                // Reset tenant variables for this loop iteration
                                $name = '';
                                $tent_id = 0;
                                $tent_phone = '';
                                $image = "public/uploads/tenants/no-image.png";

                                $sql_tenant = mysqli_query($db, "SELECT * FROM tenants WHERE role IN ('Tenant') AND building_id = '$building_name' AND unit_id = '$unit_id' AND status = 'Active' ");
                                while ($tent_row = mysqli_fetch_assoc($sql_tenant)) {
                                    $name = $tent_row['name'];
                                    $tent_id = $tent_row['id'];
                                    $tent_phone = $tent_row['phone'];
                                    if(!empty($tent_row['tenant_image'])) {
                                        $image = "public/uploads/tenants/" . $tent_row['tenant_image'];
                                    }
                                }
                                ?>
                                <tr>
                                    <td><?= $i; ?></td>
                                    <td><?= $unit_name; ?></td>
                                    <td>
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
                                    
                                        // Show Due only if payable > 0
                                        if ($payable > 0) {
                                            echo '<span style="color: #dc3545; font-weight: 600;">Advance      = <small> ৳ </small>' . $payable . '</span><br>'; // Red
                                        }
                                        ?>

                                        <?php
                                        // Dynamic Invoice Message Setup for WhatsApp Button
                                        $Gas_mess = 0; $Water_mess = 0; $Electricity_mess = 0; $Others_mess = 0;
                                        $billing_month_db = ''; $Gas_month_db_mess = ''; $Water_month_db_mess = ''; $Electricity_month_db_mess = ''; $Others_month_db_mess = '';
                                        $total_bill_mess = $rent;

                                        $pay_info = mysqli_query($db, "SELECT * FROM invoices WHERE tenant_id = '$tent_id' AND unit_id = '$unit_id' AND billing_month = '$this_month' ");
                                        if (!mysqli_num_rows($pay_info) > 0) {
                                            echo '<span class="fw-bold text-danger">';
                                            echo 'Due = ৳ ' . number_format($rent, 2);
                                            echo '</span>';
                                            $status = 'Unpaid';
                                        } else {
                                            mysqli_data_seek($pay_info, 0); 
                                            while ($pay_info_sh = mysqli_fetch_assoc($pay_info)) {
                                                $billing_month_db = $pay_info_sh['billing_month'];
                                                $total_amount_db = $pay_info_sh['total_amount'];
                                                $paid_amount_db = $pay_info_sh['paid_amount'];
                                                $due_amount_db = $total_amount_db-$paid_amount_db;
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

                                                $total_bill_mess = $rent + $Gas_mess + $Water_mess + $Electricity_mess + $Others_mess;
                                                ?>     
                                                
                                                <span class="fw-semibold text-primary">
                                                    Total = <small>৳ </small> <?= number_format($total_amount_db, 0) ?? '' ?>
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
                                        } 

                                        // Creating dynamic WhatsApp message format for this row
                                        $msg = "$name          INVOICE\n";
                                        $msg .= "Flat No : $unit_name\n";
                                        $msg .= "$building_name_db\n\n";

                                        if (!empty($rent)) {
                                            $msg .= "Rent (" . formatMonth($billing_month_db ?: $this_month) . ")           ={$rent}/-\n";
                                        }
                                        if (!empty($Gas_mess)) {
                                            $msg .= "Gash (" . formatMonth($Gas_month_db_mess) . ")        ={$Gas_mess}/-\n";
                                        }
                                        if (!empty($Electricity_mess)) {
                                            $msg .= "Current (" . formatMonth($Electricity_month_db_mess) . ")    ={$Electricity_mess}/-\n";
                                        }
                                        if (!empty($Water_mess)) {
                                            $msg .= "Washa (" . formatMonth($Water_month_db_mess) . ")     ={$Water_mess}/-\n";
                                        }
                                        if (!empty($Others_mess)) {
                                            $msg .= "Others (" . formatMonth($Others_month_db_mess) . ")    ={$Others_mess}/-\n";
                                        }
                                        $msg .= "TOTAL                   =" . $total_bill_mess . "/-";
                                        $json_message = json_encode($msg);
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        if (!mysqli_num_rows($pay_info) > 0) {
                                            echo "<button class='p-1 btn btn-sm btn-secondary'>No Invoice</button>";
                                        } else {
                                            ?>
                                            <button
                                                class="p-1 btn btn-sm btn-<?php if ($status == 'Paid') {
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
                                            echo '<span class="fw-bold text-warning" style="font-size:10px;">Not Found</span>';
                                        } else {
                                            $manager_paid_total = 0;
                                            $manager_self = 0;
                                            $pay_method_his = ''; $transaction_id_db = ''; $manager_payment_method = ''; $transaction_date = ''; $transaction_number = '';
                                            
                                            while ($pay_history = mysqli_fetch_assoc($history_sql)) {
                                                $pay_method_his = $pay_history['payment_method'];
                                                $transaction_id_db = $pay_history['transaction_id'];
                                                $manager_payment_method = $pay_history['manager_payment_method'];
                                                $transaction_date = $pay_history['payment_date'];
                                                $transaction_number = $pay_history['transaction_number'];
                                            }

                                            $manager_acount_sql = mysqli_query($db, "SELECT * FROM `payment_history` WHERE `tenant_id` = '$tent_id' AND bill_month = '$this_month' AND payment_method = 'Manager' ");
                                            while($manger_ac = mysqli_fetch_assoc($manager_acount_sql)){
                                                $paid_amu = $manger_ac['paid_amount'];
                                                $manager_paid_total += (float)$manger_ac['manager_paid'];  
                                                $manager_self = (float)$paid_amu-$manager_paid_total;
                                            }

                                            echo '<div style="display: flex; flex-direction: column; line-height: 1.2; gap: 1px;">';
                                                echo "<small class='text-success fw-bold' style='font-size: 11px;'>$pay_method_his</small>";

                                                if ($manager_paid_total > 0) {
                                                    echo "<small class='text-dark' style='font-size: 9px;'><b>Manager (Paid):   ৳</b> " . number_format($manager_paid_total, 0) . "</small>";
                                                }
                                                if ($manager_self > 0) {
                                                    echo "<small class='text-danger' style='font-size: 9px;'><b>Manager (Self):   ৳</b> " . number_format($manager_self, 0) . "</small>";
                                                }

                                                $details = [
                                                    ['Txn ID', $transaction_id_db],
                                                    ['Payment Method', $manager_payment_method],
                                                    ['Txn Number', $transaction_number],
                                                    ['Date', $transaction_date]
                                                ];

                                                foreach ($details as $detail) {
                                                    if (!empty($detail[1])) {
                                                        echo "<small style='font-size: 8.5px; color: #666;'>{$detail[0]}: {$detail[1]}</small>";
                                                    }
                                                }
                                            echo "</div>"; 
                                        }
                                        ?> 
                                    </td>

                                    <td>
                                        <div class="btn-group align-items-center">
                                            <a href="admin.php?page=editbill&tenant_id=<?= $tent_id ?>"
                                                class="text-end p-1 btn btn-sm btn-info" title="Invoice Create & Payment">
                                                Details
                                            </a>
                                            <a href="javascript:void(0);" onclick='sendWhatsApp(<?= $json_message ?>, "<?= $tent_phone ?>", "admin.php?page=bill&unit_id=<?= $unit_id ?>&id=<?= $building_name ?>")' class="p-1 btn btn-sm btn-success" title="Message Send with Copy">
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

<script>
function sendWhatsApp(message, phone, redirectUrl) 
{
    // Copy to clipboard
    if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(message);
    } else {
        let textarea = document.createElement("textarea");
        textarea.value = message;
        document.body.appendChild(textarea);
        textarea.select();
        document.execCommand("copy");
        document.body.removeChild(textarea);
    }

    // Encode Message
    let encodedMessage = encodeURIComponent(message);

    // WhatsApp url
    let url = `https://wa.me/${phone}?text=${encodedMessage}`;
    window.open(url, '_blank');

    // Redirect
    setTimeout(function() {
        window.location.href = redirectUrl;
    }, 500); // 500ms delay ensures window.open runs smoothly
}
</script>