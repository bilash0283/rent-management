<?php
    if(isset($_GET['unit_id'])){
        $unit_id = $_GET['unit_id'];
    }

    $query = "SELECT * FROM unit wHERE id = '$unit_id'";
    $result = mysqli_query($db, $query);
    while ($row = mysqli_fetch_assoc($result)) {
        $unit_id = $row['id'];
        $advance = $row['advance'];
        $rent = $row['rent'];
        $Gas = $row['Gas'];
        $Water = $row['Water'];
        $Electricity = $row['Electricity'];
        $Internet = $row['Internet'];
        $Maintenance = $row['Maintenance'];
        $Others = $row['Others'];
        $building_name = $row['building_name'];
    }

    $tent_sql = mysqli_query($db,"SELECT * FROM tenants WHERE building_id = '$building_name' AND unit_id = '$unit_id'");
    while($tent_row = mysqli_fetch_assoc($tent_sql)){
        $tent_name = $tent_row['name'];
        $tent_id = $tent_row['id'];
    }


    // Advace Save SQL 
    

?>




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
                                    <label class="fw-semibold">Advance Amount ৳ = <?= $advance ?></label><br>
                                    <b>Payment History</b><br>
                                    <?php 
                                        $advance_sql = mysqli_query($db,"SELECT * FROM `advance` WHERE tenant_id = '$tent_id' AND unit_id = '$unit_id' ");

                                        while($advance_his = mysqli_fetch_assoc($advance_sql)){
                                        $add_pay_date = $advance_his['paid_amount'];
                                        $add_paid_amount = $advance_his['paid_amount'];
                                    ?>
                                    <label class="fw-semibold"><?= $add_pay_date ?> ৳ = <?= $add_paid_amount ?></label><br>
                                    <?php } ?>
                                </div>
                                <div class="col-lg-6">
                                    <input type="number" name="advace_amount" class="form-control" placeholder="Advance Amount" required>
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="row">
                                <div class="col-lg-6"></div>
                                <div class="col-lg-6">
                                    <button type="submit" name="advance_save" class="btn btn-success">
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
