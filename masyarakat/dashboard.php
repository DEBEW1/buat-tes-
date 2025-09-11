<?php
// masyarakat/dashboard.php
session_start();
require_once '../config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'masyarakat') {
    header('Location: ../index.php?page=login');
    exit();
}

// Ambil data pengaduan
try {
    $sql = "SELECT * FROM pengaduan WHERE nik = ? ORDER BY tgl_pengaduan DESC";
    $pengaduan = $db->fetchAll($sql, [$_SESSION['user_id']]);

    // Statistik
    $total_pengaduan   = count($pengaduan);
    $pengaduan_pending = count(array_filter($pengaduan, fn($p) => $p['status'] == '0'));
    $pengaduan_proses  = count(array_filter($pengaduan, fn($p) => $p['status'] == 'proses'));
    $pengaduan_selesai = count(array_filter($pengaduan, fn($p) => $p['status'] == 'selesai'));
} catch (Exception $e) {
    $pengaduan = [];
    $total_pengaduan = $pengaduan_pending = $pengaduan_proses = $pengaduan_selesai = 0;
}

// Greeting dinamis
$hour = date('H');
if ($hour < 12) $greeting = "Selamat Pagi";
elseif ($hour < 15) $greeting = "Selamat Siang";
elseif ($hour < 18) $greeting = "Selamat Sore";
else $greeting = "Selamat Malam";
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard Masyarakat - Sistem Pengaduan</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
:root {
    --primary:#2d4a3e; --secondary:#87a96b;
    --success:#6b8e5a; --warning:#c9a876; --info:#7ba098; --danger:#d4756b;
}
body{background:#f8faf9;font-family:'Inter',sans-serif;}
.navbar{background:linear-gradient(135deg,var(--primary),var(--secondary));}
.navbar .nav-link,.navbar-brand{color:#fff!important;}
.card{border-radius:15px;border:none;box-shadow:0 4px 6px rgba(0,0,0,.1);transition:.2s;}
.card:hover{transform:translateY(-4px);}
.stat-card{color:#fff;}
.badge-pending{background:var(--warning);} 
.badge-proses{background:var(--info);} 
.badge-selesai{background:var(--success);}
.table td{text-overflow:ellipsis;max-width:220px;overflow:hidden;white-space:nowrap;}
@media (max-width: 768px){
  .table td, .table th{font-size:0.85rem;}
  .btn, .btn-sm{font-size:0.85rem;}
}
</style>
</head>
<body>
<!-- Navbar -->
<nav class="navbar navbar-expand-lg shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="#">Sistem Pengaduan</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto text-center">
        <li class="nav-item"><a class="nav-link active" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="pengaduan.php"><i class="bi bi-file-earmark-text"></i> Buat Pengaduan</a></li>
        <li class="nav-item"><a class="nav-link" href="riwayat.php"><i class="bi bi-clock-history"></i> Riwayat</a></li>
        <li class="nav-item"><a class="nav-link" href="profile.php"><i class="bi bi-person"></i> Profil</a></li>
        <li class="nav-item"><a class="nav-link text-warning" href="#" data-bs-toggle="modal" data-bs-target="#logoutModal"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Main -->
<main class="container my-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h1 class="h5 mb-0"><?= $greeting; ?>, <span class="text-primary"><?= htmlspecialchars($_SESSION['nama']); ?></span></h1>
    <a href="pengaduan.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Buat Pengaduan Baru</a>
  </div>

  <!-- Statistik -->
  <div class="row g-3 mb-4">
    <div class="col-6 col-lg-3"><div class="card stat-card bg-success"><div class="card-body">
      <div class="d-flex justify-content-between align-items-center"><div><div>Total</div><div class="h2"><?= $total_pengaduan; ?></div></div><i class="bi bi-files h1"></i></div>
    </div></div></div>
    <div class="col-6 col-lg-3"><div class="card stat-card bg-warning text-dark"><div class="card-body">
      <div class="d-flex justify-content-between align-items-center"><div><div>Pending</div><div class="h2"><?= $pengaduan_pending; ?></div></div><i class="bi bi-clock h1"></i></div>
    </div></div></div>
    <div class="col-6 col-lg-3"><div class="card stat-card bg-info"><div class="card-body">
      <div class="d-flex justify-content-between align-items-center"><div><div>Diproses</div><div class="h2"><?= $pengaduan_proses; ?></div></div><i class="bi bi-arrow-repeat h1"></i></div>
    </div></div></div>
    <div class="col-6 col-lg-3"><div class="card stat-card bg-danger"><div class="card-body">
      <div class="d-flex justify-content-between align-items-center"><div><div>Selesai</div><div class="h2"><?= $pengaduan_selesai; ?></div></div><i class="bi bi-check-circle h1"></i></div>
    </div></div></div>
  </div>

  <!-- Pengaduan Terbaru -->
  <div class="card">
    <div class="card-header"><h5 class="mb-0">Pengaduan Terbaru</h5></div>
    <div class="card-body">
      <?php if (empty($pengaduan)): ?>
        <div class="text-center py-5">
          <i class="bi bi-file-earmark-x h1 text-muted"></i>
          <p class="text-muted">Belum ada pengaduan</p>
          <a href="pengaduan.php" class="btn btn-primary">Buat Pengaduan Pertama</a>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-sm table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th>Tanggal</th>
                <th>Isi Laporan</th>
                <th>Status</th>
                <th>Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach (array_slice($pengaduan,0,5) as $item): ?>
              <tr>
                <td><?= date('d/m/Y H:i', strtotime($item['tgl_pengaduan'])); ?></td>
                <td class="text-truncate" style="max-width:300px;"><?= htmlspecialchars($item['isi_laporan']); ?></td>
                <td>
                  <span class="badge 
                    <?= $item['status']=='0'?'badge-pending':($item['status']=='proses'?'badge-proses':'badge-selesai'); ?>">
                    <?= $item['status']=='0'?'Pending':($item['status']=='proses'?'Diproses':'Selesai'); ?>
                  </span>
                </td>
                <td>
                  <a href="detail.php?id=<?= $item['id_pengaduan']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Detail</a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <?php if (count($pengaduan)>5): ?>
          <div class="text-center mt-3"><a href="riwayat.php" class="btn btn-outline-primary">Lihat Semua <i class="bi bi-arrow-right"></i></a></div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</main>

<!-- Modal Logout -->
<div class="modal fade" id="logoutModal"><div class="modal-dialog"><div class="modal-content">
  <div class="modal-header"><h5 class="modal-title">Konfirmasi Logout</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body text-center"><i class="bi bi-question-circle-fill text-warning" style="font-size:2.5rem;"></i><p>Yakin ingin keluar?</p></div>
  <div class="modal-footer"><button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button><a href="../config/logout.php" class="btn btn-danger"><i class="bi bi-box-arrow-right"></i> Ya, Logout</a></div>
</div></div></div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
