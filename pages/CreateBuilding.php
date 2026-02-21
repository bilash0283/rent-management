<?php

$message = "";
$building_id = isset($_GET['id']) && is_numeric($_GET['id']) ? (int)$_GET['id'] : 0;
$is_edit = $building_id > 0;

function getBuildingTypeName($type) {
    $types = [
        1 => 'Residential',
        2 => 'Commercial',
        3 => 'Industrial',
        4 => 'Institutional',
        // add more if needed
    ];
    return $types[$type] ?? 'Unknown';
}

// Fetch existing data if editing
$existing = [
    'name'          => '',
    'building_type' => '',
    'address'       => '',
    'description'   => '',
    'location'      => '',
    'image'         => ''
];

if ($is_edit) {
    $sql = "SELECT * FROM building WHERE id = $building_id";
    $result = mysqli_query($db, $sql);
    if ($row = mysqli_fetch_assoc($result)) {
        $existing = $row;
    } else {
        $message = "<div class='alert alert-danger'>Building not found!</div>";
        $is_edit = false;
    }
}

// ==================== Form Submission ====================
if (isset($_POST['btn'])) {
    $name          = trim($_POST['name'] ?? '');
    $building_type = (int)($_POST['building_type'] ?? 0);   // Now integer
    $address       = trim($_POST['address'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $location      = trim($_POST['location'] ?? '');

    // Validation
    if (empty($name) || $building_type === 0 || empty($address)) {
        $message = "<div class='alert alert-danger'>Name, Building Type and Address are required!</div>";
    } else {
        $image_name = $existing['image']; // keep old image by default
        $upload_dir = "public/uploads/buildings/";
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        // Handle new image upload
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_name = $_FILES['image']['name'];
            $file_tmp  = $_FILES['image']['tmp_name'];
            $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            if (!in_array($file_ext, $allowed_types)) {
                $message = "<div class='alert alert-danger'>Only JPG, JPEG, PNG & GIF allowed!</div>";
            } elseif ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                $message = "<div class='alert alert-danger'>File size must be less than 5MB!</div>";
            } else {
                $new_file_name = time() . '_' . uniqid() . '.' . $file_ext;
                $destination   = $upload_dir . $new_file_name;

                if (move_uploaded_file($file_tmp, $destination)) {
                    // Delete old image if exists
                    if ($is_edit && !empty($existing['image']) && file_exists($upload_dir . $existing['image'])) {
                        @unlink($upload_dir . $existing['image']);
                    }
                    $image_name = $new_file_name;
                } else {
                    $message = "<div class='alert alert-danger'>Failed to upload image!</div>";
                }
            }
        }

        // Proceed only if no upload error
        if (empty($message)) {
            $name          = mysqli_real_escape_string($db, $name);
            $address       = mysqli_real_escape_string($db, $address);
            $description   = mysqli_real_escape_string($db, $description);
            $location      = mysqli_real_escape_string($db, $location);
            $image_db      = mysqli_real_escape_string($db, $image_name);

            if ($is_edit) {
                // UPDATE
                $sql = "UPDATE building SET 
                            name = '$name',
                            building_type = $building_type,
                            address = '$address',
                            description = '$description',
                            location = '$location',
                            image = '$image_db'
                        WHERE id = $building_id";

                if (mysqli_query($db, $sql)) {
                    $message = "<div class='alert alert-success'>Building updated successfully!</div>";
                } else {
                    $message = "<div class='alert alert-danger'>Update failed: " . mysqli_error($db) . "</div>";
                }
            } else {
                // INSERT
                $sql = "INSERT INTO building 
                        (name, building_type, address, description, image, location) 
                        VALUES 
                        ('$name', $building_type, '$address', '$description', '$image_db', '$location')";

                if (mysqli_query($db, $sql)) {
                    $message = "<div class='alert alert-success'>Building added successfully!</div>";
                    // Reset form after success
                    $existing = ['name'=>'','building_type'=>'','address'=>'','description'=>'','location'=>'','image'=>''];
                    $is_edit = false;
                } else {
                    $message = "<div class='alert alert-danger'>Insert failed: " . mysqli_error($db) . "</div>";
                }
            }
        }
    }
}
?>

<div class="nxl-content">
    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10"><?php echo $is_edit ? 'Edit Building' : 'Create New Building'; ?></h5>
            </div>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex d-md-none">
                    <a href="javascript:void(0)" class="page-header-right-close-toggle">
                        <i class="feather-arrow-left me-2"></i><span>Back</span>
                    </a>
                </div>
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="admin.php?page=building" class="btn btn-primary">
                        <i class="feather-arrow-left me-2"></i><span>Back to List</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages -->
    <?php if (!empty($message)) echo $message; ?>

    <!-- Main Content -->
    <div class="main-content">
        <div class="row">
            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="card-body general-info">
                            <!-- Name -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label class="fw-semibold">Name <span class="text-danger">*</span></label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="feather-user"></i></span>
                                        <input type="text" name="name" class="form-control" 
                                               value="<?php echo htmlspecialchars($existing['name']); ?>" 
                                               placeholder="Building Name" required>
                                    </div>
                                </div>
                            </div>

                            <!-- Building Type -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label class="fw-semibold">Building Type <span class="text-danger">*</span></label>
                                </div>
                                <div class="col-lg-8">
                                    <select name="building_type" class="form-control" required>
                                        <option value="0" disabled <?php echo empty($existing['building_type']) ? 'selected' : ''; ?>>Select Type</option>
                                        <option value="1" <?php echo $existing['building_type'] == 1 ? 'selected' : ''; ?>>Residential</option>
                                        <option value="2" <?php echo $existing['building_type'] == 2 ? 'selected' : ''; ?>>Commercial</option>
                                        <option value="3" <?php echo $existing['building_type'] == 3 ? 'selected' : ''; ?>>Industrial</option>
                                        <option value="4" <?php echo $existing['building_type'] == 4 ? 'selected' : ''; ?>>Institutional</option>
                                        <option value="5" <?php echo $existing['building_type'] == 5 ? 'selected' : ''; ?>>Residential & Commercial</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label class="fw-semibold">Address <span class="text-danger">*</span></label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="feather-map-pin"></i></span>
                                        <textarea name="address" class="form-control" rows="3" 
                                                  placeholder="Full Address" required><?php echo htmlspecialchars($existing['address']); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label class="fw-semibold">Description</label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="feather-type"></i></span>
                                        <textarea name="description" class="form-control" rows="5" 
                                                  placeholder="Details about the building"><?php echo htmlspecialchars($existing['description']); ?></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Image + Preview -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label class="fw-semibold">Image</label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="input-group mb-2">
                                        <span class="input-group-text"><i class="feather-photo"></i></span>
                                        <input type="file" name="image" class="form-control" accept="image/*" id="imageInput">
                                    </div>

                                    <!-- Image Preview -->
                                    <div id="imagePreview" class="mt-3" <?php echo empty($existing['image']) ? 'style="display:none;"' : ''; ?>>
                                        <p class="mb-2 fw-medium">Current / Selected Image:</p>
                                        <img id="previewImg" src="<?php 
                                            $img_src = !empty($existing['image']) && file_exists("public/uploads/buildings/" . $existing['image'])
                                                ? "public/uploads/buildings/" . htmlspecialchars($existing['image'])
                                                : "assets/images/no-image.png";
                                            echo $img_src;
                                        ?>" 
                                             alt="Preview" class="img-thumbnail" 
                                             style="max-width: 300px; max-height: 200px; object-fit: cover;">
                                    </div>
                                </div>
                            </div>

                            <!-- Location -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label class="fw-semibold">Location (Link)</label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="feather-link"></i></span>
                                        <input type="url" name="location" class="form-control" 
                                               value="<?php echo htmlspecialchars($existing['location']); ?>" 
                                               placeholder="https://maps.google.com/...">
                                    </div>
                                </div>
                            </div>

                            <!-- Submit -->
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4"></div>
                                <div class="col-lg-8">
                                    <button type="submit" name="btn" class="btn btn-success px-5">
                                        <?php echo $is_edit ? 'Update Building' : 'Save Building'; ?>
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

<!-- Live Image Preview Script -->
<script>
document.getElementById('imageInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('previewImg').src = event.target.result;
            document.getElementById('imagePreview').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});
</script>