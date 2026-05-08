<?php
$message = '';

// ==============================
// DELETE BUILDING + RELATED UNITS
// ==============================
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {

    $id = (int) $_GET['id'];

    // Get building image
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

    // Delete units images first
    $sql_unit = "SELECT unit_image FROM unit WHERE building_name = $id";
    $result_unit = mysqli_query($db, $sql_unit);

    while ($result_unit && $unit_img = mysqli_fetch_assoc($result_unit)) {

        if (!empty($unit_img['unit_image'])) {

            $unit_file = "public/uploads/units/" . $unit_img['unit_image'];

            if (file_exists($unit_file)) {
                unlink($unit_file);
            }
        }
    }

    // Delete units
    mysqli_query($db, "DELETE FROM unit WHERE building_name = $id");

    // Delete building
    $delete_query = "DELETE FROM building WHERE id = $id";

    if (mysqli_query($db, $delete_query)) {

        $message = '
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            <strong>Success!</strong> Building deleted successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';

    } else {

        $message = '
        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
            <strong>Error!</strong> Delete Failed : ' . mysqli_error($db) . '
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }
}


// ==============================
// FETCH BUILDINGS
// ==============================
$sql = "SELECT * FROM building ORDER BY id DESC";
$result = mysqli_query($db, $sql) or die("Query Failed : " . mysqli_error($db));
?>

<style>
    .mobile-building-card {
        border: none;
        border-radius: 18px;
        overflow: hidden;
        background: #fff;
        box-shadow: 0 2px 15px rgba(0,0,0,0.06);
        transition: 0.3s;
        height: 100%;
    }

    .mobile-building-card:hover {
        transform: translateY(-3px);
    }

    .building-image {
        width: 100%;
        height: 200px;
        object-fit: cover;
    }

    .building-body {
        padding: 15px;
    }

    .building-title {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 8px;
    }

    .building-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 30px;
        font-size: 12px;
        font-weight: 600;
        background: #eef2ff;
        color: #4338ca;
        margin-bottom: 12px;
    }

    .building-text {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 8px;
        line-height: 1.6;
    }

    .building-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }

    .building-actions a {
        flex: 1;
        border-radius: 12px;
        padding: 10px;
        font-size: 14px;
        font-weight: 600;
    }

    .top-header-mobile {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .create-btn-mobile {
        border-radius: 12px;
        padding: 10px 16px;
        font-size: 14px;
        font-weight: 600;
    }

    .location-btn {
        text-decoration: none;
        color: #2563eb;
        font-weight: 600;
    }

    @media(max-width: 768px) {
        .building-image {
            height: 180px;
        }

        .building-title {
            font-size: 16px;
        }
    }
</style>

<div class="nxl-content">

    <!-- HEADER -->
    <div class="top-header-mobile">

        <div>
            <h4 class="mb-1 fw-bold">Building Manage</h4>
            <small class="text-muted">All Buildings List</small>
        </div>

        <a href="admin.php?page=CreateBuilding" class="btn btn-primary create-btn-mobile">
            <i class="feather-plus"></i>
        </a>

    </div>

    <!-- MESSAGE -->
    <?= $message ?>

    <!-- BUILDING GRID -->
    <div class="row g-3">

        <?php if (mysqli_num_rows($result) > 0): ?>

            <?php while ($row = mysqli_fetch_assoc($result)): ?>

                <?php

                $img_path = !empty($row['image'])
                    ? "public/uploads/buildings/" . htmlspecialchars($row['image'])
                    : "public/uploads/users/no-image.png";

                // Building Type
                switch ($row['building_type']) {

                    case 1:
                        $type = 'Residential';
                        break;

                    case 2:
                        $type = 'Commercial';
                        break;

                    case 3:
                        $type = 'Industrial';
                        break;

                    case 4:
                        $type = 'Institutional';
                        break;

                    case 5:
                        $type = 'Residential & Commercial';
                        break;

                    default:
                        $type = 'Unknown';
                }

                ?>

                <div class="col-12">

                    <div class="mobile-building-card">

                        <!-- IMAGE -->
                        <img src="<?= $img_path ?>"
                             class="building-image"
                             alt="Building">

                        <!-- BODY -->
                        <div class="building-body">

                            <div class="building-title">
                                <?= htmlspecialchars($row['name']) ?>
                            </div>

                            <div class="building-badge">
                                <?= $type ?>
                            </div>

                            <div class="building-text">
                                <strong>Address:</strong><br>
                                <?= htmlspecialchars($row['address'] ?? 'N/A') ?>
                            </div>

                            <div class="building-text">
                                <strong>Description:</strong><br>

                                <?php
                                $desc = htmlspecialchars($row['description'] ?? '');

                                echo strlen($desc) > 120
                                    ? substr($desc, 0, 120) . '...'
                                    : $desc;
                                ?>
                            </div>

                            <?php if (!empty($row['location'])): ?>

                                <div class="mt-2">
                                    <a href="<?= htmlspecialchars($row['location']) ?>"
                                       target="_blank"
                                       class="location-btn">

                                        <i class="feather-map-pin"></i>
                                        View Location
                                    </a>
                                </div>

                            <?php endif; ?>

                            <!-- ACTION BUTTONS -->
                            <div class="building-actions">

                                <!-- EDIT -->
                                <a href="admin.php?page=CreateBuilding&id=<?= $row['id'] ?>"
                                   class="btn btn-primary">

                                    <i class="feather-edit-2"></i>
                                    Edit
                                </a>

                                <!-- DELETE -->
                                <a href="admin.php?page=building&action=delete&id=<?= $row['id'] ?>"
                                   class="btn btn-danger"
                                   onclick="return confirm('Are you sure?\n\nThis action cannot be undone.')">

                                    <i class="feather-trash-2"></i>
                                    Delete
                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="col-12">

                <div class="card border-0 shadow-sm rounded-4">

                    <div class="card-body text-center py-5">

                        <img src="public/uploads/users/no-image.png"
                             width="80"
                             class="mb-3 opacity-50">

                        <h5 class="fw-bold mb-2">
                            No Buildings Found
                        </h5>

                        <p class="text-muted mb-0">
                            Please create a new building.
                        </p>

                    </div>

                </div>

            </div>

        <?php endif; ?>

    </div>

</div>