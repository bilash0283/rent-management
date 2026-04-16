<div class="nxl-content">
    <!-- Page Header -->
    <div class="page-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Expense</h5>

        <a href="admin.php?page=create_expense" class="btn btn-primary">
            <i class="feather-plus me-1"></i> Create Expense
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="card shadow-sm">
            <?= $message ?? '' ?>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Building</th>
                                <th>Unit</th>
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
                                    <td><?= $row['date']; ?></td>
                                    <td>
                                        <?php
                                        $building_sql = mysqli_query($db, "SELECT * FROM `building` WHERE id = '$building_id'");
                                        $building_row = mysqli_fetch_assoc($building_sql);
                                        echo $building_row['name'];
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $unit_sql = mysqli_query($db, "SELECT * FROM `unit` WHERE id = '$unit_id'");
                                        $unit_row = mysqli_fetch_assoc($unit_sql);
                                        echo $unit_row['unit_name'];
                                        ?>
                                    </td>
                                    <td><?= $row['expense_for']; ?></td>
                                    <td><?= $row['amount']; ?> ৳</td>
                                    <td><?= $row['expense_method']; ?></td>
                                    <td><?= $row['expense_by']; ?></td>

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