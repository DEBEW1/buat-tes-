<?php
// File: masyarakat/tanggapan_ajax.php
session_start();
require_once '../config/koneksi.php';

$id_pengaduan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_pengaduan <= 0) {
    echo "<div class='text-center text-muted'>ID pengaduan tidak valid.</div>";
    exit;
}

// Pastikan hanya masyarakat yang memiliki akses ke pengaduan ini
if ($_SESSION['level'] == 'masyarakat') {
    $nik = $_SESSION['user_id'];
    $check_sql = "SELECT * FROM pengaduan WHERE id_pengaduan = ? AND nik = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("is", $id_pengaduan, $nik);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows == 0) {
        echo "<div class='text-center text-muted'>Anda tidak memiliki akses ke pengaduan ini.</div>";
        exit;
    }
}

// Ambil tanggapan dengan JOIN ke tabel petugas untuk mendapatkan nama petugas
$sql = "SELECT t.*, p.nama_petugas 
        FROM tanggapan t
        LEFT JOIN petugas p ON t.id_petugas = p.id_petugas
        WHERE t.id_pengaduan = ? 
        ORDER BY t.created_at ASC"; // Urutkan berdasarkan created_at

try {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_pengaduan);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<div class='alert alert-info mb-3'>
                <i class='bi bi-info-circle'></i> 
                <strong>Ada " . $result->num_rows . " tanggapan dari petugas</strong>
              </div>";
        
        while ($row = $result->fetch_assoc()) {
            $nama_petugas = !empty($row['nama_petugas']) ? $row['nama_petugas'] : 'Admin';
            $tanggal_formatted = date('d F Y', strtotime($row['tgl_tanggapan']));
            
            echo '<div class="card mb-3 border-success">';
            echo '<div class="card-header bg-success text-white d-flex justify-content-between">';
            echo '<span><i class="bi bi-person-badge"></i> ' . htmlspecialchars($nama_petugas) . '</span>';
            echo '<span class="badge bg-light text-dark">' . $tanggal_formatted . '</span>';
            echo '</div>';
            echo '<div class="card-body">';
            echo '<p class="mb-0">' . nl2br(htmlspecialchars($row['tanggapan'])) . '</p>';
            echo '</div>';
            echo '</div>';
        }
        
        // Update last_viewed untuk menandai bahwa notifikasi telah dilihat
        if ($_SESSION['level'] == 'masyarakat') {
            $update_sql = "UPDATE pengaduan SET last_viewed = NOW() WHERE id_pengaduan = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("i", $id_pengaduan);
            $update_stmt->execute();
        }
              
    } else {
        echo "<div class='text-center text-muted py-4'>
                <i class='bi bi-chat-dots h1 mb-3 d-block'></i>
                <h5>Belum ada tanggapan</h5>
                <p>Pengaduan Anda sedang dalam proses review oleh petugas terkait.</p>
                <div class='spinner-border text-primary' role='status'>
                    <span class='visually-hidden'>Loading...</span>
                </div>
              </div>";
    }
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>
            <i class='bi bi-exclamation-triangle'></i> 
            Terjadi kesalahan: " . htmlspecialchars($e->getMessage()) . "
          </div>";
}
?>