<?php
session_start();
require_once '../config/koneksi.php';

if (!isset($_SESSION['login']) || $_SESSION['level'] != 'masyarakat') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$nik = $_SESSION['user_id'];

// Hitung pengaduan yang sudah ditanggapi tapi belum dilihat
$sql = "SELECT COUNT(DISTINCT p.id_pengaduan) as total_notifikasi
        FROM pengaduan p
        INNER JOIN tanggapan t ON p.id_pengaduan = t.id_pengaduan
        WHERE p.nik = ? 
        AND p.status IN ('proses', 'selesai')
        AND (t.created_at > IFNULL(p.last_viewed, '0000-00-00 00:00:00') 
             OR p.updated_at > IFNULL(p.last_viewed, '0000-00-00 00:00:00'))";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $nik);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

echo json_encode([
    'status' => 'success',
    'total_notifikasi' => $data['total_notifikasi'] ?? 0,
    'message' => $data['total_notifikasi'] > 0 ? 'Ada tanggapan baru!' : 'Tidak ada notifikasi'
]);
?>