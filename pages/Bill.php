<?php
    if(isset($_GET['id'])){
        $building_id = $_GET['id'];
          // Fetch all units
        $query  = "SELECT * FROM unit WHERE building_name = '$building_id' AND status = 'Rented' ORDER BY id DESC";
        $result = mysqli_query($db, $query);
        if (!$result) {
            die("Query Failed: " . mysqli_error($db));
        }

        // fetch building 
        $buil_sql = mysqli_query($db,"SELECT * FROM `building` WHERE id = '$building_id' ");
        while($buli_name = mysqli_fetch_assoc($buil_sql)){
            $building_name_db = $buli_name['name'];
        }
    }
?>

<div class="nxl-content">
    <!-- Page Header -->
    <div class="page-header d-flex align-items-center justify-content-between">
       
        <h5 class="mb-0">
            <?= $building_name_db ?? ''; ?> / Bill Month (<?php echo date('M - Y') ?>)
        </h5>
       
        <!-- <a href="admin.php?page=CreateTenant" class="btn btn-primary">
            <i class="feather-plus me-1"></i> Create Bill
        </a> -->
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">Bill List</h6>
                <?= $message ?? '' ?>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tenant</th>
                                <th>Advance</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($row = mysqli_fetch_assoc($result)) {
                                $unit_id = $row['id'];
                                $advance = $row['advance'];
                                $rent = $row['rent'];
                                $water = $row['water'];
                                $gas = $row['gas'];

                                $building_name = $row['building_name'];
                                $total_bill = $rent + $water + $gas;

                                ?>
                                <tr>
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
                                        <div class="d-flex align-items-center col-span">
                                            <img src="<?= htmlspecialchars($image) ?>"
                                             width="50" height="50"
                                             style="object-fit:cover;border-radius:6px;border-radius:50%;" class="mx-auto">
                                             
                                            <a href="admin.php?page=view_tenant&id=<?= $tent_id ?>" class="text-secendary fw-bold">
                                                <?= $name; ?>
                                            </a>
                                        </div>                                        
                                    </td>

                                    <td>
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
                                        echo '<span style="color: #0d6efd; font-weight: 600;">Advance = ৳ ' . $advance . '</span><br>';  // Blue
                                        echo '<span style="color: #198754; font-weight: 600;">Paid    = ৳ ' . $total_paid . '</span><br>'; // Green
                                    
                                        // Show Due only if payable > 0
                                        if ($payable > 0) {
                                            echo '<span style="color: #dc3545; font-weight: 600;">Due     = ৳ ' . $payable . '</span><br>'; // Red
                                        }
                                        ?>
                                    </td>

                                    <td>

                                        <?php 
                                            if(!empty($rent)){ ?>
                                                <span class="fw-semibold text-primary">
                                            Rent = ৳ <?= number_format($rent, 2) ?? '' ?>
                                            </span><br>
                                        <?php } ?> 

                                        <?php 
                                            if(!empty($water)){ ?>
                                                <span class="fw-semibold text-primary">
                                            Water = ৳ <?= number_format($water, 2) ?? '' ?>
                                            </span><br>
                                        <?php } ?> 

                                        <?php 
                                            if(!empty($gas)){ ?>
                                                <span class="fw-semibold text-primary">
                                            Gas = ৳ <?= number_format($gas, 2) ?? '' ?>
                                            </span><br>
                                        <?php } ?> 

                                        <?php
                                        $pay_info = mysqli_query($db, "SELECT * FROM invoices WHERE tenant_id = '$tent_id' AND unit_id = '$unit_id' AND billing_month = '$this_month' ");
                                        if (!mysqli_num_rows($pay_info) > 0) {
                                            echo '<span class="fw-bold text-danger">';
                                            echo 'Due = ৳ ' . number_format($total_bill, 2);
                                            echo '</span>';
                                        } else {
                                            mysqli_data_seek($pay_info, 0); // rewind result to loop again
                                            while ($pay_info_sh = mysqli_fetch_assoc($pay_info)) {
                                                $billing_month_db = $pay_info_sh['billing_month'];
                                                $paid_amount_db = $pay_info_sh['paid_amount'];
                                                $due_amount_db = $pay_info_sh['due_amount'];
                                                $created_at = $pay_info_sh['created_at'];
                                                $status = $pay_info_sh['status'];

                                                $Gas_db = $pay_info_sh['Gas'];
                                                $Water_db = $pay_info_sh['Water'];
                                                $Electricity_db = $pay_info_sh['Electricity'];
                                                $Others_db = $pay_info_sh['Others'];
                                                ?>                                        

                                                <span class="fw-semibold text-primary">
                                                    Total = ৳ <?= number_format($total_bill, 2) ?? '' ?>
                                                </span><br>

                                                <?php if (!empty($paid_amount_db)) { ?>
                                                    <span class="fw-semibold text-success">
                                                        Paid = ৳ <?= number_format($paid_amount_db, 2) ?? '' ?>
                                                    </span><br>
                                                <?php } ?>

                                                <?php if (!empty($due_amount_db)) { ?>
                                                    <span class="fw-semibold text-danger">
                                                        Due = ৳ <?= number_format($due_amount_db, 2) ?? '' ?>
                                                    </span><br>
                                                <?php }
                                            }
                                        } ?>

                                    </td>

                                    <td>
                                        <?php
                                        if (!mysqli_num_rows($pay_info) > 0) {
                                            echo "<button class='btn btn-sm btn-primary'>Pending</button>";
                                        } else {
                                            ?>
                                            <button
                                                class="btn btn-sm btn-<?php if ($status == 'Paid') {
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
                                        <div class="btn-group align-items-center">
                                            <!-- <button class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </button> -->
                                            <!-- <a class="btn btn-sm btn-outline-success" title="Invoice">
                                                <i class="bi bi-eye"></i>
                                            </a> -->
                                            <a href="admin.php?page=editbill&unit_id=<?= $unit_id ?>"
                                                class="text-end btn btn-sm btn-outline-success" title="Add Payment">
                                                Add Payment
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