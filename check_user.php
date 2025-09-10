<?php
// check_users.php - Script untuk mengecek user yang ada di database
require_once 'config/koneksi.php';

try {
    echo "<h2>Daftar User Admin/Petugas di Database:</h2>";
    
    // Ambil semua data petugas
    $sql = "SELECT id_petugas, nama_petugas, username, level, created_at FROM petugas ORDER BY id_petugas";
    $result = $db->query($sql);
    
    if ($result->rowCount() > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background-color: #f2f2f2;'>";
        echo "<th>ID</th><th>Nama</th><th>Username</th><th>Level</th><th>Dibuat</th>";
        echo "</tr>";
        
        while ($user = $result->fetch()) {
            echo "<tr>";
            echo "<td>{$user['id_petugas']}</td>";
            echo "<td>{$user['nama_petugas']}</td>";
            echo "<td>{$user['username']}</td>";
            echo "<td>{$user['level']}</td>";
            echo "<td>{$user['created_at']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p style='color: red;'>❌ Tidak ada user admin/petugas di database!</p>";
        echo "<p>Silakan jalankan create_admin.php terlebih dahulu.</p>";
    }
    
    // Cek juga total record
    $count_sql = "SELECT COUNT(*) as total FROM petugas";
    $count_result = $db->query($count_sql);
    $count = $count_result->fetch();
    
    echo "<p><strong>Total user admin/petugas: {$count['total']}</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>

<hr>
<h3>Test Login Manual:</h3>
<form method="post" action="">
    <p>
        <label>Username:</label><br>
        <input type="text" name="test_username" value="admin" required>
    </p>
    <p>
        <label>Password:</label><br>
        <input type="text" name="test_password" value="admin123" required>
    </p>
    <p>
        <input type="submit" name="test_login" value="Test Login">
    </p>
</form>

<?php
if (isset($_POST['test_login'])) {
    $test_username = $_POST['test_username'];
    $test_password = $_POST['test_password'];
    
    try {
        echo "<h4>Hasil Test Login:</h4>";
        
        $sql = "SELECT id_petugas, nama_petugas, username, password, level FROM petugas WHERE username = ?";
        $stmt = $db->query($sql, [$test_username]);
        
        if ($stmt->rowCount() > 0) {
            $user = $stmt->fetch();
            echo "<p>✅ User ditemukan:</p>";
            echo "<ul>";
            echo "<li>ID: {$user['id_petugas']}</li>";
            echo "<li>Nama: {$user['nama_petugas']}</li>";
            echo "<li>Username: {$user['username']}</li>";
            echo "<li>Level: {$user['level']}</li>";
            echo "<li>Password Hash: " . substr($user['password'], 0, 20) . "...</li>";
            echo "</ul>";
            
            // Test password verify
            if (password_verify($test_password, $user['password'])) {
                echo "<p style='color: green;'>✅ <strong>Password COCOK!</strong></p>";
                echo "<p>Login seharusnya berhasil. Cek kode aksi_login.php</p>";
            } else {
                echo "<p style='color: red;'>❌ <strong>Password TIDAK COCOK!</strong></p>";
                echo "<p>Password di database berbeda dengan yang Anda masukkan.</p>";
            }
        } else {
            echo "<p style='color: red;'>❌ User tidak ditemukan di database</p>";
        }
        
    } catch (Exception $e) {
        echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
    }
}
?>