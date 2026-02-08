

<div class="nxl-content">

    <!-- Page Header -->
    <div class="page-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Bill Month (<?php echo date('M - Y') ?>)</h5>

        <a href="admin.php?page=CreateTenant" class="btn btn-primary">
            <i class="feather-plus me-1"></i> Create Bill
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content mt-4">
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
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>                      
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
