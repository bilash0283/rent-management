<?php
// building 
$builiding = mysqli_query($db, "SELECT * FROM building ");
$total_building = mysqli_num_rows($builiding);
while ($buill_info = mysqli_fetch_assoc($builiding)) {
    $buill_id = $buill_info['id'];
}

// unit 
$unit = mysqli_query($db, "SELECT * FROM unit ");
$total_unit = mysqli_num_rows($unit);
while ($unit_info = mysqli_fetch_assoc($unit)) {
    $unit_id = $unit_info['id'];
    $rent = intval($unit_info['rent']);
    $Gas = intval($unit_info['Gas']);
    $Water = intval($unit_info['Water']);
    $Electricity = intval($unit_info['Electricity']);
    $Internet = intval($unit_info['Internet']);
    $Maintenance = intval($unit_info['Maintenance']);
    $Others = intval($unit_info['Others']);

    $total_bill = $rent + $Gas + $Water + $Electricity + $Maintenance + $Others;

    $advance = intval($unit_info['advance']);
}

// Invoice 
$invoice = mysqli_query($db, "SELECT * FROM invoices ");
$total_invoice = mysqli_num_rows($invoice);
while ($invoice_info = mysqli_fetch_assoc($invoice)) {
    $invoice_id = $invoice_info['id'];
    $tenant_id = $invoice_info['tenant_id'];
    $unit_id = $invoice_info['unit_id'];
    $billing_month = $invoice_info['billing_month'];
    $total_amount = $invoice_info['total_amount'];
    $paid_amount = $invoice_info['paid_amount'];
    $due_amount = $invoice_info['due_amount'];
    $status = $invoice_info['status'];
}

// tenant 
$tenant = mysqli_query($db, "SELECT * FROM tenants ");
$total_tenant = mysqli_num_rows($tenant);

while ($tenant_info = mysqli_fetch_assoc($tenant)) {
    $tenant_id = $tenant_info['id'];
}

// total bill,paid,due calculation 
$result = mysqli_query($db, "SELECT 
SUM(total_amount) AS total_bill,
SUM(paid_amount)  AS total_paid,
SUM(due_amount)   AS total_due
FROM invoices WHERE billing_month = '$this_month' ");

$summary = mysqli_fetch_assoc($result);

// Fallback to 0 if no rows or NULL sums
$total_bill = number_format($summary['total_bill'] ?? 0, 2);
$total_paid = number_format($summary['total_paid'] ?? 0, 2);
$total_due = number_format($summary['total_due'] ?? 0, 2);

// Optional: also get total number of invoices
$count_result = mysqli_query($db, "SELECT COUNT(*) AS total_invoices FROM invoices WHERE billing_month = '$this_month' ");
$count_row = mysqli_fetch_assoc($count_result);
$total_invoices = $count_row['total_invoices'] ?? 0;

?>

<div class="nxl-content">
    <!-- [ Main Content ] start -->
    <div class="main-content">
        <div class="row">

            <!-- Dashboard Cards -->
            <div class="row">
                <!-- Building Card -->
                <div class="col-lg-4">
                    <a href="admin.php?page=building" class="text-decoration-none">
                        <div class="card dashboard-card shadow-sm border-0 rounded-4 mb-4">
                            <div class="card-body d-flex justify-content-between align-items-center">

                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle bg-primary-subtle text-primary">
                                        <i class="fas fa-building fa-lg"></i>
                                    </div>

                                    <div>
                                        <h6 class="mb-1 fw-bold text-dark">Total Buildings</h6>
                                        <small class="text-muted">All registered buildings</small>
                                    </div>
                                </div>

                                <h3 class="fw-bold text-primary mb-0">
                                    <?= $total_building ?>
                                </h3>

                            </div>
                        </div>
                    </a>
                </div>
                <!-- Unit Card -->
                <div class="col-lg-4">
                    <a href="admin.php?page=unit&id=<?= $buill_id ?>" class="text-decoration-none">
                        <div class="card dashboard-card shadow-sm border-0 rounded-4 mb-4">
                            <div class="card-body d-flex justify-content-between align-items-center">

                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle bg-success-subtle text-success">
                                        <i class="fas fa-door-open fa-lg"></i>
                                    </div>

                                    <div>
                                        <h6 class="mb-1 fw-bold text-dark">Total Units</h6>
                                        <small class="text-muted">Available & Occupied</small>
                                    </div>
                                </div>

                                <h3 class="fw-bold text-success mb-0">
                                    <?= $total_unit ?>
                                </h3>

                            </div>
                        </div>
                    </a>
                </div>
                <!-- Tenant Card -->
                <div class="col-lg-4">
                    <a href="admin.php?page=tenant" class="text-decoration-none">
                        <div class="card dashboard-card shadow-sm border-0 rounded-4 mb-4">
                            <div class="card-body d-flex justify-content-between align-items-center">

                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle bg-warning-subtle text-warning">
                                        <i class="fas fa-users fa-lg"></i>
                                    </div>

                                    <div>
                                        <h6 class="mb-1 fw-bold text-dark">Total Tenants</h6>
                                        <small class="text-muted">Currently Active</small>
                                    </div>
                                </div>

                                <h3 class="fw-bold text-warning mb-0">
                                    <?= $total_tenant ?>
                                </h3>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
            <!-- Dashboard Cards -->

            <!-- [Payment Records] end -->
            <div class="col-xxl-8">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Monthly Payment Record</h5>
                    </div>
                    <div class="card-body custom-card-action p-0">
                        <div id="payment-records-chart"></div>
                    </div>
                    <div class="card-footer">
                        <div class="row g-4">
                            <div class="col-lg-4">
                                <div class="p-3 border border-dashed rounded">
                                    <div class="fs-12 text-muted mb-1">Total Bills</div>
                                    <h6 class="fw-bold text-dark"><small>৳</small> <?= $total_bill ?></h6>
                                    <div class="progress mt-2 ht-3">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 81%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="p-3 border border-dashed rounded">
                                    <div class="fs-12 text-muted mb-1">Total Paid</div>
                                    <h6 class="fw-bold text-dark"><small>৳</small> <?= $total_paid ?></h6>
                                    <div class="progress mt-2 ht-3">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 82%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="p-3 border border-dashed rounded">
                                    <div class="fs-12 text-muted mb-1">Total Due</div>
                                    <h6 class="fw-bold text-dark"><small>৳</small> <?= $total_due ?></h6>
                                    <div class="progress mt-2 ht-3">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: 68%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [Payment Records] end -->

        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>