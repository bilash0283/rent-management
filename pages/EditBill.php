<?php
// ==========================
// GET UNIT ID
// ==========================
$unit_id = isset($_GET['unit_id']) ? intval($_GET['unit_id']) : 0;

if ($unit_id <= 0) {
    die("Invalid Unit ID");
}

// ==========================
// UNIT INFO
// ==========================
$query = mysqli_query($db, "SELECT * FROM unit WHERE id='$unit_id' LIMIT 1");

if (!$query || mysqli_num_rows($query) == 0) {
    die("Unit not found");
}

$row = mysqli_fetch_assoc($query);

$unit_name = $row['unit_name'];
$advance = (float)$row['advance'];
$size = $row['size'];
$rent = (float)$row['rent'];
$water = (float)$row['water'];
$gas = (float)$row['gas'];
$building_name = $row['building_name'];
$unit_type = $row['unit_type'];
$Electricity_meter_no = $row['size'];

// ==========================
// BUILDING INFO
// ==========================
$building_sql = mysqli_query($db, "SELECT name FROM building WHERE id='$building_name' LIMIT 1");
$building_row = mysqli_fetch_assoc($building_sql);
$building_name_db = $building_row['name'] ?? 'N/A';

// ==========================
// TENANT INFO
// ==========================
$tent_sql = mysqli_query($db, "SELECT id,name FROM tenants WHERE building_id='$building_name' AND unit_id='$unit_id' LIMIT 1");

$tent_row = mysqli_fetch_assoc($tent_sql);

$tent_name = $tent_row['name'] ?? 'No Tenant';
$tent_id = $tent_row['id'] ?? 0;


// ==========================
// ADVANCE SAVE
// ==========================
if (isset($_POST['advance_save'])) {

    $advance_pay_amount = (float)$_POST['advance_amount'];

    if ($advance_pay_amount <= 0) {
        echo "<script>alert('Invalid Amount');</script>";
    } else {

        $advance_add_sql = mysqli_query($db, "
            INSERT INTO advance 
            (tenant_id, unit_id, paid_amount, date)
            VALUES 
            ('$tent_id','$unit_id','$advance_pay_amount',NOW())
        ");

        if ($advance_add_sql) {
            echo "<script>
                alert('Advance Added Successfully');
                window.location.href='admin.php?page=editbill&unit_id=$unit_id';
            </script>";
            exit;
        }
    }
}



// ==========================
// CREATE INVOICE
// ==========================
if (isset($_POST['create_invoice'])) {

    $status = 'Unpaid';

    $rent_month = $_POST['rent_month'];

    $rent_amount = (float)$_POST['rent'];
    $Gas = (float)$_POST['Gas'];
    $Water = (float)$_POST['Water'];
    $Electricity = (float)$_POST['Electricity'];
    $Others = (float)$_POST['Others'];

    $Gas_month = $_POST['Gas_month'];
    $Water_month = $_POST['Water_month'];
    $Electricity_month = $_POST['Electricity_month'];
    $Others_month = $_POST['Others_month'];

    $total_amount = $rent_amount + $Gas + $Water + $Electricity + $Others;

    // Duplicate invoice check
    $check_invoice = mysqli_query($db,
        "SELECT id FROM invoices 
        WHERE tenant_id='$tent_id'
        AND billing_month='$rent_month'
        LIMIT 1"
    );

    if ($total_amount <= 0) {

        echo "<script>
            alert('Total amount must be greater than 0');
            window.history.back();
        </script>";

        exit;
    }

    $bill_sql = mysqli_query($db, "
        INSERT INTO invoices
        (
            tenant_id,
            unit_id,
            billing_month,
            Rent,
            Gas,
            Gas_month,
            water,
            Water_month,
            Electricity,
            Electricity_month,
            Others,
            Others_month,
            total_amount,
            paid_amount,
            status,
            created_at
        )
        VALUES
        (
            '$tent_id',
            '$unit_id',
            '$rent_month',
            '$rent_amount',
            '$Gas',
            '$Gas_month',
            '$Water',
            '$Water_month',
            '$Electricity',
            '$Electricity_month',
            '$Others',
            '$Others_month',
            '$total_amount',
            '0',
            '$status',
            NOW()
        )
    ");

    if ($bill_sql) {

        echo "<script>
            alert('Invoice Created Successfully');
            window.location.href='admin.php?page=editbill&unit_id=$unit_id';
        </script>";

        exit;
    }
}



// ==========================
// PAYMENT SAVE
// ==========================
if (isset($_POST['save_bill'])) {

    $invoice_id = intval($_POST['invoice_id']);
    $paid_amount = (float)$_POST['paid_amount'];

    $payment_method = mysqli_real_escape_string($db, $_POST['payment_method']);
    $note = mysqli_real_escape_string($db, $_POST['note']);

    $transaction_id = mysqli_real_escape_string($db, $_POST['transaction_id'] ?? '');
    $transaction_number = mysqli_real_escape_string($db, $_POST['transaction_number'] ?? '');

    $manager_paid_amount = (float)($_POST['manager_paid_amount'] ?? 0);

    $manager_payment_method = mysqli_real_escape_string(
        $db,
        $_POST['manager_payment_method'] ?? ''
    );

    $payment_date = $_POST['payment_date'];

    // invoice
    $check_sql = mysqli_query($db,
        "SELECT * FROM invoices 
        WHERE id='$invoice_id'
        AND tenant_id='$tent_id'
        LIMIT 1"
    );

    $inv = mysqli_fetch_assoc($check_sql);

    if (!$inv) {

        echo "<script>alert('Invoice Not Found');</script>";
        exit;
    }

    $current_due = $inv['total_amount'] - $inv['paid_amount'];

    if ($paid_amount > $current_due) {

        echo "<script>
            alert('You cannot pay more than due amount');
            window.history.back();
        </script>";

        exit;
    }

    if ($manager_paid_amount > $paid_amount) {

        echo "<script>
            alert('Manager amount cannot exceed payment amount');
            window.history.back();
        </script>";

        exit;
    }

    // update
    $new_paid_total = $inv['paid_amount'] + $paid_amount;
    $new_due = $inv['total_amount'] - $new_paid_total;

    $status = ($new_due <= 0) ? 'Paid' : 'Partial';

    mysqli_query($db,
        "UPDATE invoices SET
        paid_amount='$new_paid_total',
        status='$status'
        WHERE id='$invoice_id'"
    );

    // history
    $history_sql = mysqli_query($db,
        "INSERT INTO payment_history
        (
            invoice_id,
            tenant_id,
            bill_month,
            payment_method,
            paid_amount,
            note,
            payment_date,
            manager_paid,
            manager_payment_method,
            transaction_id,
            transaction_number
        )
        VALUES
        (
            '$invoice_id',
            '$tent_id',
            '{$inv['billing_month']}',
            '$payment_method',
            '$paid_amount',
            '$note',
            '$payment_date',
            '$manager_paid_amount',
            '$manager_payment_method',
            '$transaction_id',
            '$transaction_number'
        )"
    );

    if ($history_sql) {

        echo "<script>
            alert('Payment Successful');
            window.location.href='admin.php?page=editbill&unit_id=$unit_id';
        </script>";

        exit;
    }
}



// ==========================
// ADVANCE SUMMARY
// ==========================
$total_paid = 0;

$advance_sql = mysqli_query($db,
    "SELECT * FROM advance
    WHERE tenant_id='$tent_id'
    AND unit_id='$unit_id'
    ORDER BY id DESC"
);

while ($advance_his = mysqli_fetch_assoc($advance_sql)) {

    $total_paid += $advance_his['paid_amount'];
}

$payable = max($advance - $total_paid, 0);

?>



<style>

body{
    background:#f3f4f7;
}

/* ==========================
 MOBILE APP UI
========================== */

.mobile-card{
    background:#fff;
    border-radius:18px;
    padding:14px;
    margin-bottom:15px;
    box-shadow:0 2px 10px rgba(0,0,0,.06);
}

.mobile-title{
    font-size:16px;
    font-weight:700;
    margin-bottom:14px;
    color:#0d6efd;
}

.mobile-input{
    border-radius:12px !important;
    height:46px;
    font-size:14px;
}

.mobile-btn{
    height:46px;
    border-radius:12px;
    font-weight:700;
    font-size:14px;
}

.summary-box{
    background:#f8f9ff;
    border-radius:14px;
    padding:12px;
    margin-bottom:10px;
}

.summary-box small{
    display:block;
    color:#888;
    margin-bottom:3px;
}

.summary-box h5{
    margin:0;
    font-size:18px;
    font-weight:700;
}

.invoice-item{
    background:#fff;
    border-radius:16px;
    padding:12px;
    margin-bottom:12px;
    border:1px solid #eee;
}

.invoice-top{
    display:flex;
    justify-content:space-between;
    align-items:center;
    margin-bottom:10px;
}

.invoice-status{
    padding:5px 10px;
    border-radius:30px;
    font-size:11px;
    color:#fff;
    font-weight:700;
}

.paid{
    background:#198754;
}

.unpaid{
    background:#dc3545;
}

.partial{
    background:#ffc107;
    color:#000;
}

.invoice-price{
    display:flex;
    justify-content:space-between;
    margin-top:8px;
    font-size:13px;
}

.invoice-actions{
    display:flex;
    gap:8px;
    margin-top:12px;
}

.invoice-actions a{
    flex:1;
}

.pay-history{
    background:#fff;
    border-radius:15px;
    padding:12px;
    margin-bottom:10px;
    border:1px solid #eee;
}

.pay-history small{
    color:#666;
}

.mobile-header{
    background:#0d6efd;
    color:#fff;
    border-radius:0 0 20px 20px;
    padding:20px 15px;
    margin-bottom:15px;
}

.mobile-header h4{
    font-size:18px;
    margin:0;
    font-weight:700;
}

.mobile-header p{
    margin:4px 0 0;
    font-size:13px;
}

@media(max-width:768px){

    .container-fluid{
        padding:8px;
    }

    .mobile-title{
        font-size:15px;
    }

    .mobile-input{
        font-size:13px;
    }
}

</style>



<div class="container-fluid px-2 pb-5">

    <!-- HEADER -->
    <div class="mobile-header">

        <h4 class="text-white">
            <?= $building_name_db ?>
        </h4>

        <p>
            <?= $unit_name ?> |
            <?= $tent_name ?>
        </p>

    </div>



    <!-- ADVANCE SUMMARY -->
    <div class="mobile-card">

        <div class="mobile-title">
            Advance Summary
        </div>

        <div class="summary-box">
            <small>Total Advance</small>
            <h5>৳ <?= number_format($advance,0) ?></h5>
        </div>

        <div class="summary-box">
            <small>Total Paid</small>
            <h5 class="text-success">
                ৳ <?= number_format($total_paid,0) ?>
            </h5>
        </div>

        <div class="summary-box">
            <small>Remaining</small>
            <h5 class="text-danger">
                ৳ <?= number_format($payable,0) ?>
            </h5>
        </div>

    </div>



    <!-- ADVANCE PAYMENT -->
    <div class="mobile-card">

        <div class="mobile-title">
            Add Advance Payment
        </div>

        <form method="POST">

            <input
                type="number"
                name="advance_amount"
                class="form-control mobile-input mb-3"
                placeholder="Enter Amount"
                required
            >

            <button
                type="submit"
                name="advance_save"
                class="btn btn-success mobile-btn w-100"
            >
                Save Advance
            </button>

        </form>

    </div>




 <!-- =========================
CREATE INVOICE FULL FIXED UI
========================= -->

<div class="mobile-card">

    <div class="mobile-title">
        Create Invoice
    </div>

    <form method="POST">

        <!-- RENT -->
        <div class="row">

            <div class="col-6 mb-2">

                <small class="fw-semibold">
                    Rent Month
                </small>

                <input
                    type="month"
                    name="rent_month"
                    value="<?= date('Y-m') ?>"
                    class="form-control mobile-input"
                    required
                >

            </div>

            <div class="col-6 mb-2">

                <small class="fw-semibold">
                    Rent Amount
                </small>

                <input
                    type="number"
                    name="rent"
                    value="<?= $rent ?>"
                    class="form-control mobile-input"
                    placeholder="Rent Amount"
                    required
                >

            </div>

        </div>



        <!-- GAS -->
        <div class="row">

            <div class="col-6 mb-2">

                <small class="fw-semibold">
                    Gas Month
                </small>

                <input
                    type="text"
                    name="Gas_month"
                    value="<?= date('M Y') ?>"
                    class="form-control mobile-input"
                    placeholder="Gas Month"
                >

            </div>

            <div class="col-6 mb-2">

                <small class="fw-semibold">
                    Gas Amount
                </small>

                <input
                    type="number"
                    name="Gas"
                    value="<?= $gas ?>"
                    class="form-control mobile-input"
                    placeholder="Gas Amount"
                >

            </div>

        </div>




        <!-- WATER -->
        <div class="row">

            <div class="col-6 mb-2">

                <small class="fw-semibold">
                    Water Month
                </small>

                <input
                    type="text"
                    name="Water_month"
                    value="<?= date('M Y') ?>"
                    class="form-control mobile-input"
                    placeholder="Water Month"
                >

            </div>

            <div class="col-6 mb-2">

                <small class="fw-semibold">
                    Water Amount
                </small>

                <input
                    type="number"
                    name="Water"
                    value="<?= $water ?>"
                    class="form-control mobile-input"
                    placeholder="Water Amount"
                >

            </div>

        </div>





        <!-- ELECTRICITY -->
        <div class="row">

            <div class="col-6 mb-2">

                <small class="fw-semibold">
                    Electricity Month
                </small>

                <input
                    type="text"
                    name="Electricity_month"
                    value="<?= date('M Y') ?>"
                    class="form-control mobile-input"
                    placeholder="Electricity Month"
                >

            </div>

            <div class="col-6 mb-2">

                <small class="fw-semibold">
                    Electricity Amount
                </small>

                <input
                    type="number"
                    name="Electricity"
                    value=""
                    class="form-control mobile-input"
                    placeholder="Electricity Amount"
                >

            </div>

        </div>




        <!-- ELECTRICITY METER -->
        <div class="row">

            <div class="col-12 mb-2">

                <small class="fw-semibold">
                    Electricity Meter No
                </small>

                <input
                    type="text"
                    value="<?= $Electricity_meter_no ?>"
                    class="form-control mobile-input"
                    readonly
                >

            </div>

        </div>





        <!-- OTHERS -->
        <div class="row">

            <div class="col-6 mb-2">

                <small class="fw-semibold">
                    Others Note
                </small>

                <input
                    type="text"
                    name="Others_month"
                    class="form-control mobile-input"
                    placeholder="Others Note"
                >

            </div>

            <div class="col-6 mb-2">

                <small class="fw-semibold">
                    Others Amount
                </small>

                <input
                    type="number"
                    name="Others"
                    value=""
                    class="form-control mobile-input"
                    placeholder="Others Amount"
                >

            </div>

        </div>



        <!-- SUBMIT -->
        <button
            type="submit"
            name="create_invoice"
            class="btn btn-primary mobile-btn w-100"
        >
            Create Invoice
        </button>

    </form>

</div>





    <!-- CONFIRM PAYMENT -->
    <div class="mobile-card">

        <div class="mobile-title">
            Confirm Payment
        </div>

        <form method="POST" id="paymentForm">

            <div class="mb-2">

                <small>Select Invoice</small>

                <select
                    name="invoice_id"
                    id="invoice_select"
                    class="form-select mobile-input"
                    required
                    onchange="updateDueAmount()"
                >

                    <option value="">
                        Select Invoice
                    </option>

                    <?php

                    $pay_info = mysqli_query($db,
                        "SELECT * FROM invoices
                        WHERE tenant_id='$tent_id'
                        AND unit_id='$unit_id'
                        ORDER BY id DESC"
                    );

                    while($row = mysqli_fetch_assoc($pay_info)):

                        $due = $row['total_amount'] - $row['paid_amount'];

                        if($due > 0):

                    ?>

                    <option
                        value="<?= $row['id'] ?>"
                        data-due="<?= $due ?>"
                    >

                        INV-<?= $row['id'] ?>
                        |
                        <?= date('M Y', strtotime($row['billing_month'])) ?>
                        |
                        Due ৳<?= $due ?>

                    </option>

                    <?php endif; endwhile; ?>

                </select>

            </div>


            <div class="mb-2">

                <small>Amount</small>

                <input
                    type="number"
                    name="paid_amount"
                    id="amount_input"
                    class="form-control mobile-input"
                    required
                >

                <input
                    type="hidden"
                    id="max_due_limit"
                    value="0"
                >

            </div>


            <div class="mb-2">

                <small>Payment Method</small>

                <select
                    name="payment_method"
                    id="payment_method"
                    class="form-select mobile-input"
                    onchange="togglePaymentFields()"
                    required
                >

                    <option value="">Select</option>

                    <option value="Cash">Cash</option>
                    <option value="Bkash">Bkash</option>
                    <option value="Nagad">Nagad</option>
                    <option value="Rocket">Rocket</option>
                    <option value="Bank Transfer">Bank</option>
                    <option value="Card">Card</option>
                    <option value="Manager">Manager</option>

                </select>

            </div>


            <div class="mb-2">

                <small>Date Time</small>

                <input
                    type="datetime-local"
                    name="payment_date"
                    value="<?= date('Y-m-d\TH:i') ?>"
                    class="form-control mobile-input"
                    required
                >

            </div>


            <div id="manager_fields" style="display:none;">

                <div class="mb-2">

                    <small>Manager Paid to Admin</small>

                    <input
                        type="text"
                        name="manager_paid_amount"
                        class="form-control mobile-input"
                    >

                </div>

                <div class="mb-2">

                    <small>Manager Payment Method</small>

                    <select
                        name="manager_payment_method"
                        class="form-select mobile-input"
                    >

                        <option value="">Select</option>
                        <option value="Cash">Cash</option>
                        <option value="Bkash">Bkash</option>
                        <option value="Nagad">Nagad</option>
                        <option value="Rocket">Rocket</option>
                        <option value="Bank">Bank</option>
                        <option value="Card">Card</option>
                    </select>

                </div>

            </div>



            <div class="mb-2">

                <small>Transaction ID</small>

                <input
                    type="text"
                    name="transaction_id"
                    class="form-control mobile-input"
                >

            </div>

            <div class="mb-2">

                <small>Transaction Number</small>

                <input
                    type="text"
                    name="transaction_number"
                    class="form-control mobile-input"
                >

            </div>

            <div class="mb-3">

                <small>Note</small>

                <textarea
                    name="note"
                    class="form-control"
                    rows="3"
                    style="border-radius:12px;"
                ></textarea>

            </div>

            <button
                type="submit"
                name="save_bill"
                class="btn btn-success mobile-btn w-100"
            >
                Confirm Payment
            </button>

        </form>

    </div>





    <!-- INVOICE HISTORY -->
    <div class="mobile-card">

        <div class="mobile-title">
            Invoice History
        </div>

        <?php

        $invoice_sql = mysqli_query($db,
            "SELECT * FROM invoices
            WHERE tenant_id='$tent_id'
            AND unit_id='$unit_id'
            ORDER BY id DESC"
        );

        while($inv = mysqli_fetch_assoc($invoice_sql)):

            $due = $inv['total_amount'] - $inv['paid_amount'];

        ?>

        <div class="invoice-item">

            <div class="invoice-top">

                <div>

                    <strong>
                        #INV-<?= $inv['id'] ?>
                    </strong>

                    <br>

                    <small>
                        <?= date('M Y', strtotime($inv['billing_month'])) ?>
                    </small>

                </div>

                <div>

                    <?php

                    $class = 'unpaid';

                    if($inv['status']=='Paid'){
                        $class='paid';
                    }

                    if($inv['status']=='Partial'){
                        $class='partial';
                    }

                    ?>

                    <span class="invoice-status <?= $class ?>">
                        <?= $inv['status'] ?>
                    </span>

                </div>

            </div>


            <div class="invoice-price">
                <span>Total</span>
                <strong>৳ <?= number_format($inv['total_amount']) ?></strong>
            </div>

            <div class="invoice-price">
                <span>Paid</span>
                <strong class="text-success">
                    ৳ <?= number_format($inv['paid_amount']) ?>
                </strong>
            </div>

            <div class="invoice-price">
                <span>Due</span>
                <strong class="text-danger">
                    ৳ <?= number_format($due) ?>
                </strong>
            </div>

            <div class="invoice-actions">

                <a
                    href="admin.php?page=viewInvoice&unit_id=<?= $unit_id ?>&invoice_id=<?= $inv['id'] ?>"
                    class="btn btn-success btn-sm"
                >
                    <i class="bi bi-eye"></i>
                </a>

                <a
                    href="admin.php?page=UpdateInvoice&unit_id=<?= $unit_id ?>&invoice_id=<?= $inv['id'] ?>"
                    class="btn btn-info btn-sm"
                >
                    <i class="bi bi-pencil-square"></i>
                </a>

                <a href="admin.php?page=DeleteInvoice&unit_id=<?php echo $unit_id;?>&invoice_id=<?php echo $inv['id']; ?>"
                    class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this Invoice ?');">
                   <i class="bi bi-trash"></i>
                </a>

            </div>

        </div>

        <?php endwhile; ?>

    </div>




    <!-- PAYMENT HISTORY -->
    <div class="mobile-card">

        <div class="mobile-title">
            Payment History
        </div>

        <?php

        $history_sql = mysqli_query($db,
            "SELECT ph.*, inv.total_amount
            FROM payment_history ph
            JOIN invoices inv ON ph.invoice_id=inv.id
            WHERE ph.tenant_id='$tent_id'
            ORDER BY ph.id DESC"
        );

        while($pay = mysqli_fetch_assoc($history_sql)):

        ?>

        <div class="pay-history">

            <div class="d-flex justify-content-between">

                <strong>
                    INV-<?= $pay['invoice_id'] ?>
                </strong>

                <small>
                    <?= date('d M Y h:i A', strtotime($pay['payment_date'])) ?>
                </small>

            </div>

            <hr>

            <div class="d-flex justify-content-between mb-1">

                <small>Method</small>

                <strong>
                    <?= $pay['payment_method'] ?>
                </strong>

            </div>

            <div class="d-flex justify-content-between mb-1">

                <small>Paid Amount</small>

                <strong class="text-success">
                    ৳ <?= number_format($pay['paid_amount']) ?>
                </strong>

            </div>

            <?php if($pay['payment_method']=='Manager'): ?>

            <div class="d-flex justify-content-between mb-1">

                <small>Manager Paid to Admin</small>

                <strong>
                    ৳ <?= number_format($pay['manager_paid']) ?>
                </strong>

            </div>

            <?php endif; ?>

            <?php if(!empty($pay['note'])): ?>

            <div class="mt-2">

                <small>Note:</small>

                <div class="text-secondary">
                    <?= $pay['note'] ?>
                </div>

            </div>

            <?php endif; ?>


            <div class="invoice-actions mt-3">

                <a
                    href="admin.php?page=payslip&unit_id=<?= $unit_id ?>&id=<?= $pay['id'] ?>"
                    class="btn btn-success btn-sm"
                >
                    <i class="bi bi-eye"></i>
                </a>

                <a
                    href="admin.php?page=update_payment&pay_his_id=<?= $pay['id'] ?>&invoice_id=<?= $pay['invoice_id'] ?>"
                    class="btn btn-primary btn-sm"
                >
                   <i class="bi bi-pencil-square"></i>
                </a>

                <a href="admin.php?page=delete_payment&pay_his_id=<?= $pay['id'] ?>&invoice_id=<?= $pay['invoice_id'] ?>&unit_id=<?= $unit_id ?>" class="p-1 btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this payment?');">
                    <i class="bi bi-trash"></i>
                </a>

            </div>

        </div>

        <?php endwhile; ?>

    </div>

</div>




<script>

function updateDueAmount(){

    const select = document.getElementById('invoice_select');

    const selectedOption =
        select.options[select.selectedIndex];

    const due =
        selectedOption.getAttribute('data-due');

    if(due){

        document.getElementById('amount_input').value = due;

        document.getElementById('max_due_limit').value = due;

    }else{

        document.getElementById('amount_input').value = '';

        document.getElementById('max_due_limit').value = 0;
    }
}



function togglePaymentFields(){

    const method =
        document.getElementById('payment_method').value;

    const managerFields =
        document.getElementById('manager_fields');

    managerFields.style.display = 'none';

    if(method === 'Manager'){

        managerFields.style.display = 'block';
    }
}



// FORM VALIDATION
document.getElementById('paymentForm').onsubmit = function(e){

    const paidAmount =
        parseFloat(document.getElementById('amount_input').value);

    const maxDue =
        parseFloat(document.getElementById('max_due_limit').value);

    if(paidAmount > maxDue){

        alert(
            "You cannot pay more than due amount"
        );

        e.preventDefault();

        return false;
    }

    return true;
};

</script>