<?php
// masyarakat/proses_pengaduan.php
session_start();
require_once '../config/koneksi.php';

// Ambil koneksi PDO
$pdo = $db->getConnection();

// Cek login
if (!isset($_SESSION['login']) || $_SESSION['level'] != 'masyarakat') {
    header("Location: ../index.php?page=login");
    exit();
}

if (isset($_POST['submit'])) {
    $tgl_pengaduan = $_POST['tgl_pengaduan'] ?? date('Y-m-d H:i:s');
    $nik           = $_POST['nik'] ?? $_SESSION['user_id'];
    $judul         = trim($_POST['judul_laporan'] ?? '');
    $isi           = trim($_POST['isi_laporan'] ?? '');
    $foto          = null;

    // Validasi input teks
    if (strlen($judul) < 10 || strlen($isi) < 20) {
        $_SESSION['error'] = "Judul minimal 10 karakter dan isi laporan minimal 20 karakter.";
        header("Location: pengaduan.php");
        exit();
    }

    // Upload foto
    if (!empty($_FILES['foto']['name'])) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $maxSize = 2 * 1024 * 1024; // 2MB

        $fileName = $_FILES['foto']['name'];
        $fileTmp  = $_FILES['foto']['tmp_name'];
        $fileSize = $_FILES['foto']['size'];
        $ext      = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        // Validasi ekstensi
        if (!in_array($ext, $allowed)) {
            $_SESSION['error'] = "Format file tidak didukung! Hanya JPG, JPEG, PNG.";
            header("Location: pengaduan.php");
            exit();
        }

        // Validasi ukuran
        if ($fileSize > $maxSize) {
            $_SESSION['error'] = "Ukuran file terlalu besar! Maksimal 2MB.";
            header("Location: pengaduan.php");
            exit();
        }

        // Nama unik file
        $newName = date('YmdHis') . '_' . uniqid() . '.' . $ext;
        $uploadDir = "../database/img/"; // konsisten dengan admin & detail.php

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        if (move_uploaded_file($fileTmp, $uploadDir . $newName)) {
            $foto = $newName;
        } else {
            $_SESSION['error'] = "Gagal mengupload file.";
            header("Location: pengaduan.php");
            exit();
        }
    }

    // Simpan ke database
    try {
        $stmt = $pdo->prepare("INSERT INTO pengaduan 
            (tgl_pengaduan, nik, judul_pengaduan, isi_laporan, foto, status) 
            VALUES (?, ?, ?, ?, ?, '0')");
        $stmt->execute([$tgl_pengaduan, $nik, $judul, $isi, $foto]);

        $_SESSION['success'] = "Pengaduan berhasil dikirim.";
        header("Location: pengaduan.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
        header("Location: pengaduan.php");
        exit();
    }
} else {
    $_SESSION['error'] = "Akses tidak valid.";
    header("Location: pengaduan.php");
    exit();
}
