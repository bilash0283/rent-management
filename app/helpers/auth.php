<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function auth_check(){
    if (!isset($_SESSION['admin_id'])) {
        header("Location: /OFFICE/rent-manage/public/login");
        exit;
    }
}
