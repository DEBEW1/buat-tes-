<?php
// masyarakat/proses_pengaduan.php
session_start();
require_once '../config/koneksi.php';

// Cek apakah user sudah login dan levelnya masyarakat
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'masyarakat') {
    header('Location: ../index.php?page=login');
    exit();
}

if (isset($_POST['submit'])) {
    $tgl_pengaduan = $_POST['tgl_pengaduan'];
    $nik = $_POST['nik'];
    $judul_laporan = trim($_POST['judul_laporan']);
    $isi_laporan = trim($_POST['isi_laporan']);
    
    // Validasi input
    $errors = [];
    
    // Validasi tanggal
    if (empty($tgl_pengaduan)) {
        $errors[] = "Tanggal pengaduan tidak boleh kosong";
    }
    
    // Validasi NIK
    if (empty($nik) || $nik != $_SESSION['user_id']) {
        $errors[] = "NIK tidak valid";
    }
    
    // Validasi judul laporan
    if (empty($judul_laporan)) {
        $errors[] = "Judul laporan tidak boleh kosong";
    } elseif (strlen($judul_laporan) < 10) {
        $errors[] = "Judul laporan minimal 10 karakter";
    } elseif (strlen($judul_laporan) > 100) {
        $errors[] = "Judul laporan maksimal 100 karakter";
    }
    
    // Validasi isi laporan
    if (empty($isi_laporan)) {
        $errors[] = "Detail laporan tidak boleh kosong";
    } elseif (strlen($isi_laporan) < 20) {
        $errors[] = "Detail laporan minimal 20 karakter";
    } elseif (strlen($isi_laporan) > 1000) {
        $errors[] = "Detail laporan maksimal 1000 karakter";
    }
    
    // Gabungkan judul dan isi laporan
    $full_laporan = $judul_laporan . " | " . $isi_laporan;
    
    // Handle file upload
    $foto_name = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $foto = $_FILES['foto'];
        
        // Validasi file
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($foto['type'], $allowed_types)) {
            $errors[] = "Format file tidak didukung. Gunakan JPG, JPEG, atau PNG";
        }
        
        if ($foto['size'] > $max_size) {
            $errors[] = "Ukuran file terlalu besar. Maksimal 2MB";
        }
        
        // Jika tidak ada error, upload file
        if (empty($errors)) {
            // Buat direktori upload jika belum ada
            $upload_dir = '../uploads/pengaduan/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Generate nama file unik
            $file_extension = pathinfo($foto['name'], PATHINFO_EXTENSION);
            $foto_name = $nik . '_' . time() . '_' . uniqid() . '.' . $file_extension;
            $foto_path = $upload_dir . $foto_name;
            
            // Upload file
            if (!move_uploaded_file($foto['tmp_name'], $foto_path)) {
                $errors[] = "Gagal mengupload file";
            }
        }
    } elseif (isset($_FILES['foto']) && $_FILES['foto']['error'] !== UPLOAD_ERR_NO_FILE) {
        // Handle upload errors
        switch ($_FILES['foto']['error']) {
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                $errors[] = "Ukuran file terlalu besar";
                break;
            case UPLOAD_ERR_PARTIAL:
                $errors[] = "File tidak terupload dengan lengkap";
                break;
            default:
                $errors[] = "Terjadi kesalahan saat upload file";
                break;
        }
    }
    
    // Jika ada error, redirect kembali
    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
        header('Location: pengaduan.php');
        exit();
    }
    
    try {
        // Mulai transaksi
        $db->beginTransaction();
        
        // Insert pengaduan ke database
        $sql = "INSERT INTO pengaduan (tgl_pengaduan, nik, isi_laporan, foto, status) VALUES (?, ?, ?, ?, '0')";
        $result = $db->execute($sql, [$tgl_pengaduan, $nik, $full_laporan, $foto_name]);
        
        if ($result) {
            // Commit transaksi
            $db->commit();
            
            $_SESSION['success'] = "Pengaduan berhasil dikirim! Kami akan menanggapi dalam waktu 3x24 jam.";
            header('Location: riwayat.php');
        } else {
            // Rollback transaksi
            $db->rollback();
            
            // Hapus file yang sudah diupload jika ada error
            if ($foto_name && file_exists('../uploads/pengaduan/' . $foto_name)) {
                unlink('../uploads/pengaduan/' . $foto_name);
            }
            
            $_SESSION['error'] = "Terjadi kesalahan saat menyimpan pengaduan";
            header('Location: pengaduan.php');
        }
        
    } catch (Exception $e) {
        // Rollback transaksi
        $db->rollback();
        
        // Hapus file yang sudah diupload jika ada error
        if ($foto_name && file_exists('../uploads/pengaduan/' . $foto_name)) {
            unlink('../uploads/pengaduan/' . $foto_name);
        }
        
        // Log error
        error_log("Pengaduan Error: " . $e->getMessage());
        
        $_SESSION['error'] = "Terjadi kesalahan sistem. Silakan coba lagi.";
        header('Location: pengaduan.php');
    }
    
    exit();
    
} else {
    // Jika tidak ada POST data
    $_SESSION['error'] = "Akses tidak valid";
    header('Location: pengaduan.php');
    exit();
}
?>