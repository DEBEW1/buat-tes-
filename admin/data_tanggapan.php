<?php
// Include koneksi database yang sudah diperbaiki
require_once "../config/koneksi.php";

// Ambil data tanggapan join pengaduan
$sql = "SELECT 
            t.id_tanggapan, 
            t.tgl_tanggapan, 
            t.tanggapan, 
            p.id_pengaduan, 
            p.nik, 
            p.judul_pengaduan,
            p.isi_laporan, 
            p.status,
            pt.nama_petugas,
            m.nama as nama_masyarakat
        FROM tanggapan t
        INNER JOIN pengaduan p ON t.id_pengaduan = p.id_pengaduan
        INNER JOIN petugas pt ON t.id_petugas = pt.id_petugas
        INNER JOIN masyarakat m ON p.nik = m.nik
        ORDER BY t.tgl_tanggapan DESC";

$query = mysqli_query($conn, $sql);
if (!$query) {
    echo "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="card mb-4 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-warning text-dark">
                <h5 class="mb-0"><i class="bi bi-reply-all"></i> Data Tanggapan</h5>
                <a href="export_tanggapan.php" class="btn btn-success btn-sm">
                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-warning text-center">
                            <tr>
                                <th>No</th>
                                <th>Tanggal</th>
                                <th>Petugas</th>
                                <th>NIK</th>
                                <th>Nama Masyarakat</th>
                                <th>Judul Laporan</th>
                                <th>Tanggapan</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($query && mysqli_num_rows($query) > 0): ?>
                                <?php $no = 1; while ($data = mysqli_fetch_assoc($query)): ?>
                                    <tr>
                                        <td class="text-center"><?= $no++; ?></td>
                                        <td class="text-center"><?= date('d/m/Y H:i', strtotime($data['tgl_tanggapan'])); ?></td>
                                        <td><?= htmlspecialchars($data['nama_petugas']); ?></td>
                                        <td class="text-center"><?= htmlspecialchars($data['nik']); ?></td>
                                        <td><?= htmlspecialchars($data['nama_masyarakat']); ?></td>
                                        <td><?= htmlspecialchars($data['judul_pengaduan']); ?></td>
                                        <td><?= htmlspecialchars(substr($data['tanggapan'], 0, 100) . (strlen($data['tanggapan']) > 100 ? '...' : '')); ?></td>
                                        <td class="text-center">
                                            <?php 
                                            switch ($data['status']) {
                                                case "selesai":
                                                    echo "<span class='badge bg-success'>Selesai</span>";
                                                    break;
                                                case "proses":
                                                    echo "<span class='badge bg-warning text-dark'>Proses</span>";
                                                    break;
                                                case "tolak":
                                                    echo "<span class='badge bg-danger'>Ditolak</span>";
                                                    break;
                                                default:
                                                    echo "<span class='badge bg-secondary'>Menunggu</span>";
                                            }
                                            ?>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detail<?= $data['id_tanggapan'] ?>">
                                                <i class="bi bi-eye"></i> Detail
                                            </button>
                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#hapus<?= $data['id_tanggapan'] ?>">
                                                <i class="bi bi-trash"></i> Hapus
                                            </button>

                                            <!-- Modal Detail -->
                                            <div class="modal fade" id="detail<?= $data['id_tanggapan'] ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-info text-white">
                                                            <h5 class="modal-title">Detail Tanggapan</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row mb-3">
                                                                <div class="col-md-6">
                                                                    <div class="card bg-light">
                                                                        <div class="card-body">
                                                                            <h6 class="card-title text-info"><i class="bi bi-person-badge"></i> Info Petugas</h6>
                                                                            <p class="mb-1"><strong>Nama:</strong> <?= htmlspecialchars($data['nama_petugas']); ?></p>
                                                                            <p class="mb-0"><strong>Tanggal:</strong> <?= date('d F Y H:i', strtotime($data['tgl_tanggapan'])); ?></p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <div class="card bg-light">
                                                                        <div class="card-body">
                                                                            <h6 class="card-title text-primary"><i class="bi bi-person"></i> Info Pelapor</h6>
                                                                            <p class="mb-1"><strong>NIK:</strong> <?= htmlspecialchars($data['nik']); ?></p>
                                                                            <p class="mb-0"><strong>Nama:</strong> <?= htmlspecialchars($data['nama_masyarakat']); ?></p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <h6 class="text-success"><i class="bi bi-chat-left-text"></i> Judul Laporan</h6>
                                                                <div class="alert alert-success">
                                                                    <?= htmlspecialchars($data['judul_pengaduan']); ?>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <h6 class="text-primary"><i class="bi bi-file-text"></i> Isi Laporan</h6>
                                                                <div class="border p-3 bg-light rounded">
                                                                    <?= nl2br(htmlspecialchars($data['isi_laporan'])); ?>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <h6 class="text-warning"><i class="bi bi-reply"></i> Tanggapan Petugas</h6>
                                                                <div class="border p-3 bg-warning bg-opacity-10 rounded">
                                                                    <?= nl2br(htmlspecialchars($data['tanggapan'])); ?>
                                                                </div>
                                                            </div>
                                                            
                                                            <div class="mb-3">
                                                                <h6><i class="bi bi-flag"></i> Status Pengaduan</h6>
                                                                <div class="text-center">
                                                                    <?php 
                                                                    switch ($data['status']) {
                                                                        case "selesai": 
                                                                            echo "<span class='badge bg-success fs-6 px-3 py-2'>
                                                                                    <i class='bi bi-check-circle'></i> Selesai
                                                                                  </span>"; 
                                                                            break;
                                                                        case "proses": 
                                                                            echo "<span class='badge bg-warning text-dark fs-6 px-3 py-2'>
                                                                                    <i class='bi bi-clock'></i> Sedang Proses
                                                                                  </span>"; 
                                                                            break;
                                                                        case "tolak": 
                                                                            echo "<span class='badge bg-danger fs-6 px-3 py-2'>
                                                                                    <i class='bi bi-x-circle'></i> Ditolak
                                                                                  </span>"; 
                                                                            break;
                                                                        default: 
                                                                            echo "<span class='badge bg-secondary fs-6 px-3 py-2'>
                                                                                    <i class='bi bi-hourglass'></i> Menunggu
                                                                                  </span>";
                                                                    }
                                                                    ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Modal Hapus -->
                                            <div class="modal fade" id="hapus<?= $data['id_tanggapan'] ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Konfirmasi Hapus</h5>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <form action="edit_data.php" method="POST">
                                                                <input type="hidden" name="id_tanggapan" value="<?= $data['id_tanggapan']; ?>">
                                                                <div class="text-center mb-3">
                                                                    <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 3rem;"></i>
                                                                </div>
                                                                <p class="text-center">Apakah Anda yakin ingin menghapus tanggapan ini?</p>
                                                                <div class="alert alert-info">
                                                                    <strong>Judul Laporan:</strong> <?= htmlspecialchars($data['judul_pengaduan']); ?><br>
                                                                    <strong>Tanggapan:</strong> <?= htmlspecialchars(substr($data['tanggapan'], 0, 200) . (strlen($data['tanggapan']) > 200 ? '...' : '')); ?><br>
                                                                    <strong>Petugas:</strong> <?= htmlspecialchars($data['nama_petugas']); ?>
                                                                </div>
                                                                <div class="alert alert-warning">
                                                                    <small><i class="bi bi-exclamation-triangle"></i> <strong>Peringatan:</strong> Data yang dihapus tidak dapat dikembalikan!</small>
                                                                </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                                                <i class="bi bi-x"></i> Batal
                                                            </button>
                                                            <button type="submit" name="hapus_tanggapan" class="btn btn-danger">
                                                                <i class="bi bi-trash"></i> Hapus Tanggapan
                                                            </button>
                                                        </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <div class="d-flex flex-column align-items-center">
                                            <i class="bi bi-inbox display-1 text-muted mb-3"></i>
                                            <h5 class="text-muted">Belum Ada Data Tanggapan</h5>
                                            <p class="text-muted">Tanggapan akan muncul setelah petugas memberikan respon terhadap pengaduan.</p>
                                        </div>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <?php if ($query && mysqli_num_rows($query) > 0): ?>
            <div class="card-footer bg-light">
                <div class="row align-items-center">
                    <div class="col-md-6">
                        <small class="text-muted">
                            <i class="bi bi-info-circle"></i> Total: <strong><?= mysqli_num_rows($query); ?></strong> tanggapan
                        </small>
                    </div>
                    <div class="col-md-6 text-end">
                        <small class="text-muted">
                            <i class="bi bi-calendar"></i> Update terakhir: <?= date('d F Y H:i'); ?>
                        </small>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Custom CSS untuk styling tambahan -->
<style>
.modal-dialog-scrollable .modal-body {
    max-height: 70vh;
}

.table-hover tbody tr:hover {
    background-color: rgba(255, 193, 7, 0.1);
}

.badge {
    font-size: 0.75em;
}

.card-hover {
    transition: transform 0.2s ease-in-out;
}

.card-hover:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
}

.alert {
    border: none;
    border-radius: 10px;
}

.bg-warning.bg-opacity-10 {
    background-color: rgba(255, 193, 7, 0.1) !important;
}
</style>

<!-- JavaScript untuk interaktivitas tambahan -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-hide alerts setelah 5 detik
    setTimeout(function() {
        const alerts = document.querySelectorAll('.alert:not(.alert-info):not(.alert-warning)');
        alerts.forEach(function(alert) {
            if (alert.classList.contains('alert-success') || alert.classList.contains('alert-danger')) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() {
                    alert.remove();
                }, 500);
            }
        });
    }, 5000);

    // Tooltip untuk tombol-tombol
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});

// Fungsi untuk konfirmasi hapus dengan SweetAlert (jika tersedia)
function confirmDelete(id, judul) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Hapus Tanggapan?',
            text: `Yakin ingin menghapus tanggapan untuk: ${judul}`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('hapusForm' + id).submit();
            }
        });
    } else {
        return confirm('Yakin ingin menghapus tanggapan ini?');
    }
}
</script>