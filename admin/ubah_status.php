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
$sql = "SELECT * FROM pengaduan WHERE id_pengaduan = :id";
$stmt = $db->prepare($sql);
$stmt->execute([':id' => $id]);
$pengaduan = $stmt->fetch();

if (!$pengaduan) {
    die("Pengaduan tidak ditemukan");
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? '';
    if (!in_array($status, ['0', 'proses', 'selesai'])) {
        $errors[] = "Status tidak valid.";
    }
    if (empty($errors)) {
        $update_sql = "UPDATE pengaduan SET status = :status WHERE id_pengaduan = :id";
        $update_stmt = $db->prepare($update_sql);
        $update_stmt->execute([':status' => $status, ':id' => $id]);
        header("Location: detail_pengaduan.php?id=$id");
        exit();
    }
}

function statusText($status) {
    return $status == '0' ? 'Pending' : ucfirst($status);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Ubah Status Pengaduan #<?= htmlspecialchars($id) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body>
<div class="container mt-4">
    <h1>Ubah Status Pengaduan #<?= htmlspecialchars($id) ?></h1>
    <a href="detail_pengaduan.php?id=<?= htmlspecialchars($id) ?>" class="btn btn-secondary mb-3">Kembali ke Detail</a>
    
    <?php if ($errors): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    
    <form method="post" class="w-50">
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select" required>
                <option value="0" <?= $pengaduan['status'] == '0' ? 'selected' : '' ?>>Pending</option>
                <option value="proses" <?= $pengaduan['status'] == 'proses' ? 'selected' : '' ?>>Proses</option>
                <option value="selesai" <?= $pengaduan['status'] == 'selesai' ? 'selected' : '' ?>>Selesai</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
    </form>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>