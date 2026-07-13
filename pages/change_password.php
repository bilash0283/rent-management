<?php 
if (!isset($_SESSION['id'])) {
    header('location:login.php');
    exit;
}

$user_id = $_SESSION['id'];

$sql = mysqli_query($db, "SELECT * FROM `tenants` WHERE id = '$user_id'");
$user_row = mysqli_fetch_assoc($sql);

$name      = $user_row['name'] ?? '';
$email     = $user_row['email'] ?? '';
$phone     = $user_row['phone'] ?? '';
$old_image = $user_row['tenant_image'] ?? '';
$db_password = $user_row['password'];   // Current hashed password from DB

// Message variables
$success_msg = '';
$error_msg   = '';

if (isset($_POST['btn'])) {
    $current_password  = md5(mysqli_real_escape_string($db, $_POST['current_password']));
    $new_password      = md5(mysqli_real_escape_string($db, $_POST['new_password']));
    $confirm_password  = md5(mysqli_real_escape_string($db, $_POST['confirm_password']));

    // Validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_msg = "Please fill in all fields!";
    } elseif ($new_password !== $confirm_password) {
        $error_msg = "New password and confirm password do not match!";
    } elseif ($current_password !== $db_password) {
        $error_msg = "Current password is incorrect!";
    } else {

        $update_sql = mysqli_query($db, "UPDATE `tenants` 
            SET `password` = '$new_password' 
            WHERE `id` = '$user_id'");

        if ($update_sql) {
            $success_msg = "Password updated successfully!";
        } else {
            $error_msg = "Failed to update password. Please try again!";
        }
    }
}
?>

<div class="nxl-content">
    <div class="main-content">
        <div class="container rounded bg-white mb-4">
            <div class="row">
                <div class="col-md-4 border-right">
                    <div class="d-flex flex-column align-items-center text-center p-3 py-5">
                        <img class="rounded-circle mt-5" 
                             src="<?php echo $old_image ? 'public/uploads/tenants/'.$old_image : 'public/uploads/tenants/no-image.png' ?>" 
                             width="90" style="width:90px; height: 90px;">
                        <span class="font-weight-bold fw-bold mt-3"><?= htmlspecialchars($name) ?></span>
                        <span class="text-black-50"><?= htmlspecialchars($email) ?></span>
                        <span><?= htmlspecialchars($phone) ?></span>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="p-3 py-5">
                        <form action="" method="POST">
                            <!-- Success Message -->
                            <?php if (!empty($success_msg)): ?>
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <?= $success_msg ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <!-- Error Message -->
                            <?php if (!empty($error_msg)): ?>
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    <?= $error_msg ?>
                                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                                </div>
                            <?php endif; ?>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <a href="admin.php" class="d-flex flex-row align-items-center back">
                                    <i class="fa fa-long-arrow-left mr-1 mb-1"></i>
                                    <h6>Back to home</h6>
                                </a>
                                <h6 class="text-right">Change Password</h6>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <input type="password" name="current_password" class="form-control" 
                                           placeholder="Current Password" required>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <input type="password" name="new_password" class="form-control" 
                                           placeholder="New Password" required>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-12">
                                    <input type="password" name="confirm_password" class="form-control" 
                                           placeholder="Confirm New Password" required>
                                </div>
                            </div>

                            <div class="mt-5 text-right">
                                <button class="btn btn-primary profile-button" type="submit" name="btn">
                                    Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>