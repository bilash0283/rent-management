<?php
if(isset($_GET['id'])){
    $building_id = $_GET['id'];

    // Fetch all units
    $query  = "SELECT * FROM unit WHERE building_name = '$building_id' ORDER BY unit_name ASC";
    $result = mysqli_query($db, $query);

    if (!$result) {
        die("Query Failed: " . mysqli_error($db));
    }

    $count_row = mysqli_num_rows($result);
}

$message = "";

// Delete Unit
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['delete_id'])) {

    $id = (int) $_GET['delete_id'];

    $sql = "SELECT unit_image FROM unit WHERE id = $id";
    $result_img = mysqli_query($db, $sql);

    if ($result_img && $row_img = mysqli_fetch_assoc($result_img)) {

        if (!empty($row_img['unit_image'])) {

            $file = "public/uploads/units/" . $row_img['unit_image'];

            if (file_exists($file)) {
                unlink($file);
            }
        }
    }

    $delete_query = "DELETE FROM unit WHERE id = $id";

    if (mysqli_query($db, $delete_query)) {

        $message = '
        <div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
            <strong>Success!</strong> Unit Deleted Successfully.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';

    } else {

        $message = '
        <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
            <strong>Error!</strong> Delete Failed : '.mysqli_error($db).'
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>';
    }
}
?>

<style>

.unit-card{
    border: none;
    border-radius: 18px;
    overflow: hidden;
    transition: 0.3s;
    background: #fff;
    box-shadow: 0 4px 14px rgba(0,0,0,0.06);
    height: 100%;
}

.unit-card:hover{
    transform: translateY(-3px);
    box-shadow: 0 10px 24px rgba(0,0,0,0.12);
}

.unit-image{
    width: 100%;
    height: 220px;
    object-fit: cover;
}

.unit-body{
    padding: 16px;
}

.unit-title{
    font-size: 18px;
    font-weight: 700;
    color: #111827;
    margin-bottom: 10px;
}

.unit-info{
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 6px;
}

.unit-price{
    background: #f9fafb;
    padding: 12px;
    border-radius: 12px;
    margin-top: 12px;
    margin-bottom: 12px;
}

.unit-price div{
    display: flex;
    justify-content: space-between;
    margin-bottom: 5px;
    font-size: 14px;
}

.unit-footer{
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 15px;
}

.badge-status{
    padding: 8px 14px;
    border-radius: 30px;
    font-size: 12px;
    font-weight: 600;
}

.bg-available{
    background: #dcfce7;
    color: #166534;
}

.bg-booked{
    background: #fee2e2;
    color: #991b1b;
}

.action-btn{
    width: 38px;
    height: 38px;
    border-radius: 10px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

@media(max-width:768px){

    .page-header{
        flex-direction: column;
        align-items: start !important;
        gap: 12px;
    }

    .unit-image{
        height: 190px;
    }

    .unit-title{
        font-size: 16px;
    }
}

</style>

<div class="nxl-content">

    <!-- Page Header -->
  <div class="page-header mb-4">

    <div class="d-flex justify-content-between align-items-center w-100">

        <!-- Left -->
        <div>

            <?php
            $sql_building = "SELECT * FROM building WHERE id = $building_id";
            $result_building = mysqli_query($db, $sql_building);

            while($buil = mysqli_fetch_assoc($result_building)){
            ?>

                <h5 class="mb-0 fw-bold d-flex align-items-center gap-2">

                    <span><?= $buil['name']; ?></span>

                    <span class="badge bg-light text-dark rounded-pill px-3 py-2">
                        <?= $count_row; ?>
                    </span>

                </h5>

            <?php } ?>

        </div>

        <!-- Right -->
        <div>

            <a href="admin.php?page=CreateUnit&building_id=<?php echo $building_id; ?>"
               class="btn btn-primary rounded-circle d-flex align-items-center justify-content-center"
               style="width:45px;height:45px;">

                <i class="feather-plus"></i>

            </a>

        </div>

    </div>

</div>

    <?php echo $message; ?>

    <!-- GRID VIEW -->
    <div class="row g-4">

        <?php if(mysqli_num_rows($result) > 0): ?>

            <?php while($row = mysqli_fetch_assoc($result)): ?>

                <?php

                $image = !empty($row['unit_image'])
                    ? "public/uploads/units/".$row['unit_image']
                    : "public/assets/images/no-image.png";

                ?>

                <div class="col-12 col-sm-6 col-lg-4">

                    <div class="unit-card">

                        <!-- Image -->
                        <img src="<?= htmlspecialchars($image) ?>"
                             class="unit-image">

                        <!-- Body -->
                        <div class="unit-body">

                            <div class="unit-title">
                                <?= htmlspecialchars($row['unit_name']) ?>
                            </div>

                            <div class="unit-info">
                                <i class="feather-layers me-1"></i>
                                Floor : <?= htmlspecialchars($row['floor'] ?? '-') ?>
                            </div>

                            <div class="unit-info">
                                <i class="feather-maximize me-1"></i>
                                Size / Meter : <?= htmlspecialchars($row['size']) ?>
                            </div>

                            <!-- Price Box -->
                            <div class="unit-price">

                                <div>
                                    <span>Advance</span>
                                    <strong>৳ <?= number_format($row['advance'],0) ?></strong>
                                </div>

                                <div>
                                    <span>Rent</span>
                                    <strong>৳ <?= number_format($row['rent'],0) ?></strong>
                                </div>

                                <?php if(!empty($row['water'])): ?>

                                <div>
                                    <span>Water</span>
                                    <strong>৳ <?= number_format($row['water'],0) ?></strong>
                                </div>

                                <?php endif; ?>

                                <?php if(!empty($row['gas'])): ?>

                                <div>
                                    <span>Gas</span>
                                    <strong>৳ <?= number_format($row['gas'],0) ?></strong>
                                </div>

                                <?php endif; ?>

                            </div>

                            <!-- Footer -->
                            <div class="unit-footer">

                                <span class="badge-status <?= ($row['status'] == 'Available') ? 'bg-available' : 'bg-booked'; ?>">

                                    <?= htmlspecialchars($row['status']) ?>

                                </span>

                                <div class="d-flex gap-2">

                                    <!-- Edit -->
                                    <a href="admin.php?page=CreateUnit&edit_id=<?= $row['id'] ?>&building_id=<?php echo $building_id; ?>"
                                       class="btn btn-primary action-btn">

                                        <i class="feather-edit"></i>
                                    </a>

                                    <!-- Delete -->
                                    <a href="admin.php?page=unit&id=<?php echo $building_id; ?>&action=delete&delete_id=<?= $row['id']; ?>"
                                       class="btn btn-danger action-btn"
                                       onclick="return confirm('Are you sure?');">

                                        <i class="feather-trash-2"></i>
                                    </a>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="col-12">

                <div class="card border-0 shadow-sm rounded-4">

                    <div class="card-body text-center py-5">

                        <img src="public/assets/images/no-data.png"
                             style="width:120px;opacity:.7">

                        <h5 class="mt-3 mb-1">No Unit Found</h5>

                        <p class="text-muted mb-0">
                            এখনও কোনো ইউনিট যোগ করা হয়নি।
                        </p>

                    </div>

                </div>

            </div>

        <?php endif; ?>

    </div>

</div>