<?php
$message = '';
$editData = null;

/* ================= AJAX : LOAD UNIT ================= */
if (isset($_POST['ajax']) && $_POST['ajax'] === 'get_units') {
    $building_id   = (int)$_POST['building_id'];
    $selected_unit = isset($_POST['selected_unit']) ? (int)$_POST['selected_unit'] : 0;
    $status        = isset($_POST['status']) ? $_POST['status'] : 'Active'; // Default Active for add

    if ($selected_unit > 0) {
        // EDIT MODE → Available + Current Unit
        $sql = "
            SELECT id, unit_name, rent, advance
            FROM unit
            WHERE building_name = $building_id
            AND (status = 'Available' OR id = $selected_unit)
            ORDER BY unit_name ASC
        ";
    } else {
        // ADD MODE
        if ($status === 'Booked') {
            // Booked হলে সব ইউনিট
            $sql = "
                SELECT id, unit_name, rent, advance
                FROM unit
                WHERE building_name = $building_id
                ORDER BY unit_name ASC
            ";
        } else {
            // Active হলে শুধু Available
            $sql = "
                SELECT id, unit_name, rent, advance
                FROM unit
                WHERE building_name = $building_id
                AND status = 'Available'
                ORDER BY unit_name ASC
            ";
        }
    }

    $q = mysqli_query($db, $sql);
    echo '<option value="">Select Unit</option>';
    while ($row = mysqli_fetch_assoc($q)) {
        $selected = ($row['id'] == $selected_unit) ? 'selected' : '';
        echo "<option value='{$row['id']}' $selected>
                {$row['unit_name']} (R-৳{$row['rent']}) (A-৳{$row['advance']})
              </option>";
    }
    exit;
}

/* ================= EDIT FETCH ================= */
if (isset($_GET['edit_id'])) {
    $id = (int)$_GET['edit_id'];
    $q = mysqli_query($db, "SELECT * FROM tenants WHERE role IN ('Tenant') AND id=$id");
    $editData = mysqli_fetch_assoc($q);
    $old_unit_id = $editData['unit_id'] ?? '';
}

/* ================= ADD / UPDATE TENANT ================= */
if (isset($_POST['save_tenant'])) {
    $id              = $_POST['id'];
    $name            = mysqli_real_escape_string($db, $_POST['name']);
    $phone           = mysqli_real_escape_string($db, $_POST['phone']);
    $email           = mysqli_real_escape_string($db, $_POST['email']);
    $address         = mysqli_real_escape_string($db, $_POST['address']);
    $family          = (int)$_POST['family'];
    $building        = (int)$_POST['building'];
    $unit            = (int)$_POST['unit'];
    $start_tanent    = $_POST['start_tanent'];
    $nid_no          = mysqli_real_escape_string($db, $_POST['nid_no']);
    $status          = $_POST['status'];
    $booking_month   = $_POST['booking_month'];

    $tenant_img = $_POST['old_tenant_image'] ?? '';
    if (!empty($_FILES['tenant_image']['name'])) {
        $tenant_img = time().'_'.$_FILES['tenant_image']['name'];
        move_uploaded_file($_FILES['tenant_image']['tmp_name'], "public/uploads/tenants/".$tenant_img);
    }

    $nid_img = $_POST['old_nid_image'] ?? '';
    if (!empty($_FILES['nid_image']['name'])) {
        $nid_img = time().'_'.$_FILES['nid_image']['name'];
        move_uploaded_file($_FILES['nid_image']['tmp_name'], "public/uploads/nid/".$nid_img);
    }

    if ($id) {
        // UPDATE
        if ($unit != $old_unit_id && !empty($unit)) {
            mysqli_query($db, "UPDATE unit SET status='Available' WHERE id=$old_unit_id");
            mysqli_query($db, "UPDATE unit SET status='Rented' WHERE id=$unit");
        }

        mysqli_query($db, "
            UPDATE tenants SET
                name='$name',
                phone='$phone',
                email='$email',
                status='$status',
                booking_month='$booking_month',
                start_tanent='$start_tanent',
                nid_no='$nid_no',
                permanent_address='$address',
                family_member='$family',
                tenant_image='$tenant_img',
                nid_image='$nid_img',
                building_id='$building',
                unit_id='$unit'
            WHERE id=$id
        ");
        $message = "<div class='alert alert-success'>Tenant updated successfully</div>";
    } else {
        // ADD
        mysqli_query($db, "
            INSERT INTO tenants 
            (name, phone, email, status, role, booking_month, permanent_address, family_member, tenant_image, nid_image, building_id, unit_id, start_tanent, nid_no)
            VALUES 
            ('$name','$phone','$email', '$status', 'Tenant', '$booking_month', '$address','$family','$tenant_img','$nid_img','$building','$unit','$start_tanent','$nid_no')
        ");
        if ($unit > 0) {
            mysqli_query($db, "UPDATE unit SET status='Rented' WHERE id=$unit");
        }
        $message = "<div class='alert alert-success'>Tenant added successfully</div>";
    }
}
?>

<div class="container mb-3 px-4">
    <h4 class="py-3"><?= $editData ? 'Update Tenant' : 'Add Tenant' ?></h4>
    <?= $message ?>

    <form method="POST" enctype="multipart/form-data" class="row g-3">
        <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
        <input type="hidden" name="old_tenant_image" value="<?= $editData['tenant_image'] ?? '' ?>">
        <input type="hidden" name="old_nid_image" value="<?= $editData['nid_image'] ?? '' ?>">

        <!-- Common Fields -->
        <div class="col-md-6">
            <label class="p-2">Name <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($editData['name'] ?? '') ?>" required>
        </div>
        <div class="col-md-6">
            <label class="p-2">Phone Number <span class="text-danger">*</span></label>
            <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($editData['phone'] ?? '') ?>" required>
        </div>

        <div class="col-md-6">
            <label class="p-2">Email</label>
            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($editData['email'] ?? '') ?>">
        </div>
        <div class="col-md-6">
            <label class="p-2">Family Member</label>
            <input type="number" name="family" class="form-control" value="<?= $editData['family_member'] ?? '' ?>">
        </div>

        <div class="col-12">
            <label class="p-2">Permanent Address</label>
            <textarea name="address" class="form-control"><?= htmlspecialchars($editData['permanent_address'] ?? '') ?></textarea>
        </div>

        <div class="col-md-6">
            <label class="p-2">Tenant Start Date <span class="text-danger">*</span></label>
            <input type="date" name="start_tanent" class="form-control" value="<?= $editData['start_tanent'] ?? '' ?>" required>
        </div>
        <div class="col-md-6">
            <label class="p-2">NID Number</label>
            <input type="text" name="nid_no" class="form-control" value="<?= htmlspecialchars($editData['nid_no'] ?? '') ?>">
        </div>

        <!-- Status & Booking Month - Hide in Edit Mode -->
        <?php if (!$editData): ?>
        <div class="col-md-6">
            <label class="p-2">Status <span class="text-danger">*</span></label>
            <select name="status" id="status" class="form-control" required>
                <option value="Active" <?= (isset($editData['status']) && $editData['status']=='Active') ? 'selected' : '' ?>>Active</option>
                <option value="Booked" <?= (isset($editData['status']) && $editData['status']=='Booked') ? 'selected' : '' ?>>Booked</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="p-2">Booking Month</label>
            <input type="month" name="booking_month" value="<?= $editData['booking_month'] ?? '' ?>" class="form-control">
        </div>
        <?php else: ?>
            <!-- Hidden fields for Edit Mode -->
            <input type="hidden" name="status" value="<?= htmlspecialchars($editData['status'] ?? 'Active') ?>">
            <input type="hidden" name="booking_month" value="<?= $editData['booking_month'] ?? '' ?>">
        <?php endif; ?>

        <?php if($_SESSION['role']=='Admin'){ ?>
        <div class="col-md-6">
            <label class="p-2">Building <span class="text-danger">*</span></label>
            <select name="building" id="building" class="form-control" required>
                <option value="">Select Building</option>
                <?php
                $b = mysqli_query($db, "SELECT id, name FROM building");
                while ($row = mysqli_fetch_assoc($b)) {
                    $selected = ($editData && $editData['building_id'] == $row['id']) ? 'selected' : '';
                    echo "<option value='{$row['id']}' $selected>{$row['name']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-md-6">
            <label class="p-2">Unit <span class="text-danger">*</span></label>
            <select name="unit" id="unit" class="form-control" required>
                <option value="">Select Unit</option>
            </select>
        </div>
        <?php }else if ($_SESSION['role'] == 'Tenant') { ?>
            <input type="hidden" name="building" value="<?= $editData['building_id'] ?>">
            <input type="hidden" name="unit" value="<?= $editData['unit_id'] ?>">
        <?php } ?>

        <!-- Images -->
        <div class="col-md-6">
            <label class="p-2">Tenant Image</label>
            <?php if(!empty($editData['tenant_image'])): ?>
                <img src="public/uploads/tenants/<?= $editData['tenant_image'] ?>" style="width:50px; height:50px; border-radius:50%; object-fit:cover;">
            <?php endif; ?>
            <input type="file" name="tenant_image" class="form-control">
        </div>

        <div class="col-md-6">
            <label class="p-2">NID Image</label>
            <?php if(!empty($editData['nid_image'])): ?>
                <img src="public/uploads/nid/<?= $editData['nid_image'] ?>" style="width:50px; height:50px; border-radius:50%; object-fit:cover;">
            <?php endif; ?>
            <input type="file" name="nid_image" class="form-control">
        </div>

        <div class="col-12">
            <button name="save_tenant" class="btn btn-primary">
                <?= $editData ? 'Update Tenant' : 'Save Tenant' ?>
            </button>
        </div>
    </form>
</div>

<script>
    function loadUnits(buildingID, selectedUnit = 0, status = 'Active') {
        if (!buildingID) {
            $('#unit').html('<option value="">Select Unit</option>');
            return;
        }
        $('#unit').html('<option>Loading...</option>');

        $.post('', {
            ajax: 'get_units',
            building_id: buildingID,
            selected_unit: selectedUnit,
            status: status
        }, function(data) {
            $('#unit').html(data);
        });
    }

    // Building Change
    $('#building').on('change', function() {
        const status = $('#status').length ? $('#status').val() : 'Active';
        loadUnits($(this).val(), 0, status);
    });

    // Status Change (Only for Add Mode)
    $('#status').on('change', function() {
        const building = $('#building').val();
        if (building) {
            loadUnits(building, 0, $(this).val());
        }
    });

    // Edit Mode - Auto Load Units
    <?php if ($editData): ?>
    $(document).ready(function() {
        loadUnits(
            <?= (int)$editData['building_id'] ?>,
            <?= (int)$editData['unit_id'] ?>
        );
    });
    <?php endif; ?>
</script>