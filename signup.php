<?php
include "database/db.php";

$error = '';
$success = '';

if (isset($_POST['sign_up'])) {

    $name = mysqli_real_escape_string($db, $_POST['name']);
    $email = mysqli_real_escape_string($db, $_POST['email']);
    $phone = mysqli_real_escape_string($db, $_POST['phone']);
    $password = md5($_POST['password']);
    $confirm_password = md5($_POST['confirm_password']);

    if ($password != $confirm_password) {

        $error = "Password does not match!";

    } else {

        $check = mysqli_query($db, "SELECT * FROM users WHERE email='$email'");

        if (mysqli_num_rows($check) > 0) {

            $error = "Email already exists!";

        } else {

            $insert = mysqli_query($db, "INSERT INTO users(name,email,phone,password,role)
            VALUES('$name','$email','$phone','$password',2)");

            if ($insert) {

                $success = "Registration successful!";

            } else {

                $error = "Something went wrong!";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Register | Rent Manager</title>

    <link rel="shortcut icon" href="public/assets/images/favicon.ico">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

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
        }

        .main-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .register-box {
            width: 100%;
            max-width: 450px;
            background: #fff;
            padding: 35px 30px;
            border-radius: 20px;
            box-shadow: 0 10px 35px rgba(0, 0, 0, .08);
            z-index: 2;
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
            border: 4px solid #f1f1f1;
            box-shadow: 0 5px 20px rgba(0, 0, 0, .08);
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
            border: 1px solid #eee;
            margin-bottom: 16px;
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
            box-shadow: none !important;
        }

        .register-btn {
            width: 100%;
            border: none;
            height: 52px;
            border-radius: 12px;
            background: #17a2b8;
            color: #fff;
            font-weight: 600;
            transition: .3s;
        }

        .register-btn:hover {
            background: #148ea1;
        }

        .alert {
            border-radius: 10px;
        }

        .footer-text {
            margin-top: 18px;
            text-align: center;
            color: #777;
            font-size: 13px;
        }

        .footer-text a {
            color: #17a2b8;
            text-decoration: none;
            font-weight: 600;
        }

        @media(max-width:576px) {

            .register-box {
                padding: 30px 22px;
            }

            .title {
                font-size: 24px;
            }

        }
    </style>

</head>

<body>

    <div class="bg-shape"></div>
    <div class="bg-shape2"></div>

    <div class="main-wrapper">

        <div class="register-box">

            <div class="text-center">

                <div class="logo-box">
                    <img src="./public/assets/images/logo-full.png" onerror="this.src='public/images/logo-full.png'">
                </div>

                <h2 class="title">Create Account</h2>

                <p class="sub-title">
                    Rent Manager Registration
                </p>

            </div>

            <?php if ($error) { ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= $error ?>
                </div>
            <?php } ?>

            <?php if ($success) { ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= $success ?>
                </div>
            <?php } ?>

            <form method="POST">

                <!-- Name -->
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fas fa-user"></i>
                        </span>
                    </div>
                    <input type="text" name="name" class="form-control" placeholder="Full Name" required>
                </div>

                <!-- Email -->
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fas fa-envelope"></i>
                        </span>
                    </div>
                    <input type="email" name="email" class="form-control" placeholder="Email Address" required>
                </div>

                <!-- Phone -->
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fas fa-phone"></i>
                        </span>
                    </div>
                    <input type="text" name="phone" class="form-control" placeholder="Phone Number" required>
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

                <!-- Confirm Password -->
                <div class="input-group">
                    <div class="input-group-prepend">
                        <span class="input-group-text">
                            <i class="fas fa-key"></i>
                        </span>
                    </div>
                    <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password"
                        required>
                </div>

                <button type="submit" name="sign_up" class="register-btn">

                    Register
                    <i class="fas fa-user-plus ml-1"></i>

                </button>

            </form>

            <div class="footer-text">

                Already have an account?

                <a href="index.php">
                    Sign In 
                </a>

            </div>

            <div class="footer-text">
                &copy; <?= date('Y') ?>
                Rent Manager |
                Powered by
                <a href="https://gsc.co.com/" target="_blank">
                    GSC
                </a>
            </div>

        </div>

    </div>

</body>

</html>