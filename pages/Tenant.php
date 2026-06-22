<?php
    if(isset($_GET['status'])){
        $status = $_GET['status'];
    }else {
         $status = 'Active';
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
            mysqli_query($db, "DELETE FROM payment_history WHERE tenant_id=$id");
            mysqli_query($db, "DELETE FROM invoices WHERE tenant_id=$id");
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
    $query = "SELECT * FROM tenants WHERE ";
    if(isset($_POST['filter_btn'])){
        if($_POST['select_building'] != 'all'){
            $select_building = $_POST['select_building'];
            $query = $query."building_id = '$select_building' AND ";
        }
    }
    $query =  $query ." status = '$status' ORDER BY unit_id ASC ";
    $result = mysqli_query($db, $query);
    $count_row = mysqli_num_rows($result);
?>

<div class="nxl-content">

    <!-- Page Header -->
    <div class="page-header d-flex align-items-center justify-content-between">
        <form action="" method="POST" class="d-flex align-items-center justify-content-between gap-3">
            <select name="select_building" id="select_building" class="form-control custom-select">
                <option value="all" selected>All Building</option>
                <?php 
                    $sql_building = "SELECT * FROM building  ";
                    $result_building = mysqli_query($db, $sql_building) or die("Query failed: " . mysqli_error($db));
                    while($buil = mysqli_fetch_assoc($result_building)){
                    $buil_id   = $buil['id'];
                    $buil_name = $buil['name'];
                ?>
                <option value="<?php echo $buil_id; ?>" <?php if(isset($_POST['select_building']) && $_POST['select_building'] == $buil_id){echo "selected";} ?>><?php echo $buil_name; ?></option>
                <?php } ?>
            </select>
            <button type="submit" class="btn btn-success" name="filter_btn">Filter</button>
        </form>

        <a href="admin.php?page=CreateTenant" class="btn btn-primary">
            <i class="feather-plus me-1"></i> Tenant
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><?= $status; ?> Tenant List - <?= $count_row; ?></h6>
                <?= $message ?>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Image</th>
                                <th>Unit</th>
                                <th>Name</th>
                                <th>Phone</th>
                                <th>Building</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): 
                                    $unit_id = $row['unit_id'];
                                    $building_id = $row['building_id'];
                                ?>
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

                                    <td>
                                        <?php 
                                            $unit = mysqli_query($db,"SELECT unit_name FROM unit WHERE id = '$unit_id' ");
                                            $unit_row = mysqli_fetch_assoc($unit);
                                            echo $unit_row['unit_name'];
                                        ?>
                                    </td>

                                    <td><a href="admin.php?page=view_tenant&id=<?= $row['id'] ?>" class="text-secendary fw-bold"><?= htmlspecialchars($row['name']) ?></a></td>
                                    <td><?= htmlspecialchars($row['phone']) ?></td>

                                    <td>
                                        <?php
                                            $building = mysqli_query($db,"SELECT name FROM building WHERE id = '$building_id' ");
                                            $building_row = mysqli_fetch_assoc($building);
                                            echo $building_row['name'];
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                            $statusClass = '';

                                            if($row['status'] == 'Active'){
                                                $statusClass = 'bg-success';
                                            }elseif($row['status'] == 'Inactive'){
                                                $statusClass = 'bg-danger';
                                            }elseif($row['status'] == 'Booked'){
                                                $statusClass = 'bg-info';
                                            }else{
                                                // $statusClass = 'bg-secondary';
                                            }
                                            ?>

                                            <span class="badge <?= $statusClass; ?>">
                                                <?= $row['status']; ?>
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

                                            <a href="admin.php?page=status_change&edit_id=<?= $row['id'] ?>"
                                            class="btn btn-sm btn-light-info"
                                            title="Status Change">
                                                <i class="feather-toggle-left"></i>
                                            </a>

                                            <a href="admin.php?page=tenant&status=<?php echo $status; ?>&action=delete&delete_id=<?= $row['id'] ?>"
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
                                    <?= $status; ?> Tenants Not Found
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
