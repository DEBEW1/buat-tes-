<?php
// masyarakat/dashboard.php
session_start();
require_once '../config/koneksi.php';

// Cek apakah user sudah login dan levelnya masyarakat
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'masyarakat') {
    header('Location: ../index.php?page=login');
    exit();
}

// Ambil data pengaduan milik user
try {
    $sql = "SELECT * FROM pengaduan WHERE nik = ? ORDER BY tgl_pengaduan DESC";
    $stmt = $db->query($sql, [$_SESSION['user_id']]);
    $pengaduan = $stmt->fetchAll();
    
    // Hitung statistik
    $total_pengaduan = count($pengaduan);
    $pengaduan_pending = count(array_filter($pengaduan, function($p) { return $p['status'] == '0'; }));
    $pengaduan_proses = count(array_filter($pengaduan, function($p) { return $p['status'] == 'proses'; }));
    $pengaduan_selesai = count(array_filter($pengaduan, function($p) { return $p['status'] == 'selesai'; }));
    
} catch (Exception $e) {
    $pengaduan = [];
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
            transform: translateY(-5px);
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
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #234035 0%, #7a9960 100%);
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(135, 169, 107, 0.1);
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
                            <a class="nav-link active" href="dashboard.php">
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
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="pengaduan.php" class="btn btn-primary">
                            <i class="bi bi-plus-circle"></i> Buat Pengaduan Baru
                        </a>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6">
                        <div class="card stat-card mb-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="text-white-50 small">Total Pengaduan</div>
                                        <div class="text-white h2"><?php echo $total_pengaduan; ?></div>
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
                                        <div class="text-white h2"><?php echo $pengaduan_pending; ?></div>
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
                                        <div class="text-white h2"><?php echo $pengaduan_proses; ?></div>
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
                                        <div class="text-white h2"><?php echo $pengaduan_selesai; ?></div>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-check-circle h1 text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Recent Reports -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Pengaduan Terbaru</h5>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pengaduan)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-file-earmark-x h1 text-muted"></i>
                                <p class="text-muted">Belum ada pengaduan yang dibuat</p>
                                <a href="pengaduan.php" class="btn btn-primary">Buat Pengaduan Pertama</a>
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Isi Laporan</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach (array_slice($pengaduan, 0, 5) as $item): ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($item['tgl_pengaduan'])); ?></td>
                                                <td>
                                                    <?php 
                                                    $isi_laporan = strlen($item['isi_laporan']) > 50 
                                                        ? substr($item['isi_laporan'], 0, 50) . '...' 
                                                        : $item['isi_laporan'];
                                                    echo htmlspecialchars($isi_laporan);
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $status_class = '';
                                                    $status_text = '';
                                                    switch($item['status']) {
                                                        case '0':
                                                            $status_class = 'badge-pending';
                                                            $status_text = 'Pending';
                                                            break;
                                                        case 'proses':
                                                            $status_class = 'badge-proses';
                                                            $status_text = 'Diproses';
                                                            break;
                                                        case 'selesai':
                                                            $status_class = 'badge-selesai';
                                                            $status_text = 'Selesai';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                                </td>
                                                <td>
                                                    <a href="detail.php?id=<?php echo $item['id_pengaduan']; ?>" 
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> Detail
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <?php if (count($pengaduan) > 5): ?>
                                <div class="text-center mt-3">
                                    <a href="riwayat.php" class="btn btn-outline-primary">
                                        Lihat Semua Pengaduan <i class="bi bi-arrow-right"></i>
                                    </a>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>