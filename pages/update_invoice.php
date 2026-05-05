<?php
if (!isset($_GET['invoice_id']) || empty($_GET['invoice_id'])) {
    die("Invoice Id not Found!");
}

$invoice_id = intval($_GET['invoice_id']);
$unit_id = isset($_GET['unit_id']) ? intval($_GET['unit_id']) : 0;

// Fetch invoice data
$stmt = $db->prepare("SELECT * FROM invoices WHERE id = ?");
$stmt->bind_param("i", $invoice_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Invoice not found!");
}

$data = $result->fetch_assoc();

$billing_month_db = $data['billing_month'];
$Rent_db = $data['Rent'];
$Gas_db = $data['Gas'];
$Water_db = $data['Water'];
$Electricity_db = $data['Electricity'];
$Others_db = $data['Others'];

$Gas_month_db = $data['Gas_month'];
$Water_month_db = $data['Water_month'];
$Electricity_month_db = $data['Electricity_month'];
$Others_month_db = $data['Others_month'];


// Update invoice
if (isset($_POST['update_invoice'])) {

    $rent = intval($_POST['rent']);
    $Gas = intval($_POST['Gas']);
    $Water = intval($_POST['Water']);
    $Electricity = intval($_POST['Electricity']);
    $Others = intval($_POST['Others']);

    $rent_month = $_POST['rent_month'];
    $Gas_month = $_POST['Gas_month'];
    $Water_month = $_POST['Water_month'];
    $Electricity_month = $_POST['Electricity_month'];
    $Others_month = $_POST['Others_month'];

    $total_amount = $rent + $Gas + $Water + $Electricity + $Others;

    // Update query (Prepared Statement)
    $update = $db->prepare("UPDATE invoices SET 
        billing_month = ?,
        Rent = ?,
        Gas = ?,
        Gas_month = ?,
        Water = ?,
        Water_month = ?,
        Electricity = ?,
        Electricity_month = ?,
        Others = ?,
        Others_month = ?,
        total_amount = ?
        WHERE id = ?
    ");

    $update->bind_param(
        "siiisiisiiii",
        $rent_month,
        $rent,
        $Gas,
        $Gas_month,
        $Water,
        $Water_month,
        $Electricity,
        $Electricity_month,
        $Others,
        $Others_month,
        $total_amount,
        $invoice_id
    );

    if ($update->execute()) {
        echo "<script>alert('Invoice Updated Successfully'); window.location.href='admin.php?page=editbill&unit_id=$unit_id';</script>";
        exit;
    } else {
        echo "Update Failed!";
    }
}
?>


<div class="nxl-content">
    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <div class="page-header-left">
            <h5 class="m-b-10">
                Update Invoice
            </h5>
        </div>
        <div class="page-header-right">
            <a href="admin.php?page=editbill&unit_id=<?php echo $unit_id; ?>" class="btn btn-primary">Back</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="row">
                <div class="col-10 mx-auto">
                    <!-- create Invoice  -->
                    <form method="POST" enctype="multipart/form-data">
                        <div class="card p-3">
                            <h4 class="mb-3 text-success">Update Invoice</h4>
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="fw-semibold">Rent for Month </small>
                                    <input type="month" name="rent_month" value="<?php echo $billing_month_db; ?>"
                                        class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <small class="fw-semibold" for="status">Rent Amount</small>
                                    <input type="text" name="rent" value="<?= $Rent_db ?? '' ?>" class="form-control">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <small class="fw-semibold">Gas </small>
                                    <input type="month" name="Gas_month"
                                        value="<?php echo date('Y-m', strtotime($Gas_month_db)); ?>"
                                        class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <small class="fw-semibold" for="status">Gas Amount</small>
                                    <input type="text" name="Gas" value="<?= $Gas_db ?? '' ?>" class="form-control">
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <small class="fw-semibold">Water </small>
                                    <input type="text" name="Water_month"
                                        value="<?php echo date('M Y', strtotime($Water_month_db)); ?>"
                                        class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <small class="fw-semibold" for="status">Water Amount</small>
                                    <input type="text" name="Water" value="<?= $Water_db ?? '' ?>" class="form-control">
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <small class="fw-semibold">Electricity </small>
                                    <input type="text" name="Electricity_month"
                                        value="<?php echo date('M Y', strtotime($Electricity_month_db)); ?>"
                                        placeholder="Note" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <small class="fw-semibold" for="status">Electricity Amount</small>
                                    <input type="text" name="Electricity" value="<?php echo $Electricity_db; ?>"
                                        class="form-control">
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <small class="fw-semibold">Others </small>
                                    <input type="text" name="Others_month" value="<?php echo $Others_month_db; ?>"
                                        placeholder="Note" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <small class="fw-semibold" for="status">Others Amount</small>
                                    <input type="text" name="Others" value="<?php echo $Others_db; ?>"
                                        class="form-control">
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