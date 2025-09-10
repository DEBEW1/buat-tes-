<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['level'] != 'admin') {
    header('Location: ../index.php?page=login');
    exit();
}

// Ambil filter dan pencarian dari query string
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Bangun query dengan filter dan pencarian
$where = [];
$params = [];

if ($status_filter && in_array($status_filter, ['0', 'proses', 'selesai'])) {
    $where[] = "p.status = :status";
    $params[':status'] = $status_filter;
}

if ($search !== '') {
    $where[] = "(p.isi_laporan LIKE :search OR m.nama LIKE :search OR DATE(p.tgl_pengaduan) = :search_date)";
    $params[':search'] = "%$search%";
    $params[':search_date'] = $search; // jika input tanggal, cocokkan juga
}

$where_sql = '';
if (count($where) > 0) {
    $where_sql = 'WHERE ' . implode(' AND ', $where);
}

$sql = "SELECT p.*, m.nama FROM pengaduan p 
        JOIN masyarakat m ON p.nik = m.nik
        $where_sql
        ORDER BY p.tgl_pengaduan DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$pengaduan_list = $stmt->fetchAll();

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
    <meta charset="UTF-8" />
    <title>Kelola Pengaduan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
    <h1>Kelola Pengaduan</h1>
    
    <form method="get" class="row g-3 mb-4">
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">-- Filter Status --</option>
                <option value="0" <?= $status_filter === '0' ? 'selected' : '' ?>>Pending</option>
                <option value="proses" <?= $status_filter === 'proses' ? 'selected' : '' ?>>Proses</option>
                <option value="selesai" <?= $status_filter === 'selesai' ? 'selected' : '' ?>>Selesai</option>
            </select>
        </div>
        <div class="col-md-6">
            <input type="text" name="search" class="form-control" placeholder="Cari judul, nama pelapor, atau tanggal (YYYY-MM-DD)" value="<?= htmlspecialchars($search) ?>" />
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary w-100">Filter & Cari</button>
        </div>
    </form>
    
    <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>ID</th>
                <th>Pelapor</th>
                <th>Judul</th>
                <th>Status</th>
                <th>Tanggal</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($pengaduan_list) === 0): ?>
                <tr><td colspan="6" class="text-center">Tidak ada data pengaduan</td></tr>
            <?php else: ?>
                <?php foreach ($pengaduan_list as $p): 
                    $laporan = formatIsiLaporan($p['isi_laporan'], 50);
                    $status_text = $p['status'] == '0' ? 'Pending' : ucfirst($p['status']);
                    $badge_class = '';
                    switch ($p['status']) {
                        case '0': $badge_class = 'bg-warning text-dark'; break;
                        case 'proses': $badge_class = 'bg-info text-white'; break;
                        case 'selesai': $badge_class = 'bg-success text-white'; break;
                    }
                ?>
                <tr>
                    <td><?= htmlspecialchars($p['id_pengaduan']) ?></td>
                    <td><?= htmlspecialchars($p['nama']) ?></td>
                    <td title="<?= htmlspecialchars($laporan['isi']) ?>"><?= htmlspecialchars($laporan['judul']) ?></td>
                    <td><span class="badge <?= $badge_class ?>"><?= $status_text ?></span></td>
                    <td><?= htmlspecialchars($p['tgl_pengaduan']) ?></td>
                    <td>
                        <a href="detail_pengaduan.php?id=<?= $p['id_pengaduan'] ?>" class="btn btn-sm btn-info">Detail</a>
                        <a href="ubah_status.php?id=<?= $p['id_pengaduan'] ?>" class="btn btn-sm btn-warning">Ubah Status</a>
                        <a href="komentar.php?id=<?= $p['id_pengaduan'] ?>" class="btn btn-sm btn-secondary">Komentar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
