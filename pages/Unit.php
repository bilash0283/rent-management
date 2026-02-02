<?php 
    if(isset($_GET['id'])){
        $building_id = $_GET['id'];
          // Fetch all units
        $query  = "SELECT * FROM unit wHERE building_name = '$building_id' ORDER BY id DESC";
        $result = mysqli_query($db, $query);

        if (!$result) {
            die("Query Failed: " . mysqli_error($db));
        }

    }

    $message = "";

    if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['delete_id'])) {

        $id = (int) $_GET['delete_id'];

        $sql = "SELECT unit_image FROM unit WHERE id = $id";
        $result = mysqli_query($db, $sql);

        if ($result && $row = mysqli_fetch_assoc($result)) {

            if (!empty($row['unit_image'])) {
                $file = "public/uploads/units/" . $row['unit_image'];
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        }

        $delete_query = "DELETE FROM unit WHERE id = $id";

        if (mysqli_query($db, $delete_query)) {
            
            $message = '
            <div class="alert alert-success alert-dismissible fade show mx-5 mt-2 mb-0" role="alert">
                <strong>Success!</strong> Unit Delete Successfull 
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        } else {
            $message = '
            <div class="alert alert-danger alert-dismissible fade show mx-5 mt-2 mb-0" role="alert">
                <strong>Error!</strong> Delete Failed : ' . mysqli_error($db) . '
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
        }
    }

?>

<div class="nxl-content">

    <!-- Page Header -->
    <div class="page-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">
            <?php 
                $sql_building = "SELECT * FROM building WHERE id = $building_id ";
                $result_building = mysqli_query($db, $sql_building) or die("Query failed: " . mysqli_error($db));
                while($buil = mysqli_fetch_assoc($result_building)){
                $buil_id   = $buil['id'];
                $buil_name = $buil['name'];
                }
            ?>
            <?= htmlspecialchars($buil_name) ?>
        </h5>

        <a href="admin.php?page=CreateUnit&buliding_id=<?php echo $building_id; ?>" class="btn btn-primary">
            <i class="feather-plus me-1"></i> Create Unit
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content mt-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">Unit List</h6>
                
                <?php if(isset($message)){echo $message;} ?>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Image</th>
                                <th>Unit Name</th>
                                <th>Floor</th>
                                <th>Rent</th>
                                <th>Bill</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>

                        <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>

                                <?php
                                    $image = !empty($row['unit_image'])
                                        ? "public/uploads/units/" . $row['unit_image']
                                        : "assets/images/no-image.png";
                                ?>

                                <tr>
                                    <td>
                                        <img src="<?= htmlspecialchars($image) ?>"
                                             width="60" height="60"
                                             style="object-fit:cover;border-radius:6px;">
                                    </td>

                                    <td><?= htmlspecialchars($row['unit_name']) ?></td>
                                    <td>
                                        <?php 
                                            $building_id = $row['building_name'];
                                            $sql_building = "SELECT * FROM building WHERE id = $building_id ";
                                            $result_building = mysqli_query($db, $sql_building) or die("Query failed: " . mysqli_error($db));
                                            while($buil = mysqli_fetch_assoc($result_building)){
                                            $buil_id   = $buil['id'];
                                            $buil_name = $buil['name'];
                                            }
                                        ?>
                                        <?= htmlspecialchars($buil_name) ?>
                                    </td>
                                    <td><?= htmlspecialchars($row['floor'] ?? '-') ?></td>
                                    <td>৳ <?= number_format($row['rent'], 2) ?></td>

                                    <td>
                                        <span class="badge <?= $row['status']=='Available' ? 'bg-success' : 'bg-danger' ?>">
                                            <?= $row['status'] ?>
                                        </span>
                                    </td>

                                    <td class="text-end">
                                        <a href="admin.php?page=CreateUnit&edit_id=<?= $row['id'] ?>"
                                           class="btn btn-sm btn-light-primary">
                                            <i class="feather-edit"></i>
                                        </a>

                                        <a href="admin.php?page=unit&id=<?php echo $building_id; ?>&action=delete&delete_id=<?= $row['id']; ?>"
                                           class="btn btn-sm btn-light-danger"
                                           onclick="return confirm('Are you sure?');">
                                            <i class="feather-trash-2"></i>
                                        </a>
                                    </td>
                                </tr>

                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No units found
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

