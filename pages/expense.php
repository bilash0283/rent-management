<?php 
    $building_id = '';
    $this_month = date('Y-m'); 

    // Handle POST Filter
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['month']) && !empty($_POST['month'])) {
            $this_month = mysqli_real_escape_string($db, $_POST['month']);
        }
        if (isset($_POST['building']) && !empty($_POST['building'])) {
            $building_id = mysqli_real_escape_string($db, $_POST['building']);
        }
    }

    // If no POST, get building_id from URL
    if (empty($building_id)) {
        if (isset($_GET['id']) && !empty($_GET['id'])) {
            $building_id = mysqli_real_escape_string($db, $_GET['id']);
        } else {
            $first_b = mysqli_query($db, "SELECT id FROM building LIMIT 1");
            $fb_row = mysqli_fetch_assoc($first_b);
            $building_id = $fb_row['id'] ?? '';
        }
    }

    // Calculations
    $total_sql = mysqli_query($db, "SELECT SUM(amount) AS total FROM `expense` WHERE building_id='$building_id' AND expense_month='$this_month'");
    $total_row = mysqli_fetch_assoc($total_sql);
    $grand_total = (float)($total_row['total'] ?? 0);

    $admin_sql = mysqli_query($db, "SELECT SUM(amount) AS total FROM `expense` WHERE building_id='$building_id' AND expense_month='$this_month' AND expense_by = 'Admin'");
    $admin_row = mysqli_fetch_assoc($admin_sql);
    $admin_total = (float)($admin_row['total'] ?? 0);

    $manager_sql = mysqli_query($db, "SELECT SUM(amount) AS total FROM `expense` WHERE building_id='$building_id' AND expense_month='$this_month' AND expense_by = 'Manager'");
    $manager_row = mysqli_fetch_assoc($manager_sql);
    $manager_total = (float)($manager_row['total'] ?? 0);

    $b_name_sql = mysqli_query($db, "SELECT name FROM building WHERE id='$building_id'");
    $b_name_row = mysqli_fetch_assoc($b_name_sql);
    $active_building_name = $b_name_row['name'] ?? 'N/A';

    if (isset($_GET['delete_id'])) {
        $delete_id = (int)$_GET['delete_id'];
        mysqli_query($db, "DELETE FROM expense WHERE id=$delete_id");
        echo "<script>window.location.href='admin.php?page=Expense&id=$building_id';</script>";
        exit;
    }
?>

<div class="app-container">
    <!-- Floating Action Button -->
    <a href="admin.php?page=create_expense" class="fab-btn shadow-lg mb-3">
        <i class="bi bi-plus-lg"></i>
    </a>

    <!-- Sticky Header -->
    <div class="app-header bg-white p-3 shadow-sm mb-3">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6 class="text-uppercase small fw-bold text-primary mb-1" style="letter-spacing: 1px;">Building Ledger</h6>
                <h4 class="fw-bold mb-0 text-dark"><?= $active_building_name ?></h4>
            </div>
            <button class="btn btn-light-soft rounded-circle" type="button" data-bs-toggle="collapse" data-bs-target="#filterPanel">
                <i class="bi bi-sliders2-vertical fs-5"></i>
            </button>
        </div>

        <div class="collapse" id="filterPanel">
            <form method="POST" class="mt-3 p-3 bg-light rounded-4 border-0">
                <div class="row g-2">
                    <div class="col-6">
                        <select name="month" class="form-select border-0 shadow-sm rounded-3">
                            <?php
                            for ($m = 1; $m <= 12; $m++) {
                                $m_val = date('Y') . '-' . str_pad($m, 2, '0', STR_PAD_LEFT);
                                $m_name = date('F', mktime(0, 0, 0, $m, 1));
                                $selected = ($m_val == $this_month) ? 'selected' : '';
                                echo "<option value='$m_val' $selected>$m_name</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <select name="building" class="form-select border-0 shadow-sm rounded-3">
                            <?php
                            $b_list = mysqli_query($db, "SELECT id, name FROM building ORDER BY name ASC");
                            while ($b = mysqli_fetch_assoc($b_list)) {
                                $sel = ($b['id'] == $building_id) ? 'selected' : '';
                                echo "<option value='{$b['id']}' $sel>{$b['name']}</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-12 mt-2">
                        <button type="submit" class="btn btn-dark w-100 rounded-3 py-2 fw-bold">Update View</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Card -->
    <div class="px-3 mb-4">
        <div class="main-card p-4">
            <p class="text-white-50 small mb-1 text-uppercase fw-bold">Net Monthly Spending</p>
            <h2 class="text-white fw-bold mb-4">৳ <?= number_format($grand_total, 0) ?></h2>
            
            <div class="row text-center border-top border-white-10 pt-3">
                <div class="col-6 border-end border-white-10">
                    <small class="text-white-50 d-block mb-1">Admin</small>
                    <span class="text-white fw-bold small">৳ <?= number_format($admin_total, 0) ?></span>
                </div>
                <div class="col-6">
                    <small class="text-white-50 d-block mb-1">Manager</small>
                    <span class="text-white fw-bold small">৳ <?= number_format($manager_total, 0) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Expense List -->
    <div class="px-3 pb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="fw-bold text-dark mb-0">Expense Details</h6>
            <span class="small text-muted fw-semibold"><?= date('M Y', strtotime($this_month)) ?></span>
        </div>

        <?php
        $exp_query = mysqli_query($db, "SELECT * FROM `expense` WHERE building_id='$building_id' AND expense_month='$this_month' ORDER BY date DESC, id DESC");
        if (mysqli_num_rows($exp_query) > 0) {
            while ($row = mysqli_fetch_assoc($exp_query)) {
                $is_admin = ($row['expense_by'] == 'Admin');
                $accent_color = $is_admin ? '#0d6efd' : '#f59e0b';
                $bg_soft = $is_admin ? '#e7f1ff' : '#fef3c7';
        ?>
        <div class="expense-card p-3 mb-3 border-0 shadow-sm bg-white position-relative overflow-hidden">
            <!-- Source Tag (Admin/Manager) -->
            <div class="source-tag" style="background: <?= $accent_color ?>;">
                <?= $row['expense_by'] ?>
            </div>

            <div class="d-flex align-items-start gap-3">
                <!-- Icon based on category -->
                <div class="icon-box" style="background: <?= $bg_soft ?>; color: <?= $accent_color ?>;">
                    <i class="bi <?= $is_admin ? 'bi-shield-check' : 'bi-person-circle' ?> fs-4"></i>
                </div>
                
                <div class="flex-grow-1">
                    <div class="d-flex justify-content-between">
                        <h6 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($row['expense_for']) ?></h6>
                        <span class="fw-bold text-dark">৳ <?= number_format($row['amount'], 0) ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <div class="d-flex flex-wrap gap-1">
                            <span class="badge-custom text-muted">
                                <i class="bi bi-calendar3 me-1"></i> <?= date('d M', strtotime($row['date'])) ?>
                            </span>
                            <span class="badge-custom text-muted">
                                <i class="bi bi-wallet2 me-1"></i> <?= $row['expense_method'] ?>
                            </span>
                            <?php if(!empty($row['unit_id'])): ?>
                                <span class="badge-custom text-primary bg-light">
                                    <i class="bi bi-house me-1"></i> Unit <?= $row['unit_id'] ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex gap-2 ms-2">
                            <a href="admin.php?page=create_expense&edit_id=<?= $row['id'] ?>" class="btn-action edit">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="admin.php?page=Expense&id=<?= $building_id ?>&delete_id=<?= $row['id'] ?>" class="btn-action delete" onclick="return confirm('Delete?')">
                                <i class="bi bi-trash3"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php 
            } 
        } else { ?>
            <div class="text-center py-5">
                <img src="https://cdn-icons-png.flaticon.com/512/7486/7486744.png" width="80" class="opacity-25 mb-3">
                <p class="text-muted small">No expenses recorded for this month.</p>
            </div>
        <?php } ?>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap');
    
    body { background-color: #f3f4f6; font-family: 'Inter', sans-serif; color: #1f2937; }
    .app-container { max-width: 480px; margin: 0 auto; min-height: 100vh; padding-bottom: 80px; }

    /* Header */
    .app-header { border-radius: 0 0 25px 25px; position: sticky; top: 0; z-index: 1000; }
    .btn-light-soft { background: #f3f4f6; color: #4b5563; width: 45px; height: 45px; display: flex; align-items: center; justify-content: center; border: none; }

    /* Summary Card */
    .main-card {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-radius: 28px;
        box-shadow: 0 15px 30px rgba(0,0,0,0.15);
    }
    .border-white-10 { border-color: rgba(255,255,255,0.1) !important; }

    /* Expense Card */
    .expense-card {
        border-radius: 20px;
        transition: all 0.3s ease;
    }
    .icon-box {
        width: 50px;
        height: 50px;
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
    
    /* Source Tag (Top Right) */
    .source-tag {
        position: absolute;
        top: 0;
        right: 0;
        padding: 3px 12px;
        font-size: 9px;
        font-weight: 800;
        text-transform: uppercase;
        color: white;
        border-bottom-left-radius: 12px;
        letter-spacing: 0.5px;
    }

    /* Badges & Actions */
    .badge-custom {
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 8px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
    }
    .btn-action {
        width: 32px;
        height: 32px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        font-size: 14px;
        transition: 0.2s;
    }
    .btn-action.edit { background: #eff6ff; color: #3b82f6; }
    .btn-action.delete { background: #fef2f2; color: #ef4444; }

    /* Floating Button */
    .fab-btn {
        position: fixed;
        bottom: 25px;
        right: 25px;
        width: 60px;
        height: 60px;
        background: #0d6efd;
        color: white;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        z-index: 1100;
        text-decoration: none;
    }
</style>