<?php
if (!isset($_GET['unit_id']) || !is_numeric($_GET['unit_id'])) {
    echo "<div class='alert alert-danger'>Invalid Unit ID</div>";
    exit;
}

if (!isset($_GET['invoice_id']) || !is_numeric($_GET['invoice_id'])) {
    echo "<div class='alert alert-danger'>Invalid Invoice ID</div>";
    exit;
}

$unit_id = $_GET['unit_id'];
$invoice_id = $_GET['invoice_id'];

// ১. ইউনিটের তথ্য আনা
$query = "SELECT * FROM unit WHERE id = '$unit_id'";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $unit_name = $row['unit_name'];
    $advance = $row['advance'];
    $size = $row['size'];
    $building_name = $row['building_name'];
    $unit_type = $row['unit_type'];
}

// ২. বিল্ডিং এর নাম আনা
$building = mysqli_query($db, "SELECT name FROM building WHERE id = '$building_name' ");
$building_row = mysqli_fetch_assoc($building);
$building_name_db = $building_row['name'];

// ৩. টেন্যান্ট এর তথ্য আনা
$tent_sql = mysqli_query($db, "SELECT id,name FROM tenants WHERE building_id = '$building_name' AND unit_id = '$unit_id'");
while ($tent_row = mysqli_fetch_assoc($tent_sql)) {
    $tent_name = $tent_row['name'];
    $tenant_id = $tent_row['id'];
}

// ৪. ইনভয়েসের তথ্য আনা
$pay_info = mysqli_query($db, "SELECT * FROM invoices WHERE id = '$invoice_id'");
while ($pay_info_sh = mysqli_fetch_assoc($pay_info)) {
    $billing_month_db = $pay_info_sh['billing_month'];
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

// ৫. অ্যাডভান্স বকেয়া হিসেব
$total_advance_paid = 0;
$advance_sql = mysqli_query($db, "SELECT SUM(paid_amount) as total FROM `advance` WHERE tenant_id = '$tenant_id' AND unit_id = '$unit_id'");
$advance_res = mysqli_fetch_assoc($advance_sql);
$total_advance_paid = $advance_res['total'] ?? 0;
$payable_advance = max($advance - $total_advance_paid, 0);
?>

<div class="nxl-content">
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <h5 class="mb-0">Monthly Invoice</h5>
        <div class="d-flex align-items-center gap-3">
            <div class="form-check form-switch mb-0">
                <input class="form-check-input" type="checkbox" id="includeDueBtn">
                <label class="form-check-label fw-bold" for="includeDueBtn" style="cursor:pointer;">Include Previous Due</label>
            </div>
            <button id="generatePdfBtn" class="btn btn-success btn-sm">
                <i class="feather-icon icon-download me-2"></i> Download
            </button>
            <a href="admin.php?page=editbill&tenant_id=<?= $tenant_id ?>" class="btn btn-info btn-sm">
                <i class="feather-icon icon-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <div class="mb-4">
        <div id="pdf-content" class="agreement-paper bg-white border position-relative" style="padding:80px;">
            <!-- লোগো ওয়াটারমার্ক (Watermark) -->
            <div class="invoice-watermark">
                <img src="public/assets/images/logo-full.png" alt="watermark">
            </div>

            <div class="card shadow-sm border-0 bg-transparent position-relative" style="z-index: 1;">
                <div class="pt-4 px-4 d-flex justify-content-between align-items-start border-bottom pb-3">
                    <!-- টপ লেফট কোণায় ছোট রাউন্ডেড লোগো -->
                    <div class="d-flex align-items-center gap-2">
                        <img src="public/assets/images/logo-full.png" alt="logo" style="width:60px; height:60px; border-radius:50%; object-fit: cover;">
                        <h4 class="fw-bold mb-1 text-info text-uppercase m-0"><?= $building_name_db ?? 'Building Name' ?></h4>
                    </div>
                    <div>
                        <h5 class="fw-bold text-info mb-1">INVOICE</h5>
                        <small>ID : #INV-<?= $invoice_id ?></small>
                    </div>
                    <div class="text-end">
                        <small class="fw-semibold">Date : <?= (!empty($created_at_db)) ? date("d M Y", strtotime($created_at_db)) : date("d M Y") ?></small>
                    </div>
                </div>

                <div class="card-body px-4">
                    <div class="row mb-3 mt-2">
                        <div class="col-7">
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">Tenant Name : <strong class="text-black"><?= $tent_name ?? 'N/A' ?></strong></small>
                            <small class="text-muted"><?= $unit_type ?? 'Unit ' ?> : <strong class="text-black"><?= $unit_name ?? 'N/A' ?></strong></small>
                        </div>
                        <div class="col-5 text-end">
                            <small class="text-muted d-block text-uppercase fw-semibold" style="font-size: 0.7rem;">Bill Month</small>
                            <span class="badge bg-light text-dark border fw-semibold">
                                <?= !empty($billing_month_db) ? date("M Y", strtotime($billing_month_db)) : date("M Y") ?>
                            </span>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-borderless align-middle mb-0" style="font-size: 0.85rem;">
                            <thead>
                                <tr class="border-bottom">
                                    <th class="py-2">Description</th>
                                    <th class="py-2 text-center">Month</th>
                                    <th class="py-2 text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $current_month_total = 0;
                                
                                $bill_items = [
                                    ['House Rent', $Rent_db, $billing_month_db],
                                    ['Gas Bill', $Gas_db, $Gas_month_db],
                                    ['Water Bill', $Water_db, $Water_month_db],
                                    ['Electricity Bill (' . $size . ')', $Electricity_db, $Electricity_month_db],
                                    ['Others Bill', $Others_db, $Others_month_db]
                                ];

                                foreach ($bill_items as $item) {
                                    $label = $item[0];
                                    $amount = $item[1];
                                    $month_or_desc = $item[2]; 

                                    if (!empty($amount) && $amount > 0) {
                                        $current_month_total += $amount;
                                        
                                        if (!empty($month_or_desc)) {
                                            $timestamp = strtotime($month_or_desc);
                                            if ($timestamp && (date('Y-m-d', $timestamp) === $month_or_desc || date('Y-m', $timestamp) === $month_or_desc || strlen($month_or_desc) <= 10)) {
                                                $display_text = date('M Y', $timestamp);
                                            } else {
                                                $display_text = htmlspecialchars($month_or_desc);
                                            }
                                        } else {
                                            $display_text = '';
                                        }

                                        echo "<tr>
                                                <td class='py-1'>$label</td>
                                                <td class='py-1 text-center'>$display_text</td>
                                                <td class='py-1 text-end'>৳ " . number_format($amount, 0) . "</td>
                                            </tr>";
                                    }
                                }
                                ?>
                            </tbody>
                            <tfoot class="border-top">
                                <tr class="table-light">
                                    <td class="fw-bold py-2 text-info">Current Month Total = </td>
                                    <td></td>
                                    <td class="fw-bold py-2 text-end text-info">৳ <?= number_format($current_month_total, 0) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <div id="due-section" style="display: none;">
                        <div class="mt-3 p-2 bg-light rounded border-start border-danger border-3">
                            <small class="fw-bold text-muted d-block mb-1">Previous Dues:</small>
                            <?php
                            $total_old_due = 0;
                            $stmt = $db->prepare("SELECT id, billing_month, total_amount, paid_amount FROM invoices WHERE tenant_id = ? AND unit_id = ? AND id != ? ORDER BY billing_month ASC");
                            $stmt->bind_param("iii", $tenant_id, $unit_id, $invoice_id);
                            $stmt->execute();
                            $stmt->bind_result($old_inv_id, $old_month, $old_total, $old_paid);

                            while ($stmt->fetch()) {
                                $due = (float) $old_total - (float) $old_paid;
                                if ($due > 0) {
                                    $total_old_due += $due;
                                    echo '<div class="d-flex justify-content-between mb-1" style="font-size: 0.8rem;">';
                                    echo '<span class="text-danger"> Due (' . date("M Y", strtotime($old_month)) . ') <small class="text-info">#INV-' . $old_inv_id . '</small></span>';
                                    echo '<span class="text-danger fw-semibold">৳ ' . number_format($due, 0) . '</span>';
                                    echo '</div>';
                                }
                            }
                            if ($payable_advance > 0) {
                                $total_old_due += $payable_advance;
                                echo '<div class="d-flex justify-content-between" style="font-size: 0.8rem;">';
                                echo '<span class="text-danger">Advance Due</span>';
                                echo '<span class="text-danger fw-semibold">৳ ' . number_format($payable_advance, 0) . '</span>';
                                echo '</div>';
                            }
                            $stmt->close();
                            ?>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-3 p-3 bg-info text-white rounded shadow-sm">
                        <span class="h6 mb-0 text-white">Total Payable Amount = </span>
                        <span class="h5 mb-0 fw-bold text-white" id="finalPayableDisplay">
                            ৳ <?= number_format($current_month_total, 0) ?>
                        </span>
                    </div>

                    <div class="mt-4 border-top">
                        <p class="text-muted mt-2" style="font-size: 0.85rem;">
                            Please complete the payment within <strong>7 days</strong> and share your deposit slip via WhatsApp at <strong>01715482363</strong>.
                        </p>
                        <div class="card border-0 p-3 bg-light rounded-3">
                            <p class="text-primary fw-bold mb-1">BANK TRANSFER DETAILS</p>
                            <h6 class="mb-1 fw-bold">MD MUSTAFIZUR RAHMAN</h6>
                            <div class="text-info fw-bold" style="letter-spacing: 1px;">A/C: 1503101624157001</div>
                            <small class="text-muted text-uppercase">BRAC BANK LTD | Moghbazar Branch</small>
                        </div>
                    </div>

                    <div class="alert alert-warning mb-0 text-center mt-3" style="font-size: 0.8rem; border-radius: 8px;">
                        <i class="fas fa-exclamation-triangle me-1"></i> সিড়িতে ও দরজার সামনে জুতা, ময়লা রাখা সম্পূর্ণ নিষিদ্ধ।
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script>
    const currentMonthTotal = <?= (float)$current_month_total ?>;
    const previousDueTotal = <?= (float)$total_old_due ?>;
    
    const includeDueBtn = document.getElementById('includeDueBtn');
    const dueSection = document.getElementById('due-section');
    const finalDisplay = document.getElementById('finalPayableDisplay');

    includeDueBtn.addEventListener('change', function() {
        if (this.checked) {
            dueSection.style.display = 'block';
            let grandTotal = currentMonthTotal + previousDueTotal;
            finalDisplay.innerText = '৳ ' + grandTotal.toLocaleString('en-IN');
        } else {
            dueSection.style.display = 'none';
            finalDisplay.innerText = '৳ ' + currentMonthTotal.toLocaleString('en-IN');
        }
    });

    document.getElementById('generatePdfBtn').addEventListener('click', function () {
        const element = document.getElementById('pdf-content');
        html2canvas(element, {
            scale: 3,
            useCORS: true,
            backgroundColor: "#ffffff"
        }).then(canvas => {
            let link = document.createElement('a');
            link.download = 'Invoice_<?= addslashes($tent_name) ?>_<?= $invoice_id ?>.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        });
    });
</script>

<style>
    .agreement-paper {
        width: 210mm;
        margin: 0 auto;
        background: #fff;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    .form-switch .form-check-input { width: 2.5em; height: 1.25em; cursor: pointer; }
    #pdf-content { color: #000; }
    
    /* ওয়াটারমার্ক স্টাইল */
    .invoice-watermark {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) rotate(-15deg);
        opacity: 0.04; /* খুব হালকা ওয়াটারমার্ক লুক দেওয়ার জন্য */
        pointer-events: none;
        user-select: none;
        width: 400px;
        height: 400px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .invoice-watermark img {
        width: 100%;
        height: 100%;
        object-fit: contain;
    }

    @media print {
        body * { visibility: hidden; }
        #pdf-content, #pdf-content * { visibility: visible; }
        #pdf-content { position: absolute; left: 0; top: 0; width: 100%; }
    }
</style>