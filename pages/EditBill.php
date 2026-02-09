


<div class="nxl-content">

    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <div class="page-header-left">
            <h5 class="m-b-10">
                Bills Manage
            </h5>
        </div>
        <div class="page-header-right">
            <a href="admin.php?page=bill" class="btn btn-primary">Back</a>
        </div>
    </div>

    <?= $message ?? '' ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="card-body general-info">

                            <!-- Unit Name -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-6">
                                    <label class="fw-semibold">Advance Amount</label>
                                </div>
                                <div class="col-lg-6">
                                    <input type="text" name="unit_name" class="form-control" required>
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="row">
                                <div class="col-lg-6"></div>
                                <div class="col-lg-6">
                                    <button type="submit" name="btn" class="btn btn-success">
                                        Save
                                    </button>
                                </div>
                            </div>

                        </div>
                    </form>

                    <hr style="width: 75%;" class="mx-auto">

                    <form method="POST" enctype="multipart/form-data">
                        <div class="card-body general-info">

                            <!-- Unit Name -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label class="fw-semibold">Advance Amount</label>
                                </div>
                                <div class="col-lg-8">
                                    <input type="text" name="unit_name" class="form-control" required>
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="row">
                                <div class="col-lg-4"></div>
                                <div class="col-lg-8">
                                    <button type="submit" name="btn" class="btn btn-success">
                                        Save
                                    </button>
                                </div>
                            </div>

                        </div>
                    </form>
                    
                </div>
            </div>
        </div>
    </div>
</div>
