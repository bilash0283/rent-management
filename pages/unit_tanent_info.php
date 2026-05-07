<?php
   if(isset($_GET['building_id'])){
        $building_id_get = mysqli_real_escape_string($db, $_GET['building_id']);
          // Fetch all units
        $query_sql  = "SELECT * FROM unit WHERE building_name = '$building_id_get' ORDER BY id DESC";
        $result_bul = mysqli_query($db, $query_sql);

        if (!$result_bul) {
            die("Query Failed: " . mysqli_error($db));
        }
    }

    $message = "";

    /* ================= DELETE TENANT ================= */
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
            mysqli_query($db, "UPDATE unit SET status='Available' WHERE id=".$row['unit_id']);
        }

        if (mysqli_query($db, "DELETE FROM tenants WHERE id=$id")) {
            mysqli_query($db,"DELETE FROM `advance` WHERE tenant_id = $id ");
            $message = '<div class="alert alert-success mx-5 mt-2"><strong>Success!</strong> Tenant Deleted Successfully</div>';
        }
    }

    /* ================= FETCH DATA ================= */
    $query = "SELECT u.*, t.*, b.name AS building_name, u.unit_name as u_name 
              FROM unit u
              JOIN building b ON u.building_name = b.id
              LEFT JOIN tenants t ON t.unit_id = u.id
              WHERE u.building_name = '$building_id_get'";
    $result = mysqli_query($db, $query);
    $count_row = mysqli_num_rows($result);

    // Fetch Building Info for Header
    $sql_building = "SELECT * FROM building WHERE id = '$building_id_get'";
    $res_b = mysqli_query($db, $sql_building);
    $buil = mysqli_fetch_assoc($res_b);
    $buil_name = $buil['name'] ?? 'Unknown';
    $building_image = !empty($buil['image']) ? "public/uploads/buildings/".$buil['image'] : "public/uploads/tenants/no-image.png";
?>

<style>
    /* মোবাইল ভিউর জন্য বিশেষ স্টাইল */
    @media (max-width: 768px) {
        .desktop-table { display: none; }
        .mobile-card-view { display: block; }
        .tenant-card {
            background: #fff;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 5px solid #17a2b8;
        }
        .action-btns {
            display: flex;
            gap: 10px;
            margin-top: 10px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }
    }
    @media (min-width: 769px) {
        .mobile-card-view { display: none; }
        .desktop-table { display: block; }
    }
</style>

<div class="nxl-content">
    <?= $message; ?>
    
    <!-- Page Header -->
    <div class="page-header d-flex align-items-center justify-content-between p-3">
        <h5 class="mb-0 d-flex align-items-center">
            <img src="<?= htmlspecialchars($building_image) ?>" style="width:40px; height:40px; object-fit:cover; border-radius:50%; margin-right:10px;">
            <span><?= htmlspecialchars($buil_name) ?> <small class="badge bg-secondary ms-1"><?= $count_row ?></small></span>
        </h5>
        <a href="admin.php?page=CreateTenant&building_id=<?= $building_id_get; ?>" class="btn btn-primary btn-sm">
            <i class="feather-plus"></i> <span class="d-none d-sm-inline">Create Tenant</span>
        </a>
    </div>

    <div class="main-content p-2">
        
        <!-- DESKTOP TABLE VIEW -->
        <div class="card desktop-table shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Tenant</th>
                            <th>Unit Info</th>
                            <th>Contact & Address</th>
                            <th>Status</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php mysqli_data_seek($result, 0); 
                        while ($row = mysqli_fetch_assoc($result)): 
                            $t_img = !empty($row['tenant_image']) ? "public/uploads/tenants/".$row['tenant_image'] : "public/uploads/tenants/no-image.png";
                        ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= $t_img ?>" style="width:45px; height:45px; border-radius:50%; object-fit:cover;">
                                    <div>
                                        <a href="admin.php?page=view_tenant&id=<?= $row['id'] ?>" class="fw-bold d-block text-dark"><?= htmlspecialchars($row['name'] ?? 'N/A') ?></a>
                                        <small class="text-muted"><?= $row['start_tanent'] != '0000-00-00' ? date('d M, Y', strtotime($row['start_tanent'])) : '' ?></small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-info mb-1"><?= htmlspecialchars($row['unit_name']) ?></span><br>
                                <small class="text-muted"><?= htmlspecialchars($row['building_name']) ?></small>
                            </td>
                            <td>
                                <i class="feather-phone-call small"></i> <?= htmlspecialchars($row['phone'] ?? '') ?><br>
                                <small class="text-muted"><i class="feather-map-pin small"></i> <?= htmlspecialchars($row['permanent_address'] ?? '') ?></small>
                            </td>
                            <td>
                                <span class="badge <?= $row['status']=='Rented' ? 'bg-danger' : 'bg-success' ?>"><?= $row['status'] ?></span>
                            </td>
                            <td class="text-end">
                                <div class="btn-group">
                                    <a href="admin.php?page=Agreement&id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-secondary"><i class="feather-download"></i></a>
                                    <a href="admin.php?page=CreateTenant&edit_id=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary"><i class="feather-edit"></i></a>
                                    <a href="admin.php?page=tenant&action=delete&delete_id=<?= $row['id'] ?>&building_id=<?= $building_id_get ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this tenant?');"><i class="feather-trash-2"></i></a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- MOBILE CARD VIEW -->
        <div class="mobile-card-view">
            <?php mysqli_data_seek($result, 0); 
            if(mysqli_num_rows($result) > 0):
                while ($row = mysqli_fetch_assoc($result)): 
                    $t_img = !empty($row['tenant_image']) ? "public/uploads/tenants/".$row['tenant_image'] : "public/uploads/tenants/no-image.png";
            ?>
            <div class="tenant-card">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="d-flex align-items-center gap-3">
                        <img src="<?= $t_img ?>" style="width:55px; height:55px; border-radius:50%; object-fit:cover; border: 2px solid #eee;">
                        <div>
                            <h6 class="mb-0"><?= htmlspecialchars($row['name'] ?? 'Vacant Unit') ?></h6>
                            <small class="text-muted"><?= htmlspecialchars($row['phone'] ?? 'No Phone') ?></small>
                        </div>
                    </div>
                    <span class="badge <?= $row['status']=='Rented' ? 'bg-danger' : 'bg-success' ?>"><?= $row['status'] ?></span>
                </div>
                
                <div class="row g-0 py-2 border-top border-bottom my-2" style="font-size: 13px;">
                    <div class="col-6"><strong>Unit:</strong> <?= htmlspecialchars($row['unit_name']) ?></div>
                    <div class="col-6 text-end"><strong>ID:</strong> <?= htmlspecialchars($row['nid_no'] ?? 'N/A') ?></div>
                    <div class="col-12 mt-1 text-muted">
                        <i class="feather-calendar"></i> Joined: <?= ($row['start_tanent'] && $row['start_tanent'] != '0000-00-00') ? date('d M, Y', strtotime($row['start_tanent'])) : 'N/A' ?>
                    </div>
                </div>

                <div class="action-btns">
                    <a href="admin.php?page=view_tenant&id=<?= $row['id'] ?>" class="btn btn-sm btn-light flex-grow-1 border"><i class="feather-eye"></i> View</a>
                    <a href="admin.php?page=CreateTenant&edit_id=<?= $row['id'] ?>" class="btn btn-sm btn-primary flex-grow-1"><i class="feather-edit"></i> Edit</a>
                    <a href="admin.php?page=tenant&action=delete&delete_id=<?= $row['id'] ?>&building_id=<?= $building_id_get ?>" class="btn btn-sm btn-danger px-3" onclick="return confirm('Are you sure?');"><i class="feather-trash-2"></i></a>
                </div>
            </div>
            <?php endwhile; else: ?>
                <div class="text-center p-5 bg-white rounded">No tenants found</div>
            <?php endif; ?>
        </div>

    </div>
</div>