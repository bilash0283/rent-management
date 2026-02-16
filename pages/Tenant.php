<?php
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
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    } else {
        $message = '
        <div class="alert alert-danger alert-dismissible fade show mx-5 mt-2 mb-0">
            <strong>Error!</strong> '.mysqli_error($db).'
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }
}

/* ================= FETCH TENANTS ================= */
$query = "
    SELECT 
        t.*, 
        b.name AS building_name,
        u.unit_name,
        u.status
    FROM tenants t
    JOIN building b ON t.building_id = b.id
    JOIN unit u ON t.unit_id = u.id
    ORDER BY t.id DESC
";
$result = mysqli_query($db, $query);
?>

<div class="nxl-content">

    <!-- Page Header -->
    <div class="page-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Tenant Manage</h5>

        <a href="admin.php?page=CreateTenant" class="btn btn-primary">
            <i class="feather-plus me-1"></i> Create Tenant
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">Tenant List</h6>
                <?= $message ?>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Image</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Building</th>
                                <th>Unit</th>
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
                                    <td>
                                        <img src="<?= htmlspecialchars($image) ?>"
                                             width="50" height="50"
                                             style="object-fit:cover;border-radius:6px;border-radius:50%;">
                                    </td>

                                    <td><a href="admin.php?page=view_tenant&id=<?= $row['id'] ?>" class="text-secendary fw-bold"><?= htmlspecialchars($row['name']) ?></a></td>
                                    <td><?= htmlspecialchars($row['phone']) ?></td>
                                    <td><?= htmlspecialchars($row['building_name']) ?></td>
                                    <td><?= htmlspecialchars($row['unit_name']) ?></td>

                                    <td>
                                        <span class="badge <?= $row['status']=='Rented' ? 'bg-danger' : 'bg-success' ?>">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>

                                    <td>
                                        <div class="btn-group ">
                                            <a href="admin.php?page=Agreement&id=<?= $row['id'] ?>"
                                            class="btn btn-sm btn-light-info "
                                            title="View">
                                                <i class="feather-download"></i>
                                            </a>

                                            <a href="admin.php?page=CreateTenant&edit_id=<?= $row['id'] ?>"
                                            class="btn btn-sm btn-light-primary"
                                            title="Edit">
                                                <i class="feather-edit"></i>
                                            </a>

                                            <a href="admin.php?page=tenant&action=delete&delete_id=<?= $row['id'] ?>"
                                            class="btn btn-sm btn-light-danger"
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
