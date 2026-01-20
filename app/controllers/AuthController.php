<?php
require_once "../config/database.php";
session_start();

class AuthController {

    public function login(){
        $db = (new Database())->connect();

        $email    = trim($_POST['email']);
        $password = trim($_POST['password']);

        if(empty($email) || empty($password)){
            $_SESSION['error'] = "All fields required";
            header("Location: /login");
            exit;
        }

        $stmt = $db->prepare("SELECT * FROM users WHERE email=? LIMIT 1");
        $stmt->bind_param("s",$email);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows == 1){
            $user = $result->fetch_assoc();

            if(password_verify($password,$user['password'])){
                $_SESSION['admin_id']   = $user['id'];
                $_SESSION['admin_name'] = $user['name'];
                header("Location: /dashboard");
            } else {
                $_SESSION['error'] = "Wrong password";
                header("Location: /login");
            }
        } else {
            $_SESSION['error'] = "User not found";
            header("Location: /login");
        }
    }

    public function logout(){
        session_destroy();
        header("Location: /login");
    }
}
