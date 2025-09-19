<?php
session_start();
include "../config/koneksi.php";

// ===============================
// HAPUS PENGADUAN
// ===============================
if (isset($_POST['hapus_pengaduan'])) {
    $id_pengaduan = intval($_POST['id_pengaduan']);

    // Mulai transaction
    mysqli_begin_transaction($conn);

    try {
        // Hapus tanggapan terlebih dahulu (foreign key constraint)
        mysqli_query($conn, "DELETE FROM tanggapan WHERE id_pengaduan = '$id_pengaduan'");
        
        // Hapus pengaduan
        mysqli_query($conn, "DELETE FROM pengaduan WHERE id_pengaduan = '$id_pengaduan'");

        // Commit transaction
        mysqli_commit($conn);
        echo "<script>alert('Data pengaduan berhasil dihapus'); window.location='index.php?page=pengaduan';</script>";
    } catch (Exception $e) {
        // Rollback jika error
        mysqli_rollback($conn);
        echo "<script>alert('Gagal menghapus data pengaduan'); window.location='index.php?page=pengaduan';</script>";
    }
    exit();
}

// ===============================
// HAPUS TANGGAPAN
// ===============================
if (isset($_POST['hapus_tanggapan'])) {
    $id_tanggapan = intval($_POST['id_tanggapan']);

    $query = mysqli_query($conn, "DELETE FROM tanggapan WHERE id_tanggapan = '$id_tanggapan'");

    if ($query) {
        echo "<script>alert('Data tanggapan berhasil dihapus'); window.location='index.php?page=tanggapan';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data tanggapan: " . mysqli_error($conn) . "'); window.location='index.php?page=tanggapan';</script>";
    }
    exit();
}

// ===============================
// HAPUS PETUGAS
// ===============================
if (isset($_POST['hapus_petugas'])) {
    $id_petugas = intval($_POST['id_petugas']);

    // Cek apakah petugas masih memiliki tanggapan
    $cek_tanggapan = mysqli_query($conn, "SELECT COUNT(*) as jumlah FROM tanggapan WHERE id_petugas = '$id_petugas'");
    $data_cek = mysqli_fetch_assoc($cek_tanggapan);

    if ($data_cek['jumlah'] > 0) {
        echo "<script>alert('Tidak dapat menghapus petugas karena masih memiliki tanggapan aktif'); window.location='index.php?page=petugas';</script>";
        exit();
    }

    $query = mysqli_query($conn, "DELETE FROM petugas WHERE id_petugas = '$id_petugas'");

    if ($query) {
        echo "<script>alert('Data petugas berhasil dihapus'); window.location='index.php?page=petugas';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data petugas: " . mysqli_error($conn) . "'); window.location='index.php?page=petugas';</script>";
    }
    exit();
}

// ===============================
// HAPUS MASYARAKAT
// ===============================
if (isset($_POST['hapus_masyarakat'])) {
    $nik = mysqli_real_escape_string($conn, $_POST['nik']);

    // Cek apakah masyarakat masih memiliki pengaduan
    $cek_pengaduan = mysqli_query($conn, "SELECT COUNT(*) as jumlah FROM pengaduan WHERE nik = '$nik'");
    $data_cek = mysqli_fetch_assoc($cek_pengaduan);

    if ($data_cek['jumlah'] > 0) {
        echo "<script>alert('Tidak dapat menghapus masyarakat karena masih memiliki pengaduan aktif'); window.location='index.php?page=masyarakat';</script>";
        exit();
    }

    $query = mysqli_query($conn, "DELETE FROM masyarakat WHERE nik = '$nik'");

    if ($query) {
        echo "<script>alert('Data masyarakat berhasil dihapus'); window.location='index.php?page=masyarakat';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data masyarakat: " . mysqli_error($conn) . "'); window.location='index.php?page=masyarakat';</script>";
    }
    exit();
}

// ===============================
// SIMPAN TANGGAPAN (INSERT)
// ===============================
if (isset($_POST['simpan_tanggapan'])) {
    $id_pengaduan = intval($_POST['id_pengaduan']);
    $tanggapan = mysqli_real_escape_string($conn, $_POST['tanggapan']);
    $tgl_tanggapan = date("Y-m-d H:i:s");

    // Ambil id_petugas dari session
    if (!isset($_SESSION['id_petugas'])) {
        echo "<script>alert('Petugas tidak terdeteksi, silakan login ulang'); window.location='../index.php?page=login';</script>";
        exit();
    }
    $id_petugas = intval($_SESSION['id_petugas']);

    // Insert ke tabel tanggapan
    $insert = mysqli_query($conn, "INSERT INTO tanggapan (id_pengaduan, tgl_tanggapan, tanggapan, id_petugas) 
                                   VALUES ('$id_pengaduan', '$tgl_tanggapan', '$tanggapan', '$id_petugas')");

    if ($insert) {
        // Update status pengaduan otomatis ke 'proses'
        mysqli_query($conn, "UPDATE pengaduan SET status = 'proses' WHERE id_pengaduan = '$id_pengaduan'");

        echo "<script>alert('Tanggapan berhasil disimpan'); window.location='index.php?page=pengaduan';</script>";
    } else {
        echo "<script>alert('Gagal menyimpan tanggapan: " . mysqli_error($conn) . "'); window.location='index.php?page=pengaduan';</script>";
    }
    exit();
}

// ===============================
// SELESAIKAN PENGADUAN
// ===============================
if (isset($_POST['selesaikan_pengaduan'])) {
    $id_pengaduan = intval($_POST['id_pengaduan']);

    $query = mysqli_query($conn, "UPDATE pengaduan SET status = 'selesai' WHERE id_pengaduan = '$id_pengaduan'");

    if ($query) {
        echo "<script>alert('Pengaduan berhasil ditandai selesai'); window.location='index.php?page=pengaduan';</script>";
    } else {
        echo "<script>alert('Gagal menandai pengaduan selesai: " . mysqli_error($conn) . "'); window.location='index.php?page=pengaduan';</script>";
    }
    exit();
}

// ===============================
// UPDATE STATUS PENGADUAN
// ===============================
if (isset($_POST['update_status'])) {
    $id_pengaduan = intval($_POST['id_pengaduan']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    $query = mysqli_query($conn, "UPDATE pengaduan SET status = '$status' WHERE id_pengaduan = '$id_pengaduan'");

    if ($query) {
        echo "<script>alert('Status pengaduan berhasil diupdate'); window.location='index.php?page=pengaduan';</script>";
    } else {
        echo "<script>alert('Gagal mengupdate status: " . mysqli_error($conn) . "'); window.location='index.php?page=pengaduan';</script>";
    }
    exit();
}

// Jika tidak ada aksi yang sesuai, redirect ke halaman utama
header("Location: index.php");
exit();
?>