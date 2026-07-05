<?php
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'Tenant') {
    header('Location: admin.php?page=dashboard');
    exit();
}
$type = $_GET['type'];

if ($type === 'invoice') {
    echo "invoice";
} else if ($type === 'payment') {
    echo "payment";
}else {
    header('Location: admin.php?page=dashboard');
    exit();
}
?>