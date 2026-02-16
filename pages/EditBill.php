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

// save_Invoice
if (isset($_POST['save_bill'])) {

    $billing_month = $_POST['billing_month'];
    $total_amount = intval($_POST['total_amount']);
    $paid_amount = intval($_POST['paid_amount']);
    $status = $_POST['status'];
    $note = $_POST['note'];
    $due_amount = $total_amount - $paid_amount;
    $payment_date = !empty($_POST['payment_date']) ? $_POST['payment_date'] : date('Y-m-d');
    $payment_method = $_POST['payment_method'];

    $month_sql = mysqli_query($db, "SELECT * FROM invoices WHERE billing_month = '$billing_month' AND tenant_id = '$tent_id' LIMIT 1 ");
    while ($ex_month_row = mysqli_fetch_assoc($month_sql)) {
        $id_db = $ex_month_row['id'];
        $old_total = intval($ex_month_row['total_amount']);
        $old_paid = intval($ex_month_row['paid_amount']);
    }
    $update_paid_amount = $old_paid + $paid_amount;
    $update_due_amount = $old_total - $update_paid_amount;

    if (mysqli_num_rows($month_sql) > 0) {
        $bill_sql = mysqli_query($db, "UPDATE invoices SET paid_amount= '$update_paid_amount', due_amount = '$update_due_amount', status='$status',note ='$note' WHERE id = '$id_db' AND tenant_id = '$tent_id' ");

        $bill_history = mysqli_query($db, "INSERT INTO payment_history(`tenant_id`, `bill_month`, `payment_method`, `total`, `paid`, `paid_amount`, `due`, `note`, `payment_date`) VALUES ('$tent_id','$billing_month','$payment_method','$old_total','$update_paid_amount','$paid_amount','$update_due_amount','$note','$payment_date')");
    } else {
        $bill_sql = mysqli_query($db, "INSERT INTO `invoices`
        (`tenant_id`, `unit_id`, `billing_month`, `total_amount`, `paid_amount`, `due_amount`, `status`, `created_at`) 
        VALUES 
        ('$tent_id','$unit_id','$billing_month','$total_amount','$paid_amount','$due_amount','$status',now())");

        $bill_history = mysqli_query($db, "INSERT INTO payment_history(`tenant_id`, `bill_month`, `payment_method`, `total`, `paid`, `paid_amount`, `due`, `note`, `payment_date`) VALUES ('$tent_id','$billing_month','$payment_method','$total_amount','$paid_amount','$paid_amount','$due_amount','$note','$payment_date')");
    }

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
                                    <div class="card p-3">
                                        <div>
                                            <label for="advance_amount">Advance Amount *</label>
                                            <input type="number" name="advance_amount" class="form-control mb-3"
                                            placeholder="Advance Amount" required>
                                        </div>

                                        <button type="submit" name="advance_save" class="btn btn-success btn-sm">
                                            Save Advance
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </form>

                    <form method="POST" enctype="multipart/form-data">
                        <div class="card-body general-info">
                            <!-- Unit Name -->
                            <div class="row">
                                <div class="col-lg-6">
                                    <div class="card p-3">
                                        <h6 class="fw-bold mb-2">Monthly Bills & Payment Summary</h6>
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
                                            $pay_info = mysqli_query($db, "SELECT * FROM invoices WHERE tenant_id = '$tent_id' AND unit_id = '$unit_id' ORDER BY billing_month ");
                                            while ($pay_info_row = mysqli_fetch_assoc($pay_info)) {
                                                $billing_month_db = $pay_info_row['billing_month'];
                                                $paid_amount_db = $pay_info_row['paid_amount'];
                                                $due_amount_db = $pay_info_row['due_amount'];
                                                $status = $pay_info_row['status'];
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
                                                            $due_amount = (float) $due_mon['due_amount'];
                                                            $total_due += $due_amount;
                                                            echo '<span class="fw-bold text-danger">';
                                                            echo 'Due - ' . htmlspecialchars($billing_month) . ' = ৳ ' . number_format($due_amount, 2);
                                                            echo '</span><br>';
                                                        }
                                                    } else {
                                                        echo '<span class="text-success">No Due Found</span><br>';
                                                    }

                                                    if ($total_due > 0) {
                                                        echo '<span class="fw-bold text-danger">Total Due = ৳ ' . number_format($total_due, 2) . '</span>';

                                                        $stmt->close();
                                                    }
                                                    ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-lg-6">
                                    <div class="card p-3">
                                        <input type="number" hidden name="total_amount" value="<?php echo $total_bill; ?>">
                                        <div>
                                            <label class="fw-semibold">Amount *</label>
                                            <input type="number" name="paid_amount" class="form-control" required>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label class="fw-semibold">For Month *<small
                                                        class="text-warning">(Invoice)</small></label>
                                                <input type="month" name="billing_month" value="<?php echo $this_month; ?>"
                                                    class="form-control" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="fw-semibold" for="status">Status *</label>
                                                <select name="status" id="status" class="form-control form-select" required>
                                                    <option selected disabled>Select One</option>
                                                    <option value="Paid">Paid</option>
                                                    <option value="Unpaid">Unpaid</option>
                                                    <option value="Partial">Partial</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label for="payment_date">Payment date *</label>
                                                <input type="date" class="form-control" name="payment_date" value="<?= date('Y-m-d'); ?>" required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="payment_method">Payment Method *</label>
                                                <select name="payment_method" id="" class="form-control form-select"
                                                    required>
                                                    <option selected disabled>Select One</option>
                                                    <option value="Cash">Cash</option>
                                                    <option value="Bkash">Bkash</option>
                                                    <option value="Nagad">Nagad</option>
                                                    <option value="Back Transfer">Back Transfer</option>
                                                    <option value="Card">Card</option>
                                                    <option value="Manager">Manager</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div>
                                            <label class="fw-semibold">Note</label>
                                            <input type="text" name="note" class="form-control">
                                        </div>
                                        <button type="submit" name="save_bill" class="btn btn-success btn-sm mt-3">
                                            Save Payment
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>

                    <!-- bill summary  -->
                    <div class="card mx-3 p-3">
                        <div class="mt-2">
                            <h6 class="fw-bold my-2">Monthly bills (invoice history)</h6>
                            <div class="table-responsive mt-3">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">Bill Month</th>
                                            <th scope="col" class="text-end">Total</th>
                                            <th scope="col" class="text-end">Paid</th>
                                            <th scope="col" class="text-end">Due</th>
                                            <th scope="col" class="text-center">Status</th>
                                            <th scope="col" class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        mysqli_data_seek($pay_info, 0); // rewind result
                                        while ($pay_info_sh = mysqli_fetch_assoc($pay_info)):
                                            $billing_month_db = $pay_info_sh['billing_month'];
                                            $total_amount_db = $pay_info_sh['total_amount'];
                                            $paid_amount_db = $pay_info_sh['paid_amount'];
                                            $due_amount_db = $pay_info_sh['due_amount'];
                                            $status = $pay_info_sh['status'];
                                            ?>
                                            <tr class="mb-1">
                                                <td class="fw-bold text-secondary">
                                                    <?= date("M Y", strtotime($billing_month_db)) ?>
                                                </td>
                                                <td class="text-end text-dark">
                                                    ৳ <?= number_format($total_amount_db, 2) ?>
                                                </td>
                                                <td class="text-end text-success fw-semibold">
                                                    ৳ <?= number_format($paid_amount_db, 2) ?>
                                                </td>
                                                <td class="text-end text-danger fw-bold">
                                                    ৳ <?= number_format($due_amount_db, 2) ?>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($status == 'Paid'): ?>
                                                        <small class="bg-success text-white p-1 rounded-2">Paid</small>
                                                    <?php elseif ($status == 'Unpaid'): ?>
                                                        <small class="bg-danger text-white p-1 rounded-2">Pending</small>
                                                    <?php elseif ($status == 'Partial'): ?>
                                                        <small class="bg-warning text-white p-1 rounded-2">Partial</small>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center pe-4">
                                                    <div class="btn-group">
                                                        <!-- <button class="btn btn-sm btn-outline-primary" title="Edit">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </button> -->
                                                        <a href="admin.php?page=invoice&id=<?php echo $tent_id; ?>"
                                                            class="btn btn-sm btn-outline-success" title="view">
                                                            <i class="bi bi-eye"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- payment History  -->
                    <div class="card mx-3 p-3">
                        <div class="mt-2">
                            <h6 class="fw-bold my-2">Payment history </h6>
                            <div class="table-responsive mt-3">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" class="ps-4">Date</th>
                                            <th scope="col" class="text-end">Bill Month</th>
                                            <th scope="col" class="text-end">Payment Method</th>
                                            <th scope="col" class="text-end">Payment Amount</th>
                                            <th scope="col" class="text-end">Bill Summary</th>
                                            <th scope="col" class="text-center">Note</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $history_sql = mysqli_query($db, "SELECT * FROM `payment_history` WHERE `tenant_id` = '$tent_id' ");

                                        while ($pay_history = mysqli_fetch_assoc($history_sql)) {
                                            $bill_his = $pay_history['bill_month'];
                                            $pay_method_his = $pay_history['payment_method'];
                                            $total_his = $pay_history['total'];
                                            $paid_his = $pay_history['paid'];
                                            $due_his = $pay_history['due'];
                                            $note_his = $pay_history['note'];
                                            $pay_date_his = $pay_history['payment_date'];
                                            $paid_amount_his = $pay_history['paid_amount'];
                                            ?>
                                            <tr>
                                                <td class="ps-4 fw-medium"><?= date('d M Y', strtotime($pay_date_his)) ?>
                                                </td>
                                                <td class="text-end fw-semibold f-w-bold text-uppercase text-secendary">
                                                    <?= date(' M Y', strtotime($bill_his)) ?>
                                                </td>
                                                <td class="text-end text-secendary fw-semibold"><?= $pay_method_his ?></td>
                                                <td class="text-end text-success fw-semibold"><?= $paid_amount_his ?></td>
                                                <td class="text-end fw-semibold">
                                                    <span class="text-primary">৳ <?= $total_his ?></span> <br>
                                                    <span class="text-success">৳ <?= $paid_his ?></span> <br>
                                                    <span class="text-danger">৳ <?= $due_his ?></span>
                                                </td>
                                                <td class="text-center pe-4">
                                                    <small class="text-secendary"><?= $note_his ?></small>
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
        </div>
    </div>
</div>