<?php
include "database/db.php";

$message = "";

// ==========================
// GET IDs SAFELY
// ==========================
$building_id = isset($_GET['buliding_id']) ? (int)$_GET['buliding_id'] : 0;
$edit_id     = isset($_GET['edit_id']) ? (int)$_GET['edit_id'] : null;

// ==========================
// DEFAULT UNIT DATA
// ==========================
$unit = [
    'unit_name' => '',
    'floor' => '',
    'unit_type' => 'Flat',
    'size' => '',
    'rent' => 0,
    'advance' => 0,
    'unit_image' => '',
    'Gas' => 0,
    'Water' => 0,
    'Electricity' => 0,
    'Internet' => 0,
    'Maintenance' => 0,
    'Others' => 0
];

// ==========================
// EDIT MODE FETCH
// ==========================
if ($edit_id) {
    $get = mysqli_query($db, "SELECT * FROM unit WHERE id=$edit_id");
    if ($get && mysqli_num_rows($get)) {
        $unit = mysqli_fetch_assoc($get);
    }
}

// ==========================
// FORM SUBMIT
// ==========================
if (isset($_POST['btn'])) {

    // TEXT FIELDS
    $unit_name = mysqli_real_escape_string($db, $_POST['unit_name']);
    $floor     = mysqli_real_escape_string($db, $_POST['floor']);
    $unit_type = mysqli_real_escape_string($db, $_POST['unit_type']);
    $size      = mysqli_real_escape_string($db, $_POST['size']);

    // NUMERIC FIELDS (EMPTY → 0)
    $rent        = (float) ($_POST['rent'] ?? 0);
    $advance     = (float) ($_POST['advance'] ?? 0);

    $status = 'Available';

    // ======================
    // IMAGE HANDLE
    // ======================
    $image_name = $unit['unit_image'];

    if (!empty($_FILES['unit_image']['name'])) {

        if (!empty($unit['unit_image'])) {
            $old = "public/uploads/units/" . $unit['unit_image'];
            if (file_exists($old)) unlink($old);
        }

        $ext = pathinfo($_FILES['unit_image']['name'], PATHINFO_EXTENSION);
        $image_name = time() . '_' . rand(1000, 9999) . '.' . $ext;

        move_uploaded_file(
            $_FILES['unit_image']['tmp_name'],
            "public/uploads/units/" . $image_name
        );
    }

    // ======================
    // UPDATE
    // ======================
    if ($edit_id) {

        $sql = "UPDATE unit SET
            unit_name='$unit_name',
            floor='$floor',
            unit_type='$unit_type',
            size='$size',
            rent=$rent,
            advance=$advance,
            unit_image='$image_name'
            WHERE id=$edit_id";

        $message = mysqli_query($db, $sql)
            ? "<div class='alert alert-success'>Unit updated successfully</div>"
            : "<div class='alert alert-danger'>Update failed</div>";

    } else {

        // ======================
        // INSERT
        // ======================
        $sql = "INSERT INTO unit
        (unit_name, building_name, floor, unit_type, size, rent, advance, unit_image, status)
        VALUES
        ('$unit_name', $building_id, '$floor', '$unit_type', '$size', $rent, $advance, '$image_name', '$status')";

        $message = mysqli_query($db, $sql)
            ? "<div class='alert alert-success'>Unit created successfully</div>"
            : "<div class='alert alert-danger'>Create failed</div>";
    }
}
?>


<div class="nxl-content">

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h5 class="m-b-10">
                <?= $edit_id ? 'Edit Unit' : 'Create Unit' ?>
            </h5>
        </div>
        <div class="page-header-right">
            <a href="admin.php?page=unit&id=<?= $building_id ?>" class="btn btn-primary">Back</a>
        </div>
    </div>

    <?= $message ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">

                    <form method="POST" enctype="multipart/form-data">
                        <div class="card-body general-info">

                            <!-- Unit Name -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label class="fw-semibold">Unit Name</label>
                                </div>
                                <div class="col-lg-8">
                                    <input type="text" name="unit_name"
                                           value="<?= htmlspecialchars($unit['unit_name']) ?>"
                                           class="form-control" required>
                                </div>
                            </div>

                            <!-- Floor -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label class="fw-semibold">Description</label>
                                </div>
                                <div class="col-lg-8">
                                    <input type="text" name="floor"
                                           value="<?= htmlspecialchars($unit['floor']) ?>"
                                           class="form-control">
                                </div>
                            </div>

                            <!-- Unit Type -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label class="fw-semibold">Unit Type</label>
                                </div>
                                <div class="col-lg-8">
                                    <select name="unit_type" class="form-control">
                                        <option value="Flat" <?= $unit['unit_type']=='Flat'?'selected':'' ?>>Flat</option>
                                        <option value="Room" <?= $unit['unit_type']=='Room'?'selected':'' ?>>Room</option>
                                        <option value="Shop" <?= $unit['unit_type']=='Shop'?'selected':'' ?>>Shop</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Size -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label class="fw-semibold">Electricity Meter No</label>
                                </div>
                                <div class="col-lg-8">
                                    <input type="text" name="size"
                                           value="<?= htmlspecialchars($unit['size']) ?>"
                                           class="form-control" >
                                </div>
                            </div>

                            <!-- Rent -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label class="fw-semibold">Rent</label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label for="rent">Rent</label>
                                            <input type="number" step="0.01" name="rent"
                                           value="<?= htmlspecialchars($unit['rent']) ?>"
                                           class="form-control" placeholder="Rent" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="advance">Advance</label>
                                            <input type="number" step="0.01" name="advance"
                                           value="<?= htmlspecialchars($unit['advance']) ?>"
                                           class="form-control" placeholder="Advance" >
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Image -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label class="fw-semibold">Unit Image</label>
                                </div>
                                <div class="col-lg-8">
                                    <input type="file" name="unit_image" class="form-control">
                                    <?php if (!empty($unit['unit_image'])): ?>
                                        <img src="public/uploads/units/<?= $unit['unit_image'] ?>"
                                             class="mt-2 rounded"
                                             width="80">
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="row">
                                <div class="col-lg-4"></div>
                                <div class="col-lg-8">
                                    <button type="submit" name="btn" class="btn btn-success">
                                        <?= $edit_id ? 'Update Unit' : 'Save Unit' ?>
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
