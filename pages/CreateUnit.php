<?php
include "database/db.php";

$message = "";

// ==========================
// GET IDs SAFELY
// ==========================
$building_id = isset($_GET['building_id']) ? (int)$_GET['building_id'] : 0;
$edit_id     = isset($_GET['edit_id'])     ? (int)$_GET['edit_id']     : 0;

// ==========================
// DEFAULT UNIT DATA
// ==========================
$unit = [
    'unit_name'     => '',
    'floor'         => '',
    'unit_type'     => 'Flat',
    'size'          => '',
    'rent'          => 0,
    'advance'       => 0,
    'unit_image'    => '',
    'water'         => 0,
    'gas'           => 0,
];

// ==========================
// EDIT MODE FETCH
// ==========================
if ($edit_id > 0) {
    $get = mysqli_query($db, "SELECT * FROM unit WHERE id = $edit_id");
    if ($get && mysqli_num_rows($get) > 0) {
        $unit = mysqli_fetch_assoc($get);
    }
}

// ==========================
// FORM SUBMIT
// ==========================
if (isset($_POST['btn'])) {

    // TEXT FIELDS
    $unit_name = mysqli_real_escape_string($db, trim($_POST['unit_name'] ?? ''));
    $floor     = mysqli_real_escape_string($db, trim($_POST['floor'] ?? ''));
    $unit_type = mysqli_real_escape_string($db, $_POST['unit_type'] ?? 'Flat');
    $size      = mysqli_real_escape_string($db, trim($_POST['size'] ?? ''));

    // NUMERIC FIELDS
    $rent        = (float) ($_POST['rent']        ?? 0);
    $advance     = (float) ($_POST['advance']     ?? 0);
    $water       = (float) ($_POST['water']       ?? 0);
    $gas         = (float) ($_POST['gas']         ?? 0);

    $status = 'Available';

    // ======================
    // IMAGE HANDLE
    // ======================
    $image_name = $unit['unit_image'] ?? '';

    if (!empty($_FILES['unit_image']['name'])) {
        // Delete old image if exists
        if (!empty($unit['unit_image'])) {
            $old_path = "public/uploads/units/" . $unit['unit_image'];
            if (file_exists($old_path)) {
                @unlink($old_path);
            }
        }

        $ext = strtolower(pathinfo($_FILES['unit_image']['name'], PATHINFO_EXTENSION));
        $image_name = time() . '_' . mt_rand(10000, 99999) . '.' . $ext;

        $upload_path = "public/uploads/units/" . $image_name;
        if (!move_uploaded_file($_FILES['unit_image']['tmp_name'], $upload_path)) {
            $message = "<div class='alert alert-danger'>Image upload failed</div>";
        }
    }

    if (empty($message)) {   // only proceed if no upload error

        if ($edit_id > 0) {
            // ======================
            // UPDATE
            // ======================
            $sql = "UPDATE unit SET
                unit_name    = '$unit_name',
                floor        = '$floor',
                unit_type    = '$unit_type',
                size         = '$size',
                rent         = $rent,
                advance      = $advance,
                unit_image   = '$image_name',
                water        = $water,
                gas          = $gas
                WHERE id = $edit_id";

            $message = mysqli_query($db, $sql)
                ? "<div class='alert alert-success'>Unit updated successfully</div>"
                : "<div class='alert alert-danger'>Update failed: " . mysqli_error($db) . "</div>";
        } else {
            // ======================
            // INSERT
            // ======================
            $sql = "INSERT INTO unit
                (building_name, unit_name, floor, unit_type, size, rent, advance, unit_image, status,
                 water, gas)
                VALUES
                ($building_id, '$unit_name', '$floor', '$unit_type', '$size', $rent, $advance, '$image_name', '$status',
                 $water, $gas)";

            $message = mysqli_query($db, $sql)
                ? "<div class='alert alert-success'>Unit created successfully</div>"
                : "<div class='alert alert-danger'>Create failed: " . mysqli_error($db) . "</div>";
        }
    }
}
?>

<div class="nxl-content">

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left">
            <h5 class="m-b-10">
                <?= $edit_id > 0 ? 'Edit Unit' : 'Create Unit' ?>
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
                                    <input type="text" name="unit_name" required
                                           value="<?= htmlspecialchars($unit['unit_name']) ?>"
                                           class="form-control">
                                </div>
                            </div>

                            <!-- Floor -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label class="fw-semibold">Floor</label>
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
                                        <option value="Flat" <?= $unit['unit_type'] === 'Flat' ? 'selected' : '' ?>>Flat</option>
                                        <option value="Room" <?= $unit['unit_type'] === 'Room' ? 'selected' : '' ?>>Room</option>
                                        <option value="Shop" <?= $unit['unit_type'] === 'Shop' ? 'selected' : '' ?>>Shop</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Size / Meter No -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label class="fw-semibold">Electricity Meter No</label>
                                </div>
                                <div class="col-lg-8">
                                    <input type="text" name="size"
                                           value="<?= htmlspecialchars($unit['size']) ?>"
                                           class="form-control">
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
                                            <label>Rent (৳)</label>
                                            <input type="number" step="0.01" name="rent" required
                                                   value="<?= htmlspecialchars($unit['rent']) ?>"
                                                   class="form-control" placeholder="Monthly Rent">
                                        </div>
                                        <div class="col-md-6">
                                            <label>Advance (৳)</label>
                                            <input type="number" step="0.01" name="advance"
                                                   value="<?= htmlspecialchars($unit['advance']) ?>"
                                                   class="form-control" placeholder="Advance / Security">
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
                                            <label>Water (৳)</label>
                                            <input type="number" step="0.01" name="water"
                                                   value="<?= htmlspecialchars($unit['water']) ?>"
                                                   class="form-control" placeholder="Water bill">
                                        </div>
                                        <div class="col-md-6">
                                            <label>Gas (৳)</label>
                                            <input type="number" step="0.01" name="gas"
                                                   value="<?= htmlspecialchars($unit['gas']) ?>"
                                                   class="form-control" placeholder="Gas bill">
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
                                    <input type="file" name="unit_image" accept="image/*" class="form-control">
                                    <?php if (!empty($unit['unit_image'])): ?>
                                        <div class="mt-2">
                                            <img src="public/uploads/units/<?= htmlspecialchars($unit['unit_image']) ?>"
                                                 class="rounded" width="120" alt="Unit Image">
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="row">
                                <div class="col-lg-4"></div>
                                <div class="col-lg-8">
                                    <button type="submit" name="btn" class="btn btn-success px-5">
                                        <?= $edit_id > 0 ? 'Update Unit' : 'Save Unit' ?>
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