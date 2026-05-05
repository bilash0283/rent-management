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
    $tent_id = $tent_row['id'];
}

// ৪. বর্তমান ইনভয়েসের (যেটি স্ক্রিনে দেখা যাচ্ছে) তথ্য আনা
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

// ৫. অ্যাডভান্স বকেয়া হিসেব (Current Invoice এর সাথে সম্পর্ক নেই)
$total_advance_paid = 0;
$advance_sql = mysqli_query($db, "SELECT SUM(paid_amount) as total FROM `advance` WHERE tenant_id = '$tent_id' AND unit_id = '$unit_id'");
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
            <a href="admin.php?page=editbill&unit_id=<?= $unit_id ?>" class="btn btn-primary btn-sm">
                <i class="feather-icon icon-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <div class="mb-4">
        <div id="pdf-content" class="agreement-paper bg-white border" style="padding:80px;">
            <div class="card shadow-sm border-0">
                <div class="pt-4 px-4 d-flex justify-content-between align-items-start border-bottom pb-3">
                    <div>
                        <h4 class="fw-bold mb-1 text-uppercase"><?= $building_name_db ?? 'Building Name' ?></h4>
                    </div>
                    <div>
                        <h5 class="fw-bold text-primary mb-1">INVOICE</h5>
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

                    <!-- বর্তমান মাসের বিলের টেবিল -->
                    <div class="table-responsive">
                        <table class="table table-sm table-striped table-borderless align-middle mb-0" style="font-size: 0.85rem;">
                            <tbody>
                                <?php 
                                $current_month_total = 0;
                                $bill_items = [
                                    ['Rent', $Rent_db, $billing_month_db, 'House Rent'],
                                    ['Gas', $Gas_db, $Gas_month_db, 'Gas Bill'],
                                    ['Water', $Water_db, $Water_month_db, 'Water Bill'],
                                    ['Electricity', $Electricity_db, $Electricity_month_db, "Electricity Bill ($size)"],
                                    ['Others', $Others_db, $Others_month_db, 'Others Bill']
                                ];

                                foreach ($bill_items as $item) {
                                    if (!empty($item[1])) {
                                        $current_month_total += $item[1];
                                        echo "<tr>
                                                <td class='py-1'>{$item[3]}</td>
                                                <td class='py-1 text-center'>" . (!empty($item[2]) ? date('M Y', strtotime($item[2])) : '') . "</td>
                                                <td class='py-1 text-end'>৳ " . number_format($item[1], 0) . "</td>
                                              </tr>";
                                    }
                                }
                                ?>
                            </tbody>
                            <tfoot class="border-top">
                                <tr class="table-light">
                                    <td class="fw-bold py-2 text-primary">Current Month Total = </td>
                                    <td></td>
                                    <td class="fw-bold py-2 text-end text-primary">৳ <?= number_format($current_month_total, 0) ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- বকেয়া সেকশন (ID != current invoice id ব্যবহার করা হয়েছে যাতে ডাবল না আসে) -->
                    <div id="due-section" style="display: none;">
                        <div class="mt-3 p-2 bg-light rounded border-start border-danger border-3">
                            <small class="fw-bold text-muted d-block mb-1">Previous Dues:</small>
                            <?php
                            $total_old_due = 0;
                            // এখানে ID != ? ব্যবহার করা হয়েছে যাতে বর্তমান ইনভয়েসটি বকেয়া হিসেবে না আসে
                            $stmt = $db->prepare("SELECT id, billing_month, total_amount, paid_amount FROM invoices WHERE tenant_id = ? AND unit_id = ? AND id != ? ORDER BY billing_month ASC");
                            $stmt->bind_param("iii", $tent_id, $unit_id, $invoice_id);
                            $stmt->execute();
                            $stmt->bind_result($old_inv_id, $old_month, $old_total, $old_paid);

                            while ($stmt->fetch()) {
                                $due = (float) $old_total - (float) $old_paid;
                                if ($due > 0) {
                                    $total_old_due += $due;
                                    echo '<div class="d-flex justify-content-between mb-1" style="font-size: 0.8rem;">';
                                    echo '<span class="text-danger"> Due (' . date("M Y", strtotime($old_month)) . ') <small class="text-primary">#INV-' . $old_inv_id . '</small></span>';
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

                    <!-- মোট প্রদেয় বিল -->
                    <div class="d-flex justify-content-between align-items-center mt-3 p-3 bg-primary text-white rounded shadow-sm">
                        <span class="h6 mb-0 text-white">Total Payable Amount = </span>
                        <span class="h5 mb-0 fw-bold text-white" id="finalPayableDisplay">
                            ৳ <?= number_format($current_month_total, 0) ?>
                        </span>
                    </div>

                    <!-- পেমেন্ট গেটওয়ে ও সতর্কবার্তা -->
                    <div class="mt-4 border-top">
                        <p class="text-muted mt-2" style="font-size: 0.85rem;">
                            Please complete the payment within <strong>7 days</strong> and share your deposit slip via WhatsApp at <strong>01715482363</strong>.
                        </p>
                        <div class="card border-0 p-3 bg-light rounded-3">
                            <h6 class="mb-1 fw-bold">MD MUSTAFIZUR RAHMAN</h6>
                            <div class="text-primary fw-bold" style="letter-spacing: 1px;">A/C: 1503101624157001</div>
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
    // PHP থেকে ভ্যালুগুলো নিয়ে আসা
    const currentMonthTotal = <?= (float)$current_month_total ?>;
    const previousDueTotal = <?= (float)$total_old_due ?>;
    
    const includeDueBtn = document.getElementById('includeDueBtn');
    const dueSection = document.getElementById('due-section');
    const finalDisplay = document.getElementById('finalPayableDisplay');

    // চেকবক্স হ্যান্ডলার
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

    // ডাউনলোড ইমেজ (PNG)
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
    .card { border: none !important; }
    #pdf-content { color: #000; }
    @media print {
        body * { visibility: hidden; }
        #pdf-content, #pdf-content * { visibility: visible; }
        #pdf-content { position: absolute; left: 0; top: 0; width: 100%; }
    }
</style>