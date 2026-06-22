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
            $first_b = mysqli_query($db, "SELECT id FROM building LIMIT 1");
            $fb_row = mysqli_fetch_assoc($first_b);
            $building_id = $fb_row['id'] ?? '';
        }
    }

    // ==================== EXPENSE SUMMARIES (Based on expense_by) ====================
    
    // 1. Total Expense
    $total_sql = mysqli_query($db, "SELECT SUM(amount) AS total FROM `expense` WHERE building_id='$building_id' AND expense_month='$this_month'");
    $total_row = mysqli_fetch_assoc($total_sql);
    $grand_total = (float)($total_row['total'] ?? 0);

    // 2. Admin Expense (Assuming 'expense_by' contains 'Admin')
    $admin_sql = mysqli_query($db, "SELECT SUM(amount) AS total FROM `expense` WHERE building_id='$building_id' AND expense_month='$this_month' AND expense_by = 'Admin'");
    $admin_row = mysqli_fetch_assoc($admin_sql);
    $admin_total = (float)($admin_row['total'] ?? 0);

    // 3. Manager Expense (Assuming 'expense_by' contains 'Manager')
    $manager_sql = mysqli_query($db, "SELECT SUM(amount) AS total FROM `expense` WHERE building_id='$building_id' AND expense_month='$this_month' AND expense_by = 'Manager'");
    $manager_row = mysqli_fetch_assoc($manager_sql);
    $manager_total = (float)($manager_row['total'] ?? 0);

    // Fetch Building Name for UI
    $b_name_sql = mysqli_query($db, "SELECT name FROM building WHERE id='$building_id'");
    $b_name_row = mysqli_fetch_assoc($b_name_sql);
    $active_building_name = $b_name_row['name'] ?? 'N/A';

    // Handle Delete
    if (isset($_GET['delete_id'])) {
        $delete_id = (int)$_GET['delete_id'];
        mysqli_query($db, "DELETE FROM expense WHERE id=$delete_id");
        echo "<script>window.location.href='admin.php?page=Expense&id=$building_id';</script>";
        exit;
    }
?>

<div class="nxl-content">
    <!-- Header Section -->
    <div class="page-header d-flex align-items-center justify-content-between pb-3 pt-3">
        <div>
            <h4 class="fw-bold mb-0">Expense Reports</h4>
            <small class="text-muted">Building: <span class="text-primary fw-bold"><?= $active_building_name ?></span></small>
        </div>
        <div class="d-flex align-items-center gap-3">
            <form method="POST" class="d-flex gap-2">
                <select name="month" class="form-select form-select-sm" style="width: 140px;">
                    <?php
                    for ($m = 1; $m <= 12; $m++) {
                        $m_val = date('Y') . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
                        $m_name = date('F', mktime(0, 0, 0, $m, 1));
                        $selected = ($m_val == $this_month) ? 'selected' : '';
                        echo "<option value='$m_val' $selected>$m_name</option>";
                    }
                    ?>
                </select>
                <select name="building" class="form-select form-select-sm" style="width: 170px;">
                    <?php
                    $b_list = mysqli_query($db, "SELECT id, name FROM building ORDER BY name ASC");
                    while ($b = mysqli_fetch_assoc($b_list)) {
                        $sel = ($b['id'] == $building_id) ? 'selected' : '';
                        echo "<option value='{$b['id']}' $sel>{$b['name']}</option>";
                    }
                    ?>
                </select>
                <button type="submit" class="btn btn-sm btn-dark">Filter</button>
            </form>
            <a href="admin.php?page=create_expense" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Create Expense
            </a>
        </div>
    </div>

    <!-- Summary Cards Section -->
    <div class="row g-3 mt-2 mx-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body bg-light-primary" style="border-left: 5px solid #0d6efd;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1 text-uppercase small fw-bold">Admin Expense</h6>
                            <h3 class="mb-0 fw-bold">৳ <?= number_format($admin_total, 0) ?></h3>
                        </div>
                        <div class="bg-primary text-white p-2 rounded-3">
                            <i class="bi bi-person-badge fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body bg-light-warning" style="border-left: 5px solid #ffc107;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1 text-uppercase small fw-bold">Manager Expense</h6>
                            <h3 class="mb-0 fw-bold">৳ <?= number_format($manager_total, 0) ?></h3>
                        </div>
                        <div class="bg-warning text-dark p-2 rounded-3">
                            <i class="bi bi-person-gear fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-body bg-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-white-50 mb-1 text-uppercase small fw-bold">Total Monthly Expense</h6>
                            <h3 class="mb-0 fw-bold text-white">৳ <?= number_format($grand_total, 0) ?></h3>
                        </div>
                        <div class="bg-white text-primary p-2 rounded-3">
                            <i class="bi bi-wallet2 fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Data Table Section -->
    <div class="card mt-2 border-0 shadow-sm mx-3">
        <div class="card-header bg-white border-bottom py-3">
            <h6 class="mb-0 fw-bold"><i class="bi bi-list-ul me-2"></i>Detailed Expense List</h6>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Date</th>
                        <th>Particulars of Expense</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>By</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $exp_query = mysqli_query($db, "SELECT * FROM `expense` WHERE building_id='$building_id' AND expense_month='$this_month' ORDER BY date DESC, id DESC");
                    
                    if (mysqli_num_rows($exp_query) > 0) {
                        while ($row = mysqli_fetch_assoc($exp_query)) {
                            // Assign color based on 'expense_by'
                            $by_badge = ($row['expense_by'] == 'Admin') ? 'bg-primary' : 'bg-warning text-dark';
                    ?>
                        <tr>
                            <td class="ps-3 text-muted small"><?= date('d M Y', strtotime($row['date'])) ?></td>
                            <td>
                                <span class="fw-bold d-block"><?= htmlspecialchars($row['expense_for']) ?></span>
                                <?php 
                                    if(!empty($row['unit_id'])) {
                                        $unit_info_expense = mysqli_query($db, "SELECT unit_name FROM unit WHERE id='{$row['unit_id']}'");
                                        $unit_row_expanse = mysqli_fetch_assoc($unit_info_expense);
                                        echo '<small class="badge bg-light text-dark border mt-1">Unit: ' . ($unit_row_expanse['unit_name'] ?? 'N/A') . '</small>';
                                    } 
                                ?>
                            </td>
                            <td class="fw-bold">৳ <?= number_format($row['amount'], 0) ?></td>
                            <td><span class="text-muted small"><?= $row['expense_method'] ?></span></td>
                            <td>
                                <span class="badge <?= $by_badge ?> py-1 px-2" style="font-size: 10px;">
                                    <?= strtoupper($row['expense_by']) ?>
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group">
                                    <a href="admin.php?page=create_expense&edit_id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary shadow-none border-0"><i class="bi bi-pencil-square"></i></a>
                                    <a href="admin.php?page=Expense&id=<?= $building_id ?>&delete_id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger shadow-none border-0" onclick="return confirm('Delete this record?')"><i class="bi bi-trash"></i></a>
                                </div>
                            </td>
                        </tr>
                    <?php 
                        } 
                    } else { ?>
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted d-block mb-2"></i>
                                <span class="text-muted">No expense records found for this period.</span>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
    .bg-light-primary { background-color: #f0f7ff !important; }
    .bg-light-warning { background-color: #fffbef !important; }
    .card { border-radius: 12px; }
    .table thead th { font-size: 12px; text-transform: uppercase; color: #6c757d; }
</style>