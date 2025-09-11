-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 11, 2025 at 03:58 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_apem`
--

-- --------------------------------------------------------

--
-- Table structure for table `masyarakat`
--

CREATE TABLE `masyarakat` (
  `nik` char(16) NOT NULL,
  `nama` varchar(35) NOT NULL,
  `username` varchar(25) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telp` varchar(13) NOT NULL,
  `foto_profil` varchar(255) DEFAULT 'default.png',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `masyarakat`
--

INSERT INTO `masyarakat` (`nik`, `nama`, `username`, `password`, `telp`, `foto_profil`, `created_at`, `updated_at`) VALUES
('1122334455667788', 'rikiriki', 'riki12', '$2y$12$g8sZHU811AOhmYXIt9lPw.4IffCxyNJ1rMjXIklh0k59vJjXlzbO6', '0982212121212', 'default.png', '2025-09-10 05:49:16', '2025-09-10 05:49:16'),
('1213141617181910', 'adil', 'adil12', '$2y$12$djqWivcug2Ztd3.18sUh5upeL6fln8wnP2tjIUdXbJtjSfBOwOf1u', '0988290172285', 'default.png', '2025-09-10 18:22:43', '2025-09-10 18:22:43'),
('1234567890123456', 'adilwibowo', 'adilwibowo', '$2y$12$ognk79LC729xM9gyZ.1h3ucf68DLdPWWmDUrcsTfheDYNEPDBq36e', '08976544321', 'default.png', '2025-09-10 04:51:03', '2025-09-10 04:51:03');

-- --------------------------------------------------------

--
-- Table structure for table `pengaduan`
--

CREATE TABLE `pengaduan` (
  `id_pengaduan` int(11) NOT NULL,
  `tgl_pengaduan` datetime NOT NULL,
  `nik` char(16) NOT NULL,
  `judul_pengaduan` varchar(255) DEFAULT NULL,
  `isi_laporan` text NOT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `status` enum('0','proses','selesai') DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pengaduan`
--

INSERT INTO `pengaduan` (`id_pengaduan`, `tgl_pengaduan`, `nik`, `judul_pengaduan`, `isi_laporan`, `foto`, `status`, `created_at`, `updated_at`) VALUES
(2, '2025-09-10 00:00:00', '1122334455667788', NULL, 'dddddddddddddddddddddddddddddddddddddddddddddddddddddddd1 | ddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddddd', '1122334455667788_1757523219_68c1ad13aa3ef.jpg', 'selesai', '2025-09-10 16:53:39', '2025-09-10 19:57:52'),
(3, '2025-09-10 00:00:00', '1213141617181910', NULL, 'Adanya Kerusakan jalan pada gang hall | rusak jalan nya di jalan amabatukam dan rusdi lagi memakan apple hijau', '1213141617181910_1757528886_68c1c33634e8c.jpeg', 'selesai', '2025-09-10 18:28:06', '2025-09-10 19:45:53'),
(4, '2025-09-10 00:00:00', '1213141617181910', 'dddddddddddddddddddddddddddddddddddddddddddddddddddddddd1', 'dddddddddddddd111111111111111111111111111111111111', '20250910204846_68c1c80ec968e.jpeg', 'selesai', '2025-09-10 18:48:46', '2025-09-10 19:36:41'),
(5, '2025-09-10 00:00:00', '1213141617181910', 'adadadadadaddaad', 'adadadaddadadadadadadadadadadada', '20250910212605_68c1d0cde8862.jpeg', 'selesai', '2025-09-10 19:26:05', '2025-09-10 19:32:18'),
(6, '2025-09-10 00:00:00', '1213141617181910', 'si rusdi ada pencuri', 'dadqqqqqqqqqq212121212212121212', '20250910221521_68c1dc59eacdd.jpeg', '0', '2025-09-10 20:15:21', '2025-09-10 20:15:21'),
(7, '2025-09-10 00:00:00', '1213141617181910', 'f2222222222222222', '2311314414214124124124124124', '20250910221924_68c1dd4c6cdb7.jpeg', 'proses', '2025-09-10 20:19:24', '2025-09-11 06:57:58'),
(8, '2025-09-11 00:00:00', '1213141617181910', 'jembut bau bener', 'wkwkwkwkwkwkkwkwkwkwkwkwk', '20250911135041_68c2b791d7644.jpeg', 'proses', '2025-09-11 11:50:41', '2025-09-11 12:02:45'),
(9, '2025-09-11 00:00:00', '1213141617181910', 'pusing bener anjing', 'aww1w1w1w2f1efcrwsdvfecdgfdfvcdvgcdfgvdcddvfgdv', '20250911140120_68c2ba1060bd2.jpeg', 'proses', '2025-09-11 12:01:20', '2025-09-11 13:43:25'),
(10, '2025-09-11 00:00:00', '1213141617181910', 'wwwwwwwwwwwwwwwwwwwwwwwwwwwwwww', 'wggggggggggggggggggggggggggggggggggggggg', '20250911153040_68c2cf00e316b.jpeg', '0', '2025-09-11 13:30:40', '2025-09-11 13:30:40');

-- --------------------------------------------------------

--
-- Table structure for table `petugas`
--

CREATE TABLE `petugas` (
  `id_petugas` int(11) NOT NULL,
  `nama_petugas` varchar(35) NOT NULL,
  `username` varchar(25) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telp` varchar(13) NOT NULL,
  `level` enum('admin','petugas') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `petugas`
--

INSERT INTO `petugas` (`id_petugas`, `nama_petugas`, `username`, `password`, `telp`, `level`, `created_at`, `updated_at`) VALUES
(7, 'Super Administrator', 'admin', '$2y$10$y7NvWpOyoKAqw/S.MZgZp.p804Txjw5uPF4za1k.dG.u//avGy5Q2', '081234567890', 'admin', '2025-09-10 17:17:55', '2025-09-10 17:17:55'),
(8, 'Petugas Layanan', 'petugas1', '$2y$10$68ez.EtZ3Vf3DcUQ5ht7A.SpGinH/m.EERE7xExmfcK2e2apKdOdO', '081234567891', 'petugas', '2025-09-10 17:17:55', '2025-09-10 17:17:55');

-- --------------------------------------------------------

--
-- Table structure for table `tanggapan`
--

CREATE TABLE `tanggapan` (
  `id_tanggapan` int(11) NOT NULL,
  `id_pengaduan` int(11) NOT NULL,
  `tgl_tanggapan` date NOT NULL,
  `tanggapan` text NOT NULL,
  `id_petugas` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `masyarakat`
--
ALTER TABLE `masyarakat`
  ADD PRIMARY KEY (`nik`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `pengaduan`
--
ALTER TABLE `pengaduan`
  ADD PRIMARY KEY (`id_pengaduan`),
  ADD KEY `nik` (`nik`);

--
-- Indexes for table `petugas`
--
ALTER TABLE `petugas`
  ADD PRIMARY KEY (`id_petugas`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `tanggapan`
--
ALTER TABLE `tanggapan`
  ADD PRIMARY KEY (`id_tanggapan`),
  ADD KEY `id_pengaduan` (`id_pengaduan`),
  ADD KEY `id_petugas` (`id_petugas`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `pengaduan`
--
ALTER TABLE `pengaduan`
  MODIFY `id_pengaduan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `petugas`
--
ALTER TABLE `petugas`
  MODIFY `id_petugas` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tanggapan`
--
ALTER TABLE `tanggapan`
  MODIFY `id_tanggapan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pengaduan`
--
ALTER TABLE `pengaduan`
  ADD CONSTRAINT `pengaduan_ibfk_1` FOREIGN KEY (`nik`) REFERENCES `masyarakat` (`nik`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tanggapan`
--
ALTER TABLE `tanggapan`
  ADD CONSTRAINT `tanggapan_ibfk_1` FOREIGN KEY (`id_pengaduan`) REFERENCES `pengaduan` (`id_pengaduan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `tanggapan_ibfk_2` FOREIGN KEY (`id_petugas`) REFERENCES `petugas` (`id_petugas`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
