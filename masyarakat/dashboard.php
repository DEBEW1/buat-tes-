<?php
// masyarakat/dashboard.php
session_start();
require_once '../config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'masyarakat') {
    header('Location: ../index.php?page=login');
    exit();
}

// Ambil data pengaduan awal dengan limit 5
try {
    $sql = "SELECT * FROM pengaduan WHERE nik = ? ORDER BY tgl_pengaduan DESC LIMIT 5";
    $pengaduan_terbaru = $db->fetchAll($sql, [$_SESSION['user_id']]);

    // Ambil semua data untuk statistik
    $sql_all = "SELECT * FROM pengaduan WHERE nik = ?";
    $semua_pengaduan = $db->fetchAll($sql_all, [$_SESSION['user_id']]);

    // Statistik
    $total_pengaduan   = count($semua_pengaduan);
    $pengaduan_pending = count(array_filter($semua_pengaduan, fn($p) => $p['status'] == '0'));
    $pengaduan_proses  = count(array_filter($semua_pengaduan, fn($p) => $p['status'] == 'proses'));
    $pengaduan_selesai = count(array_filter($semua_pengaduan, fn($p) => $p['status'] == 'selesai'));
} catch (Exception $e) {
    $pengaduan_terbaru = [];
    $semua_pengaduan = [];
    $total_pengaduan = $pengaduan_pending = $pengaduan_proses = $pengaduan_selesai = 0;
}
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
.table td{text-overflow:ellipsis;overflow:hidden;}

/* Responsive improvements */
@media (max-width: 992px) { .navbar-collapse { background: linear-gradient(135deg, var(--primary), var(--secondary)); padding: 15px; border-radius: 0 0 10px 10px; margin-top: 10px; } .navbar-nav { gap: 10px; } .stat-card .h2 { font-size: 1.5rem; } .stat-card .h1 { font-size: 2rem; } }
@media (max-width: 768px) { .container { padding-left: 15px; padding-right: 15px; } .table td, .table th { font-size: 0.85rem; padding: 8px; } .btn, .btn-sm { font-size: 0.85rem; padding: 6px 12px; } .card-body { padding: 15px; } .d-flex.justify-content-between.align-items-center.mb-3 { flex-direction: column; align-items: flex-start !important; gap: 15px; } .d-flex.justify-content-between.align-items-center.mb-3 .btn { width: 100%; } .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; } .table td, .table th { white-space: nowrap; min-width: 100px; } .table td:nth-child(2) { max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; } }
@media (max-width: 576px) { .navbar-brand { font-size: 1.1rem; } #statistik .col-6 { flex: 0 0 50%; max-width: 50%; } .stat-card .card-body { padding: 15px 10px; } .stat-card .h2 { font-size: 1.3rem; } .stat-card .h1 { font-size: 1.7rem; } .modal-dialog { margin: 10px; } .table td, .table th { padding: 6px; font-size: 0.8rem; } .table td:nth-child(2) { max-width: 150px; } .btn-sm { padding: 4px 8px; font-size: 0.8rem; } }

/* Mobile first improvements */
.navbar-toggler { border: none; padding: 5px 10px; }
.navbar-toggler:focus { box-shadow: none; }
.navbar-toggler-icon { width: 1.2em; height: 1.2em; }
.btn, .nav-link { padding: 10px 15px; }
.table-hover tbody tr { cursor: pointer; transition: background-color 0.2s; }
.table-hover tbody tr:hover { background-color: rgba(0,0,0,0.05); }
.card-title, .card-text { word-wrap: break-word; }
.py-5 { padding-top: 2rem !important; padding-bottom: 2rem !important; }
.modal-content { border-radius: 15px; }
.badge { font-size: 0.75em; padding: 0.4em 0.6em; }
.gap-2 { gap: 10px !important; }
.text-muted { color: #6c757d !important; }
.form-control, .form-select { font-size: 16px; }
.navbar-collapse.collapsing { transition: height 0.3s ease; }
.loading-spinner { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(255,255,255,0.8); z-index: 9999; justify-content: center; align-items: center; }
.toast-container { position: fixed; bottom: 20px; right: 20px; z-index: 9999; }
</style>
</head>
<body>

<!-- Loading Spinner -->
<div class="loading-spinner" id="loadingSpinner">
    <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;"></div>
</div>

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
    <h1 class="h5 mb-0">
      <span id="greeting"></span>, 
      <span class="text-primary"><?= htmlspecialchars($_SESSION['nama']); ?></span>
    </h1>
    <a href="pengaduan.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Buat Pengaduan Baru</a>
  </div>

  <!-- Statistik -->
  <div class="row g-3 mb-4" id="statistik">
    <div class="col-6 col-lg-3"><div class="card stat-card bg-success"><div class="card-body">
      <div class="d-flex justify-content-between align-items-center"><div><div>Total</div><div class="h2" id="total_pengaduan"><?= $total_pengaduan; ?></div></div><i class="bi bi-files h1"></i></div>
    </div></div></div>
    <div class="col-6 col-lg-3"><div class="card stat-card bg-warning text-dark"><div class="card-body">
      <div class="d-flex justify-content-between align-items-center"><div><div>Pending</div><div class="h2" id="pengaduan_pending"><?= $pengaduan_pending; ?></div></div><i class="bi bi-clock h1"></i></div>
    </div></div></div>
    <div class="col-6 col-lg-3"><div class="card stat-card bg-info"><div class="card-body">
      <div class="d-flex justify-content-between align-items-center"><div><div>Diproses</div><div class="h2" id="pengaduan_proses"><?= $pengaduan_proses; ?></div></div><i class="bi bi-arrow-repeat h1"></i></div>
    </div></div></div>
    <div class="col-6 col-lg-3"><div class="card stat-card bg-danger"><div class="card-body">
      <div class="d-flex justify-content-between align-items-center"><div><div>Selesai</div><div class="h2" id="pengaduan_selesai"><?= $pengaduan_selesai; ?></div></div><i class="bi bi-check-circle h1"></i></div>
    </div></div></div>
  </div>

  <!-- Pengaduan Terbaru -->
  <div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0">Pengaduan Terbaru</h5>
      <span class="badge bg-primary"><?= count($pengaduan_terbaru); ?> dari <?= $total_pengaduan; ?></span>
    </div>
    <div class="card-body">
      <div id="pengaduan_container">
        <?php if (empty($pengaduan_terbaru)): ?>
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
              <tbody id="pengaduan_list">
                <?php foreach ($pengaduan_terbaru as $item): ?>
                <tr data-id="<?= $item['id_pengaduan']; ?>">
                  <td class="time" data-time="<?= strtotime($item['tgl_pengaduan']); ?>"></td>
                  <td class="text-truncate" style="max-width:300px;" title="<?= htmlspecialchars($item['isi_laporan']); ?>">
                    <?= htmlspecialchars(mb_substr($item['isi_laporan'], 0, 100) . (strlen($item['isi_laporan']) > 100 ? '...' : '')); ?>
                  </td>
                  <td>
                    <span class="badge <?= 
                      $item['status'] == '0' ? 'badge-pending' : 
                      ($item['status'] == 'proses' ? 'badge-proses' : 'badge-selesai'); ?>">
                      <?= 
                      $item['status'] == '0' ? 'Pending' : 
                      ($item['status'] == 'proses' ? 'Diproses' : 'Selesai'); ?>
                    </span>
                  </td>
                  <td>
                    <a href="detail.php?id=<?= $item['id_pengaduan']; ?>" class="btn btn-sm btn-outline-primary">
                      <i class="bi bi-eye"></i> <span class="d-none d-md-inline">Detail</span>
                    </a>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <?php if ($total_pengaduan > 5): ?>
            <div class="text-center mt-3">
              <a href="riwayat.php" class="btn btn-outline-primary">
                Lihat Semua <i class="bi bi-arrow-right"></i>
              </a>
            </div>
          <?php endif; ?>
        <?php endif; ?>
      </div>
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

<script>
// --- Tanggal realtime (tanggal saja) ---
function updateTime() {
    document.querySelectorAll('.time').forEach(td => {
        const tgl = new Date(parseInt(td.dataset.time) * 1000);
        td.textContent = tgl.toLocaleDateString('id-ID', { day:'numeric', month:'long', year:'numeric' });
    });
}
updateTime();
setInterval(updateTime, 3600000); // update tiap jam

// --- Greeting realtime ---
function updateGreeting() {
    const hour = new Date().getHours();
    let greeting = '';
    if(hour < 12) greeting = 'Selamat Pagi';
    else if(hour < 15) greeting = 'Selamat Siang';
    else if(hour < 18) greeting = 'Selamat Sore';
    else greeting = 'Selamat Malam';
    document.getElementById('greeting').textContent = greeting;
}
updateGreeting();
setInterval(updateGreeting, 1000); // update tiap detik
</script>

</body>
</html>
