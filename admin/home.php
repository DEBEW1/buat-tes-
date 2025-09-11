<?php
// =======================
// Konfigurasi & Koneksi
// =======================
class Database {
    private string $host = 'localhost';
    private string $username = 'root';
    private string $password = '';
    private string $database = 'db_apem';
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

    public function getLatest(string $table, string $timeColumn = "tgl_pengaduan", int $limit = 5): array {
        $sql = "SELECT * FROM {$table} ORDER BY {$timeColumn} DESC LIMIT :limit";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindValue(":limit", $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}

// =======================
// Ambil Data dari DB
// =======================
$db = new Database();

$jml_masyarakat = $db->fetch("SELECT COUNT(*) AS total FROM masyarakat")['total'] ?? 0;
$jml_pengaduan  = $db->fetch("SELECT COUNT(*) AS total FROM pengaduan")['total'] ?? 0;
$jml_tanggapan  = $db->fetch("SELECT COUNT(*) AS total FROM tanggapan")['total'] ?? 0;
$jml_petugas    = $db->fetch("SELECT COUNT(*) AS total FROM petugas")['total'] ?? 0;

$latest_pengaduan = $db->getLatest("pengaduan", "tgl_pengaduan", 5);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Sistem Pengaduan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; }
        .card-stat { transition: transform .2s; cursor: pointer; }
        .card-stat:hover { transform: translateY(-5px); }
        .inbox-item { transition: background .2s; }
        .inbox-item:hover { background: #f1f1f1; }
    </style>
</head>
<body>

<div class="container py-5">
    <!-- Header -->
    <div class="text-center mb-5">
        <h1 class="fw-bold"><i class="bi bi-building-check"></i> Sistem Pengaduan Warga</h1>
        <p class="text-muted">Transparan • Cepat • Interaktif</p>
    </div>

    <!-- Statistik -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="card text-bg-primary shadow-sm card-stat">
                <div class="card-body text-center">
                    <i class="bi bi-people-fill fs-1"></i>
                    <h3 class="mt-2"><?= $jml_masyarakat ?></h3>
                    <p class="mb-0">Masyarakat</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-success shadow-sm card-stat">
                <div class="card-body text-center">
                    <i class="bi bi-chat-dots-fill fs-1"></i>
                    <h3 class="mt-2"><?= $jml_pengaduan ?></h3>
                    <p class="mb-0">Pengaduan</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-warning shadow-sm card-stat">
                <div class="card-body text-center">
                    <i class="bi bi-reply-all-fill fs-1"></i>
                    <h3 class="mt-2"><?= $jml_tanggapan ?></h3>
                    <p class="mb-0">Tanggapan</p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-bg-danger shadow-sm card-stat">
                <div class="card-body text-center">
                    <i class="bi bi-shield-lock-fill fs-1"></i>
                    <h3 class="mt-2"><?= $jml_petugas ?></h3>
                    <p class="mb-0">Petugas</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Kotak Masuk -->
    <div class="card shadow-sm">
        <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
            <span><i class="bi bi-inbox-fill"></i> Kotak Masuk Pengaduan Terbaru</span>
            <span class="badge bg-light text-dark"><?= count($latest_pengaduan) ?> Baru</span>
        </div>
        <div class="card-body">
            <?php if (empty($latest_pengaduan)): ?>
                <p class="text-muted">Belum ada pengaduan baru.</p>
            <?php else: ?>
                <div class="list-group">
                    <?php foreach ($latest_pengaduan as $p): ?>
                        <a href="#" class="list-group-item list-group-item-action inbox-item">
                            <div class="d-flex w-100 justify-content-between">
                                <h6 class="mb-1"><i class="bi bi-person-badge"></i> NIK: <?= htmlspecialchars($p['nik']) ?></h6>
                                <small class="text-muted"><?= $p['tgl_pengaduan'] ?></small>
                            </div>
                            <p class="mb-1"><?= htmlspecialchars(substr($p['isi_laporan'], 0, 80)) ?>...</p>
                            <span class="badge 
                                <?= $p['status'] === 'selesai' ? 'bg-success' : 
                                    ($p['status'] === 'proses' ? 'bg-warning text-dark' : 'bg-secondary') ?>">
                                <?= ucfirst($p['status']) ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

</body>
</html>
