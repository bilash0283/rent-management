<?php 
    if(isset($_GET['edit_id'])){
        $id = (int)$_GET['edit_id'] ?? '';
        $q = mysqli_query($db, "SELECT * FROM tenants WHERE id=$id");
        $editData = mysqli_fetch_assoc($q);
        $old_unit_id = $editData['unit_id'] ?? '';




    }
?>

<div class="container mb-3 px-4">
    <h4 class="py-3"><?= $editData ? 'Update Tenant Status' : 'Add Tenant' ?></h4>
    <?= $message ?? '' ?>

    <form method="POST" enctype="multipart/form-data" class="row g-3">

        <div class="col-md-6">
            <label for="start_tanent" class="p-2">Status <span class="text-danger">*</span></label>
            <select name="status" id="status" class="form-control custom-select" required>
                <option value="Active" <?php if(isset($editData['status']) && $editData['status'] == 'Active'){echo 'selected';} ?> >Active</option>
                <option value="Inactive" <?php if(isset($editData['status']) && $editData['status'] == 'Inactive'){echo 'selected';} ?> >Inactive</option>
                <option value="Booked" <?php if(isset($editData['status']) && $editData['status'] == 'Booked'){echo 'selected';} ?> >Booked</option>
            </select>
        </div>

        <div class="col-md-6">
            <label for="nid_no" class="p-2">Booking Month</label>
            <input type="month" name="booking_month" value="<?php echo $editData['booking_month'] ?>" class="form-control" placeholder="Booking Month" >
        </div>

        <div class="col-12">
            <button name="save_tenant" class="btn btn-primary">
                <?= $editData ? 'Update Tenant' : 'Save Tenant' ?>
            </button>
        </div>
    </form>
</div>