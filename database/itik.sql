-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Nov 10, 2026 at 06:36 AM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `itik`
--

-- --------------------------------------------------------

--
-- Table structure for table `anggota_kelas`
--

CREATE TABLE `anggota_kelas` (
  `id_anggota_kelas` bigint UNSIGNED NOT NULL,
  `id_kelas` varchar(50) NOT NULL,
  `id_user` varchar(50) NOT NULL COMMENT 'role=siswa',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `anggota_kelas`
--

INSERT INTO `anggota_kelas` (`id_anggota_kelas`, `id_kelas`, `id_user`, `created_at`) VALUES
(1, 'KLS001', 'U002', '2026-10-28 19:32:59'),
(2, 'KLS001', 'U004', '2026-10-29 06:05:38'),
(3, 'KLS003', 'U005', '2026-10-29 06:54:31'),
(4, 'KLS004', 'U005', '2026-10-29 07:03:58'),
(5, 'KLS004', 'U006', '2026-10-29 07:31:02'),
(6, 'KLS001', 'U008', '2026-11-10 02:05:13');

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `draft_jawaban`
--

CREATE TABLE `draft_jawaban` (
  `id_draft` int NOT NULL,
  `id_kuis` varchar(50) NOT NULL,
  `id_user` varchar(50) NOT NULL COMMENT 'Siswa yang mengerjakan',
  `id_soal` varchar(50) NOT NULL,
  `jawaban_dipilih` varchar(5) DEFAULT NULL COMMENT 'A/B/C/D/E',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `draft_jawaban`
--

INSERT INTO `draft_jawaban` (`id_draft`, `id_kuis`, `id_user`, `id_soal`, `jawaban_dipilih`, `updated_at`) VALUES
(71, 'K003', 'U002', 'S009', 'B', '2026-10-31 05:32:25'),
(72, 'K003', 'U002', 'S010', 'A', '2026-10-31 05:32:26'),
(73, 'K003', 'U002', 'S011', 'A', '2026-10-31 05:32:27'),
(74, 'K003', 'U002', 'S012', 'B', '2026-10-31 05:32:28'),
(75, 'K003', 'U002', 'S013', 'A', '2026-10-31 05:32:29'),
(76, 'K003', 'U002', 'S014', 'C', '2026-10-31 05:32:30'),
(77, 'K003', 'U002', 'S015', 'A', '2026-10-31 05:32:31'),
(78, 'K005', 'U002', 'S019', 'A', '2026-10-31 08:09:16'),
(79, 'K005', 'U002', 'S020', 'B', '2026-10-31 08:09:18'),
(80, 'K005', 'U002', 'S021', 'B', '2026-10-31 08:09:19'),
(81, 'K005', 'U002', 'S022', 'A', '2026-10-31 08:09:19'),
(82, 'K005', 'U002', 'S023', 'C', '2026-10-31 08:09:22'),
(86, 'K006', 'U002', 'S024', 'B', '2026-10-31 15:33:43'),
(87, 'K006', 'U002', 'S025', 'A', '2026-10-31 15:33:46'),
(88, 'K006', 'U002', 'S026', 'D', '2026-10-31 15:33:54'),
(89, 'K006', 'U002', 'S027', 'A', '2026-10-31 15:34:00'),
(90, 'K006', 'U002', 'S028', 'B', '2026-10-31 15:34:14'),
(96, 'K002', 'U002', 'S004', 'B', '2026-10-31 22:54:36'),
(97, 'K002', 'U002', 'S005', 'B', '2026-10-31 22:54:36'),
(98, 'K002', 'U002', 'S006', 'B', '2026-10-31 22:54:38'),
(99, 'K001', 'U002', 'S001', 'B', '2026-11-06 00:11:09'),
(100, 'K001', 'U002', 'S002', 'C', '2026-11-06 00:11:11'),
(101, 'K001', 'U002', 'S003', 'A', '2026-11-06 00:11:12'),
(102, 'K005', 'U008', 'S019', 'A', '2026-11-09 20:18:24'),
(103, 'K005', 'U008', 'S020', 'C', '2026-11-09 20:18:26'),
(104, 'K005', 'U008', 'S021', 'A', '2026-11-09 20:18:28'),
(105, 'K005', 'U008', 'S022', 'A', '2026-11-09 20:18:29'),
(106, 'K005', 'U008', 'S023', 'A', '2026-11-09 20:18:30'),
(116, 'K001', 'U008', 'S001', 'A', '2026-11-09 21:21:49');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint UNSIGNED NOT NULL,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint UNSIGNED NOT NULL,
  `reserved_at` int UNSIGNED DEFAULT NULL,
  `available_at` int UNSIGNED NOT NULL,
  `created_at` int UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kelas`
--

CREATE TABLE `kelas` (
  `id_kelas` varchar(50) NOT NULL,
  `nama_kelas` varchar(50) NOT NULL COMMENT 'Contoh: X TKJ 1',
  `tingkat` varchar(10) NOT NULL COMMENT 'X / XI / XII',
  `id_tahun_ajaran` varchar(50) NOT NULL,
  `is_aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kelas`
--

INSERT INTO `kelas` (`id_kelas`, `nama_kelas`, `tingkat`, `id_tahun_ajaran`, `is_aktif`, `created_at`, `updated_at`) VALUES
('KLS001', 'X TKJ 1', 'X', 'TA001', 1, '2026-10-28 19:32:59', '2026-10-28 19:32:59'),
('KLS002', 'X TKJ 2', 'X', 'TA001', 1, '2026-10-28 19:32:59', '2026-10-28 19:32:59'),
('KLS003', 'XI TKJ 1', 'XI', 'TA002', 1, '2026-10-28 23:51:41', '2026-10-28 23:51:41'),
('KLS004', 'XI TKJ 1', 'XI', 'TA001', 1, '2026-10-29 00:03:52', '2026-10-29 00:03:52');

-- --------------------------------------------------------

--
-- Table structure for table `kuis`
--

CREATE TABLE `kuis` (
  `id_kuis` varchar(50) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `deskripsi` text,
  `kategori` varchar(50) NOT NULL COMMENT 'post-test/pre-test/kuis harian/ulangan/remedial',
  `tingkat_kesulitan` enum('mudah','sedang','sulit') NOT NULL DEFAULT 'mudah' COMMENT 'Level kuis; naik otomatis jika siswa lulus KKM',
  `id_mapel` varchar(50) NOT NULL,
  `id_tahun_ajaran` varchar(50) NOT NULL,
  `alokasi_waktu` int UNSIGNED DEFAULT NULL COMMENT 'Batas waktu pengerjaan dalam menit',
  `kkm` decimal(5,2) DEFAULT NULL COMMENT 'Nilai minimum kelulusan (KKM) untuk kuis ini',
  `waktu_mulai` datetime DEFAULT NULL COMMENT 'Khusus kategori ulangan: kapan ulangan mulai bisa diakses semua siswa',
  `waktu_selesai` datetime DEFAULT NULL COMMENT 'Khusus kategori ulangan: kapan ulangan ditutup, tidak bisa diakses lagi',
  `id_user` varchar(50) NOT NULL COMMENT 'Guru/operator pembuat kuis (remedial khusus guru)',
  `status_publikasi` varchar(20) NOT NULL DEFAULT 'draft' COMMENT 'draft/terbit - validasi isi soal, bukan nilai',
  `id_publisher` varchar(50) DEFAULT NULL COMMENT 'Guru yang mempublikasikan/memvalidasi kuis ini',
  `is_aktif` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Soft delete: 0 = diarsipkan/dihapus',
  `tampil_jawaban` enum('tidak','setelah_submit','setelah_kkm') NOT NULL DEFAULT 'setelah_submit' COMMENT 'Kapan siswa boleh melihat benar/salah per soal',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kuis`
--

INSERT INTO `kuis` (`id_kuis`, `judul`, `deskripsi`, `kategori`, `tingkat_kesulitan`, `id_mapel`, `id_tahun_ajaran`, `alokasi_waktu`, `kkm`, `waktu_mulai`, `waktu_selesai`, `id_user`, `status_publikasi`, `id_publisher`, `is_aktif`, `created_at`) VALUES
('K001', 'Kuis Harian Bab 1 - Tingkat Mudah', 'Uji pemahaman dasar mengenai pengertian jaringan komputer dan pengenalan jenis topologi.', 'Kuis Harian', 'mudah', 'MP001', 'TA001', 15, 75.00, NULL, NULL, 'U001', 'terbit', 'U001', 1, '2026-10-31 01:46:04'),
('K002', 'Kuis Harian Bab 1 - Tingkat Sedang', 'Uji pemahaman mendalam karakteristik, kelebihan, dan kelemahan berbagai arsitektur topologi jaringan.', 'Kuis Harian', 'sedang', 'MP001', 'TA001', 20, 75.00, NULL, NULL, 'U001', 'draft', NULL, 1, '2026-10-31 01:46:04'),
('K003', 'Kuis Harian Bab 1 - Tingkat Sulit', 'Uji analisis troubleshooting arsitektur jaringan, optimasi collision domain, dan manajemen hierarki switch.', 'Kuis Harian', 'sulit', 'MP001', 'TA001', 25, 75.00, NULL, NULL, 'U001', 'terbit', 'U001', 1, '2026-10-31 01:46:04'),
('K004', 'Kuis Harian Bab 2 - Tingkat Mudah', 'Uji pemahaman dasar mengenai pengkabelan UTP dan konsep pengalamatan IPv4.', 'Kuis Harian', 'mudah', 'MP001', 'TA001', 15, 75.00, NULL, NULL, 'U001', 'terbit', 'U001', 1, '2026-10-31 01:46:04'),
('K005', 'Ulangan Harian 1 - Dasar Jaringan Komputer', 'Ulangan evaluasi materi Bab 1 (Dasar Jaringan Komputer dan Topologi Jaringan). Siswa yang mendapatkan nilai di bawah KKM (75.00) wajib mengikuti remedial.', 'ulangan', 'sedang', 'MP001', 'TA001', 20, 75.00, '2026-10-01 00:00:00', '2026-12-31 23:59:59', 'U001', 'terbit', 'U001', 1, '2026-10-31 14:50:52'),
('K006', 'Remedial Ulangan Harian 1 - Dasar Jaringan Komputer', 'Kuis remedial untuk perbaikan nilai Ulangan Harian 1. Dikhususkan bagi siswa yang belum mencapai KKM (75.00).', 'remedial', 'sedang', 'MP001', 'TA001', 20, 75.00, NULL, NULL, 'U001', 'terbit', 'U001', 1, '2026-10-31 14:50:52'),
('K007', 'Ulangan coba 2', 'coba coba', 'ulangan', 'sedang', 'MP001', 'TA001', 60, 74.99, '2026-10-31 23:09:00', '2026-11-01 23:05:00', 'U001', 'draft', NULL, 1, '2026-10-31 16:06:39'),
('K008', 'coba kuis', 'a[pa aja', 'kuis harian', 'sedang', 'MP002', 'TA001', 20, 75.00, NULL, NULL, 'U001', 'draft', NULL, 1, '2026-11-01 03:44:13'),
('K009', 'sqxsac', 'cdswxs', 'kuis harian', 'mudah', 'MP001', 'TA001', 60, 74.97, '2026-11-01 14:00:00', '2026-11-01 14:04:00', 'U001', 'draft', NULL, 1, '2026-11-01 05:26:28');

-- --------------------------------------------------------

--
-- Table structure for table `mapel`
--

CREATE TABLE `mapel` (
  `id_mapel` varchar(50) NOT NULL,
  `kode_mapel` varchar(20) NOT NULL COMMENT 'Contoh: TIK',
  `nama_mapel` varchar(100) NOT NULL,
  `deskripsi` text,
  `is_aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `mapel`
--

INSERT INTO `mapel` (`id_mapel`, `kode_mapel`, `nama_mapel`, `deskripsi`, `is_aktif`, `created_at`, `updated_at`) VALUES
('MP001', 'TIK', 'Teknologi Informasi dan Komunikasi', 'Mata pelajaran TIK untuk jurusan TKJ.', 1, '2026-10-28 19:32:59', '2026-10-28 19:32:59'),
('MP002', 'BINDO', 'Bahasa Indonesia', 'mata pelajaran bahasa  indonesia', 1, '2026-10-28 23:49:54', '2026-10-28 23:50:03'),
('MP003', 'SR-001', 'Server', 'server', 1, '2026-10-29 00:03:30', '2026-10-29 00:03:30'),
('MP004', 'MTKO2', 'Matematika', NULL, 1, '2026-11-05 23:52:51', '2026-11-05 23:52:51');

-- --------------------------------------------------------

--
-- Table structure for table `mapel_kelas`
--

CREATE TABLE `mapel_kelas` (
  `id_mapel_kelas` bigint UNSIGNED NOT NULL,
  `id_mapel` varchar(50) NOT NULL,
  `id_kelas` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `mapel_kelas`
--

INSERT INTO `mapel_kelas` (`id_mapel_kelas`, `id_mapel`, `id_kelas`, `created_at`) VALUES
(1, 'MP001', 'KLS001', '2026-10-28 19:32:59'),
(2, 'MP001', 'KLS002', '2026-10-28 19:32:59'),
(3, 'MP002', 'KLS003', '2026-10-29 06:51:41'),
(4, 'MP001', 'KLS003', '2026-10-29 06:51:41'),
(5, 'MP003', 'KLS004', '2026-10-29 07:03:52'),
(6, 'MP002', 'KLS001', '2026-11-09 13:20:24'),
(7, 'MP004', 'KLS001', '2026-11-09 13:20:24'),
(8, 'MP003', 'KLS001', '2026-11-09 13:20:24');

-- --------------------------------------------------------

--
-- Table structure for table `materi`
--

CREATE TABLE `materi` (
  `id_materi` varchar(50) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `bab` varchar(50) DEFAULT NULL,
  `kelas` varchar(50) DEFAULT NULL,
  `id_mapel` varchar(50) NOT NULL,
  `deskripsi` text,
  `isi_materi` varchar(255) DEFAULT NULL COMMENT 'Path/nama file PDF materi, contoh: uploads/materi/materi01.pdf',
  `link_youtube` varchar(255) DEFAULT NULL,
  `id_user` varchar(50) NOT NULL COMMENT 'Guru/operator yang mengelola materi ini',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `materi`
--

INSERT INTO `materi` (`id_materi`, `judul`, `bab`, `kelas`, `id_mapel`, `deskripsi`, `isi_materi`, `link_youtube`, `id_user`, `created_at`, `updated_at`) VALUES
('M001', 'Dasar Jaringan Komputer dan Topologi Jaringan', 'Bab 1', 'X', 'MP001', 'Mempelajari konsep dasar jaringan komputer, jenis-jenis jaringan (LAN, MAN, WAN), perangkat jaringan (Hub, Switch, Router), serta perbandingan topologi Star, Bus, Ring, dan Mesh.', 'uploads/materi/M001_algoritma.pdf', 'https://www.youtube.com/watch?v=3Q_oYBoorCE', 'U001', '2026-10-31 01:46:04', '2026-10-31 01:46:04'),
('M002', 'Pengkabelan Jaringan dan Pengalamatan IP Address', 'Bab 2', 'X', 'MP001', 'Mempelajari standar urutan warna kabel UTP (T568A dan T568B), jenis kabel straight vs crossover, serta konsep pengalamatan IPv4 kelas A, B, C dan Subnet Mask dasar.', 'uploads/materi/M002_struktur_data.pdf', NULL, 'U001', '2026-10-31 01:46:04', '2026-10-31 01:46:04'),
('M003', 'coba materi', 'bab 1', 'X', 'MP002', 'apa saja', 'materi/cjm4fywtzkEKsMfDcDbwaqehJ5Hm5Z23pEigvyfy.pdf', 'https://youtu.be/SlSzGxrsqsU?si=ieHCu8ngkZT4wiaC', 'U001', '2026-10-31 20:41:40', '2026-10-31 20:41:40');

-- --------------------------------------------------------

--
-- Table structure for table `materis`
--

CREATE TABLE `materis` (
  `id_materi` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `judul` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `bab` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `kelas` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_unicode_ci,
  `isi_materi` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Path/nama file PDF materi',
  `link_youtube` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_user` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Guru/operator yang mengelola materi ini',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `materi_kuis`
--

CREATE TABLE `materi_kuis` (
  `id_materi_kuis` int NOT NULL,
  `id_materi` varchar(50) NOT NULL,
  `id_kuis` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `materi_kuis`
--

INSERT INTO `materi_kuis` (`id_materi_kuis`, `id_materi`, `id_kuis`) VALUES
(27, 'M001', 'K001'),
(28, 'M001', 'K002'),
(29, 'M001', 'K003'),
(31, 'M001', 'K005'),
(32, 'M001', 'K006'),
(33, 'M001', 'K007'),
(36, 'M001', 'K009'),
(30, 'M002', 'K004'),
(34, 'M002', 'K007'),
(37, 'M002', 'K009'),
(35, 'M003', 'K008');

-- --------------------------------------------------------

--
-- Table structure for table `mengerjakan`
--

CREATE TABLE `mengerjakan` (
  `id_mengerjakan` int NOT NULL,
  `id_kuis` varchar(50) NOT NULL,
  `id_user` varchar(50) NOT NULL COMMENT 'Siswa yang mengerjakan',
  `nilai` decimal(5,2) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `percobaan_ke` int NOT NULL DEFAULT '1' COMMENT 'Percobaan ke berapa (mendukung kerjakan ulang)',
  `status_pengerjaan` varchar(20) NOT NULL DEFAULT 'final' COMMENT 'final/dibatalkan',
  `status_validasi` varchar(50) DEFAULT 'belum divalidasi' COMMENT 'Validasi NILAI oleh guru',
  `id_validator` varchar(50) DEFAULT NULL COMMENT 'Guru yang memvalidasi nilai ini'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `mengerjakan`
--

INSERT INTO `mengerjakan` (`id_mengerjakan`, `id_kuis`, `id_user`, `nilai`, `tanggal`, `percobaan_ke`, `status_pengerjaan`, `status_validasi`, `id_validator`) VALUES
(35, 'K001', 'U002', 33.34, '2026-10-31', 1, 'final', 'belum divalidasi', NULL),
(36, 'K001', 'U002', 100.00, '2026-10-31', 2, 'final', 'belum divalidasi', NULL),
(37, 'K002', 'U002', 60.00, '2026-10-31', 1, 'final', 'belum divalidasi', NULL),
(38, 'K002', 'U002', 60.00, '2026-10-31', 2, 'final', 'belum divalidasi', NULL),
(39, 'K002', 'U002', 60.00, '2026-10-31', 3, 'final', 'sudah divalidasi', 'U001'),
(40, 'K002', 'U002', 80.00, '2026-10-31', 4, 'final', 'sudah divalidasi', 'U001'),
(41, 'K003', 'U002', 42.84, '2026-10-31', 1, 'final', 'sudah divalidasi', 'U001'),
(42, 'K003', 'U002', 85.72, '2026-10-31', 2, 'final', 'sudah divalidasi', 'U001'),
(43, 'K005', 'U002', 40.00, '2026-10-31', 1, 'final', 'sudah divalidasi', 'U001'),
(44, 'K001', 'U002', 100.00, '2026-10-31', 3, 'final', 'sudah divalidasi', 'U001'),
(45, 'K006', 'U002', 40.00, '2026-10-31', 1, 'final', 'sudah divalidasi', 'U001'),
(46, 'K002', 'U002', 40.00, '2026-11-01', 5, 'final', 'sudah divalidasi', 'U001'),
(47, 'K002', 'U002', 20.00, '2026-11-01', 6, 'final', 'sudah divalidasi', 'U001'),
(48, 'K001', 'U002', 33.33, '2026-11-06', 4, 'final', 'sudah divalidasi', 'U001'),
(49, 'K005', 'U008', 40.00, '2026-11-10', 1, 'final', 'sudah divalidasi', 'U001'),
(50, 'K001', 'U008', 66.67, '2026-11-10', 1, 'final', 'sudah divalidasi', 'U001'),
(51, 'K001', 'U008', 100.00, '2026-11-10', 2, 'final', 'sudah divalidasi', 'U001'),
(52, 'K001', 'U008', 33.33, '2026-11-10', 3, 'final', 'sudah divalidasi', 'U001'),
(53, 'K001', 'U008', 33.34, '2026-11-10', 4, 'final', 'sudah divalidasi', 'U001');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int UNSIGNED NOT NULL,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pengajaran`
--

CREATE TABLE `pengajaran` (
  `id_pengajaran` int UNSIGNED NOT NULL,
  `id_user` varchar(50) NOT NULL,
  `id_kelas` varchar(50) NOT NULL,
  `id_mapel` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `progress`
--

CREATE TABLE `progress` (
  `id_progress` varchar(50) NOT NULL,
  `status` varchar(50) DEFAULT NULL COMMENT 'Contoh: belum mulai, sedang belajar, selesai',
  `tanggal` date DEFAULT NULL,
  `id_materi` varchar(50) NOT NULL,
  `id_user` varchar(50) NOT NULL COMMENT 'Siswa pemilik progress ini'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `progress`
--

INSERT INTO `progress` (`id_progress`, `status`, `tanggal`, `id_materi`, `id_user`) VALUES
('P2QHMCZTJXS', 'selesai', '2026-11-08', 'M003', 'U002'),
('P6PZDMW4DEQ', 'selesai', '2026-11-01', 'M001', 'U002');

-- --------------------------------------------------------

--
-- Table structure for table `remedial`
--

CREATE TABLE `remedial` (
  `id_remedial` int NOT NULL,
  `id_kuis_remedial` varchar(50) NOT NULL COMMENT 'Kuis dengan kategori=remedial',
  `id_kuis_asal` varchar(50) NOT NULL COMMENT 'Kuis asal (misal pass-test) yang jadi alasan remedial'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `remedial`
--

INSERT INTO `remedial` (`id_remedial`, `id_kuis_remedial`, `id_kuis_asal`) VALUES
(3, 'K006', 'K005');

-- --------------------------------------------------------

--
-- Table structure for table `remedial_siswa`
--

CREATE TABLE `remedial_siswa` (
  `id_remedial_siswa` int NOT NULL,
  `id_kuis_remedial` varchar(50) NOT NULL,
  `id_mengerjakan_asal` int NOT NULL COMMENT 'Baris nilai siswa yang di bawah KKM (alasan diberi remedial)',
  `id_guru` varchar(50) NOT NULL COMMENT 'Guru yang menugaskan remedial ini',
  `tanggal_ditugaskan` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'ditugaskan' COMMENT 'ditugaskan/selesai'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `remedial_siswa`
--

INSERT INTO `remedial_siswa` (`id_remedial_siswa`, `id_kuis_remedial`, `id_mengerjakan_asal`, `id_guru`, `tanggal_ditugaskan`, `status`) VALUES
(4, 'K006', 43, 'U001', '2026-10-31', 'selesai');

-- --------------------------------------------------------

--
-- Table structure for table `sesi_kuis`
--

CREATE TABLE `sesi_kuis` (
  `id_sesi` int NOT NULL,
  `id_kuis` varchar(50) NOT NULL,
  `id_user` varchar(50) NOT NULL COMMENT 'Siswa yang memulai kuis',
  `percobaan_ke` int UNSIGNED NOT NULL DEFAULT '1',
  `waktu_mulai` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `waktu_selesai` timestamp NULL DEFAULT NULL,
  `jumlah_pelanggaran` tinyint UNSIGNED NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `sesi_kuis`
--

INSERT INTO `sesi_kuis` (`id_sesi`, `id_kuis`, `id_user`, `percobaan_ke`, `waktu_mulai`, `waktu_selesai`, `jumlah_pelanggaran`) VALUES
(24, 'K001', 'U002', 1, '2026-10-31 04:20:51', '2026-10-31 04:21:42', 0),
(25, 'K001', 'U002', 2, '2026-10-31 04:22:26', '2026-10-31 04:23:00', 0),
(26, 'K002', 'U002', 1, '2026-10-31 04:23:36', '2026-10-31 04:34:21', 0),
(27, 'K002', 'U002', 2, '2026-10-31 04:34:50', '2026-10-31 04:35:01', 0),
(28, 'K002', 'U002', 3, '2026-10-31 05:30:34', '2026-10-31 05:31:00', 0),
(29, 'K002', 'U002', 4, '2026-10-31 05:31:17', '2026-10-31 05:31:25', 0),
(30, 'K003', 'U002', 1, '2026-10-31 05:31:55', '2026-10-31 05:32:05', 0),
(31, 'K003', 'U002', 2, '2026-10-31 05:32:22', '2026-10-31 05:32:33', 0),
(32, 'K005', 'U002', 1, '2026-10-31 08:08:58', '2026-10-31 08:09:24', 0),
(33, 'K001', 'U002', 3, '2026-10-31 14:55:07', '2026-10-31 14:55:32', 0),
(34, 'K006', 'U002', 1, '2026-10-31 15:33:24', '2026-10-31 15:34:19', 0),
(35, 'K002', 'U002', 5, '2026-10-31 22:53:50', '2026-10-31 22:54:02', 0),
(36, 'K002', 'U002', 6, '2026-10-31 22:54:11', '2026-10-31 22:54:45', 0),
(37, 'K001', 'U002', 4, '2026-11-06 00:09:59', '2026-11-06 00:11:16', 0),
(38, 'K005', 'U008', 1, '2026-11-09 20:17:09', '2026-11-09 20:18:32', 0),
(39, 'K001', 'U008', 1, '2026-11-09 20:37:45', '2026-11-09 20:41:31', 0),
(40, 'K001', 'U008', 2, '2026-11-09 21:12:18', '2026-11-09 21:13:08', 1),
(41, 'K001', 'U008', 3, '2026-11-09 21:14:13', '2026-11-09 21:14:57', 3),
(42, 'K001', 'U008', 4, '2026-11-09 21:20:59', '2026-11-09 21:22:29', 3);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('NAaTpeknyx55pxR342jhd8XREyz3l2aS1z9kWbOL', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/156.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiI0T25BUFRGSE02YWRLN3BYRjVhOVU0ZlQ4WktyOXJhWXU4WGJRVFJFIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL2xvZ2luIiwicm91dGUiOiJsb2dpbiJ9fQ==', 1794230608),
('quHdcHZcdAv7kRhtUAHaPDyqXtboPIpCWAkRZNPZ', 'U008', '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/157.0.0.0 Safari/537.36', 'eyJfdG9rZW4iOiJ5Qjl3TXBPUFh4MVlvWFN5elNZRHc5TVNycWtpeWU0NzBibWpabXdNIiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJfcHJldmlvdXMiOnsidXJsIjoiaHR0cDpcL1wvMTI3LjAuMC4xOjgwMDBcL3Byb2ZpbCIsInJvdXRlIjoicHJvZmlsIn0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjoiVTAwOCIsInBhc3N3b3JkX2hhc2hfd2ViIjoiOTRiOTg2Y2UwNTc1YjFlYzZjNjJmNTJiN2JlODY0ZGY0NzY5Mzk1NjZlNTdjZDM5ODk1YTExZDU2ZjY4MTAwOSJ9', 1794292376);

-- --------------------------------------------------------

--
-- Table structure for table `soal`
--

CREATE TABLE `soal` (
  `id_soal` varchar(50) NOT NULL,
  `id_kuis` varchar(50) NOT NULL,
  `pertanyaan` text NOT NULL,
  `opsi_a` varchar(255) DEFAULT NULL,
  `opsi_b` varchar(255) DEFAULT NULL,
  `opsi_c` varchar(255) DEFAULT NULL,
  `opsi_d` varchar(255) DEFAULT NULL,
  `opsi_e` varchar(255) DEFAULT NULL,
  `jawaban` varchar(5) DEFAULT NULL COMMENT 'Contoh: A/B/C/D/E',
  `bobot` decimal(5,2) NOT NULL DEFAULT '1.00',
  `tingkat_kesulitan` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `soal`
--

INSERT INTO `soal` (`id_soal`, `id_kuis`, `pertanyaan`, `opsi_a`, `opsi_b`, `opsi_c`, `opsi_d`, `opsi_e`, `jawaban`, `bobot`, `tingkat_kesulitan`) VALUES
('S001', 'K001', 'Kepanjangan dari istilah LAN dalam jaringan komputer adalah...', 'Local Area Network', 'Large Area Network', 'Logical Array Node', 'Link Access Network', 'Local Architecture Node', 'A', 33.34, 'Mudah'),
('S002', 'K001', 'Topologi jaringan yang menggunakan sebuah perangkat switch atau hub sebagai titik konsentrator pusat adalah topologi...', 'Bus', 'Ring', 'Star', 'Mesh', 'Tree', 'C', 33.33, 'Mudah'),
('S003', 'K001', 'Perangkat keras jaringan yang berfungsi meneruskan paket data ke komputer tujuan berdasarkan MAC Address adalah...', 'Repeater', 'Switch', 'Kabel UTP', 'Power Supply', 'VGA Card', 'B', 33.33, 'Mudah'),
('S004', 'K002', 'Apa yang terjadi pada jaringan bertopologi Bus jika kabel tulang punggung (backbone utama) mengalami putus di tengah jalur?', 'Seluruh aktivitas komunikasi jaringan lumpuh total', 'Hanya komputer paling ujung yang terputus', 'Kecepatan transfer data otomatis berlipat ganda', 'Tidak terjadi gangguan apapun pada komputer lain', 'Switch pusat akan restart otomatis', 'A', 20.00, 'Sedang'),
('S005', 'K002', 'Pada topologi Ring, metode akses yang digunakan untuk menghindari terjadinya tabrakan data (collision) antar node adalah...', 'Carrier Sense Multiple Access (CSMA/CD)', 'Token Passing', 'Time Division Multiplexing', 'Handshaking Tiga Arah', 'Broadcasting Kontinu', 'B', 20.00, 'Sedang'),
('S006', 'K002', 'Jika sebuah ruangan memiliki 5 komputer yang dihubungkan secara Full Mesh, berapa banyak link kabel fisik yang diperlukan (Rumus: N*(N-1)/2)?', '5 kabel', '8 kabel', '10 kabel', '15 kabel', '20 kabel', 'C', 20.00, 'Sedang'),
('S007', 'K002', 'Topologi Tree (Pohon) pada dasarnya merupakan integrasi dan kombinasi struktural dari karakteristik dua topologi, yaitu...', 'Topologi Bus dan Topologi Star', 'Topologi Ring dan Topologi Mesh', 'Topologi Star dan Topologi Ring', 'Topologi Mesh dan Topologi Bus', 'Topologi Point-to-Point dan Topologi Ring', 'A', 20.00, 'Sedang'),
('S008', 'K002', 'Salah satu keunggulan topologi Star dibandingkan Bus adalah isolasi kegagalan transmisi. Hal ini berarti...', 'Jika satu kabel klien putus, komputer klien lainnya tetap dapat berkomunikasi dengan normal', 'Topologi Star tidak memerlukan listrik sama sekali', 'Kabel yang digunakan jauh lebih sedikit dibanding Bus', 'Biaya instalasi lebih murah dibanding topologi Bus', 'Kecepatan internet otomatis naik menjadi Gigabit', 'A', 20.00, 'Sedang'),
('S009', 'K003', 'Sebuah gedung 4 lantai memiliki puluhan PC pada setiap lantai. Untuk mencegah domain broadcast meluap dan meningkatkan keamanan, solusi arsitektur jaringan paling tepat adalah...', 'Menghubungkan semua lantai dengan kabel coaxial dan bus terminator', 'Menggunakan switch terkelola (manageable) dengan segmentasi VLAN per lantai', 'Memasang repeater pasif di tengah tangga gedung', 'Mengganti semua kabel ethernet dengan koneksi Bluetooth', 'Menggunakan satu hub 48 port di lantai paling bawah', 'B', 14.32, 'Sulit'),
('S010', 'K003', 'Perbedaan fundamental cara kerja Switch dan Hub dalam meminimalisir tabrakan paket (collision) adalah...', 'Switch memisahkan collision domain pada setiap port-nya menggunakan tabel MAC address', 'Hub memiliki firewall bawaan yang menyaring paket data', 'Switch selalu melakukan broadcast frame ke seluruh port tanpa filter', 'Hub menggunakan enkripsi SSL tingkat tinggi secara default', 'Switch tidak membutuhkan daya listrik ketika mentransfer data', 'A', 14.28, 'Sulit'),
('S011', 'K003', 'Pada implementasi Dual Ring (FDDI), fitur proteksi yang memungkinkan transmisi tetap berjalan saat salah satu cincin kabel putus dinamakan...', 'Wrap around (pembelokan ke cincin sekunder)', 'Spanning Tree Disabled', 'Loopback Termination', 'Half-duplex Throttling', 'Port Mirroring Bypass', 'A', 14.28, 'Sulit'),
('S012', 'K003', 'Untuk menghubungkan 6 kantor cabang antar provinsi dengan persyaratan tidak boleh ada single point of failure dan toleransi kegagalan maksimum, topologi yang wajib diterapkan adalah...', 'Topologi Bus Terbuka', 'Topologi Mesh Penuh (Full Mesh)', 'Topologi Daisy Chain Sederhana', 'Topologi Star Terpusat Tanpa Cadangan', 'Topologi Ring Tunggal', 'B', 14.28, 'Sulit'),
('S013', 'K003', 'Apa penyebab utama fenomena Broadcast Storm pada topologi jaringan bertingkat jika beberapa switch redundan dihubungkan tanpa mengaktifkan protokol STP (Spanning Tree Protocol)?', 'Terjadi switching loop fisik yang menyebabkan frame broadcast digandakan terus-menerus tanpa henti', 'Kabel UTP terpasang melebihi jarak 5 meter', 'IP Gateway router salah diketik', 'Alamat DNS server kadaluarsa', 'Suhu ruangan server terlalu tinggi', 'A', 14.28, 'Sulit'),
('S014', 'K003', 'Dalam arsitektur hierarki jaringan Cisco 3-Layer (Access, Distribution, Core), peran utama dari Core Layer adalah...', 'Menghubungkan langsung kabel ke laptop pengguna akhir', 'Menyaring paket akses daftar kontrol (ACL) yang rumit', 'Melakukan transmisi paket berkecepatan sangat tinggi antar switch distribusi tanpa beban komputasi berat (Fast Switching Backbone)', 'Memberikan alamat IP otomatis melalui DHCP server', 'Mengatur pembagian bandwidth wifi ruang guru', 'C', 14.28, 'Sulit'),
('S015', 'K003', 'Seorang teknisi TKJ mendeteksi adanya packet loss yang signifikan dan perlambatan transfer data pada topologi Star. Prosedur troubleshooting awal yang paling ilmiah adalah...', 'Mengganti seluruh monitor PC di laboratorium', 'Mengecek statistik error interface switch (CRC error, duplex mismatch) dan menganalisis traffic capture dengan Wireshark', 'Mematikan seluruh server selama 24 jam', 'Mencabut kabel listrik gedung sekolah', 'Menginstall ulang sistem operasi pada semua komputer sekaligus', 'B', 14.28, 'Sulit'),
('S016', 'K004', 'Kabel jenis Straight-through umumnya digunakan untuk menghubungkan dua perangkat yang...', 'Berbeda jenis (misal: Komputer ke Switch)', 'Sama persis jenisnya (misal: Switch ke Switch)', 'Komputer ke Komputer secara langsung tanpa perantara', 'Router ke Router tanpa switch', 'Hub ke Hub tanpa port uplink', 'A', 33.34, 'Mudah'),
('S017', 'K004', 'Standar urutan pin kabel T568B dimulai dengan susunan warna...', 'Putih Hijau, Hijau', 'Putih Oranye, Oranye', 'Putih Biru, Biru', 'Putih Cokelat, Cokelat', 'Oranye, Putih Hijau', 'B', 33.33, 'Mudah'),
('S018', 'K004', 'Alamat IP 192.168.1.100 termasuk ke dalam pengalamatan IPv4 kelas...', 'Kelas A', 'Kelas B', 'Kelas C', 'Kelas D', 'Kelas E', 'C', 33.33, 'Mudah'),
('S019', 'K005', 'Jaringan komputer dengan cakupan area geografis terbatas seperti dalam satu ruangan laboratorium atau gedung sekolah dikenal dengan istilah...', 'LAN (Local Area Network)', 'MAN (Metropolitan Area Network)', 'WAN (Wide Area Network)', 'PAN (Personal Area Network)', 'SAN (Storage Area Network)', 'A', 20.00, 'Sedang'),
('S020', 'K005', 'Topologi fisik jaringan komputer yang menggunakan perangkat sentral (seperti Switch atau Hub) sebagai pusat lalu lintas data antar node adalah...', 'Topologi Bus', 'Topologi Ring', 'Topologi Star', 'Topologi Mesh', 'Topologi Linier', 'C', 20.00, 'Sedang'),
('S021', 'K005', 'Perangkat jaringan yang beroperasi pada OSI Layer 3 (Network Layer) dan berfungsi menghubungkan dua jaringan yang memiliki Network ID / subnet berbeda adalah...', 'Hub Pasif', 'Switch Unmanaged', 'Router', 'Repeater', 'LAN Tester', 'C', 20.00, 'Sedang'),
('S022', 'K005', 'Jenis media transmisi jaringan berkabel yang paling umum digunakan untuk instalasi LAN lokal di laboratorium SMK TKJ adalah...', 'Kabel Coaxial Thinnet', 'Kabel UTP (Unshielded Twisted Pair)', 'Kabel Fiber Optik Single Mode', 'Kabel Twinaxial', 'Kabel Ribbon', 'B', 20.00, 'Sedang'),
('S023', 'K005', 'Pada model referensi OSI 7 Layer, lapisan yang bertanggung jawab menangani pengalamatan logis (IP Address) dan perutean paket data (routing) adalah...', 'Physical Layer', 'Data Link Layer', 'Network Layer', 'Transport Layer', 'Application Layer', 'C', 20.00, 'Sedang'),
('S024', 'K006', 'Konektor standar 8-pin yang dipasang pada ujung kabel UTP untuk dikoneksikan ke port Ethernet komputer atau switch adalah...', 'Konektor RJ-11', 'Konektor RJ-45', 'Konektor BNC', 'Konektor ST', 'Konektor SC', 'B', 20.00, 'Sedang'),
('S025', 'K006', 'Keuntungan utama menggunakan topologi Star dibandingkan topologi Bus pada jaringan lokal adalah...', 'Membutuhkan kabel yang lebih sedikit', 'Jika salah satu komputer mengalami gangguan kabel, jaringan komputer lain tetap normal', 'Tidak memerlukan perangkat sentral switch/hub', 'Tidak memerlukan konfigurasi IP Address', 'Kecepatan jaringan selalu mencapai 100 Gbps', 'B', 20.00, 'Sedang'),
('S026', 'K006', 'Layanan atau protokol jaringan yang bertugas mendistribusikan konfigurasi IP Address secara otomatis dan dinamis kepada host client adalah...', 'DNS (Domain Name System)', 'DHCP (Dynamic Host Configuration Protocol)', 'FTP (File Transfer Protocol)', 'HTTP (Hypertext Transfer Protocol)', 'SMTP (Simple Mail Transfer Protocol)', 'B', 20.00, 'Sedang'),
('S027', 'K006', 'Batas maksimal panjang segmen kabel UTP (Cat5e / Cat6) yang direkomendasikan agar sinyal data tetap stabil tanpa bantuan repeater adalah...', '50 meter', '100 meter', '200 meter', '500 meter', '1000 meter', 'B', 20.00, 'Sedang'),
('S028', 'K006', 'Perangkat keras jaringan komputer yang bekerja pada OSI Layer 2 (Data Link) dan meneruskan data berdasarkan alamat MAC (Media Access Control) adalah...', 'Hub Pasif', 'Switch', 'Repeater Sinyal', 'Modem Analog', 'Crimping Tool', 'B', 20.00, 'Sedang'),
('S029', 'K007', 'lima tahun', '500', '55', '555', '5', '54', 'D', 50.00, 'mudah'),
('S030', 'K007', '40tahun', '4', '44444', '40', '67', '76', 'C', 50.00, 'sulit'),
('S031', 'K008', 'xsxxsxsxsx', 'xsxsxs', 'azazaz', 'zazaz', 'wqwqewdwe', '123456098765', 'A', 100.00, 'mudah'),
('S032', 'K009', 'xxsaxxwxw', 'wddwd', 'dwdwd', 'wdwdwd', 'wdwdwd', 'dwdwdw', 'C', 50.00, 'mudah'),
('S033', 'K009', 'azaxceccececec', 'sdv', 'sx', 'rstjhtrbnth tr', 'cdcd', 'h53q', 'B', 50.00, 'sedang');

-- --------------------------------------------------------

--
-- Table structure for table `tahun_ajaran`
--

CREATE TABLE `tahun_ajaran` (
  `id_tahun_ajaran` varchar(50) NOT NULL,
  `nama` varchar(20) NOT NULL COMMENT 'Contoh: 2026/2027',
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `is_aktif` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Hanya satu baris boleh aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `aktif_unik` tinyint GENERATED ALWAYS AS (if((`is_aktif` = 1),1,NULL)) STORED
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `tahun_ajaran`
--

INSERT INTO `tahun_ajaran` (`id_tahun_ajaran`, `nama`, `tanggal_mulai`, `tanggal_selesai`, `is_aktif`, `created_at`, `updated_at`) VALUES
('TA001', '2026/2027', '2026-07-14', '2027-06-20', 1, '2026-10-28 19:32:59', '2026-11-05 23:51:23'),
('TA002', '2025/2026', '2025-07-07', '2026-07-06', 0, '2026-10-28 23:47:12', '2026-11-05 23:51:23');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id_user` varchar(50) NOT NULL,
  `nomor_induk` varchar(50) DEFAULT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','guru','siswa') NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'aktif' COMMENT 'aktif/nonaktif - siswa/guru keluar diset nonaktif',
  `kelas` varchar(50) DEFAULT NULL COMMENT 'Hanya diisi jika role=siswa, contoh: X, XI IPA 1',
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `wajib_ganti_password` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1 = dipaksa ganti password saat login berikutnya',
  `dibuat_oleh` varchar(50) DEFAULT NULL COMMENT 'id_user admin yang membuat akun ini',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id_user`, `nomor_induk`, `nama_lengkap`, `role`, `status`, `kelas`, `email`, `password`, `wajib_ganti_password`, `dibuat_oleh`, `created_at`, `updated_at`) VALUES
('U001', '198001', 'Budi Guru', 'guru', 'aktif', NULL, NULL, '$2y$12$eKPWxbVjXMbny45BTDjmnOyo1TTQmJqYjQoISOPlhn88ikQedvf/u', 0, NULL, '2026-10-28 19:32:59', '2026-10-28 19:32:59'),
('U002', '202401', 'Siti Siswa', 'siswa', 'aktif', 'X', NULL, '$2y$12$Kg6QdWb756qCRmbnJqA7xeUqbzCBpP0mDWI9nkSSTX3iDWmGO4EsS', 0, NULL, '2026-10-28 19:32:59', '2026-10-28 19:32:59'),
('U003', '199001', 'Andi Operator', 'admin', 'aktif', NULL, 'andi@sekolah.sch.id', '$2y$12$AndWwgSGI0s7e43BJSdHK.au6idJPVbW0V8C9VlEzA6vmJh3eiQiq', 0, NULL, '2026-10-28 19:32:59', '2026-10-28 21:11:35'),
('U004', '0113377232', 'ABI REPALDI', 'siswa', 'aktif', 'X', NULL, '$2y$12$X8oVSo3gXuWPWfwPMM2bge.kXgNWuYCLLf/S8Ya16240qS6.PcjnS', 1, 'U003', '2026-10-28 23:05:38', '2026-10-28 23:45:37'),
('U005', '0092980660', 'ABDUL WAHAB MALIQ', 'siswa', 'aktif', 'XI', NULL, '$2y$12$Ckljf/3aYq9D9mvU4onNP.WU.DASpdqtKO99bOVMu9cKtBdMbd5oq', 1, 'U003', '2026-10-28 23:54:31', '2026-10-28 23:54:31'),
('U006', '0094659443', 'ACHMAD INGGAR ROSIDI', 'siswa', 'aktif', 'XI', NULL, '$2y$12$jYC2mnt15C9rXE5uZw8XA.UJSVoBFChOHud6oJxaYBt724G/zPDWu', 0, 'U003', '2026-10-29 00:31:02', '2026-11-09 06:23:16'),
('U007', '7846746', 'Sadan', 'guru', 'nonaktif', NULL, NULL, '$2y$12$3NQO83otZoiGnj3il4APqe.AAQ/9lB3r0gvqIs.NE4TOESnmQA1au', 1, 'U003', '2026-10-29 00:32:10', '2026-10-31 16:58:04'),
('U008', '0987654321', 'DesuDesu', 'siswa', 'aktif', 'X', NULL, '$2y$12$BzrQTmxlAGBuBDCiVbpHK.jLPfvwhTqrX4e2TK2KQIz8bizJDERXS', 0, 'U003', '2026-11-09 19:05:13', '2026-11-09 19:06:32');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `anggota_kelas`
--
ALTER TABLE `anggota_kelas`
  ADD PRIMARY KEY (`id_anggota_kelas`),
  ADD UNIQUE KEY `uq_anggota_kelas` (`id_kelas`,`id_user`),
  ADD KEY `idx_anggota_kelas_user` (`id_user`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_expiration_index` (`expiration`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`),
  ADD KEY `cache_locks_expiration_index` (`expiration`);

--
-- Indexes for table `draft_jawaban`
--
ALTER TABLE `draft_jawaban`
  ADD PRIMARY KEY (`id_draft`),
  ADD UNIQUE KEY `uq_draft` (`id_kuis`,`id_user`,`id_soal`),
  ADD KEY `fk_draft_user` (`id_user`),
  ADD KEY `fk_draft_soal` (`id_soal`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  ADD KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kelas`
--
ALTER TABLE `kelas`
  ADD PRIMARY KEY (`id_kelas`),
  ADD UNIQUE KEY `uq_kelas_nama_tahun` (`nama_kelas`,`id_tahun_ajaran`),
  ADD KEY `idx_kelas_tahun` (`id_tahun_ajaran`);

--
-- Indexes for table `kuis`
--
ALTER TABLE `kuis`
  ADD PRIMARY KEY (`id_kuis`),
  ADD KEY `fk_kuis_user` (`id_user`),
  ADD KEY `fk_kuis_publisher` (`id_publisher`),
  ADD KEY `idx_kuis_mapel` (`id_mapel`),
  ADD KEY `idx_kuis_tahun` (`id_tahun_ajaran`);

--
-- Indexes for table `mapel`
--
ALTER TABLE `mapel`
  ADD PRIMARY KEY (`id_mapel`),
  ADD UNIQUE KEY `uq_mapel_kode` (`kode_mapel`);

--
-- Indexes for table `mapel_kelas`
--
ALTER TABLE `mapel_kelas`
  ADD PRIMARY KEY (`id_mapel_kelas`),
  ADD UNIQUE KEY `uq_mapel_kelas` (`id_mapel`,`id_kelas`),
  ADD KEY `idx_mapel_kelas_kelas` (`id_kelas`);

--
-- Indexes for table `materi`
--
ALTER TABLE `materi`
  ADD PRIMARY KEY (`id_materi`),
  ADD UNIQUE KEY `uq_materi_judul` (`kelas`,`bab`,`judul`),
  ADD KEY `fk_materi_user` (`id_user`),
  ADD KEY `idx_materi_mapel` (`id_mapel`);

--
-- Indexes for table `materis`
--
ALTER TABLE `materis`
  ADD PRIMARY KEY (`id_materi`),
  ADD UNIQUE KEY `uq_materi_judul` (`kelas`,`bab`,`judul`);

--
-- Indexes for table `materi_kuis`
--
ALTER TABLE `materi_kuis`
  ADD PRIMARY KEY (`id_materi_kuis`),
  ADD UNIQUE KEY `uq_materi_kuis` (`id_materi`,`id_kuis`),
  ADD KEY `fk_materikuis_kuis` (`id_kuis`);

--
-- Indexes for table `mengerjakan`
--
ALTER TABLE `mengerjakan`
  ADD PRIMARY KEY (`id_mengerjakan`),
  ADD UNIQUE KEY `uq_kuis_siswa_percobaan` (`id_kuis`,`id_user`,`percobaan_ke`),
  ADD KEY `fk_mengerjakan_user` (`id_user`),
  ADD KEY `fk_mengerjakan_validator` (`id_validator`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`email`);

--
-- Indexes for table `pengajaran`
--
ALTER TABLE `pengajaran`
  ADD PRIMARY KEY (`id_pengajaran`),
  ADD UNIQUE KEY `uq_pengajaran` (`id_user`,`id_kelas`,`id_mapel`),
  ADD KEY `fk_pengajaran_mapel_kelas` (`id_mapel`,`id_kelas`);

--
-- Indexes for table `progress`
--
ALTER TABLE `progress`
  ADD PRIMARY KEY (`id_progress`),
  ADD KEY `fk_progress_materi` (`id_materi`),
  ADD KEY `fk_progress_user` (`id_user`);

--
-- Indexes for table `remedial`
--
ALTER TABLE `remedial`
  ADD PRIMARY KEY (`id_remedial`),
  ADD UNIQUE KEY `uq_kuis_remedial` (`id_kuis_remedial`),
  ADD KEY `fk_remedial_kuisasal` (`id_kuis_asal`);

--
-- Indexes for table `remedial_siswa`
--
ALTER TABLE `remedial_siswa`
  ADD PRIMARY KEY (`id_remedial_siswa`),
  ADD UNIQUE KEY `uq_remedial_siswa` (`id_kuis_remedial`,`id_mengerjakan_asal`),
  ADD KEY `fk_remedialsiswa_mengerjakan` (`id_mengerjakan_asal`),
  ADD KEY `fk_remedialsiswa_guru` (`id_guru`);

--
-- Indexes for table `sesi_kuis`
--
ALTER TABLE `sesi_kuis`
  ADD PRIMARY KEY (`id_sesi`),
  ADD UNIQUE KEY `uq_sesi_kuis_siswa_percobaan` (`id_kuis`,`id_user`,`percobaan_ke`),
  ADD KEY `fk_sesi_user` (`id_user`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `soal`
--
ALTER TABLE `soal`
  ADD PRIMARY KEY (`id_soal`),
  ADD KEY `fk_soal_kuis` (`id_kuis`);

--
-- Indexes for table `tahun_ajaran`
--
ALTER TABLE `tahun_ajaran`
  ADD PRIMARY KEY (`id_tahun_ajaran`),
  ADD UNIQUE KEY `uq_tahun_ajaran_nama` (`nama`),
  ADD UNIQUE KEY `uq_hanya_satu_aktif` (`aktif_unik`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id_user`),
  ADD UNIQUE KEY `uq_user_nomor_induk` (`nomor_induk`),
  ADD KEY `idx_user_dibuat_oleh` (`dibuat_oleh`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `anggota_kelas`
--
ALTER TABLE `anggota_kelas`
  MODIFY `id_anggota_kelas` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `draft_jawaban`
--
ALTER TABLE `draft_jawaban`
  MODIFY `id_draft` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=117;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mapel_kelas`
--
ALTER TABLE `mapel_kelas`
  MODIFY `id_mapel_kelas` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `materi_kuis`
--
ALTER TABLE `materi_kuis`
  MODIFY `id_materi_kuis` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT for table `mengerjakan`
--
ALTER TABLE `mengerjakan`
  MODIFY `id_mengerjakan` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pengajaran`
--
ALTER TABLE `pengajaran`
  MODIFY `id_pengajaran` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `remedial`
--
ALTER TABLE `remedial`
  MODIFY `id_remedial` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `remedial_siswa`
--
ALTER TABLE `remedial_siswa`
  MODIFY `id_remedial_siswa` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sesi_kuis`
--
ALTER TABLE `sesi_kuis`
  MODIFY `id_sesi` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=43;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `anggota_kelas`
--
ALTER TABLE `anggota_kelas`
  ADD CONSTRAINT `fk_anggota_kelas_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_anggota_kelas_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `draft_jawaban`
--
ALTER TABLE `draft_jawaban`
  ADD CONSTRAINT `fk_draft_kuis` FOREIGN KEY (`id_kuis`) REFERENCES `kuis` (`id_kuis`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_draft_soal` FOREIGN KEY (`id_soal`) REFERENCES `soal` (`id_soal`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_draft_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `kelas`
--
ALTER TABLE `kelas`
  ADD CONSTRAINT `fk_kelas_tahun_ajaran` FOREIGN KEY (`id_tahun_ajaran`) REFERENCES `tahun_ajaran` (`id_tahun_ajaran`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `kuis`
--
ALTER TABLE `kuis`
  ADD CONSTRAINT `fk_kuis_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `mapel` (`id_mapel`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kuis_publisher` FOREIGN KEY (`id_publisher`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kuis_tahun_ajaran` FOREIGN KEY (`id_tahun_ajaran`) REFERENCES `tahun_ajaran` (`id_tahun_ajaran`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kuis_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `mapel_kelas`
--
ALTER TABLE `mapel_kelas`
  ADD CONSTRAINT `fk_mapel_kelas_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mapel_kelas_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `mapel` (`id_mapel`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `materi`
--
ALTER TABLE `materi`
  ADD CONSTRAINT `fk_materi_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `mapel` (`id_mapel`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_materi_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `materi_kuis`
--
ALTER TABLE `materi_kuis`
  ADD CONSTRAINT `fk_materikuis_kuis` FOREIGN KEY (`id_kuis`) REFERENCES `kuis` (`id_kuis`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_materikuis_materi` FOREIGN KEY (`id_materi`) REFERENCES `materi` (`id_materi`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `mengerjakan`
--
ALTER TABLE `mengerjakan`
  ADD CONSTRAINT `fk_mengerjakan_kuis` FOREIGN KEY (`id_kuis`) REFERENCES `kuis` (`id_kuis`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mengerjakan_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_mengerjakan_validator` FOREIGN KEY (`id_validator`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `pengajaran`
--
ALTER TABLE `pengajaran`
  ADD CONSTRAINT `fk_pengajaran_mapel_kelas` FOREIGN KEY (`id_mapel`,`id_kelas`) REFERENCES `mapel_kelas` (`id_mapel`, `id_kelas`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pengajaran_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `progress`
--
ALTER TABLE `progress`
  ADD CONSTRAINT `fk_progress_materi` FOREIGN KEY (`id_materi`) REFERENCES `materi` (`id_materi`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_progress_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `remedial`
--
ALTER TABLE `remedial`
  ADD CONSTRAINT `fk_remedial_kuisasal` FOREIGN KEY (`id_kuis_asal`) REFERENCES `kuis` (`id_kuis`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_remedial_kuisremedial` FOREIGN KEY (`id_kuis_remedial`) REFERENCES `kuis` (`id_kuis`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `remedial_siswa`
--
ALTER TABLE `remedial_siswa`
  ADD CONSTRAINT `fk_remedialsiswa_guru` FOREIGN KEY (`id_guru`) REFERENCES `user` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_remedialsiswa_kuis` FOREIGN KEY (`id_kuis_remedial`) REFERENCES `kuis` (`id_kuis`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_remedialsiswa_mengerjakan` FOREIGN KEY (`id_mengerjakan_asal`) REFERENCES `mengerjakan` (`id_mengerjakan`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `sesi_kuis`
--
ALTER TABLE `sesi_kuis`
  ADD CONSTRAINT `fk_sesi_kuis` FOREIGN KEY (`id_kuis`) REFERENCES `kuis` (`id_kuis`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_sesi_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `soal`
--
ALTER TABLE `soal`
  ADD CONSTRAINT `fk_soal_kuis` FOREIGN KEY (`id_kuis`) REFERENCES `kuis` (`id_kuis`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `user`
--
ALTER TABLE `user`
  ADD CONSTRAINT `fk_user_dibuat_oleh` FOREIGN KEY (`dibuat_oleh`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
