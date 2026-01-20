<?php
session_start();

require_once "../app/config/database.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /OFFICE/rent-manage/public/login");
    exit;
}

$email    = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');

if ($email === '' || $password === '') {
    $_SESSION['error'] = "Please Enter Email and Password";
    header("Location: /OFFICE/rent-manage/public/login");
    exit;
}

$db = (new Database())->connect();

$stmt = $db->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $_SESSION['error'] = "Email Not Found !";
    header("Location: /OFFICE/rent-manage/public/login");
    exit;
}

$user = $result->fetch_assoc();

if (!password_verify($password, $user['password'])) {
    $_SESSION['error'] = "Wrong Password!";
    header("Location: /OFFICE/rent-manage/public/login");
    exit;
}

/* ===== LOGIN SUCCESS ===== */
$_SESSION['admin_id']   = $user['id'];
$_SESSION['admin_name'] = $user['name'];

header("Location: /OFFICE/rent-manage/public/dashboard");
exit;
