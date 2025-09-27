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

    if (!isset($_SESSION['id_petugas'])) {
        echo "<script>alert('Error: Petugas belum login.'); window.location='../index.php?page=login';</script>";
        exit();
    }

    $id_petugas = intval($_SESSION['id_petugas']);
    $tanggal = date("Y-m-d H:i:s");
    $tanggapan = mysqli_real_escape_string($conn, $_POST["tanggapan"]);

    $query = "INSERT INTO tanggapan (id_pengaduan, tgl_tanggapan, tanggapan, id_petugas)
              VALUES ('$id_pengaduan', '$tanggal', '$tanggapan', '$id_petugas')";

    if (mysqli_query($conn, $query)) {
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

<style>
.table-compact {
    font-size: 0.9rem;
}
.table-compact th,
.table-compact td {
    padding: 10px 8px;
    vertical-align: middle;
}
.btn-xs {
    padding: 4px 8px;
    font-size: 0.8rem;
}
.text-truncate-custom {
    max-width: 200px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}
@media (max-width: 768px) {
    .table-compact {
        font-size: 0.8rem;
    }
    .table-compact th,
    .table-compact td {
        padding: 6px 4px;
    }
    .text-truncate-custom {
        max-width: 120px;
    }
}
</style>

<div class="card shadow-sm">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="bi bi-chat-dots"></i> Data Pengaduan Warga</h5>
        <button class="btn btn-light btn-sm" onclick="location.reload()">
            <i class="bi bi-arrow-clockwise"></i> Refresh
        </button>
    </div>
    <div class="card-body p-2">
        <!-- Quick Actions -->
        <div class="row mb-3">
            <div class="col-md-4">
                <div class="card bg-warning text-dark">
                    <div class="card-body p-2 text-center">
                        <h6 class="mb-1">Menunggu</h6>
                        <h4><?php 
                        $pending = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengaduan WHERE status='0' OR status='' OR status IS NULL");
                        echo mysqli_fetch_assoc($pending)['total']; 
                        ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-info text-white">
                    <div class="card-body p-2 text-center">
                        <h6 class="mb-1">Diproses</h6>
                        <h4><?php 
                        $proses = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengaduan WHERE status='proses'");
                        echo mysqli_fetch_assoc($proses)['total']; 
                        ?></h4>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body p-2 text-center">
                        <h6 class="mb-1">Selesai</h6>
                        <h4><?php 
                        $selesai = mysqli_query($conn, "SELECT COUNT(*) as total FROM pengaduan WHERE status='selesai'");
                        echo mysqli_fetch_assoc($selesai)['total']; 
                        ?></h4>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-hover table-compact align-middle mb-0">
                <thead class="table-success text-center">
                    <tr>
                        <th width="5%">No</th>
                        <th width="10%">Tanggal</th>
                        <th width="15%">Pelapor</th>
                        <th width="25%">Judul</th>
                        <th width="20%">Ringkasan</th>
                        <th width="8%">Foto</th>
                        <th width="10%">Status</th>
                        <th width="7%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($query && mysqli_num_rows($query) > 0): ?>
                        <?php while ($data = mysqli_fetch_assoc($query)): ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td class="text-center small"><?= date('d/m/Y', strtotime($data['tgl_pengaduan'])); ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($data['nama']); ?></strong><br>
                                    <small class="text-muted"><?= htmlspecialchars($data['nik']); ?></small>
                                </td>
                                <td class="text-truncate-custom" title="<?= htmlspecialchars($data['judul_pengaduan']); ?>">
                                    <?= htmlspecialchars($data['judul_pengaduan']); ?>
                                </td>
                                <td class="text-truncate-custom" title="<?= htmlspecialchars($data['isi_laporan']); ?>">
                                    <?= htmlspecialchars(substr($data['isi_laporan'], 0, 80)) . (strlen($data['isi_laporan']) > 80 ? '...' : ''); ?>
                                </td>
                                <td class="text-center">
                                    <?php if (!empty($data['foto'])): ?>
                                        <a href="../database/img/<?= htmlspecialchars($data['foto']); ?>" target="_blank" class="btn btn-outline-primary btn-xs">
                                            <i class="bi bi-image"></i>
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
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
                                    <div class="btn-group-vertical btn-group-sm" role="group">
                                        <button class="btn btn-info btn-xs text-white" onclick="showDetail(<?= $data['id_pengaduan'] ?>)">
                                            <i class="bi bi-eye"></i>
                                        </button>
                                        <?php if ($data['status'] == '0' || $data['status'] == 'menunggu' || empty($data['status'])): ?>
                                        <button class="btn btn-warning btn-xs" onclick="showVerifikasi(<?= $data['id_pengaduan'] ?>)">
                                            <i class="bi bi-check"></i>
                                        </button>
                                        <?php elseif ($data['status'] == 'proses'): ?>
                                        <button class="btn btn-success btn-xs" onclick="showTanggapan(<?= $data['id_pengaduan'] ?>)">
                                            <i class="bi bi-chat"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-inbox display-4 text-muted"></i>
                                <p class="mt-2">Belum ada pengaduan</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Detail Panel (akan ditampilkan di bawah tabel) -->
<div id="detailPanel" style="display: none;" class="card mt-3">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0" id="detailTitle">Detail Pengaduan</h6>
        <button class="btn btn-light btn-sm" onclick="hideDetail()">
            <i class="bi bi-x"></i> Tutup
        </button>
    </div>
    <div class="card-body" id="detailContent">
        <!-- Content akan diload via AJAX atau JavaScript -->
    </div>
</div>

<script>
// Data pengaduan dalam format JavaScript untuk manipulasi
const pengaduanData = {
    <?php 
    mysqli_data_seek($query, 0); // Reset pointer
    $jsData = [];
    while ($data = mysqli_fetch_assoc($query)) {
        $jsData[] = json_encode($data);
    }
    echo implode(',', $jsData);
    ?>
};

function showDetail(id) {
    const data = pengaduanData.find(p => p.id_pengaduan == id);
    if (!data) return;
    
    document.getElementById('detailTitle').textContent = `Detail Pengaduan #${id}`;
    document.getElementById('detailContent').innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <h6 class="text-primary">Informasi Pelapor</h6>
                <table class="table table-sm">
                    <tr><td><strong>Nama:</strong></td><td>${data.nama}</td></tr>
                    <tr><td><strong>NIK:</strong></td><td>${data.nik}</td></tr>
                    <tr><td><strong>Telepon:</strong></td><td>${data.telp}</td></tr>
                    <tr><td><strong>Tanggal:</strong></td><td>${new Date(data.tgl_pengaduan).toLocaleDateString('id-ID')}</td></tr>
                </table>
            </div>
            <div class="col-md-6">
                <h6 class="text-primary">Status & Foto</h6>
                <p><strong>Status:</strong> ${getStatusBadge(data.status)}</p>
                ${data.foto ? `<img src="../database/img/${data.foto}" class="img-thumbnail" style="max-width: 200px;">` : '<p class="text-muted">Tidak ada foto</p>'}
            </div>
        </div>
        <div class="mt-3">
            <h6 class="text-primary">Judul Pengaduan</h6>
            <p><strong>${data.judul_pengaduan}</strong></p>
            <h6 class="text-primary">Isi Laporan</h6>
            <div class="border p-3 bg-light rounded">
                ${data.isi_laporan.replace(/\n/g, '<br>')}
            </div>
        </div>
        <div class="mt-3 border-top pt-3">
            ${getActionButtons(data)}
        </div>
    `;
    
    document.getElementById('detailPanel').style.display = 'block';
    document.getElementById('detailPanel').scrollIntoView({ behavior: 'smooth' });
}

function showVerifikasi(id) {
    document.getElementById('detailTitle').textContent = `Verifikasi Pengaduan #${id}`;
    document.getElementById('detailContent').innerHTML = `
        <form action="" method="POST" class="row g-3">
            <input type="hidden" name="id_pengaduan" value="${id}">
            <div class="col-md-6">
                <label class="form-label">Pilih Status:</label>
                <select class="form-select" name="status" required>
                    <option value="">-- Pilih Status --</option>
                    <option value="proses">Terima & Proses Pengaduan</option>
                    <option value="tolak">Tolak Pengaduan</option>
                </select>
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <button type="submit" name="kirim" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Update Status
                </button>
                <button type="button" class="btn btn-secondary ms-2" onclick="hideDetail()">Batal</button>
            </div>
        </form>
    `;
    
    document.getElementById('detailPanel').style.display = 'block';
    document.getElementById('detailPanel').scrollIntoView({ behavior: 'smooth' });
}

function showTanggapan(id) {
    document.getElementById('detailTitle').textContent = `Beri Tanggapan #${id}`;
    document.getElementById('detailContent').innerHTML = `
        <form action="" method="POST">
            <input type="hidden" name="id_pengaduan" value="${id}">
            <div class="mb-3">
                <label class="form-label">Tanggapan:</label>
                <textarea name="tanggapan" class="form-control" rows="4" 
                          placeholder="Masukkan tanggapan untuk pengaduan ini..." required></textarea>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" name="tanggapi" class="btn btn-success">
                    <i class="bi bi-send"></i> Kirim Tanggapan
                </button>
                <button type="button" class="btn btn-warning" onclick="if(confirm('Tandai pengaduan selesai?')) selesaikanPengaduan(${id})">
                    <i class="bi bi-check-all"></i> Tandai Selesai
                </button>
                <button type="button" class="btn btn-secondary" onclick="hideDetail()">Batal</button>
            </div>
        </form>
    `;
    
    document.getElementById('detailPanel').style.display = 'block';
    document.getElementById('detailPanel').scrollIntoView({ behavior: 'smooth' });
}

function selesaikanPengaduan(id) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.innerHTML = `
        <input type="hidden" name="id_pengaduan" value="${id}">
        <input type="hidden" name="selesaikan" value="1">
    `;
    document.body.appendChild(form);
    form.submit();
}

function hideDetail() {
    document.getElementById('detailPanel').style.display = 'none';
}

function getStatusBadge(status) {
    switch(status) {
        case 'selesai': return '<span class="badge bg-success">Selesai</span>';
        case 'proses': return '<span class="badge bg-warning text-dark">Proses</span>';
        case 'tolak': return '<span class="badge bg-danger">Ditolak</span>';
        default: return '<span class="badge bg-secondary">Menunggu</span>';
    }
}

function getActionButtons(data) {
    let buttons = '';
    
    if (data.status == '0' || data.status == 'menunggu' || !data.status) {
        buttons = `<button class="btn btn-warning" onclick="showVerifikasi(${data.id_pengaduan})">
                     <i class="bi bi-check-circle"></i> Verifikasi
                   </button>`;
    } else if (data.status == 'proses') {
        buttons = `
            <button class="btn btn-success me-2" onclick="showTanggapan(${data.id_pengaduan})">
                <i class="bi bi-chat-dots"></i> Beri Tanggapan
            </button>
            <button class="btn btn-warning" onclick="if(confirm('Tandai selesai?')) selesaikanPengaduan(${data.id_pengaduan})">
                <i class="bi bi-check-all"></i> Selesai
            </button>`;
    } else {
        buttons = '<div class="alert alert-info">Pengaduan sudah ditangani.</div>';
    }
    
    return buttons + `
        <hr>
        <button class="btn btn-outline-danger btn-sm" onclick="if(confirm('Yakin hapus pengaduan ini?')) hapusPengaduan(${data.id_pengaduan})">
            <i class="bi bi-trash"></i> Hapus Pengaduan
        </button>`;
}

function hapusPengaduan(id) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'edit_data.php';
    form.innerHTML = `
        <input type="hidden" name="id_pengaduan" value="${id}">
        <input type="hidden" name="hapus_pengaduan" value="1">
    `;
    document.body.appendChild(form);
    form.submit();
}
</script>