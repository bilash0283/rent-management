<?php
// বর্তমান মাসের ফরম্যাট (Y-m)
$this_month = date('Y-m');
$year = date('Y');

// ১. ডাটাবেস কোয়েরি অপ্টিমাইজেশন (শুধুমাত্র কাউন্ট নেওয়া হচ্ছে)
$total_building = mysqli_num_rows(mysqli_query($db, "SELECT id FROM building"));
$total_unit     = mysqli_num_rows(mysqli_query($db, "SELECT id FROM unit"));
$total_tenant   = mysqli_num_rows(mysqli_query($db, "SELECT id FROM tenants"));
$total_user     = mysqli_num_rows(mysqli_query($db, "SELECT id FROM users"));

// ২. বর্তমান মাসের সামারি
$sum_res = mysqli_query($db, "SELECT SUM(total_amount) AS bill, SUM(paid_amount) AS paid 
                               FROM invoices WHERE billing_month = '$this_month'");
$summary = mysqli_fetch_assoc($sum_res);

$raw_bill = (float)($summary['bill'] ?? 0);
$raw_paid = (float)($summary['paid'] ?? 0);
$raw_due  = $raw_bill - $raw_paid;

// ৩. চার্ট ডাটা (১২ মাস)
$monthly_totals = [];
for ($m = 1; $m <= 12; $m++) {
    $key = $year . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
    $monthly_totals[$key] = ['bill' => 0, 'paid' => 0];
}

$invoice_q = mysqli_query($db, "SELECT total_amount, paid_amount, billing_month 
                                FROM invoices WHERE billing_month LIKE '$year-%'");

while ($row = mysqli_fetch_assoc($invoice_q)) {
    $m_key = $row['billing_month'];
    if (isset($monthly_totals[$m_key])) {
        $monthly_totals[$m_key]['bill'] += (float)$row['total_amount'];
        $monthly_totals[$m_key]['paid'] += (float)$row['paid_amount'];
    }
}

$chart_labels = []; $chart_bills = []; $chart_paids = []; $chart_dues = [];

foreach ($monthly_totals as $month => $data) {
    $chart_labels[] = date('M', strtotime($month . '-01'));
    $calc_due = $data['bill'] - $data['paid'];
    $chart_bills[] = $data['bill'] / 1000; // k তে কনভার্ট
    $chart_paids[] = $data['paid'] / 1000;
    $chart_dues[]  = ($calc_due > 0 ? $calc_due : 0) / 1000;
}


// building query  
$building_sql = mysqli_query($db,"SELECT * FROM building");
$building_info = mysqli_fetch_assoc($building_sql);
$building_id = $building_info['id'];
?>

<style>
    .dashboard-card { transition: transform 0.2s; border: none !important; }
    .dashboard-card:hover { transform: translateY(-5px); }
    .icon-circle { width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; border-radius: 10px; }
    @media (max-width: 768px) {
        .icon-circle { width: 35px; height: 35px; }
        .card-body h3 { font-size: 1.2rem; }
        .card-body h6 { font-size: 13px; }
    }
</style>

<div class="nxl-content">
    <div class="main-content p-3">
        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <!-- Buildings -->
            <div class="col-6 col-lg-3">
                <a href="admin.php?page=building" class="text-decoration-none">
                    <div class="card dashboard-card shadow-sm rounded-4">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="icon-circle bg-primary-subtle text-primary"><i class="fas fa-building"></i></div>
                                <h3 class="fw-bold text-primary mb-0"><?= $total_building ?></h3>
                            </div>
                            <h6 class="mt-2 mb-0 text-dark">Buildings</h6>
                        </div>
                    </div>
                </a>
            </div>
            <!-- Units -->
            <div class="col-6 col-lg-3">
                <a href="admin.php?page=unit&id=<?php echo $building_id; ?>" class="text-decoration-none">
                    <div class="card dashboard-card shadow-sm rounded-4">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="icon-circle bg-success-subtle text-success"><i class="fas fa-door-open"></i></div>
                                <h3 class="fw-bold text-success mb-0"><?= $total_unit ?></h3>
                            </div>
                            <h6 class="mt-2 mb-0 text-dark">Units</h6>
                        </div>
                    </div>
                </a>
            </div>
            <!-- Tenants -->
            <div class="col-6 col-lg-3">
                <a href="admin.php?page=tenant&building_id=<?php echo $building_id; ?>" class="text-decoration-none">
                    <div class="card dashboard-card shadow-sm rounded-4">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="icon-circle bg-warning-subtle text-warning"><i class="fas fa-users"></i></div>
                                <h3 class="fw-bold text-warning mb-0"><?= $total_tenant ?></h3>
                            </div>
                            <h6 class="mt-2 mb-0 text-dark">Tenants</h6>
                        </div>
                    </div>
                </a>
            </div>
            <!-- Users -->
            <div class="col-6 col-lg-3">
                <a href="admin.php?page=users" class="text-decoration-none">
                    <div class="card dashboard-card shadow-sm rounded-4">
                        <div class="card-body p-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="icon-circle bg-info-subtle text-info"><i class="fas fa-user-shield"></i></div>
                                <h3 class="fw-bold text-info mb-0"><?= $total_user ?></h3>
                            </div>
                            <h6 class="mt-2 mb-0 text-dark">Users</h6>
                        </div>
                    </div>
                </a>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="card shadow-sm border-0 rounded-4">
            <div class="card-header bg-transparent pt-4 px-4 border-0">
                <h5 class="fw-bold">Payment Record (<?= $year ?>)</h5>
            </div>
            <div class="card-body px-2">
                <div style="height: 320px; position: relative;">
                    <canvas id="paymentChartCanvas"></canvas>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0 pb-4">
                <div class="row g-2">
                    <div class="col-4">
                        <div class="p-2 border border-dashed rounded text-center">
                            <small class="text-muted d-block">Bill</small>
                            <span class="fw-bold small">৳<?= number_format($raw_bill, 0) ?></span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border border-dashed rounded text-center">
                            <small class="text-muted d-block">Paid</small>
                            <span class="fw-bold text-success small">৳<?= number_format($raw_paid, 0) ?></span>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 border border-dashed rounded text-center">
                            <small class="text-muted d-block">Due</small>
                            <span class="fw-bold text-danger small">৳<?= number_format($raw_due, 0) ?></span>
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
                { label: 'Bill', data: <?= json_encode($chart_bills) ?>, borderColor: '#3b82f6', backgroundColor: 'transparent', tension: 0.3, pointRadius: 2 },
                { label: 'Paid', data: <?= json_encode($chart_paids) ?>, borderColor: '#10b981', backgroundColor: 'transparent', tension: 0.3, pointRadius: 2 },
                { label: 'Due', data: <?= json_encode($chart_dues) ?>, borderColor: '#ef4444', backgroundColor: 'transparent', tension: 0.3, pointRadius: 2 }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 10, font: { size: 11 } } } },
            scales: {
                y: { beginAtZero: true, ticks: { font: { size: 10 }, callback: v => v + 'k' } },
                x: { ticks: { font: { size: 10 } } }
            }
        }
    });
</script>