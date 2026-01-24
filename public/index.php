<?php
session_start();

/*
|--------------------------------------------------------------------------
| REQUEST URI
|--------------------------------------------------------------------------
*/
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

/*
|--------------------------------------------------------------------------
| AUTO LOGIN PAGE
|--------------------------------------------------------------------------
*/
if (
    $uri === '/OFFICE/rent-manage/public' ||
    $uri === '/OFFICE/rent-manage/public/'
) {
    header("Location: /OFFICE/rent-manage/public/login");
    exit;
}

/*
|--------------------------------------------------------------------------
| ROUTER
|--------------------------------------------------------------------------
*/
switch ($uri) {

    case '/OFFICE/rent-manage/public/login':
        require "../resources/views/auth/login.php";
        break;

    case '/OFFICE/rent-manage/public/login-action':
        require "../app/controllers/AuthController.php";
        (new AuthController)->login();
        break;

    case '/OFFICE/rent-manage/public/dashboard':
        require "../app/controllers/DashboardController.php";
        (new DashboardController)->index();
        break;

    case '/OFFICE/rent-manage/public/logout':
        require "../app/controllers/AuthController.php";
        (new AuthController)->logout();
        break;

    default:
        http_response_code(404);
        echo "404 Not Found";
}



