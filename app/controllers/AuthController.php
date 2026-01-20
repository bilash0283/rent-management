<?php
session_start();
require_once "../config/database.php";

class AuthController {

    public function login(){
        $db = (new Database())->connect();

        $email = $_POST['email'];
        $password = $_POST['password'];

        $sql = "SELECT * FROM users WHERE email='$email' LIMIT 1";
        $result = $db->query($sql);

        if($result->num_rows == 1){
            $user = $result->fetch_assoc();
            if(password_verify($password,$user['password'])){
                $_SESSION['admin'] = $user['id'];
                header("Location: /dashboard");
            } else {
                echo "Password wrong";
            }
        } else {
            echo "User not found";
        }
    }

    public function logout(){
        session_destroy();
        header("Location: /login");
    }
}


?>