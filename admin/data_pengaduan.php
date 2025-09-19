<?php
// Update status verifikasi
if (isset($_POST["kirim"])) {
    $id_pengaduan = intval($_POST["id_pengaduan"]);
    $status = mysqli_real_escape_string($conn, $_POST["status"]);
    
    $query = mysqli_query($conn, "UPDATE pengaduan SET status = '$status' WHERE id_pengaduan = '$id_pengaduan'");
    
    if ($query) {
        echo "<script>alert('Status berhasil diubah'); window.location='index.php?page=pengaduan';</script>";
    } else {
        echo "<script>alert('Gagal mengubah status: " . mysqli_error($conn) . "'); window.location='index.php?page=pengaduan';</script>";
    }
}

// Simpan tanggapan
if (isset($_POST["tanggapi"])) {
    $id_pengaduan = intval($_POST["id_pengaduan"]);

    // Pastikan session id_petugas ada
    if (!isset($_SESSION['id_petugas'])) {
        echo "<script>alert('Error: Petugas belum login.'); window.location='../index.php?page=login';</script>";
        exit();
    }

    $id_petugas = intval($_SESSION['id_petugas']);
    $tanggal = date("Y-m-d H:i:s");
    $tanggapan = mysqli_real_escape_string($conn, $_POST["tanggapan"]);

    // Insert tanggapan
    $query = "INSERT INTO tanggapan (id_pengaduan, tgl_tanggapan, tanggapan, id_petugas)
              VALUES ('$id_pengaduan', '$tanggal', '$tanggapan', '$id_petugas')";

    if (mysqli_query($conn, $query)) {
        // Update status otomatis ke 'proses'
        mysqli_query($conn, "UPDATE pengaduan SET status='proses' WHERE id_pengaduan='$id_pengaduan'");
        echo "<script>alert('Tanggapan berhasil disimpan'); window.location='index.php?page=pengaduan';</script>";
    } else {
        echo "<script>alert('Error insert tanggapan: " . mysqli_error($conn) . "'); window.location='index.php?page=pengaduan';</script>";
    }
}

// Tandai selesai
if (isset($_POST["selesaikan"])) {
    $id_pengaduan = intval($_POST["id_pengaduan"]);
    $query = mysqli_query($conn, "UPDATE pengaduan SET status = 'selesai' WHERE id_pengaduan = '$id_pengaduan'");
    
    if ($query) {
        echo "<script>alert('Pengaduan ditandai selesai'); window.location='index.php?page=pengaduan';</script>";
    } else {
        echo "<script>alert('Gagal menandai selesai: " . mysqli_error($conn) . "'); window.location='index.php?page=pengaduan';</script>";
    }
}

// Query data pengaduan
$query = mysqli_query($conn, "SELECT a.*, b.* 
                              FROM pengaduan a 
                              INNER JOIN masyarakat b ON a.nik = b.nik 
                              ORDER BY a.tgl_pengaduan DESC");

if (!$query) {
    echo "<div class='alert alert-danger'>Error: " . mysqli_error($conn) . "</div>";
}

$no = 1;
?>

<div class="card shadow-sm">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="bi bi-chat-dots"></i> Data Pengaduan Warga</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle">
                <thead class="table-success text-center">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama</th>
                        <th>Judul</th>
                        <th>Ringkasan</th>
                        <th>Foto</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($query && mysqli_num_rows($query) > 0): ?>
                        <?php while ($data = mysqli_fetch_assoc($query)): ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td class="text-center"><?= date('d/m/Y', strtotime($data['tgl_pengaduan'])); ?></td>
                                <td><?= htmlspecialchars($data['nama']); ?></td>
                                <td><?= htmlspecialchars($data['judul_pengaduan']); ?></td>
                                <td><?= htmlspecialchars(substr($data['isi_laporan'], 0, 50)); ?>...</td>
                                <td class="text-center">
                                    <?php if (!empty($data['foto'])): ?>
                                        <img src="../database/img/<?= htmlspecialchars($data['foto']); ?>" 
                                             alt="Foto" width="60" class="img-thumbnail" 
                                             onclick="showImageModal('<?= htmlspecialchars($data['foto']); ?>')">
                                    <?php else: ?>
                                        <span class="text-muted small">Tidak ada foto</span>
                                    <?php endif; ?>
                                </td>
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
                                    <button class="btn btn-info btn-sm text-white" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#detail<?= $data['id_pengaduan'] ?>">
                                        <i class="bi bi-eye"></i> Detail
                                    </button>
                                </td>
                            </tr>

                            <!-- Modal Detail -->
                            <div class="modal fade" id="detail<?= $data['id_pengaduan'] ?>" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-xl modal-dialog-scrollable">
                                    <div class="modal-content">
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title">Detail Pengaduan</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <!-- Tabs -->
                                            <ul class="nav nav-tabs" role="tablist">
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link active" data-bs-toggle="tab" 
                                                            data-bs-target="#detail-tab<?= $data['id_pengaduan'] ?>" 
                                                            type="button" role="tab">
                                                        <i class="bi bi-info-circle"></i> Detail
                                                    </button>
                                                </li>
                                                <li class="nav-item" role="presentation">
                                                    <button class="nav-link" data-bs-toggle="tab" 
                                                            data-bs-target="#aksi-tab<?= $data['id_pengaduan'] ?>" 
                                                            type="button" role="tab">
                                                        <i class="bi bi-gear"></i> Aksi
                                                    </button>
                                                </li>
                                            </ul>

                                            <div class="tab-content mt-3">
                                                <!-- Tab Detail -->
                                                <div class="tab-pane fade show active" id="detail-tab<?= $data['id_pengaduan'] ?>" role="tabpanel">
                                                    <div class="row g-3">
                                                        <!-- Profil Pelapor -->
                                                        <div class="col-md-4 text-center">
                                                            <div class="card bg-light">
                                                                <div class="card-body">
                                                                    <i class="bi bi-person-circle display-3 text-primary"></i>
                                                                    <h6 class="fw-bold mt-2"><?= htmlspecialchars($data['nama']); ?></h6>
                                                                    <p class="text-muted small mb-1">NIK: <?= htmlspecialchars($data['nik']); ?></p>
                                                                    <p class="text-muted small">Telp: <?= htmlspecialchars($data['telp']); ?></p>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        
                                                        <!-- Detail Laporan -->
                                                        <div class="col-md-8">
                                                            <div class="mb-3">
                                                                <strong>Tanggal Pengaduan:</strong> 
                                                                <span class="badge bg-info"><?= date('d F Y H:i', strtotime($data['tgl_pengaduan'])); ?></span>
                                                            </div>
                                                            <div class="mb-3">
                                                                <strong>Judul Pengaduan:</strong><br>
                                                                <h6 class="text-primary"><?= htmlspecialchars($data['judul_pengaduan']); ?></h6>
                                                            </div>
                                                            <div class="mb-3">
                                                                <strong>Kronologi:</strong><br>
                                                                <div class="border p-3 bg-light rounded">
                                                                    <?= nl2br(htmlspecialchars($data['isi_laporan'])); ?>
                                                                </div>
                                                            </div>
                                                            <div class="mb-3">
                                                                <strong>Status Saat Ini:</strong> 
                                                                <?php 
                                                                switch ($data['status']) {
                                                                    case "selesai": echo "<span class='badge bg-success fs-6'>Selesai</span>"; break;
                                                                    case "proses": echo "<span class='badge bg-warning text-dark fs-6'>Proses</span>"; break;
                                                                    case "tolak": echo "<span class='badge bg-danger fs-6'>Ditolak</span>"; break;
                                                                    default: echo "<span class='badge bg-secondary fs-6'>Menunggu</span>";
                                                                }
                                                                ?>
                                                            </div>
                                                            <div>
                                                                <strong>Foto Laporan:</strong><br>
                                                                <?php if (!empty($data['foto'])): ?>
                                                                    <div class="mt-2">
                                                                        <img src="../database/img/<?= htmlspecialchars($data['foto']); ?>" 
                                                                             class="img-thumbnail" style="max-width: 300px;" 
                                                                             onclick="showImageModal('<?= htmlspecialchars($data['foto']); ?>')">
                                                                        <br><a href="../database/img/<?= htmlspecialchars($data['foto']); ?>" 
                                                                               download class="btn btn-sm btn-outline-primary mt-2">
                                                                            <i class="bi bi-download"></i> Download
                                                                        </a>
                                                                    </div>
                                                                <?php else: ?>
                                                                    <span class="text-muted">Tidak ada foto</span>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>

                                                <!-- Tab Aksi -->
                                                <div class="tab-pane fade" id="aksi-tab<?= $data['id_pengaduan'] ?>" role="tabpanel">
                                                    <?php if ($data['status'] == "0" || $data['status'] == "menunggu" || empty($data['status'])): ?>
                                                        <!-- Verifikasi -->
                                                        <div class="card mb-3">
                                                            <div class="card-header bg-info text-white">
                                                                <h6 class="mb-0">Verifikasi Pengaduan</h6>
                                                            </div>
                                                            <div class="card-body">
                                                                <form action="" method="POST">
                                                                    <input type="hidden" name="id_pengaduan" value="<?= $data['id_pengaduan']; ?>">
                                                                    <div class="row align-items-center">
                                                                        <div class="col-md-6">
                                                                            <select class="form-select" name="status" required>
                                                                                <option value="">Pilih Status</option>
                                                                                <option value="proses">Terima & Proses</option>
                                                                                <option value="tolak">Tolak Pengaduan</option>
                                                                            </select>
                                                                        </div>
                                                                        <div class="col-md-6">
                                                                            <button type="submit" name="kirim" class="btn btn-primary">
                                                                                <i class="bi bi-check-circle"></i> Update Status
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if ($data['status'] == "proses"): ?>
                                                        <!-- Tanggapan -->
                                                        <div class="card mb-3">
                                                            <div class="card-header bg-warning text-dark">
                                                                <h6 class="mb-0">Beri Tanggapan</h6>
                                                            </div>
                                                            <div class="card-body">
                                                                <form action="" method="POST">
                                                                    <input type="hidden" name="id_pengaduan" value="<?= $data['id_pengaduan']; ?>">
                                                                    <div class="mb-3">
                                                                        <textarea name="tanggapan" class="form-control" rows="4" 
                                                                                  placeholder="Masukkan tanggapan untuk pengaduan ini..." required></textarea>
                                                                    </div>
                                                                    <button type="submit" name="tanggapi" class="btn btn-success">
                                                                        <i class="bi bi-send"></i> Kirim Tanggapan
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>

                                                        <!-- Selesaikan -->
                                                        <div class="card mb-3">
                                                            <div class="card-header bg-success text-white">
                                                                <h6 class="mb-0">Selesaikan Pengaduan</h6>
                                                            </div>
                                                            <div class="card-body">
                                                                <p class="text-muted">Tandai pengaduan ini sebagai selesai jika sudah ditangani dengan baik.</p>
                                                                <form action="" method="POST" onsubmit="return confirm('Yakin laporan ini sudah selesai ditangani?');">
                                                                    <input type="hidden" name="id_pengaduan" value="<?= $data['id_pengaduan']; ?>">
                                                                    <button type="submit" name="selesaikan" class="btn btn-success">
                                                                        <i class="bi bi-check-all"></i> Tandai Selesai
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if ($data['status'] == "selesai"): ?>
                                                        <div class="alert alert-success">
                                                            <i class="bi bi-check-circle"></i> Pengaduan ini sudah diselesaikan.
                                                        </div>
                                                    <?php endif; ?>

                                                    <?php if ($data['status'] == "tolak"): ?>
                                                        <div class="alert alert-danger">
                                                            <i class="bi bi-x-circle"></i> Pengaduan ini ditolak.
                                                        </div>
                                                    <?php endif; ?>

                                                    <!-- Hapus Pengaduan -->
                                                    <div class="card border-danger">
                                                        <div class="card-header bg-danger text-white">
                                                            <h6 class="mb-0">Zona Berbahaya</h6>
                                                        </div>
                                                        <div class="card-body">
                                                            <p class="text-danger">Menghapus pengaduan akan menghapus semua data terkait termasuk tanggapan.</p>
                                                            <form action="edit_data.php" method="POST" 
                                                                  onsubmit="return confirm('PERHATIAN: Yakin ingin menghapus pengaduan ini? Data yang dihapus tidak dapat dikembalikan!');">
                                                                <input type="hidden" name="id_pengaduan" value="<?= $data['id_pengaduan']; ?>">
                                                                <button type="submit" name="hapus_pengaduan" class="btn btn-danger">
                                                                    <i class="bi bi-trash"></i> Hapus Pengaduan
                                                                </button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-inbox display-1 text-muted"></i>
                                <p class="mt-2">Belum ada pengaduan</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal untuk tampil gambar -->
<div class="modal fade" id="imageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Foto Pengaduan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img id="modalImage" src="" class="img-fluid" alt="Foto Pengaduan">
            </div>
            <div class="modal-footer">
                <a id="downloadLink" href="" download class="btn btn-primary">
                    <i class="bi bi-download"></i> Download
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function showImageModal(filename) {
    const modal = new bootstrap.Modal(document.getElementById('imageModal'));
    const modalImage = document.getElementById('modalImage');
    const downloadLink = document.getElementById('downloadLink');
    
    const imagePath = '../database/img/' + filename;
    modalImage.src = imagePath;
    downloadLink.href = imagePath;
    
    modal.show();
}
</script>