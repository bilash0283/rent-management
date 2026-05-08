<?php
// Default values
$building_id = '';
$this_month = date('Y-m');

// Handle POST Filter
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['month']) && !empty($_POST['month'])) {
        $this_month = mysqli_real_escape_string($db, $_POST['month']);
    }
    if (isset($_POST['building']) && !empty($_POST['building'])) {
        $building_id = mysqli_real_escape_string($db, $_POST['building']);
    }
}

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

// Overall Summary Calculation
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
$manager_net_payable = max($total_received - $manager_paid_total, 0);
?>

<style>
    /* Mobile App Style Custom CSS */
    .app-card { border-radius: 15px; border: none; transition: transform 0.2s; }
    .tenant-card { background: #fff; border-radius: 12px; margin-bottom: 15px; padding: 15px; border-left: 5px solid #0d6efd; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .status-badge { font-size: 0.75rem; padding: 5px 12px; border-radius: 20px; }
    .floating-filter { position: sticky; top: 0; z-index: 1000; background: #f8f9fa; padding: 10px 0; }
    .mobile-label { font-size: 0.8rem; color: #6c757d; margin-bottom: 2px; }
    .amount-text { font-weight: 700; color: #212529; }
    
    @media (max-width: 768px) {
        .desktop-table { display: none; }
        .mobile-list { display: block; }
    }
    @media (min-width: 769px) {
        .mobile-list { display: none; }
        .desktop-table { display: block; }
    }
</style>

<div class="container-fluid bg-light min-vh-100 pb-5">
    
    <!-- Header Section -->
    <div class="pt-4 px-2">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h3 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($building_name_db) ?></h3>
                <p class="text-muted small mb-0"><i class="far fa-calendar-alt"></i> <?= date('F Y', strtotime($this_month . '-01')) ?> | Units: <?= $total_unit ?></p>
            </div>
            <div class="icon-box bg-white shadow-sm p-3 rounded-circle">
                <i class="fas fa-building text-primary fs-4"></i>
            </div>
        </div>

        <!-- Mobile Filter Form -->
        <form method="POST" class="row g-2 mb-4">
            <div class="col-6">
                <select name="month" class="form-select border-0 shadow-sm rounded-pill px-3">
                    <?php 
                    $currentYear = date('Y');
                    for ($m = 1; $m <= 12; $m++): 
                        $monthValue = $currentYear . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
                        $displayText = date('F', mktime(0, 0, 0, $m, 1, $currentYear));
                    ?>
                        <option value="<?= $monthValue ?>" <?= $monthValue == $this_month ? 'selected' : '' ?>><?= $displayText ?></option>
                    <?php endfor; ?>
                </select>
            </div>
            <div class="col-6">
                <select name="building" class="form-select border-0 shadow-sm rounded-pill px-3">
                    <?php
                    $buildings_sql = mysqli_query($db, "SELECT id, name FROM building ORDER BY name ASC");
                    while ($buil = mysqli_fetch_assoc($buildings_sql)) {
                        $selected = ($buil['id'] == $building_id) ? 'selected' : '';
                        echo "<option value='{$buil['id']}' $selected>{$buil['name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="col-12 mt-2">
                <button type="submit" class="btn btn-primary w-100 rounded-pill shadow-sm py-2">
                    <i class="fas fa-filter me-2"></i>Apply Filter
                </button>
            </div>
        </form>
    </div>

    <!-- Summary Statistics -->
    <div class="row g-3 px-2 mb-4">
        <div class="col-4 text-center">
            <div class="p-2 bg-info bg-opacity-10 rounded-3">
                <p class="small text-muted mb-1 text-white">Collected</p>
                <strong class="fw-bold mb-0 text-white">৳<?= number_format($total_received, 0) ?></strong>
            </div>
        </div>
        <div class="col-4 text-center">
            <div class="p-2 bg-success bg-opacity-10 rounded-3">
                <p class="small text-muted mb-1 text-white">To Admin</p>
                <strong class="fw-bold  mb-0 text-white">৳<?= number_format($manager_paid_total, 0) ?></strong>
            </div>
        </div>
        <div class="col-4 text-center">
            <div class="p-2 bg-warning bg-opacity-10 rounded-3">
                <p class="small text-muted mb-1 text-white">Self/Net</p>
                <strong class="fw-bold  mb-0 text-white">৳<?= number_format($manager_net_payable, 0) ?></strong>
            </div>
        </div>
    </div>

    <!-- Desktop View (Table) -->
    <div class="desktop-table card shadow-sm border-0 mx-2">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-3">Unit & Tenant</th>
                        <th>Bill Summary</th>
                        <th>Manager Info</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php mysqli_data_seek($result, 0); while ($row = mysqli_fetch_assoc($result)): 
                         // Logic to fetch tenant & invoice data (reuse your logic)
                    ?>
                    <!-- Logic re-execution (keeping your original variable names) -->
                    <?php
                        $unit_id = $row['id'];
                        $unit_name = $row['unit_name'];
                        $tenant_query = mysqli_query($db, "SELECT * FROM tenants WHERE building_id = '$building_id' AND unit_id = '$unit_id' LIMIT 1");
                        $tenant = mysqli_fetch_assoc($tenant_query);
                        $tent_id = $tenant['id'] ?? '';
                        $name = $tenant['name'] ?? 'N/A';
                        $image = !empty($tenant['tenant_image']) ? "public/uploads/tenants/" . $tenant['tenant_image'] : "public/uploads/tenants/no-image.png";
                        
                        $inv_query = mysqli_query($db, "SELECT * FROM invoices WHERE tenant_id = '$tent_id' AND billing_month = '$this_month' LIMIT 1");
                        $inv = mysqli_fetch_assoc($inv_query);
                        $total_bill = (float)($row['rent'] + ($inv['Gas']??0) + ($inv['Water']??0) + ($inv['Electricity']??0) + ($inv['Others']??0));
                        $paid_db = (float)($inv['paid_amount'] ?? 0);
                        $due_db = $total_bill - $paid_db;
                        $status = $inv['status'] ?? 'No Invoice';

                        $history_sql = mysqli_query($db, "SELECT SUM(paid_amount) as rec, SUM(manager_paid) as mp FROM payment_history WHERE tenant_id = '$tent_id' AND bill_month = '$this_month' AND payment_method = 'Manager'");
                        $h = mysqli_fetch_assoc($history_sql);
                        $rec = (float)$h['rec']; $mp = (float)$h['mp'];
                    ?>
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex align-items-center">
                                <img src="<?= $image ?>" class="rounded-circle me-2" width="40" height="40" style="object-fit: cover;">
                                <div>
                                    <div class="fw-bold"><?= $unit_name ?></div>
                                    <div class="small text-muted"><?= $name ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div class="small">Total: ৳<?= number_format($total_bill) ?></div>
                            <div class="small text-danger fw-bold">Due: ৳<?= number_format($due_db) ?></div>
                        </td>
                        <td>
                            <span class="badge bg-<?= $status=='Paid'?'success':'danger' ?> status-badge mb-1"><?= $status ?></span>
                            <?php if($rec > 0): ?>
                                <div class="small text-muted">Self: ৳<?= number_format($rec-$mp) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="text-end pe-3">
                            <a href="admin.php?page=editbill&unit_id=<?= $unit_id ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">Details</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Mobile View (Card List) -->
    <div class="mobile-list px-2">
        <?php mysqli_data_seek($result, 0); while ($row = mysqli_fetch_assoc($result)): 
            // Repeat same logic fetch for mobile
            $unit_id = $row['id'];
            $unit_name = $row['unit_name'];
            $tenant_query = mysqli_query($db, "SELECT * FROM tenants WHERE building_id = '$building_id' AND unit_id = '$unit_id' LIMIT 1");
            $tenant = mysqli_fetch_assoc($tenant_query);
            $tent_id = $tenant['id'] ?? '';
            $name = $tenant['name'] ?? 'N/A';
            $image = !empty($tenant['tenant_image']) ? "public/uploads/tenants/" . $tenant['tenant_image'] : "public/uploads/tenants/no-image.png";
            
            $inv_query = mysqli_query($db, "SELECT * FROM invoices WHERE tenant_id = '$tent_id' AND billing_month = '$this_month' LIMIT 1");
            $inv = mysqli_fetch_assoc($inv_query);
            $total_bill = (float)($row['rent'] + ($inv['Gas']??0) + ($inv['Water']??0) + ($inv['Electricity']??0) + ($inv['Others']??0));
            $paid_db = (float)($inv['paid_amount'] ?? 0);
            $due_db = $total_bill - $paid_db;
            $status = $inv['status'] ?? 'No Invoice';

            $history_sql = mysqli_query($db, "SELECT SUM(paid_amount) as rec, SUM(manager_paid) as mp FROM payment_history WHERE tenant_id = '$tent_id' AND bill_month = '$this_month' AND payment_method = 'Manager'");
            $h = mysqli_fetch_assoc($history_sql);
            $rec = (float)$h['rec']; $mp = (float)$h['mp'];
        ?>
        <div class="tenant-card shadow-sm border-start <?= $status=='Paid' ? 'border-success' : 'border-danger' ?>">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex align-items-center">
                    <img src="<?= $image ?>" class="rounded-circle me-3" width="50" height="50" style="object-fit: cover; border: 2px solid #eee;">
                    <div>
                        <h6 class="fw-bold mb-0 text-primary"><?= $unit_name ?></h6>
                        <span class="small text-muted"><?= $name ?></span>
                    </div>
                </div>
                <span class="badge bg-<?= $status=='Paid'?'success':($status=='Unpaid'?'danger':'secondary') ?> status-badge">
                    <?= $status ?>
                </span>
            </div>
            
            <div class="row g-2 text-center mb-3">
                <div class="col-4 border-end">
                    <div class="mobile-label text-uppercase">Total Bill</div>
                    <div class="amount-text">৳<?= number_format($total_bill, 0) ?></div>
                </div>
                <div class="col-4 border-end">
                    <div class="mobile-label text-uppercase text-danger">Due</div>
                    <div class="amount-text text-danger">৳<?= number_format($due_db, 0) ?></div>
                </div>
                <div class="col-4">
                    <div class="mobile-label text-uppercase text-warning">Self</div>
                    <div class="amount-text text-warning">৳<?= number_format($rec-$mp, 0) ?></div>
                </div>
            </div>

            <div class="d-flex gap-2">
                <a href="admin.php?page=editbill&unit_id=<?= $unit_id ?>" class="btn btn-primary btn-sm flex-grow-1 rounded-pill py-2">
                    <i class="fas fa-file-invoice me-1"></i> Billing & Details
                </a>
                <a href="admin.php?page=view_tenant&id=<?= $tent_id ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2">
                    <i class="fas fa-user"></i>
                </a>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>