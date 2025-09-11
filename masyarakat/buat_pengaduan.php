<form action="simpan_pengaduan.php" method="POST" enctype="multipart/form-data">
    <label>Judul</label>
    <input type="text" name="judul_pengaduan" required><br>

    <label>Isi Laporan</label>
    <textarea name="isi_laporan" required></textarea><br>

    <label>Foto</label>
    <input type="file" name="foto"><br>

    <button type="submit" name="kirim">Kirim</button>
</form>
