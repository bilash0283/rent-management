<?php
// ইউজার আইডি এবং পে স্লিপ আইডি চেক
if (!isset($_GET['unit_id']) || !is_numeric($_GET['unit_id'])) {
    echo "<div class='alert alert-danger'>Invalid Unit ID</div>";
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-danger'>Invalid Slip ID</div>";
    exit;
}

$unit_id = $_GET['unit_id'];
$pay_slip_id = $_GET['id'];

// ১. ইউনিট এবং বিল্ডিং তথ্য সংগ্রহ
$unit_query = mysqli_query($db, "SELECT u.*, b.name as building_name_db 
                                 FROM unit u 
                                 JOIN building b ON u.building_name = b.id 
                                 WHERE u.id = '$unit_id'");
$unit_data = mysqli_fetch_assoc($unit_query);
$unit_name = $unit_data['unit_name'];
$building_name_db = $unit_data['building_name_db'];
$unit_type = $unit_data['unit_type'];

// ২. টেন্যান্ট তথ্য সংগ্রহ
$tent_sql = mysqli_query($db, "SELECT name FROM tenants WHERE role IN ('Tenant') AND unit_id = '$unit_id' LIMIT 1");
$tent_row = mysqli_fetch_assoc($tent_sql);
$tent_name = $tent_row['name'] ?? 'N/A';

// ৩. বর্তমান পেমেন্ট স্লিপ এবং ইনভয়েসের বিস্তারিত তথ্য (JOIN সহ)
$history_query = mysqli_query($db, "SELECT ph.*, inv.total_amount, inv.billing_month 
                                    FROM `payment_history` ph 
                                    JOIN `invoices` inv ON ph.invoice_id = inv.id 
                                    WHERE ph.id = '$pay_slip_id'");
$pay_history = mysqli_fetch_assoc($history_query);

if (!$pay_history) {
    echo "<div class='alert alert-danger'>Payment record not found!</div>";
    exit;
}

$invoice_id = $pay_history['invoice_id'];
$bill_month = $pay_history['billing_month'];
$total_bill_amount = (float)$pay_history['total_amount'];
$current_paid_entry = (float)$pay_history['paid_amount'];
$pay_method_his = $pay_history['payment_method'];
$pay_date_his = $pay_history['payment_date'];
$transaction_id_db = $pay_history['transaction_id'];
$transaction_number_db = $pay_history['transaction_number'];

// ৪. এই ইনভয়েসের জন্য এ পর্যন্ত মোট কত পেইড হয়েছে তা বের করা (Running Total)
$total_paid_query = mysqli_query($db, "SELECT SUM(paid_amount) as total_paid_till_now 
                                       FROM payment_history 
                                       WHERE invoice_id = '$invoice_id' 
                                       AND (payment_date < '{$pay_history['payment_date']}' 
                                       OR (payment_date = '{$pay_history['payment_date']}' AND id <= '$pay_slip_id'))");
$total_paid_row = mysqli_fetch_assoc($total_paid_query);
$calculated_total_paid = (float)$total_paid_row['total_paid_till_now'];
$calculated_due = $total_bill_amount - $calculated_total_paid;

// ৫. ওয়াটারমার্ক লজিক
$watermark_text = "";
$watermark_class = "";

if ($calculated_due <= 0) {
    $watermark_text = "PAID";
    $watermark_class = "watermark-paid";
} elseif ($calculated_total_paid > 0) {
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

    <div class="mb-3">
        <div id="pdf-content" class="payslip-wrapper bg-white shadow-lg mx-auto position-relative">
            
            <!-- Watermark -->
            <?php if (!empty($watermark_text)): ?>
                <div class="watermark-container <?= $watermark_class ?>">
                    <?= $watermark_text ?>
                </div>
            <?php endif; ?>

            <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4" style="position: relative; z-index: 2;">
                <div class="d-flex">
                    <div>
                        <img src="public/assets/images/logo-full.png" alt="logo" style="width:60px; height:60px; border-radius:50%; object-fit: cover;">
                    </div>
                    <div>
                        <h2 class="building-title text-info mb-1"><?= $building_name_db; ?></h2>
                        <p class="text-muted small mb-0 fw-600">PREMIUM HOUSING & PROPERTY MANAGEMENT</p>
                    </div>
                </div>
                <div class="text-end">
                    <h1 class="payslip-label mb-0">PAY SLIP</h1>
                    <p class="text-muted small">TRX ID: #PAY-<?= str_pad($pay_slip_id, 5, '0', STR_PAD_LEFT); ?></p>
                </div>
            </div>

            <div class="row mb-5" style="position: relative; z-index: 2;">
                <div class="col-6 border-end">
                    <p class="label-heading">TENANT INFORMATION</p>
                    <h5 class="fw-bold text-dark mb-1 text-uppercase"><?= $tent_name ?></h5>
                    <p class="mb-0 text-secondary"><?= $unit_type ?> : <strong><?= $unit_name ?></strong></p>
                    <p class="mb-0 text-secondary">Billing Month : <strong><?= date('F, Y', strtotime($bill_month)) ?></strong></p>
                    <p class="mb-0 text-secondary">Invoice No : <strong>#INV-<?= $invoice_id ?></strong></p>
                </div>
                <div class="col-6 ps-4">
                    <p class="label-heading text-end text-sm-start">PAYMENT DETAILS</p>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Method:</span>
                        <span class="fw-bold small text-uppercase"><?= $pay_method_his ?: 'N/A'; ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Transaction ID:</span>
                        <span class="fw-bold small"><?= $transaction_id_db ?: 'N/A'; ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted small">Transaction Number:</span>
                        <span class="fw-bold small"><?= $transaction_number_db ?: 'N/A'; ?></span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Payment Date:</span>
                        <span class="fw-bold small"><?= date("d M, Y", strtotime($pay_date_his)); ?></span>
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
                                <span class="fw-bold text-dark">Monthly Rent & Utilities</span><br>
                                <span class="text-muted small">Total amount due for the month of <?= date('M Y', strtotime($bill_month)) ?></span>
                            </td>
                            <td class="text-end py-4 fw-bold text-dark fs-5"><?= number_format($total_bill_amount, 0); ?> ৳</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="row justify-content-end" style="position: relative; z-index: 2;">
                <div class="col-md-5">
                    <div class="summary-box">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Current Paid :</span>
                            <span class="fw-bold text-success">+ <?= number_format($current_paid_entry, 0); ?> ৳</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 border-top pt-2">
                            <span class="text-muted">Total Paid :</span>
                            <span class="fw-bold text-info"><?= number_format($calculated_total_paid, 0); ?> ৳</span>
                        </div>
                        <?php if ($calculated_due > 0){ ?>
                            <div class="d-flex justify-content-between pt-2 border-top">
                                <span class="fw-800 text-muted">REMAINING DUE :</span>
                                <span class="fw-800 text-danger mb-0"><?= number_format($calculated_due, 0); ?> ৳</span>
                            </div>
                        <?php } else { ?>
                             <div class="d-flex justify-content-between pt-2 border-top text-success fw-bold">
                                <span>STATUS :</span>
                                <span>FULLY PAID</span>
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
                        <p class="text-info fw-bold" style="letter-spacing: 1px;">A/C: 1503101624157001</p>
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
                    <span class="d-block mt-1 small opacity-75">This is a computer-generated receipt.</span>
                </p>
            </div>
        </div>
    </div>
</div>

<style>
    #pdf-content {
        font-family: 'Inter', sans-serif;
        color: #2d3436;
        padding: 50px;
        max-width: 850px;
        background: white;
    }
    .building-title { font-weight: 800; color: #0984e3; font-size: 1.8rem; }
    .payslip-label { font-weight: 300; letter-spacing: 10px; color: #b2bec3; font-size: 1.5rem; }
    .label-heading { font-size: 11px; font-weight: 800; color: #636e72; letter-spacing: 1.5px; margin-bottom: 12px; }
    .table-clean thead { background-color: #f8f9fa; border-top: 2px solid #2d3436; }
    .table-clean thead th { font-size: 11px; font-weight: 800; letter-spacing: 1px; }
    .summary-box { background: #f8f9fa; padding: 20px; border-radius: 8px; }
    .bank-card { border: 1px dashed #dfe6e9; }
    .bank-title { font-size: 10px; font-weight: 800; color: #0984e3; }
    .account-number { font-family: 'Courier New', monospace; font-size: 1.1rem; font-weight: 700; }
    .watermark-container {
        position: absolute; top: 50%; left: 50%;
        transform: translate(-50%, -50%) rotate(-25deg);
        font-size: 120px; font-weight: 900;
        opacity: 0.1; z-index: 1; pointer-events: none;
        white-space: nowrap; border: 15px solid; padding: 10px 40px; border-radius: 20px;
    }
    .watermark-paid { color: #00b894; border-color: #00b894; }
    .watermark-partial { color: #fdcb6e; border-color: #fdcb6e; font-size: 90px; }
    .signature-line { border-top: 1.5px solid #2d3436; width: 100%; }
    .notice-text { font-size: 12px; font-weight: 600; color: #636e72; background: #fff5f5; padding: 8px 30px; border-radius: 30px; display: inline-block; }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
document.getElementById('generatePdfBtn').addEventListener('click', function () {
    const element = document.getElementById('pdf-content');
    const btn = this;
    btn.innerText = 'PROCESSING...';
    
    html2canvas(element, {
        scale: 3, 
        useCORS: true,
        backgroundColor: "#ffffff",
    }).then(canvas => {
        let link = document.createElement('a');
        link.download = 'Receipt_<?= $invoice_id ?>_<?= addslashes($tent_name) ?>.png';
        link.href = canvas.toDataURL('image/png');
        link.click();
        btn.innerHTML = '<i class="feather-icon icon-download me-2"></i> DOWNLOAD RECEIPT';
    });
});
</script>