<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['level'] != 'admin') {
    header('Location: ../index.php?page=login');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: pengaduan.php');
    exit();
}

$id = $_GET['id'];

// Ambil data pengaduan
$sql = "SELECT p.*, m.nama FROM pengaduan p JOIN masyarakat m ON p.nik = m.nik WHERE p.id_pengaduan = :id";
$stmt = $db->prepare($sql);
$stmt->execute([':id' => $id]);
$pengaduan = $stmt->fetch();

if (!$pengaduan) {
    die("Pengaduan tidak ditemukan");
}

function formatIsiLaporanFull($isi_laporan) {
    $parts = explode(' | ', $isi_laporan, 2);
    if (count($parts) == 2) {
        return ['judul' => $parts[0], 'isi' => $parts[1]];
    }
    return ['judul' => 'Pengaduan', 'isi' => $isi_laporan];
}

$laporan = formatIsiLaporanFull($pengaduan['isi_laporan']);
$status_text = $pengaduan['status'] == '0' ? 'Pending' : ucfirst($pengaduan['status']);
$badge_class = '';
switch ($pengaduan['status']) {
    case '0': $badge_class = 'bg-warning text-dark'; break;
    case 'proses': $badge_class = 'bg-info text-white'; break;
    case 'selesai': $badge_class = 'bg-success text-white'; break;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Detail Pengaduan #<?= htmlspecialchars($pengaduan['id_pengaduan']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
    <h1>Detail Pengaduan</h1>
    <a href="pengaduan.php" class="btn btn-secondary mb-3">Kembali ke Daftar</a>
    
    <div class="card">
        <div class="card-header">
            <h4><?= htmlspecialchars($laporan['judul']) ?></h4>
            <span class="badge <?= $badge_class ?>"><?= $status_text ?></span>
        </div>
        <div class="card-body">
            <p><strong>Pelapor:</strong> <?= htmlspecialchars($pengaduan['nama']) ?></p>
            <p><strong>Tanggal Pengaduan:</strong> <?= htmlspecialchars($pengaduan['tgl_pengaduan']) ?></p>
            <p><strong>Isi Laporan:</strong></p>
            <p><?= nl2br(htmlspecialchars($laporan['isi'])) ?></p>
            <?php if (!empty($pengaduan['foto'])): ?>
                <p><strong>Foto:</strong></p>
                <img src="../uploads/<?= htmlspecialchars($pengaduan['foto']) ?>" alt="Foto Pengaduan" class="img-fluid rounded" style="max-width: 400px;" />
            <?php endif; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>