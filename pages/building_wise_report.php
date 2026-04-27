<?php
// Default values
$building_id = '';
$this_month = date('Y-m');   // Current month in YYYY-MM format

// Handle POST Filter
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['month']) && !empty($_POST['month'])) {
        $this_month = mysqli_real_escape_string($db, $_POST['month']);
    }

    if (isset($_POST['building']) && !empty($_POST['building'])) {
        $building_id = mysqli_real_escape_string($db, $_POST['building']);
    }
}

// If no POST, get building_id from URL
if (empty($building_id)) {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo '<div class="alert alert-danger">Invalid Building ID</div>';
        exit;
    }
    $building_id = mysqli_real_escape_string($db, $_GET['id']);
}

// Fetch Building Name
$buil_sql = mysqli_query($db, "SELECT name FROM building WHERE id = '$building_id'");
$building_row = mysqli_fetch_assoc($buil_sql);
$building_name_db = $building_row['name'] ?? 'Unknown Building';

// Fetch all rented units for this building
$query = "SELECT * FROM unit 
          WHERE building_name = '$building_id' 
            AND status = 'Rented'";

$result = mysqli_query($db, $query);
$total_unit = mysqli_num_rows($result);
?>

<div class="nxl-content">
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap align-items-center justify-content-between p-4 mb-4 bg-white shadow-sm rounded-3">

        <div class="d-flex align-items-center mb-2 mb-lg-0">
            <div class="icon-box bg-primary-soft text-primary me-3 p-3 rounded-circle"
                style="background: rgba(13, 110, 253, 0.1);">
                <i class="fas fa-file-invoice-dollar fs-4"></i>
            </div>
            <div>
                <h4 class="mb-1 fw-bold text-dark">
                    <?= htmlspecialchars($building_name_db) ?>
                </h4>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill">
                        <i class="fas fa-door-open me-1"></i> Total Units: <?= $total_unit ?>
                    </span>
                    <span class="text-muted small">
                        <i class="far fa-calendar-alt me-1"></i> <?= date('M Y', strtotime($this_month . '-01')) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="POST" class="d-flex flex-wrap gap-2 align-items-center">
            <div class="input-group input-group-sm shadow-sm" style="width: 180px;">
                <span class="input-group-text bg-white border-end-0"><i class="far fa-calendar-check text-muted"></i></span>
                <select name="month" class="form-select border-start-0 ps-0 fw-medium">
                    <?php
                    $currentYear = date('Y');
                    $selectedMonth = (int) substr($this_month, 5, 2);

                    for ($m = 1; $m <= 12; $m++):
                        $monthValue = $currentYear . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
                        $displayText = date('F', mktime(0, 0, 0, $m, 1, $currentYear));
                        ?>
                        <option value="<?= $monthValue ?>" <?= $m == $selectedMonth ? 'selected' : '' ?>>
                            <?= $displayText ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="input-group input-group-sm shadow-sm" style="width: 220px;">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-building text-muted"></i></span>
                <select name="building" class="form-select border-start-0 ps-0 fw-medium">
                    <?php
                    $buildings_sql = mysqli_query($db, "SELECT id, name FROM building ORDER BY name ASC");
                    while ($buil = mysqli_fetch_assoc($buildings_sql)) {
                        $b_id = $buil['id'];
                        $b_name = $buil['name'];
                        $selected = ($b_id == $building_id) ? 'selected' : '';
                        echo "<option value='$b_id' $selected>$b_name</option>";
                    }
                    ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm d-flex align-items-center gap-2">
                <i class="fas fa-filter"></i>
                <span>Filter</span>
            </button>
        </form>
    </div>
</div>

<div class="main-content">
    <?php
    // ==================== OVERALL MANAGER PAYMENT SUMMARY ====================
    $manager_summary = mysqli_query($db, "
            SELECT 
                SUM(ph.paid_amount) as total_received,
                SUM(ph.manager_self) as manager_self_total,
                SUM(ph.expense) as expense_total
            FROM payment_history ph
            JOIN tenants t ON ph.tenant_id = t.id
            WHERE t.building_id = '$building_id' 
            AND ph.bill_month = '$this_month' 
            AND ph.payment_method = 'Manager'
        ");

    $summary = mysqli_fetch_assoc($manager_summary);

    $total_received = (float) ($summary['total_received'] ?? 0);
    $manager_self_total = (float) ($summary['manager_self_total'] ?? 0);
    $expense_total = (float) ($summary['expense_total'] ?? 0);
    $manager_net_paid = $total_received - $manager_self_total - $expense_total;
    ?>

    <!-- Summary Cards -->
    <div class="row g-3 mx-3">
        <?php
        // Initialize totals
        $total_bill_amount = 0;
        $paid_amount_db_amount = 0;
        $due_amount_db_amount = 0;

        $temp_result = mysqli_query($db, $query); // Reset result for summary calculation

        while ($row = mysqli_fetch_assoc($temp_result)) {

            $unit_id = $row['id'];
            $rent = (float) $row['rent'];

            // Tenant Info
            $tenant_query = mysqli_query($db, "SELECT id FROM tenants 
                WHERE building_id = '$building_id' AND unit_id = '$unit_id' LIMIT 1");

            $tenant = mysqli_fetch_assoc($tenant_query);
            $tent_id = $tenant['id'] ?? 0;

            $total_bill = 0;
            $paid_amount_db = 0;
            $due_amount_db = 0;

            // **শুধুমাত্র ইনভয়েস থাকলে** বিল ক্যালকুলেট করবে
            if ($tent_id) {
                $inv_query = mysqli_query($db, "SELECT * FROM invoices 
                    WHERE tenant_id = '$tent_id' 
                    AND unit_id = '$unit_id' 
                    AND billing_month = '$this_month' LIMIT 1");

                if ($inv = mysqli_fetch_assoc($inv_query)) {
                    $Gas = (float) ($inv['Gas'] ?? 0);
                    $Water = (float) ($inv['Water'] ?? 0);
                    $Electricity = (float) ($inv['Electricity'] ?? 0);
                    $Others = (float) ($inv['Others'] ?? 0);

                    $total_bill = $rent + $Gas + $Water + $Electricity + $Others;
                    $paid_amount_db = (float) ($inv['paid_amount'] ?? 0);
                    $due_amount_db = (float) ($inv['due_amount'] ?? $total_bill);
                }
            }

            // Add to summary (শুধু যাদের invoice আছে)
            $total_bill_amount += $total_bill;
            $paid_amount_db_amount += $paid_amount_db;
            $due_amount_db_amount += $due_amount_db;
        }
        ?>

        <div class="col-md">
            <div class="card shadow-sm border-0 bg-primary text-white">
                <div class="card-body text-center">
                    <h6 class="text-white">Total Amount</h6>
                    <h4 class="text-white">৳ <?= number_format($total_bill_amount, 0) ?></h4>
                </div>
            </div>
        </div>

        <div class="col-md">
            <div class="card shadow-sm border-0 bg-success text-white">
                <div class="card-body text-center">
                    <h6 class="text-white">Total Paid</h6>
                    <h4 class="text-white">৳ <?= number_format($paid_amount_db_amount, 0) ?></h4>
                </div>
            </div>
        </div>

        <div class="col-md">
            <div class="card shadow-sm border-0 bg-danger text-white">
                <div class="card-body text-center">
                    <h6 class="text-white">Total Unpaid</h6>
                    <h4 class="text-white">৳ <?= number_format($due_amount_db_amount, 0) ?></h4>
                </div>
            </div>
        </div>

        <div class="col-md">
            <div class="card shadow-sm border-0 bg-warning text-white">
                <div class="card-body text-center">
                    <h6 class="text-white">Total Expense</h6>
                    <h4 class="text-white">৳ <?php
                        $admin_total_expense_sql = mysqli_query($db, "SELECT SUM(amount) AS total_in_expances FROM `expense` WHERE building_id='$building_id' AND expense_month='$this_month' ");
                        $admin_total_expense_row = mysqli_fetch_assoc($admin_total_expense_sql);
                        $admin_total_expense = (float)($admin_total_expense_row['total_in_expances'] ?? 0);
                        $in_total_expanse = $admin_total_expense + $expense_total;
                        echo number_format(max($in_total_expanse, 0), 0);
                    ?></h4>
                </div>
            </div>
        </div>

        <div class="col-md">
            <div class="card shadow-sm border-0 bg-secondary text-white">
                <div class="card-body text-center py-4" style="margin:-14px 0px;">
                    <strong>Paid By Manager</strong><br>
                    <div style="text-align: left; margin: 0px -13px;">
                        <small>Total : ৳ <?= number_format(max($total_received, 0), 0) ?></small><br>
                        <small>Manager Self : ৳ <?= number_format(max($manager_self_total, 0), 0) ?></small><br>
                        <small>Manager Paid : ৳ <?= number_format(max($manager_net_paid, 0), 0) ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Method Wise Ratio (আগের কোডই রাখলাম, শুধু ছোটখাটো improvement) -->
    <?php
    // ... (আপনার আগের Payment Method Ratio কোডটা এখানে রাখুন - কোনো পরিবর্তন লাগবে না)
    ?>

    <!-- Main Table -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>SL</th>
                            <th>Unit</th>
                            <th>Tenant</th>
                            <th>Bill Details</th>
                            <th>Status</th>
                            <th>Manager Payment Info</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 0;
                        mysqli_data_seek($result, 0);   // Reset pointer

                        while ($row = mysqli_fetch_assoc($result)) {
                            $i++;
                            $unit_id = $row['id'];
                            $unit_name = $row['unit_name'];
                            $rent = (float) $row['rent'];
                            $advance = (float) $row['advance'] ?? 0;
                            $size = $row['size'] ?? '';

                            // Tenant Info
                            $tenant_query = mysqli_query($db, "SELECT * FROM tenants 
                                    WHERE building_id = '$building_id' AND unit_id = '$unit_id' LIMIT 1");
                            $tenant = mysqli_fetch_assoc($tenant_query);

                            $tent_id = $tenant['id'] ?? '';
                            $name = $tenant['name'] ?? 'N/A';
                            $image = !empty($tenant['tenant_image'])
                                ? "public/uploads/tenants/" . $tenant['tenant_image']
                                : "public/uploads/tenants/no-image.png";

                            // Advance Due
                            $adv_sql = mysqli_query($db, "SELECT SUM(paid_amount) as total 
                                    FROM advance WHERE tenant_id = '$tent_id' AND unit_id = '$unit_id'");
                            $adv = mysqli_fetch_assoc($adv_sql);
                            $total_advance_paid = (float) ($adv['total'] ?? 0);
                            $advance_due = max($advance - $total_advance_paid, 0);

                            // ==================== INVOICE CHECK ====================
                            $total_bill = 0;
                            $paid_amount_db = 0;
                            $due_amount_db = 0;
                            $Gas = $Water = $Electricity = $Others = 0;
                            $status = 'No Invoice';

                            if ($tent_id) {
                                $inv_query = mysqli_query($db, "SELECT * FROM invoices 
                                    WHERE tenant_id = '$tent_id' 
                                    AND unit_id = '$unit_id' 
                                    AND billing_month = '$this_month' LIMIT 1");

                                if ($inv = mysqli_fetch_assoc($inv_query)) {
                                    $Gas = (float) ($inv['Gas'] ?? 0);
                                    $Water = (float) ($inv['Water'] ?? 0);
                                    $Electricity = (float) ($inv['Electricity'] ?? 0);
                                    $Others = (float) ($inv['Others'] ?? 0);

                                    $total_bill = $rent + $Gas + $Water + $Electricity + $Others;
                                    $paid_amount_db = (float) ($inv['paid_amount'] ?? 0);
                                    $due_amount_db = (float) ($inv['due_amount'] ?? $total_bill);
                                    $status = $inv['status'] ?? 'Unpaid';
                                }
                            }

                            // Manager Payment Info
                            $history_sql = mysqli_query($db, "
                                SELECT * FROM payment_history 
                                WHERE tenant_id = '$tent_id' 
                                AND bill_month = '$this_month'
                            ");

                            $manager_self = 0;
                            $expense = 0;
                            $received = 0;
                            $pay_method = '';
                            $transaction_id_db = '';
                            $manager_transaction_id = '';
                            $transaction_number = '';

                            while ($his = mysqli_fetch_assoc($history_sql)) {
                                $manager_self += (float) ($his['manager_self'] ?? 0);
                                $expense += (float) ($his['expense'] ?? 0);
                                $received += (float) ($his['paid_amount'] ?? 0);
                                $pay_method = $his['payment_method'] ?? '';
                                $transaction_id_db = $his['transaction_id'] ?? '';
                                $manager_transaction_id = $his['manager_transaction_id'] ?? '';
                                $transaction_number = $his['transaction_number'] ?? '';
                            }
                            $manager_paid = $received - $manager_self - $expense;
                            ?>
                            <tr>
                                <td><?= $i ?></td>
                                <td><strong><?= htmlspecialchars($unit_name) ?></strong></td>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= htmlspecialchars($image) ?>" width="50" height="50"
                                            style="object-fit:cover; border-radius:50%;" alt="">
                                        <div>
                                            <a href="admin.php?page=view_tenant&id=<?= $tent_id ?>"
                                                class="fw-bold text-secondary"><?= htmlspecialchars($name) ?></a>
                                            <?php if ($size): ?>
                                                <small class="text-muted d-block">Ele.M.N: <?= htmlspecialchars($size) ?></small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <!-- Bill Details - শুধু ইনভয়েস থাকলে দেখাবে -->
                                <td>
                                    <?php if ($total_bill > 0): ?>
                                        <?php if ($advance_due > 0): ?>
                                            <span class="text-danger fw-bold">Advance Due: ৳ <?= number_format($advance_due, 0) ?></span><br>
                                        <?php endif; ?>

                                        <strong>Total = ৳ <?= number_format($total_bill, 0) ?></strong><br>

                                        <?php if ($paid_amount_db > 0): ?>
                                            <span class="text-success fw-bold">Paid = ৳ <?= number_format($paid_amount_db, 0) ?></span><br>
                                        <?php endif; ?>
                                        <?php if ($due_amount_db > 0): ?>
                                            <span class="text-danger fw-bold">Due = ৳ <?= number_format($due_amount_db, 0) ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-muted">No Invoice Created</span>
                                    <?php endif; ?>
                                </td>

                                <!-- Status -->
                                <td>
                                    <button class="btn btn-sm btn-<?= $status == 'Paid' ? 'success' : ($status == 'Partial' ? 'warning' : ($status == 'Unpaid' ? 'danger' : 'secondary')) ?>">
                                        <?= htmlspecialchars($status) ?>
                                    </button>
                                </td>

                                <!-- Manager Payment Info -->
                                <td>
                                    <?php if (mysqli_num_rows($history_sql) == 0): ?>
                                        <small class="text-danger">Payment not Found !</small>
                                    <?php else: ?>
                                        <small class="text-success fw-bold"><?= htmlspecialchars($pay_method) ?></small><br>
                                        <?php if (!empty($transaction_id_db)): ?>
                                            <small class="text-info">Trx ID: <?= htmlspecialchars($transaction_id_db) ?></small><br>
                                        <?php endif; ?>
                                        <?php if (!empty($manager_transaction_id)): ?>
                                            <small class="text-info">Manager Trx: <?= htmlspecialchars($manager_transaction_id) ?></small><br>
                                        <?php endif; ?>
                                        <?php if ($manager_self > 0): ?>
                                            <small class="text-primary">Self: ৳ <?= number_format($manager_self, 0) ?></small><br>
                                        <?php endif; ?>
                                        <?php if ($expense > 0): ?>
                                            <small class="text-warning">Expense: ৳ <?= number_format($expense, 0) ?></small><br>
                                        <?php endif; ?>
                                        <small class="text-success">Paid: ৳ <?= number_format(max($manager_paid, 0), 0) ?></small>
                                    <?php endif; ?>
                                </td>

                                <!-- Action -->
                                <td class="text-end">
                                    <a href="admin.php?page=editbill&unit_id=<?= $unit_id ?>"
                                        class="btn btn-sm btn-info" title="Invoice Create & Payment">
                                        Details
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