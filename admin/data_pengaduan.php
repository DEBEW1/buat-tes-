<?php
include "../config/koneksi.php";
$no = 1;
$ambil = mysqli_query($conn, "SELECT a.*, b.* 
                              FROM pengaduan a 
                              INNER JOIN masyarakat b ON a.nik = b.nik 
                              ORDER BY id_pengaduan ASC;");
?>

<div class="card shadow-sm">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Data Pengaduan Warga</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-hover table-striped align-middle">
                <thead class="table-primary text-center">
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
                    <?php while ($data = mysqli_fetch_assoc($ambil)) { ?>
                        <tr>
                            <td class="text-center"><?= $no++; ?></td>
                            <td class="text-center"><?= htmlspecialchars($data['tgl_pengaduan']); ?></td>
                            <td><?= htmlspecialchars($data['nama']); ?></td>
                            <td><?= htmlspecialchars($data['judul_pengaduan']); ?></td>
                            <td><?= htmlspecialchars(substr($data['isi_laporan'], 0, 50)); ?>...</td>
                            <td class="text-center">
                                <?php if (!empty($data['foto'])) { ?>
                                    <img src="../database/img/<?= htmlspecialchars($data['foto']); ?>" 
                                         alt="Foto" width="60" class="img-thumbnail">
                                <?php } else { ?>
                                    <span class="text-muted small">Tidak ada foto</span>
                                <?php } ?>
                            </td>
                            <td class="text-center">
                                <?php 
                                switch ($data['status']) {
                                    case "selesai": echo "<span class='badge bg-success'>Selesai</span>"; break;
                                    case "proses": echo "<span class='badge bg-warning text-dark'>Proses</span>"; break;
                                    case "tolak": echo "<span class='badge bg-danger'>Ditolak</span>"; break;
                                    default: echo "<span class='badge bg-secondary'>Menunggu</span>";
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
                            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                                <div class="modal-content">
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title">Detail Pengaduan</h5>
                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Tabs -->
                                        <ul class="nav nav-tabs">
                                            <li class="nav-item">
                                                <button class="nav-link active" data-bs-toggle="tab" 
                                                        data-bs-target="#detail-tab<?= $data['id_pengaduan'] ?>">
                                                    Detail
                                                </button>
                                            </li>
                                            <li class="nav-item">
                                                <button class="nav-link" data-bs-toggle="tab" 
                                                        data-bs-target="#aksi-tab<?= $data['id_pengaduan'] ?>">
                                                    Aksi
                                                </button>
                                            </li>
                                        </ul>

                                        <div class="tab-content mt-3">
                                            <!-- Tab Detail -->
                                            <div class="tab-pane fade show active" id="detail-tab<?= $data['id_pengaduan'] ?>">
                                                <div class="row g-3">
                                                    <!-- Profil Pelapor -->
                                                    <div class="col-md-4 text-center">
                                                        <img src="../database/foto_masyarakat/<?= !empty($data['foto_profil']) ? htmlspecialchars($data['foto_profil']) : 'default.png'; ?>" 
                                                             alt="Foto Pelapor" width="120" class="rounded-circle shadow mb-2">
                                                        <h6 class="fw-bold"><?= htmlspecialchars($data['nama']); ?></h6>
                                                        <p class="text-muted small mb-1">NIK: <?= htmlspecialchars($data['nik']); ?></p>
                                                        <p class="text-muted small">Telp: <?= htmlspecialchars($data['telp']); ?></p>
                                                    </div>
                                                    <!-- Detail Laporan -->
                                                    <div class="col-md-8">
                                                        <p><strong>Tanggal:</strong> <?= htmlspecialchars($data['tgl_pengaduan']); ?></p>
                                                        <p><strong>Judul:</strong> <?= htmlspecialchars($data['judul_pengaduan']); ?></p>
                                                        <p><strong>Kronologi:</strong><br><?= nl2br(htmlspecialchars($data['isi_laporan'])); ?></p>
                                                        <p><strong>Status:</strong> 
                                                            <?php 
                                                            switch ($data['status']) {
                                                                case "selesai": echo "<span class='badge bg-success'>Selesai</span>"; break;
                                                                case "proses": echo "<span class='badge bg-warning text-dark'>Proses</span>"; break;
                                                                case "tolak": echo "<span class='badge bg-danger'>Ditolak</span>"; break;
                                                                default: echo "<span class='badge bg-secondary'>Menunggu</span>";
                                                            }
                                                            ?>
                                                        </p>
                                                        <p><strong>Foto Laporan:</strong></p>
                                                        <?php if (!empty($data['foto'])) { ?>
                                                            <a href="../database/img/<?= htmlspecialchars($data['foto']); ?>" 
                                                               download class="btn btn-sm btn-outline-primary mb-2">Download</a>
                                                            <img src="../database/img/<?= htmlspecialchars($data['foto']); ?>" 
                                                                 width="100%" class="img-thumbnail shadow">
                                                        <?php } else { ?>
                                                            <span class="text-muted">Tidak ada foto</span>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Tab Aksi -->
                                            <div class="tab-pane fade" id="aksi-tab<?= $data['id_pengaduan'] ?>">
                                                <?php if ($data['status'] == "0" || $data['status'] == "menunggu") { ?>
                                                    <!-- Verifikasi -->
                                                    <form action="" method="POST" class="mb-3">
                                                        <input type="hidden" name="id_pengaduan" value="<?= $data['id_pengaduan']; ?>">
                                                        <label class="form-label fw-bold">Verifikasi</label>
                                                        <div class="input-group" style="max-width:300px;">
                                                            <select class="form-select" name="status">
                                                                <option value="proses">Proses</option>
                                                                <option value="tolak">Tolak</option>
                                                            </select>
                                                            <button type="submit" name="kirim" class="btn btn-primary">Simpan</button>
                                                        </div>
                                                    </form>
                                                <?php } ?>

                                                <?php if ($data['status'] == "proses") { ?>
                                                    <!-- Tanggapan -->
                                                    <form action="" method="POST" class="mb-3">
                                                        <input type="hidden" name="id_pengaduan" value="<?= $data['id_pengaduan']; ?>">
                                                        <label class="form-label fw-bold">Tanggapan</label>
                                                        <textarea name="tanggapan" class="form-control mb-2" rows="3" 
                                                                  placeholder="Masukkan tanggapan..." required></textarea>
                                                        <button type="submit" name="tanggapi" class="btn btn-success">Kirim</button>
                                                    </form>

                                                    <!-- Selesaikan -->
                                                    <form action="" method="POST" onsubmit="return confirm('Yakin laporan ini sudah selesai ditangani?');">
                                                        <input type="hidden" name="id_pengaduan" value="<?= $data['id_pengaduan']; ?>">
                                                        <button type="submit" name="selesaikan" class="btn btn-outline-success">
                                                            Tandai Selesai
                                                        </button>
                                                    </form>
                                                <?php } ?>

                                                <form action="edit_data.php" method="POST" 
                                                      onsubmit="return confirm('Yakin mau hapus laporan ini?');">
                                                    <input type="hidden" name="id_pengaduan" value="<?= $data['id_pengaduan']; ?>">
                                                    <button type="submit" name="hapus_pengaduan" class="btn btn-danger mt-2">Hapus Laporan</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// Update status verifikasi
if (isset($_POST["kirim"])) {
    $id_pengaduan = intval($_POST["id_pengaduan"]);
    $status = mysqli_real_escape_string($conn, $_POST["status"]);
    mysqli_query($conn, "UPDATE pengaduan SET status = '$status' WHERE id_pengaduan = '$id_pengaduan'");
    echo "<script>alert('Status berhasil diubah'); document.location.href='index.php?page=pengaduan';</script>";
}

// Simpan tanggapan
if (isset($_POST["tanggapi"])) {
    $id_pengaduan = intval($_POST["id_pengaduan"]);

    // Pastikan session id_petugas ada
    if (!isset($_SESSION['id_petugas'])) {
        die("Error: Petugas belum login.");
    }

    $id_petugas = $_SESSION['id_petugas'];
    $tanggal    = date("Y-m-d H:i:s"); // timestamp lengkap
    $tanggapan  = mysqli_real_escape_string($conn, $_POST["tanggapan"]);

    // Insert tanggapan
    $query = "INSERT INTO tanggapan (id_pengaduan, tgl_tanggapan, tanggapan, id_petugas)
              VALUES ('$id_pengaduan', '$tanggal', '$tanggapan', '$id_petugas')";

    if (!mysqli_query($conn, $query)) {
        die("Error insert tanggapan: " . mysqli_error($conn));
    }

    // Update status otomatis ke 'proses'
    mysqli_query($conn, "UPDATE pengaduan SET status='proses' WHERE id_pengaduan='$id_pengaduan'");

    echo "<script>
            alert('Tanggapan berhasil disimpan'); 
            document.location.href='index.php?page=pengaduan';
          </script>";
}




// Tandai selesai
if (isset($_POST["selesaikan"])) {
    $id_pengaduan = intval($_POST["id_pengaduan"]);
    mysqli_query($conn, "UPDATE pengaduan SET status = 'selesai' WHERE id_pengaduan = '$id_pengaduan'");
    echo "<script>alert('Pengaduan ditandai selesai'); document.location.href='index.php?page=pengaduan';</script>";
}
?>
