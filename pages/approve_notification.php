<?php
    if (isset($_GET['notification_id'])) {
        $notification_id = $_GET['notification_id'];

        // Update the notification status to 'Approved'
        $sql = "UPDATE notification SET status = 'Approved', title = 'Payment Successful' WHERE id = $notification_id";
        if (mysqli_query($db, $sql)) {
            header("Location: admin.php?page=notification&type=Admin");
            exit();
        } else {
            header("Location: admin.php?page=notification&type=Admin");
            exit();
        }
    } else {
        echo "Invalid notification ID.";
    }
?>