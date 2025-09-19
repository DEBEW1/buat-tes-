<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['level'] != 'masyarakat') {
    header('Location: ../index.php?page=login');
    exit();
}

$id_pengaduan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_pengaduan <= 0) {
    $_SESSION['error'] = "ID pengaduan tidak valid";
    header('Location: riwayat.php');
    exit();
}

try {
    $sql = "SELECT p.*, m.nama 
            FROM pengaduan p 
            JOIN masyarakat m ON p.nik = m.nik 
            WHERE p.id_pengaduan = ? AND p.nik = ?";
    $pengaduan = $db->fetch($sql, [$id_pengaduan, $_SESSION['user_id']]);

    if (!$pengaduan) {
        $_SESSION['error'] = "Pengaduan tidak ditemukan atau tidak punya akses";
        header('Location: riwayat.php');
        exit();
    }

    // Path foto yang konsisten dengan proses_pengaduan.php
    $fotoPath = null;
    $fotoURL = null;
    
    if (!empty($pengaduan['foto'])) {
        // Path server untuk pengecekan file
        $fotoServer = __DIR__ . "/../database/img/" . basename($pengaduan['foto']);
        // URL untuk browser
        $fotoURL = "../database/img/" . basename($pengaduan['foto']);
        
        // Cek apakah file benar-benar ada
        if (is_file($fotoServer)) {
            $fotoPath = $fotoServer;
        }
    }

    // Get tanggapan if exists
    $tanggapan_sql = "SELECT t.*, p.nama_petugas 
                      FROM tanggapan t 
                      JOIN petugas p ON t.id_petugas = p.id_petugas 
                      WHERE t.id_pengaduan = ?";
    $tanggapan = $db->fetch($tanggapan_sql, [$id_pengaduan]);
    
    // Tentukan tanggal proses berdasarkan tanggapan
    $tgl_proses = null;
    $tgl_selesai = null;
    
    if ($tanggapan) {
        $tgl_proses = $tanggapan['tgl_tanggapan'];
        if ($pengaduan['status'] == 'selesai') {
            $tgl_selesai = $tanggapan['tgl_tanggapan'];
        }
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
    header('Location: riwayat.php');
    exit();
}

function formatIsiLaporan($isi) {
    $parts = explode(' | ', $isi, 2);
    return count($parts) === 2 ? ['judul'=>$parts[0],'isi'=>$parts[1]] : ['judul'=>'Pengaduan','isi'=>$isi];
}
$laporan = formatIsiLaporan($pengaduan['isi_laporan']);

// Function untuk menentukan status timeline
function getTimelineStatus($status) {
    switch($status) {
        case '0':
            return [
                'diterima' => true,
                'proses' => false, 
                'selesai' => false
            ];
        case 'proses':
            return [
                'diterima' => true,
                'proses' => true,
                'selesai' => false
            ];
        case 'selesai':
            return [
                'diterima' => true,
                'proses' => true,
                'selesai' => true
            ];
        default:
            return [
                'diterima' => true,
                'proses' => false,
                'selesai' => false
            ];
    }
}

$timeline_status = getTimelineStatus($pengaduan['status']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Detail Pengaduan #<?= $id_pengaduan ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
:root{--primary:#2d4a3e;--secondary:#87a96b;--success:#6b8e5a;--warning:#c9a876;--info:#7ba098;}
body{background:#f8faf9;font-family:'Inter',sans-serif;}
.card{border-radius:15px;border:none;box-shadow:0 4px 6px rgba(0,0,0,.1);}
.status-timeline{position:relative;padding-left:30px;}
.status-timeline::before{content:'';position:absolute;left:8px;top:0;bottom:0;width:2px;background:#dee2e6;}
.timeline-item{position:relative;margin-bottom:20px;padding:10px 0;}
.timeline-item::before{content:'';position:absolute;left:-24px;top:15px;width:12px;height:12px;border-radius:50%;background:#dee2e6;border:2px solid #fff;z-index:2;}
.timeline-item.active::before{background:var(--success);box-shadow:0 0 0 4px rgba(107,142,90,.2);}
.timeline-item.current::before{background:var(--info);box-shadow:0 0 0 4px rgba(123,160,152,.2);animation:pulse 2s infinite;}
.timeline-item.pending::before{background:var(--warning);box-shadow:0 0 0 4px rgba(201,168,118,.2);}
.timeline-content{opacity:0.6;transition:opacity 0.3s ease;}
.timeline-item.active .timeline-content{opacity:1;}
.timeline-item.current .timeline-content{opacity:1;font-weight:600;}
.badge-proses{background:var(--info);}
.badge-selesai{background:var(--success);}
.badge-pending{background:var(--warning);}
.photo-container {
    position: relative;
    display: inline-block;
}
.photo-container img {
    transition: transform 0.3s ease;
    border: 3px solid #dee2e6;
}
.photo-container img:hover {
    transform: scale(1.05);
    border-color: var(--primary);
}
.photo-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.7));
    color: white;
    padding: 10px;
    text-align: center;
    border-radius: 0 0 8px 8px;
}
@keyframes pulse {
    0% { box-shadow: 0 0 0 0 rgba(123,160,152,.4); }
    70% { box-shadow: 0 0 0 10px rgba(123,160,152,0); }
    100% { box-shadow: 0 0 0 0 rgba(123,160,152,0); }
}
.status-indicator {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.9em;
}
.auto-refresh-indicator {
    position: fixed;
    top: 20px;
    right: 20px;
    background: rgba(0,0,0,0.8);
    color: white;
    padding: 8px 12px;
    border-radius: 20px;
    font-size: 0.8em;
    z-index: 1000;
    display: none;
}
</style>
</head>
<body>
<div class="auto-refresh-indicator" id="refreshIndicator">
    <i class="bi bi-arrow-clockwise"></i> Memperbarui data...
</div>

<div class="container my-4">
<div class="d-flex justify-content-between align-items-center mb-3">
<h2>Detail Pengaduan #<?= $id_pengaduan ?></h2>
<div class="d-flex gap-2">
    <button class="btn btn-outline-info btn-sm" onclick="refreshData()" title="Refresh Data">
        <i class="bi bi-arrow-clockwise"></i> Refresh
    </button>
    <a href="riwayat.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>
</div>

<div class="row g-4">
<div class="col-lg-8">
<div class="card">
<div class="card-header bg-primary text-white">
<h5><i class="bi bi-file-earmark-text"></i> <?= htmlspecialchars($laporan['judul']) ?></h5>
</div>
<div class="card-body">
<p><strong>Tanggal:</strong> <?= date('d F Y, H:i', strtotime($pengaduan['tgl_pengaduan'])) ?></p>
<p><strong>Status:</strong>
<?php
$status_map = [
    '0' => ['Menunggu Tanggapan', 'badge-pending', 'bi-clock', 'text-warning'],
    'proses' => ['Sedang Diproses', 'badge-proses', 'bi-arrow-repeat', 'text-info'], 
    'selesai' => ['Selesai Ditangani', 'badge-selesai', 'bi-check-circle', 'text-success']
];
[$text, $class, $icon, $text_class] = $status_map[$pengaduan['status']] ?? ['Unknown', 'bg-secondary', 'bi-question', 'text-muted'];
?>
<span class="badge <?= $class ?>"><i class="bi <?= $icon ?>"></i> <?= $text ?></span>
<small class="ms-2 <?= $text_class ?>">
    <?php if($pengaduan['status'] == '0'): ?>
        Menunggu petugas untuk memproses
    <?php elseif($pengaduan['status'] == 'proses'): ?>
        Sedang dalam penanganan
    <?php else: ?>
        Pengaduan telah selesai ditangani
    <?php endif; ?>
</small>
</p>
<p><strong>Pelapor:</strong> <?= htmlspecialchars($pengaduan['nama']) ?> (<?= $pengaduan['nik'] ?>)</p>

<div class="mb-3">
<strong>Detail Laporan:</strong>
<div class="p-3 bg-light rounded"><?= nl2br(htmlspecialchars($laporan['isi'])) ?></div>
</div>

<div class="mb-3">
<strong>Foto Pendukung:</strong><br>
<?php if($fotoPath && is_file($fotoPath)): ?>
    <div class="photo-container mt-2">
        <img src="<?= $fotoURL ?>" class="img-fluid rounded" style="max-width:400px;cursor:pointer" data-bs-toggle="modal" data-bs-target="#imageModal" alt="Foto Pengaduan">
        <div class="photo-overlay">
            <small><i class="bi bi-zoom-in"></i> Klik untuk memperbesar</small>
        </div>
    </div>
    <small class="d-block text-muted mt-1">
        <i class="bi bi-info-circle"></i> File: <?= basename($pengaduan['foto']) ?>
        <?php if(file_exists($fotoPath)): ?>
        | Ukuran: <?= number_format(filesize($fotoPath)/1024, 1) ?> KB
        <?php endif; ?>
    </small>
<?php else: ?>
    <div class="alert alert-info mt-2">
        <i class="bi bi-image"></i> 
        <?php if(empty($pengaduan['foto'])): ?>
            Tidak ada foto yang dilampirkan
        <?php else: ?>
            Foto tidak dapat ditampilkan (file tidak ditemukan: <?= htmlspecialchars($pengaduan['foto']) ?>)
            <br><small class="text-muted">Path yang dicari: <?= $fotoServer ?? 'N/A' ?></small>
        <?php endif; ?>
    </div>
<?php endif; ?>
</div>
</div>
</div>
</div>

<div class="col-lg-4">
<div class="card mb-4">
<div class="card-header d-flex justify-content-between align-items-center">
    <span><i class="bi bi-clock-history"></i> Timeline Status</span>
    <small class="text-muted">Real-time</small>
</div>
<div class="card-body status-timeline">
    <!-- Diterima -->
    <div class="timeline-item <?= $timeline_status['diterima'] ? 'active' : '' ?>">
        <div class="timeline-content">
            <div class="d-flex justify-content-between align-items-center">
                <div class="fw-bold">
                    <i class="bi bi-check-circle-fill text-success me-1"></i>
                    Pengaduan Diterima
                </div>
                <span class="status-indicator text-success">
                    <i class="bi bi-check"></i> Selesai
                </span>
            </div>
            <small class="text-muted d-block mt-1">
                <i class="bi bi-calendar3"></i> 
                <?= date('d M Y, H:i', strtotime($pengaduan['tgl_pengaduan'])) ?>
            </small>
            <small class="text-muted d-block">Pengaduan telah masuk ke sistem</small>
        </div>
    </div>

    <!-- Diproses -->
    <div class="timeline-item <?= $timeline_status['proses'] ? ($pengaduan['status'] == 'proses' ? 'current' : 'active') : 'pending' ?>">
        <div class="timeline-content">
            <div class="d-flex justify-content-between align-items-center">
                <div class="fw-bold">
                    <?php if($timeline_status['proses']): ?>
                        <i class="bi bi-arrow-repeat text-info me-1"></i>
                        Sedang Diproses
                    <?php else: ?>
                        <i class="bi bi-clock text-warning me-1"></i>
                        Menunggu Diproses
                    <?php endif; ?>
                </div>
                <?php if($timeline_status['proses']): ?>
                    <span class="status-indicator <?= $pengaduan['status'] == 'proses' ? 'text-info' : 'text-success' ?>">
                        <i class="bi <?= $pengaduan['status'] == 'proses' ? 'bi-arrow-repeat' : 'bi-check' ?>"></i>
                        <?= $pengaduan['status'] == 'proses' ? 'Aktif' : 'Selesai' ?>
                    </span>
                <?php else: ?>
                    <span class="status-indicator text-warning">
                        <i class="bi bi-clock"></i> Menunggu
                    </span>
                <?php endif; ?>
            </div>
            <?php if($tgl_proses): ?>
                <small class="text-muted d-block mt-1">
                    <i class="bi bi-calendar3"></i> 
                    <?= date('d M Y, H:i', strtotime($tgl_proses)) ?>
                </small>
                <small class="text-muted d-block">
                    <?= $pengaduan['status'] == 'proses' ? 'Petugas sedang menangani' : 'Telah diproses petugas' ?>
                </small>
            <?php else: ?>
                <small class="text-muted d-block mt-1">Menunggu petugas memproses pengaduan</small>
            <?php endif; ?>
        </div>
    </div>

    <!-- Selesai -->
    <div class="timeline-item <?= $timeline_status['selesai'] ? 'active' : 'pending' ?>">
        <div class="timeline-content">
            <div class="d-flex justify-content-between align-items-center">
                <div class="fw-bold">
                    <?php if($timeline_status['selesai']): ?>
                        <i class="bi bi-check-circle-fill text-success me-1"></i>
                        Selesai Ditangani
                    <?php else: ?>
                        <i class="bi bi-hourglass text-muted me-1"></i>
                        Menunggu Penyelesaian
                    <?php endif; ?>
                </div>
                <?php if($timeline_status['selesai']): ?>
                    <span class="status-indicator text-success">
                        <i class="bi bi-check-circle"></i> Selesai
                    </span>
                <?php else: ?>
                    <span class="status-indicator text-muted">
                        <i class="bi bi-hourglass"></i> Belum
                    </span>
                <?php endif; ?>
            </div>
            <?php if($tgl_selesai): ?>
                <small class="text-muted d-block mt-1">
                    <i class="bi bi-calendar3"></i> 
                    <?= date('d M Y, H:i', strtotime($tgl_selesai)) ?>
                </small>
                <small class="text-muted d-block">Pengaduan telah selesai ditangani</small>
            <?php else: ?>
                <small class="text-muted d-block mt-1">
                    <?= $pengaduan['status'] == 'proses' ? 'Sedang dalam penanganan' : 'Menunggu untuk diproses' ?>
                </small>
            <?php endif; ?>
        </div>
    </div>
</div>
</div>

<div class="card">
<div class="card-header bg-success text-white">
    <i class="bi bi-chat-dots"></i> Tanggapan Petugas
    <?php if($tanggapan): ?>
        <span class="badge bg-light text-success ms-2">
            <i class="bi bi-check-circle"></i> Ada Tanggapan
        </span>
    <?php else: ?>
        <span class="badge bg-light text-warning ms-2">
            <i class="bi bi-clock"></i> Belum Ada
        </span>
    <?php endif; ?>
</div>
<div class="card-body">
<?php if($tanggapan): ?>
    <div class="d-flex">
        <div class="flex-shrink-0">
            <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                <i class="bi bi-person-check text-white"></i>
            </div>
        </div>
        <div class="flex-grow-1 ms-3">
            <h6 class="mb-1"><?= htmlspecialchars($tanggapan['nama_petugas']) ?></h6>
            <small class="text-muted">
                <i class="bi bi-calendar3"></i> 
                <?= date('d M Y', strtotime($tanggapan['tgl_tanggapan'])) ?>
                <i class="bi bi-clock ms-2"></i> <?= date('H:i', strtotime($tanggapan['tgl_tanggapan'])) ?> WIB
            </small>
            <div class="mt-2 p-3 bg-light rounded">
                <?= nl2br(htmlspecialchars($tanggapan['tanggapan'])) ?>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="text-center text-muted py-4">
        <i class="bi bi-chat-dots h1 mb-2"></i>
        <p class="mb-1">Belum ada tanggapan dari petugas</p>
        <?php if($pengaduan['status'] == '0'): ?>
            <small>Pengaduan Anda sedang menunggu untuk diproses</small>
        <?php elseif($pengaduan['status'] == 'proses'): ?>
            <small>Petugas sedang memproses pengaduan Anda</small>
        <?php endif; ?>
    </div>
<?php endif; ?>
</div>
</div>
</div>
</div>
</div>

<!-- Modal for Image -->
<?php if($fotoPath && is_file($fotoPath)): ?>
<div class="modal fade" id="imageModal" tabindex="-1">
<div class="modal-dialog modal-xl modal-dialog-centered">
<div class="modal-content">
<div class="modal-header">
<h5 class="modal-title">
    <i class="bi bi-image"></i> Foto Pengaduan #<?= $id_pengaduan ?>
</h5>
<button type="button" class="btn-close" data-bs-dismiss="modal"></button>
</div>
<div class="modal-body text-center">
<img src="<?= $fotoURL ?>" class="img-fluid rounded" style="max-height:80vh;">
</div>
<div class="modal-footer justify-content-center">
<small class="text-muted">
    <?= basename($pengaduan['foto']) ?> 
    <?php if(file_exists($fotoPath)): ?>
    | <?= number_format(filesize($fotoPath)/1024, 1) ?> KB
    <?php endif; ?>
</small>
</div>
</div>
</div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
let refreshInterval;
let lastStatus = '<?= $pengaduan['status'] ?>';

function showRefreshIndicator() {
    document.getElementById('refreshIndicator').style.display = 'block';
}

function hideRefreshIndicator() {
    document.getElementById('refreshIndicator').style.display = 'none';
}

function refreshData() {
    showRefreshIndicator();
    setTimeout(() => {
        location.reload();
    }, 500);
}

// Auto refresh setiap 10 detik jika status bukan selesai
function startAutoRefresh() {
    if (lastStatus !== 'selesai') {
        refreshInterval = setInterval(() => {
            console.log('Auto refreshing data...');
            refreshData();
        }, 10000); // 10 detik
    }
}

// Hentikan auto refresh jika status selesai
function stopAutoRefresh() {
    if (refreshInterval) {
        clearInterval(refreshInterval);
        refreshInterval = null;
    }
}

// Mulai auto refresh saat halaman dimuat
document.addEventListener('DOMContentLoaded', function() {
    console.log('Status pengaduan:', lastStatus);
    
    if (lastStatus !== 'selesai') {
        startAutoRefresh();
        console.log('Auto refresh started (10 seconds interval)');
    } else {
        console.log('Pengaduan selesai, no auto refresh needed');
    }
});

// Hentikan auto refresh saat user meninggalkan halaman
window.addEventListener('beforeunload', function() {
    stopAutoRefresh();
});

// Debug info - diperluas untuk troubleshooting
console.log('=== DEBUG DATETIME ===');
console.log('Detail pengaduan loaded:', {
    id: <?= $id_pengaduan ?>,
    status: '<?= $pengaduan['status'] ?>',
    timeline: <?= json_encode($timeline_status) ?>,
    timeline_dates: <?= json_encode($timeline_dates) ?>,
    tgl_pengaduan_raw: '<?= $pengaduan['tgl_pengaduan'] ?>',
    tgl_pengaduan_formatted: '<?= formatDateTime($pengaduan['tgl_pengaduan']) ?>',
    <?php if($tanggapan): ?>
    tgl_tanggapan_raw: '<?= $tanggapan['tgl_tanggapan'] ?>',
    tgl_tanggapan_formatted: '<?= formatDateTime($tanggapan['tgl_tanggapan']) ?>',
    <?php endif; ?>
    foto: '<?= $pengaduan['foto'] ?? 'null' ?>',
    tanggapan: <?= $tanggapan ? 'true' : 'false' ?>
});

// Test parsing manual untuk debugging
<?php if($tanggapan): ?>
console.log('Manual test tanggapan:');
console.log('Raw:', '<?= $tanggapan['tgl_tanggapan'] ?>');
console.log('JS Date:', new Date('<?= $tanggapan['tgl_tanggapan'] ?>'));
<?php endif; ?>

console.log('Manual test pengaduan:');
console.log('Raw:', '<?= $pengaduan['tgl_pengaduan'] ?>');
console.log('JS Date:', new Date('<?= $pengaduan['tgl_pengaduan'] ?>'));
console.log('======================');
</script>
</body>
</html>