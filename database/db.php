
<?php
// $db = mysqli_connect('localhost', 'root', '', 'adminction_portal');
$db = mysqli_connect("127.0.0.1","root","","rent_management",3307);

if(!$db){
    echo "Database connection Error!";
} else {
    mysqli_set_charset($db, "utf8mb4");
    echo "DB connect successful";
}
?>

<!-- college-details.php?name=uk -->




