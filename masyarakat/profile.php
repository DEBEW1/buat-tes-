<?php
// masyarakat/profile.php
session_start();
require_once '../config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'masyarakat') {
    header('Location: ../index.php?page=login');
    exit();
}

try {
    // Ambil data user
    $sql = "SELECT * FROM masyarakat WHERE nik = ?";
    $user = $db->fetch($sql, [$_SESSION['user_id']]);
    
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
    $stats = $db->fetch($stats_sql, [$_SESSION['user_id']]);
    
    // Set default values if no stats found
    if (!$stats) {
        $stats = [
            'total_pengaduan' => 0,
            'pending' => 0,
            'proses' => 0,
            'selesai' => 0,
            'first_complaint' => null,
            'last_complaint' => null
        ];
    }
    
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
            --primary:#2d4a3e;
            --secondary:#87a96b;
        }
        body { background:#f8faf9; font-family:'Inter',sans-serif; }
        .sidebar {
            min-height:100vh;
            background:linear-gradient(135deg,var(--primary),var(--secondary));
            box-shadow:2px 0 10px rgba(0,0,0,.15);
        }
        .sidebar .nav-link {
            color:rgba(255,255,255,0.85);
            border-radius:8px;
            margin:5px 0;
            transition:.3s;
        }
        .sidebar .nav-link:hover,.sidebar .nav-link.active {
            background:rgba(255,255,255,0.2);
            color:#fff;
        }
        .card { border-radius:15px; border:none; box-shadow:0 4px 6px rgba(0,0,0,.1); }
        /* Mobile sidebar (offcanvas style) */
        @media(max-width:768px){
            .sidebar {
                position:fixed;
                top:0; left:-250px;
                width:220px;
                z-index:1050;
                transition:left .3s;
            }
            .sidebar.show { left:0; }
            .overlay {
                position:fixed;
                top:0; left:0;
                width:100%; height:100%;
                background:rgba(0,0,0,.5);
                z-index:1040;
                display:none;
            }
            .overlay.show { display:block; }
        }
    </style>
</head>
<body>
<div class="overlay" id="overlay"></div>
<div class="container-fluid">
  <div class="row">
    <!-- Sidebar -->
    <nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block sidebar collapse">
      <div class="position-sticky pt-3">
        <div class="text-center mb-4">
            <h5 class="text-white">Dashboard Masyarakat</h5>
            <p class="text-white-50 mb-0">Halo, <?= htmlspecialchars($_SESSION['nama']); ?></p>
        </div>
        <ul class="nav flex-column">
          <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="pengaduan.php"><i class="bi bi-file-earmark-text"></i> Buat Pengaduan</a></li>
          <li class="nav-item"><a class="nav-link" href="riwayat.php"><i class="bi bi-clock-history"></i> Riwayat Pengaduan</a></li>
          <li class="nav-item"><a class="nav-link active" href="profile.php"><i class="bi bi-person"></i> Profil</a></li>
          <li class="nav-item mt-3"><a class="nav-link text-warning" href="logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
      </div>
    </nav>

    <!-- Main -->
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
      <!-- Topbar -->
      <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <div class="d-flex align-items-center">
            <!-- Hamburger button -->
            <button class="btn btn-outline-secondary d-md-none me-2" id="menuToggle">
                <i class="bi bi-list"></i>
            </button>
            <h1 class="h4 mb-0">Profil Saya</h1>
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

      <!-- Profile Card -->
      <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-person-circle"></i> Data Akun</h5>
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#changePasswordModal">
                <i class="bi bi-key"></i> Ganti Password
            </button>
        </div>
        <div class="card-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label"><strong>NIK</strong></label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($user['nik']); ?>" readonly>
            </div>
            <div class="col-md-6">
              <label class="form-label"><strong>Nama Lengkap</strong></label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($user['nama']); ?>" readonly>
            </div>
            <div class="col-md-6">
              <label class="form-label"><strong>Username</strong></label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($user['username']); ?>" readonly>
            </div>
            <div class="col-md-6">
              <label class="form-label"><strong>Nomor Telepon</strong></label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($user['telp']); ?>" readonly>
            </div>
          </div>
        </div>
      </div>

      <!-- Statistik -->
      <div class="card mb-4">
        <div class="card-header"><h6 class="mb-0"><i class="bi bi-bar-chart"></i> Statistik Pengaduan</h6></div>
        <div class="card-body">
          <div class="row text-center">
            <div class="col-6 col-md-3 mb-3">
                <div class="p-3 bg-primary text-white rounded">
                    <h3 class="mb-1"><?= $stats['total_pengaduan']; ?></h3>
                    <small>Total Pengaduan</small>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="p-3 bg-warning text-dark rounded">
                    <h3 class="mb-1"><?= $stats['pending']; ?></h3>
                    <small>Pending</small>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="p-3 bg-info text-white rounded">
                    <h3 class="mb-1"><?= $stats['proses']; ?></h3>
                    <small>Diproses</small>
                </div>
            </div>
            <div class="col-6 col-md-3 mb-3">
                <div class="p-3 bg-success text-white rounded">
                    <h3 class="mb-1"><?= $stats['selesai']; ?></h3>
                    <small>Selesai</small>
                </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Activity Info -->
      <?php if ($stats['total_pengaduan'] > 0): ?>
      <div class="card mb-4">
        <div class="card-header"><h6 class="mb-0"><i class="bi bi-activity"></i> Aktivitas Pengaduan</h6></div>
        <div class="card-body">
          <div class="row">
            <?php if ($stats['first_complaint']): ?>
            <div class="col-md-6 mb-3">
              <div class="d-flex align-items-center">
                <i class="bi bi-calendar-plus text-success me-2"></i>
                <div>
                    <small class="text-muted">Pengaduan Pertama:</small><br>
                    <strong><?= date('d M Y', strtotime($stats['first_complaint'])); ?></strong>
                </div>
              </div>
            </div>
            <?php endif; ?>
            <?php if ($stats['last_complaint']): ?>
            <div class="col-md-6 mb-3">
              <div class="d-flex align-items-center">
                <i class="bi bi-calendar-check text-info me-2"></i>
                <div>
                    <small class="text-muted">Pengaduan Terakhir:</small><br>
                    <strong><?= date('d M Y', strtotime($stats['last_complaint'])); ?></strong>
                </div>
              </div>
            </div>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Quick Actions -->
      <div class="card">
        <div class="card-header"><h6 class="mb-0"><i class="bi bi-lightning"></i> Aksi Cepat</h6></div>
        <div class="card-body">
          <div class="d-grid gap-2 d-md-flex">
            <a href="pengaduan.php" class="btn btn-primary">
                <i class="bi bi-plus-circle"></i> Buat Pengaduan Baru
            </a>
            <a href="riwayat.php" class="btn btn-outline-secondary">
                <i class="bi bi-clock-history"></i> Lihat Riwayat
            </a>
            <a href="dashboard.php" class="btn btn-outline-info">
                <i class="bi bi-speedometer2"></i> Ke Dashboard
            </a>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>

<!-- Modal Change Password -->
<div class="modal fade" id="changePasswordModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title"><i class="bi bi-key"></i> Ganti Password</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <form action="change_password.php" method="POST" id="changePasswordForm">
        <div class="modal-body">
          <div class="mb-3">
            <label for="current_password" class="form-label">Password Saat Ini</label>
            <input type="password" class="form-control" id="current_password" name="current_password" required>
          </div>
          <div class="mb-3">
            <label for="new_password" class="form-label">Password Baru</label>
            <input type="password" class="form-control" id="new_password" name="new_password" required minlength="6">
            <div class="form-text">Minimal 6 karakter</div>
          </div>
          <div class="mb-3">
            <label for="confirm_password" class="form-label">Konfirmasi Password Baru</label>
            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-primary">
            <i class="bi bi-check-circle"></i> Ganti Password
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const menuToggle=document.getElementById('menuToggle');
const sidebar=document.getElementById('sidebarMenu');
const overlay=document.getElementById('overlay');

if(menuToggle){
  menuToggle.addEventListener('click',()=>{
    sidebar.classList.toggle('show');
    overlay.classList.toggle('show');
  });
}
if(overlay){
  overlay.addEventListener('click',()=>{
    sidebar.classList.remove('show');
    overlay.classList.remove('show');
  });
}

// Password validation
document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
    const newPassword = document.getElementById('new_password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    if (newPassword !== confirmPassword) {
        e.preventDefault();
        alert('Konfirmasi password tidak cocok!');
        return false;
    }
    
    if (newPassword.length < 6) {
        e.preventDefault();
        alert('Password baru minimal 6 karakter!');
        return false;
    }
    
    return confirm('Apakah Anda yakin ingin mengganti password?');
});
</script>
</body>
</html>