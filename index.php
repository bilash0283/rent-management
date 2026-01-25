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
    <title>Rent-Manage | Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body class="bg-light">

<div class="container vh-100 d-flex align-items-center justify-content-center">
    <div class="col-md-4">

        <div class="card border-0 shadow-lg rounded-4">
            <div class="card-body p-4">

                <div class="text-center mb-4">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center mb-3"
                         style="width:60px;height:60px;">
                        <i class="bi bi-person-fill fs-3"></i>
                    </div>
                    <h3 class="fw-bold">Welcome Back</h3>
                    <p class="text-muted small">Login to your account</p>
                </div>

                <!-- ERROR MESSAGE -->
                <?php if($error): ?>
                    <div class="alert alert-danger text-center small">
                        <?= $error ?>
                    </div>
                <?php endif; ?>

                <!-- LOGIN FORM -->
                <form method="POST">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-envelope"></i>
                            </span>
                            <input type="email" name="email" class="form-control" placeholder="admin@mail.com" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Password</label>
                        <div class="input-group">
                            <span class="input-group-text bg-white">
                                <i class="bi bi-lock"></i>
                            </span>
                            <input type="password" name="password" class="form-control" placeholder="********" required>
                        </div>
                    </div>

                    <button type="submit" name="sign_in"
                            class="btn btn-primary w-100 py-2 fw-bold rounded-3">
                        SIGN IN
                    </button>
                </form>

            </div>
        </div>

        <p class="text-center text-muted mt-4 small">
            © <?php echo date('Y'); ?> Rent Management System
        </p>

    </div>
</div>

</body>
</html>
