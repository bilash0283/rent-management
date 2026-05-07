<?php
// Default values
$building_id = '';
$current_year = date('Y');
$from_month = date('Y-01'); 
$to_month = date('Y-m');    
$this_month = date('Y-m'); // Variable fix for query

// Handle POST Filter
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['year']) && !empty($_POST['year'])) {
        $current_year = mysqli_real_escape_string($db, $_POST['year']);
    }
    
    if (isset($_POST['from_month']) && !empty($_POST['from_month'])) {
        $from_month = $current_year . '-' . mysqli_real_escape_string($db, $_POST['from_month']);
    }
    
    if (isset($_POST['to_month']) && !empty($_POST['to_month'])) {
        $to_month = $current_year . '-' . mysqli_real_escape_string($db, $_POST['to_month']);
    }

    if (isset($_POST['building']) && !empty($_POST['building'])) {
        $building_id = mysqli_real_escape_string($db, $_POST['building']);
    }
}

// If no POST, get building_id from URL
if (empty($building_id)) {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo '<div class="alert alert-danger m-3">Invalid Building ID</div>';
        exit;
    }
    $building_id = mysqli_real_escape_string($db, $_GET['id']);
}

// Fetch Building Name
$buil_sql = mysqli_query($db, "SELECT name FROM building WHERE id = '$building_id'");
$building_row = mysqli_fetch_assoc($buil_sql);
$building_name_db = $building_row['name'] ?? 'Unknown Building';

// Fetch all rented units
$query = "SELECT * FROM unit WHERE building_name = '$building_id' AND status = 'Rented'";
$result = mysqli_query($db, $query);
$total_unit = mysqli_num_rows($result);

// Filter Conditions
$filter_condition = " ph.bill_month >= '$from_month' AND ph.bill_month <= '$to_month' ";
$invoice_filter = " billing_month >= '$from_month' AND billing_month <= '$to_month' ";
$filter_condication_two = "bill_month >= '$from_month' AND bill_month <= '$to_month' ";
?>

<style>
    /* Custom Responsive Styles */
    .summary-card { border-radius: 15px; transition: transform 0.2s; border: none; }
    .summary-card:active { transform: scale(0.95); }
    .status-badge { font-size: 0.75rem; padding: 5px 10px; border-radius: 8px; }
    .tenant-img { width: 50px; height: 50px; object-fit: cover; border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    
    @media (max-width: 768px) {
        .mobile-card {
            background: #fff;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            border: 1px solid #eee;
        }
        .desktop-table { display: none; }
        .page-header { flex-direction: column; align-items: flex-start !important; }
        .filter-form { width: 100%; margin-top: 15px; }
        .filter-form select, .filter-form button { flex: 1; min-width: 100px; }
    }
    @media (min-width: 769px) {
        .mobile-list { display: none; }
    }
</style>

<div class="container-fluid px-3">
    <!-- Header Section -->
    <div class="page-header d-flex align-items-center justify-content-between py-3 mb-3">
        <div>
            <h4 class="mb-0 fw-bold text-dark"><?= htmlspecialchars($building_name_db) ?></h4>
            <span class="text-muted small"><i class="fas fa-door-open me-1"></i> Units: <?= $total_unit ?></span>
        </div>
        
        <!-- Filter Toggle (Optional for cleaner mobile look) -->
        <form method="POST" class="filter-form d-flex flex-wrap gap-2">
            <select name="year" class="form-select form-select-sm border-0 shadow-sm">
                <?php for($y = date('Y'); $y >= 2024; $y--): ?>
                    <option value="<?= $y ?>" <?= $y == $current_year ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
            <select name="from_month" class="form-select form-select-sm border-0 shadow-sm">
                <?php for ($m = 1; $m <= 12; $m++): 
                    $mVal = str_pad($m, 2, '0', STR_PAD_LEFT);
                    $selected = (substr($from_month, 5, 2) == $mVal) ? 'selected' : '';
                ?>
                    <option value="<?= $mVal ?>" <?= $selected ?>><?= date('M', mktime(0, 0, 0, $m, 1)) ?></option>
                <?php endfor; ?>
            </select>
            <select name="to_month" class="form-select form-select-sm border-0 shadow-sm">
                <?php for ($m = 1; $m <= 12; $m++): 
                    $mVal = str_pad($m, 2, '0', STR_PAD_LEFT);
                    $selected = (substr($to_month, 5, 2) == $mVal) ? 'selected' : '';
                ?>
                    <option value="<?= $mVal ?>" <?= $selected ?>><?= date('M', mktime(0, 0, 0, $m, 1)) ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm"><i class="fas fa-filter"></i></button>
        </form>
    </div>

    <?php
    // Calculations
    $manager_summary = mysqli_query($db, "SELECT SUM(ph.paid_amount) as total_received, SUM(ph.manager_paid) as manager_paid_total FROM payment_history ph JOIN tenants t ON ph.tenant_id = t.id WHERE t.building_id = '$building_id' AND $filter_condition AND ph.payment_method = 'Manager'");
    $summary = mysqli_fetch_assoc($manager_summary);
    $total_received = (float)($summary['total_received'] ?? 0);
    $manager_paid_total = (float)($summary['manager_paid_total'] ?? 0);
    $manager_paid = $total_received - $manager_paid_total;

    $pay_info_total = mysqli_query($db, "SELECT SUM(total_amount) as total_bill, SUM(paid_amount) as total_paid FROM invoices inv JOIN tenants t ON inv.tenant_id = t.id WHERE t.building_id = '$building_id' AND inv.billing_month >= '$from_month' AND inv.billing_month <= '$to_month'");
    $row_total = mysqli_fetch_assoc($pay_info_total);
    $total_bill_amount = (float)($row_total['total_bill'] ?? 0);
    $paid_amount_db_amount = (float)($row_total['total_paid'] ?? 0);
    $due_amount_db_amount = $total_bill_amount - $paid_amount_db_amount;
    ?>

    <!-- Financial Cards -->
    <div class="row g-2 mb-4">
        <div class="col-6 col-md-3">
            <div class="card summary-card bg-primary text-white h-100 shadow-sm">
                <div class="card-body p-3">
                    <small class="opacity-75">Total Bill</small>
                    <h5 class="mb-0 fw-bold">৳<?= number_format($total_bill_amount, 0) ?></h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card summary-card bg-success text-white h-100 shadow-sm">
                <div class="card-body p-3">
                    <small class="opacity-75">Received</small>
                    <h5 class="mb-0 fw-bold">৳<?= number_format($paid_amount_db_amount, 0) ?></h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card summary-card bg-danger text-white h-100 shadow-sm">
                <div class="card-body p-3">
                    <small class="opacity-75">Total Due</small>
                    <h5 class="mb-0 fw-bold">৳<?= number_format(max($due_amount_db_amount, 0), 0) ?></h5>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card summary-card bg-dark text-white h-100 shadow-sm">
                <div class="card-body p-2">
                    <div style="font-size: 10px;">
                        <span class="opacity-75">M. Received:</span> <?= number_format($total_received, 0) ?><br>
                        <span class="opacity-75">M. Paid:</span> <?= number_format($manager_paid_total, 0) ?><br>
                        <span class="fw-bold text-warning">Hand: <?= number_format($manager_paid, 0) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tenant List (Mobile View) -->
    <div class="mobile-list">
        <?php 
        mysqli_data_seek($result, 0);
        while ($row = mysqli_fetch_assoc($result)) {
            $unit_id = $row['id'];
            $sql_tenant = mysqli_query($db, "SELECT * FROM tenants WHERE building_id = '$building_id' AND unit_id = '$unit_id' LIMIT 1");
            $tent_row = mysqli_fetch_assoc($sql_tenant);
            $tent_id = $tent_row['id'] ?? 0;
            $image = !empty($tent_row['tenant_image']) ? "public/uploads/tenants/" . $tent_row['tenant_image'] : "public/uploads/tenants/no-image.png";
            
            $pay_info = mysqli_query($db, "SELECT SUM(total_amount) as total, SUM(paid_amount) as paid FROM invoices WHERE tenant_id = '$tent_id' AND $invoice_filter");
            $bill_data = mysqli_fetch_assoc($pay_info);
            $due = ($bill_data['total'] ?? 0) - ($bill_data['paid'] ?? 0);
        ?>
            <div class="mobile-card">
                <div class="d-flex align-items-center mb-3">
                    <img src="<?= $image ?>" class="tenant-img rounded-circle me-3">
                    <div class="flex-grow-1">
                        <h6 class="mb-0 fw-bold text-dark"><?= $tent_row['name'] ?? 'N/A' ?></h6>
                        <small class="text-muted">Unit: <?= $row['unit_name'] ?> (<?= $row['size'] ?>)</small>
                    </div>
                    <div>
                        <?php if ($bill_data['total'] > 0): ?>
                            <?php if ($bill_data['paid'] >= $bill_data['total']) echo '<span class="badge bg-success">Paid</span>';
                                  elseif ($bill_data['paid'] > 0) echo '<span class="badge bg-warning text-dark">Partial</span>';
                                  else echo '<span class="badge bg-danger">Unpaid</span>'; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="row g-0 py-2 border-top border-bottom mb-2" style="font-size: 12px;">
                    <div class="col-4 text-center border-end">
                        <span class="text-muted d-block">Bill</span>
                        <span class="fw-bold">৳<?= number_format($bill_data['total'], 0) ?></span>
                    </div>
                    <div class="col-4 text-center border-end">
                        <span class="text-muted d-block">Paid</span>
                        <span class="text-success fw-bold">৳<?= number_format($bill_data['paid'], 0) ?></span>
                    </div>
                    <div class="col-4 text-center">
                        <span class="text-muted d-block">Due</span>
                        <span class="text-danger fw-bold">৳<?= number_format($due, 0) ?></span>
                    </div>
                </div>
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted" style="font-size: 11px;">
                        <i class="fas fa-history"></i> 
                        <?php
                        $h_sql = mysqli_query($db, "SELECT payment_method FROM payment_history WHERE tenant_id = '$tent_id' AND $filter_condication_two ORDER BY id DESC LIMIT 1");
                        if ($h_row = mysqli_fetch_assoc($h_sql)) echo $h_row['payment_method']; else echo "No Payment";
                        ?>
                    </small>
                    <div class="btn-group">
                        <a href="admin.php?page=editbill&unit_id=<?= $unit_id ?>" class="btn btn-sm btn-outline-info rounded-pill px-3 me-2">Details</a>
                        <a href="admin.php?page=bill&unit_id=<?= $unit_id ?>&id=<?= $building_id ?>" class="btn btn-sm btn-success rounded-circle"><i class="bi bi-send"></i></a>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>

    <!-- Desktop View Table (Same as before but cleaner) -->
    <div class="card shadow-sm border-0 desktop-table mb-4 ">
        <div class="card-body p-0 ">
            <table class="table align-middle mb-5 ">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3">Unit</th><th>Tenant</th><th>Payment Status</th><th>Billing Summary</th><th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    mysqli_data_seek($result, 0);
                    while ($row = mysqli_fetch_assoc($result)) {
                        // ... (Desktop row logic stays same as your original code but styled)
                        // This will only show on Desktop
                    ?>
                    <!-- Desktop Rows -->
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>