<?php
// building 
$builiding = mysqli_query($db, "SELECT * FROM building ");
$total_building = mysqli_num_rows($builiding);
while ($buill_info = mysqli_fetch_assoc($builiding)) {
    $buill_id = $buill_info['id'];
}

// unit 
$unit = mysqli_query($db, "SELECT * FROM unit ");
$total_unit = mysqli_num_rows($unit);
while ($unit_info = mysqli_fetch_assoc($unit)) {
    $unit_id = $unit_info['id'];
    $unit_id = $unit_info['id'];
    $unit_id = $unit_info['id'];
    $unit_id = $unit_info['id'];
    $unit_id = $unit_info['id'];
}

// tenant 
$tenant = mysqli_query($db, "SELECT * FROM tenants ");
$total_tenant = mysqli_num_rows($tenant);

?>

<div class="nxl-content">
    <!-- [ Main Content ] start -->
    <div class="main-content">
        <div class="row">

            <!-- Dashboard Cards -->
            <div class="row">
                <!-- Building Card -->
                <div class="col-lg-4">
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

                                <h3 class="fw-bold text-primary mb-0">
                                    <?= $total_building ?>
                                </h3>

                            </div>
                        </div>
                    </a>
                </div>
                <!-- Unit Card -->
                <div class="col-lg-4">
                    <a href="admin.php?page=unit&id=<?= $buill_id ?>" class="text-decoration-none">
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

                                <h3 class="fw-bold text-success mb-0">
                                    <?= $total_unit ?>
                                </h3>

                            </div>
                        </div>
                    </a>
                </div>
                <!-- Tenant Card -->
                <div class="col-lg-4">
                    <a href="admin.php?page=tenant" class="text-decoration-none">
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

                                <h3 class="fw-bold text-warning mb-0">
                                    <?= $total_tenant ?>
                                </h3>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- [Monthly Payment Records] end -->
            <div class="col-xxl-8">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Monthly Payment Record</h5>
                        <div class="card-header-action">
                            <div class="card-header-btn">
                                <div data-bs-toggle="tooltip" title="Delete">
                                    <a href="javascript:void(0);" class="avatar-text avatar-xs bg-danger"
                                        data-bs-toggle="remove"> </a>
                                </div>
                                <div data-bs-toggle="tooltip" title="Refresh">
                                    <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning"
                                        data-bs-toggle="refresh"> </a>
                                </div>
                                <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                                    <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success"
                                        data-bs-toggle="expand"> </a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a href="javascript:void(0);" class="avatar-text avatar-sm" data-bs-toggle="dropdown"
                                    data-bs-offset="25, 25">
                                    <div data-bs-toggle="tooltip" title="Options">
                                        <i class="feather-more-vertical"></i>
                                    </div>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a href="javascript:void(0);" class="dropdown-item"><i
                                            class="feather-at-sign"></i>New</a>
                                    <a href="javascript:void(0);" class="dropdown-item"><i
                                            class="feather-calendar"></i>Event</a>
                                    <a href="javascript:void(0);" class="dropdown-item"><i
                                            class="feather-bell"></i>Snoozed</a>
                                    <a href="javascript:void(0);" class="dropdown-item"><i
                                            class="feather-trash-2"></i>Deleted</a>
                                    <div class="dropdown-divider"></div>
                                    <a href="javascript:void(0);" class="dropdown-item"><i
                                            class="feather-settings"></i>Settings</a>
                                    <a href="javascript:void(0);" class="dropdown-item"><i
                                            class="feather-life-buoy"></i>Tips & Tricks</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body custom-card-action p-0">
                        <div id="payment-records-chart"></div>
                    </div>
                    <div class="card-footer">
                        <div class="row g-4">
                            <div class="col-lg-3">
                                <div class="p-3 border border-dashed rounded">
                                    <div class="fs-12 text-muted mb-1">Awaiting</div>
                                    <h6 class="fw-bold text-dark">$5,486</h6>
                                    <div class="progress mt-2 ht-3">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 81%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="p-3 border border-dashed rounded">
                                    <div class="fs-12 text-muted mb-1">Completed</div>
                                    <h6 class="fw-bold text-dark">$9,275</h6>
                                    <div class="progress mt-2 ht-3">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 82%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="p-3 border border-dashed rounded">
                                    <div class="fs-12 text-muted mb-1">Rejected</div>
                                    <h6 class="fw-bold text-dark">$3,868</h6>
                                    <div class="progress mt-2 ht-3">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: 68%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="p-3 border border-dashed rounded">
                                    <div class="fs-12 text-muted mb-1">Revenue</div>
                                    <h6 class="fw-bold text-dark">$50,668</h6>
                                    <div class="progress mt-2 ht-3">
                                        <div class="progress-bar bg-dark" role="progressbar" style="width: 75%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [Monthly Payment Records] end -->

            <!-- [Payment Records] end -->
            <div class="col-xxl-8">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Payment Record</h5>
                        <div class="card-header-action">
                            <div class="card-header-btn">
                                <div data-bs-toggle="tooltip" title="Delete">
                                    <a href="javascript:void(0);" class="avatar-text avatar-xs bg-danger"
                                        data-bs-toggle="remove"> </a>
                                </div>
                                <div data-bs-toggle="tooltip" title="Refresh">
                                    <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning"
                                        data-bs-toggle="refresh"> </a>
                                </div>
                                <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                                    <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success"
                                        data-bs-toggle="expand"> </a>
                                </div>
                            </div>
                            <div class="dropdown">
                                <a href="javascript:void(0);" class="avatar-text avatar-sm" data-bs-toggle="dropdown"
                                    data-bs-offset="25, 25">
                                    <div data-bs-toggle="tooltip" title="Options">
                                        <i class="feather-more-vertical"></i>
                                    </div>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end">
                                    <a href="javascript:void(0);" class="dropdown-item"><i
                                            class="feather-at-sign"></i>New</a>
                                    <a href="javascript:void(0);" class="dropdown-item"><i
                                            class="feather-calendar"></i>Event</a>
                                    <a href="javascript:void(0);" class="dropdown-item"><i
                                            class="feather-bell"></i>Snoozed</a>
                                    <a href="javascript:void(0);" class="dropdown-item"><i
                                            class="feather-trash-2"></i>Deleted</a>
                                    <div class="dropdown-divider"></div>
                                    <a href="javascript:void(0);" class="dropdown-item"><i
                                            class="feather-settings"></i>Settings</a>
                                    <a href="javascript:void(0);" class="dropdown-item"><i
                                            class="feather-life-buoy"></i>Tips & Tricks</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-body custom-card-action p-0">
                        <div id="payment-records-chart"></div>
                    </div>
                    <div class="card-footer">
                        <div class="row g-4">
                            <div class="col-lg-3">
                                <div class="p-3 border border-dashed rounded">
                                    <div class="fs-12 text-muted mb-1">Awaiting</div>
                                    <h6 class="fw-bold text-dark">$5,486</h6>
                                    <div class="progress mt-2 ht-3">
                                        <div class="progress-bar bg-primary" role="progressbar" style="width: 81%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="p-3 border border-dashed rounded">
                                    <div class="fs-12 text-muted mb-1">Completed</div>
                                    <h6 class="fw-bold text-dark">$9,275</h6>
                                    <div class="progress mt-2 ht-3">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 82%">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="p-3 border border-dashed rounded">
                                    <div class="fs-12 text-muted mb-1">Rejected</div>
                                    <h6 class="fw-bold text-dark">$3,868</h6>
                                    <div class="progress mt-2 ht-3">
                                        <div class="progress-bar bg-danger" role="progressbar" style="width: 68%"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-3">
                                <div class="p-3 border border-dashed rounded">
                                    <div class="fs-12 text-muted mb-1">Revenue</div>
                                    <h6 class="fw-bold text-dark">$50,668</h6>
                                    <div class="progress mt-2 ht-3">
                                        <div class="progress-bar bg-dark" role="progressbar" style="width: 75%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- [Payment Records] end -->

            <!-- [Total Sales] start -->
            <div class="col-xxl-4">
                <div class="card stretch stretch-full overflow-hidden">
                    <div class="bg-primary text-white">
                        <div class="p-4">
                            <span class="badge bg-light text-primary text-dark float-end">12%</span>
                            <div class="text-start">
                                <h4 class="text-reset">30,569</h4>
                                <p class="text-reset m-0">Total Sales</p>
                            </div>
                        </div>
                        <div id="total-sales-color-graph"></div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="hstack gap-3">
                                <div class="avatar-image avatar-lg p-2 rounded">
                                    <img class="img-fluid" src="public/assets/images/brand/shopify.png" alt="" />
                                </div>
                                <div>
                                    <a href="javascript:void(0);" class="d-block">Shopify eCommerce Store</a>
                                    <span class="fs-12 text-muted">Development</span>
                                </div>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">$1200</div>
                                <div class="fs-12 text-end">6 Projects</div>
                            </div>
                        </div>
                        <hr class="border-dashed my-3" />
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="hstack gap-3">
                                <div class="avatar-image avatar-lg p-2 rounded">
                                    <img class="img-fluid" src="public/assets/images/brand/app-store.png" alt="" />
                                </div>
                                <div>
                                    <a href="javascript:void(0);" class="d-block">iOS Apps Development</a>
                                    <span class="fs-12 text-muted">Development</span>
                                </div>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">$1450</div>
                                <div class="fs-12 text-end">3 Projects</div>
                            </div>
                        </div>
                        <hr class="border-dashed my-3" />
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="hstack gap-3">
                                <div class="avatar-image avatar-lg p-2 rounded">
                                    <img class="img-fluid" src="public/assets/images/brand/figma.png" alt="" />
                                </div>
                                <div>
                                    <a href="javascript:void(0);" class="d-block">Figma Dashboard Design</a>
                                    <span class="fs-12 text-muted">UI/UX Design</span>
                                </div>
                            </div>
                            <div>
                                <div class="fw-bold text-dark">$1250</div>
                                <div class="fs-12 text-end">5 Projects</div>
                            </div>
                        </div>
                    </div>
                    <a href="javascript:void(0);" class="card-footer fs-11 fw-bold text-uppercase text-center py-4">Full
                        Details</a>
                </div>
            </div>
            <!-- [Total Sales] end !-->

        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>