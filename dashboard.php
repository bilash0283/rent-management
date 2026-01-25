<?php 
session_start();
if (empty($_SESSION["role"]) || empty($_SESSION['email']) || empty($_SESSION['id'])) {
    header('location:index.php');
}

echo $_SESSION['id'];     
echo $_SESSION['name'];        
echo $_SESSION['email'];      
echo $_SESSION['phone']."***";         
echo $_SESSION['role']."***";        



?>

<a href="pages/logout.php">logout</a>