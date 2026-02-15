<?php
if (isset($_GET['unit_id'])) {
    $unit_id = $_GET['unit_id'];
}

$query = "SELECT * FROM unit wHERE id = '$unit_id'";
$result = mysqli_query($db, $query);
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
}

$tent_sql = mysqli_query($db, "SELECT * FROM tenants WHERE building_id = '$building_name' AND unit_id = '$unit_id'");
while ($tent_row = mysqli_fetch_assoc($tent_sql)) {
    $tent_name = $tent_row['name'];
    $tent_id = $tent_row['id'];
}

// Advace Save SQL 
if (isset($_POST['advance_save'])) {
    $advance_pay_amount = $_POST['advance_amount'];

    $advance_add_sql = mysqli_query($db, "
            INSERT INTO `advance`
            (`tenant_id`, `unit_id`, `paid_amount`, `date`)
            VALUES ('$tent_id', '$unit_id', '$advance_pay_amount', NOW())
        ");

    if ($advance_add_sql) {
        header("Location: admin.php?page=editbill&unit_id=$unit_id");
        exit();
    }
}

// save_bill

if(isset($_POST['save_bill'])){
    $billing_month = $_POST['billing_month'];
    $total_amount  = $_POST['total_amount'];
    $paid_amount   = $_POST['paid_amount'];
    $status        = $_POST['status'];
    $due_amount    = $total_amount - $paid_amount;

    $bill_sql = mysqli_query($db,"INSERT INTO `invoices`
    (`tenant_id`, `unit_id`, `billing_month`, `total_amount`, `paid_amount`, `due_amount`, `status`, `created_at`) 
    VALUES 
    ('$tent_id','$unit_id','$billing_month','$total_amount','$paid_amount','$due_amount','$status',now())");

    if ($bill_sql) {
        header("Location: admin.php?page=editbill&unit_id=$unit_id");
        exit();
    }
}


?>

<div class="nxl-content">

    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <div class="page-header-left">
            <h5 class="m-b-10">
                Bills Manage
            </h5>
        </div>
        <div class="page-header-right">
            <a href="admin.php?page=bill" class="btn btn-primary">Back</a>
        </div>
    </div>

    <?= $message ?? '' ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="card-body px-3 general-info">

                            <!-- Unit Name -->
                            <div class="row">
                                <div class="col-lg-6">
                                    <?php
                                    // Total Advance Paid
                                    $total_paid = 0;

                                    $advance_sql = mysqli_query($db, "SELECT * FROM `advance` WHERE tenant_id = '$tent_id' AND unit_id = '$unit_id'");
                                    while ($advance_his = mysqli_fetch_assoc($advance_sql)) {
                                        $total_paid += $advance_his['paid_amount'];
                                    }

                                    // Remaining Payable Amount
                                    $payable = max($advance - $total_paid, 0); // avoid negative
                                    ?>

                                    <div class="card shadow-sm mb-3">
                                        <div class="card-body">
                                            <h6 class="fw-bold mb-2">Advance & Payment Summary</h6>

                                            <div class="mb-2">
                                                <span class="text-muted">Total Advance:</span>
                                                <span class="fw-semibold">৳ <?= number_format($advance, 2) ?></span>
                                            </div>

                                            <div class="mb-2">
                                                <span class="text-muted">Total Paid:</span>
                                                <span class="fw-semibold text-success">৳
                                                    <?= number_format($total_paid, 2) ?></span>
                                            </div>

                                            <div class="mb-3">
                                                <span class="text-muted">Remaining Payable:</span>
                                                <span class="fw-bold text-danger">৳
                                                    <?= number_format($payable, 2) ?></span>
                                            </div>

                                            <hr>

                                            <h6 class="fw-bold mb-2">Payment History</h6>
                                            <?php
                                            mysqli_data_seek($advance_sql, 0); // rewind result to loop again
                                            while ($advance_his = mysqli_fetch_assoc($advance_sql)):
                                                $add_pay_date = $advance_his['date'];
                                                $add_paid_amount = $advance_his['paid_amount'];
                                                ?>
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <small
                                                        class="text-muted"><?= date("d-M-Y h:i A", strtotime($add_pay_date)) ?></small>
                                                    <span class="text-success fw-semibold">৳
                                                        <?= number_format($add_paid_amount, 2) ?></span>
                                                </div>
                                            <?php endwhile; ?>

                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6">
                                    <input type="number" name="advance_amount" class="form-control mb-3"
                                        placeholder="Advance Amount" required>

                                    <button type="submit" name="advance_save" class="btn btn-success">
                                        Save
                                    </button>
                                </div>
                            </div>

                        </div>
                    </form>

                    <hr style="width: 75%;" class="mx-auto">

                    <form method="POST" enctype="multipart/form-data">
                        <div class="card-body px-3 general-info">

                            <!-- Unit Name -->
                            <div class="row">
                                <div class="col-lg-6">
                                    <h6 class="fw-bold mb-2">Bills & Payment Summary</h6>
                                    <div class="row">
                                        <div class="col-md-6">
                                            Rent = ৳ <?php echo $rent; ?><br>
                                            <?php if (!empty($Gas)) {
                                                echo 'Gas = ৳ ' . $Gas;
                                            } ?><br>
                                            <?php if (!empty($Water)) {
                                                echo 'Water = ৳ ' . $Water;
                                            } ?><br>
                                            <?php if (!empty($Electricity)) {
                                                echo 'Gas = ৳ ' . $Electricity;
                                            } ?><br>
                                            <?php if (!empty($Internet)) {
                                                echo 'Gas = ৳ ' . $Internet;
                                            } ?><br>
                                            <?php if (!empty($Others)) {
                                                echo 'Gas = ৳ ' . $Others;
                                            } ?><br>
                                            
                                             <?php $total_bill = $rent + $Gas + $Water + $Electricity + $Internet + $Others; ?>
                                             <span class="fw-bold text-primary">Total Bill = ৳ <?= $total_bill ?></span>
                                        </div>
                                        <?php 
                                            $pay_info = mysqli_query($db,"SELECT * FROM invoices WHERE tenant_id = '$tent_id' AND unit_id = '$unit_id' ORDER BY billing_month ");
                                            while($pay_info_row = mysqli_fetch_assoc($pay_info)){
                                                $billing_month_db = $pay_info_row['billing_month'];
                                                $paid_amount_db = $pay_info_row['paid_amount'];
                                                $due_amount_db = $pay_info_row['due_amount'];
                                            }
                                        ?>
                                        <div class="col-md-6">
                                            <div class="mb-2">
                                                <span class="text-muted">Bill Month :</span>
                                                <span>
                                                    <?= !empty($this_month) ? date("M Y", strtotime($this_month)) : '' ?>
                                                </span>
                                            </div>

                                            <div class="mb-2">
                                                <span class="text-muted">Total Paid :</span>
                                                <span class="fw-semibold text-success">
                                                    <?php
                                                    if (!empty($paid_amount_db)) {
                                                        echo '৳ ' . number_format($paid_amount_db, 2);
                                                    }
                                                    ?>
                                                </span>
                                            </div>

                                            <div>
                                                <?php
                                                // Prepare statement (prevents SQL injection)
                                                $stmt = $db->prepare("SELECT billing_month, due_amount 
                                                                    FROM invoices 
                                                                    WHERE tenant_id = ? 
                                                                    AND unit_id = ? 
                                                                    AND due_amount > 0 
                                                                    ORDER BY billing_month");

                                                $stmt->bind_param("ii", $tent_id, $unit_id); // change "ii" if IDs are not integers
                                                $stmt->execute();

                                                $result = $stmt->get_result();

                                                $total_due = 0;

                                                if ($result->num_rows > 0) {
                                                    while ($due_mon = $result->fetch_assoc()) {
                                                        $billing_month = date("M Y", strtotime($due_mon['billing_month']));
                                                        $due_amount = (float)$due_mon['due_amount'];
                                                        $total_due += $due_amount;
                                                        
                                                        echo '<span class="fw-bold text-danger">';
                                                        echo 'Due - ' . htmlspecialchars($billing_month) . ' = ৳ ' . number_format($due_amount, 2);
                                                        echo '</span><br>';
                                                    }
                                                } else {
                                                    echo '<span class="text-success">No Due Found</span><br>';
                                                }

                                                echo '<span class="fw-bold text-danger">Total Due = ৳ ' . number_format($total_due, 2) . '</span>';

                                                $stmt->close();
                                                ?>
                                            </div>
                                        </div>

                                    </div>
                                    
                                </div>

                                <div class="col-lg-6">
                                    <div>
                                        <label class="fw-semibold">Total Bill</label> 
                                        <input type="number" name="total_amount" value="<?= $total_bill ?>" class="form-control" required>
                                    </div>
                                    <div>
                                        <label class="fw-semibold">Pay Amount</label> 
                                        <input type="text" name="paid_amount" class="form-control" required>
                                    </div>
                                    <div>
                                        <label class="fw-semibold">Bill Month</label>
                                        <input type="month" name="billing_month"  value="<?php echo $this_month; ?>"  class="form-control" required>
                                    </div>
                                    <div>
                                        <label class="fw-semibold" for="status">Status</label>
                                        <select name="status" id="status" class="form-control form-select" required>
                                            <option selected disabled>Select One</option>
                                            <option value="Paid" <?php if($status == 'Paid'){ echo 'selected'; } ?>>Paid</option>
                                            <option value="Unpaid" <?php if($status == 'Unpaid'){ echo 'selected'; } ?>>Unpaid</option>
                                        </select>
                                    </div>
                                    <button type="submit" name="save_bill" class="btn btn-success mt-3">
                                        Save
                                    </button>
                                </div>
                            </div>

                        </div>
                    </form>

                    <div class="card mx-3 px-3">
                        <div class="mt-2">
                            <h6 class="fw-bold my-2">Monthly bills (invoice history)</h6>
                            <div class="d-flex mb-3 justify-content-between align-items-center mb-1">
                                <span>Bill Month</span>
                                <span>Total</span>
                                <span>Total</span>
                                <span>Due</span>
                            </div>
                            <?php
                            mysqli_data_seek($pay_info, 0); // rewind result to loop again
                            while ($pay_info_sh = mysqli_fetch_assoc($pay_info)):
                                    $billing_month_db = $pay_info_sh['billing_month'];
                                    $total_amount_db = $pay_info_sh['total_amount'];
                                    $paid_amount_db = $pay_info_sh['paid_amount'];
                                    $due_amount_db = $pay_info_sh['due_amount'];
                                    $created_at = $pay_info_sh['created_at'];
                                    $status = $pay_info_sh['status'];
                                ?>
                                
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span>
                                        <?= date("M Y", strtotime($billing_month_db)) ?>
                                    </span>
                                    <span class="text-success fw-semibold">
                                        ৳ <?=$total_amount_db ?>
                                    </span>
                                    <span class="text-success fw-semibold"> 
                                        ৳ <?= $paid_amount_db ?>
                                    </span>
                                    <span class="text-danger fw-semibold">
                                        ৳ <?= number_format($due_amount_db, 2) ?>
                                    </span>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>