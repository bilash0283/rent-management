<?php 
    $building_id = '';
    $this_month = date('Y-m');   // Current month in YYYY-MM format

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
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            echo '<div class="alert alert-danger">Invalid Building ID</div>';
            exit;
        }
        $building_id = mysqli_real_escape_string($db, $_GET['id']);
    }


    // ==================== OVERALL MANAGER PAYMENT SUMMARY ====================
    $manager_summary = mysqli_query($db, "
            SELECT 
                SUM(ph.paid_amount) as total_received,
                SUM(ph.manager_self) as manager_self_total,
                SUM(ph.expense) as expense_total
            FROM payment_history ph
            JOIN tenants t ON ph.tenant_id = t.id
            WHERE t.building_id = '$building_id' 
            AND ph.bill_month = '$this_month' 
            AND ph.payment_method = 'Manager'
        ");

    $summary = mysqli_fetch_assoc($manager_summary);

    $total_received = (float) ($summary['total_received'] ?? 0);
    $manager_self_total = (float) ($summary['manager_self_total'] ?? 0);
    $manger_expense_total = (float) ($summary['expense_total'] ?? 0);
    $manager_net_paid = $total_received - $manager_self_total - $manger_expense_total;

    // Handle Delete
    if (isset($_GET['delete_id'])) {
        $delete_id = (int)$_GET['delete_id'];
        mysqli_query($db, "DELETE FROM expense WHERE id=$delete_id");
        header("Location: admin.php?page=Expense&id=" . urlencode($building_id));
        exit;
    }
?>
    

<div class="nxl-content">
    <!-- Page Header -->
    <div class="page-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Expenses</h5>
        <div class="d-flex justify-between algin-center">
            <!-- Filter Form -->
            <form method="POST" class="d-flex flex-wrap gap-2 align-items-center">
                <div class="input-group input-group-sm shadow-sm" style="width: 180px;">
                    <span class="input-group-text bg-white border-end-0"><i
                            class="far fa-calendar-check text-muted"></i></span>
                    <select name="month" class="form-select border-start-0 ps-0 fw-medium">
                        <?php
                        $currentYear = date('Y');
                        $selectedMonth = (int) substr($this_month, 5, 2);

                        for ($m = 1; $m <= 12; $m++):
                            $monthValue = $currentYear . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
                            $displayText = date('F', mktime(0, 0, 0, $m, 1, $currentYear)); // শুধু month
                            ?>
                            <option value="<?= $monthValue ?>" <?= $m == $selectedMonth ? 'selected' : '' ?>>
                                <?= $displayText ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="input-group input-group-sm shadow-sm" style="width: 220px;">
                    <span class="input-group-text bg-white border-end-0"><i class="fas fa-building text-muted"></i></span>
                    <select name="building" class="form-select border-start-0 ps-0 fw-medium">
                        <?php
                        $buildings_sql = mysqli_query($db, "SELECT id, name FROM building ORDER BY name ASC");
                        while ($buil = mysqli_fetch_assoc($buildings_sql)) {
                            $b_id = $buil['id'];
                            $b_name = $buil['name'];
                            $selected = ($b_id == $building_id) ? 'selected' : '';
                            echo "<option value='$b_id' $selected>$b_name</option>";
                        }
                        ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm d-flex align-items-center gap-2">
                    <i class="fas fa-filter"></i>
                    <span>Filter</span>
                </button>
            </form>
        </div>

        <a href="admin.php?page=create_expense" class="btn btn-primary">
            <i class="feather-plus me-1"></i> Create Expense
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="card shadow-sm">
            <?= $message ?? '' ?>

            <div class="row">
                <div class="col-md-4">
                    <div class="card-body">
                        <h6 class="mb-0">Admin Expense</h6>
                        <h3 class="mb-0">
                            <?php
                            $total_expense_sql = mysqli_query($db, "SELECT SUM(amount) AS total FROM `expense` WHERE building_id='$building_id' AND expense_month='$this_month' ");
                            $total_expense_row = mysqli_fetch_assoc($total_expense_sql);
                            echo $total_expense_row['total'] ?? 0;
                            ?> ৳
                        </h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-body">
                        <h6 class="mb-0">Manager Expense</h6>
                        <h3 class="mb-0">
                            <?php
                            echo $manger_expense_total ?? 0;
                            ?> ৳
                        </h3>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card-body">
                        <h6 class="mb-0">Total Expense</h6>
                        <h3 class="mb-0">
                            <?php
                            $total_expense_sql = mysqli_query($db, "SELECT SUM(amount) AS total FROM `expense` WHERE building_id='$building_id' AND expense_month='$this_month' ");
                            $total_expense_row = mysqli_fetch_assoc($total_expense_sql);
                            echo $total_expense_row['total'] + $manger_expense_total ?? 0;
                            ?> ৳
                        </h3>
                    </div>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Building / Unit</th>
                                <th>Expense For</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Expense By</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $expense_sql = mysqli_query($db, "SELECT * FROM `expense` WHERE building_id='$building_id' AND expense_month='$this_month' ORDER BY id DESC");

                            while ($row = mysqli_fetch_assoc($expense_sql)) {
                                $building_id = $row['building_id'];
                                $unit_id = $row['unit_id'];
                                ?>
                                <tr>
                                    <td><?php echo $row['expense_month'] ? date('F Y', strtotime($row['expense_month'])) : date('F Y', strtotime($row['date'])); ?></td>
                                    <td>
                                        <?php
                                        $building_sql = mysqli_query($db, "SELECT * FROM `building` WHERE id = '$building_id'");
                                        $building_row = mysqli_fetch_assoc($building_sql);
                                        echo $building_row['name'] ?? 'N/A';
                                        ?>
                                        <br>
                                        <small class="text-muted">
                                            <?php
                                            $unit_sql = mysqli_query($db, "SELECT * FROM `unit` WHERE id = '$unit_id'");
                                            $unit_row = mysqli_fetch_assoc($unit_sql);
                                            echo $unit_row['unit_name'] ?? '';
                                            ?>
                                        </small>
                                    </td>
                                    
                                    <td><?= $row['expense_for'] ?? 'N/A'; ?></td>
                                    <td><?= $row['amount'] ?? 'N/A'; ?> ৳</td>
                                    <td><?= $row['expense_method'] ?? 'N/A'; ?></td>
                                    <td><?= $row['expense_by'] ?? 'N/A'; ?></td>

                                    <td class="text-end">
                                        <div class="btn-group align-items-center">
                                            <a href="admin.php?page=create_expense&edit_id=<?= $row['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>

                                            <!-- <a href="admin.php?page=view_expense&view_id=<?= $row['id']; ?>" 
                                               class="btn btn-sm btn-outline-success" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a> -->

                                            <a href="admin.php?page=Expense&id=<?= htmlspecialchars($building_id) ?>&delete_id=<?= $row['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Are you sure?')"
                                               title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>
</div>