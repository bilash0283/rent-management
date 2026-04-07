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
    $size = $row['size'];
    $rent = $row['rent'];
    $water = $row['water'];
    $gas = $row['gas'];
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

// Create Invoice
if (isset($_POST['create_invoice'])) {

    $billing_month = $this_month;
    $status = 'Unpaid';
    $Gas = intval($_POST['Gas']);
    $Water = intval($_POST['Water']);
    $Electricity = intval($_POST['Electricity']);
    $Others = intval($_POST['Others']);
    $Gas_month = $_POST['Gas_month'];
    $Water_month = $_POST['Water_month'];
    $Electricity_month = $_POST['Electricity_month'];
    $Others_month = $_POST['Others_month'];

    $total_amount = $rent + $Gas + $Water + $Electricity + $Others;


    $month_sql = mysqli_query($db, "SELECT * FROM invoices WHERE billing_month = '$billing_month' AND tenant_id = '$tent_id' LIMIT 1 ");
    while ($ex_month_row = mysqli_fetch_assoc($month_sql)) {
        $id_db = $ex_month_row['id'];
        $old_total = intval($ex_month_row['total_amount']);
        $old_paid = intval($ex_month_row['paid_amount']);
    }

    if (mysqli_num_rows($month_sql) > 0) {
        $bill_sql = mysqli_query($db, "UPDATE `invoices` SET 
                `tenant_id` = '$tent_id',
                `unit_id` = '$unit_id',
                `billing_month` = '$billing_month',
                `gas` = '$Gas',
                `gas_month` = '$Gas_month',
                `water` = '$Water',
                `water_month` = '$Water_month',
                `electricity` = '$Electricity',
                `electricity_month` = '$Electricity_month',
                `others` = '$Others',
                `others_month` = '$Others_month',
                `total_amount` = '$total_amount',
                `due_amount` = '$total_amount',
                `status` = '$status'
            WHERE `id` = '$id_db' 
            AND `tenant_id` = '$tent_id'
            ");

        // $bill_history = mysqli_query($db, "INSERT INTO payment_history(`tenant_id`, `bill_month`, `payment_method`, `total`, `paid`, `paid_amount`, `due`, `note`, `payment_date`) VALUES ('$tent_id','$billing_month','$payment_method','$old_total','$update_paid_amount','$paid_amount','$update_due_amount','$note','$payment_date')");
    } else {
        $bill_sql = mysqli_query($db, "INSERT INTO `invoices`
            (
                `tenant_id`,
                `unit_id`,
                `billing_month`,
                `gas`,
                `gas_month`,
                `water`,
                `water_month`,
                `electricity`,
                `electricity_month`,
                `others`,
                `others_month`,
                `total_amount`,
                `due_amount`,
                `status`,
                `created_at`
            ) 
            VALUES 
            (
                '$tent_id',
                '$unit_id',
                '$billing_month',
                '$Gas',
                '$Gas_month',
                '$Water',
                '$Water_month',
                '$Electricity',
                '$Electricity_month',
                '$Others',
                '$Others_month',
                '$total_amount',
                '$total_amount',
                '$status',
                now()
            )");

        // $bill_history = mysqli_query($db, "INSERT INTO payment_history(`tenant_id`, `bill_month`, `payment_method`, `total`, `paid`, `paid_amount`, `due`, `note`, `payment_date`) VALUES ('$tent_id','$billing_month','$payment_method','$total_amount','$paid_amount','$paid_amount','$due_amount','$note','$payment_date')");
    }

    if ($bill_sql) {
        header("Location: admin.php?page=editbill&unit_id=$unit_id");
        exit();
    }
}


// ==================== Confirm Payment - Fixed & Clean Version ====================

if (isset($_POST['save_bill'])) {

    // ==================== Get and Sanitize Input ====================
    $billing_month          = trim($_POST['billing_month'] ?? '');
    $total_amount           = (int) ($_POST['total_amount'] ?? 0);        // reference only
    $paid_amount            = (int) ($_POST['paid_amount'] ?? 0);
    $note                   = trim($_POST['note'] ?? '');
    $payment_date           = date('Y-m-d');                              // Today's date
    $payment_method         = trim($_POST['payment_method'] ?? '');
    $expense                = (int) ($_POST['expense'] ?? 0);
    $expense_note           = trim($_POST['expense_note'] ?? '');
    $transaction_id         = trim($_POST['transaction_id'] ?? '') ?: NULL;

    $manager_payment        = (int) ($_POST['manager_payment'] ?? 0);
    $manager_payment_method = trim($_POST['manager_payment_method'] ?? '');
    $manager_transaction_id = trim($_POST['manager_transaction_id'] ?? '');

    $tren_date              = trim($_POST['transaction_date'] ?? '');
    $transaction_date       = $tren_date ? date('Y-m-d H:i:s', strtotime($tren_date)) : date('Y-m-d H:i:s');
    $transaction_number     = trim($_POST['transaction_number'] ?? '');

    $manager_self = 0;
    $errors = [];

    // ==================== Validation ====================

    if (empty($payment_method)) {
        $errors[] = "Please select a Payment Method.";
    }

    if ($payment_method === 'Manager') {
        // Manager Payment Logic
        if ($paid_amount < 0 || $manager_payment < 0 || $expense < 0) {
            $errors[] = "Paid amount, Manager payment and Expense cannot be negative.";
        }

        if ($paid_amount <= 0) {
            $errors[] = "Paid amount must be greater than 0.";
        }

        $calculated_self = $paid_amount - $manager_payment - $expense;

        if ($calculated_self < 0) {
            $errors[] = "Manager payment + Expense cannot exceed Paid Amount.";
        } else {
            $manager_self = $calculated_self;
        }

        // Final strict check
        if (($manager_payment + $expense + $manager_self) !== $paid_amount) {
            $errors[] = "Payment distribution error!\n\n" .
                        "Paid Amount = $paid_amount\n" .
                        "Manager Payment = $manager_payment\n" .
                        "Expense = $expense\n" .
                        "Manager Self = $manager_self";
        }
    } 
    else {
        // Normal Payment (Cash, Bank, etc.)
        if ($paid_amount <= 0) {
            $errors[] = "Paid amount must be greater than 0.";
        }

        // Reset manager fields
        $manager_payment = 0;
        $expense         = 0;
        $manager_self    = 0;
    }

    // Show errors if any
    if (!empty($errors)) {
        $error_message = addslashes(implode("\\n\\n", $errors));
        echo "<script>alert('$error_message'); window.history.back();</script>";
        exit;
    }

    // ==================== Fetch Current Invoice ====================
    $month_sql = mysqli_query($db, "SELECT * FROM invoices 
                                    WHERE billing_month = '$billing_month' 
                                    AND tenant_id = '$tent_id' 
                                    LIMIT 1");

    if (mysqli_num_rows($month_sql) == 0) {
        echo "<script>alert('No invoice found for the selected month. Please create an invoice first.'); window.history.back();</script>";
        exit;
    }

    $row = mysqli_fetch_assoc($month_sql);

    $invoice_id = $row['id'];
    $old_total  = (int) $row['total_amount'];
    $old_paid   = (int) $row['paid_amount'];
    $old_due    = (int) $row['due_amount'];
    $old_status = $row['status'];

    // Already fully paid?
    if ($old_status === 'Paid' || $old_due <= 0) {
        echo "<script>alert('This month bill is already fully paid. No more payment is allowed.'); window.history.back();</script>";
        exit;
    }

    // Overpayment check
    if ($paid_amount > $old_due) {
        echo "<script>alert('You cannot pay more than the due amount. Due amount is: $old_due'); window.history.back();</script>";
        exit;
    }

    // Transaction ID duplicate check
    if ($transaction_id !== NULL) {
        $check_query = mysqli_query($db, "SELECT id FROM payment_history 
                                          WHERE transaction_id = '" . mysqli_real_escape_string($db, $transaction_id) . "' 
                                          LIMIT 1");
        if (mysqli_num_rows($check_query) > 0) {
            echo "<script>alert('This Transaction ID already exists. Please use a unique one.'); window.history.back();</script>";
            exit;
        }
    }

    // ==================== Calculate New Values ====================
    $new_paid = $old_paid + $paid_amount;
    $new_due  = $old_total - $new_paid;

    // Auto update status
    if ($new_due <= 0) {
        $new_status = 'Paid';
    } elseif ($new_paid > 0) {
        $new_status = 'Partial';
    } else {
        $new_status = $old_status;
    }

    // ==================== Update Invoice ====================
    $update_invoice = mysqli_query($db, "UPDATE invoices SET 
            paid_amount = '$new_paid',
            due_amount  = '$new_due',
            status      = '$new_status',
            note        = '" . mysqli_real_escape_string($db, $note) . "'
            WHERE id = '$invoice_id' AND tenant_id = '$tent_id'");

    if (!$update_invoice) {
        die("Invoice Update Error: " . mysqli_error($db));
    }

    // ==================== Insert Payment History (FIXED) ====================

    $sql = "INSERT INTO payment_history 
            (tenant_id, bill_month, payment_method, total, paid, paid_amount, due, note, 
             payment_date, manager_self, expense, expense_note, transaction_id, 
             manager_payment_method, manager_transaction_id, transaction_date, transaction_number) 
            VALUES 
            ('$tent_id', 
             '$billing_month', 
             '" . mysqli_real_escape_string($db, $payment_method) . "', 
             '$old_total', 
             '$new_paid', 
             '$paid_amount', 
             '$new_due', 
             '" . mysqli_real_escape_string($db, $note) . "', 
             '$payment_date', 
             '$manager_self', 
             '$expense', 
             '" . mysqli_real_escape_string($db, $expense_note) . "', 
             " . ($transaction_id === NULL ? "NULL" : "'" . mysqli_real_escape_string($db, $transaction_id) . "'") . ",
             " . (empty($manager_payment_method) ? "NULL" : "'" . mysqli_real_escape_string($db, $manager_payment_method) . "'") . ",
             " . (empty($manager_transaction_id) ? "NULL" : "'" . mysqli_real_escape_string($db, $manager_transaction_id) . "'") . ",
             '" . mysqli_real_escape_string($db, $transaction_date) . "',
             " . (empty($transaction_number) ? "NULL" : "'" . mysqli_real_escape_string($db, $transaction_number) . "'") . "
            )";

    $insert_history = mysqli_query($db, $sql);

    if ($insert_history) {
        header("Location: admin.php?page=editbill&unit_id=$unit_id");
        exit();
    } else {
        die("Payment History Insert Error: " . mysqli_error($db));
    }
}

// monthly payment sql 
$pay_info = mysqli_query($db, "SELECT * FROM invoices WHERE tenant_id = '$tent_id' AND unit_id = '$unit_id' ORDER BY billing_month ");
while ($pay_info_sh = mysqli_fetch_assoc($pay_info)) {
    $billing_month_db = $pay_info_sh['billing_month'];
    $total_amount_db = $pay_info_sh['total_amount'];
    $paid_amount_db = $pay_info_sh['paid_amount'];
    $due_amount_db = $pay_info_sh['due_amount'];
    $status = $pay_info_sh['status'];
    $Gas_db = $pay_info_sh['Gas'];
    $Water_db = $pay_info_sh['Water'];
    $Electricity_db = $pay_info_sh['Electricity'];
    $Others_db = $pay_info_sh['Others'];

    $Gas_month_db = $pay_info_sh['Gas_month'];
    $Water_month_db = $pay_info_sh['Water_month'];
    $Electricity_month_db = $pay_info_sh['Electricity_month'];
    $Others_month_db = $pay_info_sh['Others_month'];
}
?>

<div class="nxl-content">
    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <div class="page-header-left">
            <h5 class="m-b-10">
                <?php echo $building_name_db . '/' . $unit_name . '/' . $tent_name ?? '' ?>
            </h5>
        </div>
        <div class="page-header-right">
            <a href="admin.php?page=bill&id=<?php echo $building_name ?>" class="btn btn-primary">Back</a>
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
                                            Advance Payment
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
                        <h6 class="fw-bold ">Monthly Bills & Payment Summary</h6>
                        <a href="admin.php?page=invoice&unit_id=<?php echo $unit_id; ?>"
                            class="btn btn-sm btn-success ">
                            Invoice
                        </a>
                    </div>
                    <!-- Unit Name -->
                    <div class="row mx-1 ">
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
                                                style="font-size: 0.7rem;">Tenant Name : <strong
                                                    class="text-black"><?php echo $tent_name ?? 'N/A' ?></strong></small>

                                            <small class="text-muted"><?php echo $unit_type ?? 'Unit ' ?> : <strong
                                                    class="text-black"><?php echo $unit_name ?? 'N/A' ?></strong>
                                            </small>
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
                                            <tbody>
                                                <tr>
                                                    <td class="py-1">House Rent</td>
                                                    <td class="py-1 text-center">
                                                        <?= !empty($this_month) ? date("M Y", strtotime($this_month)) : '' ?>
                                                    </td>
                                                    <td class="py-1 text-end">৳
                                                        <?php echo number_format($rent, 2);
                                                        $total_bill = 0;
                                                        $total_bill += $rent;
                                                        ?>
                                                    </td>
                                                </tr>

                                                <?php if (!empty($Gas_db)) { ?>
                                                    <tr>
                                                        <td class="py-1">Gas Bill</td>
                                                        <td class="py-1 text-center">
                                                            <?= !empty($Gas_month_db) ? date("M Y", strtotime($Gas_month_db)) : '' ?>
                                                            <?php $total_bill += $Gas_db; ?>
                                                        </td>
                                                        <td class="py-1 text-end">৳
                                                            <?php echo number_format($Gas_db, 2); ?>
                                                        </td>
                                                    </tr>
                                                <?php } ?>

                                                <?php if (!empty($Water_db)) { ?>
                                                    <tr>
                                                        <td class="py-1">Water Bill</td>
                                                        <td class="py-1 text-center">
                                                            <?= $Water_month_db ?? '';
                                                            $total_bill += $Water_db;
                                                            ?>
                                                        </td>
                                                        <td class="py-1 text-end">৳
                                                            <?php echo number_format($Water_db, 2); ?>
                                                        </td>
                                                    </tr>
                                                <?php } ?>

                                                <?php if (!empty($Electricity_db)) { ?>
                                                    <tr>
                                                        <td class="py-1">Electricity Bill <span class="text-warning"
                                                                style="font-size:10px;">(<?= $size ?>)</span></td>
                                                        <td class="py-1 text-center">
                                                            <?= $Electricity_month_db ?? '';
                                                            $total_bill += $Electricity_db;
                                                            ?>
                                                        </td>
                                                        <td class="py-1 text-end">৳
                                                            <?php echo number_format($Electricity_db, 2); ?>
                                                        </td>
                                                    </tr>
                                                <?php } ?>

                                                <?php if (!empty($Others_db)) { ?>
                                                    <tr>
                                                        <td class="py-1">Others Bill</td>
                                                        <td class="py-1 text-center">
                                                            <?= $Others_month_db ?? '';
                                                            $total_bill += $Others_db;
                                                            ?>
                                                        </td>
                                                        <td class="py-1 text-end">৳
                                                            <?php echo number_format($Others_db, 2); ?>
                                                        </td>
                                                    </tr>
                                                <?php } ?>

                                            </tbody>
                                            <tfoot class="border-top">
                                                <tr class="table-light">
                                                    <td class="fw-bold py-2 text-primary">Current Month Total = </td>
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
                                                <span class="small fw-bold text-primary">Total Amount = </span>
                                                <span class="small fw-bold text-primary">৳
                                                    <?= number_format($total_due, 2) ?>
                                                </span>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div
                                        class="d-flex justify-content-between align-items-center mt-3 p-3 bg-primary text-white rounded shadow-sm">
                                        <span class="h6 mb-0 text-white">Total Payable Amount = </span>
                                        <span class="h5 mb-0 fw-bold text-white">৳
                                            <?= number_format($total_due, 2) ?>
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
                            <!-- create Invoice  -->
                            <form method="POST" enctype="multipart/form-data">
                                <div class="card p-3">
                                    <h6>Create Invoice : </h6>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <small class="fw-semibold">Gas Bill Month </small>
                                            <input type="month" name="Gas_month"
                                                value="<?php echo date('Y-m', strtotime('first day of last month')); ?>"
                                                class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <small class="fw-semibold" for="status">Gas Bill Amount</small>
                                            <input type="text" name="Gas" value="<?= $gas ?? '' ?>"
                                                class="form-control">
                                        </div>
                                    </div>

                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <small class="fw-semibold">Water Bill </small>
                                            <input type="text" name="Water_month"
                                                value="<?php echo date('M Y', strtotime('first day of this month -4 months')); ?>"
                                                class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <small class="fw-semibold" for="status">Water Bill Amount</small>
                                            <input type="text" name="Water" value="<?= $water ?? '' ?>"
                                                class="form-control">
                                        </div>
                                    </div>

                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <small class="fw-semibold">Electricity Bill <span class="text-warning"
                                                    style="font-size:10px;">(<?= $size ?>)</span></small>
                                            <input type="text" name="Electricity_month"
                                                value="<?php echo date('M Y', strtotime('first day of this month -2 months')); ?>"
                                                placeholder="Note" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <small class="fw-semibold" for="status">Electricity Bill Amount</small>
                                            <input type="text" name="Electricity" value="" class="form-control">
                                        </div>
                                    </div>

                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <small class="fw-semibold">Others Bill </small>
                                            <input type="text" name="Others_month" placeholder="Note"
                                                class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <small class="fw-semibold" for="status">Others Bill Amount</small>
                                            <input type="text" name="Others" value="" class="form-control">
                                        </div>
                                    </div>

                                    <button type="submit" name="create_invoice" class="btn btn-success btn-sm mt-3">
                                        Create Invoice
                                    </button>
                                </div>
                            </form>

                            <!-- confirm payment  -->
                            <form method="POST" enctype="multipart/form-data">
                                <div class="card p-3">
                                    <h6>Confirm Payment :</h6>

                                    <input type="hidden" name="total_amount" value="<?php echo $total_bill; ?>">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="">Amount *</label>
                                            <input type="text" name="paid_amount" class="form-control" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="">Pay For Month* <small class="text-warning"
                                                    style="font-size: 10px;">(Invoice)</small></label>
                                            <input type="month" name="billing_month" value="<?php echo $this_month; ?>"
                                                class="form-control" required>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="transaction_date">Transaction Time *</label>
                                            <input type="datetime-local" class="form-control" name="transaction_date" value="<?= date('Y-m-d\TH:i'); ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="payment_method">Payment Method *</label>
                                            <select name="payment_method" id="payment_method"
                                                class="form-control form-select" required
                                                onchange="togglePaymentFields()">
                                                <option value="" selected disabled>Select One</option>
                                                <option value="Cash">Cash</option>
                                                <option value="Bkash">Bkash</option>
                                                <option value="Nagad">Nagad</option>
                                                <option value="Rocket">Rocket</option> 
                                                <option value="Bank Transfer">Bank Transfer</option>
                                                <option value="Card">Card</option>
                                                <option value="Manager">Manager</option>
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Dynamic Fields -->
                                    <div id="payment_fields" style="display: none;">

                                        <!-- Manager Payment Section -->
                                        <div class="row" id="manager_section" style="display: none;">
                                            <div class="col-md-6">
                                                <label for="manager_payment" style="font-size: 12px; color: blue;">Manager Payment Amount</label>
                                                <input type="text" class="form-control" name="manager_payment" id="manager_payment">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="manager_payment_method" style="font-size: 12px; color: blue;">Manager Payment Method</label>
                                                <select name="manager_payment_method" id="manager_payment_method" 
                                                    class="form-control form-select" onchange="toggleManagerTransaction()">
                                                    <option value="" selected disabled>Select One</option>
                                                    <option value="Cash">Cash</option>
                                                    <option value="Bkash">Bkash</option>
                                                    <option value="Nagad">Nagad</option>
                                                    <option value="Rocket">Rocket</option>
                                                    <option value="Bank Transfer">Bank Transfer</option>
                                                    <option value="Card">Card</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Manager Transaction ID -->
                                        <div class="row" id="manager_transaction_id_div" >
                                            <div class="col-md-6">
                                                <label class="">Transaction ID</label>
                                                <input type="text" name="manager_transaction_id" id="manager_transaction_id" class="form-control">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="">Transaction Number</label>
                                                <input type="text" name="transaction_number" id="transaction_number" class="form-control">
                                            </div>
                                        </div>

                                        <!-- Expense Section (সবসময় দেখাবে যখন payment_fields active থাকবে) -->
                                        <div class="row" id="expense_row">
                                            <div class="col-md-6">
                                                <label for="expense">Expense Amount</label>
                                                <input type="text" class="form-control" name="expense" id="expense">
                                            </div>
                                            <div class="col-md-6">
                                                <label for="expense_note">Expense Note</label>
                                                <input type="text" name="expense_note" id="expense_note" class="form-control">
                                            </div>
                                        </div>

                                        <!-- Main Transaction ID (Non-Manager Digital Payments) -->
                                        <div class="row" id="transaction_id_div">
                                            <div class="col-md-6">
                                                <label class="">Transaction ID</label>
                                                <input type="text" name="transaction_id" id="transaction_id" class="form-control">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="">Transaction Number</label>
                                                <input type="text" name="transaction_number" id="transaction_number" class="form-control">
                                            </div>
                                        </div>

                                    </div>

                                    <div>
                                        <label class="">Note</label>
                                        <input type="text" name="note" class="form-control">
                                    </div>

                                    <button type="submit" name="save_bill" class="btn btn-success btn-sm mt-3">
                                        Confirm Payment
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- bill summary  -->
                <div class="card">
                    <div class="card-header">
                        <h6 class="fw-bold">Monthly bills (invoice history)</h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
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
                                        $invoice_id_db = $pay_info_sh['id'];
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
                                                <?php echo $due_amount_db ? '<small>৳</small>' . number_format($due_amount_db, 2) : ''; ?>
                                            </td>
                                            <td class="text-center">
                                                <?php if ($status == 'Paid'): ?>
                                                    <small class="bg-success text-white p-1 rounded-2">Paid</small>
                                                <?php elseif ($status == 'Unpaid'): ?>
                                                    <small class="bg-danger text-white p-1 rounded-2">Unpaid</small>
                                                <?php elseif ($status == 'Partial'): ?>
                                                    <small class="bg-warning text-white p-1 rounded-2">Partial</small>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-center pe-4">
                                                <div class="btn-group">
                                                    <!-- <button class="btn btn-sm btn-outline-primary" title="Edit">
                                                            <i class="bi bi-pencil-square"></i>
                                                        </button> -->
                                                    <a href="admin.php?page=viewInvoice&unit_id=<?php echo $unit_id; ?>&invoice_id=<?php echo $invoice_id_db; ?>"
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
                        <h6 class="fw-bold">Payment history </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col" class="ps-4">Date</th>
                                        <th scope="col" class="text-end">Bill Month</th>
                                        <th scope="col" class="text-end">Payment Method</th>
                                        <th scope="col" class="text-end">Payment Amount</th>
                                        <th scope="col" class="text-end">Bill Summary</th>
                                        <th scope="col" class="text-end">Manager Self</th>
                                        <th scope="col" class="text-end">Expense</th>
                                        <th scope="col" class="text-center">Note</th>
                                        <th scope="col" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $history_sql = mysqli_query($db, "SELECT * FROM `payment_history` WHERE `tenant_id` = '$tent_id' ");

                                    while ($pay_history = mysqli_fetch_assoc($history_sql)) {
                                        $pay_slip_id = $pay_history['id'];
                                        $bill_his = $pay_history['bill_month'];
                                        $pay_method_his = $pay_history['payment_method'];
                                        $total_his = $pay_history['total'];
                                        $paid_his = $pay_history['paid'];
                                        $due_his = $pay_history['due'];
                                        $note_his = $pay_history['note'];
                                        $pay_date_his = $pay_history['payment_date'];
                                        $paid_amount_his = $pay_history['paid_amount'];
                                        $manager_self = $pay_history['manager_self'];
                                        $expense = $pay_history['expense'];
                                        $expense_note = $pay_history['expense_note'];
                                        $transaction_id_db = $pay_history['transaction_id'];
                                        $manager_payment_method = $pay_history['manager_payment_method'];
                                        $manager_transaction_id = $pay_history['manager_transaction_id'];
                                        $transaction_date = $pay_history['transaction_date'];
                                        $transaction_number = $pay_history['transaction_number'];

                                        ?>
                                        <tr>
                                            <td class="ps-4 fw-medium">
                                                <?= date('d M Y', strtotime($pay_date_his)) ?>
                                            </td>
                                            <td class="text-end fw-semibold f-w-bold text-uppercase text-secendary">
                                                <?= date(' M Y', strtotime($bill_his)) ?>
                                            </td>
                                            <td class="text-end text-secendary fw-semibold">
                                                <?= $pay_method_his ?><br>
                                                <?php if (!empty($transaction_id_db)) : ?>
                                                    <small style="font-size:8px;" class="text-secondary">
                                                        ( Txn ID : <?= $transaction_id_db ?> )
                                                    </small><br>
                                                <?php endif; ?>

                                                <?php if (!empty($manager_transaction_id)) : ?>
                                                    <small style="font-size:8px;" class="text-secondary">
                                                        ( Txn ID : <?= $manager_transaction_id ?> )
                                                    </small><br>
                                                <?php endif; ?>

                                                <?php if (!empty($manager_payment_method)) : ?>
                                                    <small style="font-size:8px;" class="text-secondary">
                                                        ( Pay Method : <?= $manager_payment_method ?> )
                                                    </small><br>
                                                <?php endif; ?>

                                                <?php if (!empty($transaction_date)) : ?>
                                                    <small style="font-size:8px;" class="text-secondary">
                                                        ( Txn Date : <?= $transaction_date ?> )
                                                    </small><br>
                                                <?php endif; ?>

                                                <?php if (!empty($transaction_number)) : ?>
                                                    <small style="font-size:8px;" class="text-secondary">
                                                        ( Txn Number : <?= $transaction_number ?> )
                                                    </small><br>
                                                <?php endif; ?>
                                            </td>
                                            <td class="text-end text-success fw-semibold">
                                                <?php echo $paid_amount_his ? '<small>৳ </small>' . number_format($paid_amount_his, 2) : ''; ?>
                                            </td>
                                            <td class="text-end fw-semibold">
                                                <span
                                                    class="text-primary"><?php echo $total_his ? '<small>৳ </small>' . number_format($total_his, 2) : ''; ?></span><br>
                                                <span
                                                    class="text-success"><?php echo $paid_his ? '<small>৳ </small>' . number_format($paid_his, 2) : ''; ?></span><br>
                                                <span
                                                    class="text-danger"><?php echo $due_his ? '<small>৳ </small>' . number_format($due_his, 2) : ''; ?></span>
                                            </td>
                                            <td>
                                                <span
                                                    class="text-danger"><?php echo $manager_self ? '<small> Self : ৳ </small>' . $manager_self : ''; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span
                                                    class="text-danger"><?php echo $expense ? '<small>৳ </small>' . $expense : ''; ?></span><br>
                                                <small><?php echo $expense_note ? '(' . $expense_note . ')' : ''; ?></small>
                                            </td>
                                            <td class="text-center pe-4">
                                                <small class="text-secendary"><?= $note_his ?? '' ?></small>
                                            </td>
                                            <td>
                                                <a href="admin.php?page=payslip&unit_id=<?php echo $unit_id; ?>&id=<?php echo $pay_slip_id; ?>"
                                                    class="btn btn-sm btn-outline-success " title="view">
                                                    <i class="bi bi-eye"></i>
                                                </a>
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

<!-- JavaScript for input file dynamic change -->
<script>
    function togglePaymentFields() {
        const method = document.getElementById('payment_method').value;
        
        const paymentFields = document.getElementById('payment_fields');
        const managerSection = document.getElementById('manager_section');
        const managerTransactionDiv = document.getElementById('manager_transaction_id_div');
        const transactionIdDiv = document.getElementById('transaction_id_div');
        const expenseRow = document.getElementById('expense_row');

        // Default: সব hide
        paymentFields.style.display = 'none';
        managerSection.style.display = 'none';
        managerTransactionDiv.style.display = 'none';
        transactionIdDiv.style.display = 'none';

        if (method === "") {
            return;
        }

        paymentFields.style.display = 'block';

        if (method === "Manager") {
            // Manager সিলেক্ট করলে
            managerSection.style.display = 'flex';
            managerTransactionDiv.style.display = 'none';  // Manager method সিলেক্ট না করা পর্যন্ত hide
            transactionIdDiv.style.display = 'none';
            expenseRow.style.display = 'flex'; // Expense সবসময় দেখাবে যখন payment_fields active থাকবে
        }
        else if (method === "Cash") {
            // Cash এ শুধু Expense Amount + Note
            transactionIdDiv.style.display = 'none';
            expenseRow.style.display = 'none';
        }
        else {
            // Bkash, Nagad, Bank Transfer, Card, Rocket
            transactionIdDiv.style.display = 'flex';
            expenseRow.style.display = 'none';
        }
    }

    // Manager Payment Method এর জন্য আলাদা ফাংশন
    function toggleManagerTransaction() {
        const managerMethod = document.getElementById('manager_payment_method').value;
        const managerTransactionDiv = document.getElementById('manager_transaction_id_div');

        if (managerMethod === "Cash") {
            managerTransactionDiv.style.display = 'none';
        } else {
            managerTransactionDiv.style.display = 'flex';
        }
    }

    // Page load এ default behavior
    window.onload = function () {
        // যদি edit mode হয় তাহলে toggle চালু করবে
        togglePaymentFields();
        
        // Manager method থাকলে তার transaction ID ও চেক করবে
        if (document.getElementById('manager_payment_method')) {
            toggleManagerTransaction();
        }
    };
</script>