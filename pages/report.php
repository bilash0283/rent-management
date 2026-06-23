<?php
    // Default values
    $building_id = '';
    $current_year = date('Y');

    // ডিফল্ট মান: চলতি বছরের জানুয়ারি থেকে বর্তমান মাস পর্যন্ত
    $from_month = $current_year . '-01';
    $to_month = date('Y-m'); 

    // Handle POST Filter
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['year']) && !empty($_POST['year'])) {
            $current_year = mysqli_real_escape_string($db, $_POST['year']);
        }

        if (isset($_POST['building']) && !empty($_POST['building'])) {
            $building_id = mysqli_real_escape_string($db, $_POST['building']);
        }

        // মাস এবং বছরকে একসাথে জোড়া লাগিয়ে YYYY-MM ফরম্যাটে রূপান্তর
        if (isset($_POST['from_month']) && !empty($_POST['from_month'])) {
            $from_month = $current_year . '-' . mysqli_real_escape_string($db, $_POST['from_month']);
        }
        if (isset($_POST['to_month']) && !empty($_POST['to_month'])) {
            $to_month = $current_year . '-' . mysqli_real_escape_string($db, $_POST['to_month']);
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

    // Fetch all rented units
    $query = "SELECT * FROM unit WHERE building_name = '$building_id' AND status = 'Rented'";
    $result = mysqli_query($db, $query);
    $total_unit = mysqli_num_rows($result);

    // Filter Condition for SQL (payment_history টেবিলের জন্য এলিয়াস ph সহ)
    $filter_condition = " ph.bill_month >= '$from_month' AND ph.bill_month <= '$to_month' ";

    // invoices টেবিলের জন্য এলিয়াস inv সহ আলাদা কন্ডিশন
    $invoice_filter_condition = " inv.billing_month >= '$from_month' AND inv.billing_month <= '$to_month' ";

    // expenses টেবিলের জন্য এলিয়াস exp সহ আলাদা কন্ডিশন
    $expense_filter_condition = " exp.expense_month >= '$from_month' AND exp.expense_month <= '$to_month' ";
?>

<div class="nxl-content">
    <!-- Page Header Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-1 bg-white">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-column flex-xl-row align-items-start align-items-xl-center justify-content-between gap-4">
                <!-- Left Side: Building Info Dashboard -->
                <div class="d-flex align-items-center mb-3 mb-lg-0">
                    <div class="icon-box bg-primary-soft text-primary me-3 p-3 rounded-circle"
                        style="background: rgba(13, 110, 253, 0.1);">
                        <i class="fas fa-file-invoice-dollar fs-4"></i>
                    </div>
                    <div>
                        <h4 class="mb-1 fw-bold text-dark">
                            <?= htmlspecialchars($building_name_db) ?>
                        </h4>
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-info-subtle text-info border border-info-subtle py-1 rounded-pill">
                                <i class="fas fa-door-open me-1"></i> Total Units: <?= $total_unit ?>
                            </span>
                            <span class="text-muted small bg-light px-2 py-1 rounded-2 text-nowrap">
                                <i class="far fa-calendar-alt me-1"></i> 
                                <?= date('M Y', strtotime($from_month)) ?> - <?= date('M Y', strtotime($to_month)) ?>
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Right Side: Pixel-Perfect Responsive Form -->
                <div class="w-100 w-xl-auto">
                    <form method="POST" class="header-filter-form">
                        <!-- Year Field -->
                        <div class="filter-field-group field-year">
                            <label class="form-label-custom">Year</label>
                            <select name="year" class="form-select custom-select-input shadow-sm-custom">
                                <?php for($y = date('Y'); $y >= 2024; $y--): ?>
                                    <option value="<?= $y ?>" <?= $y == $current_year ? 'selected' : '' ?>><?= $y ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <!-- From Month Field -->
                        <div class="filter-field-group field-month">
                            <label class="form-label-custom">From Month</label>
                            <select name="from_month" class="form-select custom-select-input shadow-sm-custom">
                                <?php for ($m = 1; $m <= 12; $m++): 
                                    $mVal = str_pad($m, 2, '0', STR_PAD_LEFT);
                                    $selected = (substr($from_month, 5, 2) == $mVal) ? 'selected' : '';
                                ?>
                                    <option value="<?= $mVal ?>" <?= $selected ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <!-- To Month Field -->
                        <div class="filter-field-group field-month">
                            <label class="form-label-custom">To Month</label>
                            <select name="to_month" class="form-select custom-select-input shadow-sm-custom">
                                <?php for ($m = 1; $m <= 12; $m++): 
                                    $mVal = str_pad($m, 2, '0', STR_PAD_LEFT);
                                    $selected = (substr($to_month, 5, 2) == $mVal) ? 'selected' : '';
                                ?>
                                    <option value="<?= $mVal ?>" <?= $selected ?>><?= date('F', mktime(0, 0, 0, $m, 1)) ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>

                        <!-- Building Field -->
                        <div class="filter-field-group field-building">
                            <label class="form-label-custom">Building</label>
                            <select name="building" class="form-select custom-select-input shadow-sm-custom">
                                <?php
                                $buildings_sql = mysqli_query($db, "SELECT id, name FROM building ORDER BY name ASC");
                                while ($buil = mysqli_fetch_assoc($buildings_sql)) {
                                    $selected = ($buil['id'] == $building_id) ? 'selected' : '';
                                    echo "<option value='{$buil['id']}' $selected>{$buil['name']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Action Button -->
                        <div class="filter-field-group field-button">
                            <button type="submit" class="btn btn-success w-100 btn-filter-submit shadow-sm d-flex align-items-center justify-content-center gap-2 fw-medium transition-all">
                                <i class="fas fa-filter fs-7"></i> <span>Filter</span>
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Custom CSS Core Styles -->
    <style>
        /* Custom Styling Core */
        .shadow-sm-custom {
            box-shadow: 0 2px 5px rgba(0,0,0,.03) !important;
        }
        .form-label-custom {
            font-size: 0.785rem;
            color: #6c757d;
            margin-bottom: 5px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            display: block;
        }
        .custom-select-input {
            border-color: #e2e8f0;
            padding: 0.52rem 0.85rem;
            font-size: 0.9rem;
            border-radius: 8px;
            color: #334155;
            background-color: #fff;
        }
        .custom-select-input:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15) !important;
        }
        .btn-filter-submit {
            height: 40px;
            border-radius: 8px;
            padding: 0 1.5rem;
            background-color: #0d6efd;
            border: none;
        }
        .transition-all {
            transition: all 0.2s ease-in-out;
        }
        .transition-all:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 10px rgba(13, 110, 253, 0.2) !important;
        }

        /* Professional Layout System (Flex-Grid CSS) */
        .header-filter-form {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: flex-end;
        }
        .filter-field-group {
            flex: 1 1 calc(50% - 6px); /* মোবাইলে সমান ২ কলাম */
        }
        .filter-field-group.field-button {
            flex: 1 1 100%; /* মোবাইলে বাটন ফুল উইডথ */
        }

        /* ছোট ও মাঝারি ডিভাইস (Tablets & Landscape Mobile) */
        @media (min-width: 576px) {
            .filter-field-group {
                flex: 1 1 calc(33.333% - 8px);
            }
            .filter-field-group.field-building {
                flex: 1 1 calc(66.666% - 8px); /* বিল্ডিং নাম বড় তাই বেশি জায়গা */
            }
            .filter-field-group.field-button {
                flex: 1 1 100%;
            }
        }

        /* ডেক্সটপ এবং বড় স্ক্রিন সংস্করণ (Perfect Single Line Alignment) */
        @media (min-width: 1200px) {
            .header-filter-form {
                flex-wrap: nowrap; /* এক লাইনে লক থাকবে */
                gap: 10px;
            }
            .filter-field-group {
                flex: 0 0 auto;
            }
            .filter-field-group.field-year {
                width: 95px;
            }
            .filter-field-group.field-month {
                width: 125px;
            }
            .filter-field-group.field-building {
                width: 165px;
            }
            .filter-field-group.field-button {
                width: auto;
            }
        }
    </style>

    <div class="main-content">
        <?php
            // ==================== MANAGER PAYMENT SUMMARY ====================
            $manager_summary = mysqli_query($db, "
                SELECT 
                    SUM(ph.paid_amount) as total_received,
                    SUM(ph.manager_paid) as manager_paid_total
                FROM payment_history ph
                JOIN tenants t ON ph.tenant_id = t.id
                WHERE t.building_id = '$building_id' 
                AND $filter_condition 
                AND ph.payment_method = 'Manager'
            ");

            $summary = mysqli_fetch_assoc($manager_summary);
            $total_received = (float) ($summary['total_received'] ?? 0);
            $manager_paid_total = (float) ($summary['manager_paid_total'] ?? 0);
            $manager_self = $total_received - $manager_paid_total;

            // ==================== BILLING SUMMARY ====================
            $total_bill_amount = 0;
            $paid_amount_db_amount = 0;
            $due_amount_db_amount = 0;

            $pay_info = mysqli_query($db, "
                SELECT 
                    SUM(total_amount) as total_bill,
                    SUM(paid_amount) as total_paid
                FROM invoices inv
                JOIN tenants t ON inv.tenant_id = t.id
                WHERE t.building_id = '$building_id' 
                AND $invoice_filter_condition
            ");

            if ($pay_info && $row = mysqli_fetch_assoc($pay_info)) {
                $total_bill_amount = (float)$row['total_bill'];
                $paid_amount_db_amount = (float)$row['total_paid'];
                $due_amount_db_amount = $total_bill_amount - $paid_amount_db_amount;
            }
        ?>

        <!-- Summary Cards -->
        <div class="row g-3 mb-2">
            <div class="col-md">
                <div class="card shadow-sm border-0 bg-primary text-white">
                    <div class="card-body text-center">
                        <h6 class="mb-1 text-white">Total Amount</h6>
                        <h4 class="mb-0 text-white">৳ <?= number_format($total_bill_amount, 0) ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card shadow-sm border-0 bg-success">
                    <div class="card-body text-center">
                        <h6 class="mb-1 text-white">Total Paid</h6>
                        <h4 class="mb-0 text-white">৳ <?= number_format($paid_amount_db_amount, 0) ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card shadow-sm border-0 bg-warning text-white">
                    <div class="card-body text-center">
                        <h6 class="mb-1 text-white">Total Due</h6>
                        <h4 class="mb-0 text-white">৳ <?= number_format(max($due_amount_db_amount, 0), 0) ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <?php 
                    // 1. Total Expense
                    $total_sql = mysqli_query($db,
                        "SELECT SUM(amount) AS total
                        FROM expense exp
                        WHERE building_id='$building_id'
                        AND $expense_filter_condition"
                    );
                    $total_row = mysqli_fetch_assoc($total_sql);
                    $grand_total = (float)($total_row['total'] ?? 0);

                    // 2. Admin Expense (Assuming 'expense_by' contains 'Admin')
                    $admin_sql = mysqli_query($db,
                        "SELECT SUM(amount) AS total
                        FROM expense exp
                        WHERE building_id='$building_id'
                        AND $expense_filter_condition
                        AND expense_by = 'Admin'"
                    );
                    $admin_row = mysqli_fetch_assoc($admin_sql);
                    $admin_total = (float)($admin_row['total'] ?? 0);

                    // 3. Manager Expense (Assuming 'expense_by' contains 'Manager')
                    $manager_sql = mysqli_query($db,
                        "SELECT SUM(amount) AS total
                        FROM expense exp
                        WHERE building_id='$building_id'
                        AND $expense_filter_condition
                        AND expense_by = 'Manager'"
                    );
                    $manager_row = mysqli_fetch_assoc($manager_sql);
                    $manager_total = (float)($manager_row['total'] ?? 0);

                    // manager payalbe amount 
                    $payable = $manager_self - $manager_total;
                ?>
                <div class="card shadow-sm border-0 bg-info text-white">
                    <div class="card-body text-left p-1 pl-4 mx-auto">
                        <strong class="mb-1 text-white">Expense Summary</strong><br>
                        <small>Admin Expense : ৳ <?= number_format($admin_total, 0) ?></small><br>
                        <small>Manager Expense : ৳ <?= number_format($manager_total, 0) ?></small><br>
                        <small>Total Expense : ৳ <?= number_format($grand_total, 0) ?></small><br>
                    </div>
                </div>
            </div>
            <div class="col-md">
                <div class="card shadow-sm border-0 bg-secondary text-white">
                    <div class="card-body text-left p-1 pl-4 mx-auto">
                        <strong class="mb-1 text-white">Paid By Manager</strong><br>
                        <small>Total Received : ৳ <?= number_format($total_received, 0) ?></small><br>
                        <small>Paid to Admin : ৳ <?= number_format($manager_paid_total, 0) ?></small><br>
                        <!-- <small>Manager self : ৳ <?= number_format($manager_self, 0) ?></small><br> -->
                        <small style="color: <?= ($payable < 0) ? 'red' : 'white'; ?>;">
                            Self (Net Payable) : ৳ <?= number_format($payable, 0) ?>
                        </small><br>
                    </div>
                </div>
            </div>
        </div>

        <!-- Payment Ratio -->
        <?php 
            $pm_query = "
                SELECT ph.payment_method, COALESCE(SUM(ph.paid_amount), 0) as method_total
                FROM payment_history ph
                JOIN tenants t ON ph.tenant_id = t.id
                WHERE t.building_id = '$building_id' AND $filter_condition
                GROUP BY ph.payment_method ORDER BY method_total DESC";
            $pm_result = mysqli_query($db, $pm_query);
            $payment_methods = [];
            $total_paid_ratio = $paid_amount_db_amount > 0 ? $paid_amount_db_amount : 1;
            while ($pm = mysqli_fetch_assoc($pm_result)) {
                $perc = round(($pm['method_total'] / $total_paid_ratio) * 100, 2);
                $payment_methods[] = ['method' => $pm['payment_method'] ?: 'Unknown', 'amount' => $pm['method_total'], 'percentage' => $perc];
            }
        ?>

        <div class="mb-2">
            <h5 class="mb-2">Payment Method Wise Ratio</h5>
            <?php if (empty($payment_methods)): ?>
                <div class="alert alert-light text-center border shadow-sm rounded-3 py-2">No payment data found.</div>
            <?php else: ?>
                <div class="card border-0 shadow-sm rounded-3 bg-white p-4">
                    <div class="progress mb-4" style="height: 22px; border-radius: 10px; overflow: hidden; background-color: #f0f0f0;">
                        <?php $colors = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger', 'bg-secondary'];
                        foreach ($payment_methods as $index => $pm): $color_class = $colors[$index % count($colors)]; ?>
                            <div class="progress-bar <?= $color_class ?>" style="width: <?= $pm['percentage'] ?>%; border-right: 1px solid rgba(255,255,255,0.3);" title="<?= $pm['method'] ?>: <?= $pm['percentage'] ?>%">
                                <?= ($pm['percentage'] > 5) ? $pm['percentage'].'%' : '' ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="row g-2">
                        <?php foreach ($payment_methods as $index => $pm): $color_class = $colors[$index % count($colors)]; ?>
                            <div class="col-6 col-md-4 col-lg-3">
                                <div class="d-flex align-items-center">
                                    <div class="<?= $color_class ?> rounded-circle me-2" style="width: 10px; height: 10px;"></div>
                                    <div class="small">
                                        <span class="fw-bold text-dark"><?= htmlspecialchars($pm['method']) ?></span><br>
                                        <span class="text-muted">৳ <?= number_format($pm['amount'], 0) ?> (<?= $pm['percentage'] ?>%)</span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Main Table -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>SL</th><th>Unit</th><th>Tenant</th><th>Bill (Period)</th><th>Status</th><th>Manager Info</th><th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            mysqli_data_seek($result, 0);
                            while ($row = mysqli_fetch_assoc($result)) {
                                $i++;
                                $unit_id = $row['id'];
                                $unit_name = $row['unit_name'];
                                $building_name = $row['building_name'];
                                $size = $row['size'];

                                $sql_tenant = mysqli_query($db, "SELECT * FROM tenants WHERE building_id = '$building_name' AND unit_id = '$unit_id' LIMIT 1");
                                $tent_row = mysqli_fetch_assoc($sql_tenant);
                                $name = $tent_row['name'] ?? 'N/A';
                                $tent_id = $tent_row['id'] ?? 0;
                                $image = !empty($tent_row['tenant_image']) ? "public/uploads/tenants/" . $tent_row['tenant_image'] : "public/uploads/tenants/no-image.png";
                            ?>
                                <tr>
                                    <td><?= $i; ?></td>
                                    <td><?= $unit_name; ?></td>
                                    <td>
                                        <div class="d-flex flex-column align-items-center text-center">
                                            <img src="<?= $image ?>" width="45" height="45" style="object-fit:cover;border-radius:50%;" class="mb-1">
                                            <a href="admin.php?page=view_tenant&id=<?= $tent_id ?>" class="text-secondary fw-bold" style="font-size:11px;"><?= $name; ?></a>
                                            <small class="text-muted" style="font-size:9px;"><?= $size; ?></small>
                                        </div>
                                    </td>
                                    <td style="font-size: 11px;">
                                        <?php
                                        // বিলিং টেবিলের জন্য ইনভয়েস কন্ডিশন ব্যবহার করা হয়েছে 
                                        $pay_info = mysqli_query($db, "SELECT SUM(total_amount) as total, SUM(paid_amount) as paid FROM invoices inv WHERE tenant_id = '$tent_id' AND unit_id = '$unit_id' AND $invoice_filter_condition");
                                        $bill_data = mysqli_fetch_assoc($pay_info);
                                        if ($bill_data['total'] > 0) {
                                            echo "<span class='text-primary'>Total: ৳".number_format($bill_data['total'],0)."</span><br>";
                                            echo "<span class='text-success'>Paid: ৳".number_format($bill_data['paid'],0)."</span><br>";
                                            $due = $bill_data['total'] - $bill_data['paid'];
                                            if($due > 0) echo "<span class='text-danger'>Due: ৳".number_format($due,0)."</span>";
                                        } else {
                                            echo '<span class="text-muted">No Invoice</span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($bill_data['total'] > 0) {
                                            if ($bill_data['paid'] >= $bill_data['total']) echo '<span class="badge bg-success">Paid</span>';
                                            elseif ($bill_data['paid'] > 0) echo '<span class="badge bg-warning">Partial</span>';
                                            else echo '<span class="badge bg-danger">Unpaid</span>';
                                        } else { echo "-"; }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        // নির্দিষ্ট সময়ের পেমেন্ট হিস্ট্রি ডাটা ফিল্টার
                                        $h_sql = mysqli_query($db, "SELECT * FROM payment_history ph WHERE tenant_id = '$tent_id' AND $filter_condition LIMIT 1");
                                        if ($h_row = mysqli_fetch_assoc($h_sql)) {
                                            echo "<small class='text-success fw-bold' style='font-size:10px;'>{$h_row['payment_method']}</small><br>";
                                            echo "<small style='font-size:9px;'>Date: {$h_row['payment_date']}</small>";
                                        } else { echo "<small class='text-danger' style='font-size:9px;'>No Payment</small>"; }
                                        ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group">
                                            <a href="admin.php?page=editbill&tenant_id=<?= $tent_id ?>" class="btn btn-sm btn-info p-1">Details</a>
                                            <a href="admin.php?page=bill&unit_id=<?= $unit_id ?>&id=<?= $building_name ?>" class="btn btn-sm btn-success p-1"><i class="bi bi-send"></i></a>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>