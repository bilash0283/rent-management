
    <div class="nxl-content">
        <!-- [ Main Content ] start -->
        <div class="main-content">
            <div class="row">
                
                <!-- card view  -->
                <div class="col-lg-4">
                    <div class="card mb-4 stretch stretch-full">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex gap-3 align-items-center">
                                <div class="avatar-text">
                                    <i class="feather feather-star"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">Tasks Completed</div>
                                    <div class="fs-12 text-muted">22/35 completed</div>
                                </div>
                            </div>
                            <div class="fs-4 fw-bold text-dark">22/35</div>
                        </div>
                        <div class="card-body d-flex align-items-center justify-content-between gap-4">
                            <div id="task-completed-area-chart"></div>
                            <div class="fs-12 text-muted text-nowrap">
                                <span class="fw-semibold text-primary">28% more</span><br />
                                <span>from last week</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card mb-4 stretch stretch-full">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex gap-3 align-items-center">
                                <div class="avatar-text">
                                    <i class="feather feather-file-text"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">New Tasks</div>
                                    <div class="fs-12 text-muted">0/20 tasks</div>
                                </div>
                            </div>
                            <div class="fs-4 fw-bold text-dark">5/20</div>
                        </div>
                        <div class="card-body d-flex align-items-center justify-content-between gap-4">
                            <div id="new-tasks-area-chart"></div>
                            <div class="fs-12 text-muted text-nowrap">
                                <span class="fw-semibold text-success">34% more</span><br />
                                <span>from last week</span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="card mb-4 stretch stretch-full">
                        <div class="card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex gap-3 align-items-center">
                                <div class="avatar-text">
                                    <i class="feather feather-airplay"></i>
                                </div>
                                <div>
                                    <div class="fw-semibold text-dark">Project Done</div>
                                    <div class="fs-12 text-muted">20/30 project</div>
                                </div>
                            </div>
                            <div class="fs-4 fw-bold text-dark">20/30</div>
                        </div>
                        <div class="card-body d-flex align-items-center justify-content-between gap-4">
                            <div id="project-done-area-chart"></div>
                            <div class="fs-12 text-muted text-nowrap">
                                <span class="fw-semibold text-danger">42% more</span><br />
                                <span>from last week</span>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- card view  -->
                 
                <!-- [Payment Records] end -->
                <div class="col-xxl-8">
                    <div class="card stretch stretch-full">
                        <div class="card-header">
                            <h5 class="card-title">Payment Record</h5>
                            <div class="card-header-action">
                                <div class="card-header-btn">
                                    <div data-bs-toggle="tooltip" title="Delete">
                                        <a href="javascript:void(0);" class="avatar-text avatar-xs bg-danger" data-bs-toggle="remove"> </a>
                                    </div>
                                    <div data-bs-toggle="tooltip" title="Refresh">
                                        <a href="javascript:void(0);" class="avatar-text avatar-xs bg-warning" data-bs-toggle="refresh"> </a>
                                    </div>
                                    <div data-bs-toggle="tooltip" title="Maximize/Minimize">
                                        <a href="javascript:void(0);" class="avatar-text avatar-xs bg-success" data-bs-toggle="expand"> </a>
                                    </div>
                                </div>
                                <div class="dropdown">
                                    <a href="javascript:void(0);" class="avatar-text avatar-sm" data-bs-toggle="dropdown" data-bs-offset="25, 25">
                                        <div data-bs-toggle="tooltip" title="Options">
                                            <i class="feather-more-vertical"></i>
                                        </div>
                                    </a>
                                    <div class="dropdown-menu dropdown-menu-end">
                                        <a href="javascript:void(0);" class="dropdown-item"><i class="feather-at-sign"></i>New</a>
                                        <a href="javascript:void(0);" class="dropdown-item"><i class="feather-calendar"></i>Event</a>
                                        <a href="javascript:void(0);" class="dropdown-item"><i class="feather-bell"></i>Snoozed</a>
                                        <a href="javascript:void(0);" class="dropdown-item"><i class="feather-trash-2"></i>Deleted</a>
                                        <div class="dropdown-divider"></div>
                                        <a href="javascript:void(0);" class="dropdown-item"><i class="feather-settings"></i>Settings</a>
                                        <a href="javascript:void(0);" class="dropdown-item"><i class="feather-life-buoy"></i>Tips & Tricks</a>
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
                                            <div class="progress-bar bg-primary" role="progressbar" style="width: 81%"></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3">
                                    <div class="p-3 border border-dashed rounded">
                                        <div class="fs-12 text-muted mb-1">Completed</div>
                                        <h6 class="fw-bold text-dark">$9,275</h6>
                                        <div class="progress mt-2 ht-3">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: 82%"></div>
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
                        <a href="javascript:void(0);" class="card-footer fs-11 fw-bold text-uppercase text-center py-4">Full Details</a>
                    </div>
                </div>
                <!-- [Total Sales] end !-->

            </div>
        </div>
        <!-- [ Main Content ] end -->
    </div>