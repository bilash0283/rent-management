<?php
$query = "SELECT * FROM unit wHERE status = 'Rented' ORDER BY id DESC";
$result = mysqli_query($db, $query);

?>

<div class="nxl-content">

    <!-- Page Header -->
    <div class="page-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Bill Month (<?php echo date('M - Y') ?>)</h5>

        <!-- <a href="admin.php?page=CreateTenant" class="btn btn-primary">
            <i class="feather-plus me-1"></i> Create Bill
        </a> -->
    </div>

    <!-- Main Content -->
    <div class="main-content mt-4">
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0">Bill List</h6>
                <?= $message ?? '' ?>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tenant</th>
                                <th>Advance</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            while ($row = mysqli_fetch_assoc($result)) {
                                $unit_id = $row['id'];
                                $advance = $row['advance'];
                                $rent = $row['rent'];
                                $Gas = $row['Gas'];
                                $Water = $row['Water'];
                                $Electricity = $row['Electricity'];
                                $Internet = $row['Internet'];
                                $Maintenance = $row['Maintenance'];
                                $Others = $row['Others'];
                                $building_name = $row['building_name'];

                                ?>
                                <tr>
                                    <td>
                                        <?php
                                        $sql_tenant = mysqli_query($db, "SELECT * FROM tenants WHERE building_id = '$building_name' AND unit_id = '$unit_id' ");
                                        while ($tent_row = mysqli_fetch_assoc($sql_tenant)) {
                                            $name = $tent_row['name'];
                                            $tent_id = $tent_row['id'];

                                            echo $name;
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        echo 'Advance = ৳ ' . $advance . '<br>';
                                        echo 'Paid    = ৳ ' . $advance . '<br>';
                                        echo 'Due     = ৳ 00';
                                        ?>
                                        <a href="" class="btn btn-sm btn-info mt-1" data-toggle="modal"
                                            data-target="#ielts_show<?php echo $tent_id ?>">
                                            Details
                                        </a>
                                    </td>

                                    <td>
                                        Rent = ৳ <?php echo $rent; ?><br>
                                        <?php if (!empty($Gas)) {
                                            echo 'Gas = ৳ ' . $Gas;
                                        } ?><br>
                                        <?php if (!empty($Water)) {
                                            echo 'Water = ৳ ' . $Water;
                                        } ?><br>
                                        <?php if (!empty($Electricity)) {
                                            echo 'Gas = ৳ ' . $Electricity;
                                        } ?><br>
                                        <?php if (!empty($Internet)) {
                                            echo 'Gas = ৳ ' . $Internet;
                                        } ?><br>
                                        <?php if (!empty($Others)) {
                                            echo 'Gas = ৳ ' . $Others;
                                        } ?><br>
                                        Total Amount = ৳ <?php echo $rent + $Gas + $Water + $Electricity + $Internet + $Others; ?>
                                        <a href="" class="btn btn-sm btn-info">Details</a>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-success">Paid</button>
                                    </td>
                                    <td>
                                        <a href="" class="btn btn-sm btn-light-info me-1" title="Invoice">
                                            <i class="feather-download"></i>
                                        </a>
                                        <a href=">" class="btn btn-sm btn-light-primary" title="Edit">
                                            <i class="feather-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>