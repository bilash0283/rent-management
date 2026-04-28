<?php 
    $building_id = '';
    $this_month = date('Y-m');   // Default current month

    // Handle POST Filter
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['month']) && !empty($_POST['month'])) {
            $this_month = mysqli_real_escape_string($db, $_POST['month']);
        }
        if (isset($_POST['building']) && !empty($_POST['building'])) {
            $building_id = mysqli_real_escape_string($db, $_POST['building']);
        }
    }

    // If no POST, get building_id from URL
    if (empty($building_id)) {
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $building_id = mysqli_real_escape_string($db, $_GET['id']);
        } else {
            // Default select the first building if no ID is found
            $first_b = mysqli_query($db, "SELECT id FROM building LIMIT 1");
            $fb_row = mysqli_fetch_assoc($first_b);
            $building_id = $fb_row['id'] ?? '';
        }
    }

    // ==================== MANAGER EXPENSE SUMMARY ====================
    $manager_sum_sql = mysqli_query($db, "
        SELECT SUM(ph.expense) as expense_total 
        FROM payment_history ph
        JOIN tenants t ON ph.tenant_id = t.id
        WHERE t.building_id = '$building_id' 
        AND ph.bill_month = '$this_month'
    ");
    $m_summary = mysqli_fetch_assoc($manager_sum_sql);
    $manger_expense_total = (float)($m_summary['expense_total'] ?? 0);

    // ==================== ADMIN EXPENSE SUMMARY ====================
    $admin_sum_sql = mysqli_query($db, "SELECT SUM(amount) AS total FROM `expense` WHERE building_id='$building_id' AND expense_month='$this_month'");
    $a_summary = mysqli_fetch_assoc($admin_sum_sql);
    $admin_total = (float)($a_summary['total'] ?? 0);

    // Handle Delete (Only for expense table)
    if (isset($_GET['delete_id'])) {
        $delete_id = (int)$_GET['delete_id'];
        mysqli_query($db, "DELETE FROM expense WHERE id=$delete_id");
        echo "<script>window.location.href='admin.php?page=Expense&id=$building_id';</script>";
        exit;
    }
?>

<div class="nxl-content mx-3">
    <div class="page-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Expense Reports</h5>
        <div class="d-flex align-items-center gap-3 py-3">
            <form method="POST" class="d-flex gap-2">
                <select name="month" class="form-select form-select-sm" style="width: 150px;">
                    <?php
                    $year = date('Y');
                    for ($m = 1; $m <= 12; $m++) {
                        $m_val = $year . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
                        $m_name = date('F', mktime(0, 0, 0, $m, 1));
                        $selected = ($m_val == $this_month) ? 'selected' : '';
                        echo "<option value='$m_val' $selected>$m_name</option>";
                    }
                    ?>
                </select>
                <select name="building" class="form-select form-select-sm" style="width: 180px;">
                    <?php
                    $b_list = mysqli_query($db, "SELECT id, name FROM building ORDER BY name ASC");
                    while ($b = mysqli_fetch_assoc($b_list)) {
                        $sel = ($b['id'] == $building_id) ? 'selected' : '';
                        echo "<option value='{$b['id']}' $sel>{$b['name']}</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="btn btn-sm btn-primary">Filter</button>
            </form>
            <a href="admin.php?page=create_expense" class="btn btn-primary ">
                <i class="feather-plus"></i> Create Expense
            </a>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-md-4">
            <div class="card card-body shadow-sm text-center">
                <h6 class="text-muted">Admin Expense</h6>
                <h3 class="mb-0"><?= number_format($admin_total, 0) ?> ৳</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-body shadow-sm text-center">
                <h6 class="text-muted">Manager Expense</h6>
                <h3 class="mb-0"><?= number_format($manger_expense_total, 0) ?> ৳</h3>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-body shadow-sm text-center bg-primary ">
                <h6 class="text-white">Total Expense</h6>
                <h3 class="mb-0 text-white"><?= number_format($admin_total + $manger_expense_total, 0) ?> ৳</h3>
            </div>
        </div>
    </div>

    <div class="card mt-4 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Particulars of Expense</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Expense By</th>
                        <th class="text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // 1. Fetch from 'expense' table
                    $exp_query = mysqli_query($db, "SELECT * FROM `expense` WHERE building_id='$building_id' AND expense_month='$this_month' ORDER BY id DESC");
                    while ($row = mysqli_fetch_assoc($exp_query)) {
                    ?>
                        <tr>
                            <td><?= date('d M Y', strtotime($row['date'])) ?></td>
                            <td>
                                <?= $row['expense_for'] ?><br>
                                <?php 
                                    if(!empty($row['unit_id'])) {
                                    $unit_info_expense = mysqli_query($db, "SELECT id, unit_name FROM unit WHERE id='{$row['unit_id']}'");
                                    $unit_row_expanse = mysqli_fetch_assoc($unit_info_expense);
                                ?>
                                <small class="text-muted">Unit : <?= $unit_row_expanse['unit_name'] ?: ''?></small>
                                <?php } ?>
                            </td>
                            <td class="fw-bold"><?= number_format($row['amount'], 0) ?> ৳</td>
                            <td><?= $row['expense_method'] ?></td>
                            <td class="text-success"><?= $row['expense_by'] ?></td>
                            <td class="text-end">
                                <div class="btn-group ">
                                    <a href="admin.php?page=create_expense&edit_id=<?= $row['id'] ?>" class="p-1 btn btn-sm btn-icon btn-primary"><i class="bi bi-pencil"></i></a>
                                    <a href="admin.php?page=Expense&id=<?= $building_id ?>&delete_id=<?= $row['id'] ?>" class="p-1 btn btn-sm btn-icon btn-danger " onclick="return confirm('Delete this record?')"><i class="bi bi-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>

                    <?php
                    // 2. Fetch from 'payment_history' table (Manager deductions)
                    $ph_query = mysqli_query($db, "
                        SELECT ph.*, t.unit_id as unit_id 
                        FROM payment_history ph
                        JOIN tenants t ON ph.tenant_id = t.id
                        WHERE t.building_id = '$building_id' 
                        AND ph.bill_month = '$this_month' 
                        AND ph.expense > 0
                    ");
                    while ($ph_row = mysqli_fetch_assoc($ph_query)) {
                    ?>
                        <tr class="table-light">
                            <td><?= date('d M Y', strtotime($ph_row['payment_date'])) ?></td>
                            <td>
                                <?= $ph_row['expense_note'] ?: 'No Note' ?><br>
                                <?php 
                                    if(!empty($ph_row['unit_id'])) {
                                    $unit_info = mysqli_query($db, "SELECT id, unit_name FROM unit WHERE id='{$ph_row['unit_id']}'");
                                    $unit_row = mysqli_fetch_assoc($unit_info);
                                ?>
                                <small class="text-muted">Unit : <?= $unit_row['unit_name'] ?: ''?></small>
                                <?php } ?>
                            </td>
                            <td class="fw-bold"><?= number_format($ph_row['expense'], 0) ?> ৳</td>
                            <td>Cash</td>
                            <td class="text-warning">Manager</td>
                            <td class="text-end text-muted"></td>
                        </tr>
                    <?php } ?>

                    <?php if (mysqli_num_rows($exp_query) == 0 && mysqli_num_rows($ph_query) == 0): ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">No records found for this month and building.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>