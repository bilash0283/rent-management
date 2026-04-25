<?php
// Functionality and variables remain exactly the same as your request
if (!isset($_GET['unit_id']) || !is_numeric($_GET['unit_id'])) {
    echo "<div class='alert alert-danger'>Invalid Unit ID</div>";
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-danger'>Invalid Invoice ID</div>";
    exit;
}

$unit_id = $_GET['unit_id'];
$pay_slip_id = $_GET['id'];

// Database queries
$query = "SELECT * FROM unit WHERE id = '$unit_id'";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $unit_name = $row['unit_name'];
    $building_name = $row['building_name'];
    $unit_type = $row['unit_type'];
}

$building = mysqli_query($db, "SELECT name FROM building WHERE id = '$building_name' ");
$building_row = mysqli_fetch_assoc($building);
$building_name_db = $building_row['name'];

$tent_sql = mysqli_query($db, "SELECT id,name FROM tenants WHERE building_id = '$building_name' AND unit_id = '$unit_id'");
while ($tent_row = mysqli_fetch_assoc($tent_sql)) {
    $tent_name = $tent_row['name'];
}

$history_sql = mysqli_query($db, "SELECT * FROM `payment_history` WHERE `id` = '$pay_slip_id' ");
while ($pay_history = mysqli_fetch_assoc($history_sql)) {
    $pay_slip_id_db = $pay_history['id'];
    $bill_his = $pay_history['bill_month'];
    $pay_method_his = $pay_history['payment_method'];
    $total_his = $pay_history['total'];
    $paid_his = $pay_history['paid'];
    $due_his = $pay_history['due'];
    $pay_date_his = $pay_history['payment_date'];
    $paid_amount_his = $pay_history['paid_amount'];
    $transaction_id_db = $pay_history['transaction_id'];
}

// Logic for Watermark Text and Class
$watermark_text = "";
$watermark_class = "";

if ($due_his <= 0 && $paid_his > 0) {
    $watermark_text = "PAID";
    $watermark_class = "watermark-paid";
} elseif ($paid_his > 0 && $due_his > 0) {
    $watermark_text = "PARTIAL";
    $watermark_class = "watermark-partial";
}
?>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800&display=swap" rel="stylesheet">

<div class="nxl-content">
    <div class="page-header d-flex align-items-center justify-content-between mb-4 px-4 mt-3">
        <h5 class="mb-0 fw-bold text-secondary">RENTAL MANAGEMENT SYSTEM</h5>
        <button id="generatePdfBtn" class="btn btn-dark shadow-sm px-4 rounded-pill">
            <i class="feather-icon icon-download me-2"></i> DOWNLOAD RECEIPT
        </button>
    </div>

    <div class="mb-5 pb-5">
        <div id="pdf-content" class="payslip-wrapper bg-white shadow-lg mx-auto position-relative">
            
            <?php if (!empty($watermark_text)): ?>
                <div class="watermark-container <?= $watermark_class ?>">
                    <?= $watermark_text ?>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4" style="position: relative; z-index: 2;">
                <div>
                    <h2 class="building-title mb-1"><?php echo $building_name_db ?? 'BUILDING NAME'; ?></h2>
                    <p class="text-muted small mb-0 fw-600">PREMIUM HOUSING & PROPERTY MANAGEMENT</p>
                </div>
                <div class="text-end">
                    <h1 class="payslip-label mb-0">PAY SLIP</h1>
                    <p class="text-muted small">ID: #INV-<?php echo str_pad($pay_slip_id_db, 5, '0', STR_PAD_LEFT); ?></p>
                </div>
            </div>

            <div class="row mb-5" style="position: relative; z-index: 2;">
                <div class="col-6 border-end">
                    <p class="label-heading">TENANT INFORMATION</p>
                    <h5 class="fw-bold text-dark mb-1 text-uppercase"><?php echo $tent_name ?? 'N/A' ?></h5>
                    <p class="mb-0 text-secondary"><?php echo $unit_type ?? 'Unit ' ?> : <strong><?php echo $unit_name ?? '' ?></strong></p>
                    <p class="mb-0 text-secondary">Month : <strong><?= !empty($bill_his) ? date("F, Y", strtotime($bill_his)) : 'N/A' ?></strong></p>
                </div>
                <div class="col-6 ps-4">
                    <p class="label-heading text-end text-sm-start">PAYMENT SUMMARY</p>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Method:</span>
                        <span class="fw-bold small text-uppercase"><?php echo $pay_method_his ?? 'N/A'; ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Transaction ID:</span>
                        <span class="fw-bold small"><?php echo $transaction_id_db ?? 'N/A'; ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Payment Date:</span>
                        <span class="fw-bold small"><?php echo !empty($pay_date_his) ? date("d M, Y", strtotime($pay_date_his)) : 'N/A'; ?></span>
                    </div>
                </div>
            </div>

            <div class="table-responsive mb-4" style="position: relative; z-index: 2;">
                <table class="table table-clean">
                    <thead>
                        <tr>
                            <th class="py-3">DESCRIPTION</th>
                            <th class="text-end py-3">AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="py-4">
                                <span class="fw-bold text-dark">Total Rent & Utility Charges</span><br>
                                <span class="text-muted small">Standard monthly billing for the mentioned period.</span>
                            </td>
                            <td class="text-end py-4 fw-bold text-dark fs-5"><?php echo number_format($total_his ?? '0', 0); ?> ৳</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="row justify-content-end" style="position: relative; z-index: 2;">
                <div class="col-md-5">
                    <div class="summary-box">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Paid Amount :</span>
                            <span class="fw-bold text-success"><?php echo number_format($paid_his ?? '0', 0); ?> ৳</span>
                        </div>
                        <?php if ($due_his > 0){ ?>
                            <div class="d-flex justify-content-between pt-2 border-top">
                                <span class="fw-800 text-muted">NET DUE :</span>
                                <span class="fw-800 text-danger mb-0"><?php echo number_format($due_his ?? '0', 0); ?> ৳</span>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="row mt-5 pt-4" style="position: relative; z-index: 2;">
                <div class="col-7">
                    <div class="bank-card p-3 rounded-3">
                        <p class="bank-title mb-1">BANK TRANSFER DETAILS</p>
                        <h6 class="fw-bold mb-0">MD MUSTAFIZUR RAHMAN</h6>
                        <p class="account-number my-1">1503101624157001</p>
                        <p class="small text-muted mb-0">BRAC BANK LTD | Moghbazar Branch</p>
                    </div>
                </div>
                <div class="col-5 text-end d-flex align-items-end justify-content-end">
                    <div class="text-center w-75">
                        <div class="signature-line mb-2"></div>
                        <p class="fw-bold small mb-0 text-uppercase">Management</p>
                    </div>
                </div>
            </div>

            <div class="mt-5 pt-4 text-center" style="position: relative; z-index: 2;">
                <p class="notice-text">
                    <i class="fas fa-info-circle me-1"></i> 
                    সিড়িতে ও দরজার সামনে জুতা অথবা ময়লা রাখা সম্পূর্ণ নিষিদ্ধ। 
                    <span class="d-block mt-1 small opacity-75">Thank you for choosing our management service.</span>
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    /* RESET & CORE FONTS */
    #pdf-content {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        color: #2d3436;
        padding: 60px;
        max-width: 850px;
        border-radius: 4px;
        line-height: 1.6;
        background: white;
    }

    .fw-600 { font-weight: 600; }
    .fw-800 { font-weight: 800; }

    /* STYLING ELEMENTS */
    .building-title { font-weight: 800; color: #0984e3; letter-spacing: -0.5px; font-size: 1.8rem; }
    .payslip-label { font-weight: 300; letter-spacing: 10px; color: #b2bec3; font-size: 1.5rem; }
    
    .label-heading { 
        font-size: 11px; 
        font-weight: 800; 
        color: #636e72; 
        letter-spacing: 1.5px; 
        margin-bottom: 12px;
    }

    /* TABLE DESIGN */
    .table-clean thead { background-color: #f8f9fa; border-top: 2px solid #2d3436; }
    .table-clean thead th { font-size: 11px; font-weight: 800; color: #2d3436; border: none; letter-spacing: 1px; }
    .table-clean tbody td { border-bottom: 1px solid #f1f2f6; }

    .summary-box { background: #f8f9fa; padding: 20px; border-radius: 8px; }

    /* BANK CARD */
    .bank-card { background: #ffffff; border: 1px dashed #dfe6e9; }
    .bank-title { font-size: 10px; font-weight: 800; color: #0984e3; letter-spacing: 1px; }
    .account-number { font-family: 'Courier New', monospace; font-size: 1.1rem; font-weight: 700; color: #2d3436; }

    /* CENTERED WATERMARK SYSTEM */
    .watermark-container {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-25deg);
        font-size: 130px;
        font-weight: 900;
        padding: 20px 60px;
        border-radius: 25px;
        opacity: 0.08; /* Low opacity for background feel */
        z-index: 1;
        pointer-events: none;
        user-select: none;
        white-space: nowrap;
        text-align: center;
    }

    .watermark-paid {
        border: 15px solid #00b894;
        color: #00b894;
    }

    .watermark-partial {
        border: 15px solid #fdcb6e;
        color: #fdcb6e;
        font-size: 100px; /* Smaller font for longer word */
    }

    .signature-line { border-top: 1.5px solid #2d3436; width: 100%; }
    .notice-text { font-size: 12px; font-weight: 600; color: #636e72; padding: 10px; background: #fff5f5; border-radius: 30px; display: inline-block; padding: 8px 30px; }

    .payslip-wrapper { overflow: hidden; background: white; }

    @media print {
        #generatePdfBtn { display: none; }
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
document.getElementById('generatePdfBtn').addEventListener('click', function () {
    const element = document.getElementById('pdf-content');
    const btn = this;
    btn.innerText = 'PROCESSING...';
    
    html2canvas(element, {
        scale: 4, 
        useCORS: true,
        backgroundColor: "#ffffff",
        letterRendering: true,
        logging: false
    }).then(canvas => {
        let link = document.createElement('a');
        link.download = 'Pay_Slip_<?= addslashes($tent_name ?? "Invoice") ?>.png';
        link.href = canvas.toDataURL('image/png', 1.0);
        link.click();
        btn.innerHTML = '<i class="feather-icon icon-download me-2"></i> DOWNLOAD RECEIPT';
    });
});
</script>