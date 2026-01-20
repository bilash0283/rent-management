<?php
require_once __DIR__ . "/../helpers/auth.php";
auth_check();

class DashboardController {
    public function index(){
        require __DIR__ . "/../../resources/views/dashboard/index.php";
    }
}
