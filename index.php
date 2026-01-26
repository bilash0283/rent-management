<?php
    include "database/db.php";
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
            // echo "<div class='alert alert-danger mt-2 text-center'>No Email Found!</div>";
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
                    // echo "<div class='alert alert-danger mt-2 text-center'>Only Admin Allowed!</div>";
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
    <title>Rent-Manage | Admin Login</title>
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
</head>
<body>

<div class="container vh-100 d-flex align-items-center justify-content-center">
    <div class="col-md-4">

        <div class="card shadow-lg login-card">
            <div class="card-body p-4">

                <div class="text-center mb-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex
                                align-items-center justify-content-center mb-3"
                         style="width:70px;height:70px;">
                        <i class="fas fa-user-shield fa-2x"></i>
                    </div>
                    <h4 class="font-weight-bold">Admin Login</h4>
                    <small class="text-muted">Sign in to dashboard</small>
                </div>

                <?php if($error): ?>
                    <div class="alert alert-danger text-center">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <form method="POST">

                    <div class="form-group">
                        <!-- <label>Email Address</label> -->
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white">
                                    <i class="fas fa-envelope"></i>
                                </span>
                            </div>
                            <input type="email" name="email"
                                   class="form-control"
                                   placeholder="admin@mail.com" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <!-- <label>Password</label> -->
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text bg-white">
                                    <i class="fas fa-lock"></i>
                                </span>
                            </div>
                            <input type="password" name="password"
                                   class="form-control"
                                   placeholder="********" required>
                        </div>
                    </div>

                    <button type="submit" name="sign_in"
                            class="btn btn-primary btn-block font-weight-bold">
                        LOGIN
                    </button>
                    
                </form>

            </div>
        </div>

        <p class="text-center text-muted mt-4 small">
            © <?= date('Y'); ?> Rent Management System
        </p>

    </div>
</div>

</body>
</html>