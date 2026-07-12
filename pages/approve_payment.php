<?php
    if (isset($_GET['payslip_id'])) {
        $payslip_id = $_GET['payslip_id'];

        //payslip information
        $payslip_query = mysqli_query($db, "SELECT * FROM payment_history WHERE id = $payslip_id");
        $payslip = mysqli_fetch_assoc($payslip_query);
        $tenant_id = $payslip['tenant_id'];
        $invoice_id = $payslip['invoice_id'];
        $paid_amount = $payslip['paid_amount'];
        $bill_month = $payslip['bill_month'];


        // Update the payslip status to 'Approved'
        $sql = "UPDATE payment_history SET status = 'Approved' WHERE id = $payslip_id";
        if (mysqli_query($db, $sql)) {

            $description = "Payment of $paid_amount ৳ Received for Invoice #INV-$invoice_id for the month of " . date("M Y", strtotime($bill_month)) . ".";

            mysqli_query($db, "INSERT INTO `notification`(`tenant_id`, `title`, `description`, `status`, `reed`) VALUES ('$tenant_id','Payment Successful','$description','Approved','Yes')");

            echo "<script>alert('Payment Approved Successfully!'); window.location.href='admin.php?page=editbill&tenant_id=$tenant_id';</script>";
        } else {
            echo "<script>alert('Payment Approval Failed!'); window.location.href='admin.php?page=editbill&tenant_id=$tenant_id';</script>";
        }
    } else {
        echo "Invalid notification ID.";
    }
?>