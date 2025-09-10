<?php
// masyarakat/riwayat.php
session_start();
require_once '../config/koneksi.php';

// Cek apakah user sudah login dan levelnya masyarakat
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
    // Query untuk menghitung total data
    $count_sql = "SELECT COUNT(*) as total FROM pengaduan WHERE nik = ?";
    $count_params = [$_SESSION['user_id']];
    
    // Query untuk mengambil data
    $sql = "SELECT * FROM pengaduan WHERE nik = ?";
    $params = [$_SESSION['user_id']];
    
    // Tambahkan filter status jika dipilih
    if ($status_filter != 'all') {
        $count_sql .= " AND status = ?";
        $sql .= " AND status = ?";
        $count_params[] = $status_filter;
        $params[] = $status_filter;
    }
    
    // Hitung total data
    $count_stmt = $db->query($count_sql, $count_params);
    $total_data = $count_stmt->fetch()['total'];
    $total_pages = ceil($total_data / $limit);
    
    // Ambil data dengan pagination
    $sql .= " ORDER BY tgl_pengaduan DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    
    $stmt = $db->query($sql, $params);
    $pengaduan = $stmt->fetchAll();
    
    // Hitung statistik
    $stats_sql = "SELECT 
                    COUNT(*) as total,
                    SUM(CASE WHEN status = '0' THEN 1 ELSE 0 END) as pending,
                    SUM(CASE WHEN status = 'proses' THEN 1 ELSE 0 END) as proses,
                    SUM(CASE WHEN status = 'selesai' THEN 1 ELSE 0 END) as selesai
                  FROM pengaduan WHERE nik = ?";
    $stats_stmt = $db->query($stats_sql, [$_SESSION['user_id']]);
    $stats = $stats_stmt->fetch();
    
} catch (Exception $e) {
    $pengaduan = [];
    $total_data = 0;
    $total_pages = 0;
    $stats = ['total' => 0, 'pending' => 0, 'proses' => 0, 'selesai' => 0];
}

// Function untuk memformat isi laporan
function formatIsiLaporan($isi_laporan, $limit = 100) {
    // Pisahkan judul dan isi jika ada separator |
    $parts = explode(' | ', $isi_laporan, 2);
    if (count($parts) == 2) {
        $judul = $parts[0];
        $isi = $parts[1];
        
        if (strlen($isi) > $limit) {
            $isi = substr($isi, 0, $limit) . '...';
        }
        
        return ['judul' => $judul, 'isi' => $isi];
    } else {
        // Jika tidak ada separator, anggap semuanya sebagai isi
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
        
        .pengaduan-card {
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }
        
        .pengaduan-card.pending {
            border-left-color: var(--warning);
        }
        
        .pengaduan-card.proses {
            border-left-color: var(--info);
        }
        
        .pengaduan-card.selesai {
            border-left-color: var(--success);
        }
        
        .pengaduan-card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .badge-pending {
            background-color: var(--warning);
        }
        
        .badge-proses {
            background-color: var(--info);
        }
        
        .badge-selesai {
            background-color: var(--success);
        }
        
        .btn-filter {
            border-radius: 20px;
            padding: 8px 20px;
            margin: 2px;
            transition: all 0.3s ease;
        }
        
        .btn-filter.active {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
        }
        
        .pagination .page-link {
            border-radius: 8px;
            margin: 0 2px;
            color: var(--primary);
            border-color: var(--secondary);
        }
        
        .pagination .page-link:hover {
            background-color: var(--secondary);
            color: white;
        }
        
        .pagination .page-item.active .page-link {
            background-color: var(--primary);
            border-color: var(--primary);
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
                            <a class="nav-link active" href="riwayat.php">
                                <i class="bi bi-clock-history"></i> Riwayat Pengaduan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
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
                    <h1 class="h2">Riwayat Pengaduan</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="pengaduan.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Buat Pengaduan Baru
                        </a>
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
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="text-white-50 small">Total Pengaduan</div>
                                        <div class="text-white h3"><?php echo $stats['total']; ?></div>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-file-earmark-text h1 text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card-warning mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="text-white-50 small">Pending</div>
                                        <div class="text-white h3"><?php echo $stats['pending']; ?></div>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-clock h1 text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card-info mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="text-white-50 small">Diproses</div>
                                        <div class="text-white h3"><?php echo $stats['proses']; ?></div>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-arrow-repeat h1 text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card-danger mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="text-white-50 small">Selesai</div>
                                        <div class="text-white h3"><?php echo $stats['selesai']; ?></div>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-check-circle h1 text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Filter -->
                <div class="card mb-4">
                    <div class="card-body">
                        <div class="d-flex flex-wrap align-items-center">
                            <h6 class="mb-0 me-3">Filter Status:</h6>
                            <div>
                                <a href="?status=all" class="btn btn-outline-secondary btn-filter <?php echo ($status_filter == 'all') ? 'active' : ''; ?>">
                                    Semua (<?php echo $stats['total']; ?>)
                                </a>
                                <a href="?status=0" class="btn btn-outline-warning btn-filter <?php echo ($status_filter == '0') ? 'active' : ''; ?>">
                                    Pending (<?php echo $stats['pending']; ?>)
                                </a>
                                <a href="?status=proses" class="btn btn-outline-info btn-filter <?php echo ($status_filter == 'proses') ? 'active' : ''; ?>">
                                    Diproses (<?php echo $stats['proses']; ?>)
                                </a>
                                <a href="?status=selesai" class="btn btn-outline-success btn-filter <?php echo ($status_filter == 'selesai') ? 'active' : ''; ?>">
                                    Selesai (<?php echo $stats['selesai']; ?>)
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Pengaduan List -->
                <?php if (empty($pengaduan)): ?>
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="bi bi-file-earmark-x h1 text-muted mb-3"></i>
                            <h5 class="text-muted">Tidak ada pengaduan ditemukan</h5>
                            <p class="text-muted">
                                <?php if ($status_filter != 'all'): ?>
                                    Belum ada pengaduan dengan status yang dipilih.
                                <?php else: ?>
                                    Anda belum membuat pengaduan apapun.
                                <?php endif; ?>
                            </p>
                            <a href="pengaduan.php" class="btn btn-primary">Buat Pengaduan Pertama</a>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="row">
                        <?php foreach ($pengaduan as $item): ?>
                            <?php 
                            $laporan = formatIsiLaporan($item['isi_laporan']);
                            $status_class = '';
                            $status_text = '';
                            $status_icon = '';
                            
                            switch($item['status']) {
                                case '0':
                                    $status_class = 'badge-pending';
                                    $status_text = 'Menunggu';
                                    $status_icon = 'bi-clock';
                                    break;
                                case 'proses':
                                    $status_class = 'badge-proses';
                                    $status_text = 'Diproses';
                                    $status_icon = 'bi-arrow-repeat';
                                    break;
                                case 'selesai':
                                    $status_class = 'badge-selesai';
                                    $status_text = 'Selesai';
                                    $status_icon = 'bi-check-circle';
                                    break;
                            }
                            ?>
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card pengaduan-card <?php echo str_replace('badge-', '', $status_class); ?> h-100">
                                    <div class="card-header d-flex justify-content-between align-items-center bg-light">
                                        <small class="text-muted">
                                            <i class="bi bi-calendar3"></i> 
                                            <?php echo date('d M Y', strtotime($item['tgl_pengaduan'])); ?>
                                        </small>
                                        <span class="badge <?php echo $status_class; ?>">
                                            <i class="<?php echo $status_icon; ?>"></i> 
                                            <?php echo $status_text; ?>
                                        </span>
                                    </div>
                                    <div class="card-body">
                                        <h6 class="card-title text-primary"><?php echo htmlspecialchars($laporan['judul']); ?></h6>
                                        <p class="card-text text-muted small">
                                            <?php echo htmlspecialchars($laporan['isi']); ?>
                                        </p>
                                        
                                        <?php if ($item['foto']): ?>
                                            <div class="mb-3">
                                                <small class="text-success">
                                                    <i class="bi bi-image"></i> Dengan foto pendukung
                                                </small>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                ID: <?php echo $item['id_pengaduan']; ?>
                                            </small>
                                            <a href="detail.php?id=<?php echo $item['id_pengaduan']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> Detail
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <nav aria-label="Pagination" class="mt-4">
                            <ul class="pagination justify-content-center">
                                <?php if ($page > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo ($page-1); ?>&status=<?php echo $status_filter; ?>">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                
                                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                    <li class="page-item <?php echo ($i == $page) ? 'active' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                <?php endfor; ?>
                                
                                <?php if ($page < $total_pages): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?page=<?php echo ($page+1); ?>&status=<?php echo $status_filter; ?>">
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </nav>
                        
                        <div class="text-center text-muted">
                            <small>
                                Menampilkan <?php echo (($page-1) * $limit) + 1; ?> - <?php echo min($page * $limit, $total_data); ?> 
                                dari <?php echo $total_data; ?> pengaduan
                            </small>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>