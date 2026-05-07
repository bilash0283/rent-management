<?php
// Default values
$building_id = '';
$this_month = date('Y-m');   // Current month in YYYY-MM format

// Handle POST Filter
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['month']) && !empty($_POST['month'])) {
        $this_month = mysqli_real_escape_string($db, $_POST['month']);
    }
    
    if (isset($_POST['building']) && !empty($_POST['building'])) {
        $building_id = mysqli_real_escape_string($db, $_POST['building']);
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

// Fetch all rented units for this building
$query = "SELECT * FROM unit 
          WHERE building_name = '$building_id' 
            AND status = 'Rented' 
          ";

$result = mysqli_query($db, $query);
$total_unit = mysqli_num_rows($result);
?>

<div class="nxl-content">
    <!-- Page Header -->
    <div class="page-header d-flex flex-wrap align-items-center justify-content-between p-4 mb-4 bg-white shadow-sm rounded-3">

        <div class="d-flex align-items-center mb-2 mb-lg-0">
            <div class="icon-box bg-primary-soft text-primary me-3 p-3 rounded-circle"
                style="background: rgba(13, 110, 253, 0.1);">
                <i class="fas fa-file-invoice-dollar fs-4"></i>
            </div>
            <div>
                <h4 class="mb-1 fw-bold text-dark">
                    <?= htmlspecialchars($building_name_db) ?>
                </h4>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill">
                        <i class="fas fa-door-open me-1"></i> Total Units: <?= $total_unit ?>
                    </span>
                    <span class="text-muted small">
                        <i class="far fa-calendar-alt me-1"></i> <?= date('M Y', strtotime($this_month . '-01')) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Filter Form -->
        <form method="POST" class="d-flex flex-wrap gap-2 align-items-center">
            <div class="input-group input-group-sm shadow-sm" style="width: 180px;">
                <span class="input-group-text bg-white border-end-0"><i class="far fa-calendar-check text-muted"></i></span>
                <select name="month" class="form-select border-start-0 ps-0 fw-medium">
                    <?php 
                    $currentYear = date('Y');
                    $selectedMonth = (int)substr($this_month, 5, 2);
                    
                    for ($m = 1; $m <= 12; $m++): 
                        $monthValue = $currentYear . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
                        $displayText = date('F', mktime(0, 0, 0, $m, 1, $currentYear)); // শুধু month
                    ?>
                        <option value="<?= $monthValue ?>" <?= $m == $selectedMonth ? 'selected' : '' ?>>
                            <?= $displayText ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>

            <div class="input-group input-group-sm shadow-sm" style="width: 220px;">
                <span class="input-group-text bg-white border-end-0"><i class="fas fa-building text-muted"></i></span>
                <select name="building" class="form-select border-start-0 ps-0 fw-medium">
                    <?php
                    $buildings_sql = mysqli_query($db, "SELECT id, name FROM building ORDER BY name ASC");
                    while ($buil = mysqli_fetch_assoc($buildings_sql)) {
                        $b_id = $buil['id'];
                        $b_name = $buil['name'];
                        $selected = ($b_id == $building_id) ? 'selected' : '';
                        echo "<option value='$b_id' $selected>$b_name</option>";
                    }
                    ?>
                </select>
            </div>

            <button type="submit" class="btn btn-success btn-sm px-3 shadow-sm d-flex align-items-center gap-2">
                <i class="fas fa-filter"></i>
                <span>Filter</span>
            </button>
        </form>
    </div>
</div>

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
            AND ph.bill_month = '$this_month' 
            AND ph.payment_method = 'Manager'
        ");

        $summary = mysqli_fetch_assoc($manager_summary);

        $total_received = (float) ($summary['total_received'] ?? 0);
        $manager_paid_total = (float) ($summary['manager_paid_total'] ?? 0);
        $manager_paid = $total_received - $manager_paid_total;
    ?>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4 mx-3">
        <?php
            $total_bill_amount = 0;
            $paid_amount_db_amount = 0;
            $due_amount_db_amount = 0;

            $pay_info = mysqli_query($db, "
                SELECT 
                    SUM(ph.paid_amount) as total_paid,
                    SUM(inv.total_amount) as total_bill
                FROM payment_history ph
                JOIN tenants t ON ph.tenant_id = t.id
                JOIN invoices inv ON ph.invoice_id = inv.id 
                WHERE t.building_id = '$building_id' 
                AND ph.bill_month = '$this_month' 
            ");

            if ($pay_info && $row = mysqli_fetch_assoc($pay_info)) {
                $total_bill_amount = (float)$row['total_bill'];
                $paid_amount_db_amount = (float)$row['total_paid'];
                $due_amount_db_amount = $total_bill_amount - $paid_amount_db_amount;
            }
        ?>  
        <div class="col-md">
            <div class="card shadow-sm border-0 bg-primary text-white">
                <div class="card-body text-center">
                    <h6 class="mb-1 text-white">Total Amount</h6>
                    <h4 class="mb-0 text-white"><?= number_format($total_bill_amount, 0) ?></h4>
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
            <div class="card shadow-sm border-0 bg-secondary text-white">
                <div class="card-body text-left p-1 pl-4 mx-auto">
                    <strong class="mb-1 text-white">Paid By Manager</strong><br>
                    <small>Total Rechive : ৳ <?= number_format($total_received, 0) ?></small><br>
                    <small>Paid to Admin : ৳ <?= number_format($manager_paid_total, 0) ?></small><br>
                    <small>Manager self  : ৳ <?= number_format($manager_paid, 0) ?></small><br>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Method wise ratio Report  -->
    <?php 
        $pm_query = "
            SELECT 
                ph.payment_method,
                COALESCE(SUM(ph.paid_amount), 0) as method_total
            FROM payment_history ph
            JOIN tenants t ON ph.tenant_id = t.id
            WHERE t.building_id = '$building_id' 
            AND ph.bill_month = '$this_month'
            GROUP BY ph.payment_method
            ORDER BY method_total DESC
        ";

        $pm_result = mysqli_query($db, $pm_query);

        $payment_methods = [];
        $total_paid_ratio = $paid_amount_db_amount > 0 ? $paid_amount_db_amount : 1;

        while ($pm = mysqli_fetch_assoc($pm_result)) {
            $perc = round(($pm['method_total'] / $total_paid_ratio) * 100, 2);
            $payment_methods[] = [
                'method'      => $pm['payment_method'] ?: 'Unknown',
                'amount'      => $pm['method_total'],
                'percentage'  => $perc
            ];
        }

        // Raw values for display (no formatting here)
        $total_bill = $summary['total_bill'] ?? 0;
        $total_paid = $agg['total_paid'] ?? 0;           // Using aggregate data
        $total_due  = $summary['total_due'] ?? 0;
    ?>
    <div class="mb-2 mx-4">
        <h5 class="mb-2">Payment Method Wise Ratio</h5>
        
        <?php if (empty($payment_methods)): ?>
            <div class="alert alert-light text-center border shadow-sm rounded-3 py-2">
                No payment data found for this period.
            </div>
        <?php else: ?>
            <div class="card border-0 shadow-sm rounded-3 bg-white p-4">
                
                <div class="progress mb-4" style="height: 22px; border-radius: 10px; overflow: hidden; background-color: #f0f0f0;">
                    <?php 
                    // কালার প্যালেট (বিভিন্ন মেথডের জন্য আলাদা কালার)
                    $colors = ['bg-primary', 'bg-success', 'bg-info', 'bg-warning', 'bg-danger', 'bg-secondary'];
                    foreach ($payment_methods as $index => $pm): 
                        $color_class = $colors[$index % count($colors)]; // মেথড অনুযায়ী কালার ঘুরিয়ে ফিরিয়ে আসবে
                    ?>
                        <div class="progress-bar <?= $color_class ?>" 
                             role="progressbar" 
                             style="width: <?= $pm['percentage'] ?>%; border-right: 1px solid rgba(255,255,255,0.3);" 
                             aria-valuenow="<?= $pm['percentage'] ?>" 
                             aria-valuemin="0" 
                             aria-valuemax="100"
                             data-bs-toggle="tooltip" 
                             title="<?= htmlspecialchars($pm['method']) ?>: <?= $pm['percentage'] ?>%">
                             <?= ($pm['percentage'] > 5) ? $pm['percentage'].'%' : '' ?>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="row g-2">
                    <?php foreach ($payment_methods as $index => $pm): 
                        $color_class = $colors[$index % count($colors)];
                        // বুটস্ট্র্যাপের কালার ক্লাসের সাথে টেক্সট কালার ম্যাচ করার জন্য
                        $text_color = str_replace('bg-', 'text-', $color_class);
                    ?>
                        <div class="col-6 col-md-4 col-lg-3">
                            <div class="d-flex align-items-center">
                                <div class="<?= $color_class ?> rounded-circle me-2" style="width: 10px; height: 10px;"></div>
                                <div class="small">
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($pm['method']) ?></span>
                                    <br>
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
                            <th>SL</th>
                            <th>Unit</th>
                            <th>Tenant</th>
                            <th>Bill Details</th>
                            <th>Status</th>
                            <th>Manager Payment Info</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $i = 0;
                        mysqli_data_seek($result, 0);

                        while ($row = mysqli_fetch_assoc($result)) {
                                $i++;
                                $unit_id = $row['id'];
                                $advance = $row['advance'];
                                $rent = $row['rent'];
                                $unit_name = $row['unit_name'];
                                $building_name = $row['building_name'];
                                $size = $row['size'];
                                ?>
                                <tr>
                                    <td><?= $i; ?></td>
                                    <td><?= $unit_name; ?></td>
                                    <td>
                                        <?php
                                        $sql_tenant = mysqli_query($db, "SELECT * FROM tenants WHERE building_id = '$building_name' AND unit_id = '$unit_id' ");
                                        while ($tent_row = mysqli_fetch_assoc($sql_tenant)) {
                                            $name = $tent_row['name'];
                                            $tent_id = $tent_row['id'];
                                            $image = !empty($tent_row['tenant_image'])
                                            ? "public/uploads/tenants/" . $tent_row['tenant_image']
                                            : "public/uploads/tenants/no-image.png";
                                        }
                                        ?>
                                        <div class="d-flex flex-column align-items-center text-center col-span">
                                            <img src="<?= htmlspecialchars($image) ?>"
                                                width="50" height="50"
                                                style="object-fit:cover;border-radius:50%;"
                                                class="mb-2">

                                            <a href="admin.php?page=view_tenant&id=<?= $tent_id ?>" 
                                            class="text-secondary fw-bold" style="font-size:12px;" >
                                                <?= $name; ?>
                                            </a>
                                            <?php if (!empty($size)): ?>
                                                <small class="text-muted">Ele.M.N : <?= $size; ?></small>
                                            <?php endif; ?>
                                        </div>                                        
                                    </td>

                                    <td style="font-size: 10px; line-height: 1.4;">
                                        <?php
                                        // Total Advance Paid
                                        $total_paid = 0;
                                        $advance_sql = mysqli_query($db, "SELECT * FROM `advance` WHERE tenant_id = '$tent_id' AND unit_id = '$unit_id'");
                                        while ($advance_his = mysqli_fetch_assoc($advance_sql)) {
                                            $total_paid += $advance_his['paid_amount'];
                                        }

                                        // Remaining Payable Amount
                                        $payable = max($advance - $total_paid, 0); // avoid negative
                                    
                                        // Show Advance and Paid
                                        // echo '<span style="color: #0d6efd; font-weight: 600;">Advance = ৳ ' . $advance . '</span><br>';  // Blue
                                        // echo '<span style="color: #198754; font-weight: 600;">Paid    = ৳ ' . $total_paid . '</span><br>'; // Green
                                    
                                        // Show Due only if payable > 0
                                        // if ($payable > 0) {
                                        //     echo '<span style="color: #dc3545; font-weight: 600;">Advance     = <small> ৳ </small>' . $payable . '</span><br>'; // Red
                                        // }
                                        ?>

                                        <!-- <?php 
                                            if(!empty($rent)){ ?>
                                                <span class="fw-semibold text-dark">
                                            Rent = ৳ <?= number_format($rent, 0) ?? '' ?>
                                            </span><br>
                                        <?php } ?>  -->

                                        <?php
                                        $pay_info = mysqli_query($db, "SELECT * FROM invoices WHERE tenant_id = '$tent_id' AND unit_id = '$unit_id' AND billing_month = '$this_month' ");
                                        if (!mysqli_num_rows($pay_info) > 0) {
                                            // echo '<span class="fw-bold text-danger">';
                                            // echo 'Due = ৳ ' . number_format($rent, 2);
                                            // echo '</span>';
                                            echo '<span class="text-danger">Invoice Not Found!</span>';
                                        } else {
                                            mysqli_data_seek($pay_info, 0); // rewind result to loop again
                                            while ($pay_info_sh = mysqli_fetch_assoc($pay_info)) {
                                                $billing_month_db = $pay_info_sh['billing_month'];
                                                $total_amount_db = $pay_info_sh['total_amount'];
                                                $paid_amount_db = $pay_info_sh['paid_amount'];
                                                $due_amount_db = $total_amount_db-$paid_amount_db;
                                                $created_at = $pay_info_sh['created_at'];
                                                $status = $pay_info_sh['status'];

                                                $Gas = $pay_info_sh['Gas'];
                                                $Water = $pay_info_sh['Water'];
                                                $Electricity = $pay_info_sh['Electricity'];
                                                $Others = $pay_info_sh['Others'];
                                                ?>     
                                                
                                                <!-- <?php 
                                                    if(!empty($Water)){ ?>
                                                        <span class="fw-semibold text-dark">
                                                    Water = <small>৳ </small> <?= number_format($Water, 0) ?? '' ?>
                                                    </span><br>
                                                <?php } ?> 

                                                <?php 
                                                    if(!empty($Gas)){ ?>
                                                        <span class="fw-semibold text-dark">
                                                    Gas = <small>৳ </small> <?= number_format($Gas, 0) ?? '' ?>
                                                    </span><br>
                                                <?php } ?> 

                                                <?php 
                                                    if(!empty($Electricity)){ ?>
                                                        <span class="fw-semibold text-dark">
                                                    Electricity = <small>৳ </small> <?= number_format($Electricity, 0) ?? '' ?>
                                                    </span><br>
                                                <?php } ?>

                                                <?php 
                                                    if(!empty($Others)){ ?>
                                                        <span class="fw-semibold text-dark">
                                                    Others = <small>৳ </small> <?= number_format($Others, 0) ?? '' ?>
                                                    </span><br>
                                                <?php } ?> -->

                                                <span class="fw-semibold text-primary">
                                                    Total = <small>৳ </small> <?= number_format($total_amount_db, 0) ?? '' ?>
                                                </span><br>

                                                <?php if (!empty($paid_amount_db)) { ?>
                                                    <span class="fw-semibold text-success">
                                                        Paid = <small>৳ </small> <?= number_format($paid_amount_db, 0) ?? '' ?>
                                                    </span><br>
                                                <?php } ?>

                                                <?php if (!empty($due_amount_db)) { ?>
                                                    <span class="fw-semibold text-danger">
                                                        Due = <small>৳ </small> <?= number_format($due_amount_db, 0) ?? '' ?>
                                                    </span><br>
                                                <?php }
                                            }
                                        } ?>
                                    </td>

                                    <td>
                                        <?php
                                        if (!mysqli_num_rows($pay_info) > 0) {
                                            echo "<button class='p-1 btn btn-sm btn-secondary'>No Invoice</button>";
                                        } else {
                                            ?>
                                            <button
                                                class="p-1 btn btn-sm btn-<?php if ($status == 'Paid') {
                                                    echo 'success';
                                                } else if ($status == 'Unpaid') {
                                                    echo 'danger';
                                                } else if ($status == 'Partial') {
                                                    echo 'warning';
                                                } ?>">
                                                <?= htmlspecialchars($status); ?>
                                            </button>
                                        <?php } ?>
                                    </td>

                                    <td>
                                        <?php
                                        $history_sql = mysqli_query($db, "SELECT * FROM `payment_history` WHERE `tenant_id` = '$tent_id' AND bill_month = '$this_month' ");
                                        
                                        if (mysqli_num_rows($history_sql) == 0) {
                                            echo '<span class="text-danger" style="font-size:10px;">Payment Not Found!</span>';
                                        } else {
                                            $manager_paid_total = 0;
                                            $manager_self = 0;
                                            
                                            // তথ্য সংগ্রহের লুপ
                                            while ($pay_history = mysqli_fetch_assoc($history_sql)) {
                                                $pay_method_his = $pay_history['payment_method'];
                                                $transaction_id_db = $pay_history['transaction_id'];
                                                $manager_payment_method = $pay_history['manager_payment_method'];
                                                $transaction_date = $pay_history['payment_date'];
                                                $transaction_number = $pay_history['transaction_number'];
                                                $paid_amount_calcu = $pay_history['paid_amount'];
                                            }


                                            $manager_acount_sql = mysqli_query($db, "SELECT * FROM `payment_history` WHERE `tenant_id` = '$tent_id' AND bill_month = '$this_month' AND payment_method = 'Manager' ");
                                            while($manger_ac = mysqli_fetch_assoc($manager_acount_sql)){

                                                $paid_amu = $manger_ac['paid_amount'];
                                                $manager_paid_total += (float)$manger_ac['manager_paid'];  
                                                $manager_self = (float)$paid_amu-$manager_paid_total;
                                            }

                                            // কন্টেইনার শুরু (গ্যাপ কমানোর জন্য CSS ব্যবহার করা হয়েছে)
                                            echo '<div style="display: flex; flex-direction: column; line-height: 1.2; gap: 1px;">';

                                                // পেমেন্ট মেথড
                                                echo "<small class='text-success fw-bold' style='font-size: 11px;'>$pay_method_his</small>";

                                                if ($manager_paid_total > 0) {
                                                    echo "<small class='text-dark' style='font-size: 9px;'><b>Manager (Paid):  ৳</b> " . number_format($manager_paid_total, 0) . "</small>";
                                                }

                                                if ($manager_self > 0) {
                                                    echo "<small class='text-danger' style='font-size: 9px;'><b>Manager (Self):  ৳</b> " . number_format($manager_self, 0) . "</small>";
                                                }

                                                // ট্রানজেকশন ডাটা এরে
                                                $details = [
                                                    ['Txn ID', $transaction_id_db],
                                                    ['Payment Method', $manager_payment_method],
                                                    ['Txn Number', $transaction_number],
                                                    ['Date', $transaction_date]
                                                ];

                                                foreach ($details as $detail) {
                                                    if (!empty($detail[1])) {
                                                        echo "<small style='font-size: 8.5px; color: #666;'>{$detail[0]}: {$detail[1]}</small>";
                                                    }
                                                }

                                            echo "</div>"; // কন্টেইনার শেষ
                                        }
                                        ?> 
                                    </td>

                                    <td>
                                        <div class="btn-group align-items-center">
                                            <!-- <button class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button> -->
                                            <a href="admin.php?page=editbill&unit_id=<?= $unit_id ?>"
                                                class="text-end p-1 btn btn-sm btn-info" title="Invoice Create & Payment">
                                                Details
                                            </a>
                                            <a href="admin.php?page=bill&unit_id=<?= $unit_id ?>&id=<?= $building_name ?>" onclick="sendWhatsApp()" class="p-1 btn btn-sm btn-success" title="Message Send with Copy">
                                                <i class="bi bi-send"></i>
                                            </a>

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