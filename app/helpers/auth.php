<?php
session_start();

function auth_check(){
    if(!isset($_SESSION['admin_id'])){
        header("Location: /login");
        exit;
    }
}
