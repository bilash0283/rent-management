<div class="nxl-content">
    <!-- Page Header -->
    <div class="page-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Expense</h5>
        <div class="d-flex justify-between algin-center">
            <form action="">
                <div class="input-group">

                    <select name="building_id" id="building_id" class="form-select">
                        <option value="">Select Building</option>
                        <?php
                        $building_sql = mysqli_query($db, "SELECT * FROM `building`");
                        while ($building_row = mysqli_fetch_assoc($building_sql)) {
                            echo "<option value='{$building_row['id']}'>{$building_row['name']}</option>";
                        }
                        ?>
                    </select>

                    <select name="date" id="date" class="form-select">
                        <option value="">Date</option>
                        <option value="<?= date('Y-m-d') ?>"><?= date('Y-m-d') ?>Today</option>
                    </select>

                    <button name="filter" class="btn btn-primary ">Filter</button>
                </div>
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
                        <h6 class="mb-0">Total Expense</h6>
                        <h3 class="mb-0">
                            <?php
                            $total_expense_sql = mysqli_query($db, "SELECT SUM(amount) AS total FROM `expense`");
                            $total_expense_row = mysqli_fetch_assoc($total_expense_sql);
                            echo $total_expense_row['total'] ?? 0;
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
                                <th>Method</th>
                                <th>By</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php
                            $expense_sql = mysqli_query($db, "SELECT * FROM `expense` ORDER BY id DESC");

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

                                            <a href="admin.php?page=view_expense&view_id=<?= $row['id']; ?>" 
                                               class="btn btn-sm btn-outline-success" title="View">
                                                <i class="bi bi-eye"></i>
                                            </a>

                                            <a href="admin.php?page=delete_expense&delete_id=<?= $row['id']; ?>" 
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