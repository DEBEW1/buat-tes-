<?php
session_start();
// Perbaiki logika session check
if (empty($_SESSION['login']) || !in_array($_SESSION['login'], ['admin', 'petugas'])) {
    header("Location: ../index.php?page=login");
    die();
}

include "../config/koneksi.php";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Sistem Pengaduan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .sidebar { background: #2c3e50; min-height: 100vh; }
        .nav-link { color: #ecf0f1; transition: all 0.3s; }
        .nav-link:hover { background: #34495e; color: #fff; }
        .nav-link.active { background: #3498db; color: #fff; }
        .main-content { background: #fff; border-radius: 15px; margin: 20px; padding: 30px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
    </style>
</head>

<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 sidebar p-0">
                <div class="p-3 text-white">
                    <h4><i class="bi bi-building-check"></i> Admin Panel</h4>
                    <hr class="text-white">
                </div>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= !isset($_GET['page']) ? 'active' : '' ?>" href="index.php">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (isset($_GET['page']) && $_GET['page'] == 'pengaduan') ? 'active' : '' ?>" href="index.php?page=pengaduan">
                            <i class="bi bi-chat-dots"></i> Data Pengaduan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (isset($_GET['page']) && $_GET['page'] == 'tanggapan') ? 'active' : '' ?>" href="index.php?page=tanggapan">
                            <i class="bi bi-reply-all"></i> Data Tanggapan
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (isset($_GET['page']) && $_GET['page'] == 'masyarakat') ? 'active' : '' ?>" href="index.php?page=masyarakat">
                            <i class="bi bi-people"></i> Data Masyarakat
                        </a>
                    </li>
                    <?php if ($_SESSION['login'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= (isset($_GET['page']) && $_GET['page'] == 'petugas') ? 'active' : '' ?>" href="index.php?page=petugas">
                            <i class="bi bi-shield-lock"></i> Data Petugas
                        </a>
                    </li>
                    <?php endif; ?>
                    <hr class="text-white">
                    <li class="nav-item">
                        <a class="nav-link" href="../logout.php" onclick="return confirm('Yakin ingin logout?')">
                            <i class="bi bi-box-arrow-right"></i> Logout
                        </a>
                    </li>
                </ul>
            </nav>

            <!-- Main Content -->
            <main class="col-md-10 main-content">
                <?php
                if (isset($_GET['page'])) {
                    $page = $_GET['page'];

                    switch ($page) {
                        case 'pengaduan':
                            include 'data_pengaduan.php';
                            break;
                        case 'tanggapan':
                            include 'data_tanggapan.php';
                            break;
                        case 'masyarakat':
                            include 'data_masyarakat.php';
                            break;
                        case 'petugas':
                            if ($_SESSION['login'] === 'admin') {
                                include 'data_petugas.php';
                            } else {
                                echo "<div class='alert alert-danger'>Akses ditolak. Hanya admin yang dapat mengakses halaman ini.</div>";
                            }
                            break;
                        default:
                            echo "<div class='alert alert-warning'>Halaman tidak tersedia</div>";
                            break;
                    }
                } else {
                    include 'home.php';
                }
                ?>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>