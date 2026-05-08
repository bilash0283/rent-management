<?php
if(isset($_GET['building_id'])){
    $building_id_get = $_GET['building_id'];

    // Fetch all units
    $query_sql  = "SELECT * FROM unit WHERE building_name = '$building_id_get' ORDER BY id DESC";
    $result_bul = mysqli_query($db, $query_sql);

    if (!$result_bul) {
        die("Query Failed: " . mysqli_error($db));
    }
}

$message = "";

// ================= DELETE TENANT =================
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['delete_id'])) {

    $id = (int) $_GET['delete_id'];
    $sql = "SELECT tenant_image, nid_image, unit_id FROM tenants WHERE id = $id";
    $result = mysqli_query($db, $sql);
    if ($result && $row = mysqli_fetch_assoc($result)) {

        if (!empty($row['tenant_image'])) {
            $file = "public/uploads/tenants/" . $row['tenant_image'];
            if (file_exists($file)) unlink($file);
        }

        if (!empty($row['nid_image'])) {
            $file = "public/uploads/nid/" . $row['nid_image'];
            if (file_exists($file)) unlink($file);
        }

        // Unit back to Available
        mysqli_query($db, "UPDATE unit SET status='Available' WHERE id=".$row['unit_id']);
    }

    if (mysqli_query($db, "DELETE FROM tenants WHERE id=$id")) {
        mysqli_query($db, "DELETE FROM payment_history WHERE tenant_id=$id");
        mysqli_query($db, "DELETE FROM invoices WHERE tenant_id=$id");
        mysqli_query($db,"DELETE FROM `advance` WHERE tenant_id = $id ");
        $message = '
        <div class="alert alert-success alert-dismissible fade show mx-3 mt-2 mb-0">
            <strong>Success!</strong> Tenant deleted successfully
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    } else {
        $message = '
        <div class="alert alert-danger alert-dismissible fade show mx-3 mt-2 mb-0">
            <strong>Error!</strong> '.mysqli_error($db).'
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }
}

// ================= FETCH TENANTS =================
$query = "
    SELECT 
        u.*, 
        t.*, 
        b.name AS building_name
    FROM unit u
    JOIN building b ON u.building_name = b.id
    LEFT JOIN tenants t ON t.unit_id = u.id
    WHERE u.building_name = '$building_id_get'
    ORDER BY u.unit_name ASC;
";
$result = mysqli_query($db, $query);
$count_row = mysqli_num_rows($result);
?>

<style>
/* Modern Tenant Card Design */
.tenant-card {
    border: none;
    border-radius: 15px;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
    transition: 0.3s;
    background: #fff;
    margin-bottom: 20px;
    padding: 20px;
    border: 1px solid #f0f2f5;
}

.tenant-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(0,0,0,0.1);
}

.tenant-header {
    display: flex;
    align-items: center;
    gap: 15px;
}

.tenant-circle-img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 50%;
    border: 3px solid #f0f2f5;
    flex-shrink: 0;
}

.tenant-main-info {
    flex-grow: 1;
    overflow: hidden;
}

.tenant-name {
    font-size: 18px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 2px;
    display: block;
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
}

.unit-label {
    font-size: 13px;
    color: #3b82f6;
    font-weight: 600;
}

.details-grid {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px dashed #eee;
}

.detail-item {
    font-size: 14px;
    color: #4b5563;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
}

.detail-item i {
    width: 20px;
    color: #9ca3af;
    font-size: 14px;
}

.badge-status {
    padding: 5px 12px;
    border-radius: 30px;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.bg-rented {
    background: #fee2e2;
    color: #dc2626;
}

.bg-available {
    background: #dcfce7;
    color: #16a34a;
}

.btn-action-group {
    display: flex;
    gap: 8px;
    margin-top: 15px;
}

.btn-action {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: 0.2s;
    text-decoration: none !important;
}

.btn-download { background: #f0f9ff; color: #0ea5e9; }
.btn-edit { background: #f5f3ff; color: #8b5cf6; }
.btn-delete { background: #fef2f2; color: #ef4444; }

.btn-action:hover {
    transform: scale(1.1);
}

@media(max-width:576px){
    .tenant-circle-img {
        width: 70px;
        height: 70px;
    }
    .tenant-name { font-size: 16px; }
}
</style>

<div class="nxl-content">

    <!-- Page Header -->
    <div class="page-header d-flex justify-content-between align-items-center mb-4">
        <div>
            <?php 
            $sql_building = "SELECT name FROM building WHERE id = $building_id_get";
            $result_building = mysqli_query($db, $sql_building);
            if($buil = mysqli_fetch_assoc($result_building)){
                echo '<h6 class="mb-0 fw-bold">'.$buil['name'].' <span class="badge bg-soft-primary text-primary ms-2" style="font-size: 12px;">'.$count_row.' Units</span></h6>';
            }
            ?>
        </div>
        <div>
            <a href="admin.php?page=CreateTenant&building_id=<?= $building_id_get; ?>" 
               class="btn btn-primary rounded-pill px-4 shadow-sm">
               <i class="feather-plus me-1"></i>
            </a>
        </div>
    </div>

    <?= $message ?>

    <!-- Tenant Grid -->
    <div class="row">
        <?php if(mysqli_num_rows($result) > 0): ?>
            <?php while($row = mysqli_fetch_assoc($result)): ?>
                <?php
                    $image = !empty($row['tenant_image']) 
                        ? "public/uploads/tenants/" . $row['tenant_image'] 
                        : "public/uploads/tenants/no-image.png";
                ?>
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="tenant-card">
                        
                        <!-- Header Section: Image Left, Name Right -->
                        <div class="tenant-header">
                            <a href="admin.php?page=view_tenant&id=<?= $row['id'] ?>"><img src="<?= htmlspecialchars($image) ?>" class="tenant-circle-img" alt="Tenant">
                            <div class="tenant-main-info">
                                <span class="tenant-name"><?= htmlspecialchars($row['name'] ?? 'Vacant Unit') ?></span>
                                <span class="unit-label">Unit: <?= htmlspecialchars($row['unit_name'] ?? '-') ?></span></a>
                                <div class="mt-1">
                                    <span class="badge-status <?= ($row['status']=='Rented') ? 'bg-rented' : 'bg-available'; ?>">
                                        <?= htmlspecialchars($row['status']) ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Info Section -->
                        <div class="details-grid">
                            <div class="detail-item">
                                <i class="feather-phone"></i>
                                <span><?= htmlspecialchars($row['phone'] ?? 'N/A') ?></span>
                            </div>
                            <div class="detail-item">
                                <i class="feather-map-pin"></i>
                                <span><?= htmlspecialchars($row['building_name'] ?? '-') ?></span>
                            </div>
                        </div>

                        <!-- Action Section -->
                        <div class="d-flex justify-content-between align-items-center mt-2">
                             <div class="btn-action-group">
                                <?php if($row['status'] == 'Rented'): ?>
                                    <a href="admin.php?page=Agreement&id=<?= $row['id'] ?>" class="btn-action btn-download" title="Agreement">
                                        <i class="feather-file-text"></i>
                                    </a>
                                <?php endif; ?>
                                
                                <a href="admin.php?page=CreateTenant&edit_id=<?= $row['id'] ?>" class="btn-action btn-edit" title="Edit">
                                    <i class="feather-edit"></i>
                                </a>
                                
                                <a href="admin.php?page=tenant&action=delete&delete_id=<?= $row['id'] ?>&building_id=<?= $building_id_get ?>" 
                                   class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete?');" title="Delete">
                                    <i class="feather-trash-2"></i>
                                </a>
                            </div>
                            <small class="text-muted" style="font-size: 11px;">ID: #<?= $row['id'] ?? '0' ?></small>
                        </div>

                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="text-center py-5 text-muted">
                    <i class="feather-users mb-3" style="font-size: 50px; opacity: 0.3;"></i>
                    <h5 class="mt-3">No Tenants Found</h5>
                    <p>No Tenant Found Hear</p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>