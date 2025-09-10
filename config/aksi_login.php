<?php
// config/aksi_login.php
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
            // Login sebagai masyarakat
            $sql = "SELECT nik, nama, username, password FROM masyarakat WHERE username = ?";
            $stmt = $db->query($sql, [$username]);
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch();
                
                // Verifikasi password
                if (password_verify($password, $user['password'])) {
                    // Set session untuk masyarakat
                    $_SESSION['user_id'] = $user['nik'];
                    $_SESSION['nama'] = $user['nama'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['level'] = 'masyarakat';
                    $_SESSION['login'] = true;
                    
                    // Redirect ke dashboard masyarakat
                    header('Location: ../masyarakat/dashboard.php');
                    exit();
                } else {
                    $_SESSION['error'] = "Username atau password salah";
                }
            } else {
                $_SESSION['error'] = "Username atau password salah";
            }
            
        } elseif ($level == 'petugas') {
            // Login sebagai petugas/admin
            $sql = "SELECT id_petugas, nama_petugas, username, password, level FROM petugas WHERE username = ?";
            $stmt = $db->query($sql, [$username]);
            
            if ($stmt->rowCount() > 0) {
                $user = $stmt->fetch();
                
                // Verifikasi password
                if (password_verify($password, $user['password'])) {
                    // Set session untuk petugas/admin
                    $_SESSION['user_id'] = $user['id_petugas'];
                    $_SESSION['nama'] = $user['nama_petugas'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['level'] = $user['level']; // 'admin' atau 'petugas'
                    $_SESSION['login'] = true;
                    
                    // Redirect berdasarkan level
                    if ($user['level'] == 'admin') {
                        header('Location: ../admin/dashboard.php');
                    } else {
                        header('Location: ../petugas/dashboard.php');
                    }
                    exit();
                } else {
                    $_SESSION['error'] = "Username atau password salah";
                }
            } else {
                $_SESSION['error'] = "Username atau password salah";
            }
        }
        
        // Jika sampai sini berarti login gagal
        header('Location: ../index.php?page=login');
        exit();
        
    } catch (Exception $e) {
        $_SESSION['error'] = "Terjadi kesalahan sistem: " . $e->getMessage();
        header('Location: ../index.php?page=login');
        exit();
    }
    
} else {
    // Jika tidak ada POST data, redirect ke halaman login
    header('Location: ../index.php?page=login');
    exit();
}
?>