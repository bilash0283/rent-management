<?php
$message = '';

/* ================= AJAX : LOAD UNIT ================= */
if (isset($_POST['ajax']) && $_POST['ajax'] === 'get_units') {
    $building_id = (int) $_POST['building_id'];

    $q = mysqli_query($db, "
        SELECT id, unit_name 
        FROM unit 
        WHERE building_name = $building_id 
        AND status = 'Available'
        ORDER BY unit_name ASC
    ");

    echo '<option value="">Select Unit</option>';
    while ($row = mysqli_fetch_assoc($q)) {
        echo "<option value='{$row['id']}'>{$row['unit_name']}</option>";
    }
    exit;
}

/* ================= DELETE TENANT ================= */
if (isset($_GET['action']) && $_GET['action'] === 'delete') {
    $id = (int) $_GET['id'];

    $q = mysqli_query($db, "SELECT tenant_image, nid_image, unit_id FROM tenants WHERE id=$id");
    if ($row = mysqli_fetch_assoc($q)) {

        if ($row['tenant_image'] && file_exists("public/uploads/tenants/" . $row['tenant_image'])) {
            unlink("public/uploads/tenants/" . $row['tenant_image']);
        }

        if ($row['nid_image'] && file_exists("public/uploads/nid/" . $row['nid_image'])) {
            unlink("public/uploads/nid/" . $row['nid_image']);
        }

        mysqli_query($db, "UPDATE unit SET status='Available' WHERE id=" . $row['unit_id']);
    }

    mysqli_query($db, "DELETE FROM tenants WHERE id=$id");
    $message = "<div class='alert alert-success'>Tenant deleted successfully</div>";
}

/* ================= ADD TENANT ================= */
if (isset($_POST['add_tenant'])) {

    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $address = $_POST['address'];
    $family = $_POST['family'];
    $building = $_POST['building'];
    $unit = $_POST['unit'];

    // Tenant Image
    $tenant_img = '';
    if (!empty($_FILES['tenant_image']['name'])) {
        $tenant_img = time() . '_' . $_FILES['tenant_image']['name'];
        move_uploaded_file($_FILES['tenant_image']['tmp_name'], "public/uploads/tenants/" . $tenant_img);
    }

    // NID Image
    $nid_img = '';
    if (!empty($_FILES['nid_image']['name'])) {
        $nid_img = time() . '_' . $_FILES['nid_image']['name'];
        move_uploaded_file($_FILES['nid_image']['tmp_name'], "public/uploads/nid/" . $nid_img);
    }

    $sql = "INSERT INTO tenants 
        (name, phone, email, permanent_address, family_member, tenant_image, nid_image, building_id, unit_id)
        VALUES
        ('$name','$phone','$email','$address','$family','$tenant_img','$nid_img','$building','$unit')";

    if (mysqli_query($db, $sql)) {
        mysqli_query($db, "UPDATE unit SET status='Rented' WHERE id=$unit");
        $message = "<div class='alert alert-success'>Tenant added successfully</div>";
    }
}

/* ================= FETCH TENANTS ================= */
$tenants = mysqli_query($db, "
    SELECT t.*, b.name AS building_name, u.unit_name 
    FROM tenants t
    JOIN building b ON t.building_id = b.id
    JOIN unit u ON t.unit_id = u.id
    ORDER BY t.id DESC
");
?>

<div class="container my-4 px-4">
    <h4 class="py-3">Add Tenant</h4>
    <?= $message ?>
    <form method="POST" enctype="multipart/form-data" class="row g-3">
            <div class="col-md-6">
                <input type="text" name="name" class="form-control" placeholder="Tenant Name" required>
            </div>

            <div class="col-md-6">
                <input type="text" name="phone" class="form-control" placeholder="Phone" required>
            </div>

            <div class="col-md-6">
                <input type="email" name="email" class="form-control" placeholder="Email">
            </div>

            <div class="col-md-6">
                <input type="number" name="family" class="form-control" placeholder="Family Member">
            </div>

            <div class="col-12">
                <textarea name="address" class="form-control" placeholder="Permanent Address" required></textarea>
            </div>

            <div class="col-md-6">
                <select name="building" id="building" class="form-control" required>
                    <option value="">Select Building</option>
                    <?php
                    $b = mysqli_query($db, "SELECT id, name FROM building");
                    while ($row = mysqli_fetch_assoc($b)) {
                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
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
                <label>Tenant Image</label>
                <input type="file" name="tenant_image" class="form-control">
            </div>

            <div class="col-md-6">
                <label>NID Image</label>
                <input type="file" name="nid_image" class="form-control">
            </div>

            <div class="col-12">
                <button name="add_tenant" class="btn btn-primary">Save Tenant</button>
            </div>
    </form>
</div>

<script>
    $('#building').on('change', function () {
        let buildingID = $(this).val();
        $('#unit').html('<option>Loading...</option>');

        $.post('', {
            ajax: 'get_units',
            building_id: buildingID
        }, function (data) {
            $('#unit').html(data);
        });
    });
</script>



