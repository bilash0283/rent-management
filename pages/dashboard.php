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
$tenant_q = mysqli_query($db, "SELECT * FROM tenants WHERE role IN ('Tenant') ORDER BY id DESC");
$total_tenant = mysqli_num_rows($tenant_q);

// users list data 
$user_sql = mysqli_query($db, "SELECT * FROM `tenants` WHERE role IN ('Admin','Manager') ORDER BY id DESC ");
$total_users = mysqli_num_rows($user_sql);

// ৪. বর্তমান মাসের সামারি (Bill, Paid, Due Calculation)
$result = mysqli_query($db, "SELECT 
        SUM(total_amount) AS total_bill,
        SUM(paid_amount) AS total_paid
        FROM invoices WHERE billing_month = '$this_month'");

$summary = mysqli_fetch_assoc($result);

// আপনার রিকোয়েস্ট অনুযায়ী Due = Total Bill - Total Paid
$raw_bill = (float) ($summary['total_bill'] ?? 0);
$raw_paid = (float) ($summary['total_paid'] ?? 0);
$raw_due = $raw_bill - $raw_paid; // বিয়োগফল

// ফরম্যাটিং
$total_bill_fmt = number_format($raw_bill, 0);
$total_paid_fmt = number_format($raw_paid, 0);
$total_due_fmt = number_format($raw_due, 0);

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
    $chart_dues[] = ($calc_due > 0 ? $calc_due : 0) / 1000; // নেগেটিভ এড়াতে
}
?>

<div class="nxl-content">
    <div class="main-content">
        <?php if ($_SESSION['role'] == 'Admin') { ?>
            <div class="row">
                <!-- Building Card -->
                <div class="col-lg-3">
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
                <div class="col-lg-3">
                    <a href="admin.php?page=unit&id=<?php echo $buill_id; ?>" class="text-decoration-none">
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
                <div class="col-lg-3">
                    <a href="admin.php?page=tenant&building_id=<?php echo $buill_id; ?>" class="text-decoration-none">
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

                <!-- Users Card -->
                <div class="col-lg-3">
                    <a href="admin.php?page=users" class="text-decoration-none">
                        <div class="card dashboard-card shadow-sm border-0 rounded-4 mb-4">
                            <div class="card-body d-flex justify-content-between align-items-center">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="icon-circle bg-info-subtle text-info">
                                        <i class="fas fa-user fa-lg"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-1 fw-bold text-dark">Total Users</h6>
                                        <small class="text-muted">Currently Active Users</small>
                                    </div>
                                </div>
                                <h3 class="fw-bold text-info mb-0 ml-2"><?= $total_users ?></h3>
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
                                        <small class="text-muted">100% of <?= $total_invoices ?> invoices</small>
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
        <?php } ?>

        <?php if ($_SESSION['role'] == 'Tenant') { ?>
            <?php include 'tenant_query.php';?>
            <div class="mb-4">
                <h2 class="fw-bold text-dark m-0">Welcome back, <?= htmlspecialchars($tenant_name ?? 'Alex') ?></h2>
                <p class="text-muted small m-0">Here's what's happening with your unit at Rent-Manager.</p>
            </div>

            <div class="row g-3 mb-4">
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border-0 shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="text-muted small fw-semibold">Rent Due</span>
                            <i class="far fa-credit-card text-muted fs-5"></i>
                        </div>
                        <?php if(mysqli_num_rows($invoice_q) > 0) { ?>
                            <h3 class="fw-bold mb-1"><small>৳ </small><?= number_format($total_rent, 0) ?></h3>
                            <small class="text-muted"><i class="far fa-calendar-alt me-1"></i> Due <?= date('M j, Y', strtotime($bill_month ?? $this_month )) ?></small>
                        <?php } else { ?>
                            <h3 class="fw-bold mb-1"><small>৳ </small>00</h3>
                            <small class="text-muted"><i class="far fa-calendar-alt me-1"></i><?php echo $bill_month ?? $this_month; ?> Invoice not found</small>
                        <?php } ?>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border-0 shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="text-muted small fw-semibold">Advance Due</span>
                            <i class="far fa-file-alt text-muted fs-5"></i>
                        </div>
                        <?php if($unit_advance > 0) { ?>
                            <h3 class="fw-bold mb-1"><small>৳ </small><?= number_format($unit_advance, 0) ?></h3>
                        <?php }?>
                        <div class="d-flex justify-content-between align-item-center">
                            <h6 class="fw-bold mb-1 text-success">Paid - <small>৳ </small><?= number_format($paid_amount,0) ?></h6>
                            <small class="text-muted"><i class="far fa-calendar-alt me-1"></i><?php echo date('M , Y', strtotime($advance_paid_date ?? $this_month)); ?></small>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border-0 shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="text-muted small fw-semibold">Status</span>
                            <i class="fas fa-wrench text-muted fs-5"></i>
                        </div>
                        <h3 class="fw-bold mb-1 text-<?php if($status == 'Active') echo 'success'; else if($status == 'Inactive') echo 'danger'; else if ($status == 'Booked') echo 'primary'; ?>"><?php echo $status; ?></h3>
                        <small class="text-success"><i class="fas fa-arrow-down me-1"></i> Tenant Status</small>
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="card h-100 border-0 shadow-sm p-3">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="text-muted small fw-semibold">Building Info</span>
                            <i class="fas fa-building text-muted fs-5"></i>
                        </div>
                        <h4 class="fw-bold mb-1"><?= $building_name ?></h4>
                        
                        <div class="d-inline-flex align-items-center px-3 py-2 rounded-3 shadow-sm"
                            style="background:linear-gradient(90deg,#198754,#20c997); color:#fff;">
                            <i class="fas fa-building me-2"></i>
                            <span class="fw-bold">
                                <?= htmlspecialchars($unit_type ?? 'Unit') ?> :
                            </span>
                            <span class="ms-2">
                                <?= htmlspecialchars($unit_name) ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-12 col-lg-8">
                    <div class="card">
                        <div class="card-header ">
                            <h5 class="card-title mb-0">Monthly Invoice vs Payment Status (<?php echo $current_year; ?>)</h5>
                        </div>
                        <div class="card-body">
                            <div style="position: relative; height:350px; width:100%">
                                <canvas id="tenantFinancialChart"></canvas>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm p-4">
                        <h5 class="fw-bold text-dark mb-1">Recent Payment </h5>
                        <p class="text-muted small mb-4">Your latest Payment History</p>

                        <div class="border-bottom ">
                             <?php
                                    // JOIN ব্যবহার করা হয়েছে যাতে invoices টেবিল থেকে total_amount এবং billing_month পাওয়া যায়
                                    $history_sql = mysqli_query($db, "SELECT ph.*, inv.total_amount, inv.billing_month 
                                                                    FROM `payment_history` ph 
                                                                    JOIN `invoices` inv ON ph.invoice_id = inv.id 
                                                                    WHERE ph.tenant_id = '$tenant_id' ORDER BY ph.id DESC LIMIT 3
                                                                    "); //ORDER BY ph.payment_date ASC, ph.id ASC

                                    $monthly_paid_tracker = [];

                                    while ($pay_history = mysqli_fetch_assoc($history_sql)) {
                                        $pay_slip_id = $pay_history['id'];
                                        $invoice_id = $pay_history['invoice_id'];
                                        $bill_month = $pay_history['billing_month']; 
                                        $total_bill_amount = (float)$pay_history['total_amount']; 
                                        $current_paid_entry = (float)$pay_history['paid_amount'];
                                        
                                        // পেমেন্ট মেথড এবং ম্যানেজার সংক্রান্ত ডাটা
                                        $pay_method_his = $pay_history['payment_method'];
                                        $manager_paid_val = (float)$pay_history['manager_paid']; 
                                        $manager_payment_method = $pay_history['manager_payment_method'];

                                        // --- লজিক ফিক্স: যদি মেথড Manager হয় তবে self ক্যালকুলেট হবে ---
                                        $manager_self = 0;
                                        if ($pay_method_his == 'Manager') {
                                            $manager_self = $current_paid_entry - $manager_paid_val;
                                        }
                                        // --------------------------------------------------------

                                        // মাস ভিত্তিক পেইড অ্যামাউন্ট ট্র্যাক করা
                                        if (!isset($monthly_paid_tracker[$invoice_id])) {
                                            $monthly_paid_tracker[$invoice_id] = 0;
                                        }
                                        $monthly_paid_tracker[$invoice_id] += $current_paid_entry;

                                        $calculated_total_paid = $monthly_paid_tracker[$invoice_id];
                                        $calculated_due = $total_bill_amount - $calculated_total_paid;

                                        $note_his = $pay_history['note'];
                                        $pay_date_his = $pay_history['payment_date'];
                                        $transaction_id_db = $pay_history['transaction_id'];
                                        $transaction_number = $pay_history['transaction_number'];
                                ?>
                                <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-3">
                                    <div class="w-20 d-flex align-items-center gap-3">
                                        <!-- আইকন বক্স -->
                                        <div class="text-success flex-shrink-0 d-flex align-items-center justify-content-center bg-success bg-opacity-10" 
                                            style="width: 40px; height: 40px; border-radius: 50%;">
                                            <i class="fas fa-receipt text-white"></i>
                                        </div>
                                        <div>
                                            <strong class="mb-0 text-dark fw-semibold" style="font-size: 0.95rem;">Invoice #INV-<?php echo $invoice_id; ?></strong> <br>
                                            <small class="text-muted" style="font-size: 0.8rem;">Paid on <?php echo date('j F Y h:i A', strtotime($pay_date_his)); ?></small>
                                        </div>
                                    </div>
                                    <div class="w-80">
                                        <div class=" d-flex justify-content-between align-items-center gap-3">
                                            <!-- Total -->
                                            <div class=" px-md-4">
                                                <span class="text-muted d-block text-uppercase" style="font-size: 0.5rem; letter-spacing: 0.6px;">Total</span>
                                                <span class="text-primary fw-medium" style="font-size: 0.7rem;">৳ <?= number_format($total_bill_amount, 0) ?></span>
                                            </div>
                                            <!-- Today Paid -->
                                            <div class="border-start border-0 border-md-start px-3 px-md-4">
                                                <span class="text-muted d-block text-uppercase" style="font-size: 0.5rem; letter-spacing: 0.6px;">Paid</span>
                                                <span class="text-success fw-semibold" style="font-size: 0.7rem;">৳ <?= number_format($calculated_total_paid, 0) ?></span>
                                            </div>
                                            <!-- Due -->
                                            <div class="border-start border-0 border-md-start px-3 px-md-4">
                                                <span class="text-muted d-block text-uppercase" style="font-size: 0.5rem; letter-spacing: 0.6px;">Due</span>
                                                <span class="text-danger fw-semibold" style="font-size: 0.7rem;">৳ <?= number_format($calculated_due, 0) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <a href="admin.php?page=tenant_inv_pay&type=payment" class="text-muted mt-2">View All <i class="fas fa-arrow-right small"></i></a>
                    </div>
                </div>
                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm p-4 mb-4">
                        <h5 class="fw-bold text-dark mb-1"><i class="fas fa-bell text-warning text-small"></i>  Notifications</h5>
                        <p class="text-muted small mb-4">Latest Notifications from Rent-Manager Admin</p>

                        <div class="mb-3 pb-3 border-bottom">
                            <div class="d-flex align-items-start gap-2 mb-1">
                                <i class="fas fa-circle-check text-success small"></i>
                                <h6 class="mb-0 fw-bold text-dark small">Payment Successful</h6>
                            </div>
                                    <p class="text-muted small mb-1" style="font-size: 0.85rem;">Your payment of $1,200 for March has been processed successfully.</p>
                            <small class="text-muted" style="font-size: 0.75rem;">Mar 3, 2026</small>
                        </div>

                        <div class="mb-3 border-bottom">
                            <div class="d-flex align-items-start gap-2 mb-1">
                                <i class="fas fa-exclamation-circle text-warning  small"></i>
                                <h6 class="mb-0 fw-bold text-dark small">Payment Pending</h6>
                            </div>
                            <p class="text-muted small mb-1" style="font-size: 0.85rem;">Your Payment for March is still pending.</p>
                            <small class="text-muted" style="font-size: 0.75rem;">Feb 28, 2026</small>
                        </div>
                        <a href="" class="text-muted">View All Notifications <i class="fas fa-arrow-right small"></i></a>
                    </div>

                    <div class="card border-0 shadow-sm p-4">
                        <h5 class="fw-bold text-dark mb-1">Confirm Payment</h5>
                        <p class="text-muted small mb-4">Common tasks at your fingertips</p>

                        <a href="admin.php?page=tenant_inv_pay&type=payment"
                            class="btn btn-primary w-100 d-flex justify-content-between align-items-center py-2 px-3 mb-3 text-start border-0"
                            style="background-color: #1a568c;">
                            <span><i class="fas fa-wallet me-2"></i> Pay Rent</span>
                            <i class="fas fa-arrow-right small"></i>
                        </a>

                        <!-- <a href="#"
                            class="btn btn-light w-100 d-flex justify-content-between align-items-center py-2 px-3 text-start bg-white border"
                            style="color: #495057;">
                            <span><i class="fas fa-tools me-2"></i> Submit Maintenance Request</span>
                            <i class="fas fa-arrow-right small text-muted"></i>
                        </a> -->
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<!-- chart for admin  -->
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
<!-- chart for tenant  -->
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('tenantFinancialChart').getContext('2d');
        
        // PHP থেকে ডাটা জাভাস্ক্রিপ্ট অ্যারেতে নেওয়া
        const invoiceData = [<?php echo $chart_bills; ?>];
        const paymentData = [<?php echo $chart_paids; ?>];
        
        const labels = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        new Chart(ctx, {
            type: 'bar', // বার চার্ট (পাশাপাশি তুলনা করার জন্য বেস্ট)
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Total Invoice Amount',
                        data: invoiceData,
                        backgroundColor: 'rgba(54, 162, 235, 0.7)', // হালকা নীল
                        borderColor: 'rgba(54, 162, 235, 1)',
                        borderWidth: 1
                    },
                    {
                        label: 'Total Paid Amount',
                        data: paymentData,
                        backgroundColor: 'rgba(75, 192, 192, 0.7)', // হালকা সবুজ/টিয়া
                        borderColor: 'rgba(75, 192, 192, 1)',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Amount (BDT/Currency)'
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Months'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                }
            }
        });
    });
</script>
