<?php
if ($_GET['type'] == 'Tenant') {
    $tenant_id = $_SESSION['id'];
    $notification_sql = mysqli_query($db, "SELECT * FROM `notification` WHERE tenant_id = '$tenant_id' ORDER BY id DESC");
} elseif($_GET['type'] == 'Admin') {
    $notification_sql = mysqli_query($db, "SELECT * FROM `notification` ORDER BY id DESC");
}
?>

<div class="card mx-3 shadow-sm">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0 text-white"><i class="fas fa-bell text-warning text-small"></i> Notifications</h5>
        <button class="btn btn-secondary" onclick="goBack()">
            ← Back
        </button>
    </div>
    <div class="card-body">
        <?php if (mysqli_num_rows($notification_sql) == 0) { ?>
            <p class="text-muted small mb-0">No notifications found.</p>
        <?php } ?>
<?php
while ($notification = mysqli_fetch_assoc($notification_sql)) {
    $notification_id = $notification['id'];
    $notification_title = $notification['title'];
    $notification_description = $notification['description'];
    $notification_date = date('M j, Y', strtotime($notification['date']));
    $status = $notification['status'];
    $tenant_id = $notification['tenant_id'];
    ?>
    <div class="mb-2 pb-2 border-bottom">
        <div class="row">
            <div class="col-md-10">
                <div class="d-flex align-items-start gap-2 mb-1">
                    <?php
                    if ($status == 'Approved') {
                        echo '<i class="fas fa-check-circle text-success small"></i>';
                    } elseif ($status == 'Pending') {
                        echo '<i class="fas fa-exclamation-circle text-warning small"></i>';
                    } elseif ($status == 'Rejected') {
                        echo '<i class="fas fa-times-circle text-danger small"></i>';
                    } else {
                        echo '<i class="fas fa-info-circle text-muted small"></i>';
                    }
                    ?>
                    <h6 class="mb-0 fw-bold text-dark small"><?= $notification_title ?></h6>
                </div>
                <p class="text-muted small mb-1" style="font-size: 0.85rem;"><?= $notification_description ?></p>
                <small class="text-muted" style="font-size: 0.75rem;"><?= $notification_date ?></small>
            </div>
            <?php if ($_SESSION['role'] == 'Admin') { ?>
            <div class="col-md-2">
                <!-- <?php if ($status == 'Pending') { ?>
                   <span class="badge bg-warning text-dark small">Pending</span>
                <?php } elseif ($status == 'Approved') { ?>
                    <span class="badge bg-success small">Approved</span>
                <?php } ?> -->
                <a href="admin.php?page=editbill&tenant_id=<?= $tenant_id ?>" class="bg-info p-1 rounded-2 fs-10">Details</a>
            </div>
            <?php } ?>
        </div>
    </div>
<?php } ?>
</div>
</div>

<script>
function goBack() {
    if (document.referrer) {
        window.history.back();
    } else {
        window.location.href = "index.php";
    }
}
</script>