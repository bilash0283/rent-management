<!-- header  -->
<?php 
include "includes/header.php";
$page = $_GET['page'] ?? '';
?>
<!-- main content  -->
 <main class="nxl-container">
    <?php
    switch ($page) {
        case 'dashboard':
            include 'pages/dashboard.php';
        break;

        case 'building':
            include 'pages/Building.php';
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

        case 'profile':
            include 'pages/profile.php';
        break;

        case 'users':
            include 'pages/users.php';
        break;

        case 'viewInvoice':
            include 'pages/view_incoice.php';
        break;

        case 'payslip':
            include 'pages/payslip.php';
        break;

        case 'unitinfo':
            include 'pages/unit_tanent_info.php';
        break;

        case 'manager_account':
            include 'pages/manager_account.php';
        break;

        case 'update_payment':
            include 'pages/update_payment.php';
        break;

        case 'report':
            include 'pages/report.php';
        break;

        case 'building_report':
            include 'pages/building_wise_report.php';    
        break;

        case 'Expense':
            include 'pages/expense.php';
        break;

        case 'create_expense':
            include 'pages/create_expense.php';
        break;

        case 'view_expense':
            include 'pages/view_expense.php';
        break;

        case 'delete_payment':
            include 'pages/delete_payment.php';
        break;

        case 'UpdateInvoice':
            include 'pages/UpdateInvoice.php';
        break;

        case 'DeleteInvoice':
            include 'pages/DeleteInvoice.php';
        break;

        case 'status_change':
            include 'pages/status_change.php';
        break;

        case 'delete_advance':
            include 'pages/delete_advance.php';
        break;

        case 'tenant_inv_pay':
            include 'pages/tenant_inv_pay.php';
        break;

        case 'view_photo':
            include 'pages/view_photo.php';
        break;

        case 'notification':
            include 'pages/notification.php';   
        break;

        case 'approve_payment':
            include 'pages/approve_payment.php';
        break;

        case 'change_password':
            include 'pages/change_password.php';
        break;

        default:
            include 'pages/dashboard.php';
        break;
    }
    ?>

    <!-- footer section  -->
    <?php include "includes/footer.php"; ?>
 </main>
<!-- main content  -->
 
<?php include "includes/footer_last.php"; ?>