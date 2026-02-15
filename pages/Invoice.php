<?php
// Assuming this file is included inside your layout (header & footer already present)

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-danger'>Invalid tenant ID</div>";
    exit;
}

$tenant_id = (int) $_GET['id'];

$query = "SELECT * FROM tenants WHERE id = $tenant_id LIMIT 1";
$result = mysqli_query($db, $query);   // $conn from your header

if (!$result || mysqli_num_rows($result) === 0) {
    echo "<div class='alert alert-danger'>Tenant not found</div>";
    exit;
}

$tenant = mysqli_fetch_assoc($result);
?>

<div class="nxl-content">

    <!-- Page Header -->
    <div class="page-header d-flex align-items-center justify-content-between mb-4">
        <h5 class="mb-0">Tenant Agreement</h5>
        <div class="text-end mb-3">
            <button id="generatePdfBtn" class="btn btn-success btn-sm pl-5">
                <i class="feather-icon icon-download me-2"></i> Download PDF
            </button>
        </div>
        <a href="admin.php?page=tenant" class="btn btn-primary">
            <i class="feather-icon icon-arrow-left me-1"></i>Back
        </a>
    </div>

    <div class="mb-4">
        <!-- Download Button -->
        <!-- Printable Agreement Area -->
        <div id="pdf-content" class="agreement-paper p-5 bg-white border">

            <!-- Header -->
            <div class="text-center mb-5">
                <h3 class="fw-bold">TENANT RENTAL AGREEMENT</h3>
                <p class="text-muted">Property Management System</p>
                <p class="mt-3"><strong>Date:</strong> <?= date('d F Y') ?></p>
            </div>

            <!-- Tenant Photo -->
            <?php if (!empty($tenant['tenant_image'])): ?>
                <img src="public/uploads/tenants/<?= htmlspecialchars($tenant['tenant_image']) ?>"
                    class="tenant-photo float-end ms-4 mb-3" alt="Tenant Photo">
            <?php endif; ?>

            <!-- Parties -->
            <h5 class="section-title">1. THE PARTIES</h5>
            <div class="mb-4">
                <p><strong>Landlord:</strong> [Your Company / Landlord Name]</p>
                <p><strong>Address:</strong> [Landlord Full Address]</p>
            </div>

            <div class="mb-4">
                <p><strong>Tenant:</strong></p>
                <p class="ms-4">
                    <strong>Name:</strong> <span class="fw-bold"><?= htmlspecialchars($tenant['name']) ?></span><br>
                    <strong>Phone:</strong> <span class="fw-bold"><?= htmlspecialchars($tenant['phone']) ?></span><br>
                    <strong>Email:</strong> <span
                        class="fw-bold"><?= htmlspecialchars($tenant['email'] ?: 'N/A') ?></span><br>
                    <strong>Permanent Address:</strong> <span
                        class="fw-bold"><?= nl2br(htmlspecialchars($tenant['permanent_address'])) ?></span><br>
                    <strong>Family Members:</strong> <span
                        class="fw-bold"><?= htmlspecialchars($tenant['family_member'] ?: '0') ?></span>
                </p>
            </div>

            <!-- Property -->
            <h5 class="section-title">2. PROPERTY DETAILS</h5>
            <div class="mb-4">
                <p><strong>Building ID:</strong> <span
                        class="fw-bold"><?= htmlspecialchars($tenant['building_id']) ?></span></p>
                <p><strong>Unit ID:</strong> <span class="fw-bold"><?= htmlspecialchars($tenant['unit_id']) ?></span>
                </p>
                <!-- Add more property info if you have it in another table -->
            </div>

            <!-- Terms -->
            <h5 class="section-title">3. TERMS & CONDITIONS</h5>
            <ol class="terms-list">
                <li>The Tenant agrees to rent the above-mentioned unit from the Landlord.</li>
                <li>Rent shall be paid monthly on or before the 5th day of each month.</li>
                <li>The tenancy period shall commence on [start date] and continue until [end date].</li>
                <li>The Tenant shall keep the premises in clean and good condition.</li>
                <li>Any violation of the agreement terms may result in termination.</li>
                <li>This agreement is executed in good faith by both parties.</li>
            </ol>

            <!-- Signatures -->
            <div class="row mt-5 pt-5">
                <div class="col-6 text-center">
                    <div class="border-top pt-4 mt-5">Landlord Signature</div>
                    <div class="mt-4">Date: ____________________</div>
                </div>
                <div class="col-6 text-center">
                    <div class="border-top pt-4 mt-5">Tenant Signature</div>
                    <div class="mt-4">Date: ____________________</div>
                </div>
            </div>

        </div><!-- #pdf-content -->
    </div>

</div><!-- nxl-content -->


<!-- ======================== -->
<!--    Required JavaScript   -->
<!-- ======================== -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
    document.getElementById('generatePdfBtn').addEventListener('click', function () {
        const element = document.getElementById('pdf-content');

        const options = {
            margin: [15, 10, 15, 10],   // top, right, bottom, left
            filename: 'Tenant_Agreement_<?= addslashes($tenant['name'] ?? 'Unknown') ?>.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { scale: 2, useCORS: true, logging: false },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };

        html2pdf()
            .from(element)
            .set(options)
            .save();
    });
</script>


<!-- ======================== -->
<!--        CSS Styles        -->
<!-- ======================== -->
<style>
    .agreement-paper {
        width: 210mm;
        min-height: 297mm;
        margin: 0 auto;
        background: white;
        box-shadow: 0 0 15px rgba(0, 0, 0, 0.15);
        font-family: 'Arial', sans-serif;
        font-size: 12pt;
        line-height: 1.5;
    }

    .tenant-photo {
        width: 130px;
        height: 170px;
        object-fit: cover;
        border: 2px solid #333;
    }

    .section-title {
        border-bottom: 2px solid #444;
        padding-bottom: 6px;
        margin: 30px 0 15px;
        font-size: 15pt;
    }

    .terms-list {
        padding-left: 20px;
        margin-bottom: 25px;
    }

    .terms-list li {
        margin-bottom: 10px;
    }

    @media print {
        body * {
            visibility: hidden;
        }

        #pdf-content,
        #pdf-content * {
            visibility: visible;
        }

        #pdf-content {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            box-shadow: none;
        }
    }
</style>