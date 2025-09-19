<?php
session_start();
require_once 'koneksi.php';

if (isset($_POST['kirim'])) {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $level = $_POST['level'];
    
    // Validasi input
    if (empty($username)) {
        $_SESSION['error'] = "Username tidak boleh kosong";
        header('Location: ../index.php?page=login');
        exit();
    }
    
    if (empty($password)) {
        $_SESSION['error'] = "Password tidak boleh kosong";
        header('Location: ../index.php?page=login');
        exit();
    }
    
    if (empty($level) || !in_array($level, ['masyarakat', 'petugas'])) {
        $_SESSION['error'] = "Level login tidak valid";
        header('Location: ../index.php?page=login');
        exit();
    }
    
    try {
        if ($level == 'masyarakat') {
            // Login sebagai masyarakat - menggunakan MySQLi
            $username_escaped = mysqli_real_escape_string($conn, $username);
            $query = mysqli_query($conn, "SELECT nik, nama, username, password FROM masyarakat WHERE username = '$username_escaped'");
            
            if (mysqli_num_rows($query) > 0) {
                $user = mysqli_fetch_assoc($query);
                
                // Verifikasi password (cek apakah menggunakan MD5 atau password_hash)
                $password_valid = false;
                if (md5($password) === $user['password']) {
                    // Password menggunakan MD5 (sistem lama)
                    $password_valid = true;
                } elseif (password_verify($password, $user['password'])) {
                    // Password menggunakan password_hash (sistem baru)
                    $password_valid = true;
                }
                
                if ($password_valid) {
                    // Set session untuk masyarakat
                    $_SESSION['user_id'] = $user['nik'];
                    $_SESSION['nik'] = $user['nik'];
                    $_SESSION['nama'] = $user['nama'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['level'] = 'masyarakat';
                    $_SESSION['login'] = 'masyarakat';
                    
                    // Redirect ke dashboard masyarakat
                    header('Location: ../masyarakat/dashboard.php');
                    exit();
                } else {
                    $_SESSION['error'] = "Username atau password salah";
                }
            } else {
                $_SESSION['error'] = "Username tidak ditemukan";
            }
            
        } elseif ($level == 'petugas') {
            // Login sebagai petugas/admin - menggunakan MySQLi
            $username_escaped = mysqli_real_escape_string($conn, $username);
            $query = mysqli_query($conn, "SELECT id_petugas, nama_petugas, username, password, level FROM petugas WHERE username = '$username_escaped'");
            
            if (mysqli_num_rows($query) > 0) {
                $user = mysqli_fetch_assoc($query);
                
                // Verifikasi password (cek apakah menggunakan MD5 atau password_hash)
                $password_valid = false;
                if (md5($password) === $user['password']) {
                    // Password menggunakan MD5 (sistem lama)
                    $password_valid = true;
                } elseif (password_verify($password, $user['password'])) {
                    // Password menggunakan password_hash (sistem baru)
                    $password_valid = true;
                }
                
                if ($password_valid) {
                    // Set session untuk petugas/admin
                    $_SESSION['user_id'] = $user['id_petugas'];
                    $_SESSION['id_petugas'] = $user['id_petugas']; // Tambahkan ini untuk tanggapan
                    $_SESSION['nama'] = $user['nama_petugas'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['level'] = $user['level']; // 'admin' atau 'petugas'
                    $_SESSION['login'] = $user['level']; // Set sesuai level
                    
                    // Redirect ke admin panel
                    header('Location: ../admin/index.php');
                    exit();
                } else {
                    $_SESSION['error'] = "Username atau password salah";
                }
            } else {
                $_SESSION['error'] = "Username tidak ditemukan";
            }
        }
        
        // Jika sampai sini berarti login gagal
        header('Location: ../index.php?page=login');
        exit();
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Terjadi kesalahan sistem: " . $e->getMessage();
        error_log("Login error: " . $e->getMessage()); // Log error untuk debugging
        header('Location: ../index.php?page=login');
        exit();
    }
    
} else {
    // Jika tidak ada POST data, redirect ke halaman login
    header('Location: ../index.php?page=login');
    exit();
}
?>