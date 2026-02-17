<?php
if (isset($_GET['id'])) {
    $tenant_id = $_GET['id'];
}

$message = "";
/* ================= FETCH TENANTS ================= */
$query = "
        SELECT 
            t.*, 
            b.name AS building_name,
            u.unit_name,
            u.status
        FROM tenants t
        JOIN building b ON t.building_id = b.id
        JOIN unit u ON t.unit_id = u.id
        WHERE t.id = $tenant_id ORDER BY t.id DESC
    ";
$result = mysqli_query($db, $query);
?>

<?php
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $image = !empty($row['tenant_image'])
            ? "public/uploads/tenants/" . $row['tenant_image']
            : "public/uploads/tenants/no-image.png";

        $nid = !empty($row['nid_image'])
            ? "public/uploads/nid/" . $row['nid_image']
            : "public/uploads/tenants/no-image.png";

        $id = $row['id'];
        $phone = $row['phone'];
        $building_name = $row['building_name'];
        $unit_name = $row['unit_name'];
        $name = $row['name'];
        $email = $row['email'];
        $unit_id = $row['unit_id'];
    }
}
?>

<div class="nxl-content">
    <!-- Page Header -->
    <div class="page-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Tenant Information</h5>

        <a href="admin.php?page=tenant" class="btn btn-primary">
            Back
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content ">
        <div class="bg-light">
            <div class="container">
                <div class="row">
                    <!-- Profile Header -->
                    <div class="col-12 ">
                        <div class="profile-header position-relative mb-2">
                            <div class="position-absolute top-0 end-0 p-3">
                                <a href="admin.php?page=CreateTenant&edit_id=<?php echo $id; ?>" class="btn btn-light"><i class="fas fa-edit me-2"></i>Edit Profile</a>
                            </div>
                        </div>

                        <div class="text-center">
                            <div class="position-relative d-inline-block">
                                <img src="<?= $image ?>"
                                    class="rounded-circle profile-pic" alt="Profile Picture">
                            </div>
                            <h3 class="mt-3 mb-1"><?= $name; ?></h3>
                            <p class="text-muted mb-3"><?= $phone ?></p>
                            <p class="text-muted mb-3"><?= $email ?></p>
                            <div class="d-flex justify-content-center gap-2 mb-4">
                                <a href="admin.php?page=editbill&unit_id=<?= $unit_id; ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-credit-card me-2"></i>Payment
                                </a>

                                <a href="admin.php?page=Agreement&id=<?= $id; ?>" class="btn btn-primary">
                                    <i class="fas fa-file-contract me-2"></i>Agreement
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="col-12">
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-0">
                                <div class="row g-0">
                                    <!-- Sidebar -->
                                    <div class="col-lg-3 border-end">
                                        <div class="p-4">
                                            <div class="nav flex-column nav-pills">
                                                <label for="">NID</label>
                                                <img src="<?= $image ?>" alt="" class="img-fluid">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Content Area -->
                                    <div class="col-lg-9">
                                        <div class="p-4">
                                            <!-- Personal Information -->
                                            <div class="mb-4">
                                                <h5 class="mb-4">Building Information</h5>
                                            </div>

                                            <!-- Settings Cards -->
                                            <div class="row g-4 mb-4">
                                                <div class="col-md-6">
                                                    <div class="settings-card card">
                                                        <div class="card-body">
                                                            <div class="d-flex align-items-center">

                                                                <!-- Icon Section -->
                                                                <div class="bg-primary bg-opacity-10 text-white 
                                                                            rounded-circle d-flex align-items-center 
                                                                            justify-content-center me-3"
                                                                    style="width:60px; height:60px;">

                                                                    <i class="fas fa-building fa-2x"></i>
                                                                </div>

                                                                <!-- Building Info -->
                                                                <div>
                                                                    <h5 class="mb-1 fw-bold"><?= $building_name ?></h5>
                                                                    <small class="text-muted">Building Information</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="settings-card card">
                                                        <div class="card-body">
                                                            <div class="d-flex align-items-center">

                                                                <!-- Icon Section -->
                                                                <div class="bg-primary bg-opacity-10 text-white 
                                                                            rounded-circle d-flex align-items-center 
                                                                            justify-content-center me-3"
                                                                    style="width:60px; height:60px;">

                                                                    <i class="fas fa-door-open fa-2x"></i>
                                                                </div>

                                                                <!-- Building Info -->
                                                                <div>
                                                                    <h5 class="mb-1 fw-bold"><?= $unit_name ?></h5>
                                                                    <small class="text-muted">Unit Information</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Recent Activity -->
                                            <div>
                                                <h5 class="mb-4">Monthly bills (invoice history)</h5>
                                                <?php
                                                $pay_info = mysqli_query($db, "SELECT * FROM invoices WHERE tenant_id = '$id' AND unit_id = '$unit_id' ORDER BY billing_month ");
                                                    while ($pay_info_row = mysqli_fetch_assoc($pay_info)) {
                                                        $billing_month_db = $pay_info_row['billing_month'];
                                                        $paid_amount_db = $pay_info_row['paid_amount'];
                                                        $due_amount_db = $pay_info_row['due_amount'];
                                                        $status = $pay_info_row['status'];
                                                    
                                                ?>
                                                    <div class="activity-item mb-3">
                                                        <h6 class="mb-1"><?= date(' M Y', strtotime($billing_month_db)) ?></h6>
                                                        <span class="text-muted small mb-0">🟢 <small>৳ </small><?= $paid_amount_db ?></span> <span class="text-muted small mb-0">🔴 <small>৳ </small> <?= $due_amount_db ?></span>
                                                    </div>
                                                <?php } ?>
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
    </div>

</div>