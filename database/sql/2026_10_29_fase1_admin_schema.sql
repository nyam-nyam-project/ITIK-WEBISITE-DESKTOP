-- =====================================================================
-- ITIK - Fase 1: tabel master admin + kolom baru
-- Dijalankan sekali, berurutan. MySQL DDL auto-commit: tidak bisa di-
-- rollback. Pastikan backup mysqldump sudah ada sebelum menjalankan.
-- =====================================================================

-- ---------------------------------------------------------------------
-- BLOK 1.1 - tahun_ajaran
-- ---------------------------------------------------------------------
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

-- ---------------------------------------------------------------------
-- BLOK 1.2 - mapel
-- ---------------------------------------------------------------------
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

-- ---------------------------------------------------------------------
-- BLOK 1.3 - kelas (X TKJ 1 dan X TKJ 2 terpisah, per tahun ajaran)
-- ---------------------------------------------------------------------
CREATE TABLE `kelas` (
  `id_kelas` varchar(50) NOT NULL,
  `nama_kelas` varchar(50) NOT NULL COMMENT 'Contoh: X TKJ 1',
  `tingkat` varchar(10) NOT NULL COMMENT 'X / XI / XII',
  `id_tahun_ajaran` varchar(50) NOT NULL,
  `is_aktif` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_kelas`),
  UNIQUE KEY `uq_kelas_nama_tahun` (`nama_kelas`, `id_tahun_ajaran`),
  KEY `idx_kelas_tahun` (`id_tahun_ajaran`),
  CONSTRAINT `fk_kelas_tahun_ajaran` FOREIGN KEY (`id_tahun_ajaran`)
    REFERENCES `tahun_ajaran` (`id_tahun_ajaran`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ---------------------------------------------------------------------
-- BLOK 1.4 - anggota_kelas (riwayat siswa per kelas per tahun ajaran)
-- ---------------------------------------------------------------------
CREATE TABLE `anggota_kelas` (
  `id_anggota_kelas` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_kelas` varchar(50) NOT NULL,
  `id_user` varchar(50) NOT NULL COMMENT 'role=siswa',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_anggota_kelas`),
  UNIQUE KEY `uq_anggota_kelas` (`id_kelas`, `id_user`),
  KEY `idx_anggota_kelas_user` (`id_user`),
  CONSTRAINT `fk_anggota_kelas_kelas` FOREIGN KEY (`id_kelas`)
    REFERENCES `kelas` (`id_kelas`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_anggota_kelas_user` FOREIGN KEY (`id_user`)
    REFERENCES `user` (`id_user`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ---------------------------------------------------------------------
-- BLOK 1.5 - mapel_kelas (admin menentukan mapel yang diterima kelas)
-- ---------------------------------------------------------------------
CREATE TABLE `mapel_kelas` (
  `id_mapel_kelas` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `id_mapel` varchar(50) NOT NULL,
  `id_kelas` varchar(50) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_mapel_kelas`),
  UNIQUE KEY `uq_mapel_kelas` (`id_mapel`, `id_kelas`),
  KEY `idx_mapel_kelas_kelas` (`id_kelas`),
  CONSTRAINT `fk_mapel_kelas_mapel` FOREIGN KEY (`id_mapel`)
    REFERENCES `mapel` (`id_mapel`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_mapel_kelas_kelas` FOREIGN KEY (`id_kelas`)
    REFERENCES `kelas` (`id_kelas`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- ---------------------------------------------------------------------
-- BLOK 2.1 - kolom baru di `user`
-- status: "data keluar" = ubah status, BUKAN delete (FK CASCADE berbahaya)
-- ---------------------------------------------------------------------
ALTER TABLE `user`
  ADD COLUMN `status` varchar(20) NOT NULL DEFAULT 'aktif'
    COMMENT 'aktif/nonaktif - siswa/guru keluar diset nonaktif' AFTER `role`,
  ADD COLUMN `wajib_ganti_password` tinyint(1) NOT NULL DEFAULT '0'
    COMMENT '1 = dipaksa ganti password saat login berikutnya' AFTER `password`,
  ADD COLUMN `dibuat_oleh` varchar(50) DEFAULT NULL
    COMMENT 'id_user admin yang membuat akun ini' AFTER `wajib_ganti_password`,
  ADD COLUMN `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP AFTER `dibuat_oleh`,
  ADD COLUMN `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
    ON UPDATE CURRENT_TIMESTAMP AFTER `created_at`;

ALTER TABLE `user`
  ADD KEY `idx_user_dibuat_oleh` (`dibuat_oleh`),
  ADD CONSTRAINT `fk_user_dibuat_oleh` FOREIGN KEY (`dibuat_oleh`)
    REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;

-- ---------------------------------------------------------------------
-- BLOK 2.2 - nomor_induk unik (NISN siswa / NIP guru dipakai untuk login)
-- ---------------------------------------------------------------------
ALTER TABLE `user`
  ADD UNIQUE KEY `uq_user_nomor_induk` (`nomor_induk`);

-- ---------------------------------------------------------------------
-- BLOK 3.1 - Optional B: lepas kepemilikan konten dari U003
-- Dijalankan SEBELUM U003 jadi admin. Admin tidak mengelola materi/kuis.
-- ---------------------------------------------------------------------
UPDATE `materi` SET `id_user` = 'U001' WHERE `id_materi` = 'M002';
UPDATE `kuis`   SET `id_user` = 'U001' WHERE `id_kuis`   = 'K003';

-- ---------------------------------------------------------------------
-- BLOK 3.2 - role: operator -> admin (widen, migrasi data, narrow)
-- ---------------------------------------------------------------------
ALTER TABLE `user`
  MODIFY COLUMN `role` enum('guru','operator','siswa','admin') NOT NULL;

UPDATE `user` SET `role` = 'admin' WHERE `role` = 'operator';

ALTER TABLE `user`
  MODIFY COLUMN `role` enum('admin','guru','siswa') NOT NULL;

-- ---------------------------------------------------------------------
-- BLOK 4.1 - kolom baru di `kuis` (nullable dulu, backfill, baru NOT NULL)
-- tingkat_kesulitan pindah dari soal ke kuis: progres level berbasis KKM
-- ---------------------------------------------------------------------
ALTER TABLE `kuis`
  ADD COLUMN `tingkat_kesulitan` enum('mudah','sedang','sulit') NOT NULL DEFAULT 'mudah'
    COMMENT 'Level kuis; naik otomatis jika siswa lulus KKM' AFTER `kategori`,
  ADD COLUMN `id_mapel` varchar(50) DEFAULT NULL AFTER `tingkat_kesulitan`,
  ADD COLUMN `id_tahun_ajaran` varchar(50) DEFAULT NULL AFTER `id_mapel`;

ALTER TABLE `materi`
  ADD COLUMN `id_mapel` varchar(50) DEFAULT NULL AFTER `kelas`;

-- ---------------------------------------------------------------------
-- BLOK 5 - seed data master
-- ---------------------------------------------------------------------
INSERT INTO `tahun_ajaran`
  (`id_tahun_ajaran`, `nama`, `tanggal_mulai`, `tanggal_selesai`, `is_aktif`) VALUES
  ('TA001', '2026/2027', '2026-07-14', '2027-06-20', 1);

INSERT INTO `mapel` (`id_mapel`, `kode_mapel`, `nama_mapel`, `deskripsi`, `is_aktif`) VALUES
  ('MP001', 'TIK', 'Teknologi Informasi dan Komunikasi',
   'Mata pelajaran TIK untuk jurusan TKJ.', 1);

INSERT INTO `kelas` (`id_kelas`, `nama_kelas`, `tingkat`, `id_tahun_ajaran`, `is_aktif`) VALUES
  ('KLS001', 'X TKJ 1', 'X', 'TA001', 1),
  ('KLS002', 'X TKJ 2', 'X', 'TA001', 1);

INSERT INTO `mapel_kelas` (`id_mapel`, `id_kelas`) VALUES
  ('MP001', 'KLS001'),
  ('MP001', 'KLS002');

-- Siswa lama (user.kelas = 'X') dimasukkan ke X TKJ 1
INSERT INTO `anggota_kelas` (`id_kelas`, `id_user`)
SELECT 'KLS001', `id_user` FROM `user` WHERE `role` = 'siswa' AND `status` = 'aktif';

-- ---------------------------------------------------------------------
-- BLOK 4.2 - backfill lalu kunci kolom kuis/materi
-- ---------------------------------------------------------------------
UPDATE `kuis`   SET `id_mapel` = 'MP001', `id_tahun_ajaran` = 'TA001';
UPDATE `materi` SET `id_mapel` = 'MP001';

ALTER TABLE `kuis`
  MODIFY COLUMN `id_mapel` varchar(50) NOT NULL,
  MODIFY COLUMN `id_tahun_ajaran` varchar(50) NOT NULL;

ALTER TABLE `kuis`
  ADD KEY `idx_kuis_mapel` (`id_mapel`),
  ADD KEY `idx_kuis_tahun` (`id_tahun_ajaran`),
  ADD CONSTRAINT `fk_kuis_mapel` FOREIGN KEY (`id_mapel`)
    REFERENCES `mapel` (`id_mapel`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_kuis_tahun_ajaran` FOREIGN KEY (`id_tahun_ajaran`)
    REFERENCES `tahun_ajaran` (`id_tahun_ajaran`) ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE `materi`
  MODIFY COLUMN `id_mapel` varchar(50) NOT NULL;

ALTER TABLE `materi`
  ADD KEY `idx_materi_mapel` (`id_mapel`),
  ADD CONSTRAINT `fk_materi_mapel` FOREIGN KEY (`id_mapel`)
    REFERENCES `mapel` (`id_mapel`) ON DELETE RESTRICT ON UPDATE CASCADE;

-- ---------------------------------------------------------------------
-- BLOK 6 - Optional A: rename kategori pass-test -> pre-test
-- Dijalankan SETELAH semua blok di atas, SEBELUM kode PHP diubah.
-- ---------------------------------------------------------------------
UPDATE `kuis` SET `kategori` = 'pre-test' WHERE `kategori` = 'pass-test';

UPDATE `kuis` SET `judul` = REPLACE(`judul`, 'Pass-test', 'Pre-test')
  WHERE `judul` LIKE '%Pass-test%';

UPDATE `kuis` SET `deskripsi` = REPLACE(`deskripsi`, 'pass-test', 'pre-test')
  WHERE `deskripsi` LIKE '%pass-test%';

ALTER TABLE `kuis`
  MODIFY COLUMN `kategori` varchar(50) NOT NULL
    COMMENT 'post-test/pre-test/kuis harian/ulangan/remedial';
