<?php
// config/koneksi.php
class Database {
    private $host = 'localhost';
    private $username = 'root';
    private $password = '';
    private $database = 'db_apem';
    private $connection;
    
    public function __construct() {
        try {
            $this->connection = new PDO(
                "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Koneksi database gagal: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    // Method untuk mengeksekusi query SELECT
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            throw new Exception("Query error: " . $e->getMessage());
        }
    }
    
    // Method untuk mengeksekusi INSERT, UPDATE, DELETE
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new Exception("Execute error: " . $e->getMessage());
        }
    }
    
    // Method untuk mendapatkan ID terakhir yang diinsert
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
    
    // Method untuk memulai transaksi
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }
    
    // Method untuk commit transaksi
    public function commit() {
        return $this->connection->commit();
    }
    
    // Method untuk rollback transaksi
    public function rollback() {
        return $this->connection->rollback();
    }
}

// Membuat instance database global
$db = new Database();
?>