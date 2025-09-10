<?php
// masyarakat/detail.php
session_start();
require_once '../config/koneksi.php';

// Cek apakah user sudah login dan levelnya masyarakat
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'masyarakat') {
    header('Location: ../index.php?page=login');
    exit();
}

// Ambil ID pengaduan dari URL
$id_pengaduan = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$id_pengaduan) {
    $_SESSION['error'] = "ID pengaduan tidak valid";
    header('Location: riwayat.php');
    exit();
}

try {
    // Ambil data pengaduan
    $sql = "SELECT p.*, m.nama FROM pengaduan p 
            JOIN masyarakat m ON p.nik = m.nik 
            WHERE p.id_pengaduan = ? AND p.nik = ?";
    $stmt = $db->query($sql, [$id_pengaduan, $_SESSION['user_id']]);
    
    if ($stmt->rowCount() == 0) {
        $_SESSION['error'] = "Pengaduan tidak ditemukan atau Anda tidak memiliki akses";
        header('Location: riwayat.php');
        exit();
    }
    
    $pengaduan = $stmt->fetch();
    
    // Ambil tanggapan jika ada
    $tanggapan_sql = "SELECT t.*, p.nama_petugas FROM tanggapan t 
                      JOIN petugas p ON t.id_petugas = p.id_petugas 
                      WHERE t.id_pengaduan = ? 
                      ORDER BY t.tgl_tanggapan DESC";
    $tanggapan_stmt = $db->query($tanggapan_sql, [$id_pengaduan]);
    $tanggapan = $tanggapan_stmt->fetchAll();
    
} catch (Exception $e) {
    $_SESSION['error'] = "Terjadi kesalahan saat mengambil data";
    header('Location: riwayat.php');
    exit();
}

// Function untuk memformat isi laporan
function formatIsiLaporan($isi_laporan) {
    $parts = explode(' | ', $isi_laporan, 2);
    if (count($parts) == 2) {
        return ['judul' => $parts[0], 'isi' => $parts[1]];
    } else {
        return ['judul' => 'Pengaduan', 'isi' => $isi_laporan];
    }
}

$laporan = formatIsiLaporan($pengaduan['isi_laporan']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengaduan #<?php echo $id_pengaduan; ?> - Sistem Pengaduan</title>
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
        
        .sidebar .nav-link:hover {
            background-color: rgba(255,255,255,0.2);
            color: white;
        }
        
        .card {
            border-radius: 15px;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        .status-timeline {
            position: relative;
            padding-left: 30px;
        }
        
        .status-timeline::before {
            content: '';
            position: absolute;
            left: 8px;
            top: 0;
            bottom: 0;
            width: 2px;
            background-color: #dee2e6;
        }
        
        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }
        
        .timeline-item::before {
            content: '';
            position: absolute;
            left: -24px;
            top: 8px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background-color: var(--secondary);
        }
        
        .timeline-item.active::before {
            background-color: var(--success);
            box-shadow: 0 0 0 4px rgba(107, 142, 90, 0.3);
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
        
        .image-modal {
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .image-modal:hover {
            transform: scale(1.05);
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            border: none;
        }
        
        .alert-info {
            background-color: rgba(123, 160, 152, 0.1);
            border-color: var(--info);
            color: var(--primary);
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
                    <h1 class="h2">Detail Pengaduan #<?php echo $id_pengaduan; ?></h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="riwayat.php" class="btn btn-outline-secondary me-2">
                            <i class="bi bi-arrow-left"></i> Kembali
                        </a>
                        <?php if ($pengaduan['status'] == 'selesai'): ?>
                            <button class="btn btn-success" disabled>
                                <i class="bi bi-check-circle"></i> Selesai
                            </button>
                        <?php else: ?>
                            <span class="badge bg-info fs-6 px-3 py-2">
                                <i class="bi bi-clock"></i> Dalam Proses
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="row">
                    <!-- Detail Pengaduan -->
                    <div class="col-lg-8 mb-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0">
                                    <i class="bi bi-file-earmark-text"></i> 
                                    <?php echo htmlspecialchars($laporan['judul']); ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <strong>Tanggal Pengaduan:</strong><br>
                                        <span class="text-muted">
                                            <i class="bi bi-calendar3"></i> 
                                            <?php echo date('d F Y', strtotime($pengaduan['tgl_pengaduan'])); ?>
                                        </span>
                                    </div>
                                    <div class="col-md-6">
                                        <strong>Status:</strong><br>
                                        <?php
                                        $status_class = '';
                                        $status_text = '';
                                        $status_icon = '';
                                        
                                        switch($pengaduan['status']) {
                                            case '0':
                                                $status_class = 'badge-pending';
                                                $status_text = 'Menunggu Tanggapan';
                                                $status_icon = 'bi-clock';
                                                break;
                                            case 'proses':
                                                $status_class = 'badge-proses';
                                                $status_text = 'Sedang Diproses';
                                                $status_icon = 'bi-arrow-repeat';
                                                break;
                                            case 'selesai':
                                                $status_class = 'badge-selesai';
                                                $status_text = 'Selesai Ditangani';
                                                $status_icon = 'bi-check-circle';
                                                break;
                                        }
                                        ?>
                                        <span class="badge <?php echo $status_class; ?> fs-6">
                                            <i class="<?php echo $status_icon; ?>"></i> 
                                            <?php echo $status_text; ?>
                                        </span>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <strong>Pelapor:</strong><br>
                                    <span class="text-muted">
                                        <i class="bi bi-person"></i> 
                                        <?php echo htmlspecialchars($pengaduan['nama']); ?> (<?php echo $pengaduan['nik']; ?>)
                                    </span>
                                </div>
                                
                                <div class="mb-4">
                                    <strong>Detail Laporan:</strong>
                                    <div class="mt-2 p-3 bg-light rounded">
                                        <?php echo nl2br(htmlspecialchars($laporan['isi'])); ?>
                                    </div>
                                </div>
                                
                                <?php if ($pengaduan['foto']): ?>
                                    <div class="mb-3">
                                        <strong>Foto Pendukung:</strong>
                                        <div class="mt-2">
                                            <img src="../uploads/pengaduan/<?php echo $pengaduan['foto']; ?>" 
                                                 class="img-fluid rounded image-modal" 
                                                 style="max-width: 300px; cursor: pointer;" 
                                                 alt="Foto Pengaduan"
                                                 data-bs-toggle="modal" data-bs-target="#imageModal">
                                            <small class="d-block text-muted mt-1">Klik untuk memperbesar</small>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Status Timeline & Tanggapan -->
                    <div class="col-lg-4">
                        <!-- Timeline Status -->
                        <div class="card mb-4">
                            <div class="card-header">
                                <h6 class="mb-0">
                                    <i class="bi bi-clock-history"></i> Timeline Pengaduan
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="status-timeline">
                                    <div class="timeline-item active">
                                        <div class="fw-bold">Pengaduan Diterima</div>
                                        <small class="text-muted">
                                            <?php echo date('d M Y, H:i', strtotime($pengaduan['created_at'])); ?>
                                        </small>
                                        <p class="small mb-0">Pengaduan telah diterima dan menunggu verifikasi</p>
                                    </div>
                                    
                                    <?php if ($pengaduan['status'] != '0'): ?>
                                        <div class="timeline-item active">
                                            <div class="fw-bold">Mulai Diproses</div>
                                            <small class="text-muted">
                                                <?php echo date('d M Y, H:i', strtotime($pengaduan['updated_at'])); ?>
                                            </small>
                                            <p class="small mb-0">Pengaduan sedang dalam tahap penanganan</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="timeline-item">
                                            <div class="fw-bold text-muted">Mulai Diproses</div>
                                            <p class="small mb-0 text-muted">Menunggu petugas untuk memproses</p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($pengaduan['status'] == 'selesai'): ?>
                                        <div class="timeline-item active">
                                            <div class="fw-bold">Pengaduan Selesai</div>
                                            <small class="text-muted">
                                                <?php echo date('d M Y, H:i', strtotime($pengaduan['updated_at'])); ?>
                                            </small>
                                            <p class="small mb-0">Pengaduan telah ditangani dan diselesaikan</p>
                                        </div>
                                    <?php else: ?>
                                        <div class="timeline-item">
                                            <div class="fw-bold text-muted">Pengaduan Selesai</div>
                                            <p class="small mb-0 text-muted">Menunggu penyelesaian</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Info Box -->
                        <div class="alert alert-info">
                            <h6><i class="bi bi-info-circle"></i> Informasi</h6>
                            <ul class="mb-0 small">
                                <li>Pengaduan akan ditanggapi maksimal 3x24 jam</li>
                                <li>Status akan diupdate secara berkala</li>
                                <li>Anda akan mendapat notifikasi saat ada update</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Tanggapan -->
                <?php if (!empty($tanggapan)): ?>
                    <div class="card mt-4">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">
                                <i class="bi bi-chat-dots"></i> 
                                Tanggapan Petugas (<?php echo count($tanggapan); ?>)
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($tanggapan as $resp): ?>
                                <div class="border rounded p-3 mb-3">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <strong class="text-primary">
                                                <i class="bi bi-person-badge"></i> 
                                                <?php echo htmlspecialchars($resp['nama_petugas']); ?>
                                            </strong>
                                            <small class="text-muted ms-2">Petugas</small>
                                        </div>
                                        <small class="text-muted">
                                            <i class="bi bi-clock"></i> 
                                            <?php echo date('d M Y, H:i', strtotime($resp['tgl_tanggapan'])); ?>
                                        </small>
                                    </div>
                                    <div class="mt-2">
                                        <?php echo nl2br(htmlspecialchars($resp['tanggapan'])); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card mt-4">
                        <div class="card-body text-center py-4">
                            <i class="bi bi-chat-dots h1 text-muted mb-3"></i>
                            <h5 class="text-muted">Belum Ada Tanggapan</h5>
                            <p class="text-muted mb-0">
                                Pengaduan Anda masih dalam proses verifikasi. 
                                Petugas akan memberikan tanggapan segera.
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <!-- Image Modal -->
    <?php if ($pengaduan['foto']): ?>
        <div class="modal fade" id="imageModal" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Foto Pengaduan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img src="../uploads/pengaduan/<?php echo $pengaduan['foto']; ?>" 
                             class="img-fluid rounded" alt="Foto Pengaduan">
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>