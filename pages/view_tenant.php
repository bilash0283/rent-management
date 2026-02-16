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
            : "assets/images/no-image.png";

        $id = $row['id'];
        $phone = $row['phone'];
        $building_name = $row['building_name'];
        $unit_name = $row['unit_name'];
    }
}
?>
<!-- <span class="badge <?= $row['status'] == 'Rented' ? 'bg-danger' : 'bg-success' ?>">
    <?= $row['status'] ?>
</span>

<a href="admin.php?page=CreateTenant&edit_id=<?= $row['id'] ?>" class="btn btn-sm btn-light-primary" title="Edit">
    <i class="feather-edit"></i>
</a> -->

<style>
   
</style>

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
            <div class="container py-5">
                <div class="row">
                    <!-- Profile Header -->
                    <div class="col-12 mb-4">
                        <div class="profile-header position-relative mb-4">
                            <div class="position-absolute top-0 end-0 p-3">
                                <button class="btn btn-light"><i class="fas fa-edit me-2"></i>Edit Cover</button>
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="position-relative d-inline-block">
                                <img src="https://randomuser.me/api/portraits/men/40.jpg"
                                    class="rounded-circle profile-pic" alt="Profile Picture">
                                <button class="btn btn-primary btn-sm position-absolute bottom-0 end-0 rounded-circle">
                                    <i class="fas fa-camera"></i>
                                </button>
                            </div>
                            <h3 class="mt-3 mb-1">Alex Johnso</h3>
                            <p class="text-muted mb-3">Senior Product Designer</p>
                            <div class="d-flex justify-content-center gap-2 mb-4">
                                <button class="btn btn-outline-primary"><i
                                        class="fas fa-envelope me-2"></i>Message</button>
                                <button class="btn btn-primary"><i class="fas fa-user-plus me-2"></i>Connect</button>
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
                                                <a class="nav-link active" href="#"><i
                                                        class="fas fa-user me-2"></i>Personal
                                                    Info</a>
                                                <a class="nav-link" href="#"><i
                                                        class="fas fa-lock me-2"></i>Security</a>
                                                <a class="nav-link" href="#"><i
                                                        class="fas fa-bell me-2"></i>Notifications</a>
                                                <a class="nav-link" href="#"><i
                                                        class="fas fa-credit-card me-2"></i>Billing</a>
                                                <a class="nav-link" href="#"><i
                                                        class="fas fa-chart-line me-2"></i>Activity</a>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Content Area -->
                                    <div class="col-lg-9">
                                        <div class="p-4">
                                            <!-- Personal Information -->
                                            <div class="mb-4">
                                                <h5 class="mb-4">Personal Information</h5>
                                                <div class="row g-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label">First Name</label>
                                                        <input type="text" class="form-control" value="Alex">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Last Name</label>
                                                        <input type="text" class="form-control" value="Johnson">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Email</label>
                                                        <input type="email" class="form-control"
                                                            value="alex.johnson@example.com">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label">Phone</label>
                                                        <input type="tel" class="form-control"
                                                            value="+1 (555) 123-4567">
                                                    </div>
                                                    <div class="col-12">
                                                        <label class="form-label">Bio</label>
                                                        <textarea class="form-control"
                                                            rows="4">Product designer with 5+ years of experience in creating user-centered digital solutions. Passionate about solving complex problems through simple and elegant designs.</textarea>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Settings Cards -->
                                            <div class="row g-4 mb-4">
                                                <div class="col-md-6">
                                                    <div class="settings-card card">
                                                        <div class="card-body">
                                                            <div
                                                                class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <h6 class="mb-1">Two-Factor Authentication</h6>
                                                                    <p class="text-muted mb-0 small">Add an extra layer
                                                                        of
                                                                        security</p>
                                                                </div>
                                                                <div class="form-check form-switch">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        checked>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="settings-card card">
                                                        <div class="card-body">
                                                            <div
                                                                class="d-flex justify-content-between align-items-center">
                                                                <div>
                                                                    <h6 class="mb-1">Email Notifications</h6>
                                                                    <p class="text-muted mb-0 small">Receive activity
                                                                        updates
                                                                    </p>
                                                                </div>
                                                                <div class="form-check form-switch">
                                                                    <input class="form-check-input" type="checkbox"
                                                                        checked>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Recent Activity -->
                                            <div>
                                                <h5 class="mb-4">Recent Activity</h5>
                                                <div class="activity-item mb-3">
                                                    <h6 class="mb-1">Updated profile picture</h6>
                                                    <p class="text-muted small mb-0">2 hours ago</p>
                                                </div>
                                                <div class="activity-item mb-3">
                                                    <h6 class="mb-1">Changed password</h6>
                                                    <p class="text-muted small mb-0">Yesterday</p>
                                                </div>
                                                <div class="activity-item">
                                                    <h6 class="mb-1">Updated billing information</h6>
                                                    <p class="text-muted small mb-0">3 days ago</p>
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

</div>