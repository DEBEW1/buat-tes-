<?php
// export_tanggapan.php
require_once "../config/koneksi.php";

// Set header untuk download Excel
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Data_Pengaduan_Tanggapan_" . date('Y-m-d') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Query data gabungan pengaduan + tanggapan
$sql = "SELECT 
            t.id_tanggapan,
            t.tgl_tanggapan,
            p.nik,
            m.nama as nama_masyarakat,
            p.judul_pengaduan,
            p.isi_laporan,
            t.tanggapan,
            pt.nama_petugas,
            p.status,
            p.tgl_pengaduan
        FROM tanggapan t
        INNER JOIN pengaduan p ON t.id_pengaduan = p.id_pengaduan
        INNER JOIN masyarakat m ON p.nik = m.nik
        INNER JOIN petugas pt ON t.id_petugas = pt.id_petugas
        ORDER BY t.tgl_tanggapan DESC";

$query = mysqli_query($conn, $sql);

if (!$query) {
    die("Error: " . mysqli_error($conn));
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Laporan Pengaduan dan Tanggapan</title>
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; text-align: center; }
        .center { text-align: center; }
        .header { text-align: center; margin-bottom: 20px; }
    </style>
</head>
<body>

<div class="header">
    <h2>LAPORAN PENGADUAN DAN TANGGAPAN</h2>
    <h3>SISTEM INFORMASI PENGADUAN MASYARAKAT</h3>
    <p>Tanggal Export: <?= date('d F Y H:i:s'); ?></p>
</div>

<table>
    <thead>
        <tr>
            <th>No</th>
            <th>Tanggal Pengaduan</th>
            <th>Tanggal Tanggapan</th>
            <th>NIK</th>
            <th>Nama Masyarakat</th>
            <th>Judul Laporan</th>
            <th>Isi Laporan</th>
            <th>Tanggapan</th>
            <th>Petugas</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        <?php if (mysqli_num_rows($query) > 0): ?>
            <?php $no = 1; while ($data = mysqli_fetch_assoc($query)): ?>
                <tr>
                    <td class="center"><?= $no++; ?></td>
                    <td class="center"><?= date('d/m/Y H:i', strtotime($data['tgl_pengaduan'])); ?></td>
                    <td class="center"><?= date('d/m/Y H:i', strtotime($data['tgl_tanggapan'])); ?></td>
                    <td><?= htmlspecialchars($data['nik']); ?></td>
                    <td><?= htmlspecialchars($data['nama_masyarakat']); ?></td>
                    <td><?= htmlspecialchars($data['judul_pengaduan']); ?></td>
                    <td><?= htmlspecialchars($data['isi_laporan']); ?></td>
                    <td><?= htmlspecialchars($data['tanggapan']); ?></td>
                    <td><?= htmlspecialchars($data['nama_petugas']); ?></td>
                    <td class="center">
                        <?php
                        switch ($data['status']) {
                            case "selesai":
                                echo "Selesai";
                                break;
                            case "proses":
                                echo "Proses";
                                break;
                            case "tolak":
                                echo "Ditolak";
                                break;
                            default:
                                echo "Menunggu";
                        }
                        ?>
                    </td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="10" class="center">Tidak ada data tanggapan</td>
            </tr>
        <?php endif; ?>
    </tbody>
</table>

<div style="margin-top: 30px; font-size: 12px;">
    <p><strong>Keterangan Status:</strong></p>
    <ul>
        <li><strong>Menunggu:</strong> Pengaduan belum diverifikasi</li>
        <li><strong>Proses:</strong> Pengaduan sedang ditangani</li>
        <li><strong>Selesai:</strong> Pengaduan telah diselesaikan</li>
        <li><strong>Ditolak:</strong> Pengaduan ditolak</li>
    </ul>
    
    <p style="margin-top: 20px;">
        <strong>Total Data:</strong> <?= mysqli_num_rows($query); ?> tanggapan<br>
        <strong>Digenerate pada:</strong> <?= date('d F Y H:i:s'); ?>
    </p>
</div>

</body>
</html>

<?php
mysqli_close($conn);
?>