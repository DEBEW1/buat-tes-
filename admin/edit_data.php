<?php
session_start();
include "../config/koneksi.php";

// ===============================
// HAPUS PENGADUAN
// ===============================
if (isset($_POST['hapus_pengaduan'])) {
    $id_pengaduan = intval($_POST['id_pengaduan']);

    // Hapus juga data tanggapan terkait pengaduan ini
    mysqli_query($conn, "DELETE FROM tanggapan WHERE id_pengaduan = '$id_pengaduan'");
    mysqli_query($conn, "DELETE FROM pengaduan WHERE id_pengaduan = '$id_pengaduan'");

    echo "<script>alert('Data pengaduan berhasil dihapus'); document.location.href='index.php?page=pengaduan';</script>";
    exit();
}

// ===============================
// HAPUS TANGGAPAN
// ===============================
if (isset($_POST['hapus_tanggapan'])) {
    $id_tanggapan = intval($_POST['id_tanggapan']);

    mysqli_query($conn, "DELETE FROM tanggapan WHERE id_tanggapan = '$id_tanggapan'");

    echo "<script>alert('Data tanggapan berhasil dihapus'); document.location.href='index.php?page=tanggapan';</script>";
    exit();
}

// ===============================
// HAPUS PETUGAS
// ===============================
if (isset($_POST['hapus_petugas'])) {
    $id_petugas = intval($_POST['id_petugas']);

    mysqli_query($conn, "DELETE FROM petugas WHERE id_petugas = '$id_petugas'");

    echo "<script>alert('Data petugas berhasil dihapus'); document.location.href='index.php?page=petugas';</script>";
    exit();
}

// ===============================
// HAPUS MASYARAKAT
// ===============================
if (isset($_POST['hapus_masyarakat'])) {
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);

    mysqli_query($conn, "DELETE FROM masyarakat WHERE nik = '$nik'");

    echo "<script>alert('Data masyarakat berhasil dihapus'); document.location.href='index.php?page=masyarakat';</script>";
    exit();
}

// ===============================
// SIMPAN TANGGAPAN (INSERT)
// ===============================
if (isset($_POST['simpan_tanggapan'])) {
    $id_pengaduan = intval($_POST['id_pengaduan']);
    $tanggapan    = mysqli_real_escape_string($conn, $_POST['tanggapan']);
    $tgl_tanggapan = date("Y-m-d");

    // Ambil id_petugas dari session
    if (!isset($_SESSION['id_petugas'])) {
        echo "<script>alert('Petugas tidak terdeteksi, silakan login ulang'); document.location.href='../index.php?page=login';</script>";
        exit();
    }
    $id_petugas = intval($_SESSION['id_petugas']);

    // Insert ke tabel tanggapan
    $insert = mysqli_query($conn, "INSERT INTO tanggapan (id_pengaduan, tgl_tanggapan, tanggapan, id_petugas) 
                                   VALUES ('$id_pengaduan', '$tgl_tanggapan', '$tanggapan', '$id_petugas')");

    if ($insert) {
        // Update status pengaduan otomatis ke 'proses' jika belum selesai
        mysqli_query($conn, "UPDATE pengaduan SET status = 'proses' WHERE id_pengaduan = '$id_pengaduan' AND status != 'selesai'");

        echo "<script>alert('Tanggapan berhasil disimpan'); document.location.href='index.php?page=tanggapan';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan tanggapan'); document.location.href='index.php?page=pengaduan';</script>";
    }
    exit();
}

// ===============================
// SELESAIKAN PENGADUAN
// ===============================
if (isset($_POST['selesaikan_pengaduan'])) {
    $id_pengaduan = intval($_POST['id_pengaduan']);

    mysqli_query($conn, "UPDATE pengaduan SET status = 'selesai' WHERE id_pengaduan = '$id_pengaduan'");

    echo "<script>alert('Pengaduan berhasil ditandai selesai'); document.location.href='index.php?page=pengaduan';</script>";
    exit();
}
?>
