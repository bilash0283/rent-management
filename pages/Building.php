<div class="nxl-content">
    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Building Manage</h5>
            </div>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex d-md-none">
                    <a href="javascript:void(0)" class="page-header-right-close-toggle">
                        <i class="feather-arrow-left me-2"></i>
                        <span>Back</span>
                    </a>
                </div>
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse"
                        data-bs-target="#collapseOne">
                        <i class="feather-bar-chart"></i>
                    </a>                    
                    <a href="admin.php?page=CreateBuilding" class="btn btn-primary">
                        <i class="feather-plus me-2"></i>
                        <span>Create Building</span>
                    </a>
                </div>
            </div>
            <div class="d-md-none d-flex align-items-center">
                <a href="javascript:void(0)" class="page-header-right-open-toggle">
                    <i class="feather-align-right fs-20"></i>
                </a>
            </div>
        </div>
    </div>
    <div id="collapseOne" class="accordion-collapse collapse page-header-collapse">
        <div class="accordion-body pb-2">
            <div class="row">
                <h1>This is filter section</h1>
            </div>
        </div>
    </div>
    <!-- [ page-header ] end -->

    <!-- [ Main Content ] start -->
    <div class="main-content">
        <div class="row">
            <!-- [Leads] start -->
            <div class="col-xxl-12">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Leads</h5>
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
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr class="border-b">
                                        <th scope="row">Users</th>
                                        <th>Proposal</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar-image">
                                                    <img src="assets/images/avatar/2.png" alt="" class="img-fluid">
                                                </div>
                                                <a href="javascript:void(0);">
                                                    <span class="d-block">Archie Cantones</span>
                                                    <span
                                                        class="fs-12 d-block fw-normal text-muted">arcie.tones@gmail.com</span>
                                                </a>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-gray-200 text-dark">Sent</span>
                                        </td>
                                        <td>11/06/2023 10:53</td>
                                        <td>
                                            <span class="badge bg-soft-success text-success">Completed</span>
                                        </td>
                                        <td class="text-end">
                                            <a href="javascript:void(0);"><i class="feather-more-vertical"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar-image">
                                                    <img src="assets/images/avatar/3.png" alt="" class="img-fluid">
                                                </div>
                                                <a href="javascript:void(0);">
                                                    <span class="d-block">Holmes Cherryman</span>
                                                    <span
                                                        class="fs-12 d-block fw-normal text-muted">golms.chan@gmail.com</span>
                                                </a>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-gray-200 text-dark">New</span>
                                        </td>
                                        <td>11/06/2023 10:53</td>
                                        <td>
                                            <span class="badge bg-soft-primary text-primary">In Progress </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="javascript:void(0);"><i class="feather-more-vertical"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar-image">
                                                    <img src="assets/images/avatar/4.png" alt="" class="img-fluid">
                                                </div>
                                                <a href="javascript:void(0);">
                                                    <span class="d-block">Malanie Hanvey</span>
                                                    <span
                                                        class="fs-12 d-block fw-normal text-muted">lanie.nveyn@gmail.com</span>
                                                </a>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-gray-200 text-dark">Sent</span>
                                        </td>
                                        <td>11/06/2023 10:53</td>
                                        <td>
                                            <span class="badge bg-soft-success text-success">Completed</span>
                                        </td>
                                        <td class="text-end">
                                            <a href="javascript:void(0);"><i class="feather-more-vertical"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar-image">
                                                    <img src="assets/images/avatar/5.png" alt="" class="img-fluid">
                                                </div>
                                                <a href="javascript:void(0);">
                                                    <span class="d-block">Kenneth Hune</span>
                                                    <span
                                                        class="fs-12 d-block fw-normal text-muted">nneth.une@gmail.com</span>
                                                </a>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-gray-200 text-dark">Returning</span>
                                        </td>
                                        <td>11/06/2023 10:53</td>
                                        <td>
                                            <span class="badge bg-soft-warning text-warning">Not Interested</span>
                                        </td>
                                        <td class="text-end">
                                            <a href="javascript:void(0);"><i class="feather-more-vertical"></i></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="avatar-image">
                                                    <img src="assets/images/avatar/6.png" alt="" class="img-fluid">
                                                </div>
                                                <a href="javascript:void(0);">
                                                    <span class="d-block">Valentine Maton</span>
                                                    <span
                                                        class="fs-12 d-block fw-normal text-muted">alenine.aton@gmail.com</span>
                                                </a>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-gray-200 text-dark">Sent</span>
                                        </td>
                                        <td>11/06/2023 10:53</td>
                                        <td>
                                            <span class="badge bg-soft-success text-success">Completed</span>
                                        </td>
                                        <td class="text-end">
                                            <a href="javascript:void(0);"><i class="feather-more-vertical"></i></a>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer">
                        <ul class="list-unstyled d-flex align-items-center gap-2 mb-0 pagination-common-style">
                            <li>
                                <a href="javascript:void(0);"><i class="bi bi-arrow-left"></i></a>
                            </li>
                            <li><a href="javascript:void(0);" class="active">1</a></li>
                            <li><a href="javascript:void(0);">2</a></li>
                            <li>
                                <a href="javascript:void(0);"><i class="bi bi-dot"></i></a>
                            </li>
                            <li><a href="javascript:void(0);">8</a></li>
                            <li><a href="javascript:void(0);">9</a></li>
                            <li>
                                <a href="javascript:void(0);"><i class="bi bi-arrow-right"></i></a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
            <!-- [Leads] end -->
        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>