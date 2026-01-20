<?php
require_once "../helpers/auth.php";
auth_check();

class DashboardController {
    public function index(){
        include "../../resources/views/dashboard/index.php";
    }
}


?>