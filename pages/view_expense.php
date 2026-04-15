<?php
include "database/db.php";

if (!isset($_GET['view_id'])) {
    die("<div class='alert alert-danger'>Invalid Request</div>");
}

$id = (int)$_GET['view_id'];

$sql = mysqli_query($db, "
SELECT e.*, b.name as building_name, u.unit_name
FROM expense e
LEFT JOIN building b ON b.id = e.building_id
LEFT JOIN unit u ON u.id = e.unit_id
WHERE e.id = $id
LIMIT 1
");

$data = mysqli_fetch_assoc($sql);

if (!$data) {
    die("<div class='alert alert-danger'>Expense not found</div>");
}
?>

<div class="nxl-content">

    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center">
        <h5>Expense Details</h5>

        <a href="admin.php?page=expense" class="btn btn-primary">
            Back to List
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="card shadow-sm">
            <div class="card-body">

                <div class="row">

                    <!-- ID -->
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Expense ID</label>
                        <div><?= $data['id']; ?></div>
                    </div>

                    <!-- Date -->
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Date</label>
                        <div><?= $data['date']; ?></div>
                    </div>

                    <!-- Building -->
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Building</label>
                        <div><?= $data['building_name']; ?></div>
                    </div>

                    <!-- Unit -->
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Unit</label>
                        <div><?= $data['unit_name']; ?></div>
                    </div>

                    <!-- Expense For -->
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Expense For</label>
                        <div><?= $data['expense_for']; ?></div>
                    </div>

                    <!-- Amount -->
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Amount</label>
                        <div class="text-success fw-bold">
                            <?= $data['amount']; ?> ৳
                        </div>
                    </div>

                    <!-- Method -->
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Payment Method</label>
                        <div>
                            <?php
                            if ($data['expense_method'] == 'Cash') {
                                echo "<span class='badge bg-success'>Cash</span>";
                            } elseif ($data['expense_method'] == 'Bank') {
                                echo "<span class='badge bg-primary'>Bank</span>";
                            } else {
                                echo "<span class='badge bg-warning text-dark'>Bkash</span>";
                            }
                            ?>
                        </div>
                    </div>

                    <!-- Expense By -->
                    <div class="col-md-6 mb-3">
                        <label class="fw-bold">Expense By</label>
                        <div><?= $data['expense_by']; ?></div>
                    </div>

                    <!-- Description -->
                    <div class="col-12 mb-3">
                        <label class="fw-bold">Description</label>
                        <div class="border p-3 rounded bg-light">
                            <?= $data['description'] ?: 'No description provided'; ?>
                        </div>
                    </div>

                </div>

                <!-- Action Buttons -->
                <div class="mt-4 text-end">
                    <a href="admin.php?page=edit_expense&edit_id=<?= $data['id']; ?>" 
                       class="btn btn-primary">
                        Edit
                    </a>

                    <a href="admin.php?page=expense" 
                       class="btn btn-secondary">
                        Back
                    </a>
                </div>

            </div>
        </div>
    </div>
</div>