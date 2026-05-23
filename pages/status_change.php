<?php 
    $message = '';

    // ==================== EDIT MODE ====================
    if (isset($_GET['edit_id'])) {
        $id = (int)$_GET['edit_id'];

        $q = mysqli_query($db, "SELECT * FROM tenants WHERE id = $id");
        $editData = mysqli_fetch_assoc($q);

        if ($editData) {
            $old_unit_id = $editData['unit_id'] ?? '';
            $old_status  = $editData['status'] ?? '';
        }
    }

    // ==================== SAVE / UPDATE ====================
    if (isset($_POST['save_tenant'])) {

        $id             = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : 0;
        $status         = mysqli_real_escape_string($db, $_POST['status']);
        $booking_month  = mysqli_real_escape_string($db, $_POST['booking_month']);

        if ($id == 0) {
            $message = "<div class='alert alert-danger'>Invalid Tenant ID!</div>";
        } else {

            // Get current tenant data
            $current = mysqli_fetch_assoc(mysqli_query($db, "SELECT unit_id, status FROM tenants WHERE id = $id"));
            $unit_id = $current['unit_id'] ?? '';

            if (empty($unit_id)) {
                $message = "<div class='alert alert-danger'>Unit not found!</div>";
            } else {

                // =========================
                // CHECK ACTIVE TENANT (Only when trying to set Active)
                // =========================
                if ($status == 'Active') {

                    $checkActive = mysqli_query($db, "
                        SELECT id, name 
                        FROM tenants 
                        WHERE unit_id = '$unit_id' 
                        AND status = 'Active' 
                        AND id != '$id'
                        LIMIT 1
                    ");

                    if (mysqli_num_rows($checkActive) > 0) {
                        $activeTenant = mysqli_fetch_assoc($checkActive);
                        $activeName   = $activeTenant['name'];

                        $message = "
                            <div class='alert alert-danger'>
                                This unit already has an active tenant: <strong>$activeName</strong>.<br>
                                Please set the existing tenant to <strong>Inactive</strong> first.
                            </div>
                        ";
                    } else {
                        // === UPDATE TENANT TO ACTIVE ===
                        mysqli_query($db, "
                            UPDATE tenants 
                            SET status = 'Active', 
                                booking_month = '$booking_month' 
                            WHERE id = '$id'
                        ");

                        // === UPDATE UNIT STATUS ===
                        mysqli_query($db, "
                            UPDATE unit 
                            SET status = 'Active' 
                            WHERE id = '$unit_id'
                        ");

                        $message = "<div class='alert alert-success'>Tenant status updated successfully (Active).</div>";
                    }

                } 
                // =========================
                // INACTIVE or BOOKED
                // =========================
                else {

                    // UPDATE TENANT
                    mysqli_query($db, "
                        UPDATE tenants 
                        SET status = '$status', 
                            booking_month = '$booking_month' 
                        WHERE id = '$id'
                    ");

                    // UPDATE UNIT TO AVAILABLE
                    mysqli_query($db, "
                        UPDATE unit 
                        SET status = 'Available' 
                        WHERE id = '$unit_id'
                    ");

                    $message = "<div class='alert alert-success'>Tenant status updated successfully.</div>";
                }
            }
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
            <label class="p-2">Status <span class="text-danger">*</span></label>
            <select name="status" id="status" class="form-control custom-select" required>
                <option value="Active" <?= (isset($editData['status']) && $editData['status'] == 'Active') ? 'selected' : '' ?>>
                    Active
                </option>
                <option value="Inactive" <?= (isset($editData['status']) && $editData['status'] == 'Inactive') ? 'selected' : '' ?>>
                    Inactive
                </option>
                <!-- <option value="Booked" ... >  // যদি পরে চাও তাহলে আনকমেন্ট করো -->
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