<?php 
    // Check Invoice ID
    if (!isset($_GET['invoice_id']) || empty($_GET['invoice_id'])) {
        die("Invoice Id not Found!");
    } else {
        $invoice_id = intval($_GET['invoice_id']);
    }

    // Check Unit ID
    if (!isset($_GET['unit_id']) || empty($_GET['unit_id'])) {
        die("Unit Id not Found!");
    } else {
        $unit_id = intval($_GET['unit_id']);
    }

    // First check payment history exists or not
    $check_payment = mysqli_query($db, "SELECT * FROM payment_history WHERE invoice_id = '$invoice_id'");

    // If payment history found, delete all records
    if (mysqli_num_rows($check_payment) > 0) {

        // Delete all payment history rows
        mysqli_query($db, "DELETE FROM payment_history WHERE invoice_id = '$invoice_id'");
    }

    // Delete invoice
    $delete_invoice = mysqli_query($db, "DELETE FROM invoices WHERE id = '$invoice_id'");

    // Success Message + Redirect
    if ($delete_invoice) {

        echo "
        <script>
            alert('Invoice Deleted Successfully');
            window.location.href='admin.php?page=editbill&unit_id=$unit_id';
        </script>
        ";

    } else {

        echo "
        <script>
            alert('Something Went Wrong!');
            window.location.href='admin.php?page=editbill&unit_id=$unit_id';
        </script>
        ";

    }

    exit;
?>