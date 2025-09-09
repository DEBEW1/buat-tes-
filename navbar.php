
<nav class="navbar navbar-expand-lg bg-gradient-primary">
    <div class="container">
        <a class="navbar-brand text-white" href="index.php">
            <i class="bi bi-building-gear me-2"></i>
            Pengaduan Digital
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="navbar-nav ms-auto">
                <a class="nav-link text-white" href="index.php">Beranda</a>
                <?php if(basename($_SERVER['PHP_SELF']) == 'login.php'): ?>
                    <a class="nav-link text-white" href="index.php?page=registrasi">Daftar</a>
                <?php else: ?>
                    <a class="nav-link text-white" href="index.php?page=login">Masuk</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>