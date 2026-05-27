<?php
if (!isset($_GET['invoice_id']) || empty($_GET['invoice_id'])) {
    die("Invoice Id not Found!");
}else{
    $invoice_id = intval($_GET['invoice_id']);
}
if (!isset($_GET['unit_id']) || empty($_GET['unit_id'])) {
    die("Unit Id not Found!");
}else{
    $unit_id = intval($_GET['unit_id']);
}

$invoice_db_info = mysqli_query($db,"SELECT * FROM invoices WHERE id = '$invoice_id' ");
$data = mysqli_fetch_assoc($invoice_db_info);

$tenant_id = $data['tenant_id'];
$billing_month_db = $data['billing_month'];
$Rent_db = $data['Rent'];
$Gas_db = $data['Gas'];
$Water_db = $data['Water'];
$Electricity_db = $data['Electricity'];
$Others_db = $data['Others'];
$paid_amount_db = $data['paid_amount'];

$total_amount_db = $Rent_db + $Gas_db + $Water_db + $Electricity_db + $Others_db;

$Gas_month_db = $data['Gas_month'];
$Water_month_db = $data['Water_month'];
$Electricity_month_db = $data['Electricity_month'];
$Others_month_db = $data['Others_month'];

// Update invoice
if (isset($_POST['update_invoice'])) {

    // ইনপুট ভ্যালু রিসিভ করা
    $rent = intval($_POST['rent']);
    $Gas = intval($_POST['Gas']);
    $Water = intval($_POST['Water']);
    $Electricity = intval($_POST['Electricity']);
    $Others = intval($_POST['Others']);

    $rent_month = mysqli_real_escape_string($db, $_POST['rent_month']);
    $Gas_month = mysqli_real_escape_string($db, $_POST['Gas_month']);
    $Water_month = mysqli_real_escape_string($db, $_POST['Water_month']);
    $Electricity_month = mysqli_real_escape_string($db, $_POST['Electricity_month']);
    $Others_month = mysqli_real_escape_string($db, $_POST['Others_month']);

    $total_amount = $rent + $Gas + $Water + $Electricity + $Others;

    // পেমেন্ট স্ট্যাটাস নির্ধারণ (paid_amount_db এর সাথে তুলনা করে)
    $status = 'Unpaid'; 
    if ($paid_amount_db > 0 && $paid_amount_db >= $total_amount) {
        $status = 'Paid';
    } else if ($paid_amount_db > 0 && $paid_amount_db < $total_amount) {
        $status = 'Partial';
    } else {
        $status = 'Unpaid';
    }

    // ভ্যালিডেশন
    if ($total_amount <= 0) {
        echo "<script>alert('Total amount must be greater than 0.'); window.history.back();</script>";
        exit;
    } else {
        // আপডেট কোয়েরি (সরাসরি কলাম অনুযায়ী)
        $update_query = "UPDATE `invoices` SET 
            `billing_month`     = '$rent_month',
            `Rent`              = '$rent',
            `Gas`               = '$Gas',
            `Gas_month`         = '$Gas_month',
            `water`             = '$Water',
            `Water_month`       = '$Water_month',
            `Electricity`       = '$Electricity',
            `Electricity_month` = '$Electricity_month',
            `Others`            = '$Others',
            `Others_month`      = '$Others_month',
            `total_amount`      = '$total_amount',
            `status`            = '$status'
            WHERE `id`          = '$invoice_id'";

        $result = mysqli_query($db, $update_query);

        if ($result) {
            echo "<script>alert('Invoice Updated Successfully'); window.location.href='admin.php?page=editbill&tenant_id=$tenant_id';</script>";
            exit;
        } else {
            echo "Error Updating Invoice: " . mysqli_error($db);
        }
    }
}
?>

<div class="nxl-content">
    <div class="page-header d-flex justify-content-between align-items-center">
        <div class="page-header-left">
            <h5 class="m-b-10">Update Invoice / #INV-<?php echo $invoice_id; ?></h5>
        </div>
        <div class="page-header-right">
            <a href="admin.php?page=editbill&tenant_id=<?php echo $tenant_id; ?>" class="btn btn-primary">Back</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-10 mx-auto">
                    <form method="POST" enctype="multipart/form-data">
                        <div class="card p-3">
                            <h4 class="mb-3 text-success">Update Invoice</h4>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="fw-semibold">Rent for Month</small>
                                    <input type="month" name="rent_month" value="<?php echo htmlspecialchars($billing_month_db); ?>" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <small class="fw-semibold">Rent Amount</small>
                                    <input type="number" name="rent" value="<?= $Rent_db ?>" class="form-control">
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <small class="fw-semibold">Gas Month</small>
                                    <input type="month" name="Gas_month" value="<?php echo date('Y-m', strtotime($Gas_month_db)); ?>" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <small class="fw-semibold">Gas Amount</small>
                                    <input type="text" name="Gas" value="<?= $Gas_db ?>" class="form-control">
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <small class="fw-semibold">Water Month/Note</small>
                                    <input type="text" name="Water_month" value="<?php echo htmlspecialchars($Water_month_db); ?>" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <small class="fw-semibold">Water Amount</small>
                                    <input type="number" name="Water" value="<?= $Water_db ?>" class="form-control">
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <small class="fw-semibold">Electricity Month/Note</small>
                                    <input type="text" name="Electricity_month" value="<?php echo htmlspecialchars($Electricity_month_db); ?>" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <small class="fw-semibold">Electricity Amount</small>
                                    <input type="number" name="Electricity" value="<?php echo $Electricity_db; ?>" class="form-control">
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <small class="fw-semibold">Others Note</small>
                                    <input type="text" name="Others_month" value="<?php echo htmlspecialchars($Others_month_db); ?>" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <small class="fw-semibold">Others Amount</small>
                                    <input type="number" name="Others" value="<?php echo $Others_db; ?>" class="form-control">
                                </div>
                            </div>

                            <button type="submit" name="update_invoice" class="btn btn-success btn-sm mt-3">
                                Update Invoice
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>