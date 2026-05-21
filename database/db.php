
<?php
$db = mysqli_connect("127.0.0.1","root","","rent_manager");

if(!$db){
    echo "Database connection Error!";
} else {
    mysqli_set_charset($db, "utf8mb4");
    // echo "DB connect successful";
}
?>





