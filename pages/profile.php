<?php 
$user_id = $_SESSION['id'];

$sql = mysqli_query($db,"SELECT * FROM `users` WHERE id = '$user_id' ");
$user_row = mysqli_fetch_assoc($sql);

$name     = $user_row['name'];
$role     = $user_row['role'];
$email    = $user_row['email'];
$phone    = $user_row['phone'];
$password = $user_row['password'];
$old_image= $user_row['image'];

if(isset($_POST['btn'])){

    $name  = mysqli_real_escape_string($db, $_POST['name']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $phone = mysqli_real_escape_string($db, $_POST['phone']);

    $image_path = "public/uploads/users/";
    $new_image_name = $old_image; // default old image থাকবে

    // যদি নতুন image upload করা হয়
    if(isset($_FILES['image']) && $_FILES['image']['name'] != ''){

        $image_name = $_FILES['image']['name'];
        $tmp_name   = $_FILES['image']['tmp_name'];
        $error      = $_FILES['image']['error'];

        if($error === 0){

            $ext = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));
            $allowed = array('jpg','jpeg','png','webp');

            if(in_array($ext, $allowed)){

                // unique name create
                $new_image_name = "user_" . time() . "." . $ext;

                // পুরানো image delete (default.png ছাড়া)
                if(!empty($old_image) && file_exists($image_path.$old_image)){
                    unlink($image_path.$old_image);
                }

                // নতুন image upload
                move_uploaded_file($tmp_name, $image_path.$new_image_name);

            }
        }
    }

    // UPDATE QUERY (IMPORTANT: WHERE condition অবশ্যই দিতে হবে)
    $update_sql = mysqli_query($db,"UPDATE `users` 
        SET `name`='$name',
            `email`='$email',
            `phone`='$phone',
            `image`='$new_image_name'
        WHERE `id`='$user_id'
    ");

    if($update_sql){
        header('location:admin.php?page=profile');
    }else{
        header('location:admin.php?page=profile');
    }
}
?>


<div class="nxl-content">
    <!-- [ Main Content ] start -->
    <div class="main-content">
        <div class="container rounded bg-white mb-4">
            <div class="row">
                <div class="col-md-4 border-right">
                    <div class="d-flex flex-column align-items-center text-center p-3 py-5"><img
                            class="rounded-circle mt-5" src="<?php echo $old_image ? 'public/uploads/users/'.$old_image : 'public/uploads/users/no-image.png' ?>" width="90">
                        <span class="font-weight-bold fw-bold mt-3"><?= $name ?? '' ?></span>
                        <span class="text-black-50"><?= $email ?? '' ?></span><span><?= $phone ?? '' ?></span>
                    </div>
                </div>
                <div class="col-md-8">
                    <div class="p-3 py-5">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <a href="admin.php" class="d-flex flex-row align-items-center back"><i
                                        class="fa fa-long-arrow-left mr-1 mb-1"></i>
                                    <h6>Back to home</h6>
                                </a>
                                <h6 class="text-right">Edit Profile</h6>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-12"><input type="text" name="name" class="form-control" placeholder="first name"
                                        value="<?= $name ?? '' ?>"></div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12"><input type="text" name="email" class="form-control" placeholder="Email"
                                        value="<?= $email ?? '' ?>"></div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12"><input type="text" name="phone" class="form-control" placeholder="Phone"
                                        value="<?= $phone ?>"></div>
                                <div class="col-md-6"></div>
                            </div>
                            <div class="row mt-3">
                                <div class="col-md-12"><input type="file" name="image" class="form-control" ></div>
                                <div class="col-md-6"></div>
                            </div>
                            <div class="mt-5 text-right">
                                <button class="btn btn-primary profile-button" type="submit" name="btn">Save
                                    Profile</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- [ Main Content ] end -->
</div>