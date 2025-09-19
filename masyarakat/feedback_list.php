<?php
session_start();
require_once '../config/koneksi.php';

$nik = $_SESSION['user_id'];

$sql = "SELECT p.id_pengaduan, p.judul_pengaduan, p.tgl_pengaduan, p.status,
               COUNT(t.id_tanggapan) as total_tanggapan,
               MAX(t.tgl_tanggapan) as tanggapan_terakhir
        FROM pengaduan p
        LEFT JOIN tanggapan t ON p.id_pengaduan = t.id_pengaduan
        WHERE p.nik = ? AND p.status IN ('proses', 'selesai')
        GROUP BY p.id_pengaduan
        ORDER BY tanggapan_terakhir DESC
        LIMIT 5";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $nik);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $status_class = $row['status'] == 'selesai' ? 'success' : 'warning';
        $status_text = $row['status'] == 'selesai' ? 'Selesai' : 'Diproses';
        
        echo '<div class="border-start border-3 border-success ps-3 mb-3">';
        echo '<h6 class="mb-1">' . htmlspecialchars($row['judul_pengaduan']) . '</h6>';
        echo '<small class="text-muted">Pengaduan: ' . date('d/m/Y', strtotime($row['tgl_pengaduan'])) . '</small><br>';
        echo '<small class="text-success">Tanggapan terakhir: ' . date('d/m/Y H:i', strtotime($row['tanggapan_terakhir'])) . '</small><br>';
        echo '<span class="badge bg-' . $status_class . '">' . $status_text . '</span> ';
        echo '<span class="badge bg-info">' . $row['total_tanggapan'] . ' Tanggapan</span>';
        echo '<div class="mt-2">';
        echo '<a href="detail.php?id=' . $row['id_pengaduan'] . '" class="btn btn-sm btn-outline-primary">Lihat Detail</a>';
        echo '</div>';
        echo '</div>';
    }
} else {
    echo '<div class="text-center text-muted">';
    echo '<i class="bi bi-chat-dots h1"></i>';
    echo '<p>Belum ada feedback dari admin</p>';
    echo '</div>';
}
?>