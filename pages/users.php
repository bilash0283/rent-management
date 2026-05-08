<div class="nxl-content" style="background: #f8f9fa; min-height: 100vh; padding-bottom: 50px;">
    <!-- Mobile App Style Header -->
    <div class="page-header d-flex align-items-center justify-content-between p-3 bg-white shadow-sm sticky-top">
        <h5 class="mb-0 fw-bold text-dark">Users</h5>
        <a href="" class="btn btn-primary btn-sm rounded-pill px-3">
            <i class="feather-plus me-1"></i> Add New
        </a>
    </div>

    <!-- Main Content Area -->
    <div class="container-fluid py-3">
        <div class="main-content">
            
            <!-- Message Alert -->
            <?php if(isset($message) && $message != ''): ?>
                <div class="alert alert-info py-2 small shadow-sm"><?= $message ?></div>
            <?php endif; ?>

            <!-- Mobile-Friendly User List -->
            <div class="row g-3">
                <?php
                $user_sql = mysqli_query($db, "SELECT * FROM `users` ORDER BY id DESC");
                while ($row = mysqli_fetch_assoc($user_sql)) {
                    $unit_id = $row['id'];
                    $name    = $row['name'];
                    $email   = $row['email'];
                    $phone   = $row['phone'];
                    $role    = $row['role'];
                ?>
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-body p-3">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <!-- Avatar Placeholder -->
                                    <div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 45px; height: 45px;">
                                        <i class="bi bi-person text-secondary fs-4"></i>
                                    </div>
                                    <div>
                                        <a href="admin.php?page=view_tenant&id=<?= $unit_id; ?>" class="text-decoration-none">
                                            <h6 class="mb-0 fw-bold text-dark"><?= $name; ?></h6>
                                        </a>
                                        <small class="text-muted d-block"><?= $email; ?></small>
                                    </div>
                                </div>
                                
                                <!-- Role Badge -->
                                <div>
                                    <?php if($role == 1): ?>
                                        <span class="badge bg-success-light text-success border border-success-subtle rounded-pill">Admin</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-light text-warning border border-warning-subtle rounded-pill">Manager</span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <hr class="my-2 opacity-25">

                            <div class="d-flex align-items-center justify-content-between mt-2">
                                <div class="text-secondary small">
                                    <i class="bi bi-telephone me-1"></i> <?= $phone ?>
                                </div>
                                
                                <!-- Action Buttons -->
                                <div class="d-flex gap-2">
                                    <a href="#" class="btn btn-light btn-sm rounded-circle shadow-sm" title="Edit">
                                        <i class="bi bi-pencil-square text-primary"></i>
                                    </a>
                                    <a href="#" class="btn btn-light btn-sm rounded-circle shadow-sm" title="View">
                                        <i class="bi bi-eye text-success"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>

            <!-- Empty State (Optional) -->
            <?php if(mysqli_num_rows($user_sql) == 0): ?>
                <div class="text-center py-5">
                    <i class="bi bi-people text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted">No users found.</p>
                </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Extra CSS for App Feel -->
<style>
    .bg-success-light { background-color: #e8f5e9; }
    .bg-warning-light { background-color: #fff3e0; }
    .card { transition: transform 0.2s; }
    .card:active { transform: scale(0.98); }
    .rounded-4 { border-radius: 1rem !important; }
    .btn-light { background: #f1f3f5; border: none; }
</style>