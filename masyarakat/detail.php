<?php
session_start();
require_once '../config/koneksi.php';

// Cek login & level
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'masyarakat') {
    header('Location: ../index.php?page=login');
    exit();
}

// Ambil ID pengaduan
$id_pengaduan = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id_pengaduan <= 0) {
    $_SESSION['error'] = "ID pengaduan tidak valid";
    header('Location: riwayat.php');
    exit();
}

try {
    // Ambil data pengaduan
    $sql = "SELECT p.*, m.nama 
            FROM pengaduan p 
            JOIN masyarakat m ON p.nik = m.nik 
            WHERE p.id_pengaduan = ? AND p.nik = ?";
    $stmt = $db->query($sql, [$id_pengaduan, $_SESSION['user_id']]);
    $pengaduan = $stmt->fetch();

    if (!$pengaduan) {
        $_SESSION['error'] = "Pengaduan tidak ditemukan atau tidak punya akses";
        header('Location: riwayat.php');
        exit();
    }

    // Ambil tanggapan
    $tanggapan_sql = "SELECT t.*, p.nama_petugas 
                      FROM tanggapan t 
                      JOIN petugas p ON t.id_petugas = p.id_petugas 
                      WHERE t.id_pengaduan = ? 
                      ORDER BY t.tgl_tanggapan DESC";
    $tanggapan_stmt = $db->query($tanggapan_sql, [$id_pengaduan]);
    $tanggapan = $tanggapan_stmt->fetchAll();
} catch (Exception $e) {
    $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
    header('Location: riwayat.php');
    exit();
}

// Format isi laporan (judul + isi)
function formatIsiLaporan($isi)
{
    $parts = explode(' | ', $isi, 2);
    return count($parts) === 2
        ? ['judul' => $parts[0], 'isi' => $parts[1]]
        : ['judul' => 'Pengaduan', 'isi' => $isi];
}
$laporan = formatIsiLaporan($pengaduan['isi_laporan']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengaduan #<?= $id_pengaduan ?> - Sistem Pengaduan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2d4a3e;
            --secondary: #87a96b;
            --success: #6b8e5a;
            --warning: #c9a876;
            --info: #7ba098;
        }
        body { background:#f8faf9; font-family:'Inter',sans-serif }
        .sidebar { min-height:100vh; background:linear-gradient(135deg,var(--primary),var(--secondary)); }
        .sidebar .nav-link{color:#fff8; border-radius:8px; margin:5px 0}
        .sidebar .nav-link:hover{background:#fff2; color:#fff}
        .card{border-radius:15px; border:none; box-shadow:0 4px 6px rgba(0,0,0,.1)}
        .status-timeline{position:relative; padding-left:30px}
        .status-timeline::before{content:''; position:absolute; left:8px; top:0; bottom:0; width:2px; background:#dee2e6}
        .timeline-item{position:relative; margin-bottom:20px}
        .timeline-item::before{content:''; position:absolute; left:-24px; top:8px; width:12px; height:12px; border-radius:50%; background:var(--secondary)}
        .timeline-item.active::before{background:var(--success); box-shadow:0 0 0 4px rgba(107,142,90,.3)}
        .badge-proses{background:var(--info)} .badge-selesai{background:var(--success)} .badge-pending{background:var(--warning)}
    </style>
</head>
<body>
<div class="container-fluid">
<div class="row">
    <!-- Sidebar -->
    <nav class="col-md-3 col-lg-2 d-md-block sidebar collapse">
        <div class="pt-3 text-center text-white">
            <h5>Dashboard Masyarakat</h5>
            <p class="text-white-50">Selamat datang, <?= htmlspecialchars($_SESSION['nama']); ?></p>
        </div>
        <ul class="nav flex-column px-2">
            <li class="nav-item"><a class="nav-link" href="dashboard.php"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="pengaduan.php"><i class="bi bi-file-earmark-text"></i> Buat Pengaduan</a></li>
            <li class="nav-item"><a class="nav-link" href="riwayat.php"><i class="bi bi-clock-history"></i> Riwayat</a></li>
            <li class="nav-item"><a class="nav-link" href="profile.php"><i class="bi bi-person"></i> Profil</a></li>
            <li class="nav-item mt-3"><a class="nav-link text-warning" href="../config/logout.php"><i class="bi bi-box-arrow-right"></i> Logout</a></li>
        </ul>
    </nav>

    <!-- Main -->
    <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-3">
        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
            <h2>Detail Pengaduan #<?= $id_pengaduan ?></h2>
            <a href="riwayat.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
        </div>

        <div class="row">
            <!-- Detail -->
            <div class="col-lg-8 mb-4">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="bi bi-file-earmark-text"></i> <?= htmlspecialchars($laporan['judul']); ?></h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Tanggal:</strong> <?= date('d F Y', strtotime($pengaduan['tgl_pengaduan'])) ?></p>
                        <p><strong>Status:</strong>
                            <?php
                            $map = [
                                '0' => ['Menunggu Tanggapan', 'badge-pending', 'bi-clock'],
                                'proses' => ['Sedang Diproses', 'badge-proses', 'bi-arrow-repeat'],
                                'selesai' => ['Selesai Ditangani', 'badge-selesai', 'bi-check-circle']
                            ];
                            [$text,$class,$icon] = $map[$pengaduan['status']] ?? ['Unknown','bg-secondary','bi-question'];
                            ?>
                            <span class="badge <?= $class ?>"><i class="bi <?= $icon ?>"></i> <?= $text ?></span>
                        </p>
                        <p><strong>Pelapor:</strong> <?= htmlspecialchars($pengaduan['nama']) ?> (<?= $pengaduan['nik'] ?>)</p>

                        <div class="mb-3"><strong>Detail Laporan:</strong>
                            <div class="p-3 bg-light rounded"><?= nl2br(htmlspecialchars($laporan['isi'])) ?></div>
                        </div>

                        <?php if (!empty($pengaduan['foto'])): 
                            $fotoPath = "../uploads/" . basename($pengaduan['foto']);
                            ?>
                            <div>
                                <strong>Foto Pendukung:</strong><br>
                                <?php if (is_file($fotoPath)): ?>
                                    <img src="<?= $fotoPath ?>" class="img-fluid rounded mt-2" 
                                         style="max-width:300px;cursor:pointer" 
                                         data-bs-toggle="modal" data-bs-target="#imageModal" 
                                         alt="Foto Pengaduan">
                                    <small class="d-block text-muted">Klik untuk memperbesar</small>
                                <?php else: ?>
                                    <span class="text-muted">File foto tidak ditemukan</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header"><i class="bi bi-clock-history"></i> Timeline</div>
                    <div class="card-body status-timeline">
                        <div class="timeline-item active">
                            <div class="fw-bold">Diterima</div>
                            <small class="text-muted"><?= date('d M Y, H:i', strtotime($pengaduan['created_at'])) ?></small>
                        </div>
                        <div class="timeline-item <?= $pengaduan['status']!='0'?'active':'' ?>">
                            <div class="fw-bold">Diproses</div>
                            <small class="text-muted">
                                <?= $pengaduan['status']!='0' ? date('d M Y, H:i', strtotime($pengaduan['updated_at'])) : 'Menunggu' ?>
                            </small>
                        </div>
                        <div class="timeline-item <?= $pengaduan['status']=='selesai'?'active':'' ?>">
                            <div class="fw-bold">Selesai</div>
                            <small class="text-muted">
                                <?= $pengaduan['status']=='selesai' ? date('d M Y, H:i', strtotime($pengaduan['updated_at'])) : 'Belum selesai' ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tanggapan -->
        <?php if ($tanggapan): ?>
            <div class="card">
                <div class="card-header bg-success text-white"><i class="bi bi-chat-dots"></i> Tanggapan</div>
                <div class="card-body">
                    <?php foreach ($tanggapan as $resp): ?>
                        <div class="border rounded p-3 mb-3">
                            <div class="d-flex justify-content-between">
                                <strong><i class="bi bi-person-badge"></i> <?= htmlspecialchars($resp['nama_petugas']) ?></strong>
                                <small class="text-muted"><?= date('d M Y, H:i', strtotime($resp['tgl_tanggapan'])) ?></small>
                            </div>
                            <div><?= nl2br(htmlspecialchars($resp['tanggapan'])) ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center text-muted py-4">
                    <i class="bi bi-chat-dots h1 d-block mb-2"></i> Belum ada tanggapan
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>
</div>

<!-- Modal Foto -->
<?php if (!empty($pengaduan['foto']) && is_file("../uploads/".basename($pengaduan['foto']))): ?>
<div class="modal fade" id="imageModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Foto Pengaduan</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body text-center">
        <img src="../uploads/<?= basename($pengaduan['foto']) ?>" class="img-fluid rounded">
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
