<?php
// masyarakat/riwayat.php
session_start();
require_once '../config/koneksi.php';

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'masyarakat') {
    header('Location: ../index.php?page=login');
    exit();
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Filter status
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

try {
    // Count total data
    $count_sql = "SELECT COUNT(*) as total FROM pengaduan WHERE nik = ?";
    $count_params = [$_SESSION['user_id']];
    
    $sql = "SELECT * FROM pengaduan WHERE nik = ?";
    $params = [$_SESSION['user_id']];
    
    if ($status_filter != 'all') {
        $count_sql .= " AND status = ?";
        $sql .= " AND status = ?";
        $count_params[] = $status_filter;
        $params[] = $status_filter;
    }
    
    // Get total count using fetch method
    $count_result = $db->fetch($count_sql, $count_params);
    $total_data = $count_result['total'];
    $total_pages = ceil($total_data / $limit);
    
    $sql .= " ORDER BY tgl_pengaduan DESC LIMIT $limit OFFSET $offset";
    
    // Get pengaduan data using fetchAll method
    $pengaduan = $db->fetchAll($sql, $params);
    
    // Get statistics
    $stats_sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = '0' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'proses' THEN 1 ELSE 0 END) as proses,
                    SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai
                  FROM pengaduan WHERE nik = ?";
    $stats = $db->fetch($stats_sql, [$_SESSION['user_id']]);
    
} catch (Exception $e) {
    $pengaduan = [];
    $total_data = 0;
    $total_pages = 0;
    $stats = ['total' => 0, 'pending' => 0, 'proses' => 0, 'selesai' => 0];
}

function formatIsiLaporan($isi_laporan, $limit = 100) {
    $parts = explode(' | ', $isi_laporan, 2);
    if (count($parts) == 2) {
        $judul = $parts[0];
        $isi = strlen($parts[1]) > $limit ? substr($parts[1], 0, $limit) . '...' : $parts[1];
        return ['judul' => $judul, 'isi' => $isi];
    } else {
        $isi = strlen($isi_laporan) > $limit ? substr($isi_laporan, 0, $limit) . '...' : $isi_laporan;
        return ['judul' => 'Pengaduan', 'isi' => $isi];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Riwayat Pengaduan - Sistem Pengaduan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <style>
    :root {
        --primary:#2d4a3e;
        --secondary:#87a96b;
        --success:#6b8e5a;
        --warning:#c9a876;
        --info:#7ba098;
        --danger:#d4756b;
    }
    body { background:#f8faf9; font-family:'Inter',sans-serif; }
    .sidebar {
        min-height:100vh;
        background:linear-gradient(135deg,var(--primary),var(--secondary));
        box-shadow:2px 0 10px rgba(0,0,0,.1);
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
    .pengaduan-card { border-left:4px solid transparent; transition:.3s; }
    .pengaduan-card.pending { border-left-color:var(--warning); }
    .pengaduan-card.proses { border-left-color:var(--info); }
    .pengaduan-card.selesai { border-left-color:var(--success); }
    .pengaduan-card:hover { box-shadow:0 8px 25px rgba(0,0,0,.15); }
    .badge-pending { background:var(--warning); }
    .badge-proses { background:var(--info); }
    .badge-selesai { background:var(--success); }
    .btn-filter { border-radius:20px; padding:8px 20px; margin:2px; }
    .btn-filter.active { background:linear-gradient(135deg,var(--primary),var(--secondary)); color:#fff; }
    .pagination .page-link { border-radius:8px; margin:0 2px; color:var(--primary); border-color:var(--secondary); }
    .pagination .page-link:hover { background:var(--secondary); color:#fff; }
    .pagination .page-item.active .page-link { background:var(--primary); border-color:var(--primary); }
    /* overlay mobile */
    @media(max-width:768px){
        .sidebar {
            position:fixed; top:0; left:-250px; width:220px; z-index:1050; transition:left .3s;
        }
        .sidebar.show { left:0; }
        .overlay {
            position:fixed; top:0; left:0; width:100%; height:100%;
            background:rgba(0,0,0,.5); z-index:1040; display:none;
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
          <li class="nav-item"><a class="nav-link active" href="riwayat.php"><i class="bi bi-clock-history"></i> Riwayat Pengaduan</a></li>
          <li class="nav-item"><a class="nav-link" href="profile.php"><i class="bi bi-person"></i> Profil</a></li>
          <li class="nav-item mt-3"><a class="nav-link text-warning" href="../config/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
      </div>
    </nav>

    <!-- Main -->
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
      <!-- Topbar -->
      <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <div class="d-flex align-items-center">
          <button class="btn btn-outline-secondary d-md-none me-2" id="menuToggle"><i class="bi bi-list"></i></button>
          <h1 class="h4 mb-0">Riwayat Pengaduan</h1>
        </div>
        <a href="pengaduan.php" class="btn btn-primary"><i class="bi bi-plus-circle"></i> Buat Baru</a>
      </div>

      <!-- Statistik -->
      <div class="row mb-4">
        <div class="col-md-3"><div class="card text-white" style="background:var(--secondary)"><div class="card-body"><div>Total<br><strong><?= $stats['total']; ?></strong></div></div></div></div>
        <div class="col-md-3"><div class="card text-white" style="background:var(--warning)"><div class="card-body"><div>Pending<br><strong><?= $stats['pending']; ?></strong></div></div></div></div>
        <div class="col-md-3"><div class="card text-white" style="background:var(--info)"><div class="card-body"><div>Diproses<br><strong><?= $stats['proses']; ?></strong></div></div></div></div>
        <div class="col-md-3"><div class="card text-white" style="background:var(--success)"><div class="card-body"><div>Selesai<br><strong><?= $stats['selesai']; ?></strong></div></div></div></div>
      </div>

      <!-- Filter -->
      <div class="card mb-4"><div class="card-body">
        <h6 class="mb-2">Filter:</h6>
        <a href="?status=all" class="btn btn-outline-secondary btn-filter <?= ($status_filter=='all'?'active':'') ?>">Semua</a>
        <a href="?status=0" class="btn btn-outline-warning btn-filter <?= ($status_filter=='0'?'active':'') ?>">Pending</a>
        <a href="?status=proses" class="btn btn-outline-info btn-filter <?= ($status_filter=='proses'?'active':'') ?>">Diproses</a>
        <a href="?status=selesai" class="btn btn-outline-success btn-filter <?= ($status_filter=='selesai'?'active':'') ?>">Selesai</a>
      </div></div>

      <!-- Pengaduan List -->
      <?php if (empty($pengaduan)): ?>
        <div class="card"><div class="card-body text-center py-5">
          <i class="bi bi-file-earmark-x h1 text-muted mb-3"></i>
          <h5 class="text-muted">Tidak ada pengaduan ditemukan</h5>
          <a href="pengaduan.php" class="btn btn-primary">Buat Pengaduan Pertama</a>
        </div></div>
      <?php else: ?>
        <div class="row">
        <?php foreach($pengaduan as $item): 
          $laporan=formatIsiLaporan($item['isi_laporan']);
          switch($item['status']){
            case '0':$status_class='badge-pending';$status_text='Menunggu';$status_icon='bi-clock';break;
            case 'proses':$status_class='badge-proses';$status_text='Diproses';$status_icon='bi-arrow-repeat';break;
            case 'selesai':$status_class='badge-selesai';$status_text='Selesai';$status_icon='bi-check-circle';break;
          } ?>
          <div class="col-md-6 col-lg-4 mb-4">
            <div class="card pengaduan-card <?= str_replace('badge-','',$status_class); ?>">
              <div class="card-header d-flex justify-content-between bg-light">
                <small><i class="bi bi-calendar3"></i> <?= date('d M Y',strtotime($item['tgl_pengaduan'])); ?></small>
                <span class="badge <?= $status_class; ?>"><i class="<?= $status_icon; ?>"></i> <?= $status_text; ?></span>
              </div>
              <div class="card-body">
                <h6 class="card-title text-primary"><?= htmlspecialchars($laporan['judul']); ?></h6>
                <p class="text-muted small"><?= htmlspecialchars($laporan['isi']); ?></p>
                <div class="d-flex justify-content-between">
                  <small>ID: <?= $item['id_pengaduan']; ?></small>
                  <a href="detail.php?id=<?= $item['id_pengaduan']; ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i> Detail</a>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
        </div>
      <?php endif; ?>

      <!-- Pagination -->
      <?php if ($total_pages>1): ?>
        <nav><ul class="pagination justify-content-center">
          <?php if ($page>1): ?><li class="page-item"><a class="page-link" href="?page=<?= $page-1; ?>&status=<?= $status_filter; ?>"><i class="bi bi-chevron-left"></i></a></li><?php endif; ?>
          <?php for($i=1;$i<=$total_pages;$i++): ?>
            <li class="page-item <?= ($i==$page?'active':''); ?>"><a class="page-link" href="?page=<?= $i; ?>&status=<?= $status_filter; ?>"><?= $i; ?></a></li>
          <?php endfor; ?>
          <?php if ($page<$total_pages): ?><li class="page-item"><a class="page-link" href="?page=<?= $page+1; ?>&status=<?= $status_filter; ?>"><i class="bi bi-chevron-right"></i></a></li><?php endif; ?>
        </ul></nav>
      <?php endif; ?>
    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const menuToggle=document.getElementById('menuToggle');
const sidebar=document.getElementById('sidebarMenu');
const overlay=document.getElementById('overlay');
if(menuToggle){
  menuToggle.addEventListener('click',()=>{sidebar.classList.toggle('show');overlay.classList.toggle('show');});
}
if(overlay){
  overlay.addEventListener('click',()=>{sidebar.classList.remove('show');overlay.classList.remove('show');});
}
</script>
</body>
</html>