<?php
if (isset($_GET['unit_id'])) {
    $unit_id = $_GET['unit_id'];
}

$query = "SELECT * FROM unit wHERE id = '$unit_id'";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $unit_id = $row['id'];
    $unit_name = $row['unit_name'];
    $advance = $row['advance'];
    $rent = $row['rent'];
    $building_name = $row['building_name'];
    $unit_type = $row['unit_type'];
    $Electricity_meter_no = $row['size'];
}

$building = mysqli_query($db, "SELECT name FROM building WHERE id = '$building_name' ");
$building_row = mysqli_fetch_assoc($building);
$building_name_db = $building_row['name'];

$tent_sql = mysqli_query($db, "SELECT id,name FROM tenants WHERE building_id = '$building_name' AND unit_id = '$unit_id'");
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

// monthly payment sql 
$pay_info = mysqli_query($db, "SELECT * FROM invoices WHERE tenant_id = '$tent_id' AND unit_id = '$unit_id' ORDER BY billing_month ");

?>


<div class="nxl-content">

    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <div class="page-header-left">
            <h5 class="m-b-10">
                Bills Manage / <?php echo $building_name_db . '/' . $unit_name . '/' . $tent_name ?? '' ?>
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

                <!-- Advance manage  -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="fw-bold mb-2">Advance & Payment Summary</h6>
                    </div>
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
                </div>

                <!-- Monthly Bill & summary  -->
                <div class="card">
                    <div class="card-header d-flex justify-content-between">
                        <h6 class="fw-bold mb-2">Monthly Bills & Payment Summary</h6>
                        <a href="admin.php?page=invoice&unit_id=<?php echo $unit_id; ?>"
                            class="btn btn-sm btn-success ">
                            Invoice
                        </a>
                    </div>
                    <form method="POST" enctype="multipart/form-data">
                        <!-- Unit Name -->
                        <div class="row mx-1 mt-4">
                            <div class="col-lg-7 mx-auto">
                                <div class="card shadow-sm border-0" id="bill-card">
                                    <div
                                        class="border-bottom-0 pt-4 px-4 d-flex justify-content-between align-items-start border-bottom">
                                        <div>
                                            <h4 class="fw-bold mb-1 text-uppercase">
                                                <?php echo $building_name_db ?? 'Building Name'; ?>
                                            </h4>
                                        </div>

                                        <div>
                                            <h5 class="fw-bold text-primary mb-1">INVOICE</h5>
                                        </div>

                                        <div class="text-end">
                                            <small class="fw-semibold">Date : <?php echo date('d M Y'); ?></small>
                                        </div>
                                    </div>

                                    <div class="card-body px-4">
                                        <div class="row mb-3">
                                            <div class="col-7">
                                                <small class="text-muted d-block text-uppercase fw-semibold"
                                                    style="font-size: 0.7rem;">Tenant Name : <strong class="text-black"><?php echo $tent_name ?? 'N/A' ?></strong></small>
                                               
                                                <small class="text-muted"><?php echo $unit_type ?? 'Unit ' ?> : <strong class="text-black"><?php echo $unit_name ?? 'N/A' ?></strong> </small>
                                            </div>
                                            <div class="col-5 text-end">
                                                <small class="text-muted d-block text-uppercase fw-semibold"
                                                    style="font-size: 0.7rem;">Bill Month
                                                </small>
                                                <span class="badge bg-light text-dark border fw-semibold">
                                                    <?= !empty($this_month) ? date("M Y", strtotime($this_month)) : '' ?>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="table-responsive">
                                            <table class="table table-sm table-striped table-borderless align-middle mb-0"
                                                style="font-size: 0.85rem;">
                                                <!-- <thead class="border-bottom">
                                                    <tr>
                                                        <th class="py-2 text-muted">Description</th>
                                                        <th class="py-2 text-end text-muted">Amount</th>
                                                    </tr>
                                                </thead> -->
                                                <tbody >
                                                    <tr>
                                                        <td class="py-1">House Rent</td>
                                                        <td class="py-1 text-center"><?= !empty($this_month) ? date("M Y", strtotime($this_month)) : '' ?></td>
                                                        <td class="py-1 text-end">৳
                                                            <?php echo number_format($rent, 2); ?>
                                                        </td>
                                                    </tr>

                                                    <?php if (!empty($Electricity)): ?>
                                                        <tr>
                                                            <td class="py-1">Electricity Bill(<small><?= $Electricity_meter_no ?? '' ?></small>)</td>
                                                            <td></td>
                                                            <td class="py-1 text-end">৳
                                                                <?php echo number_format($Electricity, 2); ?>
                                                            </td>
                                                        </tr>
                                                    <?php endif; ?>

                                                    <?php if (!empty($Gas)): ?>
                                                        <tr>
                                                            <td class="py-1">Gas Bill</td>
                                                            <td class="py-1 text-center"><?= !empty($this_month) ? date("M Y", strtotime($this_month)) : '' ?></td>
                                                            <td class="py-1 text-end">৳
                                                                <?php echo number_format($Gas, 2); ?>
                                                            </td>
                                                        </tr>
                                                    <?php endif; ?>
                                                    <?php if (!empty($Water)): ?>
                                                        <tr>
                                                            <td class="py-1">Water Bill</td>
                                                            <td></td>
                                                            <td class="py-1 text-end">৳
                                                                <?php echo number_format($Water, 2); ?>
                                                            </td>
                                                        </tr>
                                                    <?php endif; ?>
                                                    
                                                    <?php if (!empty($Internet)): ?>
                                                        <tr>
                                                            <td class="py-1">Internet Bill</td>
                                                            <td></td>
                                                            <td class="py-1 text-end">৳
                                                                <?php echo number_format($Internet, 2); ?>
                                                            </td>
                                                        </tr>
                                                    <?php endif; ?>
                                                    <?php if (!empty($Others)): ?>
                                                        <tr>
                                                            <td class="py-1">Others Bill</td>
                                                            <td></td>
                                                            <td class="py-1 text-end">৳
                                                                <?php echo number_format($Others, 2); ?>
                                                            </td>
                                                        </tr>
                                                    <?php endif; ?>
                                                </tbody>
                                                <tfoot class="border-top">
                                                    <?php $total_bill = $rent; ?>
                                                    <tr class="table-light">
                                                        <td class="fw-bold py-2">Current Month Total = </td>
                                                        <td></td>
                                                        <td class="fw-bold py-2 text-end text-primary">৳
                                                            <?= number_format($total_bill, 2) ?>
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>

                                        <div class="mt-3 p-2 bg-light rounded">
                                            <?php
                                            $stmt = $db->prepare("SELECT billing_month, due_amount FROM invoices WHERE tenant_id = ? AND unit_id = ? AND due_amount > 0 ORDER BY billing_month");
                                            $stmt->bind_param("ii", $tent_id, $unit_id);
                                            $stmt->execute();
                                            $stmt->bind_result($month, $due);
                                            $total_due = 0;
                                            while ($stmt->fetch()) {
                                                $total_due += (float) $due;
                                                echo '<div class="d-flex justify-content-between" style="font-size: 0.8rem;">';
                                                echo '<span class="text-danger">Due (' . date("M Y", strtotime($month)) . ')</span>';
                                                echo '<span class="text-danger fw-semibold">৳ ' . number_format($due, 2) . '</span>';
                                                echo '</div>';
                                            }
                                            if ($payable > 0) {
                                                $total_due += $payable;
                                                echo '<div class="d-flex justify-content-between" style="font-size: 0.8rem;">';
                                                echo '<span class="text-danger">Advance Due</span>';
                                                echo '<span class="text-danger fw-semibold">৳ ' . number_format($payable, 2) . '</span>';
                                                echo '</div>';
                                            }
                                            $stmt->close();
                                            ?>

                                            <?php if ($total_due > 0): ?>
                                                <div class="d-flex justify-content-between border-top mt-1 pt-1">
                                                    <span class="small fw-bold text-black">Total Previous Due = </span>
                                                    <span class="small fw-bold text-primary">৳
                                                        <?= number_format($total_due, 2) ?>
                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>

                                        <div
                                            class="d-flex justify-content-between align-items-center mt-3 p-3 bg-primary text-white rounded shadow-sm">
                                            <span class="h6 mb-0 text-white">Total Payable = </span>
                                            <span class="h5 mb-0 fw-bold text-white">৳
                                                <?= number_format($total_bill + $total_due, 2) ?>
                                            </span>
                                        </div>

                                        <div class="mt-4 border-top">
                                            <p class="text-muted" style="font-size: 0.85rem;">
                                                Please pay within <strong>7th
                                                    <?php echo date("M Y", strtotime($this_month)); ?></strong> to
                                                following account &
                                                WhatsApp your deposit slip to <strong>01715482363</strong>.
                                            </p>
                                            <div class="card  border-0 p-3">
                                                <h6 class="mb-1 fw-bold">MD MUSTAFIZUR RAHMAN</h6>
                                                <div class="text-primary fw-bold" style="letter-spacing: 1px;">A/C:
                                                    1503101624157001</div>
                                                <small class="text-muted">BRACK BANK LTD | Moghbazar Branch</small>
                                            </div>
                                        </div>

                                        <div class="alert alert-warning mb-0 text-center" style="font-size: 0.8rem;">
                                            <i class="fas fa-exclamation-triangle me-1"></i> সিড়িতে ও দরজার সামনে জুতা,
                                            ময়লা রাখা সম্পূর্ণ নিষিদ্ধ।
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-5">
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
                                            <input type="date" class="form-control" name="payment_date"
                                                value="<?= date('Y-m-d'); ?>" required>
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
                    </form>
                </div>

                <!-- bill summary  -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="fw-bold my-2">Monthly bills (invoice history)</h6>
                    </div>
                    <div class="card-body">
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
                                            <td class="text-end text-primary fw-bold">
                                                <small>৳</small> <?= number_format($total_amount_db, 2) ?>
                                            </td>
                                            <td class="text-end text-success fw-bold">
                                                <small>৳</small> <?= number_format($paid_amount_db, 2) ?>
                                            </td>
                                            <td class="text-end text-danger fw-bold">
                                                <small>৳</small> <?= number_format($due_amount_db, 2) ?>
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
                <div class="card">
                    <div class="card-header">
                        <h6 class="fw-bold my-2">Payment history </h6>
                    </div>
                    <div class="card-body">
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
                                            <td class="text-end text-success fw-semibold"><small>৳</small>
                                                <?= $paid_amount_his ?></td>
                                            <td class="text-end fw-semibold">
                                                <span class="text-primary"><small>৳</small> <?= $total_his ?></span> <br>
                                                <span class="text-success"><small>৳</small> <?= $paid_his ?></span> <br>
                                                <span class="text-danger"><small>৳</small> <?= $due_his ?></span>
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