<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

switch ($uri) {
 case '/login':
   include "../resources/views/auth/login.php";
   break;

 case '/login-action':
   require "../app/controllers/AuthController.php";
   (new AuthController)->login();
   break;

 case '/dashboard':
   require "../app/controllers/DashboardController.php";
   (new DashboardController)->index();
   break;

 case '/logout':
   require "../app/controllers/AuthController.php";
   (new AuthController)->logout();
   break;

 default:
   echo "404 Not Found";
}

?>