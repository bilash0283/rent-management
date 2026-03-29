<?php
    if(isset($_GET['id'])){
        $building_id = $_GET['id'];
          // Fetch all units
        $query  = "SELECT * FROM unit WHERE building_name = '$building_id' AND status = 'Rented' ORDER BY id DESC";
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
            <?= $building_name_db ?? ''; ?> <span style="background:#28a745;color:#fff;padding:6px 14px;border-radius:50px;font-size:13px;font-weight:500;display:inline-block;box-shadow:0 2px 6px rgba(0,0,0,0.15);"><?= $totla_unit; ?></span> / Bill Month (<?php echo date('M - Y') ?>)
        </h5>
       
        <!-- <a href="admin.php?page=CreateTenant" class="btn btn-primary">
            <i class="feather-plus me-1"></i> Create Bill
        </a> -->
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">Bill List</h6>
                <?= $message ?? '' ?>
            </div>

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
                                <th>Method</th>
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
                                            class="text-secondary fw-bold">
                                                <?= $name; ?>
                                            </a>
                                        </div>                                        
                                    </td>

                                    <td>

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
                                            Rent = ৳ <?= number_format($rent, 2) ?? '' ?>
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
                                                    Water = <small>৳ </small> <?= number_format($Water, 2) ?? '' ?>
                                                    </span><br>
                                                <?php } ?> 

                                                <?php 
                                                    if(!empty($Gas)){ ?>
                                                        <span class="fw-semibold text-primary">
                                                    Gas = <small>৳ </small> <?= number_format($Gas, 2) ?? '' ?>
                                                    </span><br>
                                                <?php } ?> 

                                                <?php 
                                                    if(!empty($Electricity)){ ?>
                                                        <span class="fw-semibold text-primary">
                                                    Electricity = <small>৳ </small> <?= number_format($Electricity, 2) ?? '' ?>
                                                    </span><br>
                                                <?php } ?>

                                                <?php 
                                                    if(!empty($Others)){ ?>
                                                        <span class="fw-semibold text-primary">
                                                    Others = <small>৳ </small> <?= number_format($Others, 2) ?? '' ?>
                                                    </span><br>
                                                <?php } ?>

                                                <span class="fw-semibold text-primary">
                                                    Total = <small>৳ </small> <?= number_format($total_bill, 2) ?? '' ?>
                                                </span><br>

                                                <?php if (!empty($paid_amount_db)) { ?>
                                                    <span class="fw-semibold text-success">
                                                        Paid = <small>৳ </small> <?= number_format($paid_amount_db, 2) ?? '' ?>
                                                    </span><br>
                                                <?php } ?>

                                                <?php if (!empty($due_amount_db)) { ?>
                                                    <span class="fw-semibold text-danger">
                                                        Due = <small>৳ </small> <?= number_format($due_amount_db, 2) ?? '' ?>
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
                                            if (!mysqli_num_rows($history_sql) > 0) {
                                                echo '<span class="fw-bold text-warning">';
                                                echo 'Not Found';
                                                echo '</span>';
                                            } else {
                                                $manager_self_total = 0;
                                                $expense_total = 0;

                                                // প্রথমে লুপ চালিয়ে সব টোটাল বের করে নিবো
                                                while ($pay_history = mysqli_fetch_assoc($history_sql)) {
                                                    $bill_his        = $pay_history['bill_month'];
                                                    $pay_method_his  = $pay_history['payment_method'];
                                                    $total_his       = $pay_history['total'];
                                                    $paid_his        = $pay_history['paid'];
                                                    $due_his         = $pay_history['due'];
                                                    $note_his        = $pay_history['note'];
                                                    $pay_date_his    = $pay_history['payment_date'];
                                                    $paid_amount_his = $pay_history['paid_amount'];
                                                    $manager_self    = $pay_history['manager_self'];
                                                    $expense         = $pay_history['expense'];
                                                    $expense_note    = $pay_history['expense_note'];

                                                    $manager_self_total += (float)$manager_self;  
                                                    $expense_total      += (float)$expense;
                                                    
                                                   
                                                }

                                                echo "<small class='text-success fw-bold'>$pay_method_his</small><br>";

                                                if ($manager_self_total > 0) {
                                                    echo "<small class='text-warning fw-bold'>Manager (Self) Total: " . number_format($manager_self_total, 2) . "</small><br>";
                                                }

                                                if ($expense_total > 0) {
                                                    echo "<small class='text-warning fw-bold'>Expense Total: " . number_format($expense_total, 2) . "</small><br>";
                                                }

                                                echo "</div>";
                                            }
                                        ?> 
                                    </td>

                                    <td>
                                        <div class="btn-group align-items-center">
                                            <!-- <button class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button> -->
                                            <!-- <a class="btn btn-sm btn-outline-success" title="Invoice">
                                                <i class="bi bi-eye"></i>
                                            </a> -->
                                            <a href="admin.php?page=editbill&unit_id=<?= $unit_id ?>"
                                                class="text-end btn btn-sm btn-info" title="Add Payment">
                                                Payment
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