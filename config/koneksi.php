<?php
// config/koneksi.php - Perbaikan konfigurasi database
if (!class_exists('Database')) {
    class Database {
        private string $host = 'localhost';
        private string $username = 'root';
        private string $password = '';
        private string $database = 'db_apem2';
        private ?PDO $connection = null;

        public function __construct() {
            $this->connect();
        }

        private function connect(): void {
            try {
                $this->connection = new PDO(
                    "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4",
                    $this->username,
                    $this->password,
                    [
                        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES   => false
                    ]
                );
            } catch (PDOException $e) {
                exit("Koneksi database gagal: " . $e->getMessage());
            }
        }

        public function fetch(string $sql, array $params = []): ?array {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch() ?: null;
        }

        public function fetchAll(string $sql, array $params = []): array {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        }

        public function execute(string $sql, array $params = []): bool {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        }

        public function getLatest(string $table, string $timeColumn = "tgl_pengaduan", int $limit = 5): array {
            $sql = "SELECT * FROM {$table} ORDER BY {$timeColumn} DESC LIMIT :limit";
            $stmt = $this->connection->prepare($sql);
            $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll();
        }
    }
}

// Koneksi MySQLi untuk backward compatibility
if (!isset($conn)) {
    $host = 'localhost';
    $username = 'root';
    $password = '';
    $database = 'db_apem2';

    $conn = new mysqli($host, $username, $password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Set charset
    $conn->set_charset("utf8mb4");
}

// Instance PDO global
if (!isset($db)) {
    $db = new Database();
}
?>