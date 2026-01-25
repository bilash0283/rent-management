<!-- header  -->
<?php include "includes/header.php"; ?>

<!-- main content  -->
 <main class="nxl-container">
    <?php 
        if(isset($_GET['page'])){
            $page = $_GET['page'];

            if($page = 'building'){
                include 'pages/building.php';
            }
        }else{
             include 'pages/dashboard.php';
        }
        
    ?>

    <!-- footer section  -->
    <?php include "includes/footer.php" ?>
 </main>
<!-- main content  -->

<!-- theem_setting  -->
<?php include "includes/theem_setting.php"; ?>

<!-- footer  -->
    <script src="public/assets/vendors/js/vendors.min.js"></script>
    <script src="public/assets/vendors/js/daterangepicker.min.js"></script>
    <script src="public/assets/vendors/js/apexcharts.min.js"></script>
    <script src="public/assets/vendors/js/circle-progress.min.js"></script>
    <script src="public/assets/js/common-init.min.js"></script>
    <script src="public/assets/js/dashboard-init.min.js"></script>
    <script src="public/assets/js/theme-customizer-init.min.js"></script>
</body>
</html>
<!-- footer  -->