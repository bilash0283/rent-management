<?php 
    if(isset($_POST['sign_in'])){
        $email = $_POST['email'];
        $pass = md5($_POST['password']);
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rent-Manage</title>
    <!-- Bootstrap 5.3 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Main Container -->
    <div class="container-fluid vh-100 d-flex align-items-center justify-content-center my-4">
        <div class="row w-100 justify-content-center">
            <div class="col-md-4">
                
                <!-- Card Design -->
                <div class="card border-0 shadow-lg rounded-4 p-3">
                    <div class="card-body">
                    
                        <!-- Header / Logo Area -->
                        <div class="text-center">
                            <div class="bg-primary bg-gradient text-white d-inline-block rounded-circle shadow" style="padding:8px 19px;">
                                <i class="bi bi-person-fill fs-1"></i>
                            </div>
                            <h2 class="fw-bold text-dark">Welcome Back</h2>
                            <p class="text-secondary small">Please enter your details to login</p>
                        </div>

                        <!-- Login Form -->
                        <form action="" method="POST">
                            <!-- Email Field -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-secondary">Email Address</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted">
                                        <i class="bi bi-envelope"></i>
                                    </span>
                                    <input type="email" class="form-control border-start-0 ps-0 shadow-none py-2" placeholder="name@example.com" required>
                                </div>
                            </div>

                            <!-- Password Field -->
                            <div class="mb-3">
                                <label class="form-label fw-semibold small text-secondary">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-white border-end-0 text-muted">
                                        <i class="bi bi-lock"></i>
                                    </span>
                                    <input type="password" class="form-control border-start-0 ps-0 shadow-none py-2" placeholder="••••••••" required>
                                </div>
                            </div>

                            <!-- Remember & Forgot -->
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input shadow-none" id="remember">
                                    <label class="form-check-label small text-muted" for="remember">Remember me</label>
                                </div>
                                <a href="#" class="text-decoration-none small fw-medium">Forgot Password?</a>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" name="sign_in" class="btn btn-primary w-100 py-2 fw-bold shadow-sm rounded-3">
                                SIGN IN
                            </button>
                        </form>

                    </div>
                </div>
                <!-- Simple Copyright Text -->
                <p class="text-center text-muted mt-4 small">© 2024 Your Company. All rights reserved.</p>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5.3 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>