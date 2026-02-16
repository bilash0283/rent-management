<!-- header  -->
<?php include "includes/header.php"; ?>

<!-- main content  -->
 <main class="nxl-container">
    <?php

    $page = $_GET['page'] ?? '';

    switch ($page) {
        case 'building':
            include 'pages/building.php';
            break;

        case 'CreateBuilding':
            include 'pages/CreateBuilding.php';
            break;

        case 'unit':
            include 'pages/Unit.php';
            break;

        case 'CreateUnit':
            include 'pages/CreateUnit.php';
            break;

        case 'tenant':
            include 'pages/Tenant.php';
            break;

        case 'CreateTenant':
            include 'pages/CreateTenant.php';
            break;

        case 'view_tenant':
            include 'pages/view_tenant.php';
            break;

        case 'bill':
            include 'pages/Bill.php';
            break;

        case 'editbill':
            include 'pages/EditBill.php';
            break;

        case 'setting':
            include 'pages/Setting.php';
            break;

        case 'Agreement':
            include 'pages/Agreement.php';
            break;

        case 'invoice':
            include 'pages/invoice.php';
            break;

        default:
            include 'pages/dashboard.php';
            break;
    }
    ?>

    <!-- footer section  -->
    <?php include "includes/footer.php" ?>
 </main>
<!-- main content  -->
 
<!-- footer  -->
    <script src="public/assets/vendors/js/vendors.min.js"></script>
    <script src="public/assets/vendors/js/daterangepicker.min.js"></script>
    <script src="public/assets/vendors/js/apexcharts.min.js"></script>
    <script src="public/assets/vendors/js/circle-progress.min.js"></script>
    <script src="public/assets/js/common-init.min.js"></script>
    <script src="public/assets/js/dashboard-init.min.js"></script>
</body>
</html>
<?php ob_end_flush();  ?>
<!-- footer  -->