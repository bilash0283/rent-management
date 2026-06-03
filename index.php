<?php
include "database/db.php";

session_name("rant_manager");
session_start();

if (!empty($_SESSION["role"]) || !empty($_SESSION['email']) || !empty($_SESSION['id'])) {
    header('location:admin.php');
    exit;
}

$error = '';

if (isset($_POST['sign_in'])) {

    $input_email = mysqli_real_escape_string($db, $_POST['email']);
    $input_password = md5($_POST['password']);

    $select_user = "SELECT * FROM users WHERE email='$input_email' LIMIT 1";
    $user_sql = mysqli_query($db, $select_user);

    if (mysqli_num_rows($user_sql) < 1) {

        $error = "Email not Found!";

    } else {

        $row = mysqli_fetch_assoc($user_sql);

        $email = $row['email'];
        $password = $row['password'];
        $role = $row['role'];

        if ($input_password == $password && $role == 1 && $input_email == $email) {

            $_SESSION['id'] = $row['id'];
            $_SESSION['name'] = $row['name'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['phone'] = $row['phone'];
            $_SESSION['role'] = $row['role'];

            header('location:admin.php');
            exit;

        } else {

            $error = "Invalid Email or Password";
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
        <link rel="shortcut icon" type="image/x-icon" href="public/assets/images/favicon.ico" />
        <!-- Bootstrap -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

        <link rel="manifest" href="manifest.json">
        <meta name="theme-color" content="#0d6efd">
        <style>
            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                background: #f4f6fb;
                font-family: 'Segoe UI', sans-serif;
                overflow-x: hidden;
            }

            /* Background Shape */
            .bg-shape {
                position: fixed;
                top: -180px;
                right: -120px;
                width: 420px;
                height: 420px;
                background: linear-gradient(135deg, #17a2b8, #5ad6e8);
                border-radius: 50%;
                opacity: .12;
                z-index: 0;
            }

            .bg-shape2 {
                position: fixed;
                bottom: -180px;
                left: -120px;
                width: 350px;
                height: 350px;
                background: linear-gradient(135deg, #17a2b8, #8ee8f5);
                border-radius: 50%;
                opacity: .10;
                z-index: 0;
            }

            .main-wrapper {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 20px;
                position: relative;
                z-index: 2;
            }

            .login-box {
                width: 100%;
                max-width: 430px;
                border-radius: 20px;
                padding: 35px 30px;
                box-shadow: 0 10px 35px rgba(0, 0, 0, 0.08);
            }

            .logo-box {
                width: 90px;
                height: 90px;
                margin: auto;
                background: #fff;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
                border: 4px solid #f1f1f1;
            }

            .logo-box img {
                width: 65px;
                height: 65px;
                object-fit: contain;
            }

            .title {
                font-size: 28px;
                font-weight: 700;
                color: #17a2b8;
                margin-top: 18px;
            }

            .sub-title {
                color: #777;
                font-size: 14px;
                margin-bottom: 25px;
            }

            .input-group {
                background: #f8f9fa;
                border-radius: 12px;
                overflow: hidden;
                margin-bottom: 18px;
                border: 1px solid #eee;
            }

            .input-group-text {
                background: none;
                border: none;
                color: #999;
                padding-left: 15px;
            }

            .form-control {
                border: none;
                background: none;
                height: 50px;
                font-size: 15px;
                box-shadow: none !important;
            }

            .form-control:focus {
                background: none;
            }

            .login-btn {
                width: 100%;
                border: none;
                height: 50px;
                border-radius: 12px;
                background: #17a2b8;
                color: #fff;
                font-weight: 600;
                transition: .3s;
            }

            .login-btn:hover {
                background: #148ea1;
            }

            .footer-text {
                margin-top: 20px;
                text-align: center;
                font-size: 13px;
                color: #777;
            }

            .footer-text a {
                color: #17a2b8;
                font-weight: 600;
                text-decoration: none;
            }

            .alert {
                border-radius: 10px;
                font-size: 14px;
            }

            @media(max-width:576px) {

                .login-box {
                    padding: 30px 22px;
                    border-radius: 18px;
                }

                .title {
                    font-size: 24px;
                }

            }
        </style>
    </head>

    <body>
        <!-- Background Shapes -->
        <div class="bg-shape"></div>
        <div class="bg-shape2"></div>
        <div class="main-wrapper">
            <div class="login-box">
                <!-- Logo -->
                <div class="text-center">
                    <div class="logo-box">
                        <img src="./public/assets/images/logo-full.png" alt="Logo"
                            onerror="this.src='public/images/logo-full.png'">
                    </div>
                    <h2 class="title">
                        Admin Sign in
                    </h2>
                    <p class="sub-title">
                        Rent Manager System
                    </p>
                </div>

                <!-- Error Message -->
                <?php if (isset($error) && $error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form method="POST">
                    <!-- Email -->
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                        </div>
                        <input type="email" name="email" class="form-control" placeholder="Email Address" required>
                    </div>
                    <!-- Password -->
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                        </div>
                        <input type="password" name="password" class="form-control" placeholder="Password" required>
                    </div>

                    <!-- Login Button -->
                    <button type="submit" name="sign_in" class="login-btn">

                        Login
                        <i class="fas fa-sign-in-alt ml-1"></i>
                    </button>
                </form>

                <a href="register.php" class="text-info">Register Now</a>

                <!-- Footer -->
                <div class="footer-text">
                    &copy; <?= date('Y') ?> Rent Manager |
                    Powered by
                    <a href="https://gsc.co.com/" target="_blank">
                        GSC
                    </a>
                </div>
            </div>
        </div>

        <!-- Service Worker -->
        <script>
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('sw.js');
            }
        </script>
    </body>
</html>