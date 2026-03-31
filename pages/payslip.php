<?php
if (!isset($_GET['unit_id']) || !is_numeric($_GET['unit_id'])) {
    echo "<div class='alert alert-danger'>Invalid Unit ID</div>";
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-danger'>Invalid Invoice ID</div>";
    exit;
}

if (isset($_GET['unit_id'])) {
    $unit_id = $_GET['unit_id'];
}

if (isset($_GET['id'])) {
    $pay_slip_id = $_GET['id'];
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

// monthly payment sql 
$history_sql = mysqli_query($db, "SELECT * FROM `payment_history` WHERE `id` = '$pay_slip_id' ");

while ($pay_history = mysqli_fetch_assoc($history_sql)) {
    $pay_slip_id_db = $pay_history['id'];
    $bill_his = $pay_history['bill_month'];
    $pay_method_his = $pay_history['payment_method'];
    $total_his = $pay_history['total'];
    $paid_his = $pay_history['paid'];
    $due_his = $pay_history['due'];
    $note_his = $pay_history['note'];
    $pay_date_his = $pay_history['payment_date'];
    $paid_amount_his = $pay_history['paid_amount'];
    $manager_self = $pay_history['manager_self'];
    $expense = $pay_history['expense'];
    $expense_note = $pay_history['expense_note'];
    $transaction_id_db = $pay_history['transaction_id'];
}
?>

<div class="nxl-content">

    <!-- Page Header -->
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <h5 class="mb-0">Pay Slip</h5>
        <div class="text-end mb-3">
            <button id="generatePdfBtn" class="btn btn-success btn-sm pl-5">
                <i class="feather-icon icon-download me-2"></i> Download
            </button>
        </div>
        <!-- <a href="admin.php?page=tenant" class="btn btn-primary">
            <i class="feather-icon icon-arrow-left me-1"></i>Back
        </a> -->
    </div>

    <div class="mb-4">

        <div id="pdf-content" class="agreement-paper bg-white shadow-sm border mx-auto" style="max-width: 800px; padding: 60px;">
    
    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
        <div>
            <h3 class="fw-bold mb-0 text-primary text-uppercase">
                <?php echo $building_name_db ?? 'Building Name'; ?>
            </h3>
            <p class="text-muted small mb-0">Residential Property Management</p>
        </div>
        <div class="text-end">
            <h2 class="fw-bold text-dark mb-0">PAY SLIP</h2>
            <span class="badge bg-primary px-3 py-2 mt-1">
                BILL MONTH: <?= !empty($bill_his) ? strtoupper(date("M Y", strtotime($bill_his))) : 'N/A' ?>
            </span>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-8">
            <div class=" p-3 ">
                <h6 class="text-muted text-uppercase fw-bold small pb-2 mb-2">Tenant Information</h6>
                <div class="mb-1">Name: <span class="fw-bold text-dark text-uppercase"><?php echo $tent_name ?? 'N/A' ?></span></div>
                <div class="small text-muted">Unit: <span class="fw-semibold text-dark"><?php echo $unit_type . ' - ' . $unit_name ?? 'N/A' ?></span></div>
            </div>
        </div>
        <div class="col-4 text-end">
            <div class=" p-3 ">
                <h6 class="text-muted text-uppercase fw-bold small pb-2 mb-2 text-end">Payment Summary</h6>
                <div class="small mb-1">Method: <span class="fw-bold text-dark"><?php echo $pay_method_his ?? 'N/A'; ?></span></div>
                <div class="small mb-1">TXN ID: <span class="fw-bold text-dark text-break"><?php echo $transaction_id_db ?? 'N/A'; ?></span></div>
                <div class="small">Date: <span class="fw-bold text-dark"><?php echo !empty($pay_date_his) ? date("d M Y", strtotime($pay_date_his)) : 'N/A'; ?></span></div>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover border border-light align-middle">
            <thead class="table-dark">
                <tr>
                    <th class="ps-3 py-2 text-white">Description</th>
                    <th class="text-end pe-3 py-2 text-white">Amount (BDT)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="ps-3 fw-semibold">Total Bill Amount  = </td>
                    <td class="text-end pe-3 fw-bold text-dark"><?php echo number_format($total_his ?? '0', 2); ?> ৳</td>
                </tr>
                <tr>
                    <td class="ps-3 fw-semibold text-success">Paid Amount  = </td>
                    <td class="text-end pe-3 fw-bold text-success "><?php echo number_format($paid_his ?? '0', 2); ?> ৳</td>
                </tr>
                <tr class="table-light">
                    <td class="ps-3 fw-bold text-danger">Due Amount  = </td>
                    <td class="text-end pe-3 fw-bold text-danger"><?php echo number_format($due_his ?? '0', 2); ?> ৳</td>
                </tr>
                
            </tbody>
        </table>
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

    <div class="mt-4 pt-3 border-top text-center">
        <div class="alert alert-warning py-2 mb-0 border-0 rounded-pill">
            <small class="fw-bold">
                <i class="fas fa-exclamation-triangle me-1"></i> 
                সিড়িতে ও দরজার সামনে জুতা অথবা ময়লা রাখা সম্পূর্ণ নিষিদ্ধ।
            </small>
        </div>
    </div>
</div>

    </div>
</div><!-- nxl-content -->

<!-- pdf generate  -->
<!-- <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    document.getElementById('generatePdfBtn').addEventListener('click', function () {

        const element = document.getElementById('pdf-content');

        const options = {
            margin: 10,

            filename: 'Pay Slip <?= addslashes($tent_name ?? "Tenant") ?>.jpeg',

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
</script> -->

<!-- Image Generate  -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
document.getElementById('generatePdfBtn').addEventListener('click', function () {

    const element = document.getElementById('pdf-content');

    html2canvas(element, {
        scale: 3,          // high resolution
        useCORS: true
    }).then(canvas => {

        // 👉 PNG download
        let link = document.createElement('a');
        link.download = 'Pay Slip <?= addslashes($tent_name ?? "Tenant") ?>.png';
        link.href = canvas.toDataURL('image/png');
        link.click();

    });

});
</script>

<style>
    #pdf-content {
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

    .card {
        box-shadow: none !important;
    }

    .rounded {
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