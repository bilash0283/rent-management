
<?php
$db = mysqli_connect("127.0.0.1","root","","rent_manager");

if(!$db){
    echo "Database connection Error!";
} else {
    mysqli_set_charset($db, "utf8mb4");
    // echo "DB connect successful";
}
?>

<div>
    <h1>This is a Database Connection Page</h1>
    <p>Database connection status: <?php echo mysqli_get_charset($db) ? "Connected" : "Not connected"; ?></p>
</div>




