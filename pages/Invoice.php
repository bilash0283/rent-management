<?php
if (!isset($_GET['unit_id']) || !is_numeric($_GET['unit_id'])) {
    echo "<div class='alert alert-danger'>Invalid tenant ID</div>";
    exit;
}

if (isset($_GET['unit_id'])) {
    $unit_id = $_GET['unit_id'];
}

$query = "SELECT * FROM unit wHERE id = '$unit_id'";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $unit_id = $row['id'];
    $unit_name = $row['unit_name'];
    $advance = $row['advance'];
    $rent = $row['rent'];
    $size = $row['size'];
    // $Gas = $row['Gas'];
    // $Water = $row['Water'];
    $building_name = $row['building_name'];
    $unit_type = $row['unit_type'];
}

$building = mysqli_query($db, "SELECT name FROM building WHERE id = '$building_name' ");
$building_row = mysqli_fetch_assoc($building);
$building_name_db = $building_row['name'];

$tent_sql = mysqli_query($db, "SELECT id,name FROM tenants WHERE building_id = '$building_name' AND unit_id = '$unit_id'");
while ($tent_row = mysqli_fetch_assoc($tent_sql)) {
    $tent_name = $tent_row['name'];
    $tent_id = $tent_row['id'];
}

// Advace Save SQL 
if (isset($_POST['advance_save'])) {
    $advance_pay_amount = $_POST['advance_amount'];

    $advance_add_sql = mysqli_query($db, "
                INSERT INTO `advance`
                (`tenant_id`, `unit_id`, `paid_amount`, `date`)
                VALUES ('$tent_id', '$unit_id', '$advance_pay_amount', NOW())
            ");

    if ($advance_add_sql) {
        header("Location: admin.php?page=editbill&unit_id=$unit_id");
        exit();
    }
}

// save_Invoice
if (isset($_POST['save_bill'])) {

    $billing_month = $_POST['billing_month'];
    $total_amount = intval($_POST['total_amount']);
    $paid_amount = intval($_POST['paid_amount']);
    $status = $_POST['status'];
    $note = $_POST['note'];
    $due_amount = $total_amount - $paid_amount;
    $payment_date = !empty($_POST['payment_date']) ? $_POST['payment_date'] : date('Y-m-d');
    $payment_method = $_POST['payment_method'];

    $month_sql = mysqli_query($db, "SELECT * FROM invoices WHERE billing_month = '$billing_month' AND tenant_id = '$tent_id' LIMIT 1 ");
    while ($ex_month_row = mysqli_fetch_assoc($month_sql)) {
        $id_db = $ex_month_row['id'];
        $old_total = intval($ex_month_row['total_amount']);
        $old_paid = intval($ex_month_row['paid_amount']);
    }
    $update_paid_amount = $old_paid + $paid_amount;
    $update_due_amount = $old_total - $update_paid_amount;

    if (mysqli_num_rows($month_sql) > 0) {
        $bill_sql = mysqli_query($db, "UPDATE invoices SET paid_amount= '$update_paid_amount', due_amount = '$update_due_amount', status='$status',note ='$note' WHERE id = '$id_db' AND tenant_id = '$tent_id' ");

        $bill_history = mysqli_query($db, "INSERT INTO payment_history(`tenant_id`, `bill_month`, `payment_method`, `total`, `paid`, `paid_amount`, `due`, `note`, `payment_date`) VALUES ('$tent_id','$billing_month','$payment_method','$old_total','$update_paid_amount','$paid_amount','$update_due_amount','$note','$payment_date')");
    } else {
        $bill_sql = mysqli_query($db, "INSERT INTO `invoices`
            (`tenant_id`, `unit_id`, `billing_month`, `total_amount`, `paid_amount`, `due_amount`, `status`, `created_at`) 
            VALUES 
            ('$tent_id','$unit_id','$billing_month','$total_amount','$paid_amount','$due_amount','$status',now())");

        $bill_history = mysqli_query($db, "INSERT INTO payment_history(`tenant_id`, `bill_month`, `payment_method`, `total`, `paid`, `paid_amount`, `due`, `note`, `payment_date`) VALUES ('$tent_id','$billing_month','$payment_method','$total_amount','$paid_amount','$paid_amount','$due_amount','$note','$payment_date')");
    }

    if ($bill_sql) {
        header("Location: admin.php?page=editbill&unit_id=$unit_id");
        exit();
    }
}

// monthly payment sql 
$pay_info = mysqli_query($db, "SELECT * FROM invoices WHERE tenant_id = '$tent_id' AND unit_id = '$unit_id' ORDER BY billing_month ");
while ($pay_info_sh = mysqli_fetch_assoc($pay_info)) {
    $billing_month_db = $pay_info_sh['billing_month'];
    $total_amount_db = $pay_info_sh['total_amount'];
    $paid_amount_db = $pay_info_sh['paid_amount'];
    $due_amount_db = $pay_info_sh['due_amount'];
    $status = $pay_info_sh['status'];
    $Gas_db = $pay_info_sh['Gas'];
    $Water_db = $pay_info_sh['Water'];
    $Electricity_db = $pay_info_sh['Electricity'];
    $Others_db = $pay_info_sh['Others'];

    $Gas_month_db = $pay_info_sh['Gas_month'];
    $Water_month_db = $pay_info_sh['Water_month'];
    $Electricity_month_db = $pay_info_sh['Electricity_month'];
    $Others_month_db = $pay_info_sh['Others_month'];
}

$advance_sql = mysqli_query($db, "SELECT * FROM `advance` WHERE tenant_id = '$tent_id' AND unit_id = '$unit_id'");
while ($advance_his = mysqli_fetch_assoc($advance_sql)) {
    $total_paid = $advance_his['paid_amount'];
}

if(!empty($total_paid)){
    $payable = max($advance - $total_paid, 0);
}else{
    $payable = $advance;
}
?>

<div class="nxl-content">

    <!-- Page Header -->
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <h5 class="mb-0">Monthly Invoice</h5>
        <div class="text-end mb-3">
            <button id="generatePdfBtn" class="btn btn-success btn-sm pl-5">
                <i class="feather-icon icon-download me-2"></i> Download PDF
            </button>
        </div>
        <!-- <a href="admin.php?page=tenant" class="btn btn-primary">
            <i class="feather-icon icon-arrow-left me-1"></i>Back
        </a> -->
    </div>

    <div class="mb-4">
        <div id="pdf-content" class="agreement-paper bg-white border" style="padding:90px;">
            <div class="border-bottom-0 pt-4 d-flex justify-content-between align-items-start border-bottom">
                <div>
                    <h3 class="fw-bold mb-1 text-uppercase">
                        <?php echo $building_name_db ?? 'Building Name'; ?>
                    </h3>
                </div>

                <div class="text-center">
                    <h5 class="fw-bold text-primary mb-1">INVOICE</h5>
                </div>

                <div class="text-end">
                    <small class="fw-semibold">Date : <?php echo date('d M Y'); ?></small>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-7">
                    <small class="text-muted d-block text-uppercase fw-semibold">
                        Tenant Name : <span class="fw-bold"><?php echo $tent_name ?? 'N/A' ?></span>
                    </small>
                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">
                        <?php echo $unit_type . ' : ' . $unit_name ?? 'N/A' ?>
                    </small>
                </div>
                <div class="col-5 text-end">
                    <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">Bill
                        Month
                    </small>
                    <span class="badge bg-light text-dark border fw-semibold">
                        <?= !empty($this_month) ? date("M Y", strtotime($this_month)) : '' ?>
                    </span>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-borderless align-middle mb-0" style="font-size: 0.85rem;">

                    <tbody>
                        <tr>
                            <td class="py-1">House Rent</td>
                            <td class="py-1 text-center">
                                <?= !empty($this_month) ? date("M Y", strtotime($this_month)) : '' ?>
                            </td>
                            <td class="py-1 text-end">৳
                                <?php echo number_format($rent, 2);
                                $total_bill = 0;
                                $total_bill += $rent;
                                ?>
                            </td>
                        </tr>

                        <?php if (!empty($Gas_db)) { ?>
                            <tr>
                                <td class="py-1">Gas Bill</td>
                                <td class="py-1 text-center">
                                    <?= !empty($Gas_month_db) ? date("M Y", strtotime($Gas_month_db)) : '' ?>
                                    <?php $total_bill += $Gas_db; ?>
                                </td>
                                <td class="py-1 text-end">৳
                                    <?php echo number_format($Gas_db, 2); ?>
                                </td>
                            </tr>
                        <?php } ?>

                        <?php if (!empty($Water_db)) { ?>
                            <tr>
                                <td class="py-1">Water Bill</td>
                                <td class="py-1 text-center">
                                    <?= $Water_month_db ?? '';
                                    $total_bill += $Water_db;
                                    ?>
                                </td>
                                <td class="py-1 text-end">৳
                                    <?php echo number_format($Water_db, 2); ?>
                                </td>
                            </tr>
                        <?php } ?>

                        <?php if (!empty($Electricity_db)) { ?>
                            <tr>
                                <td class="py-1">Electricity Bill <span class="text-warning"
                                        style="font-size:10px;">(<?= $size ?>)</span></td>
                                <td class="py-1 text-center">
                                    <?= $Electricity_month_db ?? '';
                                    $total_bill += $Electricity_db;
                                    ?>
                                </td>
                                <td class="py-1 text-end">৳
                                    <?php echo number_format($Electricity_db, 2); ?>
                                </td>
                            </tr>
                        <?php } ?>

                        <?php if (!empty($Others_db)) { ?>
                            <tr>
                                <td class="py-1">Others Bill</td>
                                <td class="py-1 text-center">
                                    <?= $Others_month_db ?? '';
                                    $total_bill += $Others_db;
                                    ?>
                                </td>
                                <td class="py-1 text-end">৳
                                    <?php echo number_format($Others_db, 2); ?>
                                </td>
                            </tr>
                        <?php } ?>

                    </tbody>

                    <tfoot class="border-top">
                        <tr class="table-light">
                            <td class="fw-bold py-2">Current Month Total = </td>
                            <td></td>
                            <td class="fw-bold py-2 text-end text-primary">৳
                                <?= number_format($total_bill, 2) ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div class="mt-3 p-2 bg-light rounded">
                <?php
                $stmt = $db->prepare("SELECT billing_month, due_amount FROM invoices WHERE tenant_id = ? AND unit_id = ? AND due_amount > 0 ORDER BY billing_month");
                $stmt->bind_param("ii", $tent_id, $unit_id);
                $stmt->execute();
                $stmt->bind_result($month, $due);
                $total_due = 0;
                while ($stmt->fetch()) {
                    $total_due += (float) $due;
                    echo '<div class="d-flex justify-content-between" style="font-size: 0.8rem;">';
                    echo '<span class="text-danger">Due (' . date("M Y", strtotime($month)) . ')</span>';
                    echo '<span class="text-danger fw-semibold">৳ ' . number_format($due, 2) . '</span>';
                    echo '</div>';
                }
                if ($payable > 0) {
                    $total_due += $payable;
                    echo '<div class="d-flex justify-content-between" style="font-size: 0.8rem;">';
                    echo '<span class="text-danger">Advance Due</span>';
                    echo '<span class="text-danger fw-semibold">৳ ' . number_format($payable, 2) . '</span>';
                    echo '</div>';
                }
                $stmt->close();
                ?>

                <?php if ($total_due > 0): ?>
                    <div class="d-flex justify-content-between border-top mt-1 pt-1">
                        <span class="small fw-bold">Total Previous Due = </span>
                        <span class="small fw-bold text-primary">৳
                            <?= number_format($total_due, 2) ?>
                        </span>
                    </div>
                <?php endif; ?>
            </div>

            <div
                class="d-flex justify-content-between align-items-center mt-3 p-3 bg-primary text-white rounded shadow-sm">
                <span class="h6 mb-0 text-white">Total Payable = </span>
                <span class="h5 mb-0 fw-bold text-white">৳
                    <?= number_format($total_due, 2) ?>
                </span>
            </div>

            <div class="mt-4 border-top">
                <p class="text-muted mt-3" style="font-size: 0.85rem;">
                    Please pay within <strong>7th
                        <?php echo date("M Y", strtotime($this_month)); ?></strong> to
                    following account &
                    WhatsApp your deposit slip to <strong>01715482363</strong>.
                </p>
                <div class="card  border-0 p-3">
                    <h6 class="mb-1 fw-bold">MD MUSTAFIZUR RAHMAN</h6>
                    <div class="text-primary fw-bold" style="letter-spacing: 1px;">A/C:
                        1503101624157001</div>
                    <small class="text-muted">BRACK BANK LTD | Moghbazar Branch</small>
                </div>
            </div>

            <div class="alert alert-warning mb-0 text-center" style="font-size: 0.8rem;">
                <i class="fas fa-exclamation-triangle me-1"></i> সিড়িতে ও দরজার সামনে জুতা,
                ময়লা রাখা সম্পূর্ণ নিষিদ্ধ।
            </div>
        </div><!-- #pdf-content -->
    </div>
</div><!-- nxl-content -->

<!-- pdf generate  -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
document.getElementById('generatePdfBtn').addEventListener('click', function () {

    const element = document.getElementById('pdf-content');

    const options = {
        margin: 10,

        filename: 'Invoice_<?= addslashes($tent_name ?? "Tenant") ?>.pdf',

        image: { 
            type: 'jpeg', 
            quality: 1
        },

        html2canvas: { 
            scale: 3,          // 🔥 resolution increase
            dpi: 300,          // 🔥 print quality
            letterRendering: true,
            useCORS: true
        },

        jsPDF: { 
            unit: 'mm', 
            format: 'a4', 
            orientation: 'portrait'
        },

        pagebreak: { 
            mode: ['avoid-all']
        }

    };

    html2pdf().set(options).from(element).save();

});
</script>

<style>

    #pdf-content{
        -webkit-font-smoothing: antialiased;
        text-rendering: optimizeLegibility;
    }

    .agreement-paper {
        width: 210mm;
        min-height: auto;
        margin: 0 auto;
        background: #fff;
        font-family: Arial, sans-serif;
        font-size: 12pt;
        line-height: 1.4;
        padding: 60px;
        box-shadow: none;
    }


    .section-title {
        border-bottom: 2px solid #444;
        padding-bottom: 6px;
        margin: 30px 0 15px;
        font-size: 15pt;
    }

    .terms-list {
        padding-left: 20px;
        margin-bottom: 25px;
    }

    .card{
        box-shadow: none !important;
    }

    .rounded{
        border-radius: 0 !important;
    }

    @media print {
        body * {
            visibility: hidden;
        }

        #pdf-content,
        #pdf-content * {
            visibility: visible;
        }

        #pdf-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            box-shadow: none;
        }
    }
</style>
<!-- pdf generate  -->