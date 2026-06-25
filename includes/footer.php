<!-- [ Footer ] Start -->
<footer class="footer py-3 border-top">
    <div class="container text-center d-flex justify-content-between align-items-center">

        <div class="text-muted small mb-1">
            © <span id="year"></span> All Rights Reserved.
        </div>

        <div class="small">
            Powered by
            <a href="https://gsc.co.com" target="_blank"
                class="fw-semibold text-primary text-decoration-none">
                GSC
            </a>
            <i class="bi bi-lightning-charge-fill text-warning ms-1"></i>
        </div>

    </div>
</footer>

<script>
    document.getElementById("year").textContent = new Date().getFullYear();
</script>
<!-- [ Footer ] End -->