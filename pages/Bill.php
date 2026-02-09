<?php 
    $query  = "SELECT * FROM unit wHERE status = 'Rented' ORDER BY id DESC";
    $result = mysqli_query($db, $query);

?>

<div class="nxl-content">

    <!-- Page Header -->
    <div class="page-header d-flex align-items-center justify-content-between">
        <h5 class="mb-0">Bill Month (<?php echo date('M - Y') ?>)</h5>

        <a href="admin.php?page=CreateTenant" class="btn btn-primary">
            <i class="feather-plus me-1"></i> Create Bill
        </a>
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
                                while($row = mysqli_fetch_assoc($result)){
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
                                        $sql_tenant = mysqli_query($db,"SELECT * FROM tenants WHERE building_id = '$building_name' AND unit_id = '$unit_id' ");
                                        while($tent_row = mysqli_fetch_assoc($sql_tenant)){
                                            $name = $tent_row['name'];

                                            echo $name;
                                        }
                                    ?>
                                </td>
                                <td>
                                    <?php echo $advance; ?>
                                </td>
                                <td>
                                    Rent : ৳ <?php echo $rent; ?>
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
