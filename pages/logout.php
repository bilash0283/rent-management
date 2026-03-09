<?php
    ob_start();
    session_name("rant_manager");
    session_start();
    session_unset();
    session_destroy();
    header('location:../index.php');
    ob_end_flush();
?>

