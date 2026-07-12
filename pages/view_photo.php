<?php
$filePath = "";

if (isset($_GET['file']) && !empty($_GET['file'])) {
    $file = basename($_GET['file']);
    $filePath = "public/uploads/payment_slip/" . $file;

    if (!file_exists($filePath)) {
        die("Image not found.");
    }
} else {
    die("No file specified.");
}
?>

<div class="container pt-3">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card shadow">
                <div class="card-header text-center bg-info text-white">
                    <h4 class="mb-0">Transaction Slip</h4>
                </div>
                <div class="card-body text-center">
                    <img src="<?php echo htmlspecialchars($filePath); ?>"
                         alt="Transaction Slip"
                         class="img-fluid rounded border"
                         style="max-height:600px; object-fit:contain;">
                </div>
                <div class="card-footer d-flex justify-content-between">
                    <button class="btn btn-secondary" onclick="goBack()">
                        ← Back
                    </button>
                    <a href="<?php echo htmlspecialchars($filePath); ?>"
                       download
                       class="btn btn-success">
                        ⬇ Download
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function goBack() {
    if (document.referrer) {
        window.history.back();
    } else {
        window.location.href = "index.php";
    }
}
</script>