<?php
// config/aksi_registrasi.php
session_start();
require_once 'koneksi.php';

if (isset($_POST['kirim'])) {
    $nik = trim($_POST['nik']);
    $nama_lengkap = trim($_POST['nama_lengkap']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $telp = trim($_POST['telp']);
    
    // Simpan data untuk ditampilkan lagi jika error
    $_SESSION['old'] = [
        'nik' => $nik,
        'nama_lengkap' => $nama_lengkap,
        'username' => $username,
        'telp' => $telp
    ];
    
    // Validasi input
    $errors = [];
    
    // Validasi NIK
    if (empty($nik)) {
        $errors[] = "NIK tidak boleh kosong";
    } elseif (!is_numeric($nik)) {
        $errors[] = "NIK harus berupa angka";
    } elseif (strlen($nik) != 16) {
        $errors[] = "NIK harus tepat 16 digit";
    }
    
    // Validasi nama lengkap
    if (empty($nama_lengkap)) {
        $errors[] = "Nama lengkap tidak boleh kosong";
    } elseif (strlen($nama_lengkap) > 35) {
        $errors[] = "Nama lengkap maksimal 35 karakter";
    } elseif (!preg_match('/^[a-zA-Z\s\'\.]+$/', $nama_lengkap)) {
        $errors[] = "Nama lengkap hanya boleh mengandung huruf, spasi, titik, dan apostrof";
    }
    
    // Validasi username
    if (empty($username)) {
        $errors[] = "Username tidak boleh kosong";
    } elseif (strlen($username) < 3) {
        $errors[] = "Username minimal 3 karakter";
    } elseif (strlen($username) > 25) {
        $errors[] = "Username maksimal 25 karakter";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = "Username hanya boleh mengandung huruf, angka, dan underscore";
    }
    
    // Validasi password
    if (empty($password)) {
        $errors[] = "Password tidak boleh kosong";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter";
    }
    
    // Validasi nomor telepon
    if (empty($telp)) {
        $errors[] = "Nomor telepon tidak boleh kosong";
    } elseif (!is_numeric($telp)) {
        $errors[] = "Nomor telepon harus berupa angka";
    } elseif (strlen($telp) < 10 || strlen($telp) > 13) {
        $errors[] = "Nomor telepon harus antara 10-13 digit";
    }
    
    // Jika ada error, redirect kembali dengan pesan error
    if (!empty($errors)) {
        $_SESSION['error'] = implode("<br>", $errors);
        header('Location: ../index.php?page=registrasi');
        exit();
    }
    
    try {
        // Mulai transaksi database
        $db->beginTransaction();
        
        // Cek apakah NIK sudah terdaftar
        $check_nik = $db->query("SELECT nik FROM masyarakat WHERE nik = ?", [$nik]);
        if ($check_nik->rowCount() > 0) {
            $_SESSION['error'] = "NIK sudah terdaftar dalam sistem";
            header('Location: ../index.php?page=registrasi');
            exit();
        }
        
        // Cek apakah username sudah terdaftar (masyarakat)
        $check_username_masyarakat = $db->query("SELECT username FROM masyarakat WHERE username = ?", [$username]);
        if ($check_username_masyarakat->rowCount() > 0) {
            $_SESSION['error'] = "Username sudah digunakan";
            header('Location: ../index.php?page=registrasi');
            exit();
        }
        
        // Cek apakah username sudah terdaftar (petugas)
        $check_username_petugas = $db->query("SELECT username FROM petugas WHERE username = ?", [$username]);
        if ($check_username_petugas->rowCount() > 0) {
            $_SESSION['error'] = "Username sudah digunakan";
            header('Location: ../index.php?page=registrasi');
            exit();
        }
        
        // Hash password dengan algoritma yang lebih kuat
        $hashed_password = password_hash($password, PASSWORD_DEFAULT, ['cost' => 12]);
        
        // Insert data ke database
        $sql = "INSERT INTO masyarakat (nik, nama, username, password, telp) VALUES (?, ?, ?, ?, ?)";
        $result = $db->execute($sql, [$nik, $nama_lengkap, $username, $hashed_password, $telp]);
        
        if ($result) {
            // Commit transaksi
            $db->commit();
            
            // Hapus data old
            unset($_SESSION['old']);
            
            $_SESSION['success'] = "Registrasi berhasil! Silakan login dengan akun Anda.";
            header('Location: ../index.php?page=login');
        } else {
            // Rollback transaksi
            $db->rollback();
            $_SESSION['error'] = "Terjadi kesalahan saat menyimpan data. Silakan coba lagi.";
            header('Location: ../index.php?page=registrasi');
        }
        
    } catch (PDOException $e) {
        // Rollback transaksi jika ada error
        $db->rollback();
        
        // Log error untuk debugging (dalam production, jangan tampilkan error detail ke user)
        error_log("Registration Error: " . $e->getMessage());
        
        // Cek jenis error
        if ($e->getCode() == 23000) { // Duplicate entry error
            if (strpos($e->getMessage(), 'nik') !== false) {
                $_SESSION['error'] = "NIK sudah terdaftar dalam sistem";
            } elseif (strpos($e->getMessage(), 'username') !== false) {
                $_SESSION['error'] = "Username sudah digunakan";
            } else {
                $_SESSION['error'] = "Data yang dimasukkan sudah ada dalam sistem";
            }
        } else {
            $_SESSION['error'] = "Terjadi kesalahan sistem. Silakan coba lagi.";
        }
        
        header('Location: ../index.php?page=registrasi');
    } catch (Exception $e) {
        // Rollback transaksi
        $db->rollback();
        
        // Log error
        error_log("Registration Error: " . $e->getMessage());
        
        $_SESSION['error'] = "Terjadi kesalahan sistem. Silakan coba lagi.";
        header('Location: ../index.php?page=registrasi');
    }
    
    exit();
} else {
    // Jika tidak ada POST data, redirect ke halaman registrasi
    $_SESSION['error'] = "Akses tidak valid";
    header('Location: ../index.php?page=registrasi');
    exit();
}
?>