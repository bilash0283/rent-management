<?php
   if(isset($_GET['building_id'])){
        $building_id_get = $_GET['building_id'];
          // Fetch all units
        $query_sql  = "SELECT * FROM unit wHERE building_name = '$building_id_get' ORDER BY id DESC";
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

            // Unit back to Available
            mysqli_query($db, "UPDATE unit SET status='Available' WHERE id=".$row['unit_id']);
        }

        if (mysqli_query($db, "DELETE FROM tenants WHERE id=$id")) {
            $delete_advace = mysqli_query($db,"DELETE FROM `advance` WHERE tenant_id = $id ");
            $message = '
            <div class="alert alert-success alert-dismissible fade show mx-5 mt-2 mb-0">
                <strong>Success!</strong> Tenant Delete Successfully
                <button type="button" class="p-1 btn-close" data-bs-dismiss="alert"></button>
            </div>';
        } else {
            $message = '
            <div class="alert alert-danger alert-dismissible fade show mx-5 mt-2 mb-0">
                <strong>Error!</strong> '.mysqli_error($db).'
                <button type="button" class="p-1 btn-close" data-bs-dismiss="alert"></button>
            </div>';
        }
    }

    /* ================= FETCH TENANTS ================= */
    $query = "
       SELECT 
    u.*, 
    t.*, 
    b.name AS building_name
    FROM unit u
    JOIN building b ON u.building_name = b.id
    LEFT JOIN tenants t ON t.unit_id = u.id
    WHERE u.building_name = '$building_id_get'
    ;
    ";
    $result = mysqli_query($db, $query);
    $count_row = mysqli_num_rows($result);
?>

<div class="nxl-content">
    <!-- Page Header -->
    <div class="page-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">
            <?php 
                $sql_building = "SELECT * FROM building WHERE id = $building_id_get ";
                $result_building = mysqli_query($db, $sql_building) or die("Query failed: " . mysqli_error($db));
                while($buil = mysqli_fetch_assoc($result_building)){
                $buil_id   = $buil['id'];
                $buil_name = $buil['name'];
                $building_image = !empty($buil['image'])
                ? "public/uploads/buildings/" . $buil['image']
                : "public/uploads/tenants/no-image.png";
                }
            ?>
                <img src="<?= htmlspecialchars($building_image) ?>" style="width:50px; height:50px; object-fit:cover; border-radius:50%; border:2px solid #ddd;">
                <?= htmlspecialchars($buil_name) ?> - (<?= $count_row ?>)
        </h5>

        <a href="admin.php?page=CreateTenant&building_id=<?= $building_id_get; ?>" class="p-1 btn btn-primary">
            <i class="feather-plus me-1"></i> Create Tenant
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="card shadow-sm">

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Image</th>
                                <th>Unit</th>
                                <th>Name</th>
                                <th>Personal Info</th>
                                <th>Files</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>

                                <?php
                                    $image = !empty($row['tenant_image'])
                                    ? "public/uploads/tenants/" . $row['tenant_image']
                                    : "public/uploads/tenants/no-image.png";
                                ?>

                                <tr>
                                    <td style="text-align:center;">
                                        <div style="display:flex; flex-direction:column; align-items:center; gap:6px;">
                                            <img src="<?= htmlspecialchars($image) ?>"
                                                style="width:50px; height:50px; object-fit:cover; border-radius:50%; border:2px solid #ddd;">

                                            <small style="
                                                font-size:8px;
                                                background:#17a2b8;
                                                color:#fff;
                                                padding:1px 5px;
                                                border-radius:12px;
                                                display:inline-block;
                                            ">
                                                <?= (!empty($row['start_tanent']) && $row['start_tanent'] != '0000-00-00') 
                                                    ? date('d-M-y', strtotime($row['start_tanent'])) 
                                                    : 'N/A'; ?>
                                            </small>
                                        </div>
                                    </td>

                                    <td style="text-align:center;">
                                        <div style="display:flex; flex-direction:column; align-items:center; line-height:1.2;">
                                            
                                            <strong style="
                                                font-size:8px;
                                                background:#17a2b8;
                                                color:#fff;
                                                padding:3px 8px;
                                                border-radius:12px;
                                                display:inline-block;
                                            ">
                                                <?= htmlspecialchars($row['unit_name']) ?>
                                            </strong>

                                            <small style="
                                                font-size:11px;
                                                color:#777;
                                                margin-top:2px;
                                            ">
                                                <?= htmlspecialchars($row['building_name']) ?>
                                            </small>

                                        </div>
                                    </td>

                                    <td>
                                        <a href="admin.php?page=view_tenant&id=<?= $row['id'] ?>" class="text-secendary fw-bold"><?= htmlspecialchars($row['name']) ?>
                                        </a><br>
                                        <?= htmlspecialchars($row['phone']) ?>
                                    </td>

                                    <td>
                                        Nid - <?= htmlspecialchars($row['nid_no']); ?> <br>
                                        Address - <?= htmlspecialchars($row['permanent_address']); ?>
                                    </td>

                                    <td>
                                        <a href="admin.php?page=Agreement&id=<?= $row['id'] ?>"
                                            class="p-1 btn btn-sm btn-info "
                                            title="Agreement Download">
                                                <i class="feather-download"></i>
                                        </a>
                                    </td>

                                    <td>
                                        <span class="badge p-1 <?= $row['status']=='Rented' ? 'bg-danger' : 'bg-success' ?>">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="btn-group ">
                                            <a href="admin.php?page=CreateTenant&edit_id=<?= $row['id'] ?>"
                                            class="p-1 btn btn-sm btn-primary"
                                            title="Edit">
                                                <i class="feather-edit"></i>
                                            </a>

                                            <a href="admin.php?page=tenant&action=delete&delete_id=<?= $row['id'] ?>&building_id=<?= $building_id_get ?>"
                                            class="p-1 btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure?');"
                                            title="Delete">
                                                <i class="feather-trash-2"></i>
                                            </a>
                                        </div>
                                    </td>

                                </tr>

                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No tenants found
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>

                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
