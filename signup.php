<?php
include "database/session_manage.php";
include "database/db.php";

if (!empty($_SESSION["role"]) || !empty($_SESSION['phone']) || !empty($_SESSION['id'])) {
    header('location:admin.php');
    exit;
}

$error = '';
$success = '';

/* ================= IMPROVED Phone Normalization ================= */
function normalizePhone($phone) {
    $phone = preg_replace('/[^0-9+]/', '', trim($phone));

    if (strpos($phone, '+880') === 0) return $phone;

    $phone = ltrim($phone, '+');

    if (strpos($phone, '880') === 0) {
        $phone = substr($phone, 3);
    }
    if (strpos($phone, '0') === 0) {
        $phone = substr($phone, 1);
    }

    if (strlen($phone) > 11) {
        $phone = substr($phone, -11);
    }

    if (strlen($phone) === 10 && strpos($phone, '1') === 0) {
        return '+880' . $phone;
    }
    if (strlen($phone) === 11 && strpos($phone, '1') === 0) {
        return '+880' . substr($phone, 1);
    }

    return '+880' . $phone;
}

/* ================= AJAX : LOAD UNITS ================= */
if (isset($_POST['ajax']) && $_POST['ajax'] === 'get_units') {
    $building_id = (int)$_POST['building_id'];

    $sql = "SELECT id, unit_name, rent, advance 
            FROM unit 
            WHERE building_name = $building_id 
            ORDER BY unit_name ASC";

    $q = mysqli_query($db, $sql);
    echo '<option value="">Select Unit</option>';
    while ($row = mysqli_fetch_assoc($q)) {
        echo "<option value='{$row['id']}'>
                {$row['unit_name']} (R-৳{$row['rent']}) (A-৳{$row['advance']})
              </option>";
    }
    exit;
}

/* ================= SIGNUP PROCESS ================= */
if (isset($_POST['sign_up'])) {

    $name         = mysqli_real_escape_string($db, trim($_POST['name']));
    $email        = mysqli_real_escape_string($db, trim($_POST['email']));
    $phone_raw    = trim($_POST['phone']);
    $building     = (int)$_POST['building'];
    $unit         = (int)$_POST['unit'];
    $password     = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    $phone = normalizePhone($phone_raw);

    if (empty($name)) {
        $error = "Name is required!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Valid email address is required!";
    } elseif ($building <= 0) {
        $error = "Please select a Building!";
    } elseif ($unit <= 0) {
        $error = "Please select a Unit!";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters!";
    } elseif ($password !== $confirm_password) {
        $error = "Password does not match!";
    } else {

        $db_phone = mysqli_real_escape_string($db, $phone);

        // Check Email
        $check_email = mysqli_query($db, "SELECT id FROM tenants WHERE email='$email' LIMIT 1");
        if (mysqli_num_rows($check_email) > 0) {
            $error = "Email already exists!";
        } else {
            // Check Phone
            $check_phone = mysqli_query($db, "SELECT id FROM tenants WHERE phone='$db_phone' LIMIT 1");
            if (mysqli_num_rows($check_phone) > 0) {
                $error = "This phone number is already registered!";
            } else {

                $hashed_password = md5($password);

                $insert = mysqli_query($db, "
                    INSERT INTO tenants 
                    (name, email, phone, password, role, status, building_id, unit_id) 
                    VALUES 
                    ('$name', '$email', '$db_phone', '$hashed_password', 'Tenant', 'Booked', '$building', '$unit')
                ");

                if ($insert) {
                    // Update Unit Status to Booked
                    mysqli_query($db, "UPDATE unit SET status='Booked' WHERE id=$unit");

                    $success = "Registration successful! Your unit has been booked.";
                } else {
                    $error = "Something went wrong! Please try again.";
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Rent Manager</title>
    <link rel="shortcut icon" href="public/assets/images/favicon.ico">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            background: #f4f6fb;
            font-family: 'Segoe UI', sans-serif;
        }
        .bg-shape, .bg-shape2 {
            position: fixed;
            border-radius: 50%;
            opacity: .12;
        }
        .bg-shape { top: -180px; right: -120px; width: 420px; height: 420px; background: linear-gradient(135deg, #17a2b8, #5ad6e8); }
        .bg-shape2 { bottom: -180px; left: -120px; width: 350px; height: 350px; background: linear-gradient(135deg, #17a2b8, #8ee8f5); }

        .main-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .register-box {
            width: 100%;
            max-width: 480px;
            background: #fff;
            padding: 35px 30px;
            border-radius: 20px;
            box-shadow: 0 10px 35px rgba(0,0,0,.08);
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
        .title { font-size: 28px; font-weight: 700; color: #17a2b8; margin-top: 18px; }
        .input-group {
            background: #f8f9fa; border-radius: 12px; overflow: hidden;
            border: 1px solid #eee; margin-bottom: 16px;
        }
        .input-group-text { background: none; border: none; color: #999; padding-left: 15px; }
        .form-control { border: none; background: none; height: 50px; }
        .register-btn {
            width: 100%; height: 52px; border: none; border-radius: 12px;
            background: #17a2b8; color: #fff; font-weight: 600;
        }
        .register-btn:hover { background: #148ea1; }
        small { display: block; margin-top: -8px; margin-bottom: 12px; color: #666; font-size: 12.5px; }
    </style>
</head>
<body>

<div class="bg-shape"></div>
<div class="bg-shape2"></div>

<div class="main-wrapper">
    <div class="register-box">
        <div class="text-center">
            <div class="logo-box">
                <img src="./public/assets/images/logo-full.png" alt="Logo"
                    onerror="this.src='public/images/logo-full.png'">
            </div>
            <h2 class="title">Create Account</h2>
            <p class="sub-title">Rent Manager Registration</p>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= $error ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= $success ?></div>
        <?php endif; ?>

        <form method="POST" id="signupForm">
            <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-user"></i></span></div>
                <input type="text" name="name" class="form-control" placeholder="Full Name" required>
            </div>

            <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-envelope"></i></span></div>
                <input type="email" name="email" class="form-control" placeholder="Email Address" required>
            </div>

            <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-phone"></i></span></div>
                <input type="text" name="phone" class="form-control" placeholder="017XXXXXXXX" required>
            </div>
            <!-- <small class="pl-2">Example: 01712345678 or +8801712345678</small> -->

            <!-- Building -->
            <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-building"></i></span></div>
                <select name="building" id="building" class="form-control" required>
                    <option value="">Select Building</option>
                    <?php
                    $b = mysqli_query($db, "SELECT id, name FROM building ORDER BY name");
                    while ($row = mysqli_fetch_assoc($b)) {
                        echo "<option value='{$row['id']}'>{$row['name']}</option>";
                    }
                    ?>
                </select>
            </div>

            <!-- Unit -->
            <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-door-open"></i></span></div>
                <select name="unit" id="unit" class="form-control" required>
                    <option value="">Select Unit</option>
                </select>
            </div>

            <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-lock"></i></span></div>
                <input type="password" name="password" class="form-control" placeholder="Password" required>
            </div>

            <div class="input-group">
                <div class="input-group-prepend"><span class="input-group-text"><i class="fas fa-key"></i></span></div>
                <input type="password" name="confirm_password" class="form-control" placeholder="Confirm Password" required>
            </div>

            <button type="submit" name="sign_up" class="register-btn">
                Register & Book Unit <i class="fas fa-user-plus ml-1"></i>
            </button>
        </form>

        <div class="text-center mt-3 ">
            Already have an account? <a href="index.php" class="text-info">Sign In</a>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#building').on('change', function() {
        const buildingID = $(this).val();
        $('#unit').html('<option value="">Loading units...</option>');

        if (buildingID) {
            $.post('', {
                ajax: 'get_units',
                building_id: buildingID
            }, function(data) {
                $('#unit').html(data);
            });
        } else {
            $('#unit').html('<option value="">Select Unit</option>');
        }
    });
});
</script>

</body>
</html>