<?php 
    if(!isset($_GET['advance_id']) || !isset($_GET['tenant_id']) || !isset($_GET['unit_id'])) {
        header("Location: admin.php");
        exit();
    }

    $advance_id = $_GET['advance_id'];
    $tenant_id = $_GET['tenant_id'];
    $unit_id = $_GET['unit_id'];

    //Delete advance payment record from the database
    $sql = "DELETE FROM advance WHERE id = $advance_id";
    if ($db->query($sql) === TRUE) {
        header("Location: admin.php?page=editbill&tenant_id=$tenant_id");
        exit();
    } else {
        echo "Error deleting record: " . $db->error;
    }

?>