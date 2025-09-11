    <?php
    session_start();
    require_once '../config/koneksi.php';

    if (!isset($_SESSION['login']) || $_SESSION['level'] != 'masyarakat') {
        header('Location: ../index.php?page=login');
        exit();
    }

    $id_pengaduan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id_pengaduan <= 0) {
        $_SESSION['error'] = "ID pengaduan tidak valid";
        header('Location: riwayat.php');
        exit();
    }

    try {
        $sql = "SELECT p.*, m.nama 
                FROM pengaduan p 
                JOIN masyarakat m ON p.nik = m.nik 
                WHERE p.id_pengaduan = ? AND p.nik = ?";
        $pengaduan = $db->fetch($sql, [$id_pengaduan, $_SESSION['user_id']]);

        if (!$pengaduan) {
            $_SESSION['error'] = "Pengaduan tidak ditemukan atau tidak punya akses";
            header('Location: riwayat.php');
            exit();
        }

        $fotoServer = __DIR__ . "/../uploads/pengaduan/" . basename($pengaduan['foto']);
        $fotoURL    = "/layanan-pengaduan-warga/uploads/pengaduan/" . basename($pengaduan['foto']);
    } catch (Exception $e) {
        $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
        header('Location: riwayat.php');
        exit();
    }

    function formatIsiLaporan($isi) {
        $parts = explode(' | ', $isi, 2);
        return count($parts) === 2 ? ['judul'=>$parts[0],'isi'=>$parts[1]] : ['judul'=>'Pengaduan','isi'=>$isi];
    }
    $laporan = formatIsiLaporan($pengaduan['isi_laporan']);
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pengaduan #<?= $id_pengaduan ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
    :root{--primary:#2d4a3e;--secondary:#87a96b;--success:#6b8e5a;--warning:#c9a876;--info:#7ba098;}
    body{background:#f8faf9;font-family:'Inter',sans-serif;}
    .card{border-radius:15px;border:none;box-shadow:0 4px 6px rgba(0,0,0,.1);}
    .status-timeline{position:relative;padding-left:30px;}
    .status-timeline::before{content:'';position:absolute;left:8px;top:0;bottom:0;width:2px;background:#dee2e6;}
    .timeline-item{position:relative;margin-bottom:20px;}
    .timeline-item::before{content:'';position:absolute;left:-24px;top:8px;width:12px;height:12px;border-radius:50%;background:var(--secondary);}
    .timeline-item.active::before{background:var(--success);box-shadow:0 0 0 4px rgba(107,142,90,.3);}
    .badge-proses{background:var(--info);}
    .badge-selesai{background:var(--success);}
    .badge-pending{background:var(--warning);}
    </style>
    </head>
    <body>
    <div class="container my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
    <h2>Detail Pengaduan #<?= $id_pengaduan ?></h2>
    <a href="riwayat.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    <div class="row g-4">
    <div class="col-lg-8">
    <div class="card">
    <div class="card-header bg-primary text-white">
    <h5><i class="bi bi-file-earmark-text"></i> <?= htmlspecialchars($laporan['judul']) ?></h5>
    </div>
    <div class="card-body">
    <p><strong>Tanggal:</strong> <?= date('d F Y ', strtotime($pengaduan['tgl_pengaduan'])) ?></p>
    <p><strong>Status:</strong>
    <?php
    $map=['0'=>['Menunggu Tanggapan','badge-pending','bi-clock'],'proses'=>['Sedang Diproses','badge-proses','bi-arrow-repeat'],'selesai'=>['Selesai Ditangani','badge-selesai','bi-check-circle']];
    [$text,$class,$icon]=$map[$pengaduan['status']]??['Unknown','bg-secondary','bi-question'];
    ?>
    <span class="badge <?= $class ?>"><i class="bi <?= $icon ?>"></i> <?= $text ?></span></p>
    <p><strong>Pelapor:</strong> <?= htmlspecialchars($pengaduan['nama']) ?> (<?= $pengaduan['nik'] ?>)</p>

    <div class="mb-3">
    <strong>Detail Laporan:</strong>
    <div class="p-3 bg-light rounded"><?= nl2br(htmlspecialchars($laporan['isi'])) ?></div>
    </div>

    <?php if(!empty($pengaduan['foto']) && is_file($fotoServer)): ?>
    <div>
    <strong>Foto Pendukung:</strong><br>
    <img src="<?= $fotoURL ?>" class="img-fluid rounded mt-2" style="max-width:300px;cursor:pointer" data-bs-toggle="modal" data-bs-target="#imageModal" alt="Foto Pengaduan">
    <small class="d-block text-muted">Klik untuk memperbesar</small>
    </div>
    <?php else: ?>
    <span class="text-muted">Foto tidak tersedia</span>
    <?php endif; ?>
    </div>
    </div>
    </div>

    <div class="col-lg-4">
    <div class="card mb-4">
    <div class="card-header"><i class="bi bi-clock-history"></i> Timeline</div>
    <div class="card-body status-timeline" id="timeline">
    <div class="timeline-item active">
    <div class="fw-bold">Diterima</div>
    <small class="text-muted" id="tgl-diterima"><?= date('d M Y, H:i', strtotime($pengaduan['created_at'])) ?></small>
    </div>
    <div class="timeline-item <?= $pengaduan['status']!='0'?'active':'' ?>">
    <div class="fw-bold">Diproses</div>
    <small class="text-muted" id="tgl-diproses"><?= $pengaduan['status']!='0'?date('d M Y, H:i', strtotime($pengaduan['updated_at'])):'Menunggu' ?></small>
    </div>
    <div class="timeline-item <?= $pengaduan['status']=='selesai'?'active':'' ?>">
    <div class="fw-bold">Selesai</div>
    <small class="text-muted" id="tgl-selesai"><?= $pengaduan['status']=='selesai'?date('d M Y, H:i', strtotime($pengaduan['updated_at'])):'Belum selesai' ?></small>
    </div>
    </div>
    </div>

    <div class="card">
    <div class="card-header bg-success text-white"><i class="bi bi-chat-dots"></i> Tanggapan</div>
    <div class="card-body" id="tanggapan-body">
    <div class="text-center text-muted py-4"><i class="bi bi-chat-dots h1 mb-2"></i> Memuat tanggapan...</div>
    </div>
    </div>
    </div>
    </div>
    </div>

    <?php if(!empty($pengaduan['foto']) && is_file($fotoServer)): ?>
    <div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
    <div class="modal-header">
    <h5 class="modal-title">Foto Pengaduan</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
    </div>
    <div class="modal-body text-center">
    <img src="<?= $fotoURL ?>" class="img-fluid rounded">
    </div>
    </div>
    </div>
    </div>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function loadTanggapan(){
        fetch('tanggapan_ajax.php?id=<?= $id_pengaduan ?>')
        .then(res=>res.text())
        .then(html=>document.getElementById('tanggapan-body').innerHTML=html)
        .catch(err=>console.error(err));
    }

    function loadTimeline(){
        fetch('timeline_ajax.php?id=<?= $id_pengaduan ?>')
        .then(res=>res.json())
        .then(data=>{
            document.getElementById('tgl-diterima').textContent=data.created_at;
            document.getElementById('tgl-diproses').textContent=data.proses_at;
            document.getElementById('tgl-selesai').textContent=data.selesai_at;
        })
        .catch(err=>console.error(err));
    }

    // Auto-refresh setiap 10 detik
    setInterval(()=>{
        loadTanggapan();
        loadTimeline();
    },10000);

    // Jalankan pertama kali
    loadTanggapan();
    loadTimeline();

    // Fungsi untuk update waktu relatif
    function updateRelativeTime() {
        document.querySelectorAll('.relative-time').forEach(el => {
            const time = new Date(el.getAttribute('data-time'));
            const now = new Date();
            const diff = Math.floor((now - time) / 1000);
            
            let display = '';
            if (diff < 60) display = `${diff} detik yang lalu`;
            else if (diff < 3600) display = `${Math.floor(diff/60)} menit yang lalu`;
            else if (diff < 86400) display = `${Math.floor(diff/3600)} jam yang lalu`;
            else display = `${Math.floor(diff/86400)} hari yang lalu`;
            
            el.textContent = display;
        });
    }

    // Jalankan dan set interval untuk waktu relatif
    setInterval(updateRelativeTime, 60000);
    updateRelativeTime();
    </script>
    </body>
    </html>
