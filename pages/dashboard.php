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
<?php
// === CURRENT YEAR এর জন্য 12 মাস প্রি-ফিল করো ===
$year = date('Y'); // 2026

$monthly_totals = [];
for ($m = 1; $m <= 12; $m++) {
    $key = $year . '-' . str_pad($m, 2, '0', STR_PAD_LEFT); // 2026-01, 2026-02, ...
    $monthly_totals[$key] = [
        'total_amount' => 0,
        'paid_amount'  => 0,
        'due_amount'   => 0
    ];
}

// === ডাটাবেজ থেকে ডাটা নাও ===
$invoice = mysqli_query($db, "SELECT * FROM invoices ORDER BY billing_month ASC");

while ($row = mysqli_fetch_assoc($invoice)) {
    $month_key = $row['billing_month'];   // আশা করি এটা "2026-02" ফরম্যাটে আছে

    if (isset($monthly_totals[$month_key])) {
        $monthly_totals[$month_key]['total_amount'] += (float)$row['total_amount'];
        $monthly_totals[$month_key]['paid_amount']  += (float)$row['paid_amount'];
        $monthly_totals[$month_key]['due_amount']   += (float)$row['due_amount'];
    }
}

// === Chart.js এর জন্য arrays তৈরি করো ===
$chart_labels = [];
$chart_bills  = [];
$chart_paids  = [];
$chart_dues   = [];

foreach ($monthly_totals as $month => $data) {
    $display_month = date('M Y', strtotime($month . '-01')); // Feb 2026
    $chart_labels[] = $display_month;
    $chart_bills[]  = $data['total_amount'] / 1000;
    $chart_paids[]  = $data['paid_amount']  / 1000;
    $chart_dues[]   = $data['due_amount']   / 1000;
}
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

            <div class="col-xxl-8">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title"> Payment Record (Last 12 Months)</h5>
                    </div>
                    <div class="card-body custom-card-action p-0" style="height: 420px;">
                        <canvas id="paymentChartCanvas" style="width:100%; height:100%;"></canvas>
                    </div>
                    <div class="card-footer">
                        <div class="row g-4">
                            <div class="col-lg-4">
                                <div class="p-3 border border-dashed rounded">
                                    <div class="fs-12 text-muted mb-1">Total Bills (this month)</div>
                                    <h6 class="fw-bold text-dark"><small>৳</small> <?= $total_bill ?></h6>
                                    <div class="progress mt-2 ht-3">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 81%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="p-3 border border-dashed rounded">
                                    <div class="fs-12 text-muted mb-1">Total Paid (this month)</div>
                                    <h6 class="fw-bold text-dark"><small>৳</small> <?= $total_paid ?></h6>
                                    <div class="progress mt-2 ht-3">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 82%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <div class="p-3 border border-dashed rounded">
                                    <div class="fs-12 text-muted mb-1">Total Due (this month)</div>
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
            
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
<script>
    const monthLabels = <?php echo json_encode($chart_labels); ?>;
    const billData    = <?php echo json_encode($chart_bills); ?>;
    const paidData    = <?php echo json_encode($chart_paids); ?>;
    const dueData     = <?php echo json_encode($chart_dues); ?>;

    console.log("Chart data:", { labels: monthLabels, bills: billData, paids: paidData, dues: dueData });

    const ctx = document.getElementById('paymentChartCanvas').getContext('2d');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: monthLabels,
            datasets: [
                { label: 'Total Bill (thousands ৳)', data: billData, borderColor: '#3b82f6', backgroundColor: 'rgba(59, 130, 246, 0.12)', tension: 0.3, fill: true, pointRadius: 4 },
                { label: 'Total Paid (thousands ৳)', data: paidData, borderColor: '#10b981', backgroundColor: 'rgba(16, 185, 129, 0.12)', tension: 0.3, fill: true, pointRadius: 4 },
                { label: 'Total Due (thousands ৳)', data: dueData, borderColor: '#ef4444', backgroundColor: 'rgba(239, 68, 68, 0.12)', tension: 0.3, fill: true, pointRadius: 4 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let value = context.parsed.y;
                            return `${context.dataset.label}: ৳ ${(value * 1000).toLocaleString()}`;
                        }
                    }
                }
            },
            scales: {
                y: { beginAtZero: true, title: { display: true, text: 'Amount (× 1,000 ৳)' } },
                x: { title: { display: true, text: 'Month' } }
            }
        }
    });
</script>