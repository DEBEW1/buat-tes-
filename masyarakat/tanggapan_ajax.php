<?php
session_start();
require_once '../config/koneksi.php';

$id_pengaduan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_pengaduan <= 0) {
    echo "<div class='text-center text-muted'>ID pengaduan tidak valid.</div>";
    exit;
}

// Ambil tanggapan dari database
$sql = "SELECT * FROM tanggapan WHERE id_pengaduan = ? ORDER BY created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_pengaduan);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Jika id_petugas ada, tampilkan "Admin" sebagai petugas
        $nama_petugas = !empty($row['id_petugas']) ? 'Admin' : 'Masyarakat';
        echo '<div class="mb-3 p-2 border rounded">';
        echo '<div class="fw-bold">' . htmlspecialchars($nama_petugas) . '</div>';
        echo '<div class="text-muted small">' . date('d M Y, H:i', strtotime($row['created_at'])) . '</div>';
        echo '<div>' . nl2br(htmlspecialchars($row['tanggapan'])) . '</div>';
        echo '</div>';
    }
} else {
    echo "<div class='text-center text-muted py-4'><i class='bi bi-chat-dots h1 mb-2'></i> Belum ada tanggapan.</div>";
}
?>
