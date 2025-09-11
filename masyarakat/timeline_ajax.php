<?php
session_start();
require_once '../config/koneksi.php';

header('Content-Type: application/json');

if (!isset($_SESSION['login']) || $_SESSION['level'] != 'masyarakat') {
    http_response_code(403);
    echo json_encode(['error' => 'Akses ditolak']);
    exit();
}

$id_pengaduan = isset($_GET['id']) ? (int) $_GET['id'] : 0;
if ($id_pengaduan <= 0) {
    echo json_encode([
        'created_at' => '-',
        'proses_at'  => '-',
        'selesai_at' => '-'
    ]);
    exit();
}

try {
    $sql = "SELECT tgl_pengaduan, status, created_at, updated_at 
            FROM pengaduan WHERE id_pengaduan = ? AND nik = ?";
    $pengaduan = $db->fetch($sql, [$id_pengaduan, $_SESSION['user_id']]);

    if (!$pengaduan) {
        echo json_encode([
            'created_at' => '-',
            'proses_at'  => '-',
            'selesai_at' => '-'
        ]);
        exit();
    }

    // Format waktu dengan lebih baik
    $created_at = date('d M Y, H:i', strtotime($pengaduan['created_at']));
    $proses_at  = $pengaduan['status'] != '0' ? date('d M Y, H:i', strtotime($pengaduan['updated_at'])) : 'Menunggu';
    $selesai_at = $pengaduan['status'] == 'selesai' ? date('d M Y, H:i', strtotime($pengaduan['updated_at'])) : 'Belum selesai';

    echo json_encode([
        'created_at' => $created_at,
        'proses_at'  => $proses_at,
        'selesai_at' => $selesai_at
    ]);
} catch (Exception $e) {
    echo json_encode([
        'created_at' => '-',
        'proses_at'  => '-',
        'selesai_at' => '-'
    ]);
}