<?php
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'Tenant') {
    header('Location: admin.php?page=dashboard');
    exit();
}
$type = $_GET['type'];



if (isset($_SESSION['id'])) {
    $tenant_id = $_SESSION['id'];

    $tent_sqls = mysqli_query($db, "SELECT * FROM tenants WHERE id = '$tenant_id' ");
    while ($tent_rows = mysqli_fetch_assoc($tent_sqls)) {
        $unit_id = $tent_rows['unit_id'];
        $tent_name = $tent_rows['name'];
    }
}

// unit info 
$query = "SELECT * FROM unit wHERE id = '$unit_id'";
$result = mysqli_query($db, $query);
while ($row = mysqli_fetch_assoc($result)) {
    $unit_id = $row['id'];
    $unit_name = $row['unit_name'];
    $advance = $row['advance'];
    $size = $row['size'];
    $rent = $row['rent'];
    $water = $row['water'];
    $gas = $row['gas'];
    $building_name = $row['building_name'];
    $unit_type = $row['unit_type'];
    $Electricity_meter_no = $row['size'];
}

// building info 
$building = mysqli_query($db, "SELECT name FROM building WHERE id = '$building_name' ");
$building_row = mysqli_fetch_assoc($building);
$building_name_db = $building_row['name'];

// Confirm Payment Logic
if (isset($_POST['save_bill'])) {
    $invoice_id = mysqli_real_escape_string($db, $_POST['invoice_id']);
    $paid_amount = (int) $_POST['paid_amount'];
    $payment_method = mysqli_real_escape_string($db, $_POST['payment_method']);
    $note = mysqli_real_escape_string($db, $_POST['note']);
    $transaction_id = mysqli_real_escape_string($db, $_POST['transaction_id'] ?? '');
    $transaction_number = mysqli_real_escape_string($db, $_POST['transaction_number'] ?? '');

    // billing_month query 
    $bill_mon_sql = mysqli_query($db, "SELECT * FROM invoices WHERE id='$invoice_id' ");
    $pay_info_for_pay = mysqli_fetch_assoc($bill_mon_sql);
    $bill_month = $pay_info_for_pay['billing_month'];

    // Manager logic
    $manager_paid_amount = (int) ($_POST['manager_paid_amount'] ?? 0);
    $manager_payment_method = mysqli_real_escape_string($db, $_POST['manager_payment_method'] ?? '');

    $tren_date = $_POST['payment_date'] ?? date('Y-m-d H:i:s');
    $payment_date = date('Y-m-d H:i:s', strtotime($tren_date));

    // ১. ইনভয়েস চেক করা (নিশ্চিত হওয়া যে ইনভয়েসটি সঠিক)
    $check_sql = mysqli_query($db, "SELECT * FROM invoices WHERE id = '$invoice_id' AND tenant_id = '$tenant_id' LIMIT 1");
    $inv = mysqli_fetch_assoc($check_sql);

    if (!$inv) {
        echo "<script>alert('Invoice not found!'); window.history.back();</script>";
        exit;
    }

    if ($manager_paid_amount > $paid_amount) {
        echo "<script>alert('Error: Manager paid amount ($manager_paid_amount ৳) cannot be greater than Total paid amount ($paid_amount ৳)'); window.history.back();</script>";
        exit;
    }

    // ডিউ চেক করার মেইন লজিক
    $current_due = $inv['total_amount'] - $inv['paid_amount'];

    if ($paid_amount > $current_due) {
        echo "<script>alert('Error: You cannot pay more than the due amount ($current_due ৳)'); window.history.back();</script>";
        exit;
    }

    $new_paid_total = $inv['paid_amount'] + $paid_amount;
    $new_due = $inv['total_amount'] - $new_paid_total;
    $status = ($new_due <= 0) ? 'Paid' : 'Partial';

    // ২. ইনভয়েস টেবিল আপডেট
    mysqli_query($db, "UPDATE invoices SET paid_amount = '$new_paid_total', status = '$status' WHERE id = '$invoice_id'");

    // ৩. পেমেন্ট হিস্ট্রি ইনসার্ট (কলামের নাম ঠিক করা হয়েছে)
    $history_sql = "INSERT INTO payment_history 
        (invoice_id, tenant_id, bill_month, payment_method, paid_amount, note, payment_date, manager_paid,manager_payment_method, transaction_id, transaction_number) 
        VALUES 
        ('$invoice_id','$tenant_id', '$bill_month', '$payment_method', '$paid_amount', '$note', '$payment_date', '$manager_paid_amount', '$manager_payment_method', '$transaction_id', '$transaction_number')";

    if (mysqli_query($db, $history_sql)) {
        echo "<script>alert('Payment Successful!'); window.location.href='admin.php?page=editbill&tenant_id=$tenant_id';</script>";
    } else {
        echo "Error: " . mysqli_error($db);
    }
}

// monthly Invoice sql show
$pay_info = mysqli_query($db, "SELECT * FROM invoices WHERE tenant_id = '$tenant_id' AND unit_id = '$unit_id' ORDER BY billing_month ");
while ($pay_info_sh = mysqli_fetch_assoc($pay_info)) {
    $invoice_id = $pay_info_sh['id'];
    $billing_month_db = $pay_info_sh['billing_month'];
    $total_amount_db = $pay_info_sh['total_amount'];
    $paid_amount_db = $pay_info_sh['paid_amount'];
    $due_amount_db = $total_amount_db - $paid_amount_db;
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
                <?php echo $building_name_db . '/' . $unit_name . '/' . $tent_name ?? '' ?>
            </h5>
        </div>
        <div class="page-header-right">
            <a href="admin.php?page=dashboard" class="btn btn-primary">Back</a>
        </div>
    </div>
    <?php if($type == 'invoice') { ?>
    <!-- Invoice history  -->
    <div class="card mx-4 mt-3">
        <div class="card-body">
            <h6 class="fw-bold text-info mb-4">Monthly (invoice history)</h6>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th scope="col">Invoice No</th>
                            <th scope="col">Bill Month</th>
                            <th scope="col" class="text-end">Total</th>
                            <th scope="col" class="text-end">Paid</th>
                            <th scope="col" class="text-end">Due</th>
                            <th scope="col" class="text-center">Status</th>
                            <th scope="col" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        mysqli_data_seek($pay_info, 0); // rewind result
                        while ($pay_info_sh = mysqli_fetch_assoc($pay_info)):
                            $invoice_id_db = $pay_info_sh['id'];
                            $billing_month_db = $pay_info_sh['billing_month'];
                            $total_amount_db = $pay_info_sh['total_amount'];
                            $paid_amount_db = $pay_info_sh['paid_amount'];
                            $due_amount_db = $total_amount_db - $paid_amount_db;
                            $status = $pay_info_sh['status'];
                            ?>
                            <tr class="mb-1">
                                <td>#INV-<?= $invoice_id_db; ?></td>
                                <td class="fw-bold text-secondary">
                                    <?= date("M Y", strtotime($billing_month_db)) ?>
                                </td>
                                <td class="text-end text-primary fw-bold">
                                    <small>৳</small> <?= number_format($total_amount_db, 0) ?>
                                </td>
                                <td class="text-end text-success fw-bold">
                                    <small>৳</small> <?= number_format($paid_amount_db, 0) ?>
                                </td>
                                <td class="text-end text-danger fw-bold">
                                    <?php echo $due_amount_db ? '<small>৳</small>' . number_format($due_amount_db, 0) : ''; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($status == 'Paid'): ?>
                                        <small class="bg-success text-white p-1 rounded-2">Paid</small>
                                    <?php elseif ($status == 'Unpaid'): ?>
                                        <small class="bg-danger text-white p-1 rounded-2">Unpaid</small>
                                    <?php elseif ($status == 'Partial'): ?>
                                        <small class="bg-warning text-white p-1 rounded-2">Partial</small>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center pe-4">
                                    <div class="btn-group">
                                        <a href="admin.php?page=viewInvoice&unit_id=<?php echo $unit_id; ?>&invoice_id=<?php echo $invoice_id_db; ?>"
                                            class="p-1 btn btn-sm btn-info" title="view">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <a href="admin.php?page=tenant_inv_pay&type=payment" class="p-1 btn btn-sm btn-success" title="payment">
                                            <i class="bi bi-currency-dollar"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php }else if($type == 'payment') { ?>
    <!-- confirm payment  -->
    <div class="row mx-2 mt-3">
        <div class="col-md-7">
            <form method="POST" enctype="multipart/form-data" id="paymentForm">
                <div class="card p-3">
                    <h6 class="fw-bold text-info mb-3">Confirm Payment </h6>
                    <div class="row">
                        <div class="col-md-6">
                            <label>Pay For Invoice*</label>
                            <select name="invoice_id" id="invoice_select" class="form-control form-select" required onchange="updateDueAmount()">
                                <option value="">Select Invoice</option>
                                <?php 
                                // শুধুমাত্র যেগুলোর বিল বাকি আছে সেগুলো দেখাবে
                                mysqli_data_seek($pay_info, 0);
                                while ($row = mysqli_fetch_assoc($pay_info)): 
                                    $invoice_id = $row['id'];
                                    $due = $row['total_amount'] - $row['paid_amount'];
                                    if($due > 0):
                                ?>
                                    <option value="<?= $invoice_id; ?>" data-due="<?= $due; ?>">
                                        <small>(INV-<?= $invoice_id; ?>) </small><?= date("M Y", strtotime($row['billing_month'])) ?> (Due: <?= $due ?>)
                                    </option>
                                <?php 
                                    endif;
                                endwhile; 
                                ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label>Amount *</label>
                            <input type="number" name="paid_amount" id="amount_input" class="form-control" required>
                            <input type="hidden" id="max_due_limit" value="0">
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-md-6">
                            <label>Transaction Time *</label>
                            <input type="datetime-local" class="form-control" name="payment_date" value="<?= date('Y-m-d\TH:i'); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label>Payment Method *</label>
                            <select name="payment_method" id="payment_method" class="form-control form-select" required onchange="togglePaymentFields()">
                                <option value="" selected disabled>Select One</option>
                                <!-- <option value="Cash">Cash</option> -->
                                <option value="Bkash">Bkash</option>
                                <!-- <option value="Nagad">Nagad</option> -->
                                <!-- <option value="Rocket">Rocket</option> -->
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Card">Card</option>
                                <!-- <option value="Manager">Manager</option> -->
                            </select>
                        </div>
                    </div>

                    <!-- Manager Payment Section -->
                    <div id="manager_fields" class="mt-2" style="display: none;">
                        <div class="row">
                            <div class="col-md-6">
                                <label style="color: blue;">Manager paid to Admin</label>
                                <input type="text" class="form-control" name="manager_paid_amount" placeholder="Manager Paid Amount">
                            </div>
                            <div class="col-md-6">
                                <label style="color: blue;">Manager Payment Method</label>
                                <select name="manager_payment_method" class="form-control form-select" id="">
                                    <option value="" selected disabled>Select One</option>
                                    <!-- <option value="Cash">Cash</option> -->
                                    <option value="Bkash">Bkash</option>
                                    <option value="Nagad">Nagad</option>
                                    <option value="Rocket">Rocket</option>
                                    <option value="Bank Transfer">Bank Transfer</option>
                                    <option value="Card">Card</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <label>Transaction ID</label>
                            <input type="text" name="transaction_id" placeholder="Transaction Id" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label>Transaction Number</label>
                            <input type="text" name="transaction_number" placeholder="Transaction Number" class="form-control">
                        </div>
                    </div>

                    <div class="mt-2">
                        <label>Note</label>
                        <input type="text" name="note" placeholder="Note for Payment" class="form-control">
                    </div>

                    <button type="submit" name="save_bill" class="btn btn-success btn-sm mt-3 w-100">Confirm Payment</button>
                </div>
            </form>
        </div>
        
        <div class="col-md-5 mb-4">
            <div class="payment-card pb-3 bg-white shadow-sm border-0 rounded-4">
                <div class="row m-0 align-items-stretch g-0">
                    <!-- Bank Transfer Section -->
                    <div class="col-12 col-sm-7 p-3 border-end border-light">
                        <div class="d-flex align-items-center mb-3">
                            <img src="public/assets/images/bank/brac.png" 
                                alt="BRAC Bank Logo" 
                                style="height: 20px;" 
                                class="me-2">
                            <span class="fw-bold fs-6 text-primary">BRAC BANK</span>
                        </div>
                        
                        <h6 class="fw-bold text-dark mb-1 ">MD MUSTAFIZUR RAHMAN</h6>
                        <div class="fw-bold fs-5 text-primary mb-1">1503101624157001</div>
                        <div class="text-black small mb-3">Account Number</div>
                        
                        <small class="text-secondary fw-semibold text-uppercase d-block" 
                            style="font-size: 0.75rem; letter-spacing: 0.6px;">
                            BRAC BANK LTD | MOGHBAZAR BRANCH
                        </small>
                    </div>

                    <!-- bKash Section -->
                    <div class="col-12 col-sm-5 p-3 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content mb-3">
                                <span style="color:#CC1D50;" class="fs-5">b</span><span class="fs-5 text-black">Kash</span>
                                <img src="public/assets/images/bank/bkash.png" 
                                    alt="bKash Logo" 
                                    style="height: 23px;">
                            </div>
                            
                            <h6 class="fw-bold text-dark mb-1 ">MD MUSTAFIZUR RAHMAN</h6>
                            <div class="fw-bold fs-5  mb-1 "  style="color:#CC1D50;">01715482363</div>
                            <div class="text-muted small mb-3 "><span style="color:#CC1D50;">b</span><span class="text-black">Kash</span> Number</div>
                        </div>
                        
                        <small class="text-secondary fw-semibold text-uppercase  d-block mt-auto" 
                            style="font-size: 0.75rem; letter-spacing: 0.6px;">
                            BKASH PERSONAL
                        </small>
                    </div>
                </div>

                <!-- Warning Box -->
                <div class=" mx-2">
                    <div class="warning-box d-flex align-items-start bg-danger bg-opacity-10 p-3 rounded-3 border border-danger border-opacity-25">
                        <i class="bi bi-exclamation-triangle-fill text-white me-3 mt-1 fs-5"></i>
                        <p class="mb-0 fw-normal small text-white">
                            পেমেন্ট নির্দেশনা,<br>
                            অনুগ্রহ করে নিজ দায়িত্বে পেমেন্ট সম্পন্ন করে পেমেন্ট স্লিপ আপলোড করুন। আপনার পেমেন্ট সর্বোচ্চ ৪৮ ঘণ্টার মধ্যে যাচাই করে এডমিন অনুমোদন করবেন।
                           <br> দ্রষ্টব্য: ভুল নম্বর বা অ্যাকাউন্টে অর্থ প্রেরণ করলে তার দায়ভার সম্পূর্ণ প্রেরণকারীর। এ ক্ষেত্রে কর্তৃপক্ষ দায়ী থাকবে না।
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- payment History  -->
    <div class="card mx-4">
        <div class="card-body">
            <h6 class="fw-bold text-info mb-4">Payment history </h6>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" class="text-end">Invoice Id</th>
                            <th scope="col" class="ps-4">Date & Time</th>
                            <th scope="col" class="text-end">Bill Month</th>
                            <th scope="col" class="text-end">Payment Method</th>
                            <th scope="col" class="text-end">Payment Amount</th>
                            <th scope="col" class="text-end">Bill Summary</th>
                            <!-- <th scope="col" class="text-end">Manager Paid to Admin</th> -->
                            <th scope="col" class="text-center">Note</th>
                            <th scope="col" class="text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // JOIN ব্যবহার করা হয়েছে যাতে invoices টেবিল থেকে total_amount এবং billing_month পাওয়া যায়
                        $history_sql = mysqli_query($db, "SELECT ph.*, inv.total_amount, inv.billing_month 
                                                        FROM `payment_history` ph 
                                                        JOIN `invoices` inv ON ph.invoice_id = inv.id 
                                                        WHERE ph.tenant_id = '$tenant_id' 
                                                        "); //ORDER BY ph.payment_date ASC, ph.id ASC

                        $monthly_paid_tracker = [];

                        while ($pay_history = mysqli_fetch_assoc($history_sql)) {
                            $pay_slip_id = $pay_history['id'];
                            $invoice_id = $pay_history['invoice_id'];
                            $bill_month = $pay_history['billing_month']; 
                            $total_bill_amount = (float)$pay_history['total_amount']; 
                            $current_paid_entry = (float)$pay_history['paid_amount'];
                            
                            // পেমেন্ট মেথড এবং ম্যানেজার সংক্রান্ত ডাটা
                            $pay_method_his = $pay_history['payment_method'];
                            $manager_paid_val = (float)$pay_history['manager_paid']; 
                            $manager_payment_method = $pay_history['manager_payment_method'];

                            // --- লজিক ফিক্স: যদি মেথড Manager হয় তবে self ক্যালকুলেট হবে ---
                            $manager_self = 0;
                            if ($pay_method_his == 'Manager') {
                                $manager_self = $current_paid_entry - $manager_paid_val;
                            }
                            // --------------------------------------------------------

                            // মাস ভিত্তিক পেইড অ্যামাউন্ট ট্র্যাক করা
                            if (!isset($monthly_paid_tracker[$invoice_id])) {
                                $monthly_paid_tracker[$invoice_id] = 0;
                            }
                            $monthly_paid_tracker[$invoice_id] += $current_paid_entry;

                            $calculated_total_paid = $monthly_paid_tracker[$invoice_id];
                            $calculated_due = $total_bill_amount - $calculated_total_paid;

                            $note_his = $pay_history['note'];
                            $pay_date_his = $pay_history['payment_date'];
                            $transaction_id_db = $pay_history['transaction_id'];
                            $transaction_number = $pay_history['transaction_number'];
                            ?>

                            <tr>
                                <td class="text-end">#INV-<?= $invoice_id; ?></td>
                                <td class="ps-4 fw-medium">
                                    <?= date('j-M-y g:i A', strtotime($pay_date_his)) ?>
                                </td>
                                <td class="text-end fw-semibold text-uppercase text-secondary">
                                    <?= date('M Y', strtotime($bill_month)) ?>
                                </td>
                                <td class="text-end text-secondary fw-semibold">
                                    <?= $pay_method_his ?><br>
                                    <?php if (!empty($transaction_id_db)): ?>
                                        <small style="font-size:10px;">(Txn: <?= $transaction_id_db ?>)</small><br>
                                        <small style="font-size:10px;">(Txn Num : <?= $transaction_number ?>)</small><br>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-success fw-bold">
                                    <?= '<small>৳ </small>' . number_format($current_paid_entry, 0); ?>
                                </td>
                                <td class="text-end fw-semibold">
                                    <span class="text-primary" title="Total Bill">
                                        <small>Total: ৳ </small><?= number_format($total_bill_amount, 0) ?>
                                    </span><br>
                                    <span class="text-success" title="Total Paid till this entry">
                                        <small>Paid: ৳ </small><?= number_format($calculated_total_paid, 0) ?>
                                    </span><br>
                                    <span class="<?= ($calculated_due > 0) ? 'text-danger' : 'text-muted' ?>" title="Remaining Due">
                                        <small>Due: ৳ </small><?= number_format($calculated_due, 0) ?>
                                    </span>
                                </td>
                                <!-- <td class="text-end text-success">
                                    <?php if ($pay_method_his == 'Manager'): ?>
                                        <small>Paid : ৳ <?= number_format($manager_paid_val, 0) ?></small><br>
                                        <small class="text-danger">Self : ৳ <?= number_format($manager_self, 0) ?></small><br>
                                        <small style="font-size:10px;" class="text-dark">Method : <?= $manager_payment_method ?></small><br>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td> -->
                                <td class="text-center">
                                    <small class="text-secondary"><?= $note_his ?></small>
                                </td>
                                <td class="text-center">
                                    <div class="btn-group">
                                        <a href="admin.php?page=payslip&unit_id=<?= $unit_id; ?>&id=<?= $pay_slip_id; ?>" class="p-1 btn btn-sm btn-success"><i class="bi bi-eye"></i></a>
                                        <!-- <a href="admin.php?page=update_payment&pay_his_id=<?= $pay_slip_id ?>&invoice_id=<?= $invoice_id; ?>" class="p-1 btn btn-sm btn-info"><i class="bi bi-pencil-square"></i></a>
                                        <a href="admin.php?page=delete_payment&pay_his_id=<?= $pay_slip_id ?>&invoice_id=<?= $invoice_id ?>&tenant_id=<?= $tenant_id ?>" 
                                            class="p-1 btn btn-sm btn-danger" 
                                            onclick="return confirm('Are you sure you want to delete this payment?');">
                                            <i class="bi bi-trash"></i>
                                        </a> -->
                                    </div>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php } ?>
</div>

<script>
    function updateDueAmount() {
        const select = document.getElementById('invoice_select');
        const selectedOption = select.options[select.selectedIndex];
        const due = selectedOption.getAttribute('data-due');
        
        if (due) {
            document.getElementById('amount_input').value = due;
            document.getElementById('max_due_limit').value = due;
        } else {
            document.getElementById('amount_input').value = '';
            document.getElementById('max_due_limit').value = '0';
        }
    }
    function togglePaymentFields() {
        const method = document.getElementById('payment_method').value;
        const digitalFields = document.getElementById('digital_payment_fields');
        const managerFields = document.getElementById('manager_fields');

        // Hide all first
        managerFields.style.display = 'none';

        if (method === 'Manager') {
            managerFields.style.display = 'block';
        }
    }
    // Form Validation before submit
    document.getElementById('paymentForm').onsubmit = function(e) {
        const paidAmount = parseFloat(document.getElementById('amount_input').value);
        const maxDue = parseFloat(document.getElementById('max_due_limit').value);

        if (paidAmount > maxDue) {
            alert("Error: You cannot pay more than the due amount (" + maxDue + " ৳)");
            e.preventDefault();
            return false;
        }
        return true;
    };
</script>