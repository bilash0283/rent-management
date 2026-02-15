<?php
$query = "SELECT * FROM unit wHERE status = 'Rented' ORDER BY id DESC";
$result = mysqli_query($db, $query);

?>

<div class="nxl-content">

    <!-- Page Header -->
    <div class="page-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Bill Month (<?php echo date('M - Y') ?>)</h5>

        <!-- <a href="admin.php?page=CreateTenant" class="btn btn-primary">
            <i class="feather-plus me-1"></i> Create Bill
        </a> -->
    </div>

    <!-- Main Content -->
    <div class="main-content mt-4">
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
                                <th>Tenant</th>
                                <th>Advance</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($row = mysqli_fetch_assoc($result)) {
                                $unit_id = $row['id'];
                                $advance = $row['advance'];
                                $rent = $row['rent'];
                                $Gas = $row['Gas'];
                                $Water = $row['Water'];
                                $Electricity = $row['Electricity'];
                                $Internet = $row['Internet'];
                                $Maintenance = $row['Maintenance'];
                                $Others = $row['Others'];
                                $building_name = $row['building_name'];

                                $total_bill = $rent + $Gas + $Water + $Electricity + $Internet + $Others;

                                ?>
                                <tr>
                                    <td>
                                        <?php
                                        $sql_tenant = mysqli_query($db, "SELECT * FROM tenants WHERE building_id = '$building_name' AND unit_id = '$unit_id' ");
                                        while ($tent_row = mysqli_fetch_assoc($sql_tenant)) {
                                            $name = $tent_row['name'];
                                            $tent_id = $tent_row['id'];

                                            echo $name;
                                        }
                                        ?>
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
                                            echo '<span style="color: #0d6efd; font-weight: 600;">Advance = ৳ ' . $advance . '</span><br>';  // Blue
                                            echo '<span style="color: #198754; font-weight: 600;">Paid    = ৳ ' . $total_paid . '</span><br>'; // Green

                                            // Show Due only if payable > 0
                                            if ($payable > 0) {
                                                echo '<span style="color: #dc3545; font-weight: 600;">Due     = ৳ ' . $payable . '</span><br>'; // Red
                                            }
                                        ?>
                                    </td>

                                    <td>
                                        <span class="fw-semibold text-primary">
                                            Total = ৳ <?= number_format($total_bill, 2) ?? '' ?>
                                        </span><br>
                                        <?php
                                            $pay_info = mysqli_query($db,"SELECT * FROM invoices WHERE tenant_id = '$tent_id' AND unit_id = '$unit_id' ");
                                            if(!mysqli_num_rows($pay_info) > 0){
                                                echo '<span class="fw-bold text-danger">';
                                                echo 'Due = ৳ ' . number_format($total_bill, 2);
                                                echo '</span>';
                                            }else{
                                            mysqli_data_seek($pay_info, 0); // rewind result to loop again
                                            while ($pay_info_sh = mysqli_fetch_assoc($pay_info)){
                                                $billing_month_db = $pay_info_sh['billing_month'];
                                                $paid_amount_db = $pay_info_sh['paid_amount'];
                                                $due_amount_db = $pay_info_sh['due_amount'];
                                                $created_at = $pay_info_sh['created_at'];
                                                $status = $pay_info_sh['status'];
                                        ?>

                                        <?php if (!empty($paid_amount_db)) { ?>
                                            <span class="fw-semibold text-success">
                                                Paid = ৳ <?= number_format($paid_amount_db, 2) ?? '' ?>
                                            </span><br>
                                        <?php } ?>

                                        <?php if (!empty($due_amount_db)) { ?>
                                            <span class="fw-semibold text-danger">
                                                Due = ৳ <?= number_format($due_amount_db, 2) ?? '' ?>
                                            </span><br>
                                        <?php } } }?>

                                    </td>
                                    <td>
                                        <?php 
                                        if(!mysqli_num_rows($pay_info) > 0){
                                            echo "<button class='btn btn-sm btn-primary'>Pending</button>";
                                        }else{
                                        ?>
                                        <button class="btn btn-sm btn-<?php if($status == 'Paid'){ echo 'success'; }else if($status == 'Unpaid'){echo 'danger';} else if($status == 'Partial') { echo 'warning'; } ?>">
                                            <?= htmlspecialchars($status); ?>
                                        </button>
                                        <?php } ?>
                                    </td>
                                    
                                    <td>
                                        
                                        <div class="btn-group text-end">
                                            <!-- <button class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button> -->
                                            <!-- <button class="btn btn-sm btn-outline-success" title="view">
                                                <i class="bi bi-eye"></i>
                                            </button> -->
                                            <a href="admin.php?page=editbill&unit_id=<?= $unit_id ?>" class="text-end btn btn-sm btn-outline-success" title="Add Payment">Add Payment</a>
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