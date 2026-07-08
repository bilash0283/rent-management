<?php
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo "<div class='alert alert-danger'>Invalid tenant ID</div>";
    exit;
}
$tenant_id = (int) $_GET['id'];

$query = "
        SELECT 
            t.*, 
            b.name AS building_name, b.address AS address,
            u.unit_name, u.unit_type AS unit_type,
            u.status
        FROM tenants t
        JOIN building b ON t.building_id = b.id
        JOIN unit u ON t.unit_id = u.id
        WHERE t.role IN ('Tenant') AND t.id = $tenant_id ORDER BY t.id DESC
    ";
$result = mysqli_query($db, $query);

$building_name = 'N/A';
$building_address = 'N/A';
$unit_name = 'N/A';
$unit_type = 'Unit';

if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $building_name = $row['building_name'];
        $unit_name = $row['unit_name'];
        $unit_type = $row['unit_type'];
        $building_address = $row['address'];
    }
}

$query = "SELECT * FROM tenants WHERE role IN ('Tenant') AND id = $tenant_id LIMIT 1";
$result = mysqli_query($db, $query);

if (!$result || mysqli_num_rows($result) === 0) {
    echo "<div class='alert alert-danger'>Tenant not found</div>";
    exit;
}

$tenant = mysqli_fetch_assoc($result);
?>

<div class="nxl-content">
    <div class="page-header d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
        <h5 class="mb-0 fw-bold text-uppercase tracking-wide">Rental Agreement Workspace</h5>
        <div class="d-flex gap-2">
            <button id="generatePdfBtn" class="btn btn-dark btn-sm px-4 py-2 d-flex align-items-center fw-bold shadow-sm">
                <i class="feather-icon icon-download me-2"></i> DOWNLOAD
            </button>
            <?php if($_SESSION['role'] == 'Admin'){ ?>
                <a href="admin.php?page=tenant&building_id=<?= $tenant['building_id'] ?>" class="btn btn-outline-secondary btn-sm px-3">
                    <i class="feather-icon icon-arrow-left me-1"></i>Back
                </a>
            <?php } else if($_SESSION['role'] == 'Tenant'){ ?>
                <a href="admin.php?page=dashboard" class="btn btn-outline-secondary btn-sm px-3">
                    <i class="feather-icon icon-arrow-left me-1"></i>Back
                </a>
            <?php } ?>
        </div>
    </div>

    <div class="agreement-screen-scroll-container">
        <!-- PDF Wrapper কন্টেইনার অ্যাড করা হয়েছে অ্যালাইনমেন্ট রিস্টোর করার জন্য -->
        <div class="pdf-render-wrapper">
            <div id="pdf-content" class="legal-agreement-master">
                
                <!-- PAGE 1 -->
                <div class="agreement-page-block">
                    <div class="top-judicial-bar"></div>
                    
                    <div class="text-center legal-header-zone">
                        <h1 class="legal-main-title">RESIDENTIAL LEASE AGREEMENT</h1>
                        <p class="legal-sub-title">DEED OF TENANCY ENFORCEABLE UNDER LOCAL PROPERTY ACT</p>
                        <div class="legal-badge">OFFICIAL COPY</div>
                    </div>

                    <div class="row align-items-center mb-4 date-meta-box">
                        <div class="col-8">
                            <table class="table table-borderless table-sm mb-0 unique-meta-table">
                                <tr>
                                    <td width="140"><strong>Document Ref:</strong></td>
                                    <td><span class="text-mono">LA-<?= date('Y') ?>-<?= str_pad($tenant['id'], 5, '0', STR_PAD_LEFT) ?></span></td>
                                </tr>
                                <tr>
                                    <td><strong>Execution Date:</strong></td>
                                    <td><?= date('d F Y') ?></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-4 text-end">
                            <?php if (!empty($tenant['tenant_image'])): ?>
                                <div class="legal-photo-frame">
                                    <img src="public/uploads/tenants/<?= htmlspecialchars($tenant['tenant_image']) ?>" alt="Tenant" crossorigin="anonymous">
                                    <div class="frame-label">TENANT PHOTO</div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <p class="preamble-text">
                        This Deed of Residential Lease Agreement is made, entered into, and executed on this day by and between the Landlord and the Tenant named below. Whereas the Landlord is the absolute lawful owner of the premises described herein and has agreed to grant a lease, and the Tenant has agreed to take on lease the specific unit under the following strictly binding terms and covenants.
                    </p>

                    <h4 class="legal-section-heading">1. THE PARTIES & REPRESENTATIVES</h4>
                    
                    <div class="party-container-table">
                        <div class="party-row">
                            <div class="party-column-cell balance-left">
                                <div class="party-header color-landlord">THE FIRST PARTY (LANDLORD)</div>
                                <div class="party-details-body">
                                    <h5 class="party-display-name"><?= htmlspecialchars($building_name) ?></h5>
                                    <p class="mb-2"><i class="feather-icon icon-map-pin me-2"></i><strong>Premises Address:</strong><br><span class="text-muted-dark"><?= htmlspecialchars($building_address) ?></span></p>
                                    <p class="mb-0"><i class="feather-icon icon-shield me-2"></i><strong>Authority Status:</strong> Legal Absolute Proprietor</p>
                                </div>
                            </div>
                            
                            <div class="party-column-cell balance-right">
                                <div class="party-header color-tenant">THE SECOND PARTY (TENANT)</div>
                                <div class="party-details-body">
                                    <h5 class="party-display-name"><?= htmlspecialchars($tenant['name']) ?></h5>
                                    <table class="table table-borderless table-sm mb-0 inline-details-table">
                                        <tr><td width="90"><strong>Phone:</strong></td><td><?= htmlspecialchars($tenant['phone']) ?></td></tr>
                                        <tr><td><strong>Email:</strong></td><td><?= htmlspecialchars($tenant['email'] ?: 'N/A') ?></td></tr>
                                        <tr><td><strong>Permanent:</strong></td><td><span class="small-address"><?= nl2br(htmlspecialchars($tenant['permanent_address'])) ?></span></td></tr>
                                        <tr><td><strong>Occupants:</strong></td><td><?= htmlspecialchars($tenant['family_member'] ?: '0') ?> Registered Member(s)</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h4 class="legal-section-heading">2. DEMISED PREMISES ALLOCATION</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered legal-structural-table">
                            <thead>
                                <tr>
                                    <th>Property / Building Entity</th>
                                    <th>Assigned <?= htmlspecialchars($unit_type) ?> No.</th>
                                    <th>Permitted Usage Category</th>
                                    <th>Fittings & Structural Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="fw-bold"><?= htmlspecialchars($building_name) ?></td>
                                    <td class="fw-bold text-highlight-red"><?= htmlspecialchars($unit_name) ?></td>
                                    <td>Strictly Residential (Private Living)</td>
                                    <td>Fully Verified, Functional & Move-in Ready</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="html2pdf__page-break"></div>

                <!-- PAGE 2 -->
                <div class="agreement-page-block">
                    <h4 class="legal-section-heading">3. MANDATORY TERMS & COVENANTS</h4>
                    <ol class="legal-numbered-clauses">
                        <li>
                            <strong>Tenancy Tenure & Renewal Option:</strong> This agreement shall remain valid for a fixed duration of one (1) year beginning from <strong>[Start Date]</strong> and terminating automatically on <strong>[End Date]</strong>. Any request for renewal must be submitted in writing at least thirty (30) days prior to expiry, subject to a standard 10% rent escalation.
                        </li>
                        <li>
                            <strong>Monthly Rental Obligation & Timeline:</strong> The agreed monthly lease rent for the demised unit is set at <strong>[Rent Amount] BDT</strong>. The Tenant covenants to dynamically clear the entire monthly rent on or before the <strong>5th day of each running calendar month</strong> without any delays or deductions.
                        </li>
                        <li>
                            <strong>Security Advance Deposit:</strong> The Tenant has deposited a sum equivalent to <strong>[Advance Months] Months</strong> rent as a refundable interest-free Security Deposit. This amount is kept safely for structural security and is strictly <strong>non-adjustable</strong> against regular running monthly rents.
                        </li>
                        <li>
                            <strong>Utility Tariffs & Maintenance:</strong> All utility expenses incurred for the specific unit, including commercial/residential electricity consumption, water supply, gas billing, internet subscriptions, and predefined monthly building society maintenance service charges, shall be cleared independently by the Tenant.
                        </li>
                        <li>
                            <strong>Premises Handover & Mandatory Notice Period:</strong> 
                            <div class="critical-notice-callout">
                                <strong>CRITICAL CLAUSE:</strong> In the event that either party desires to terminate or vacate the premises prior to the natural contract expiration, a mandatory <strong>two (2) months advanced written notice</strong> must be formally served. Failure by the Tenant to provide this full 2-month notice will result in the immediate forfeiture of the security deposit equivalent to the notice duration.
                            </div>
                        </li>
                        <li>
                            <strong>Prohibition of Subletting & Structural Changes:</strong> The Tenant shall not sublet, transfer, or part with the possession of the demised premises or any part thereof to any third-party occupants. Furthermore, no structural modifications, wall drillings for permanent alterations, or color changes are allowed without the explicit written authorization of the Landlord.
                        </li>
                    </ol>
                </div>

                <div class="html2pdf__page-break"></div>

                <!-- PAGE 3 -->
                <div class="agreement-page-block">
                    <ol class="legal-numbered-clauses" start="7">
                        <li>
                            <strong>Peaceful Habitancy & Statutory Compliance:</strong> The Tenant agrees to use the flat safely and maintain high standards of civic discipline. Carrying out illegal, hazardous, commercial, or immoral activities inside the premises is completely prohibited. Any verified public nuisance or breach of law will result in immediate termination of the lease and summary eviction.
                        </li>
                        <li>
                            <strong>Routine Inspections & Property Access:</strong> The Landlord, or their designated asset manager, retains the structural right to enter the leased unit during reasonable daylight hours with an advanced 24-hour notice to verify building maintenance conditions or to display the unit to future prospective lessees.
                        </li>
                        <li>
                            <strong>Evacuation Protocol & Damage Repair:</strong> Upon the expiration or formal termination of this lease, the Tenant covenants to peacefully hand over vacant possession of the unit along with all structural keys. If any malicious or negligent damages are found on the walls, plumbing, or electrical channels, the cost of reinstatement will be directly deducted from the Tenant's Security Deposit.
                        </li>
                    </ol>

                    <div class="formal-declaration-statement">
                        <h5>EXECUTION & SOLEMN DECLARATION</h5>
                        <p>In witness whereof, both the First Party (Landlord) and the Second Party (Tenant) have read, completely understood, and unconditionally accepted all conditions, penalties, and terms compiled across this 3-page digital lease document. Both parties place their respective signatures on this deed voluntarily, in sound health, and in the presence of competent witnesses.</p>
                    </div>

                    <div class="signature-container-table">
                        <div class="party-row">
                            <div class="signature-cell text-center">
                                <div class="signature-capture-box">
                                    <div class="sig-line"></div>
                                    <p class="sig-title">LANDLORD / AUTHORIZED SIGNATORY</p>
                                    <p class="text-muted small mb-3">First Party Signature</p>
                                    <div class="sig-date-placeholder">Date: ____ / ____ / 20___</div>
                                </div>
                            </div>
                            <div class="signature-cell text-center">
                                <div class="signature-capture-box">
                                    <div class="sig-line"></div>
                                    <p class="sig-title">TENANT (SECOND PARTY)</p>
                                    <p class="text-muted small mb-3"><?= htmlspecialchars($tenant['name']) ?></p>
                                    <div class="sig-date-placeholder">Date: ____ / ____ / 20___</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="agreement-legal-footer">
                        <div class="footer-line"></div>
                        <div class="footer-content-table">
                            <div class="party-row">
                                <div class="footer-cell text-start">Document ID: PMS-REF-<?= htmlspecialchars($tenant['id']) ?></div>
                                <div class="footer-cell text-center fw-bold text-uppercase">Page 3 of 3</div>
                                <div class="footer-cell text-end">Secured Digital Lease Contract</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
    document.getElementById('generatePdfBtn').addEventListener('click', function () {
        const element = document.getElementById('pdf-content');
        
        const options = {
            margin: [12, 12, 12, 12], 
            filename: 'Official_Lease_Agreement_<?= addslashes($tenant['name'] ?? 'Tenant') ?>.pdf',
            image: { type: 'jpeg', quality: 0.98 },
            html2canvas: { 
                scale: 2, 
                useCORS: true, 
                allowTaint: false,
                logging: false,
                letterRendering: true,
                // CRITICAL SHIFTING FIX: এক্সিস পজিশন এবং স্ক্রল লক করা হয়েছে জিরোতে
                scrollX: 0,
                scrollY: 0,
                x: 0,
                y: 0,
                width: 793, 
                windowWidth: 793 
            },
            jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
            pagebreak: { mode: ['css', 'legacy'] } 
        };

        // রেন্ডার করার আগে উইন্ডো কারেন্ট ভিউ স্ক্রল পজিশন জিরো এসাইন করা
        html2pdf().from(element).set(options).save();
    });
</script>

<style>
    .agreement-screen-scroll-container {
        width: 100%;
        overflow-x: auto;
        background-color: #f0f2f5;
        padding: 25px 15px;
        border-radius: 12px;
        border: 1px solid #e3e6ec;
    }

    /* পজিশনিং ইরর ফিক্সড করার জন্য প্যারেন্ট র্যাপার */
    .pdf-render-wrapper {
        position: relative;
        width: 100%;
        margin: 0;
        padding: 0;
    }

    .legal-agreement-master {
        width: 210mm; 
        margin: 0 auto;
        background: #ffffff;
        padding: 20mm 15mm;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
        font-family: 'Times New Roman', Times, serif;
        color: #111111;
        font-size: 11pt;
        line-height: 1.6;
        box-sizing: border-box;
        position: relative;
    }

    .agreement-page-block {
        padding: 5px 0;
        min-height: 255mm;
        position: relative;
        box-sizing: border-box;
    }

    .top-judicial-bar {
        height: 6px;
        background: linear-gradient(90deg, #0d1b2a 0%, #e0a96d 50%, #0d1b2a 100%);
        margin-bottom: 25px;
    }

    .legal-header-zone {
        margin-bottom: 35px;
    }

    .legal-main-title {
        color: #0d1b2a;
        font-size: 22pt;
        font-weight: bold;
        letter-spacing: 2px;
        margin-bottom: 4px;
    }

    .legal-sub-title {
        color: #5c677d;
        font-size: 9.5pt;
        letter-spacing: 1px;
        font-weight: 600;
    }

    .legal-badge {
        display: inline-block;
        border: 2px solid #0d1b2a;
        color: #0d1b2a;
        padding: 2px 14px;
        font-weight: bold;
        font-size: 9pt;
        letter-spacing: 2px;
        margin-top: 15px;
        border-radius: 3px;
        background-color: #f8f9fa;
    }

    .date-meta-box {
        border-bottom: 1px dashed #cccccc;
        padding-bottom: 15px;
    }

    .unique-meta-table td {
        padding: 3px 0;
        font-size: 11pt;
    }
    .text-mono {
        font-family: 'Courier New', Courier, monospace;
        font-weight: bold;
        color: #c1121f;
    }

    .legal-photo-frame {
        display: inline-block;
        border: 2px solid #0d1b2a;
        padding: 3px;
        background-color: #ffffff;
        box-shadow: 2px 2px 8px rgba(0,0,0,0.06);
    }
    .legal-photo-frame img {
        width: 115px;
        height: 145px;
        object-fit: cover;
        display: block;
    }
    .frame-label {
        font-size: 7.5pt;
        font-weight: bold;
        text-align: center;
        background: #0d1b2a;
        color: #ffffff;
        padding: 3px 0;
        letter-spacing: 1px;
    }

    .preamble-text {
        text-align: justify;
        text-indent: 40px;
        margin-bottom: 25px;
        font-style: italic;
    }

    .legal-section-heading {
        font-size: 13pt;
        font-weight: bold;
        color: #ffffff;
        background-color: #0d1b2a;
        padding: 6px 15px;
        margin: 25px 0 15px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .party-container-table, .signature-container-table, .footer-content-table {
        display: table;
        width: 100%;
        table-layout: fixed;
        border-collapse: separate;
        border-spacing: 15px 0;
        margin-bottom: 20px;
    }
    
    .party-row {
        display: table-row;
    }
    
    .party-column-cell {
        display: table-cell;
        width: 50%;
        border: 1px solid #dcdcdc;
        background-color: #fafbfc;
        border-radius: 4px;
        vertical-align: top;
    }

    .signature-cell {
        display: table-cell;
        width: 50%;
        vertical-align: top;
    }

    .footer-cell {
        display: table-cell;
        width: 33.33%;
        font-size: 8.5pt;
        color: #6c757d;
    }

    .party-header {
        padding: 6px 12px;
        font-weight: bold;
        font-size: 10pt;
        color: #ffffff;
        letter-spacing: 0.5px;
    }
    .color-landlord { background-color: #1f3a52; }
    .color-tenant { background-color: #2a4736; }
    
    .party-details-body {
        padding: 15px;
    }
    .party-display-name {
        font-size: 13pt;
        font-weight: bold;
        color: #111;
        margin-bottom: 12px;
        border-bottom: 1px solid #e1e1e1;
        padding-bottom: 4px;
    }
    .small-address {
        font-size: 10.5pt;
        line-height: 1.4;
        display: block;
    }
    .inline-details-table td {
        padding: 4px 0;
        vertical-align: top;
    }

    .legal-structural-table {
        border: 1px solid #111111 !important;
        width: 100%;
    }
    .legal-structural-table th {
        background-color: #f1f3f5 !important;
        color: #0d1b2a !important;
        font-weight: bold;
        font-size: 10.5pt;
        text-transform: uppercase;
        border: 1px solid #111111 !important;
        padding: 8px;
    }
    .legal-structural-table td {
        border: 1px solid #111111 !important;
        font-size: 11pt;
        padding: 8px;
    }
    .text-highlight-red {
        color: #b7094c;
    }

    .legal-numbered-clauses {
        padding-left: 22px;
    }
    .legal-numbered-clauses li {
        margin-bottom: 20px;
        text-align: justify;
    }

    .critical-notice-callout {
        background-color: #fffaf0;
        border-left: 4px solid #e0a96d;
        padding: 12px 18px;
        margin-top: 10px;
        border-radius: 0 4px 4px 0;
        font-size: 11pt;
    }

    .formal-declaration-statement {
        border: 1px solid #0d1b2a;
        padding: 18px;
        background-color: #fdfefe;
        margin-top: 35px;
        text-align: justify;
    }
    .formal-declaration-statement h5 {
        font-weight: bold;
        font-size: 11.5pt;
        margin-bottom: 8px;
        color: #0d1b2a;
        text-align: center;
        letter-spacing: 1px;
    }

    .signature-container-table {
        margin-top: 60px;
    }
    .signature-capture-box {
        padding: 10px;
    }
    .sig-line {
        border-top: 1.5px solid #111111;
        width: 85%;
        margin: 0 auto 12px;
    }
    .sig-title {
        font-size: 11pt;
        font-weight: bold;
        margin-bottom: 2px;
    }
    .sig-date-placeholder {
        font-size: 9.5pt;
        color: #444444;
        border: 1px solid #e1e1e1;
        display: inline-block;
        padding: 4px 12px;
        background: #fafafa;
    }

    .agreement-legal-footer {
        position: absolute;
        bottom: 0;
        left: 15px;
        right: 15px;
    }
    .footer-line {
        height: 1px;
        background: #dddddd;
        margin-bottom: 8px;
    }

    @media print {
        body * {
            visibility: hidden;
        }
        .nxl-content, .agreement-screen-scroll-container, .legal-agreement-master, .legal-agreement-master * {
            visibility: visible;
        }
        .agreement-screen-scroll-container {
            overflow: visible;
            padding: 0;
            background: none;
            border: none;
        }
        .legal-agreement-master {
            box-shadow: none;
            padding: 0;
            margin: 0;
            width: 100%;
        }
    }
</style>