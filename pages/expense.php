
<div class="nxl-content">
    <!-- Page Header -->
    <div class="page-header d-flex align-items-center justify-content-between">
       
        <h5 class="mb-0">
            Expense
        </h5>
       
        <a href="" class="btn btn-primary">
            <i class="feather-plus me-1"></i> Create Expense
        </a>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        <div class="card shadow-sm">
            <?= $message ?? '' ?>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Role</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $user_sql = mysqli_query($db,"SELECT * FROM `users` ORDER BY id DESC ");
                             while ($row = mysqli_fetch_assoc($user_sql)) {
                                $unit_id = $row['id'];
                                $name = $row['name'];
                                $email = $row['email'];
                                $phone = $row['phone'];
                                $role = $row['role'];
                                ?>
                                <tr>
                                    <td>
                                        <a href="admin.php?page=view_tenant&id=" class="text-secendary fw-bold">
                                            <?= $name; ?>
                                        </a>
                                    </td>

                                    <td><?= $email; ?></td>

                                    <td><?= $phone ?></td>

                                    <td>
                                        <?php 
                                            if($role == 1){
                                                echo "<span class='bg-success text-white p-1 rounded-2'>Admin</span>";
                                            }else{
                                                echo "<span class='bg-warning text-white p-1 rounded-2'>Manager</span>";
                                            }
                                        ?>
                                    </td>

                                    <td>
                                        <div class="btn-group align-items-center">
                                            <a class="btn btn-sm btn-outline-primary" title="Edit">
                                                <i class="bi bi-pencil-square"></i>
                                            </a>
                                            <a class="btn btn-sm btn-outline-success" title="Invoice">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                        </div>
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