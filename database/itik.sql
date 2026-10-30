-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: itik
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `anggota_kelas`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `anggota_kelas` (
  `id_anggota_kelas` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_kelas` varchar(50) NOT NULL,
  `id_user` varchar(50) NOT NULL COMMENT 'role=siswa',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_anggota_kelas`),
  UNIQUE KEY `uq_anggota_kelas` (`id_kelas`,`id_user`),
  KEY `idx_anggota_kelas_user` (`id_user`),
  CONSTRAINT `fk_anggota_kelas_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_anggota_kelas_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `anggota_kelas`
--

LOCK TABLES `anggota_kelas` WRITE;
/*!40000 ALTER TABLE `anggota_kelas` DISABLE KEYS */;
INSERT INTO `anggota_kelas` (`id_anggota_kelas`, `id_kelas`, `id_user`, `created_at`) VALUES (1,'KLS001','U002','2026-10-28 19:32:59');
/*!40000 ALTER TABLE `anggota_kelas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `draft_jawaban`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `draft_jawaban` (
  `id_draft` int NOT NULL AUTO_INCREMENT,
  `id_kuis` varchar(50) NOT NULL,
  `id_user` varchar(50) NOT NULL COMMENT 'Siswa yang mengerjakan',
  `id_soal` varchar(50) NOT NULL,
  `jawaban_dipilih` varchar(5) DEFAULT NULL COMMENT 'A/B/C/D/E',
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_draft`),
  UNIQUE KEY `uq_draft` (`id_kuis`,`id_user`,`id_soal`),
  KEY `fk_draft_user` (`id_user`),
  KEY `fk_draft_soal` (`id_soal`),
  CONSTRAINT `fk_draft_kuis` FOREIGN KEY (`id_kuis`) REFERENCES `kuis` (`id_kuis`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_draft_soal` FOREIGN KEY (`id_soal`) REFERENCES `soal` (`id_soal`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_draft_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `draft_jawaban`
--

LOCK TABLES `draft_jawaban` WRITE;
/*!40000 ALTER TABLE `draft_jawaban` DISABLE KEYS */;
INSERT INTO `draft_jawaban` (`id_draft`, `id_kuis`, `id_user`, `id_soal`, `jawaban_dipilih`, `updated_at`) VALUES (9,'K005','U002','S002','B','2026-09-30 11:59:03'),(10,'K005','U002','S003','B','2026-09-30 11:59:04'),(11,'K005','U002','S004','B','2026-09-30 11:59:05');
/*!40000 ALTER TABLE `draft_jawaban` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kelas`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kelas` (
  `id_kelas` varchar(50) NOT NULL,
  `nama_kelas` varchar(50) NOT NULL COMMENT 'Contoh: X TKJ 1',
  `tingkat` varchar(10) NOT NULL COMMENT 'X / XI / XII',
  `id_tahun_ajaran` varchar(50) NOT NULL,
  `is_aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_kelas`),
  UNIQUE KEY `uq_kelas_nama_tahun` (`nama_kelas`,`id_tahun_ajaran`),
  KEY `idx_kelas_tahun` (`id_tahun_ajaran`),
  CONSTRAINT `fk_kelas_tahun_ajaran` FOREIGN KEY (`id_tahun_ajaran`) REFERENCES `tahun_ajaran` (`id_tahun_ajaran`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kelas`
--

LOCK TABLES `kelas` WRITE;
/*!40000 ALTER TABLE `kelas` DISABLE KEYS */;
INSERT INTO `kelas` (`id_kelas`, `nama_kelas`, `tingkat`, `id_tahun_ajaran`, `is_aktif`, `created_at`, `updated_at`) VALUES ('KLS001','X TKJ 1','X','TA001',1,'2026-10-28 19:32:59','2026-10-28 19:32:59'),('KLS002','X TKJ 2','X','TA001',1,'2026-10-28 19:32:59','2026-10-28 19:32:59');
/*!40000 ALTER TABLE `kelas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kuis`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kuis` (
  `id_kuis` varchar(50) NOT NULL,
  `judul` varchar(150) NOT NULL,
  `deskripsi` text,
  `kategori` varchar(50) NOT NULL COMMENT 'post-test/pre-test/kuis harian/ulangan/remedial',
  `tingkat_kesulitan` enum('mudah','sedang','sulit') NOT NULL DEFAULT 'mudah' COMMENT 'Level kuis; naik otomatis jika siswa lulus KKM',
  `id_mapel` varchar(50) NOT NULL,
  `id_tahun_ajaran` varchar(50) NOT NULL,
  `alokasi_waktu` int unsigned DEFAULT NULL COMMENT 'Batas waktu pengerjaan dalam menit',
  `kkm` decimal(5,2) DEFAULT NULL COMMENT 'Nilai minimum kelulusan (KKM) untuk kuis ini',
  `waktu_mulai` datetime DEFAULT NULL COMMENT 'Khusus kategori ulangan: kapan ulangan mulai bisa diakses semua siswa',
  `waktu_selesai` datetime DEFAULT NULL COMMENT 'Khusus kategori ulangan: kapan ulangan ditutup, tidak bisa diakses lagi',
  `id_user` varchar(50) NOT NULL COMMENT 'Guru/operator pembuat kuis (remedial khusus guru)',
  `status_publikasi` varchar(20) NOT NULL DEFAULT 'draft' COMMENT 'draft/terbit - validasi isi soal, bukan nilai',
  `id_publisher` varchar(50) DEFAULT NULL COMMENT 'Guru yang mempublikasikan/memvalidasi kuis ini',
  `is_aktif` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Soft delete: 0 = diarsipkan/dihapus',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_kuis`),
  KEY `fk_kuis_user` (`id_user`),
  KEY `fk_kuis_publisher` (`id_publisher`),
  KEY `idx_kuis_mapel` (`id_mapel`),
  KEY `idx_kuis_tahun` (`id_tahun_ajaran`),
  CONSTRAINT `fk_kuis_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `mapel` (`id_mapel`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_kuis_publisher` FOREIGN KEY (`id_publisher`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_kuis_tahun_ajaran` FOREIGN KEY (`id_tahun_ajaran`) REFERENCES `tahun_ajaran` (`id_tahun_ajaran`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_kuis_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kuis`
--

LOCK TABLES `kuis` WRITE;
/*!40000 ALTER TABLE `kuis` DISABLE KEYS */;
INSERT INTO `kuis` (`id_kuis`, `judul`, `deskripsi`, `kategori`, `tingkat_kesulitan`, `id_mapel`, `id_tahun_ajaran`, `alokasi_waktu`, `kkm`, `waktu_mulai`, `waktu_selesai`, `id_user`, `status_publikasi`, `id_publisher`, `is_aktif`, `created_at`) VALUES ('K001','Pre-test Bab 1 - Algoritma','Kuis kelulusan Bab 1','pre-test','mudah','MP001','TA001',20,75.00,NULL,NULL,'U001','terbit','U001',0,'2026-09-22 23:35:25'),('K002','Ulangan Tengah Semester','Mencakup Bab 1 dan Bab 2','ulangan','mudah','MP001','TA001',60,75.00,NULL,NULL,'U001','terbit','U001',0,'2026-09-22 23:35:25'),('K003','Kuis Harian Bab 1','Kuis harian untuk latihan rutin Bab 1','kuis harian','mudah','MP001','TA001',15,70.00,NULL,NULL,'U001','draft',NULL,1,'2026-09-22 23:35:25'),('K004','Remedial Pre-test Bab 1','Kuis remedial untuk siswa yang belum lulus K001','remedial','mudah','MP001','TA001',20,75.00,NULL,NULL,'U001','terbit','U001',0,'2026-09-22 23:35:25'),('K005','Kuis coba',NULL,'kuis harian','mudah','MP001','TA001',10,75.00,NULL,NULL,'U001','terbit',NULL,0,'2026-09-29 02:08:20'),('K006','FREE FIRE','Battle royal dulu','kuis harian','mudah','MP001','TA001',10,50.00,NULL,NULL,'U001','draft',NULL,1,'2026-09-30 06:32:01'),('K007','ufo','wq','post-test','mudah','MP001','TA001',25,50.00,NULL,NULL,'U001','draft',NULL,1,'2026-09-30 16:50:41'),('K008','FREE FIRE','sdfbgfvdcs','kuis harian','mudah','MP001','TA001',60,100.00,NULL,NULL,'U001','terbit','U001',1,'2026-09-30 18:04:15'),('K009','kamis','asasas','post-test','mudah','MP001','TA001',60,75.00,NULL,NULL,'U001','terbit','U001',1,'2026-10-01 00:10:18'),('K010','kamis','asasas','post-test','mudah','MP001','TA001',60,75.00,NULL,NULL,'U001','draft',NULL,1,'2026-10-01 02:06:02'),('K011','Kuis Benar','benar lengkap','post-test','mudah','MP001','TA001',300,100.00,NULL,NULL,'U001','terbit','U001',1,'2026-10-01 02:09:18'),('K012','5 ribu gitu loh','sulit, susah','kuis harian','mudah','MP001','TA001',60,75.00,NULL,NULL,'U001','terbit','U001',1,'2026-10-24 09:50:09');
/*!40000 ALTER TABLE `kuis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mapel`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mapel` (
  `id_mapel` varchar(50) NOT NULL,
  `kode_mapel` varchar(20) NOT NULL COMMENT 'Contoh: TIK',
  `nama_mapel` varchar(100) NOT NULL,
  `deskripsi` text,
  `is_aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mapel`),
  UNIQUE KEY `uq_mapel_kode` (`kode_mapel`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mapel`
--

LOCK TABLES `mapel` WRITE;
/*!40000 ALTER TABLE `mapel` DISABLE KEYS */;
INSERT INTO `mapel` (`id_mapel`, `kode_mapel`, `nama_mapel`, `deskripsi`, `is_aktif`, `created_at`, `updated_at`) VALUES ('MP001','TIK','Teknologi Informasi dan Komunikasi','Mata pelajaran TIK untuk jurusan TKJ.',1,'2026-10-28 19:32:59','2026-10-28 19:32:59');
/*!40000 ALTER TABLE `mapel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mapel_kelas`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mapel_kelas` (
  `id_mapel_kelas` bigint unsigned NOT NULL AUTO_INCREMENT,
  `id_mapel` varchar(50) NOT NULL,
  `id_kelas` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mapel_kelas`),
  UNIQUE KEY `uq_mapel_kelas` (`id_mapel`,`id_kelas`),
  KEY `idx_mapel_kelas_kelas` (`id_kelas`),
  CONSTRAINT `fk_mapel_kelas_kelas` FOREIGN KEY (`id_kelas`) REFERENCES `kelas` (`id_kelas`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_mapel_kelas_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `mapel` (`id_mapel`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mapel_kelas`
--

LOCK TABLES `mapel_kelas` WRITE;
/*!40000 ALTER TABLE `mapel_kelas` DISABLE KEYS */;
INSERT INTO `mapel_kelas` (`id_mapel_kelas`, `id_mapel`, `id_kelas`, `created_at`) VALUES (1,'MP001','KLS001','2026-10-28 19:32:59'),(2,'MP001','KLS002','2026-10-28 19:32:59');
/*!40000 ALTER TABLE `mapel_kelas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `materi`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_materi`),
  UNIQUE KEY `uq_materi_judul` (`kelas`,`bab`,`judul`),
  KEY `fk_materi_user` (`id_user`),
  KEY `idx_materi_mapel` (`id_mapel`),
  CONSTRAINT `fk_materi_mapel` FOREIGN KEY (`id_mapel`) REFERENCES `mapel` (`id_mapel`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_materi_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `materi`
--

LOCK TABLES `materi` WRITE;
/*!40000 ALTER TABLE `materi` DISABLE KEYS */;
INSERT INTO `materi` (`id_materi`, `judul`, `bab`, `kelas`, `id_mapel`, `deskripsi`, `isi_materi`, `link_youtube`, `id_user`, `created_at`, `updated_at`) VALUES ('M001','doa','bab2','X','MP001','Materi dasar mengenai konsep algoritma dan langkah-langkah pemrograman.','uploads/materi/M001_algoritma.pdf','https://www.youtube.com/watch?v=contoh123','U001','2026-09-22 23:35:25','2026-10-28 19:32:59'),('M002','Struktur Data Dasar','Bab 2','X','MP001','Pengenalan array, list, dan struktur data sederhana.','uploads/materi/M002_struktur_data.pdf',NULL,'U001','2026-09-22 23:35:25','2026-10-28 19:32:59'),('M003','dosa','bab2','X','MP001','QWQW','materi/5mdUpYdtsYNWa5KOeRsvzXfYgbjipJ30hvqR2aW6.pdf',NULL,'U001','2026-09-29 23:50:04','2026-10-28 19:32:59'),('M004','dosa','bab 4','X','MP001','sdwfeggnhm',NULL,NULL,'U001','2026-09-30 11:03:05','2026-10-28 19:32:59'),('M005','coba kamis','bab 4234567890-=098765432134567890','x','MP001','iasmckdsoncodsnv nb nungervmeigmjcfpfriojfkcfopwx','materi/MFuzEddrO0qVaCsYZj8iofomxmqQYMg7pI4fyiZu.pdf',NULL,'U001','2026-09-30 17:22:34','2026-10-28 19:32:59'),('M006','dosa#','bab1','X','MP001','iya',NULL,NULL,'U001','2026-09-30 18:57:04','2026-10-28 19:32:59'),('M007','Test Upload PDF','Bab 7','X','MP001','Materi untuk menguji unggah dan tampilan PDF siswa.','materi/hpF8Nw7k9JlGFuMfOyU4XoSjJuYoZe9ifIVnwrpc.pdf',NULL,'U001','2026-10-05 18:01:52','2026-10-28 19:32:59');
/*!40000 ALTER TABLE `materi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `materi_kuis`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `materi_kuis` (
  `id_materi_kuis` int NOT NULL AUTO_INCREMENT,
  `id_materi` varchar(50) NOT NULL,
  `id_kuis` varchar(50) NOT NULL,
  PRIMARY KEY (`id_materi_kuis`),
  UNIQUE KEY `uq_materi_kuis` (`id_materi`,`id_kuis`),
  KEY `fk_materikuis_kuis` (`id_kuis`),
  CONSTRAINT `fk_materikuis_kuis` FOREIGN KEY (`id_kuis`) REFERENCES `kuis` (`id_kuis`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_materikuis_materi` FOREIGN KEY (`id_materi`) REFERENCES `materi` (`id_materi`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `materi_kuis`
--

LOCK TABLES `materi_kuis` WRITE;
/*!40000 ALTER TABLE `materi_kuis` DISABLE KEYS */;
INSERT INTO `materi_kuis` (`id_materi_kuis`, `id_materi`, `id_kuis`) VALUES (1,'M001','K001'),(3,'M001','K002'),(2,'M001','K003'),(16,'M002','K001'),(4,'M002','K002'),(6,'M002','K005'),(11,'M002','K008'),(15,'M002','K011'),(17,'M003','K003'),(8,'M003','K006'),(10,'M003','K007'),(12,'M004','K009'),(14,'M004','K010');
/*!40000 ALTER TABLE `materi_kuis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `materis`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_materi`),
  UNIQUE KEY `uq_materi_judul` (`kelas`,`bab`,`judul`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `materis`
--

LOCK TABLES `materis` WRITE;
/*!40000 ALTER TABLE `materis` DISABLE KEYS */;
/*!40000 ALTER TABLE `materis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mengerjakan`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mengerjakan` (
  `id_mengerjakan` int NOT NULL AUTO_INCREMENT,
  `id_kuis` varchar(50) NOT NULL,
  `id_user` varchar(50) NOT NULL COMMENT 'Siswa yang mengerjakan',
  `nilai` decimal(5,2) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `percobaan_ke` int NOT NULL DEFAULT '1' COMMENT 'Percobaan ke berapa (mendukung kerjakan ulang)',
  `status_pengerjaan` varchar(20) NOT NULL DEFAULT 'final' COMMENT 'final/dibatalkan',
  `status_validasi` varchar(50) DEFAULT 'belum divalidasi' COMMENT 'Validasi NILAI oleh guru',
  `id_validator` varchar(50) DEFAULT NULL COMMENT 'Guru yang memvalidasi nilai ini',
  PRIMARY KEY (`id_mengerjakan`),
  UNIQUE KEY `uq_kuis_siswa_percobaan` (`id_kuis`,`id_user`,`percobaan_ke`),
  KEY `fk_mengerjakan_user` (`id_user`),
  KEY `fk_mengerjakan_validator` (`id_validator`),
  CONSTRAINT `fk_mengerjakan_kuis` FOREIGN KEY (`id_kuis`) REFERENCES `kuis` (`id_kuis`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_mengerjakan_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_mengerjakan_validator` FOREIGN KEY (`id_validator`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mengerjakan`
--

LOCK TABLES `mengerjakan` WRITE;
/*!40000 ALTER TABLE `mengerjakan` DISABLE KEYS */;
INSERT INTO `mengerjakan` (`id_mengerjakan`, `id_kuis`, `id_user`, `nilai`, `tanggal`, `percobaan_ke`, `status_pengerjaan`, `status_validasi`, `id_validator`) VALUES (1,'K001','U002',60.00,'2026-09-05',1,'final','sudah divalidasi','U001'),(3,'K005','U002',100.00,'2026-09-29',1,'final','belum divalidasi',NULL),(4,'K005','U002',33.33,'2026-09-30',2,'final','sudah divalidasi','U001'),(5,'K005','U002',33.33,'2026-09-30',3,'final','sudah divalidasi','U001'),(6,'K008','U002',100.00,'2026-09-30',1,'final','belum divalidasi',NULL),(8,'K008','U002',0.00,'2026-09-30',2,'final','belum divalidasi',NULL),(11,'K008','U002',100.00,'2026-09-30',3,'final','sudah divalidasi','U001'),(15,'K001','U002',100.00,'2026-09-30',2,'final','belum divalidasi',NULL),(16,'K008','U002',50.00,'2026-09-30',4,'final','sudah divalidasi','U001'),(17,'K001','U002',100.00,'2026-09-30',3,'final','belum divalidasi',NULL),(18,'K009','U002',50.00,'2026-10-01',1,'final','sudah divalidasi','U001'),(19,'K011','U002',0.00,'2026-10-01',1,'final','belum divalidasi',NULL),(20,'K012','U002',62.00,'2026-10-24',1,'final','sudah divalidasi','U001'),(21,'K012','U002',0.00,'2026-10-24',2,'final','belum divalidasi',NULL);
/*!40000 ALTER TABLE `mengerjakan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `progress`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `progress` (
  `id_progress` varchar(50) NOT NULL,
  `status` varchar(50) DEFAULT NULL COMMENT 'Contoh: belum mulai, sedang belajar, selesai',
  `tanggal` date DEFAULT NULL,
  `id_materi` varchar(50) NOT NULL,
  `id_user` varchar(50) NOT NULL COMMENT 'Siswa pemilik progress ini',
  PRIMARY KEY (`id_progress`),
  KEY `fk_progress_materi` (`id_materi`),
  KEY `fk_progress_user` (`id_user`),
  CONSTRAINT `fk_progress_materi` FOREIGN KEY (`id_materi`) REFERENCES `materi` (`id_materi`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_progress_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `progress`
--

LOCK TABLES `progress` WRITE;
/*!40000 ALTER TABLE `progress` DISABLE KEYS */;
INSERT INTO `progress` (`id_progress`, `status`, `tanggal`, `id_materi`, `id_user`) VALUES ('P001','selesai','2026-09-05','M001','U002'),('PJEO2XWPVLZ','selesai','2026-09-30','M003','U002'),('PSE5HIYZODX','selesai','2026-09-30','M002','U002');
/*!40000 ALTER TABLE `progress` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `remedial`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `remedial` (
  `id_remedial` int NOT NULL AUTO_INCREMENT,
  `id_kuis_remedial` varchar(50) NOT NULL COMMENT 'Kuis dengan kategori=remedial',
  `id_kuis_asal` varchar(50) NOT NULL COMMENT 'Kuis asal (misal pass-test) yang jadi alasan remedial',
  PRIMARY KEY (`id_remedial`),
  UNIQUE KEY `uq_kuis_remedial` (`id_kuis_remedial`),
  KEY `fk_remedial_kuisasal` (`id_kuis_asal`),
  CONSTRAINT `fk_remedial_kuisasal` FOREIGN KEY (`id_kuis_asal`) REFERENCES `kuis` (`id_kuis`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_remedial_kuisremedial` FOREIGN KEY (`id_kuis_remedial`) REFERENCES `kuis` (`id_kuis`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `remedial`
--

LOCK TABLES `remedial` WRITE;
/*!40000 ALTER TABLE `remedial` DISABLE KEYS */;
INSERT INTO `remedial` (`id_remedial`, `id_kuis_remedial`, `id_kuis_asal`) VALUES (1,'K004','K001');
/*!40000 ALTER TABLE `remedial` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `remedial_siswa`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `remedial_siswa` (
  `id_remedial_siswa` int NOT NULL AUTO_INCREMENT,
  `id_kuis_remedial` varchar(50) NOT NULL,
  `id_mengerjakan_asal` int NOT NULL COMMENT 'Baris nilai siswa yang di bawah KKM (alasan diberi remedial)',
  `id_guru` varchar(50) NOT NULL COMMENT 'Guru yang menugaskan remedial ini',
  `tanggal_ditugaskan` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'ditugaskan' COMMENT 'ditugaskan/selesai',
  PRIMARY KEY (`id_remedial_siswa`),
  UNIQUE KEY `uq_remedial_siswa` (`id_kuis_remedial`,`id_mengerjakan_asal`),
  KEY `fk_remedialsiswa_mengerjakan` (`id_mengerjakan_asal`),
  KEY `fk_remedialsiswa_guru` (`id_guru`),
  CONSTRAINT `fk_remedialsiswa_guru` FOREIGN KEY (`id_guru`) REFERENCES `user` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_remedialsiswa_kuis` FOREIGN KEY (`id_kuis_remedial`) REFERENCES `kuis` (`id_kuis`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_remedialsiswa_mengerjakan` FOREIGN KEY (`id_mengerjakan_asal`) REFERENCES `mengerjakan` (`id_mengerjakan`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `remedial_siswa`
--

LOCK TABLES `remedial_siswa` WRITE;
/*!40000 ALTER TABLE `remedial_siswa` DISABLE KEYS */;
INSERT INTO `remedial_siswa` (`id_remedial_siswa`, `id_kuis_remedial`, `id_mengerjakan_asal`, `id_guru`, `tanggal_ditugaskan`, `status`) VALUES (3,'K004',1,'U001','2026-10-01','ditugaskan');
/*!40000 ALTER TABLE `remedial_siswa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sesi_kuis`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sesi_kuis` (
  `id_sesi` int NOT NULL AUTO_INCREMENT,
  `id_kuis` varchar(50) NOT NULL,
  `id_user` varchar(50) NOT NULL COMMENT 'Siswa yang memulai kuis',
  `percobaan_ke` int unsigned NOT NULL DEFAULT '1',
  `waktu_mulai` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `waktu_selesai` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_sesi`),
  UNIQUE KEY `uq_sesi_kuis_siswa_percobaan` (`id_kuis`,`id_user`,`percobaan_ke`),
  KEY `fk_sesi_user` (`id_user`),
  CONSTRAINT `fk_sesi_kuis` FOREIGN KEY (`id_kuis`) REFERENCES `kuis` (`id_kuis`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_sesi_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sesi_kuis`
--

LOCK TABLES `sesi_kuis` WRITE;
/*!40000 ALTER TABLE `sesi_kuis` DISABLE KEYS */;
INSERT INTO `sesi_kuis` (`id_sesi`, `id_kuis`, `id_user`, `percobaan_ke`, `waktu_mulai`, `waktu_selesai`) VALUES (3,'K008','U002',1,'2026-09-30 11:58:29','2026-09-30 11:58:41'),(4,'K005','U002',1,'2026-09-30 11:58:59',NULL),(5,'K008','U002',2,'2026-09-30 11:59:23','2026-09-30 11:59:30'),(6,'K002','U002',1,'2026-09-30 11:59:38',NULL),(7,'K004','U002',1,'2026-09-30 11:59:59',NULL),(8,'K001','U002',1,'2026-09-30 12:00:08','2026-09-30 12:18:47'),(9,'K008','U002',3,'2026-09-30 12:09:33','2026-09-30 12:09:39'),(10,'K008','U002',4,'2026-09-30 12:18:51','2026-09-30 12:18:55'),(11,'K001','U002',3,'2026-09-30 12:19:00','2026-09-30 12:19:06'),(12,'K009','U002',1,'2026-09-30 18:37:28','2026-09-30 18:37:38'),(13,'K011','U002',1,'2026-09-30 19:22:14','2026-09-30 19:22:26'),(14,'K012','U002',1,'2026-10-24 02:55:23','2026-10-24 02:55:41'),(15,'K012','U002',2,'2026-10-24 02:56:15','2026-10-24 02:56:26');
/*!40000 ALTER TABLE `sesi_kuis` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `soal`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
  `tingkat_kesulitan` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_soal`),
  KEY `fk_soal_kuis` (`id_kuis`),
  CONSTRAINT `fk_soal_kuis` FOREIGN KEY (`id_kuis`) REFERENCES `kuis` (`id_kuis`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `soal`
--

LOCK TABLES `soal` WRITE;
/*!40000 ALTER TABLE `soal` DISABLE KEYS */;
INSERT INTO `soal` (`id_soal`, `id_kuis`, `pertanyaan`, `opsi_a`, `opsi_b`, `opsi_c`, `opsi_d`, `opsi_e`, `jawaban`, `bobot`, `tingkat_kesulitan`) VALUES ('S001','K001','Apa yang dimaksud dengan algoritma?','Bahasa pemrograman','Langkah-langkah logis penyelesaian masalah','Perangkat keras komputer','Sistem operasi','Jaringan komputer','B',1.00,'Mudah'),('S002','K005','asdfghj','sa','cs','a','c',NULL,'B',1.00,'Sedang'),('S003','K005','xsxcscvdv','cddcdvew','dwdw','dwcwc','cscs',NULL,'D',1.00,'Sedang'),('S004','K005','v,mjhtyjk','nmhgtr','nbgfde','fdew','fv',NULL,'A',1.00,'Sedang'),('S005','K008','sadan','sadan','sadan','sadan','sadan','sadan','A',50.00,'mudah'),('S006','K008','sadan arya','sadan','sadan','sadan','sadan','sadan','A',50.00,'mudah'),('S007','K009','poiuhytrew','nk','oonojn','bnm','byibhilubn',NULL,'A',50.00,NULL),('S008','K009','mcowpmcwkp','dwlq','ncejwko','kfrfk','r4r fj f',NULL,'A',50.00,'sedang'),('S009','K011','nkdsakdnwejfweopfmf','jndwnqon','nqwecnq','ndwqnd','dwqbd',NULL,'B',50.00,'mudah'),('S010','K011','nkdsakdnwejfweopfm','jndwnqon','nqwecnq','ndwqnd','dwqbd',NULL,'B',50.00,'mudah'),('S011','K012','ngngffdfe','sadan','f','w','w',NULL,'B',12.00,'mudah'),('S012','K012','dwq','ce','fewq','cd','vde','e','D',50.00,'sedang'),('S013','K012','fwtbvfadv','nbg','45g4e','cdaerg','2345gtvf','bgvfdc frqa','D',38.00,'sulit');
/*!40000 ALTER TABLE `soal` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tahun_ajaran`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tahun_ajaran` (
  `id_tahun_ajaran` varchar(50) NOT NULL,
  `nama` varchar(20) NOT NULL COMMENT 'Contoh: 2026/2027',
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `is_aktif` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Hanya satu baris boleh aktif',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_tahun_ajaran`),
  UNIQUE KEY `uq_tahun_ajaran_nama` (`nama`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tahun_ajaran`
--

LOCK TABLES `tahun_ajaran` WRITE;
/*!40000 ALTER TABLE `tahun_ajaran` DISABLE KEYS */;
INSERT INTO `tahun_ajaran` (`id_tahun_ajaran`, `nama`, `tanggal_mulai`, `tanggal_selesai`, `is_aktif`, `created_at`, `updated_at`) VALUES ('TA001','2026/2027','2026-07-14','2027-06-20',1,'2026-10-28 19:32:59','2026-10-28 19:32:59');
/*!40000 ALTER TABLE `tahun_ajaran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
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
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `uq_user_nomor_induk` (`nomor_induk`),
  KEY `idx_user_dibuat_oleh` (`dibuat_oleh`),
  CONSTRAINT `fk_user_dibuat_oleh` FOREIGN KEY (`dibuat_oleh`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--

LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` (`id_user`, `nomor_induk`, `nama_lengkap`, `role`, `status`, `kelas`, `email`, `password`, `wajib_ganti_password`, `dibuat_oleh`, `created_at`, `updated_at`) VALUES ('U001','198001','Budi Guru','guru','aktif',NULL,'budi@guru.sch.id','$2y$12$eKPWxbVjXMbny45BTDjmnOyo1TTQmJqYjQoISOPlhn88ikQedvf/u',0,NULL,'2026-10-28 19:32:59','2026-10-28 19:32:59'),('U002','202401','Siti Siswa','siswa','aktif','X','siti@siswa.sch.id','$2y$12$Kg6QdWb756qCRmbnJqA7xeUqbzCBpP0mDWI9nkSSTX3iDWmGO4EsS',0,NULL,'2026-10-28 19:32:59','2026-10-28 19:32:59'),('U003','199001','Andi Operator','admin','aktif',NULL,'andi@sekolah.sch.id','$2y$12$AndWwgSGI0s7e43BJSdHK.au6idJPVbW0V8C9VlEzA6vmJh3eiQiq',0,NULL,'2026-10-28 19:32:59','2026-10-28 21:11:35');
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-29 11:22:41
-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: itik
-- ------------------------------------------------------
-- Server version	8.4.3

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `sessions`
--

/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `payload` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-29 11:22:41
