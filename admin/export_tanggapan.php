<?php
// export_pengaduan_tanggapan.php
require_once "../config/koneksi.php";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Data_Pengaduan_Tanggapan.xls");

$db = new Database();

// Ambil data gabungan pengaduan + tanggapan
$data = $db->fetchAll("
    SELECT 
        t.id_tanggapan,
        t.tgl_tanggapan,
        p.nik,
        p.judul_pengaduan,
        p.isi_laporan,
        t.tanggapan,
        p.status
    FROM tanggapan t
    INNER JOIN pengaduan p ON t.id_pengaduan = p.id_pengaduan
    ORDER BY t.id_tanggapan ASC
");
?>

<center>
    <h3>LAPORAN PENGADUAN DAN TANGGAPAN <br>KELURAHAN PONDOK JAYA</h3>
</center>

<table border="1" cellpadding="10" cellspacing="0">
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal Tanggapan</th>
            <th>NIK</th>
            <th>Judul Laporan</th>
            <th>Isi Laporan</th>
            <th>Tanggapan</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if ($data): ?>
            <?php $no = 1; foreach ($data as $row): ?>
                <tr>
                    <td><?= $no++; ?></td>
                    <td><?= htmlspecialchars($row['tgl_tanggapan']); ?></td>
                    <td><?= htmlspecialchars($row['nik']); ?></td>
                    <td><?= htmlspecialchars($row['judul_pengaduan']); ?></td>
                    <td><?= htmlspecialchars($row['isi_laporan']); ?></td>
                    <td><?= htmlspecialchars($row['tanggapan']); ?></td>
                    <td>
                        <?php
                        if ($row['status'] === "selesai") {
                            echo "Selesai";
                        } elseif ($row['status'] === "proses") {
                            echo "Proses";
                        } elseif ($row['status'] === "tolak") {
                            echo "Ditolak";
                        } else {
                            echo "Menunggu";
                        }
                        ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="7" align="center">Tidak ada data.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
