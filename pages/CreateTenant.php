<?php
$message = '';
$editData = null;

/* ================= AJAX : LOAD UNIT ================= */
if (isset($_POST['ajax']) && $_POST['ajax'] === 'get_units') {

    $building_id  = (int)$_POST['building_id'];
    $selected_unit = isset($_POST['selected_unit']) ? (int)$_POST['selected_unit'] : 0;

    if ($selected_unit > 0) {
        // EDIT MODE → Available + current rented unit
        $sql = "
            SELECT id, unit_name, rent, advance
            FROM unit
            WHERE building_name = $building_id
            AND (status = 'Available' OR id = $selected_unit)
            ORDER BY unit_name ASC
        ";
    } else {
        // ADD MODE → Only available units
        $sql = "
            SELECT id, unit_name, rent, advance
            FROM unit
            WHERE building_name = $building_id
            AND status = 'Available'
            ORDER BY unit_name ASC
        ";
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
    $q = mysqli_query($db, "SELECT * FROM tenants WHERE id=$id");
    $editData = mysqli_fetch_assoc($q);
    $old_unit_id = $editData['unit_id'];
}

/* ================= ADD / UPDATE TENANT ================= */
if (isset($_POST['save_tenant'])) {

    $id       = $_POST['id'];
    $name     = $_POST['name'];
    $phone    = $_POST['phone'];
    $email    = $_POST['email'];
    $address  = $_POST['address'];
    $family   = $_POST['family'];
    $building = $_POST['building'];
    $unit     = $_POST['unit'];

    $tenant_img = $_POST['old_tenant_image'];
    if (!empty($_FILES['tenant_image']['name'])) {
        $tenant_img = time().'_'.$_FILES['tenant_image']['name'];
        move_uploaded_file($_FILES['tenant_image']['tmp_name'], "public/uploads/tenants/".$tenant_img);
    }

    $nid_img = $_POST['old_nid_image'];
    if (!empty($_FILES['nid_image']['name'])) {
        $nid_img = time().'_'.$_FILES['nid_image']['name'];
        move_uploaded_file($_FILES['nid_image']['tmp_name'], "public/uploads/nid/".$nid_img);
    }

    if ($id) {

        if($unit != $old_unit_id && !empty($unit)){
            mysqli_query($db, "UPDATE unit SET status='Available' WHERE id=$old_unit_id");
            mysqli_query($db, "UPDATE unit SET status='Rented' WHERE id=$unit");
        }
        // UPDATE TENANT
        mysqli_query($db, "
            UPDATE tenants SET
                name='$name',
                phone='$phone',
                email='$email',
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
        // ADD TENANT
        mysqli_query($db, "
            INSERT INTO tenants
            (name, phone, email, permanent_address, family_member, tenant_image, nid_image, building_id, unit_id)
            VALUES
            ('$name','$phone','$email','$address','$family','$tenant_img','$nid_img','$building','$unit')
        ");

        mysqli_query($db, "UPDATE unit SET status='Rented' WHERE id=$unit");

        $message = "<div class='alert alert-success'>Tenant added successfully</div>";
    }
}
?>

<div class="container my-4 px-4">
    <h4 class="py-3"><?= $editData ? 'Update Tenant' : 'Add Tenant' ?></h4>
    <?= $message ?>

    <form method="POST" enctype="multipart/form-data" class="row g-3">
        <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">
        <input type="hidden" name="old_tenant_image" value="<?= $editData['tenant_image'] ?? '' ?>">
        <input type="hidden" name="old_nid_image" value="<?= $editData['nid_image'] ?? '' ?>">

        <div class="col-md-6">
            <input type="text" name="name" class="form-control" value="<?= $editData['name'] ?? '' ?>" placeholder="Tenant Name" required>
        </div>

        <div class="col-md-6">
            <input type="text" name="phone" class="form-control" value="<?= $editData['phone'] ?? '' ?>" placeholder="Phone" required>
        </div>

        <div class="col-md-6">
            <input type="email" name="email" class="form-control" value="<?= $editData['email'] ?? '' ?>" placeholder="Email">
        </div>

        <div class="col-md-6">
            <input type="number" name="family" class="form-control" value="<?= $editData['family_member'] ?? '' ?>" placeholder="Family Member">
        </div>

        <div class="col-12">
            <textarea name="address" class="form-control" placeholder="Permanent Address"><?= $editData['permanent_address'] ?? '' ?></textarea>
        </div>

        <div class="col-md-6">
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
            <select name="unit" id="unit" class="form-control" required>
                <option value="">Select Unit</option>
            </select>
        </div>

        <div class="col-md-6">
            <label for="tenant_image" >Tenant Image</label>
            <?php if(!empty($editData['tenant_image'])){ ?>
            <img src="<?php echo 'public/uploads/tenants/'.$editData['tenant_image'] ?? 'No Image Found!' ?>" alt="" style="width:40px; border-radius:50%; height: 40px;">
            <?php } else {echo "<span class='text-danger'>Please Upload Image File!</span>";}?>
            <input type="file" name="tenant_image" class="form-control">
        </div>

        <div class="col-md-6">
            <label for="nid_image" >Nid Image</label>
            <?php if(!empty($editData['nid_image'])){ ?>
            <img src="<?php echo 'public/uploads/nid/'.$editData['nid_image'] ?? 'No Image Found!' ?>" alt="" style="width:40px; border-radius:50%; height: 40px;">
            <?php } else {echo "<span class='text-danger'>Please Upload Image File!</span>";}?>
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
function loadUnits(buildingID, selectedUnit = 0) {
    $('#unit').html('<option>Loading...</option>');
    $.post('', {
        ajax: 'get_units',
        building_id: buildingID,
        selected_unit: selectedUnit
    }, function (data) {
        $('#unit').html(data);
    });
}

$('#building').on('change', function () {
    loadUnits($(this).val());
});

/* ===== AUTO LOAD UNIT ON EDIT ===== */
<?php if ($editData): ?>
$(document).ready(function () {
    loadUnits(
        <?= (int)$editData['building_id'] ?>,
        <?= (int)$editData['unit_id'] ?>
    );
});
<?php endif; ?>
</script>
