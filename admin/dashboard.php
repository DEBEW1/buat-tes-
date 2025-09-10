<?php
session_start();
require_once '../config/koneksi.php';

// Cek apakah user sudah login dan levelnya admin
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'admin') {
    header('Location: ../index.php?page=login');
    exit();
}

try {
    // Ambil statistik umum
    $stats_sql = "SELECT 
                    (SELECT COUNT(*) FROM pengaduan) as total_pengaduan,
                    (SELECT COUNT(*) FROM pengaduan WHERE status = '0') as pending,
                    (SELECT COUNT(*) FROM pengaduan WHERE status = 'proses') as proses,
                    (SELECT COUNT(*) FROM pengaduan WHERE status = 'selesai') as selesai,
                    (SELECT COUNT(*) FROM masyarakat) as total_masyarakat,
                    (SELECT COUNT(*) FROM petugas) as total_petugas";
    $stats_stmt = $db->query($stats_sql);
    $stats = $stats_stmt->fetch();
    
    // Ambil pengaduan terbaru
    $pengaduan_sql = "SELECT p.*, m.nama FROM pengaduan p 
                      JOIN masyarakat m ON p.nik = m.nik 
                      ORDER BY p.created_at DESC LIMIT 5";
    $pengaduan_stmt = $db->query($pengaduan_sql);
    $pengaduan_terbaru = $pengaduan_stmt->fetchAll();
    
    // Ambil data untuk chart (pengaduan per bulan)
    $chart_sql = "SELECT 
                    DATE_FORMAT(tgl_pengaduan, '%Y-%m') as bulan,
                    COUNT(*) as jumlah
                  FROM pengaduan 
                  WHERE tgl_pengaduan >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                  GROUP BY DATE_FORMAT(tgl_pengaduan, '%Y-%m')
                  ORDER BY bulan ASC";
    $chart_stmt = $db->query($chart_sql);
    $chart_data = $chart_stmt->fetchAll();
    
} catch (Exception $e) {
    $stats = ['total_pengaduan' => 0, 'pending' => 0, 'proses' => 0, 'selesai' => 0, 'total_masyarakat' => 0, 'total_petugas' => 0];
    $pengaduan_terbaru = [];
    $chart_data = [];
}

function formatIsiLaporan($isi_laporan, $limit = 80) {
    $parts = explode(' | ', $isi_laporan, 2);
    if (count($parts) == 2) {
        $judul = $parts[0];
        $isi = $parts[1];
        
        if (strlen($isi) > $limit) {
            $isi = substr($isi, 0, $limit) . '...';
        }
        
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
    <title>Dashboard Admin - Sistem Pengaduan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #1e3a8a;
            --primary-dark: #1e40af;
            --secondary: #3b82f6;
            --success: #10b981;
            --warning: #f59e0b;
            --info: #06b6d4;
            --danger: #ef4444;
        }
        
        body {
            background-color: #f8fafc;
            font-family: 'Inter', sans-serif;
        }
        
        .sidebar {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.8);
            border-radius: 8px;
            margin: 5px 0;
            transition: all 0.3s ease;
            padding: 12px 16px;
        }
        
        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background-color: rgba(255,255,255,0.2);
            color: white;
            transform: translateX(5px);
        }
        
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.07);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }
        
        .stat-card {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
        }
        
        .stat-card-success {
            background: linear-gradient(135deg, var(--success) 0%, #34d399 100%);
        }
        
        .stat-card-warning {
            background: linear-gradient(135deg, var(--warning) 0%, #fbbf24 100%);
        }
        
        .stat-card-info {
            background: linear-gradient(135deg, var(--info) 0%, #22d3ee 100%);
        }
        
        .stat-card-danger {
            background: linear-gradient(135deg, var(--danger) 0%, #f87171 100%);
        }
        
        .stat-card-users {
            background: linear-gradient(135deg, #8b5cf6 0%, #a78bfa 100%);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
            border-radius: 10px;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(59, 130, 246, 0.1);
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
        
        .chart-container {
            position: relative;
            height: 300px;
        }
        
        .welcome-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .activity-card {
            max-height: 400px;
            overflow-y: auto;
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
                        <div class="rounded-circle bg-light p-3 d-inline-block mb-2">
                            <i class="bi bi-shield-check text-primary fs-1"></i>
                        </div>
                        <h5 class="text-white">Admin Panel</h5>
                        <p class="text-white-50 mb-0"><?php echo $_SESSION['nama']; ?></p>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="bi bi-speedometer2 me-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="pengaduan.php">
                                <i class="bi bi-file-earmark-text me-2"></i> Kelola Pengaduan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="masyarakat.php">
                                <i class="bi bi-people me-2"></i> Data Masyarakat
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="petugas.php">
                                <i class="bi bi-person-badge me-2"></i> Data Petugas
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="laporan.php">
                                <i class="bi bi-graph-up me-2"></i> Laporan
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="bi bi-gear me-2"></i> Pengaturan
                            </a>
                        </li>
                        <li class="nav-item mt-3">
                            <a class="nav-link text-warning" href="../config/logout.php">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
                    <h1 class="h2">Dashboard Admin</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="bi bi-calendar3"></i> Periode
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="#">Hari ini</a></li>
                                <li><a class="dropdown-item" href="#">7 hari terakhir</a></li>
                                <li><a class="dropdown-item" href="#">30 hari terakhir</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#">Custom</a></li>
                            </ul>
                        </div>
                        <button type="button" class="btn btn-sm btn-primary">
                            <i class="bi bi-download"></i> Export
                        </button>
                    </div>
                </div>
                
                <!-- Welcome Card -->
                <div class="card welcome-card mb-4">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col">
                                <h4 class="mb-1">Selamat datang kembali, <?php echo $_SESSION['nama']; ?>!</h4>
                                <p class="mb-0 opacity-75">
                                    Berikut adalah ringkasan sistem pengaduan masyarakat hari ini
                                </p>
                            </div>
                            <div class="col-auto">
                                <i class="bi bi-graph-up-arrow display-4 opacity-75"></i>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Statistics Cards -->
                <div class="row mb-4">
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                        <div class="card stat-card text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="text-white-50 small">Total Pengaduan</div>
                                        <div class="h3 mb-0"><?php echo $stats['total_pengaduan']; ?></div>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-file-earmark-text display-6 text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                        <div class="card stat-card-warning text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="text-white-50 small">Pending</div>
                                        <div class="h3 mb-0"><?php echo $stats['pending']; ?></div>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-clock display-6 text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                        <div class="card stat-card-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="text-white-50 small">Diproses</div>
                                        <div class="h3 mb-0"><?php echo $stats['proses']; ?></div>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-arrow-repeat display-6 text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                        <div class="card stat-card-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="text-white-50 small">Selesai</div>
                                        <div class="h3 mb-0"><?php echo $stats['selesai']; ?></div>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-check-circle display-6 text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                        <div class="card stat-card-users text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="text-white-50 small">Masyarakat</div>
                                        <div class="h3 mb-0"><?php echo $stats['total_masyarakat']; ?></div>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-people display-6 text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-2 col-md-4 col-sm-6 mb-3">
                        <div class="card stat-card-danger text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <div class="text-white-50 small">Petugas</div>
                                        <div class="h3 mb-0"><?php echo $stats['total_petugas']; ?></div>
                                    </div>
                                    <div class="align-self-center">
                                        <i class="bi bi-person-badge display-6 text-white-50"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Chart -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-bar-chart-line text-primary"></i> 
                                    Tren Pengaduan 6 Bulan Terakhir
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="trendChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Activity -->
                    <div class="col-lg-4 mb-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="card-title mb-0">
                                    <i class="bi bi-clock-history text-primary"></i> 
                                    Pengaduan Terbaru
                                </h5>
                            </div>
                            <div class="card-body activity-card">
                                <?php if (!empty($pengaduan_terbaru)): ?>
                                    <?php foreach ($pengaduan_terbaru as $item): ?>
                                        <?php 
                                        $laporan = formatIsiLaporan($item['isi_laporan'], 60);
                                        $status_class = '';
                                        switch($item['status']) {
                                            case '0': $status_class = 'badge-pending'; break;
                                            case 'proses': $status_class = 'badge-proses'; break;
                                            case 'selesai': $status_class = 'badge-selesai'; break;
                                        }
                                        ?>
                                        <div class="d-flex align-items-start mb-3 p-2 rounded bg-light">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 text-truncate"><?php echo htmlspecialchars($laporan['judul']); ?></h6>
                                                <p class="mb-1 small text-muted"><?php echo htmlspecialchars($laporan['isi']); ?></p>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <small class="text-muted">
                                                        <i class="bi bi-person"></i> <?php echo htmlspecialchars($item['nama']); ?>
                                                    </small>
                                                    <span class="badge <?php echo $status_class; ?> small">
                                                        <?php echo $item['status'] == '0' ? 'Pending' : ucfirst($item['status']); ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    <div class="text-center">
                                        <a href="pengaduan.php" class="btn btn-sm btn-outline-primary">
                                            Lihat Semua <i class="bi bi-arrow-right"></i>
                                        </a>
                                    </div>
                                <?php else: ?>
                                    <div class="text-center py-4">
                                        <i class="bi bi-inbox h2 text-muted"></i>
                                        <p class="text-muted mb-0">Belum ada pengaduan</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Chart configuration
        const ctx = document.getElementById('trendChart').getContext('2d');
        const trendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [
                    <?php 
                    foreach ($chart_data as $data) {
                        echo "'" . date('M Y', strtotime($data['bulan'] . '-01')) . "',";
                    }
                    ?>
                ],
                datasets: [{
                    label: 'Pengaduan',
                    data: [
                        <?php 
                        foreach ($chart_data as $data) {
                            echo $data['jumlah'] . ',';
                        }
                        ?>
                    ],
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    },
                    x: {
                        grid: {
                            color: 'rgba(0,0,0,0.1)'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>