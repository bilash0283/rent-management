<?php 
    if(empty($_GET['invoice_id']) || $_GET['invoice_id'] == '' || $_GET['invoice_id'] == null){
        echo "Invoice Id not Found !";
    }

    $invoice_id = $_GET['invoice_id'];

    echo $invoice_id;

?>