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
            AND status = 'Rented' 
          ";

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
        <form method="POST" class="row align-items-end">
            <!-- Building -->
            <div class="col-12 col-md">
                <div class="input-group input-group-sm shadow-sm">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="fas fa-building text-muted"></i>
                    </span>
                    <select name="building" class="form-select border-start-0">
                        <?php
                        $buildings_sql = mysqli_query($db, "SELECT id, name FROM building ORDER BY name ASC");
                        while ($buil = mysqli_fetch_assoc($buildings_sql)) {
                            $selected = ($buil['id'] == $building_id) ? 'selected' : '';
                            echo "<option value='{$buil['id']}' $selected>{$buil['name']}</option>";
                        }
                        ?>
                    </select>
                </div>
            </div>

            <!-- Month -->
            <div class="col-7 col-md-auto">
                <div class="input-group input-group-sm shadow-sm">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="far fa-calendar-check text-muted"></i>
                    </span>
                    <select name="month" class="form-select border-start-0">
                        <?php
                        $currentYear = date('Y');
                        $selectedMonth = (int)substr($this_month, 5, 2);

                        for ($m = 1; $m <= 12; $m++):
                            $monthValue = $currentYear . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
                            $displayText = date('F', mktime(0, 0, 0, $m, 1, $currentYear));
                        ?>
                            <option value="<?= $monthValue ?>"
                                <?= $m == $selectedMonth ? 'selected' : '' ?>>
                                <?= $displayText ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
            </div>

            <!-- Filter Button -->
            <div class="col-5 col-md-auto">
                <label class="form-label small fw-semibold mb-1 invisible">
                    Filter
                </label>
                <button type="submit"
                    class="btn btn-success btn-sm shadow-sm w-100 d-flex align-items-center justify-content-center gap-2">
                    <i class="fas fa-filter"></i>
                    <span>Filter</span>
                </button>
            </div>
        </form>
    </div>
</div>

<div class="main-content">
    <?php
    // ==================== OVERALL MANAGER PAYMENT SUMMARY ====================
    $manager_summary = mysqli_query($db, "
            SELECT 
                SUM(ph.paid_amount) as total_received,
                SUM(ph.manager_paid) as manager_paid_total
            FROM payment_history ph
            JOIN tenants t ON ph.tenant_id = t.id
            WHERE t.building_id = '$building_id' 
            AND ph.bill_month = '$this_month' 
            AND ph.payment_method = 'Manager'
        ");

    $summary = mysqli_fetch_assoc($manager_summary);

    $total_received = (float) ($summary['total_received'] ?? 0);
    $manager_paid_total = (float) ($summary['manager_paid_total'] ?? 0);
    $manager_self = $total_received - $manager_paid_total;
    ?>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4 mx-3">
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-primary text-white">
                <div class="card-body text-center">
                    <strong class="mb-1 text-white">Total Collected </strong>
                    <h4 class="mb-0 text-white">৳ <?= number_format($total_received, 0) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-success">
                <div class="card-body text-center">
                    <strong class="mb-1 text-white">Manager Paid to Admin</strong>
                    <h4 class="mb-0 text-white">৳ <?= number_format($manager_paid_total, 0) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <?php 
                // 3. Manager Expense (Assuming 'expense_by' contains 'Manager')
                $manager_sql = mysqli_query($db, "SELECT SUM(amount) AS total FROM `expense` WHERE building_id='$building_id' AND expense_month='$this_month' AND expense_by = 'Manager'");
                $manager_row = mysqli_fetch_assoc($manager_sql);
                $manager_total = (float)($manager_row['total'] ?? 0);

                // manager payalbe amount 
                $payable = $manager_self-$manager_total;
            ?>
            <div class="card shadow-sm border-0 bg-info text-white">
                <div class="card-body text-center px-0 mx-0">
                    <strong class="mb-1 text-white">Total Expense</strong>
                    <h4 class="mb-0 text-white">৳ <?= number_format(max($manager_total, 0), 0) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card shadow-sm border-0 bg-warning text-white">
                <div class="card-body text-center px-0 mx-0">
                    <strong class="mb-1 text-white">Manager self (Net Payable)</strong>
                    <h4 class="mb-0" style="color: <?= ($payable < 0) ? 'red' : 'white'; ?>;">৳ <?= number_format($payable, 0) ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Table -->
    <div class="card shadow-sm mx-4">
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
                        mysqli_data_seek($result, 0);

                        while ($row = mysqli_fetch_assoc($result)) {

                            $unit_id = $row['id'];
                            $unit_name = $row['unit_name'];
                            $advance = (float) $row['advance'];
                            $rent = (float) $row['rent'];
                            $size = $row['size'] ?? '';

                            // Tenant Info
                            $tenant_query = mysqli_query($db, "
                                SELECT * FROM tenants
                                WHERE building_id = '$building_id'
                                AND unit_id = '$unit_id'
                                LIMIT 1
                            ");

                            $tenant = mysqli_fetch_assoc($tenant_query);

                            $tent_id = $tenant['id'] ?? '';

                            // Tenant না থাকলে skip
                            if (empty($tent_id)) {
                                continue;
                            }

                            // ===============================
                            // ONLY MANAGER PAYMENT HOLDERS
                            // ===============================
                            $history_sql = mysqli_query($db, "
                                SELECT * FROM payment_history
                                WHERE tenant_id = '$tent_id'
                                AND bill_month = '$this_month'
                                AND payment_method = 'Manager'
                            ");

                            // Manager payment না থাকলে row show করবে না
                            if (mysqli_num_rows($history_sql) == 0) {
                                continue;
                            }

                            $i++;

                            $name = $tenant['name'] ?? 'N/A';

                            $image = !empty($tenant['tenant_image'])
                                ? "public/uploads/tenants/" . $tenant['tenant_image']
                                : "public/uploads/tenants/no-image.png";

                            // Advance Due
                            $adv_sql = mysqli_query($db, "
                                SELECT SUM(paid_amount) as total
                                FROM advance
                                WHERE tenant_id = '$tent_id'
                                AND unit_id = '$unit_id'
                            ");

                            $adv = mysqli_fetch_assoc($adv_sql);

                            $total_advance_paid = (float) ($adv['total'] ?? 0);
                            $advance_due = max($advance - $total_advance_paid, 0);

                            // Invoice Info
                            $inv_query = mysqli_query($db, "
                                SELECT *
                                FROM invoices
                                WHERE tenant_id = '$tent_id'
                                AND unit_id = '$unit_id'
                                AND billing_month = '$this_month'
                                LIMIT 1
                            ");

                            $has_invoice = mysqli_num_rows($inv_query) > 0;

                            $status = 'No Invoice';
                            $total_bill = $rent;
                            $paid_amount_db = 0;
                            $due_amount_db = $rent;

                            if ($has_invoice) {

                                $inv = mysqli_fetch_assoc($inv_query);

                                $Gas = (float) ($inv['Gas'] ?? 0);
                                $Water = (float) ($inv['Water'] ?? 0);
                                $Electricity = (float) ($inv['Electricity'] ?? 0);
                                $Others = (float) ($inv['Others'] ?? 0);

                                $total_bill = $rent + $Gas + $Water + $Electricity + $Others;

                                $paid_amount_db = (float) ($inv['paid_amount'] ?? 0);
                                $due_amount_db = $total_bill - $paid_amount_db;

                                $status = $inv['status'] ?? 'Unpaid';
                            }

                            // Manager Payment Info
                            $manager_self = 0;
                            $received = 0;
                            $manager_paid = 0;
                            $pay_method = '';

                            while ($his = mysqli_fetch_assoc($history_sql)) {

                                $received += (float) ($his['paid_amount'] ?? 0);
                                $manager_paid += (float) ($his['manager_paid'] ?? 0);

                                $pay_method = $his['payment_method'];
                            }

                            $manager_self = $received - $manager_paid;
                            ?>

                            <tr>
                                <td><?= $i ?></td>

                                <td>
                                    <strong><?= htmlspecialchars($unit_name) ?></strong>
                                </td>

                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="<?= htmlspecialchars($image) ?>"
                                            width="50"
                                            height="50"
                                            style="object-fit:cover;border-radius:50%;"
                                            alt="">

                                        <div>
                                            <a href="admin.php?page=view_tenant&id=<?= $tent_id ?>"
                                                class="fw-bold text-secondary">
                                                <?= htmlspecialchars($name) ?>
                                            </a>

                                            <?php if ($size): ?>
                                                <small class="text-muted d-block">
                                                    Ele.M.N:
                                                    <?= htmlspecialchars($size) ?>
                                                </small>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <!-- Bill Details -->
                                <td>

                                    <?php if ($advance_due > 0): ?>
                                        <span class="text-danger fw-bold">
                                            Advance Due: ৳ <?= number_format($advance_due, 0) ?>
                                        </span>
                                        <br>
                                    <?php endif; ?>

                                    <strong>
                                        Total = ৳ <?= number_format($total_bill, 0) ?>
                                    </strong>
                                    <br>

                                    <?php if ($paid_amount_db > 0): ?>
                                        <span class="text-success fw-bold">
                                            Paid = ৳ <?= number_format($paid_amount_db, 0) ?>
                                        </span>
                                        <br>
                                    <?php endif; ?>

                                    <?php if ($due_amount_db > 0): ?>
                                        <span class="text-danger fw-bold">
                                            Due = ৳ <?= number_format($due_amount_db, 0) ?>
                                        </span>
                                    <?php endif; ?>

                                </td>

                                <!-- Status -->
                                <td>
                                    <button class="p-1 btn btn-sm btn-<?=
                                        $status == 'Paid'
                                            ? 'success'
                                            : ($status == 'Partial'
                                                ? 'warning'
                                                : ($status == 'Unpaid'
                                                    ? 'danger'
                                                    : 'secondary'))
                                    ?>">
                                        <?= htmlspecialchars($status) ?>
                                    </button>
                                </td>

                                <!-- Manager Payment -->
                                <td>

                                    <small class="text-success fw-bold">
                                        <?= htmlspecialchars($pay_method) ?>
                                    </small>
                                    <br>

                                    <?php if ($received > 0): ?>
                                        <small class="text-primary fw-bold">
                                            Receive: ৳ <?= number_format($received, 0) ?>
                                        </small>
                                        <br>
                                    <?php endif; ?>

                                    <?php if ($manager_paid > 0): ?>
                                        <small class="text-success fw-bold">
                                            Paid: ৳ <?= number_format($manager_paid, 0) ?>
                                        </small>
                                        <br>
                                    <?php endif; ?>

                                    <strong class="text-danger">
                                        Self: ৳ <?= number_format($manager_self, 0) ?>
                                    </strong>

                                </td>

                                <!-- Action -->
                                <td class="text-end">
                                    <a href="admin.php?page=editbill&tenant_id=<?= $tent_id ?>"
                                        class="text-end p-1 btn btn-sm btn-info">
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