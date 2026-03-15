<?php
include "database/db.php";
$message = "";

$building_id = isset($_GET['building_id']) ? (int)$_GET['building_id'] : 0;
$edit_id     = isset($_GET['edit_id'])     ? (int)$_GET['edit_id']     : 0;

if ($building_id <= 0 && $edit_id <= 0) {
    die("<div class='alert alert-danger'>Invalid request - building ID missing</div>");
}

$unit = [
    'id'            => 0,
    'building_id'   => $building_id,
    'unit_name'     => '',
    'floor'         => '',
    'unit_type'     => 'Flat',
    'size'          => '',
    'rent'          => 0,
    'advance'       => 0,
    'unit_image'    => '',
    'water'         => 0,
    'gas'           => 0,
    'status'        => 'Available',
];

if ($edit_id > 0) {

    $get = mysqli_query($db, "SELECT * FROM unit WHERE id = $edit_id LIMIT 1");

    if ($get && mysqli_num_rows($get) === 1) {

        $unit = mysqli_fetch_assoc($get);
        $building_id = (int)$unit['building_name'];

    } else {
        $message = "<div class='alert alert-danger'>Unit not found</div>";
    }
}

if (isset($_POST['btn']) && empty($message)) {

    $unit_name = mysqli_real_escape_string($db, trim($_POST['unit_name'] ?? ''));
    $floor     = mysqli_real_escape_string($db, trim($_POST['floor'] ?? ''));
    $unit_type = mysqli_real_escape_string($db, $_POST['unit_type'] ?? 'Flat');
    $size      = mysqli_real_escape_string($db, trim($_POST['size'] ?? ''));

    $rent    = (float)($_POST['rent'] ?? 0);
    $advance = (float)($_POST['advance'] ?? 0);
    $water   = (float)($_POST['water'] ?? 0);
    $gas     = (float)($_POST['gas'] ?? 0);

    // IMPORTANT FIX
    if ($edit_id > 0) {
        $status = $unit['status']; // keep previous status
    } else {
        $status = 'Available'; // new unit
    }

    $image_name = $unit['unit_image'] ?? '';

    if (!empty($_FILES['unit_image']['name']) && $_FILES['unit_image']['error'] === UPLOAD_ERR_OK) {

        if (!empty($unit['unit_image'])) {

            $old_path = "public/uploads/units/" . $unit['unit_image'];

            if (file_exists($old_path)) {
                @unlink($old_path);
            }
        }

        $ext = strtolower(pathinfo($_FILES['unit_image']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];

        if (!in_array($ext,$allowed)) {

            $message = "<div class='alert alert-danger'>Only jpg, jpeg, png, webp allowed</div>";

        } else {

            $image_name = time().'_'.mt_rand(10000,99999).'.'.$ext;
            $upload_path = "public/uploads/units/".$image_name;

            if (!move_uploaded_file($_FILES['unit_image']['tmp_name'],$upload_path)) {
                $message = "<div class='alert alert-danger'>Image upload failed</div>";
            }
        }
    }

    if (empty($message)) {

        if ($edit_id > 0) {

            $sql = "UPDATE unit SET
                unit_name='$unit_name',
                floor='$floor',
                unit_type='$unit_type',
                size='$size',
                rent=$rent,
                advance=$advance,
                unit_image='$image_name',
                water=$water,
                gas=$gas
                WHERE id=$edit_id";

            if (mysqli_query($db,$sql)) {

                $message = "<div class='alert alert-success'>Unit updated successfully</div>";

            } else {

                $message = "<div class='alert alert-danger'>Update failed: ".mysqli_error($db)."</div>";
            }

        } else {

            $sql = "INSERT INTO unit
            (building_name,unit_name,floor,unit_type,size,rent,advance,unit_image,status,water,gas)
            VALUES
            ($building_id,'$unit_name','$floor','$unit_type','$size',$rent,$advance,'$image_name','$status',$water,$gas)";

            if (mysqli_query($db,$sql)) {

                $message = "<div class='alert alert-success'>Unit created successfully</div>";

            } else {

                $message = "<div class='alert alert-danger'>Create failed: ".mysqli_error($db)."</div>";
            }
        }
    }
}
?>

<div class="nxl-content">

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h5 class="m-b-10">
                <?= $edit_id > 0 ? 'Edit Unit' : 'Create New Unit' ?>
            </h5>
        </div>
        <div class="page-header-right">
            <a href="admin.php?page=unit&id=<?= $building_id ?>" class="btn btn-primary">Back to Units</a>
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
                                    <label class="fw-semibold">Unit Name <span class="text-danger">*</span></label>
                                </div>
                                <div class="col-lg-8">
                                    <input type="text" name="unit_name" required
                                           value="<?= htmlspecialchars($unit['unit_name'] ?? '') ?>"
                                           class="form-control" placeholder="e.g. A-101, B-05">
                                </div>
                            </div>

                            <!-- Floor -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label class="fw-semibold">Floor</label>
                                </div>
                                <div class="col-lg-8">
                                    <input type="text" name="floor"
                                           value="<?= htmlspecialchars($unit['floor'] ?? '') ?>"
                                           class="form-control" placeholder="e.g. 3rd, Ground, Basement">
                                </div>
                            </div>

                            <!-- Unit Type -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label class="fw-semibold">Unit Type</label>
                                </div>
                                <div class="col-lg-8">
                                    <select name="unit_type" class="form-control">
                                        <option value="Flat"  <?= ($unit['unit_type'] ?? '') === 'Flat'  ? 'selected' : '' ?>>Flat</option>
                                        <option value="Room"  <?= ($unit['unit_type'] ?? '') === 'Room'  ? 'selected' : '' ?>>Room</option>
                                        <option value="Shop"  <?= ($unit['unit_type'] ?? '') === 'Shop'  ? 'selected' : '' ?>>Shop</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Electricity Meter No -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label class="fw-semibold">Electricity Meter No</label>
                                </div>
                                <div class="col-lg-8">
                                    <input type="text" name="size"
                                           value="<?= htmlspecialchars($unit['size'] ?? '') ?>"
                                           class="form-control" placeholder="e.g. EM-456789">
                                </div>
                            </div>

                            <!-- Rent & Advance -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label class="fw-semibold">Rent & Advance</label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>Rent (৳) <span class="text-danger">*</span></label>
                                            <input type="number" step="0.01" min="0" name="rent" required
                                                   value="<?= htmlspecialchars($unit['rent'] ?? 0) ?>"
                                                   class="form-control" placeholder="Monthly Rent">
                                        </div>
                                        <div class="col-md-6">
                                            <label>Advance / Security (৳)</label>
                                            <input type="number" step="0.01" min="0" name="advance"
                                                   value="<?= htmlspecialchars($unit['advance'] ?? 0) ?>"
                                                   class="form-control" placeholder="Advance amount">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Utility Bills -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label class="fw-semibold">Fixed Utility Bills</label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label>Water (৳/month)</label>
                                            <input type="number" step="0.01" min="0" name="water"
                                                   value="<?= htmlspecialchars($unit['water'] ?? 0) ?>"
                                                   class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label>Gas (৳/month)</label>
                                            <input type="number" step="0.01" min="0" name="gas"
                                                   value="<?= htmlspecialchars($unit['gas'] ?? 0) ?>"
                                                   class="form-control">
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
                                    <input type="file" name="unit_image" accept="image/jpeg,image/png,image/webp" class="form-control">
                                    <?php if (!empty($unit['unit_image'])): ?>
                                        <div class="mt-3">
                                            <img src="public/uploads/units/<?= htmlspecialchars($unit['unit_image']) ?>"
                                                 class="rounded shadow-sm" width="180" alt="Current Unit Image">
                                            <small class="d-block text-muted mt-1">Current image</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="row mt-5">
                                <div class="col-lg-4"></div>
                                <div class="col-lg-8">
                                    <button type="submit" name="btn" class="btn btn-success px-5 py-2">
                                        <?= $edit_id > 0 ? 'Update Unit' : 'Save New Unit' ?>
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