<?php 
$message = ""; // To store success or error message

if (isset($_POST['btn'])) {
    // Get form data
    $name          = trim($_POST['name'] ?? '');
    $building_type = trim($_POST['building_type'] ?? '');
    $address       = trim($_POST['address'] ?? '');
    $description   = trim($_POST['description'] ?? '');
    $location      = trim($_POST['location'] ?? '');

    // Basic validation
    if (empty($name) || empty($building_type) || empty($address)) {
        $message = "<div class='alert alert-danger'>Name, Building Type and Address are required!</div>";
    } else {
        // Image handling
        $image_name = "";
        $upload_dir  = "public/uploads/";           // Make sure this folder exists and is writable (chmod 755 or 777)
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $file_name     = $_FILES['image']['name'];
            $file_tmp      = $_FILES['image']['tmp_name'];
            $file_ext      = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

            // Check file type
            if (!in_array($file_ext, $allowed_types)) {
                $message = "<div class='alert alert-danger'>Only JPG, JPEG, PNG & GIF files are allowed!</div>";
            } 
            // Check file size (e.g. max 5MB)
            else if ($_FILES['image']['size'] > 5 * 1024 * 1024) {
                $message = "<div class='alert alert-danger'>File size must be less than 5MB!</div>";
            } 
            else {
                // Create unique filename
                $new_file_name = time() . '_' . uniqid() . '.' . $file_ext;
                $destination   = $upload_dir . $new_file_name;

                // Move uploaded file
                if (move_uploaded_file($file_tmp, $destination)) {
                    $image_name = $new_file_name; // This will be saved in database
                } else {
                    $message = "<div class='alert alert-danger'>Failed to upload image!</div>";
                }
            }
        }

        // If no errors so far, proceed to insert
        if (empty($message)) {
            // Escape values to prevent SQL injection
            $name          = mysqli_real_escape_string($db, $name);
            $building_type = mysqli_real_escape_string($db, $building_type);
            $address       = mysqli_real_escape_string($db, $address);
            $description   = mysqli_real_escape_string($db, $description);
            $location      = mysqli_real_escape_string($db, $location);
            $image_db      = mysqli_real_escape_string($db, $image_name);

            // Correct INSERT query (no need to insert 'id' if it's AUTO_INCREMENT)
            $sql = "INSERT INTO `building` 
                    (`name`, `building_type`, `address`, `description`, `image`, `location`) 
                    VALUES 
                    ('$name', '$building_type', '$address', '$description', '$image_db', '$location')";

            if (mysqli_query($db, $sql)) {
                $message = "<div class='alert alert-success'>Building added successfully!</div>";
            } else {
                $message = "<div class='alert alert-danger'>Error: " . mysqli_error($db) . "</div>";
            }
        }
    }
}
?>


<div class="nxl-content">
    <!-- [ page-header ] start -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Building Create</h5>
            </div>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex d-md-none">
                    <a href="javascript:void(0)" class="page-header-right-close-toggle">
                        <i class="feather-arrow-left me-2"></i>
                        <span>Back</span>
                    </a>
                </div>
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">

                    <a href="admin.php?page=building" class="btn btn-primary">
                        <i class="feather-arrow me-2"></i>
                        <span>Back</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- [ page-header ] end -->

    <!-- [ Main Content ] start -->
    <div class="main-content">
        <div class="row">
            <?php if (!empty($message)): ?>
                <?php echo $message; ?>
            <?php endif; ?>

            <div class="col-lg-12">
                <div class="card stretch stretch-full">
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="card-body general-info">
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="fullnameInput" class="fw-semibold">Name : </label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-user"></i></div>
                                        <input type="text" name="name" class="form-control" id="fullnameInput" placeholder="Name">
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label class="fw-semibold">Building Type : </label>
                                </div>
                                <div class="col-lg-8">
                                    <select class="form-control" name="building_type" data-select2-selector="country">
                                        <option selected disabled>Select Building Type</option>
                                        <option data-country="af" value="1">type One</option>
                                        <option data-country="ax" value="2">Type Two</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="addressInput" class="fw-semibold">Address: </label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-map-pin"></i></div>
                                        <textarea class="form-control" name="address" id="addressInput" cols="30" rows="3"
                                            placeholder="Address"></textarea>
                                    </div>
                                </div>
                            </div>
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="descriptionInput" class="fw-semibold">Description: </label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-type"></i></div>
                                        <textarea class="form-control" name="description" id="descriptionInput" cols="30" rows="5"
                                            placeholder="Description"></textarea>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="companyInput" class="fw-semibold">Image : </label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-photo"></i></div>
                                        <input type="file" name="image" class="form-control" id="companyInput" >
                                    </div>
                                </div>
                            </div>
                        
                            <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    <label for="websiteInput" class="fw-semibold">Location : </label>
                                </div>
                                <div class="col-lg-8">
                                    <div class="input-group">
                                        <div class="input-group-text"><i class="feather-link"></i></div>
                                        <input type="url" name="location" class="form-control" id="websiteInput" placeholder="Location">
                                    </div>
                                </div>
                            </div>

                             <div class="row mb-4 align-items-center">
                                <div class="col-lg-4">
                                    
                                </div>
                                <div class="col-lg-8">
                                    <button type="submit" name="btn" class="btn btn-success ">Save</button>
                                </div>
                            </div>
                            
                        </div>
                    </form>
                </div>      
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>