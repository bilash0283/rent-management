<?php
if (empty($_GET['invoice_id']) || $_GET['invoice_id'] == '' || $_GET['invoice_id'] == null) {
    echo "Invoice Id not Found !";
}
$unit_id = $_GET['unit_id'];
$invoice_id = $_GET['invoice_id'];

// invoice show from db 
$pay_info = mysqli_query($db, "SELECT * FROM invoices WHERE id='$invoice_id' ");
while ($pay_info_sh = mysqli_fetch_assoc($pay_info)) {
    $invoice_id    = $pay_info_sh['id'];
    $billing_month_db = $pay_info_sh['billing_month'];
    $total_amount_db = $pay_info_sh['total_amount'];
    $paid_amount_db = $pay_info_sh['paid_amount'];
    $due_amount_db = $pay_info_sh['due_amount'];
    $status = $pay_info_sh['status'];
    $Rent_db = $pay_info_sh['Rent'];
    $Gas_db = $pay_info_sh['Gas'];
    $Water_db = $pay_info_sh['Water'];
    $Electricity_db = $pay_info_sh['Electricity'];
    $Others_db = $pay_info_sh['Others'];

    $Gas_month_db = $pay_info_sh['Gas_month'];
    $Water_month_db = $pay_info_sh['Water_month'];
    $Electricity_month_db = $pay_info_sh['Electricity_month'];
    $Others_month_db = $pay_info_sh['Others_month'];
    $created_at_db = $pay_info_sh['created_at'];
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
                                    <input type="month" name="rent_month" value="<?php echo $this_month; ?>"
                                        class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <small class="fw-semibold" for="status">Rent Amount</small>
                                    <input type="text" name="rent" value="<?= $rent ?? '' ?>" class="form-control">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <small class="fw-semibold">Gas </small>
                                    <input type="month" name="Gas_month"
                                        value="<?php echo date('Y-m', strtotime('first day of last month')); ?>"
                                        class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <small class="fw-semibold" for="status">Gas Amount</small>
                                    <input type="text" name="Gas" value="<?= $gas ?? '' ?>" class="form-control">
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <small class="fw-semibold">Water </small>
                                    <input type="text" name="Water_month"
                                        value="<?php echo date('M Y', strtotime('first day of this month -4 months')); ?>"
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
                                        value="<?php echo date('M Y', strtotime('first day of this month -2 months')); ?>"
                                        placeholder="Note" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <small class="fw-semibold" for="status">Electricity Amount</small>
                                    <input type="text" name="Electricity" value="" class="form-control">
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-6">
                                    <small class="fw-semibold">Others </small>
                                    <input type="text" name="Others_month" placeholder="Note" class="form-control">
                                </div>
                                <div class="col-md-6">
                                    <small class="fw-semibold" for="status">Others Amount</small>
                                    <input type="text" name="Others" value="" class="form-control">
                                </div>
                            </div>

                            <button type="submit" name="create_invoice" class="btn btn-success btn-sm mt-3">
                                Update Invoice
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>