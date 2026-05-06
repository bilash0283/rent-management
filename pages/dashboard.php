<?php
// বর্তমান মাসের ফরম্যাট (Y-m)
$this_month = date('Y-m');
$year = date('Y');

// ১. বিল্ডিং ডাটা
$builiding_q = mysqli_query($db, "SELECT * FROM building");
$total_building = mysqli_num_rows($builiding_q);
// শেষ বিল্ডিং আইডি স্টোর হচ্ছে লিংকের জন্য
while ($buill_info = mysqli_fetch_assoc($builiding_q)) {
    $buill_id = $buill_info['id'];
}

// ২. ইউনিট ডাটা
$unit_q = mysqli_query($db, "SELECT * FROM unit");
$total_unit = mysqli_num_rows($unit_q);

// ৩. টেন্যান্ট ডাটা
$tenant_q = mysqli_query($db, "SELECT * FROM tenants");
$total_tenant = mysqli_num_rows($tenant_q);

// ৪. বর্তমান মাসের সামারি (Bill, Paid, Due Calculation)
$result = mysqli_query($db, "SELECT 
    SUM(total_amount) AS total_bill,
    SUM(paid_amount) AS total_paid
    FROM invoices WHERE billing_month = '$this_month'");

$summary = mysqli_fetch_assoc($result);

// আপনার রিকোয়েস্ট অনুযায়ী Due = Total Bill - Total Paid
$raw_bill = (float)($summary['total_bill'] ?? 0);
$raw_paid = (float)($summary['total_paid'] ?? 0);
$raw_due  = $raw_bill - $raw_paid; // বিয়োগফল

// ফরম্যাটিং
$total_bill_fmt = number_format($raw_bill, 0);
$total_paid_fmt = number_format($raw_paid, 0);
$total_due_fmt  = number_format($raw_due, 0);

// ইনভয়েস কাউন্ট
$count_result = mysqli_query($db, "SELECT COUNT(*) AS total_invoices FROM invoices WHERE billing_month = '$this_month'");
$count_row = mysqli_fetch_assoc($count_result);
$total_invoices = $count_row['total_invoices'] ?? 0;

// ৫. চার্ট ডাটা প্রিপারেশন (১২ মাস)
$monthly_totals = [];
for ($m = 1; $m <= 12; $m++) {
    $key = $year . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
    $monthly_totals[$key] = [
        'total_amount' => 0,
        'paid_amount' => 0,
        'due_amount' => 0
    ];
}

$invoice_q = mysqli_query($db, "SELECT total_amount, paid_amount, billing_month FROM invoices WHERE billing_month LIKE '$year-%' ORDER BY billing_month ASC");

while ($row = mysqli_fetch_assoc($invoice_q)) {
    $month_key = $row['billing_month'];
    if (isset($monthly_totals[$month_key])) {
        $monthly_totals[$month_key]['total_amount'] += (float) $row['total_amount'];
        $monthly_totals[$month_key]['paid_amount'] += (float) $row['paid_amount'];
    }
}

$chart_labels = [];
$chart_bills = [];
$chart_paids = [];
$chart_dues = [];

foreach ($monthly_totals as $month => $data) {
    $display_month = date('M Y', strtotime($month . '-01'));
    $chart_labels[] = $display_month;
    
    // চার্টের জন্যও Due = Bill - Paid লজিক
    $calc_due = $data['total_amount'] - $data['paid_amount'];
    
    $chart_bills[] = $data['total_amount'] / 1000;
    $chart_paids[] = $data['paid_amount'] / 1000;
    $chart_dues[]  = ($calc_due > 0 ? $calc_due : 0) / 1000; // নেগেটিভ এড়াতে
}
?>

<div class="nxl-content">
    <div class="main-content">
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
                            <h3 class="fw-bold text-primary mb-0"><?= $total_building ?></h3>
                        </div>
                    </div>
                </a>
            </div>

            <!-- Unit Card -->
            <div class="col-lg-4">
                <a href="admin.php?page=unit" class="text-decoration-none">
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
                            <h3 class="fw-bold text-success mb-0"><?= $total_unit ?></h3>
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
                            <h3 class="fw-bold text-warning mb-0"><?= $total_tenant ?></h3>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row">
            <div class="col-xxl-12">
                <div class="card stretch stretch-full shadow-sm">
                    <div class="card-header">
                        <h5 class="card-title">Payment Record (<?= $year ?>)</h5>
                    </div>
                    <div class="card-body p-0" style="height: 400px; position: relative;">
                        <canvas id="paymentChartCanvas"></canvas>
                    </div>
                    <div class="card-footer">
                        <div class="row g-4">
                            <div class="col-lg-4">
                                <div class="p-3 border border-dashed rounded">
                                    <div class="fs-12 text-muted mb-1">Total Bills (<?= date('M') ?>)</div>
                                    <h6 class="fw-bold text-dark"><small>৳</small> <?= $total_bill_fmt ?></h6>
                                    <div class="progress mt-2" style="height: 4px;">
                                        <div class="progress-bar bg-primary" style="width: 100%"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="p-3 border border-dashed rounded">
                                    <div class="fs-12 text-muted mb-1">Total Paid (<?= date('M') ?>)</div>
                                    <h6 class="fw-bold text-dark"><small>৳</small> <?= $total_paid_fmt ?></h6>
                                    <?php 
                                        $paid_percent = ($raw_bill > 0) ? round(($raw_paid / $raw_bill) * 100, 1) : 0;
                                    ?>
                                    <div class="progress mt-2" style="height: 4px;">
                                        <div class="progress-bar bg-success" style="width: <?= $paid_percent ?>%"></div>
                                    </div>
                                    <small class="text-muted"><?= $paid_percent ?>% collected</small>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="p-3 border border-dashed rounded">
                                    <div class="fs-12 text-muted mb-1">Total Due (<?= date('M') ?>)</div>
                                    <h6 class="fw-bold text-dark"><small>৳</small> <?= $total_due_fmt ?></h6>
                                    <?php 
                                        $due_percent = ($raw_bill > 0) ? round(($raw_due / $raw_bill) * 100, 1) : 0;
                                    ?>
                                    <div class="progress mt-2" style="height: 4px;">
                                        <div class="progress-bar bg-danger" style="width: <?= $due_percent ?>%"></div>
                                    </div>
                                    <small class="text-muted"><?= $due_percent ?>% pending</small>
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
    const ctx = document.getElementById('paymentChartCanvas').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode($chart_labels) ?>,
            datasets: [
                { 
                    label: 'Bill', 
                    data: <?= json_encode($chart_bills) ?>, 
                    borderColor: '#3b82f6', 
                    backgroundColor: 'rgba(59, 130, 246, 0.05)', 
                    tension: 0.4, 
                    fill: true 
                },
                { 
                    label: 'Paid', 
                    data: <?= json_encode($chart_paids) ?>, 
                    borderColor: '#10b981', 
                    backgroundColor: 'rgba(16, 185, 129, 0.05)', 
                    tension: 0.4, 
                    fill: true 
                },
                { 
                    label: 'Due', 
                    data: <?= json_encode($chart_dues) ?>, 
                    borderColor: '#ef4444', 
                    backgroundColor: 'rgba(239, 68, 68, 0.05)', 
                    tension: 0.4, 
                    fill: true 
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: true, position: 'top' },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ৳ ' + (context.parsed.y * 1000).toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: { beginAtZero: true, grid: { drawBorder: false } },
                x: { grid: { display: false } }
            }
        }
    });
</script>