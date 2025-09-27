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
    <title>Admin - Sistem Pengaduan Masyarakat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #2d4a3e;
            --secondary-color: #34495e;
            --accent-color: #3498db;
            --success-color: #27ae60;
            --warning-color: #f39c12;
            --danger-color: #e74c3c;
            --light-bg: #ecf0f1;
            --shadow: 0 4px 15px rgba(0,0,0,0.1);
            --shadow-lg: 0 10px 30px rgba(0,0,0,0.15);
            --gradient-primary: linear-gradient(135deg, #2d4a3e 0%, #3e5c50 100%);
            --gradient-accent: linear-gradient(135deg, #3498db 0%, #2980b9 100%);
        }

        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            min-height: 100vh; 
            font-family: 'Inter', system-ui, sans-serif;
        }

        /* Sidebar Styling */
        .sidebar { 
            background: var(--gradient-primary);
            min-height: 100vh; 
            position: fixed;
            top: 0;
            left: 0;
            width: 280px;
            z-index: 1000;
            transition: all 0.3s ease;
            box-shadow: 2px 0 15px rgba(0,0,0,0.1);
            border-right: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-header {
            padding: 2rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background: rgba(0,0,0,0.1);
        }

        .sidebar-header h4 {
            color: #fff;
            margin: 0;
            font-weight: 700;
            font-family: 'Plus Jakarta Sans', sans-serif;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-header .brand-icon {
            width: 40px;
            height: 40px;
            background: var(--gradient-accent);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .sidebar-nav {
            padding: 1.5rem 0;
        }

        .nav-link { 
            color: rgba(255,255,255,0.8) !important;
            padding: 15px 24px;
            margin: 6px 16px;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            border: none;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.1), transparent);
            transition: left 0.6s;
        }

        .nav-link:hover::before {
            left: 100%;
        }

        .nav-link i {
            font-size: 1.1rem;
            width: 20px;
            text-align: center;
        }

        .nav-link:hover { 
            background: rgba(255,255,255,0.15);
            color: #fff !important;
            transform: translateX(8px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .nav-link.active { 
            background: var(--gradient-accent);
            color: #fff !important;
            box-shadow: 0 6px 20px rgba(52, 152, 219, 0.4);
            transform: translateX(8px);
        }

        .nav-divider {
            border-color: rgba(255,255,255,0.2);
            margin: 1.5rem 24px;
        }

        /* User Info Section */
        .user-info {
            padding: 1rem 1.5rem;
            margin: 1rem 16px;
            background: rgba(0,0,0,0.2);
            border-radius: 12px;
            color: rgba(255,255,255,0.9);
        }

        .user-info small {
            display: block;
            opacity: 0.7;
            margin-bottom: 4px;
            font-size: 0.8rem;
        }

        .user-info strong {
            font-size: 0.9rem;
            font-weight: 600;
        }

        /* Logout Button */
        .logout-btn {
            margin: 16px;
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            border: none;
            color: white;
            padding: 12px 20px;
            border-radius: 12px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-weight: 500;
            text-decoration: none;
        }

        .logout-btn:hover {
            background: linear-gradient(135deg, #c0392b, #a93226);
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(192, 57, 43, 0.4);
            color: white;
        }

        /* Main Content */
        .main-content { 
            margin-left: 280px;
            padding: 0;
            min-height: 100vh;
        }

        .content-wrapper {
            background: #fff; 
            border-radius: 20px; 
            margin: 25px; 
            padding: 0;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }

        .content-header {
            background: var(--gradient-accent);
            color: white;
            padding: 2rem 2.5rem;
            border-bottom: none;
        }

        .content-header h5 {
            margin: 0;
            font-weight: 700;
            font-family: 'Plus Jakarta Sans', sans-serif;
            display: flex;
            align-items: center;
            gap: 15px;
            font-size: 1.5rem;
        }

        .content-header i {
            font-size: 1.8rem;
            opacity: 0.9;
        }

        .content-body {
            padding: 2.5rem;
        }

        /* Enhanced Components */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: var(--shadow);
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-lg);
        }

        .card-header {
            border-bottom: 1px solid rgba(0,0,0,0.05);
            font-weight: 600;
            padding: 1.5rem 2rem;
            background: rgba(52, 152, 219, 0.05);
        }

        .card-body {
            padding: 2rem;
        }

        .btn {
            border-radius: 10px;
            font-weight: 500;
            padding: 10px 20px;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border: none;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }

        .btn:hover::before {
            left: 100%;
        }

        .btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.15);
        }

        .btn-primary {
            background: var(--gradient-accent);
            color: white;
        }

        .btn-success {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, #f39c12, #e67e22);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }

        .btn-info {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
        }

        .table {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }

        .table thead th {
            background: var(--gradient-accent);
            color: white;
            border: none;
            font-weight: 600;
            padding: 1rem;
        }

        .table tbody tr {
            transition: all 0.2s ease;
        }

        .table tbody tr:hover {
            background: rgba(52, 152, 219, 0.05);
            transform: scale(1.01);
        }

        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: rgba(0,0,0,0.05);
        }

        .alert {
            border: none;
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow);
        }

        .badge {
            padding: 8px 12px;
            border-radius: 20px;
            font-weight: 500;
        }

        /* Quick Actions */
        .quick-actions {
            background: rgba(52, 152, 219, 0.05);
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .quick-actions h6 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .quick-btn {
            background: white;
            border: 2px solid rgba(52, 152, 219, 0.2);
            color: var(--primary-color);
            padding: 10px 16px;
            border-radius: 8px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin: 4px;
            transition: all 0.3s ease;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .quick-btn:hover {
            background: var(--gradient-accent);
            color: white;
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(52, 152, 219, 0.3);
            text-decoration: none;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                position: relative;
                min-height: auto;
            }

            .main-content {
                margin-left: 0;
            }

            .content-wrapper {
                margin: 15px;
                border-radius: 15px;
            }

            .content-header,
            .content-body {
                padding: 1.5rem;
            }

            .sidebar-header h4 {
                font-size: 1.2rem;
            }
        }

        /* Loading Animation */
        .loading {
            display: inline-block;
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255,255,255,.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-on-load {
            animation: fadeInUp 0.8s ease forwards;
        }

        .animate-on-load.delay-1 { animation-delay: 0.2s; }
        .animate-on-load.delay-2 { animation-delay: 0.4s; }
        .animate-on-load.delay-3 { animation-delay: 0.6s; }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="sidebar d-flex flex-column">
            <!-- Sidebar Header -->
            <div class="sidebar-header">
                <h4>
                    <div class="brand-icon">
                        <i class="bi bi-building-check"></i>
                    </div>
                    <div>
                        <?= $_SESSION['login'] == 'admin' ? 'Admin Panel' : 'Petugas Panel' ?>
                    </div>
                </h4>
            </div>
            
            <!-- Navigation Menu -->
            <div class="sidebar-nav flex-grow-1">
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link <?= !isset($_GET['page']) ? 'active' : '' ?>" href="index.php">
                            <i class="bi bi-speedometer2"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (isset($_GET['page']) && $_GET['page'] == 'pengaduan') ? 'active' : '' ?>" href="index.php?page=pengaduan">
                            <i class="bi bi-chat-dots"></i>
                            <span>Data Pengaduan</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (isset($_GET['page']) && $_GET['page'] == 'tanggapan') ? 'active' : '' ?>" href="index.php?page=tanggapan">
                            <i class="bi bi-reply-all"></i>
                            <span>Data Tanggapan</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= (isset($_GET['page']) && $_GET['page'] == 'masyarakat') ? 'active' : '' ?>" href="index.php?page=masyarakat">
                            <i class="bi bi-people"></i>
                            <span>Data Masyarakat</span>
                        </a>
                    </li>
                    <?php if ($_SESSION['login'] === 'admin'): ?>
                    <li class="nav-item">
                        <a class="nav-link <?= (isset($_GET['page']) && $_GET['page'] == 'petugas') ? 'active' : '' ?>" href="index.php?page=petugas">
                            <i class="bi bi-shield-lock"></i>
                            <span>Data Petugas</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>

            <!-- User Info -->
            <div class="user-info">
                <small>Masuk sebagai:</small>
                <strong><?= ucfirst($_SESSION['login']) ?>: <?= isset($_SESSION['nama']) ? $_SESSION['nama'] : $_SESSION['username'] ?></strong>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <h6>
                    <i class="bi bi-lightning-fill me-2"></i>
                    Aksi Cepat
                </h6>
                <div class="d-flex flex-wrap">
                    <a href="../index.php" class="quick-btn">
                        <i class="bi bi-house"></i>
                        <span>Beranda</span>
                    </a>
                </div>
            </div>

            <hr class="nav-divider">
            
            <!-- Logout Button -->
            <a href="#" class="logout-btn" onclick="confirmLogout()">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </a>
        </nav>

        <!-- Main Content -->
        <main class="main-content flex-grow-1">
            <div class="content-wrapper animate-on-load">
                <?php
                $page_title = "Dashboard";
                $page_icon = "bi-speedometer2";

                if (isset($_GET['page'])) {
                    $page = $_GET['page'];
                    
                    switch ($page) {
                        case 'pengaduan':
                            $page_title = "Data Pengaduan";
                            $page_icon = "bi-chat-dots";
                            break;
                        case 'tanggapan':
                            $page_title = "Data Tanggapan";
                            $page_icon = "bi-reply-all";
                            break;
                        case 'masyarakat':
                            $page_title = "Data Masyarakat";
                            $page_icon = "bi-people";
                            break;
                        case 'petugas':
                            $page_title = "Data Petugas";
                            $page_icon = "bi-shield-lock";
                            break;
                    }
                }
                ?>
                
                <!-- Content Header -->
                <div class="content-header">
                    <h5>
                        <i class="bi <?= $page_icon ?>"></i>
                        <?= $page_title ?>
                    </h5>
                </div>

                <!-- Content Body -->
                <div class="content-body">
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
                                    echo "<div class='alert alert-danger animate-on-load delay-1'>
                                            <i class='bi bi-shield-exclamation me-2'></i>
                                            <strong>Akses Ditolak!</strong> 
                                            Hanya admin yang dapat mengakses halaman ini.
                                          </div>";
                                }
                                break;
                            default:
                                echo "<div class='alert alert-warning animate-on-load delay-1'>
                                        <i class='bi bi-exclamation-triangle me-2'></i>
                                        <strong>Perhatian!</strong> 
                                        Halaman yang Anda cari tidak tersedia.
                                      </div>";
                                break;
                        }
                    } else {
                        include 'home.php';
                    }
                    ?>
                </div>
            </div>
        </main>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <script>
        // Fungsi untuk konfirmasi logout
        function confirmLogout() {
            Swal.fire({
                title: 'Konfirmasi Logout',
                text: 'Apakah Anda yakin ingin keluar dari sistem?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#95a5a6',
                confirmButtonText: '<i class="bi bi-box-arrow-right"></i> Ya, Logout',
                cancelButtonText: '<i class="bi bi-x"></i> Batal',
                reverseButtons: true,
                customClass: {
                    popup: 'animate-on-load'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Tampilkan loading
                    Swal.fire({
                        title: 'Sedang Logout...',
                        html: '<div class="loading"></div>',
                        showConfirmButton: false,
                        allowOutsideClick: false,
                        customClass: {
                            popup: 'animate-on-load'
                        }
                    });
                    
                    // Redirect ke logout.php setelah 1 detik
                    setTimeout(() => {
                        window.location.href = '../logout.php';
                    }, 1000);
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
                alerts.forEach(function(alert) {
                    alert.style.transition = 'opacity 0.5s, transform 0.5s';
                    alert.style.opacity = '0';
                    alert.style.transform = 'translateY(-20px)';
                    setTimeout(function() {
                        if (alert.parentNode) {
                            alert.parentNode.removeChild(alert);
                        }
                    }, 500);
                });
            }, 5000);

            // Add smooth transitions to cards
            const cards = document.querySelectorAll('.card');
            cards.forEach(function(card, index) {
                card.style.animationDelay = (index * 0.1) + 's';
                card.classList.add('animate-on-load');
            });

            // Enhanced form submissions
            document.querySelectorAll('form').forEach(function(form) {
                form.addEventListener('submit', function(e) {
                    const submitBtn = form.querySelector('button[type="submit"], input[type="submit"]');
                    if (submitBtn && !submitBtn.disabled) {
                        const originalText = submitBtn.innerHTML;
                        submitBtn.innerHTML = '<div class="loading me-2"></div>Memproses...';
                        submitBtn.disabled = true;
                        
                        // Re-enable after timeout as fallback
                        setTimeout(() => {
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                        }, 10000);
                    }
                });
            });

            // Smooth hover effects for navigation
            const navLinks = document.querySelectorAll('.nav-link');
            navLinks.forEach(function(link) {
                link.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(8px)';
                });
                
                link.addEventListener('mouseleave', function() {
                    if (!this.classList.contains('active')) {
                        this.style.transform = 'translateX(0)';
                    }
                });
            });
        });

        // Global function for showing success message
        function showSuccess(message) {
            Swal.fire({
                icon: 'success',
                title: 'Berhasil!',
                text: message,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                toast: true,
                position: 'top-end'
            });
        }

        // Global function for showing error message
        function showError(message) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: message,
                confirmButtonText: 'OK',
                customClass: {
                    confirmButton: 'btn btn-danger'
                }
            });
        }
    </script>
</body>
</html>