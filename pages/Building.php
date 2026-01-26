<?php
$message = '';

if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {

    $id = (int) $_GET['id'];

    $sql = "SELECT image FROM building WHERE id = $id";
    $result = mysqli_query($db, $sql);

    if ($result && $row = mysqli_fetch_assoc($result)) {

        if (!empty($row['image'])) {
            $file = "public/uploads/buildings/" . $row['image'];
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    $delete_query = "DELETE FROM building WHERE id = $id";

    if (mysqli_query($db, $delete_query)) {

        $message = '
        <div class="alert alert-success alert-dismissible fade show mx-5 mt-2 mb-0" role="alert">
            <strong>Success!</strong> Building Delete Successfull 
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

// Fetch all buildings
$sql = "SELECT * FROM building ORDER BY id DESC";
$result = mysqli_query($db, $sql) or die("Query failed: " . mysqli_error($db));
?>

<div class="nxl-content">

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-header-left d-flex align-items-center">
            <div class="page-header-title">
                <h5 class="m-b-10">Building Manage</h5>
            </div>
        </div>
        <div class="page-header-right ms-auto">
            <div class="page-header-right-items">
                <div class="d-flex d-md-none">
                    <a href="javascript:void(0)" class="page-header-right-close-toggle">
                        <i class="feather-arrow-left me-2"></i>
                        <span>Back</span>
                    </a>
                </div>
                <div class="d-flex align-items-center gap-2 page-header-right-items-wrapper">
                    <a href="javascript:void(0);" class="btn btn-icon btn-light-brand" data-bs-toggle="collapse"
                        data-bs-target="#collapseOne">
                        <i class="feather-bar-chart"></i>
                    </a>
                    <a href="admin.php?page=CreateBuilding" class="btn btn-primary">
                        <i class="feather-plus me-2"></i>
                        <span>Create Building</span>
                    </a>
                </div>
            </div>
            <div class="d-md-none d-flex align-items-center">
                <a href="javascript:void(0)" class="page-header-right-open-toggle">
                    <i class="feather-align-right fs-20"></i>
                </a>
            </div>
        </div>
    </div>

    <!-- Filter collapse (placeholder) -->
    <div id="collapseOne" class="accordion-collapse collapse page-header-collapse">
        <div class="accordion-body pb-2">
            <div class="row">
                <div class="col-12">
                    <h6>Filter Section (Coming Soon)</h6>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="row">
            <div class="col-xxl-12">
                <div class="card stretch stretch-full">
                    <div class="card-header">
                        <h5 class="card-title">Buildings List</h5>
                        <!-- Show message if any -->
                        <?php if ($message !== ''): ?>
                            <?= $message ?>
                        <?php endif; ?>
                    </div>

                    <div class="card-body custom-card-action p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Image</th>
                                        <th scope="col">Name</th>
                                        <th scope="col">Building Type</th>
                                        <th scope="col">Address</th>
                                        <th scope="col">Description</th>
                                        <th scope="col">Location</th>
                                        <th scope="col" class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($result) > 0): ?>
                                        <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td>
                                                    <?php
                                                    $img_path = !empty($row['image'])
                                                        ? "public/uploads/buildings/" . htmlspecialchars($row['image'])
                                                        : "assets/images/no-image.png";
                                                    ?>
                                                    <img src="<?= $img_path ?>" alt="Building Image" class="rounded"
                                                        style="width: 60px; height: 60px; object-fit: cover;">
                                                </td>
                                                <td>
                                                    <strong><?= htmlspecialchars($row['name']) ?></strong>
                                                </td>
                                                <td>
                                                    <?php
                                                    $type = $row['building_type'] ?? null;

                                                    switch ($type) {
                                                        case 1:
                                                            echo 'Residential';
                                                            break;
                                                        case 2:
                                                            echo 'Commercial';
                                                            break;
                                                        case 3:
                                                            echo 'Industrial';
                                                            break;
                                                        case 4:
                                                            echo 'Institutional';
                                                            break;
                                                        default:
                                                            echo '—';
                                                            break;
                                                    }
                                                    ?>
                                                </td>
                                                <td><?= htmlspecialchars($row['address'] ?? '—') ?></td>
                                                <td>
                                                    <?php
                                                    $desc = htmlspecialchars($row['description'] ?? '');
                                                    echo strlen($desc) > 80 ? substr($desc, 0, 80) . '...' : $desc;
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php if (!empty($row['location'])): ?>
                                                        <a href="<?= htmlspecialchars($row['location']) ?>" target="_blank">
                                                            <i class="feather-eye"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        —
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex justify-content-end gap-2">
                                                        <!-- Edit Button -->
                                                        <a href="admin.php?page=CreateBuilding&id=<?= $row['id'] ?>"
                                                            class="btn btn-sm btn-icon btn-light-primary"
                                                            title="Edit this building" data-bs-toggle="tooltip"
                                                            data-bs-placement="top">
                                                            <i class="feather-edit-2"></i>
                                                        </a>

                                                        <!-- Delete Button -->
                                                        <a href="admin.php?page=building&action=delete&id=<?= $row['id'] ?>"
                                                            class="btn btn-sm btn-icon btn-light-danger delete-btn"
                                                            onclick="return confirm('Are you sure you want to delete this building?\n\nThis action cannot be undone.');"
                                                            title="Delete this building" data-bs-toggle="tooltip"
                                                            data-bs-placement="top">
                                                            <i class="feather-trash-2"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="text-center py-5 text-muted">
                                                No buildings found.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination (placeholder - implement real pagination later) -->
                    <div class="card-footer">
                        <ul
                            class="list-unstyled d-flex align-items-center gap-2 mb-0 pagination-common-style justify-content-center">
                            <li><a href="javascript:void(0);"><i class="bi bi-arrow-left"></i></a></li>
                            <li><a href="javascript:void(0);" class="active">1</a></li>
                            <li><a href="javascript:void(0);">2</a></li>
                            <li><a href="javascript:void(0);">3</a></li>
                            <li><a href="javascript:void(0);"><i class="bi bi-arrow-right"></i></a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>