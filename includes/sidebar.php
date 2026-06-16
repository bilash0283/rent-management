<?php 
// Run query only ONE time
$sql = "SELECT * FROM building ORDER BY id DESC";
$result = mysqli_query($db, $sql) or die("Query failed: " . mysqli_error($db));

// Store all buildings in array
$buildings = [];
while($row = mysqli_fetch_assoc($result)) {
    $buildings[] = $row;
}
?>

<nav class="nxl-navigation">
    <div class="navbar-wrapper">
        <div class="m-header" style="border-bottom: none;">
            <a href="admin.php" class="mx-auto my-3" style="width:100px;height:100px;">
                <!-- ========   change your logo hear   ============ -->
                <img src="public/assets/images/logo-full.png" alt="" class="logo logo-lg img-fluid" />
                <img src="public/assets/images/logo-abbr.png" alt="" class="logo logo-sm" />
            </a>
        </div>
        
        <div class="navbar-content">
            <ul class="nxl-navbar">
                <li class="nxl-item nxl-caption">
                    <label>Navigation</label>
                </li>
                
                <li class="nxl-item nxl-hasmenu">
                    <a href="admin.php" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-airplay text-info"></i></span>
                        <span class="nxl-mtext">Dashboards <?php echo $_SESSION['role']; ?></span>
                    </a>
                </li>

                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-home text-info"></i></span>
                        <span class="nxl-mtext">Building</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right text-info"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        <?php foreach($buildings as $row): 
                            $id   = $row['id'];
                            $name = $row['name'] ?? 'Unnamed';
                        ?>
                            <li class="nxl-item">
                                <a class="nxl-link" href="admin.php?page=unitinfo&building_id=<?= htmlspecialchars($id) ?>">
                                    <?= htmlspecialchars($name) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-dollar-sign text-info"></i></span>
                        <span class="nxl-mtext">Bills</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right text-info"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        <?php foreach($buildings as $row): 
                            $id   = $row['id'];
                            $name = $row['name'] ?? 'Unnamed';
                        ?>
                            <li class="nxl-item">
                                <a class="nxl-link" href="admin.php?page=bill&unit_id=0&id=<?= htmlspecialchars($id) ?>">
                                    <?= htmlspecialchars($name) ?>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </li>

                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-pie-chart text-info"></i></span>
                        <span class="nxl-mtext">Report</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right text-info"></i></span>
                    </a>
                    <ul class="nxl-submenu">
                        <li class="nxl-item"><a class="nxl-link" href="admin.php?page=building_report&unit_id=0&id=<?= htmlspecialchars($id) ?>">Monthly</a></li>
                        <li class="nxl-item"><a class="nxl-link" href="admin.php?page=report&unit_id=0&id=<?= htmlspecialchars($id) ?>">Yearly</a></li>
                    </ul>
                </li>

                <li class="nxl-item nxl-hasmenu">
                    <a href="admin.php?page=manager_account&unit_id=0&id=<?= htmlspecialchars($id) ?>" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-dollar-sign text-info"></i></span>
                        <span class="nxl-mtext">Manager Accounts</span>
                    </a>
                </li>

                <li class="nxl-item">
                    <a class="nxl-link" href="admin.php?page=Expense&id=<?= htmlspecialchars($id) ?>">
                        <span class="nxl-micon"> <i class="feather-pocket text-info"></i></span>
                        <span class="nxl-mtext">Expense</span>
                    </a>
                </li>
                
                <li class="nxl-item nxl-hasmenu">
                    <a href="javascript:void(0);" class="nxl-link">
                        <span class="nxl-micon"><i class="feather-settings text-info"></i></span>
                        <span class="nxl-mtext">Settings</span>
                        <span class="nxl-arrow"><i class="feather-chevron-right text-info"></i></span>
                    </a>

                    <ul class="nxl-submenu">
                        <li class="nxl-item">
                            <a class="nxl-link" href="admin.php?page=profile">
                                 <span class="nxl-micon"> <i class="feather-user text-info"></i></span>
                                <span class="nxl-mtext">Profile</span>
                            </a>
                        </li>
                        <li class="nxl-item">
                            <a class="nxl-link" href="admin.php?page=users">
                                <span class="nxl-micon"> <i class="feather-users text-info"></i></span>
                                <span class="nxl-mtext">Users</span>
                            </a>
                        </li>

                        <li class="nxl-item">
                            <a href="admin.php?page=building" class="nxl-link">
                                <span class="nxl-micon"><i class="feather-home text-info"></i></span>
                                <span class="nxl-mtext">Building</span>
                            </a>
                        </li>

                        <li class="nxl-item nxl-hasmenu">
                            <a href="javascript:void(0);" class="nxl-link">
                                <span class="nxl-micon"><i class="feather-layout text-info"></i>  </span>
                                <span class="nxl-mtext">Unit</span>
                                <span class="nxl-arrow"><i class="feather-chevron-right text-info"></i></span>
                            </a>
                            <ul class="nxl-submenu">
                                <?php foreach($buildings as $row): 
                                    $id   = $row['id'];
                                    $name = $row['name'] ?? 'Unnamed';
                                ?>
                                    <li class="nxl-item">
                                        <a class="nxl-link" href="admin.php?page=unit&id=<?= htmlspecialchars($id) ?>">
                                            <?= htmlspecialchars($name) ?>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </li>

                        <li class="nxl-item nxl-hasmenu">
                            <a href="javascript:void(0);" class="nxl-link">
                                <span class="nxl-micon"><i class="feather-users text-info"></i></span>
                                <span class="nxl-mtext">Tenant</span>
                                <span class="nxl-arrow"><i class="feather-chevron-right text-info"></i></span>
                            </a>
                            <ul class="nxl-submenu">
                                <li class="nxl-item nxl-hasmenu">
                                    <a href="admin.php?page=tenant&status=Active" class="nxl-link">
                                        <span class="nxl-mtext">Active Tenant</span>
                                    </a>
                                </li>
                                <li class="nxl-item nxl-hasmenu">
                                    <a href="admin.php?page=tenant&status=Inactive" class="nxl-link">
                                        <span class="nxl-mtext">Inactive Tenant</span>
                                    </a>
                                </li>
                                <li class="nxl-item nxl-hasmenu">
                                    <a href="admin.php?page=tenant&status=Booked" class="nxl-link">
                                        <span class="nxl-mtext">Booked Tenant</span>
                                    </a>
                                </li>
                            </ul>
                        </li>

                    </ul>
                </li>
                
            </ul>
        </div>
    </div>
</nav>