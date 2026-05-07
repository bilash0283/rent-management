<?php
// logic অংশ আগের মতোই থাকবে
$building_id = '';
$this_month_only = date('m'); 
$this_year = date('Y'); 

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['year']) && !empty($_POST['year'])) {
        $this_year = mysqli_real_escape_string($db, $_POST['year']);
    }
    if (isset($_POST['month']) && !empty($_POST['month'])) {
        $this_month_only = mysqli_real_escape_string($db, $_POST['month']);
    }
    if (isset($_POST['building']) && !empty($_POST['building'])) {
        $building_id = mysqli_real_escape_string($db, $_POST['building']);
    }
}

$this_month = $this_year . '-' . str_pad($this_month_only, 2, '0', STR_PAD_LEFT);

if (empty($building_id)) {
    if (!isset($_GET['id']) || empty($_GET['id'])) {
        echo '<div class="alert alert-danger">Invalid Building ID</div>';
        exit;
    }
    $building_id = mysqli_real_escape_string($db, $_GET['id']);
}

$buil_sql = mysqli_query($db, "SELECT name FROM building WHERE id = '$building_id'");
$building_row = mysqli_fetch_assoc($buil_sql);
$building_name_db = $building_row['name'] ?? 'Unknown Building';

$query = "SELECT * FROM unit WHERE building_name = '$building_id' AND status = 'Rented'";
$result = mysqli_query($db, $query);
$total_unit = mysqli_num_rows($result);
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<div class="nxl-content px-2 px-md-4 py-3">
    <!-- Header Section -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-body p-3 p-md-4">
            <div class="row align-items-center">
                <div class="col-12 col-lg-5 d-flex align-items-center mb-3 mb-lg-0">
                    <div class="icon-box bg-primary text-white me-3 d-flex align-items-center justify-content-center rounded-3" style="width: 50px; height: 50px;">
                        <i class="fas fa-city fs-5"></i>
                    </div>
                    <div>
                        <h5 class="mb-0 fw-bold text-dark" style="font-size: 1.15rem;"><?= htmlspecialchars($building_name_db) ?></h5>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="badge bg-soft-success text-success fw-medium">
                                <i class="fas fa-door-open me-1"></i><?= $total_unit ?> Units
                            </span>
                            <span class="text-muted" style="font-size: 0.85rem;">
                                <i class="far fa-calendar-check me-1"></i> <?= date('M Y', strtotime($this_month . '-01')) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Filter Form -->
                <div class="col-12 col-lg-7">
                    <form method="POST" class="row g-2 justify-content-lg-end">
                        <div class="col-4 col-md-3">
                            <select name="year" class="form-select form-select-sm custom-select">
                                <?php 
                                $startYear = 2025;
                                $endYear = date('Y') + 1;
                                for ($y = $startYear; $y <= $endYear; $y++): ?>
                                    <option value="<?= $y ?>" <?= $y == $this_year ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-4 col-md-3">
                            <select name="month" class="form-select form-select-sm custom-select">
                                <?php 
                                for ($m = 1; $m <= 12; $m++): 
                                    $monthValue = str_pad($m, 2, '0', STR_PAD_LEFT);
                                    $displayText = date('M', mktime(0, 0, 0, $m, 1));
                                ?>
                                    <option value="<?= $monthValue ?>" <?= $m == (int)$this_month_only ? 'selected' : '' ?>><?= $displayText ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                        <div class="col-4 col-md-4">
                            <select name="building" class="form-select form-select-sm custom-select">
                                <?php
                                $buildings_sql = mysqli_query($db, "SELECT id, name FROM building ORDER BY name ASC");
                                while ($buil = mysqli_fetch_assoc($buildings_sql)) {
                                    $selected = ($buil['id'] == $building_id) ? 'selected' : '';
                                    echo "<option value='{$buil['id']}' $selected>{$buil['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-2 mt-2 mt-md-0">
                            <button type="submit" class="btn btn-primary btn-sm w-100 fw-bold shadow-sm">
                                <i class="fas fa-search me-1"></i> Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics -->
    <?php
        $manager_summary = mysqli_query($db, "SELECT SUM(ph.paid_amount) as total_received, SUM(ph.manager_paid) as manager_paid_total FROM payment_history ph JOIN tenants t ON ph.tenant_id = t.id WHERE t.building_id = '$building_id' AND ph.bill_month = '$this_month' AND ph.payment_method = 'Manager'");
        $summary = mysqli_fetch_assoc($manager_summary);
        $total_received = (float) ($summary['total_received'] ?? 0);

        $pay_info_total = mysqli_query($db, "SELECT SUM(total_amount) as total_bill, SUM(paid_amount) as total_paid FROM invoices inv JOIN tenants t ON inv.tenant_id = t.id WHERE t.building_id = '$building_id' AND inv.billing_month = '$this_month'");
        $row_total = mysqli_fetch_assoc($pay_info_total);
        $total_bill_amount = (float)$row_total['total_bill'];
        $paid_amount_db_amount = (float)$row_total['total_paid'];
        $due_amount_db_amount = max($total_bill_amount - $paid_amount_db_amount, 0);
    ?>

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 stat-card bg-white border-start border-primary border-4">
                <div class="card-body p-3">
                    <p class="text-muted mb-1 fw-medium" style="font-size: 0.75rem; text-transform: uppercase;">Total Bill</p>
                    <h4 class="mb-0 fw-bold">৳<?= number_format($total_bill_amount, 0) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 stat-card bg-white border-start border-success border-4">
                <div class="card-body p-3">
                    <p class="text-muted mb-1 fw-medium" style="font-size: 0.75rem; text-transform: uppercase;">Received</p>
                    <h4 class="mb-0 fw-bold text-success">৳<?= number_format($paid_amount_db_amount, 0) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 stat-card bg-white border-start border-danger border-4">
                <div class="card-body p-3">
                    <p class="text-muted mb-1 fw-medium" style="font-size: 0.75rem; text-transform: uppercase;">Total Due</p>
                    <h4 class="mb-0 fw-bold text-danger">৳<?= number_format($due_amount_db_amount, 0) ?></h4>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-3 stat-card bg-white border-start border-dark border-4">
                <div class="card-body p-3">
                    <p class="text-muted mb-1 fw-medium" style="font-size: 0.75rem; text-transform: uppercase;">Mgr Collect</p>
                    <h4 class="mb-0 fw-bold">৳<?= number_format($total_received, 0) ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- Details List -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold text-dark">Tenant Billing List</h6>
            <span class="text-muted" style="font-size: 0.8rem;">Live Data</span>
        </div>
        
        <div class="card-body p-0">
            <!-- Desktop View -->
            <div class="table-responsive d-none d-md-block">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="text-muted" style="font-size: 0.85rem;">
                            <th class="ps-4">UNIT & TENANT</th>
                            <th>BILL DETAILS</th>
                            <th>STATUS</th>
                            <th>COLLECTION</th>
                            <th class="text-end pe-4">ACTION</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        mysqli_data_seek($result, 0);
                        while ($row = mysqli_fetch_assoc($result)): 
                            $unit_id = $row['id'];
                            $sql_tenant = mysqli_query($db, "SELECT * FROM tenants WHERE building_id = '$building_id' AND unit_id = '$unit_id'");
                            $tent_row = mysqli_fetch_assoc($sql_tenant);
                            $tent_id = $tent_row['id'] ?? '';
                            $image = !empty($tent_row['tenant_image']) ? "public/uploads/tenants/" . $tent_row['tenant_image'] : "public/uploads/tenants/no-image.png";
                            
                            $pay_info = mysqli_query($db, "SELECT * FROM invoices WHERE tenant_id = '$tent_id' AND unit_id = '$unit_id' AND billing_month = '$this_month'");
                            $inv = mysqli_fetch_assoc($pay_info);
                        ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <img src="<?= $image ?>" width="40" height="40" class="rounded-circle me-3 border" style="object-fit: cover;">
                                    <div>
                                        <div class="fw-bold text-dark mb-0" style="font-size: 0.9rem;"><?= $row['unit_name'] ?></div>
                                        <a href="admin.php?page=view_tenant&id=<?= $tent_id ?>" class="text-muted small"><?= $tent_row['name'] ?? 'Vacant' ?></a>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php if($inv): ?>
                                    <div class="text-dark fw-medium" style="font-size: 0.85rem;">৳<?= number_format($inv['total_amount'],0) ?></div>
                                    <div class="text-success" style="font-size: 0.75rem;">Paid: ৳<?= number_format($inv['paid_amount'],0) ?></div>
                                <?php else: ?>
                                    <span class="text-danger small">No Invoice</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($inv): ?>
                                    <span class="badge rounded-pill bg-<?= ($inv['status']=='Paid')?'success':(($inv['status']=='Partial')?'warning':'danger') ?>-subtle text-<?= ($inv['status']=='Paid')?'success':(($inv['status']=='Partial')?'warning':'danger') ?> px-2 py-1" style="font-size: 0.7rem;">
                                        <?= $inv['status'] ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small" style="font-size: 0.75rem;">
                                <?php 
                                $hist_sql = mysqli_query($db, "SELECT payment_method, paid_amount FROM payment_history WHERE tenant_id = '$tent_id' AND bill_month = '$this_month'");
                                while($h = mysqli_fetch_assoc($hist_sql)) echo $h['payment_method']." (".number_format($h['paid_amount']).")<br>";
                                ?>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="admin.php?page=editbill&unit_id=<?= $unit_id ?>" class="btn btn-outline-light btn-sm text-primary border-0"><i class="fas fa-eye"></i></a>
                                    <a href="admin.php?page=bill&unit_id=<?= $unit_id ?>" class="btn btn-outline-light btn-sm text-success border-0"><i class="fab fa-whatsapp"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

            <!-- Mobile View -->
            <div class="d-block d-md-none">
                <?php 
                mysqli_data_seek($result, 0);
                while ($row = mysqli_fetch_assoc($result)): 
                    $unit_id = $row['id'];
                    $sql_tenant = mysqli_query($db, "SELECT * FROM tenants WHERE building_id = '$building_id' AND unit_id = '$unit_id'");
                    $tent_row = mysqli_fetch_assoc($sql_tenant);
                    $tent_id = $tent_row['id'] ?? '';
                    $image = !empty($tent_row['tenant_image']) ? "public/uploads/tenants/" . $tent_row['tenant_image'] : "public/uploads/tenants/no-image.png";
                    $pay_info = mysqli_query($db, "SELECT * FROM invoices WHERE tenant_id = '$tent_id' AND unit_id = '$unit_id' AND billing_month = '$this_month'");
                    $inv = mysqli_fetch_assoc($pay_info);
                    $status = $inv['status'] ?? 'None';
                ?>
                <div class="mobile-card p-3 border-bottom position-relative">
                    <div class="d-flex align-items-center mb-3">
                        <img src="<?= $image ?>" width="45" height="45" class="rounded-circle me-3 border shadow-sm">
                        <div class="flex-grow-1">
                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.95rem;"><?= $row['unit_name'] ?> - <?= $tent_row['name'] ?? 'No Tenant' ?></h6>
                            <span class="badge bg-<?= ($status=='Paid')?'success':(($status=='Partial')?'warning':(($status=='Unpaid')?'danger':'secondary')) ?>-subtle text-<?= ($status=='Paid')?'success':(($status=='Partial')?'warning':(($status=='Unpaid')?'danger':'secondary')) ?> mt-1" style="font-size: 0.65rem;">
                                <?= $status ?>
                            </span>
                        </div>
                        <div class="dropdown">
                            <button class="btn btn-light btn-sm rounded-circle" type="button" data-bs-toggle="dropdown"><i class="fas fa-ellipsis-v"></i></button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><a class="dropdown-item small" href="admin.php?page=editbill&unit_id=<?= $unit_id ?>"><i class="fas fa-file-invoice me-2"></i> Details</a></li>
                                <li><a class="dropdown-item small text-success" href="admin.php?page=bill&unit_id=<?= $unit_id ?>"><i class="fab fa-whatsapp me-2"></i> WhatsApp</a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="row g-2 text-center bg-light rounded-3 p-2 mx-0">
                        <div class="col-4 border-end">
                            <span class="text-muted d-block" style="font-size: 0.65rem;">TOTAL</span>
                            <span class="fw-bold text-dark" style="font-size: 0.85rem;">৳<?= number_format($inv['total_amount'] ?? 0, 0) ?></span>
                        </div>
                        <div class="col-4 border-end">
                            <span class="text-muted d-block" style="font-size: 0.65rem;">PAID</span>
                            <span class="fw-bold text-success" style="font-size: 0.85rem;">৳<?= number_format($inv['paid_amount'] ?? 0, 0) ?></span>
                        </div>
                        <div class="col-4">
                            <span class="text-muted d-block" style="font-size: 0.65rem;">DUE</span>
                            <span class="fw-bold text-danger" style="font-size: 0.85rem;">৳<?= number_format(($inv['total_amount'] ?? 0) - ($inv['paid_amount'] ?? 0), 0) ?></span>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
</div>

<style>
:root {
    --primary-color: #4361ee;
    --success-color: #2ec4b6;
    --danger-color: #e71d36;
}

body {
    background-color: #f8f9fa;
    font-family: 'Inter', sans-serif;
}

.bg-soft-success { background-color: rgba(46, 196, 182, 0.1); }
.bg-success-subtle { background-color: rgba(25, 135, 84, 0.1); }
.bg-danger-subtle { background-color: rgba(220, 53, 69, 0.1); }
.bg-warning-subtle { background-color: rgba(255, 193, 7, 0.1); }

.custom-select {
    border: 1px solid #edf2f7;
    background-color: #fcfcfc;
    font-size: 0.8rem !important;
    border-radius: 8px;
    padding: 0.4rem 0.5rem;
}

.stat-card h4 {
    font-size: 1.25rem;
    letter-spacing: -0.5px;
}

.mobile-card:last-child { border-bottom: none; }

.btn-primary {
    background-color: var(--primary-color);
    border: none;
    padding: 0.5rem;
    border-radius: 8px;
}

@media (max-width: 768px) {
    .nxl-content { padding-top: 15px; }
    .stat-card h4 { font-size: 1.1rem; }
    .page-header h5 { font-size: 1rem; }
}
</style>