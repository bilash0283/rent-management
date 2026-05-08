<?php
if (isset($_GET['id'])) {
    $building_id = $_GET['id'];
    $query = "SELECT * FROM unit WHERE building_name = '$building_id' AND status = 'Rented' ";
    $result = mysqli_query($db, $query);
    $totla_unit = mysqli_num_rows($result);

    if (!$result) {
        die("Query Failed: " . mysqli_error($db));
    }

    $buil_sql = mysqli_query($db, "SELECT * FROM `building` WHERE id = '$building_id' ");
    while ($buli_name = mysqli_fetch_assoc($buil_sql)) {
        $building_name_db = $buli_name['name'];
    }
}
?>

<style>
    /* Mobile-specific Card Design */
    @media (max-width: 767.98px) {
        .mobile-card-view {
            display: block;
        }

        .desktop-table-view {
            display: none;
        }

        .mobile-unit-card {
            background: #fff;
            border-radius: 12px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-left: 5px solid #28a745;
        }

        .status-badge-mobile {
            position: absolute;
            top: 15px;
            right: 15px;
        }
    }

    @media (min-width: 768px) {
        .mobile-card-view {
            display: none;
        }

        .desktop-table-view {
            display: block;
        }
    }
</style>

<div class="nxl-content">
    <!-- Page Header -->
    <div class="page-header d-flex align-items-center justify-content-between p-3 bg-white mb-3 shadow-sm rounded">
        <h5 class="mb-0 fw-bold">
            <?= $building_name_db ?? ''; ?>
            <span class="badge rounded-pill bg-success ms-2"><?= $totla_unit ?? ''; ?> Units</span>
        </h5>
        <div class="text-muted small fw-medium text-uppercase">
            <i class="bi bi-calendar-event me-1"></i> <?php echo date('M - Y') ?>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content px-2">

        <!-- Mobile Card View -->
        <div class="mobile-card-view">

            <?php
            mysqli_data_seek($result, 0);
            $i = 0;

            while ($row = mysqli_fetch_assoc($result)) {

                $i++;

                $unit_id = $row['id'];
                $rent = $row['rent'];
                $unit_name = $row['unit_name'];
                $building_name = $row['building_name'];
                $size = $row['size'];

                // Latest Tenant
                $sql_tenant = mysqli_query($db, "
                SELECT * 
                FROM tenants 
                WHERE building_id = '$building_name' 
                AND unit_id = '$unit_id'
                ORDER BY id DESC
                LIMIT 1
            ");

                $tent_row = mysqli_fetch_assoc($sql_tenant);

                $name = $tent_row['name'] ?? 'No Tenant';
                $tent_id = $tent_row['id'] ?? 0;
                $tent_phone = $tent_row['phone'] ?? '';

                $image = (!empty($tent_row['tenant_image']))
                    ? "public/uploads/tenants/" . $tent_row['tenant_image']
                    : "public/uploads/tenants/no-image.png";


                // Latest Invoice
                $pay_info = mysqli_query($db, "
                SELECT * 
                FROM invoices 
                WHERE tenant_id = '$tent_id'
                AND unit_id = '$unit_id'
                AND billing_month = '$this_month'
                ORDER BY id DESC
                LIMIT 1
            ");

                $pay_data = mysqli_fetch_assoc($pay_info);

                $status = $pay_data['status'] ?? 'No Invoice';
                $total_amount_db = $pay_data['total_amount'] ?? $rent;
                $paid_amount_db = $pay_data['paid_amount'] ?? 0;
                $due_amount_db = $total_amount_db - $paid_amount_db;

                ?>

                <div class="mobile-unit-card position-relative">

                    <div class="d-flex align-items-center mb-3">

                        <img src="<?= $image ?>" class="rounded-circle me-3 border" width="55" height="55"
                            style="object-fit: cover;">

                        <div>
                            <p class="mb-0 fw-bold text-dark"><?= $name ?></p>

                            <small class="text-muted">
                                Unit: <strong><?= $unit_name ?></strong>

                                <?php if ($size) { ?>
                                    | Ele: <?= $size ?>
                                <?php } ?>

                            </small>
                        </div>

                    </div>


                    <!-- Status -->

                    <div class="status-badge-mobile">

                        <?php

                        $badgeColor = 'warning';

                        if ($status == 'Paid') {
                            $badgeColor = 'success';
                        } elseif ($status == 'Unpaid') {
                            $badgeColor = 'danger';
                        }

                        ?>

                        <span
                            class="badge bg-<?= $badgeColor ?>-subtle text-<?= $badgeColor ?> border border-<?= $badgeColor ?>">
                            <?= $status ?>
                        </span>

                    </div>


                    <!-- Bill Summary -->

                    <div class="row g-2 text-center mb-3 border-top pt-2">

                        <div class="col-4 border-end">
                            <div class="text-muted small">Total</div>
                            <div class="fw-bold text-primary">
                                ৳<?= number_format($total_amount_db, 0) ?>
                            </div>
                        </div>

                        <div class="col-4 border-end">
                            <div class="text-muted small">Paid</div>
                            <div class="fw-bold text-success">
                                ৳<?= number_format($paid_amount_db, 0) ?>
                            </div>
                        </div>

                        <div class="col-4">
                            <div class="text-muted small">Due</div>
                            <div class="fw-bold text-danger">
                                ৳<?= number_format($due_amount_db, 0) ?>
                            </div>
                        </div>

                    </div>


                    <!-- Buttons -->

                    <div class="d-flex gap-2">

                        <a href="admin.php?page=editbill&unit_id=<?= $unit_id ?>"
                            class="btn btn-outline-info btn-sm flex-grow-1 py-2 rounded-pill">

                            <i class="bi bi-eye me-1"></i>
                            Details

                        </a>

                        <a href="admin.php?page=bill&unit_id=<?= $unit_id ?>&id=<?= $building_name ?>"
                            onclick="sendWhatsApp()" class="btn btn-success btn-sm flex-grow-1 py-2 rounded-pill">

                            <i class="bi bi-whatsapp me-1"></i>
                            Send Bill

                        </a>

                    </div>

                </div>

            <?php } ?>

        </div>




        <!-- Desktop Table View -->

        <div class="desktop-table-view card shadow-sm rounded-3 overflow-hidden">

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead class="table-light text-uppercase small fw-bold">

                        <tr>
                            <th class="ps-3">SL</th>
                            <th>Unit / Tenant</th>
                            <th>Bill Summary</th>
                            <th>Status</th>
                            <th>Payment Details</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php

                        mysqli_data_seek($result, 0);
                        $i = 0;

                        while ($row = mysqli_fetch_assoc($result)) {

                            $i++;

                            $unit_id = $row['id'];
                            $rent = $row['rent'];
                            $unit_name = $row['unit_name'];
                            $building_name = $row['building_name'];

                            // Latest Tenant
                            $sql_tenant = mysqli_query($db, "
                        SELECT * 
                        FROM tenants 
                        WHERE building_id = '$building_name'
                        AND unit_id = '$unit_id'
                        ORDER BY id DESC
                        LIMIT 1
                    ");

                            $tent_row = mysqli_fetch_assoc($sql_tenant);

                            $name = $tent_row['name'] ?? 'No Tenant';
                            $tent_id = $tent_row['id'] ?? 0;

                            $image = (!empty($tent_row['tenant_image']))
                                ? "public/uploads/tenants/" . $tent_row['tenant_image']
                                : "public/uploads/tenants/no-image.png";


                            // Latest Invoice
                            $pay_info = mysqli_query($db, "
                        SELECT * 
                        FROM invoices
                        WHERE tenant_id = '$tent_id'
                        AND unit_id = '$unit_id'
                        AND billing_month = '$this_month'
                        ORDER BY id DESC
                        LIMIT 1
                    ");

                            $p = mysqli_fetch_assoc($pay_info);

                            ?>

                            <tr>

                                <td class="ps-3"><?= $i ?></td>

                                <td>

                                    <div class="d-flex align-items-center">

                                        <img src="<?= $image ?>" class="rounded-circle me-2" width="40" height="40"
                                            style="object-fit:cover;">

                                        <div>

                                            <div class="fw-bold">
                                                <?= $unit_name ?>
                                            </div>

                                            <a href="admin.php?page=view_tenant&id=<?= $tent_id ?>"
                                                class="text-secondary small">

                                                <?= $name ?>

                                            </a>

                                        </div>

                                    </div>

                                </td>


                                <!-- Bill Summary -->

                                <td class="small">

                                    <?php

                                    if ($p) {

                                        $total = $p['total_amount'];
                                        $paid = $p['paid_amount'];
                                        $due = $total - $paid;

                                        echo "<span class='text-primary'>Total: ৳" . number_format($total, 0) . "</span><br>";

                                        echo "<span class='text-success'>Paid: ৳" . number_format($paid, 0) . "</span><br>";

                                        echo "<span class='text-danger fw-bold'>Due: ৳" . number_format($due, 0) . "</span>";

                                        $status = $p['status'];

                                    } else {

                                        echo "<span class='text-danger'>Due: ৳" . number_format($rent, 0) . "</span>";

                                        $status = "No Invoice";
                                    }

                                    ?>

                                </td>


                                <!-- Status -->

                                <td>

                                    <?php

                                    $badge = 'warning';

                                    if ($status == 'Paid') {
                                        $badge = 'success';
                                    } elseif ($status == 'Unpaid') {
                                        $badge = 'danger';
                                    }

                                    ?>

                                    <span class="badge bg-<?= $badge ?>">
                                        <?= $status ?>
                                    </span>

                                </td>


                                <!-- Payment History -->

                                <td>

                                    <?php

                                    $history_sql = mysqli_query($db, "
                                SELECT * 
                                FROM payment_history 
                                WHERE tenant_id = '$tent_id'
                                AND bill_month = '$this_month'
                            ");

                                    if (mysqli_num_rows($history_sql) > 0) {
                                        echo "<small class='text-success'>Payment Found</small>";
                                    } else {
                                        echo "<small class='text-warning'>Pending</small>";
                                    }
                                    ?>
                                </td>
                                <!-- Action -->
                                <td class="text-end pe-3">
                                    <div class="btn-group">
                                        <a href="admin.php?page=editbill&unit_id=<?= $unit_id ?>"
                                            class="btn btn-sm btn-info text-white">
                                            Details
                                        </a>
                                        <a href="#" onclick="sendWhatsApp()" class="btn btn-sm btn-success">
                                            <i class="bi bi-whatsapp"></i>
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

<!-- Scripts remain same with your WhatsApp logic -->
<?php
// ... [আপনার বিদ্যমান WhatsApp Message তৈরির PHP কোডটুকু এখানে থাকবে] ...
?>
<script>
    function sendWhatsApp() {
        // ... [আপনার বিদ্যমান WhatsApp JS কোডটুকু এখানে থাকবে] ...
    }
</script>