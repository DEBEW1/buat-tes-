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

// Ambil komentar terkait pengaduan
$komentar_sql = "SELECT k.*, pt.nama_petugas FROM komentar k LEFT JOIN petugas pt ON k.id_petugas = pt.id_petugas WHERE k.id_pengaduan = :id ORDER BY k.created_at DESC";
$komentar_stmt = $db->prepare($komentar_sql);
$komentar_stmt->execute([':id' => $id]);
$komentar_list = $komentar_stmt->fetchAll();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isi_komentar = trim($_POST['isi_komentar'] ?? '');
    if ($isi_komentar === '') {
        $errors[] = "Komentar tidak boleh kosong.";
    }
    if (empty($errors)) {
        $insert_sql = "INSERT INTO komentar (id_pengaduan, id_petugas, isi_komentar, created_at) VALUES (:id_pengaduan, :id_petugas, :isi_komentar, NOW())";
        $insert_stmt = $db->prepare($insert_sql);
        $insert_stmt->execute([
            ':id_pengaduan' => $id,
            ':id_petugas' => $_SESSION['id_petugas'], // pastikan session menyimpan id_petugas
            ':isi_komentar' => $isi_komentar
        ]);
        header("Location: komentar.php?id=$id");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Komentar Pengaduan #<?= htmlspecialchars($id) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
    <h1>Komentar Pengaduan</h1>
    <a href="detail_pengaduan.php?id=<?= htmlspecialchars($id) ?>" class="btn btn-secondary mb-3">Kembali ke Detail</a>
    
    <div class="card mb-4">
        <div class="card-header">Daftar Komentar</div>
        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
            <?php if (count($komentar_list) === 0): ?>
                <p class="text-muted">Belum ada komentar.</p>
            <?php else: ?>
                <?php foreach ($komentar_list as $k): ?>
                    <div class="mb-3 border-bottom pb-2">
                        <strong><?= htmlspecialchars($k['nama_petugas'] ?? 'Petugas') ?></strong> 
                        <small class="text-muted"><?= $k['created_at'] ?></small>
                        <p><?= nl2br(htmlspecialchars($k['isi_komentar'])) ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="post" class="mb-5">
        <div class="mb-3">
            <label for="isi_komentar" class="form-label">Tulis Komentar</label>
            <textarea name="isi_komentar" id="isi_komentar" rows="4" class="form-control" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Kirim Komentar</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>