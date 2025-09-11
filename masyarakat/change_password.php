<?php
// masyarakat/change_password.php
session_start();
require_once '../config/koneksi.php';

// Cek apakah user sudah login dan levelnya masyarakat
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'masyarakat') {
    header('Location: ../index.php?page=login');
    exit();
}

// Cek apakah request method POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header('Location: profile.php');
    exit();
}

try {
    // Ambil data dari form
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $nik = $_SESSION['user_id'];
    
    // Validasi input
    $errors = [];
    
    // Validasi password saat ini tidak kosong
    if (empty($current_password)) {
        $errors[] = "Password saat ini tidak boleh kosong";
    }
    
    // Validasi password baru
    if (empty($new_password)) {
        $errors[] = "Password baru tidak boleh kosong";
    } elseif (strlen($new_password) < 6) {
        $errors[] = "Password baru minimal 6 karakter";
    }
    
    // Validasi konfirmasi password
    if (empty($confirm_password)) {
        $errors[] = "Konfirmasi password tidak boleh kosong";
    } elseif ($new_password !== $confirm_password) {
        $errors[] = "Konfirmasi password tidak cocok";
    }
    
    // Jika ada error, redirect dengan pesan error
    if (!empty($errors)) {
        $_SESSION['error'] = implode(', ', $errors);
        header('Location: profile.php');
        exit();
    }
    
    // Ambil password saat ini dari database
    $get_password_sql = "SELECT password FROM masyarakat WHERE nik = ?";
    $get_stmt = $db->query($get_password_sql, [$nik]);
    $user = $get_stmt->fetch();
    
    if (!$user) {
        $_SESSION['error'] = "User tidak ditemukan";
        header('Location: profile.php');
        exit();
    }
    
    // Verifikasi password saat ini
    // Cek apakah password sudah di-hash dengan password_hash()
    $password_verified = false;
    
    if (password_verify($current_password, $user['password'])) {
        // Password menggunakan password_hash()
        $password_verified = true;
        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
    } elseif (md5($current_password) === $user['password']) {
        // Password menggunakan MD5
        $password_verified = true;
        $hashed_new_password = md5($new_password);
    } elseif ($current_password === $user['password']) {
        // Password plain text (tidak direkomendasikan)
        $password_verified = true;
        $hashed_new_password = password_hash($new_password, PASSWORD_DEFAULT);
    }
    
    if (!$password_verified) {
        $_SESSION['error'] = "Password saat ini tidak benar";
        header('Location: profile.php');
        exit();
    }
    
    // Update password di database
    $update_password_sql = "UPDATE masyarakat SET password = ? WHERE nik = ?";
    $update_stmt = $db->query($update_password_sql, [$hashed_new_password, $nik]);
    
    if ($update_stmt) {
        $_SESSION['success'] = "Password berhasil diubah";
    } else {
        $_SESSION['error'] = "Gagal mengubah password";
    }
    
} catch (Exception $e) {
    $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
}

// Redirect kembali ke halaman profile
header('Location: profile.php');
exit();
?>