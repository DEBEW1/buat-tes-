<?php
// Proses tambah data masyarakat
if (isset($_POST["kirim"])) {
    // Validasi input
    $nik = htmlspecialchars(trim($_POST["nik"]));
    $nama = htmlspecialchars(trim($_POST["nama"]));
    $username = htmlspecialchars(trim($_POST["username"]));
    $password = htmlspecialchars(trim($_POST["password"]));
    $telp = htmlspecialchars(trim($_POST["telp"]));
    $level = 'masyarakat';

    // Cek apakah NIK sudah ada
    $cek_nik = mysqli_query($conn, "SELECT nik FROM masyarakat WHERE nik = '$nik'");
    if (mysqli_num_rows($cek_nik) > 0) {
        echo "<script>alert('NIK sudah terdaftar!'); window.location='index.php?page=masyarakat';</script>";
        exit();
    }

    // Cek apakah username sudah ada
    $cek_username = mysqli_query($conn, "SELECT username FROM masyarakat WHERE username = '$username'");
    if (mysqli_num_rows($cek_username) > 0) {
        echo "<script>alert('Username sudah digunakan!'); window.location='index.php?page=masyarakat';</script>";
        exit();
    }

    // Hash password
    $password_hash = md5($password);

    // Insert data
    $query = mysqli_query($conn, "INSERT INTO masyarakat (nik, nama, username, password, telp, level) 
                                  VALUES ('$nik', '$nama', '$username', '$password_hash', '$telp', '$level')");

    if ($query) {
        echo "<script>alert('Data berhasil ditambahkan'); window.location='index.php?page=masyarakat';</script>";
    } else {
        echo "<script>alert('Data gagal ditambahkan: " . mysqli_error($conn) . "'); window.location='index.php?page=masyarakat';</script>";
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="card shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-people"></i> DATA MASYARAKAT</h5>
                <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#tambah">
                    <i class="bi bi-plus-circle"></i> Tambah Data
                </button>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover align-middle">
                        <thead class="table-primary text-center">
                            <tr>
                                <th>No</th>
                                <th>NIK</th>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Telepon</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $query = mysqli_query($conn, "SELECT * FROM masyarakat ORDER BY nama ASC");
                            $no = 1;
                            while ($data = mysqli_fetch_array($query)) {
                            ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td class="text-center"><?= htmlspecialchars($data['nik']); ?></td>
                                    <td><?= htmlspecialchars($data['nama']); ?></td>
                                    <td><?= htmlspecialchars($data['username']); ?></td>
                                    <td><?= htmlspecialchars($data['telp']); ?></td>
                                    <td class="text-center">
                                        <button class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#hapus<?= $data['nik'] ?>">
                                            <i class="bi bi-trash"></i> Hapus
                                        </button>

                                        <!-- Modal Hapus -->
                                        <div class="modal fade" id="hapus<?= $data['nik'] ?>" tabindex="-1" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-danger text-white">
                                                        <h5 class="modal-title">Konfirmasi Hapus</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <form action="edit_data.php" method="POST">
                                                            <input type="hidden" name="nik" value="<?= $data['nik']; ?>">
                                                            <p>Apakah Anda yakin ingin menghapus data:</p>
                                                            <div class="alert alert-info">
                                                                <strong>NIK:</strong> <?= htmlspecialchars($data['nik']); ?><br>
                                                                <strong>Nama:</strong> <?= htmlspecialchars($data['nama']); ?>
                                                            </div>
                                                            <p class="text-warning"><small><i class="bi bi-exclamation-triangle"></i> Data yang dihapus tidak dapat dikembalikan!</small></p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" name="hapus_masyarakat" class="btn btn-danger">Hapus</button>
                                                    </div>
                                                        </form>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Data -->
<div class="modal fade" id="tambah" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Tambah Data Masyarakat</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form action="" method="POST" id="formTambahMasyarakat">
                    <div class="mb-3">
                        <label class="form-label">NIK <span class="text-danger">*</span></label>
                        <input type="number" name="nik" class="form-control" placeholder="Masukan NIK (16 digit)" 
                               required autofocus autocomplete="off" maxlength="16" minlength="16">
                        <small class="text-muted">NIK harus 16 digit</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                        <input type="text" name="nama" class="form-control" placeholder="Masukan Nama Lengkap" 
                               required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Username <span class="text-danger">*</span></label>
                        <input type="text" name="username" class="form-control" placeholder="Masukan Username" 
                               required autocomplete="off" minlength="4">
                        <small class="text-muted">Minimal 4 karakter</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password <span class="text-danger">*</span></label>
                        <input type="password" name="password" class="form-control" placeholder="Masukan Password" 
                               required autocomplete="off" minlength="6">
                        <small class="text-muted">Minimal 6 karakter</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">No. Telepon <span class="text-danger">*</span></label>
                        <input type="number" name="telp" class="form-control" placeholder="Masukan No. Telepon" 
                               required autocomplete="off">
                    </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="kirim" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Simpan
                </button>
            </div>
                </form>
        </div>
    </div>
</div>

<script>
// Validasi NIK 16 digit
document.getElementById('formTambahMasyarakat').addEventListener('submit', function(e) {
    const nik = document.querySelector('input[name="nik"]').value;
    if (nik.length !== 16) {
        e.preventDefault();
        alert('NIK harus 16 digit!');
        return false;
    }
});
</script>