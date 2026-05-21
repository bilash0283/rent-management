<?php 
    $message = '';

    // EDIT MODE
    if(isset($_GET['edit_id'])){
        $id = (int)$_GET['edit_id'];

        $q = mysqli_query($db, "SELECT * FROM tenants WHERE id = $id");
        $editData = mysqli_fetch_assoc($q);

        $old_unit_id = $editData['unit_id'] ?? '';
    }

    // SAVE / UPDATE
    if (isset($_POST['save_tenant'])) {

        $status         = mysqli_real_escape_string($db, $_POST['status']);
        $booking_month  = mysqli_real_escape_string($db, $_POST['booking_month']);

        // =========================
        // CHECK ACTIVE TENANT
        // =========================

        if($status == 'Active'){

            // Check if another active tenant already exists in this unit
            $checkActive = mysqli_query($db, "
                SELECT * 
                FROM tenants 
                WHERE unit_id = '$old_unit_id'
                AND status = 'Active'
                AND id != '$id'
                LIMIT 1
            ");

            // If active tenant found
            if(mysqli_num_rows($checkActive) > 0){

                $activeTenant = mysqli_fetch_assoc($checkActive);
                $activeTenantName = $activeTenant['name'];

                $message = "
                    <div class='alert alert-danger'>
                        This unit already has an active tenant 
                        (<strong>$activeTenantName</strong>). 
                        Please set the current active tenant to 
                        <strong>Inactive</strong> before assigning 
                        another tenant as <strong>Active</strong>.
                    </div>
                ";

            }else{

                // UPDATE TENANT
                mysqli_query($db, "
                    UPDATE tenants SET
                        status = '$status',
                        booking_month = '$booking_month'
                    WHERE id = '$id'
                ");

                // UPDATE UNIT STATUS
                mysqli_query($db, "
                    UPDATE unit 
                    SET status = 'Occupied' 
                    WHERE id = '$old_unit_id'
                ");

                $message = "
                    <div class='alert alert-success'>
                        Tenant status has been updated successfully.
                    </div>
                ";
            }

        }else if($status == 'Inactive' || $status == 'Booked'){

            // UPDATE TENANT
            mysqli_query($db, "
                UPDATE tenants SET
                    status = '$status',
                    booking_month = '$booking_month'
                WHERE id = '$id'
            ");

            // UPDATE UNIT STATUS
            mysqli_query($db,"
                UPDATE unit 
                SET status = 'Available' 
                WHERE id = '$old_unit_id'
            ");

            $message = "
                <div class='alert alert-success'>
                    Tenant status has been updated successfully.
                </div>
            ";
        }
    }
?>

<div class="container mb-3 px-4">

    <h4 class="py-3">
        <?= isset($editData) ? 'Update Tenant Status' : 'Add Tenant' ?>
    </h4>

    <?= $message ?? '' ?>

    <form method="POST" enctype="multipart/form-data" class="row g-3">

        <!-- STATUS -->
        <div class="col-md-6">
            <label class="p-2">
                Status <span class="text-danger">*</span>
            </label>

            <select name="status" id="status" class="form-control custom-select" required>

                <option value="Active"
                    <?php 
                        if(isset($editData['status']) && $editData['status'] == 'Active'){
                            echo 'selected';
                        } 
                    ?>>
                    Active
                </option>

                <option value="Inactive"
                    <?php 
                        if(isset($editData['status']) && $editData['status'] == 'Inactive'){
                            echo 'selected';
                        } 
                    ?>>
                    Inactive
                </option>

                <!-- <option value="Booked"
                    <?php 
                        if(isset($editData['status']) && $editData['status'] == 'Booked'){
                            echo 'selected';
                        } 
                    ?>>
                    Booked
                </option> -->

            </select>
        </div>

        <!-- BOOKING MONTH -->
        <div class="col-md-6">
            <label class="p-2">Booking Month</label>

            <input 
                type="month" 
                name="booking_month"
                value="<?= $editData['booking_month'] ?? '' ?>"
                class="form-control"
                placeholder="Booking Month"
            >
        </div>

        <!-- BUTTON -->
        <div class="col-12">
            <button name="save_tenant" class="btn btn-primary">
                <?= isset($editData) ? 'Update Tenant' : 'Save Tenant' ?>
            </button>
        </div>

    </form>
</div>