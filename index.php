<?php
    include "database/db.php";
    session_name("rant_manager");
    session_start();
    if (!empty($_SESSION["role"]) || !empty($_SESSION['email']) || !empty($_SESSION['id'])) {
        header('location:admin.php');
    }

    $error = '';
    if (isset($_POST['sign_in'])) {
        $input_email = $_POST['email'];
        $input_password = md5($_POST['password']);

        $select_user = "SELECT * FROM users WHERE email = '$input_email'";
        $user_sql = mysqli_query($db, $select_user);

        $count_user = mysqli_num_rows($user_sql);

        if ($count_user < 1) {
            $error = "No Email Found!";
        } else {
            $_SESSION = [];
            while ($row = mysqli_fetch_assoc($user_sql)) {
                $email                   = $row['email'];
                $password                = $row['password'];
                $role                    = $row['role'];

                if($input_password == $password && $role == 1 && $input_email = $email){
                    $_SESSION['id']          = $row['id'];
                    $_SESSION['name']        = $row['name'];
                    $_SESSION['email']       = $row['email'];
                    $_SESSION['phone']       = $row['phone'];
                    $_SESSION['role']        = $row['role'];

                    header('location:admin.php');
                }else{
                    $error = "Invalid Email or Password";
                }


            }

        }
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>RENT MANAGER</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Bootstrap 4 -->
    <link rel="stylesheet"
          href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <!-- Icons -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        body {
            background: #f2f4f8;
        }
        .login-card {
            border-radius: 15px;
        }
    </style>
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0d6efd">
</head>
<script>
if ('serviceWorker' in navigator) {
  navigator.serviceWorker.register('sw.js');
}
</script>
<body>

<div class="bg-light min-vh-100 d-flex align-items-center justify-content-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5 col-xl-4">
                
                <!-- Login Card -->
                <div class="card border-0 shadow-lg rounded-4">
                    <div class="card-body p-4 p-sm-5">
                        
                        <!-- Header & Logo -->
                        <div class="text-center mb-4">
                            <div class="bg-white shadow-sm d-inline-flex align-items-center justify-content-center rounded-circle border p-2 mb-3" 
                                 style="width: 90px; height: 90px;">
                                <!-- Image Path ঠিক থাকলে এটি কাজ করবে -->
                                <img src="./public/assets/images/logo-full.png" 
                                     alt="Logo" 
                                     class="img-fluid rounded-circle"
                                     onerror="this.src='public/images/logo-full.png ">
                            </div>
                            <h3 class="fw-bold mb-1 text-info"><strong>Admin Sign in</strong></h3>
                            <p class="text-muted small">Rent Manager System</p>
                        </div>

                        <!-- Error Message -->
                        <?php if(isset($error) && $error): ?>
                            <div class="alert alert-danger d-flex align-items-center small py-2 border-0" role="alert">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <div><?= $error ?></div>
                            </div>
                        <?php endif; ?>

                        <!-- Login Form -->
                        <form method="POST">
                            <!-- Email -->
                            <div class="mb-3">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted">
                                        <i class="fas fa-envelope"></i>
                                    </span>
                                    <input type="email" name="email" 
                                           class="form-control bg-light border-start-0 py-2" 
                                           placeholder="Email Address" required>
                                </div>
                            </div>

                            <!-- Password -->
                            <div class="mb-4">
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0 text-muted">
                                        <i class="fas fa-lock"></i>
                                    </span>
                                    <input type="password" name="password" 
                                           class="form-control bg-light border-start-0 py-2" 
                                           placeholder="Password" required>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" name="sign_in" 
                                    class="btn btn-info w-100 text-white fw-bold py-2 rounded-3 shadow-sm">
                                Login <i class="fas fa-sign-in-alt ms-2"></i>
                            </button>
                        </form>

                    </div>
                </div>
                
                <!-- Footer Text -->
                <div class="text-center mt-4 text-muted small">
                    &copy; <?= date('Y') ?> Rent Manager | Design by <a href="https://bilash.ci-gsc.com/" class="text-info">Bilash Kumar</a>
                </div>

            </div>
        </div>
    </div>
</div>

</body>
</html>