<?php
// data_tanggapan.php
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

$tanggapans = $db->fetchAll($sql);
?>

<div class="container-fluid">
    <div class="row">
        <div class="card mb-4">
            <div class="card-header d-flex pb-0 align-items-center">
                <h6 class="mb-0">Data Tanggapan</h6>
                <a href="export_tanggapan.php" class="btn btn-success btn-sm ms-auto">
                    <i class="bi bi-file-earmark-excel"></i> Export Excel
                </a>
            </div>
            <div class="card-body px-0 pt-0 pb-2">
                <div class="table-responsive p-0">
                    <table class="table table-striped align-items-center mb-0">
                        <thead class="text-center bg-light">
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
                        <tbody class="text-center">
                            <?php if (!empty($tanggapans)): ?>
                                <?php $no = 1; foreach ($tanggapans as $data): ?>
                                    <tr>
                                        <td><?= $no++; ?></td>
                                        <td><?= date('d/m/Y H:i', strtotime($data['tgl_tanggapan'])); ?></td>
                                        <td><?= htmlspecialchars($data['nama_petugas']); ?></td>
                                        <td><?= htmlspecialchars($data['nik']); ?></td>
                                        <td><?= htmlspecialchars($data['nama_masyarakat']); ?></td>
                                        <td><?= htmlspecialchars($data['judul_pengaduan']); ?></td>
                                        <td><?= nl2br(htmlspecialchars(substr($data['tanggapan'], 0, 100) . (strlen($data['tanggapan']) > 100 ? '...' : ''))); ?></td>
                                        <td>
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
                                        <td>
                                            <!-- Tombol Detail -->
                                            <button class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#detail<?= $data['id_tanggapan'] ?>">
                                                Detail
                                            </button>

                                            <!-- Tombol Hapus -->
                                            <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#hapus<?= $data['id_tanggapan'] ?>">
                                                Hapus
                                            </button>

                                            <!-- Modal Detail -->
                                            <div class="modal fade" id="detail<?= $data['id_tanggapan'] ?>" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-lg">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-info text-white">
                                                            <h5 class="modal-title">Detail Tanggapan</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            <div class="row mb-3">
                                                                <div class="col-md-6">
                                                                    <strong>Petugas:</strong> <?= htmlspecialchars($data['nama_petugas']); ?>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <strong>Tanggal:</strong> <?= date('d F Y H:i', strtotime($data['tgl_tanggapan'])); ?>
                                                                </div>
                                                            </div>
                                                            <div class="row mb-3">
                                                                <div class="col-md-6">
                                                                    <strong>NIK:</strong> <?= htmlspecialchars($data['nik']); ?>
                                                                </div>
                                                                <div class="col-md-6">
                                                                    <strong>Nama:</strong> <?= htmlspecialchars($data['nama_masyarakat']); ?>
                                                                </div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <strong>Judul Laporan:</strong><br>
                                                                <?= htmlspecialchars($data['judul_pengaduan']); ?>
                                                            </div>
                                                            <div class="mb-3">
                                                                <strong>Isi Laporan:</strong><br>
                                                                <?= nl2br(htmlspecialchars($data['isi_laporan'])); ?>
                                                            </div>
                                                            <div class="mb-3">
                                                                <strong>Tanggapan:</strong><br>
                                                                <?= nl2br(htmlspecialchars($data['tanggapan'])); ?>
                                                            </div>
                                                            <div>
                                                                <strong>Status:</strong> 
                                                                <?php 
                                                                switch ($data['status']) {
                                                                    case "selesai": echo "<span class='badge bg-success'>Selesai</span>"; break;
                                                                    case "proses": echo "<span class='badge bg-warning text-dark'>Proses</span>"; break;
                                                                    case "tolak": echo "<span class='badge bg-danger'>Ditolak</span>"; break;
                                                                    default: echo "<span class='badge bg-secondary'>Menunggu</span>";
                                                                }
                                                                ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Modal Hapus -->
                                            <div class="modal fade" id="hapus<?= $data['id_tanggapan'] ?>" tabindex="-1" aria-labelledby="hapusLabel<?= $data['id_tanggapan'] ?>" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header bg-danger text-white">
                                                            <h5 class="modal-title" id="hapusLabel<?= $data['id_tanggapan'] ?>">Konfirmasi Hapus</h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body text-start">
                                                            <form action="edit_data.php" method="POST">
                                                                <input type="hidden" name="id_tanggapan" value="<?= $data['id_tanggapan']; ?>">
                                                                <input type="hidden" name="id_pengaduan" value="<?= $data['id_pengaduan']; ?>">
                                                                <p>Apakah Anda yakin ingin menghapus tanggapan untuk laporan:</p>
                                                                <blockquote class="blockquote">
                                                                    <strong><?= htmlspecialchars($data['judul_pengaduan']); ?></strong><br>
                                                                    <?= nl2br(htmlspecialchars(substr($data['tanggapan'], 0, 200) . (strlen($data['tanggapan']) > 200 ? '...' : ''))); ?>
                                                                </blockquote>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <button type="submit" name="hapus_tanggapan" value="hapus_tanggapan" class="btn btn-danger">Hapus</button>
                                                        </div>
                                                            </form>
                                                    </div>
                                                </div>
                                            </div>
                                            <!-- /Modal Hapus -->
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="text-center text-muted">Belum ada data tanggapan</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>