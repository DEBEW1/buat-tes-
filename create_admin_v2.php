<?php
// create_admin_v2.php - Script pembuatan admin yang lebih robust
require_once 'config/koneksi.php';

// Fungsi untuk membuat user
function createUser($db, $nama, $username, $password, $telp, $level) {
    try {
        // Cek apakah username sudah ada
        $check_sql = "SELECT username FROM petugas WHERE username = ?";
        $check_stmt = $db->query($check_sql, [$username]);
        
        if ($check_stmt->rowCount() > 0) {
            return "User '$username' sudah ada";
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        echo "<p>Password '$password' di-hash menjadi: " . substr($hashed_password, 0, 30) . "...</p>";
        
        // Insert user
        $insert_sql = "INSERT INTO petugas (nama_petugas, username, password, telp, level) VALUES (?, ?, ?, ?, ?)";
        $result = $db->execute($insert_sql, [$nama, $username, $hashed_password, $telp, $level]);
        
        if ($result) {
            // Verifikasi data tersimpan
            $verify_sql = "SELECT * FROM petugas WHERE username = ?";
            $verify_stmt = $db->query($verify_sql, [$username]);
            $saved_user = $verify_stmt->fetch();
            
            if ($saved_user) {
                return "SUCCESS: User '$username' berhasil dibuat (ID: {$saved_user['id_petugas']})";
            } else {
                return "ERROR: User sepertinya tersimpan tapi tidak bisa ditemukan";
            }
        } else {
            return "ERROR: Gagal menyimpan user '$username'";
        }
        
    } catch (Exception $e) {
        return "ERROR: " . $e->getMessage();
    }
}

echo "<h2>Membuat User Admin dan Petugas - Versi 2</h2>";

// Hapus user lama jika ada (opsional)
if (isset($_GET['reset']) && $_GET['reset'] == '1') {
    try {
        $db->execute("DELETE FROM petugas WHERE username IN ('admin', 'petugas1')");
        echo "<p style='color: orange;'>🗑️ User lama dihapus</p>";
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error menghapus: " . $e->getMessage() . "</p>";
    }
}

// Data user yang akan dibuat
$users = [
    [
        'nama' => 'Super Administrator',
        'username' => 'admin',
        'password' => 'admin123',
        'telp' => '081234567890',
        'level' => 'admin'
    ],
    [
        'nama' => 'Petugas Layanan',
        'username' => 'petugas1',
        'password' => 'petugas123',
        'telp' => '081234567891',
        'level' => 'petugas'
    ]
];

// Buat setiap user
foreach ($users as $user) {
    echo "<hr>";
    echo "<h3>Membuat {$user['level']}: {$user['username']}</h3>";
    
    $result = createUser(
        $db, 
        $user['nama'], 
        $user['username'], 
        $user['password'], 
        $user['telp'], 
        $user['level']
    );
    
    if (strpos($result, 'SUCCESS') === 0) {
        echo "<p style='color: green;'>✅ $result</p>";
        echo "<div style='background: #f0f0f0; padding: 10px; border-left: 4px solid green;'>";
        echo "<strong>Detail Login:</strong><br>";
        echo "Username: <strong>{$user['username']}</strong><br>";
        echo "Password: <strong>{$user['password']}</strong><br>";
        echo "Level: <strong>{$user['level']}</strong><br>";
        echo "</div>";
    } else {
        echo "<p style='color: red;'>❌ $result</p>";
    }
}

echo "<hr>";
echo "<h3>🎯 Langkah Selanjutnya:</h3>";
echo "<ol>";
echo "<li>Cek database dengan: <a href='check_users.php'>check_users.php</a></li>";
echo "<li>Coba login dengan username: <strong>admin</strong>, password: <strong>admin123</strong></li>";
echo "<li>Pilih level: <strong>Petugas</strong> di form login</li>";
echo "<li>Jika masih error, cek file aksi_login.php</li>";
echo "</ol>";

echo "<p><a href='?reset=1' style='color: red;'>🗑️ Reset (Hapus dan Buat Ulang User)</a></p>";
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
p { margin: 5px 0; }
</style>