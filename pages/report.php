<?php
if(!isset($_GET['report_type']) || !in_array($_GET['report_type'], ['monthly', 'yearly'])) {
    header('Location: admin.php?page=report&report_type=monthly');
    exit;
}else{
    $report_type = $_GET['report_type'] ?? 'monthly';
}

// ====================== FILTER SETUP ======================
$filter_month = isset($_GET['month']) ? (int)$_GET['month'] : date('n');
$filter_year  = isset($_GET['year'])  ? (int)$_GET['year']  : date('Y');

// Pad month with zero (01, 02, ..., 12)
$month_padded = str_pad($filter_month, 2, '0', STR_PAD_LEFT);

if($report_type === 'monthly') {
    $where_clause = "WHERE bill_month = '$filter_year-$month_padded'";
    $report_title = date('F Y', strtotime("$filter_year-$month_padded-01"));
} else {
    $where_clause = "WHERE bill_month LIKE '%$filter_year%'";
    $report_title = $filter_year . " Yearly Report";
}

// invoice thaka total bill, paid, due amount ber korar jonno query 
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

// ====================== AGGREGATE QUERY ======================
$agg_query = "
    SELECT 
        COALESCE(SUM(manager_self), 0) as total_manager_self,
        COALESCE(SUM(expense), 0) as total_expense
    FROM `payment_history`
    $where_clause
";

$agg_result = mysqli_query($db, $agg_query);
$agg = mysqli_fetch_assoc($agg_result);

$agg_query = mysqli_query($db, "SELECT 
SUM(total_amount) AS total_amount,
SUM(paid_amount)  AS total_paid,
SUM(due_amount)   AS total_unpaid
FROM invoices WHERE billing_month = '$this_month' ");

// Payment method wise ratio
$pm_query = "
    SELECT 
        payment_method,
        COALESCE(SUM(paid), 0) as method_total
    FROM `payment_history`
    $where_clause
    GROUP BY payment_method
    ORDER BY method_total DESC
";

$pm_result = mysqli_query($db, $pm_query);

$payment_methods = [];
$total_paid_ratio = $agg['total_paid'] > 0 ? $agg['total_paid'] : 1;

while($pm = mysqli_fetch_assoc($pm_result)) {
    $perc = round(($pm['method_total'] / $total_paid_ratio) * 100, 2);
    $payment_methods[] = [
        'method' => $pm['payment_method'] ?: 'Unknown',
        'amount' => $pm['method_total'],
        'percentage' => $perc
    ];
}

// Detailed history
$history_sql = mysqli_query($db, "
    SELECT * FROM `payment_history` 
    $where_clause 
    ORDER BY bill_month DESC, payment_date DESC
");
?>

<div class="nxl-content">
    <div class="main-content">
        <div class="card shadow-lg">
            <div class="card-body">
                <!-- ==================== TOP FILTER FORM ==================== -->
                <form method="GET" class="row g-3 align-items-end mb-4 border-bottom pb-3">
                    <input type="hidden" name="page" value="report">
                    <input type="hidden" name="report_type" value="<?= htmlspecialchars($report_type) ?>">

                    <?php if($report_type === 'monthly'): ?>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Month</label>
                            <select name="month" class="form-select">
                                <option selected>Select Month</option>
                                <?php for($m = 1; $m <= 12; $m++): ?>
                                    <option value="<?= $m ?>" <?= $m === $filter_month ? 'selected' : '' ?>>
                                        <?= date('F', mktime(0, 0, 0, $m, 1)) ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <div class="col-md-3">
                        <label class="form-label fw-bold">Year</label>
                        <select name="year" class="form-select">
                            <option selected>Select Year</option>
                            <?php 
                            $currentYear = date('Y');
                            for($y = $currentYear - 5; $y <= $currentYear + 2; $y++): 
                            ?>
                                <option value="<?= $y ?>" <?= $y === $filter_year ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-filter"></i> Show Report
                        </button>
                    </div>
                </form>

                <!-- ==================== HEADER ==================== -->
                <h3 class="card-title text-primary mb-1"><?= ucfirst($report_type) ?> Payment Report</h3>
                <h5 class="text-muted"><?= $report_title ?></h5>
                <p class="text-muted"><?= $filter_year.'-'.$month_padded ?> <strong>payment_history</strong> </p>

                <!-- ==================== SUMMARY CARDS ==================== -->
                <div class="row g-4 mb-5">
                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between text-white">
                                    <div>
                                        <h6 class="opacity-75">TOTAL AMOUNT</h6>
                                        <h2 class="mb-0"><?= number_format($agg['total_amount'], 2) ?></h2>
                                    </div>
                                    <i class="fas fa-money-bill-wave fa-3x opacity-25"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 bg-success text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between text-white">
                                    <div>
                                        <h6 class="opacity-75">TOTAL PAID</h6>
                                        <h2 class="mb-0"><?= number_format($agg['total_paid'], 2) ?></h2>
                                    </div>
                                    <i class="fas fa-check-circle fa-3x opacity-25"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 bg-danger text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <h6 class="opacity-75 text-white">TOTAL UNPAID</h6>
                                        <h2 class="mb-0 text-white"><?= number_format($agg['total_unpaid'], 2) ?></h2>
                                    </div>
                                    <i class="fas fa-exclamation-triangle fa-3x opacity-25"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6">
                        <div class="card border-0 bg-info text-white h-100">
                            <div class="card-body">
                                <h6 class="opacity-75 mb-3">MANAGER SUMMARY</h6>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Manager Self:</span>
                                    <strong><?= number_format($agg['total_manager_self'], 2) ?></strong>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Expense:</span>
                                    <strong><?= number_format($agg['total_expense'], 2) ?></strong>
                                </div>
                                <hr class="border-light">
                                <div class="d-flex justify-content-between">
                                    <span class="fw-bold">Total Payment Handled:</span>
                                    <strong><?= number_format($agg['total_paid'], 2) ?></strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ==================== PAYMENT METHOD RATIO ==================== -->
                <h5 class="mb-3">Payment Method Wise Ratio</h5>
                <div class="table-responsive mb-5">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Payment Method</th>
                                <th class="text-end">Amount</th>
                                <th class="text-end">Percentage</th>
                                <th>Ratio</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(empty($payment_methods)): ?>
                                <tr><td colspan="4" class="text-center text-muted py-4">No payment data found for this period.</td></tr>
                            <?php else: ?>
                                <?php foreach($payment_methods as $pm): ?>
                                <tr>
                                    <td><?= htmlspecialchars($pm['method']) ?></td>
                                    <td class="text-end fw-bold"><?= number_format($pm['amount'], 2) ?></td>
                                    <td class="text-end"><?= $pm['percentage'] ?>%</td>
                                    <td style="width: 40%;">
                                        <div class="progress" style="height: 18px;">
                                            <div class="progress-bar bg-success" style="width: <?= $pm['percentage'] ?>%;">
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>