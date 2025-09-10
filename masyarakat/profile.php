<?php
// masyarakat/profile.php
session_start();
require_once '../config/koneksi.php';

// Cek apakah user sudah login dan levelnya masyarakat
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'masyarakat') {
    header('Location: ../index.php?page=login');
    exit();
}

try {
    // Ambil data lengkap user
    $sql = "SELECT * FROM masyarakat WHERE nik = ?";
    $stmt = $db->query($sql, [$_SESSION['user_id']]);
    $user = $stmt->fetch();
    
    if (!$user) {
        $_SESSION['error'] = "Data pengguna tidak ditemukan";
        header('Location: dashboard.php');
        exit();
    }
    
    // Ambil statistik pengaduan user
    $stats_sql = "SELECT 
                    COUNT(*) as total_pengaduan,
                    SUM(CASE WHEN status = '0' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'proses' THEN 1 ELSE 0 END) as proses,
                    SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai,
                    MIN(tgl_pengaduan) as first_complaint,
                    MAX(tgl_pengaduan) as last_complaint
                  FROM pengaduan WHERE nik = ?";
    $stats_stmt = $db->query($stats_sql, [$_SESSION['user_id']]);
    $stats = $stats_stmt->fetch();
    
} catch (Exception $e) {
    $_SESSION['error'] = "Terjadi kesalahan saat mengambil data";
    header('Location: dashboard.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Sistem Pengaduan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2d4a3e;
            --secondary: #87a96b;
            --success: #6b8e5a;
            --warning: #c9a876;
            --info: #7ba098;
            --danger: #d4756b;
        }
        
        body {
            background-color: #f8faf9;
            font-family: 'Inter', sans-serif;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 8px;
            margin: 5px 0;
            transition: all 0.3s ease;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.2);
            color: white;
        }
        
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-2px);
        }
        
        .profile-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border-radius: 15px 15px 0 0;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            margin: 0 auto 20px;
            border: 4px solid rgba(255,255,255,0.3);
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--secondary) 0%, var(--success) 100%);
            color: white;
        }
        
        .stat-card-warning {
            background: linear-gradient(135deg, var(--warning) 0%, #d4b986 100%);
        }
        
        .stat-card-info {
            background: linear-gradient(135deg, var(--info) 0%, #8bb0a8 100%);
        }
        
        .stat-card-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #de857b 100%);
        }
        
        .info-item {
            padding: 12px 0;
            border-bottom: 1px solid #e9ecef;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
            border-radius: 10px;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #234035 0%, #7a9960 100%);
        }
        
        .btn-outline-primary {
            border-color: var(--secondary);
            color: var(--secondary);
        }
        
        .btn-outline-primary:hover {
            background-color: var(--secondary);
            border-color: var(--secondary);
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
        }
        
        .form-control:focus {
            border-color: var(--secondary);
            box-shadow: 0 0 0 0.2rem rgba(135, 169, 107, 0.25);
        }
        
        .activity-timeline {
            max-height: 400px;
            overflow-y: auto;
        }
        
        .timeline-item {
            border-left: 3px solid var(--secondary);
            padding-left: 15px;
            margin-bottom: 15px;
            position: relative;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -6px;
            top: 5px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: var(--secondary);
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
                <div class="position-sticky pt-3">
                    <div class="text-center mb-4">
                        <h5 class="text-white">Dashboard Masyarakat</h5>
                        <p class="text-white-50 mb-0">Selamat datang, <?php echo $_SESSION['nama']; ?></p>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="pengaduan.php">
                                <i class="bi bi-file-earmark-text"></i> Buat Pengaduan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="riwayat.php">
                                <i class="bi bi-clock-history"></i> Riwayat Pengaduan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="profile.php">
                                <i class="bi bi-person"></i> Profile
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-warning" href="../config/logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Profile Saya</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                            <i class="bi bi-pencil"></i> Edit Profile
                        </button>
                    </div>
                </div>
                
                <!-- Alert Messages -->
                <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <div class="row">
                    <!-- Profile Info -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="profile-header text-center p-4">
                                <div class="profile-avatar">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                                <h3><?php echo htmlspecialchars($user['nama']); ?></h3>
                                <p class="mb-0 opacity-75">@<?php echo htmlspecialchars($user['username']); ?></p>
                                <small class="opacity-75">Bergabung sejak <?php echo date('M Y', strtotime($user['created_at'])); ?></small>
                            </div>
                            <div class="card-body">
                                <h5 class="card-title mb-4">
                                    <i class="bi bi-info-circle text-primary"></i> Informasi Pribadi
                                </h5>
                                
                                <div class="info-item">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <strong><i class="bi bi-person-badge text-muted"></i> NIK</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            <?php echo htmlspecialchars($user['nik']); ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <strong><i class="bi bi-person text-muted"></i> Nama Lengkap</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            <?php echo htmlspecialchars($user['nama']); ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <strong><i class="bi bi-at text-muted"></i> Username</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            <?php echo htmlspecialchars($user['username']); ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <strong><i class="bi bi-phone text-muted"></i> No. Telepon</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            <?php echo htmlspecialchars($user['telp']); ?>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="info-item">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <strong><i class="bi bi-calendar-plus text-muted"></i> Bergabung</strong>
                                        </div>
                                        <div class="col-sm-8">
                                            <?php echo date('d F Y, H:i', strtotime($user['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Statistics & Activity -->
                    <div class="col-lg-4">
                        <!-- Statistics -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bi bi-bar-chart"></i> Statistik Pengaduan
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-6">
                                        <div class="card stat-card text-center">
                                            <div class="card-body py-3">
                                                <div class="h4 mb-1"><?php echo $stats['total_pengaduan']; ?></div>
                                                <small class="opacity-75">Total</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card stat-card-warning text-center">
                                            <div class="card-body py-3">
                                                <div class="h4 mb-1"><?php echo $stats['pending']; ?></div>
                                                <small class="opacity-75">Pending</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card stat-card-info text-center">
                                            <div class="card-body py-3">
                                                <div class="h4 mb-1"><?php echo $stats['proses']; ?></div>
                                                <small class="opacity-75">Proses</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="card stat-card-danger text-center">
                                            <div class="card-body py-3">
                                                <div class="h4 mb-1"><?php echo $stats['selesai']; ?></div>
                                                <small class="opacity-75">Selesai</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Activity Summary -->
                        <div class="card">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bi bi-activity"></i> Aktivitas
                                </h6>
                            </div>
                            <div class="card-body">
                                <?php if ($stats['total_pengaduan'] > 0): ?>
                                    <div class="activity-timeline">
                                        <div class="timeline-item">
                                            <h6 class="mb-1">Pengaduan Pertama</h6>
                                            <small class="text-muted">
                                                <?php echo date('d M Y', strtotime($stats['first_complaint'])); ?>
                                            </small>
                                        </div>
                                        
                                        <?php if ($stats['last_complaint'] != $stats['first_complaint']): ?>
                                            <div class="timeline-item">
                                                <h6 class="mb-1">Pengaduan Terakhir</h6>
                                                <small class="text-muted">
                                                    <?php echo date('d M Y', strtotime($stats['last_complaint'])); ?>
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="timeline-item">
                                            <h6 class="mb-1">Status Aktif</h6>
                                            <small class="text-success">Akun Terverifikasi</small>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-3">
                                        <i class="bi bi-clock-history h2 text-muted"></i>
                                        <p class="text-muted mb-0">Belum ada aktivitas</p>
                                        <small class="text-muted">Mulai dengan membuat pengaduan pertama</small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil"></i> Edit Profile
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="update_profile.php" method="POST">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="nama" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama" name="nama" 
                                   value="<?php echo htmlspecialchars($user['nama']); ?>" 
                                   required maxlength="35">
                        </div>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username" 
                                   value="<?php echo htmlspecialchars($user['username']); ?>" 
                                   required maxlength="25">
                            <small class="form-text text-muted">Username hanya boleh huruf, angka, dan underscore</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="telp" class="form-label">No. Telepon</label>
                            <input type="text" class="form-control" id="telp" name="telp" 
                                   value="<?php echo htmlspecialchars($user['telp']); ?>" 
                                   required maxlength="13">
                        </div>
                        
                        <div class="alert alert-info">
                            <small>
                                <i class="bi bi-info-circle"></i>
                                NIK tidak dapat diubah untuk alasan keamanan.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Change Password Modal -->
    <div class="modal fade" id="changePasswordModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-lock"></i> Ubah Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="change_password.php" method="POST">
                <div class="modal-body">
                        <div class="mb-3">
                            <label for="current_password" class="form-label">Password Saat Ini</label>
                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Password Baru</label>
                            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
                            <small class="form-text text-muted">Minimal 6 karakter</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                        </div>
                        
                        <div class="alert alert-warning">
                            <small>
                                <i class="bi bi-exclamation-triangle"></i>
                                Pastikan password baru Anda aman dan mudah diingat.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">Ubah Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Form validation for edit profile
        document.querySelector('#editProfileModal form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value;
            const nama = document.getElementById('nama').value;
            const telp = document.getElementById('telp').value;
            
            // Username validation
            const usernameRegex = /^[a-zA-Z0-9_]+$/;
            if (!usernameRegex.test(username)) {
                e.preventDefault();
                alert('Username hanya boleh mengandung huruf, angka, dan underscore');
                return false;
            }
            
            // Phone number validation
            const phoneRegex = /^[0-9]+$/;
            if (!phoneRegex.test(telp)) {
                e.preventDefault();
                alert('Nomor telepon hanya boleh mengandung angka');
                return false;
            }
            
            if (telp.length < 10 || telp.length > 13) {
                e.preventDefault();
                alert('Nomor telepon harus antara 10-13 digit');
                return false;
            }
        });

        // Form validation for change password
        document.querySelector('#changePasswordModal form').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Konfirmasi password tidak cocok');
                document.getElementById('confirm_password').focus();
                return false;
            }
            
            if (newPassword.length < 6) {
                e.preventDefault();
                alert('Password minimal 6 karakter');
                document.getElementById('new_password').focus();
                return false;
            }
        });

        // Add button to trigger change password modal
        document.addEventListener('DOMContentLoaded', function() {
            const editButton = document.querySelector('[data-bs-target="#editProfileModal"]');
            if (editButton) {
                const changePasswordBtn = document.createElement('button');
                changePasswordBtn.className = 'btn btn-outline-primary ms-2';
                changePasswordBtn.setAttribute('data-bs-toggle', 'modal');
                changePasswordBtn.setAttribute('data-bs-target', '#changePasswordModal');
                changePasswordBtn.innerHTML = '<i class="bi bi-lock"></i> Ubah Password';
                editButton.parentNode.appendChild(changePasswordBtn);
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const alertInstance = new bootstrap.Alert(alert);
                alertInstance.close();
            });
        }, 5000);
    </script>
</body>
</html>