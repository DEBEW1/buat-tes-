<?php
// logout.php - File ini harus disimpan di root directory project (layanan-pengaduan-warga/)
session_start();

// Simpan informasi logout untuk notifikasi
$logout_message = "";
$user_info = "";

if (isset($_SESSION['login'])) {
    $user_type = $_SESSION['login'];
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : (isset($_SESSION['nama']) ? $_SESSION['nama'] : 'User');
    $user_info = ucfirst($user_type) . " " . $username;
    $logout_message = "Logout berhasil! Terima kasih " . $user_info . " telah menggunakan sistem kami.";
}

// Hapus semua session data
$_SESSION = array();

// Hapus cookie session jika ada
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time() - 3600, '/');
}

// Hapus custom cookies jika ada
$cookies_to_remove = ['remember_me', 'user_login', 'admin_login', 'petugas_login'];
foreach ($cookies_to_remove as $cookie) {
    if (isset($_COOKIE[$cookie])) {
        setcookie($cookie, '', time() - 3600, '/');
    }
}

// Destroy session
session_destroy();

// Redirect ke halaman utama dengan pesan sukses
if (!empty($logout_message)) {
    $encoded_message = urlencode($logout_message);
    header("Location: index.php?logout=success&message=" . $encoded_message);
} else {
    header("Location: index.php?logout=success&message=" . urlencode("Logout berhasil! Terima kasih telah menggunakan sistem kami."));
}

exit();
?>