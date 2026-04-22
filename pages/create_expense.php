<?php
include "database/db.php";
$message = '';
$editData = null;

/* ================= AJAX : LOAD UNIT ================= */
if (isset($_POST['ajax']) && $_POST['ajax'] === 'get_units') {
    $building_id  = (int)$_POST['building_id'];
    $selected_unit = isset($_POST['selected_unit']) ? (int)$_POST['selected_unit'] : 0;

    $sql = "
        SELECT id, unit_name 
        FROM unit
        WHERE building_name = $building_id
        ORDER BY unit_name ASC
    ";

    $q = mysqli_query($db, $sql);

    echo '<option value="">Select Unit</option>';
    while ($row = mysqli_fetch_assoc($q)) {
        $selected = ($row['id'] == $selected_unit) ? 'selected' : '';
        echo "<option value='{$row['id']}' $selected>{$row['unit_name']}</option>";
    }
    exit;
}

/* ================= EDIT FETCH ================= */
if (isset($_GET['edit_id'])) {
    $id = (int)$_GET['edit_id'];
    $q = mysqli_query($db, "SELECT * FROM expense WHERE id=$id");
    $editData = mysqli_fetch_assoc($q);
}

/* ================= ADD / UPDATE ================= */
if (isset($_POST['save_expense'])) {
    $id            = $_POST['id'];
    $date          = date('Y-m-d');
    $expense_month = date('Y-m', strtotime($_POST['expense_month']));
    $building      = $_POST['building'];
    $unit          = $_POST['unit'];
    $expense_for   = mysqli_real_escape_string($db, $_POST['expense_for']);
    $amount        = (float)$_POST['amount'];
    $method        = mysqli_real_escape_string($db, $_POST['expense_method']);
    $by            = mysqli_real_escape_string($db, $_POST['expense_by']);
    $desc          = mysqli_real_escape_string($db, $_POST['description']);

    if ($id) {
        // UPDATE
        mysqli_query($db, "
            UPDATE expense SET
                date='$date',
                expense_month='$expense_month',
                building_id='$building',
                unit_id='$unit',
                expense_for='$expense_for',
                amount='$amount',
                expense_method='$method',
                expense_by='$by',
                description='$desc'
            WHERE id=$id
        ");
        $message = "<div class='alert alert-success'>Expense updated successfully</div>";
        // Update editData to reflect changes in UI
        $q = mysqli_query($db, "SELECT * FROM expense WHERE id=$id");
        $editData = mysqli_fetch_assoc($q);
    } else {
        // INSERT
        mysqli_query($db, "
            INSERT INTO expense
            (date, expense_month, building_id, unit_id, expense_for, amount, expense_method, expense_by, description)
            VALUES
            ('$date','$expense_month','$building','$unit','$expense_for','$amount','$method','$by','$desc')
        ");
        $message = "<div class='alert alert-success'>Expense added successfully</div>";
    }
}

// Back বাটনের জন্য building_id সেট করা
$back_building_id = '';
if ($editData) {
    $back_building_id = $editData['building_id'];
} elseif (isset($_GET['id'])) {
    $back_building_id = $_GET['id'];
}
?>

<div class="container my-4 px-4">
    <div class="d-flex justify-content-between align-items-center mt-3">
        <h4 class="mb-0">
            <?= $editData ? 'Update Expense' : 'Add Expense' ?>
        </h4>

        <a href="admin.php?page=Expense&id=<?= $back_building_id ?>" class="btn btn-sm btn-primary">
            <i class="fas fa-arrow-left"></i> Back
        </a>
    </div>
    
    <?= $message ?>

    <form method="POST" class="row g-3 mt-2">
        <input type="hidden" name="id" value="<?= $editData['id'] ?? '' ?>">

        <div class="col-md-6">
            <label class="form-label">Expense Month</label>
            <input type="month" name="expense_month" class="form-control"
                   value="<?= $editData['expense_month'] ?? date('Y-m') ?>" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Building</label>
            <select name="building" id="building" class="form-control" required>
                <option value="">Select Building</option>
                <?php
                $b = mysqli_query($db, "SELECT id, name FROM building");
                while ($row = mysqli_fetch_assoc($b)) {
                    $selected = ($editData && $editData['building_id'] == $row['id']) ? 'selected' : '';
                    echo "<option value='{$row['id']}' $selected>{$row['name']}</option>";
                }
                ?>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Unit</label>
            <select name="unit" id="unit" class="form-control">
                <option value="">Select Unit</option>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Expense For</label>
            <input type="text" name="expense_for" class="form-control"
                   value="<?= $editData['expense_for'] ?? '' ?>" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Amount (৳)</label>
            <input type="number" step="0.01" name="amount" class="form-control"
                   value="<?= $editData['amount'] ?? '' ?>" required>
        </div>

        <div class="col-md-6">
            <label class="form-label">Payment Method</label>
            <select name="expense_method" class="form-control">
                <option value="Cash" <?= ($editData['expense_method'] ?? '')=='Cash'?'selected':'' ?>>Cash</option>
                <option value="Bank" <?= ($editData['expense_method'] ?? '')=='Bank'?'selected':'' ?>>Bank</option>
                <option value="Bkash" <?= ($editData['expense_method'] ?? '')=='Bkash'?'selected':'' ?>>Bkash</option>
                <option value="Nagod" <?= ($editData['expense_method'] ?? '')=='Nagod'?'selected':'' ?>>Nagod</option>
            </select>
        </div>

        <div class="col-md-6">
            <label class="form-label">Expense By</label>
            <input type="text" name="expense_by" class="form-control"
                   value="<?= $editData['expense_by'] ?? '' ?>" placeholder="Manager / Admin">
        </div>

        <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3"><?= $editData['description'] ?? '' ?></textarea>
        </div>

        <div class="col-12">
            <button name="save_expense" class="btn btn-primary px-4">
                <?= $editData ? 'Update Expense' : 'Save Expense' ?>
            </button>
        </div>
    </form>
</div>

<script>
function loadUnits(buildingID, selectedUnit = 0) {
    if(!buildingID) return;
    $('#unit').html('<option>Loading...</option>');
    $.post('', {
        ajax: 'get_units',
        building_id: buildingID,
        selected_unit: selectedUnit
    }, function (data) {
        $('#unit').html(data);
    });
}

$('#building').on('change', function () {
    loadUnits($(this).val());
});

/* EDIT MODE AUTO LOAD */
$(document).ready(function () {
    <?php if ($editData): ?>
        loadUnits(
            <?= (int)$editData['building_id'] ?>,
            <?= (int)$editData['unit_id'] ?>
        );
    <?php endif; ?>
});
</script>