-- Verifikasi Fase 1. Jalankan setelah 2026_10_29_fase1_admin_schema.sql.
-- Semua hasil harus sesuai kolom "harapan".

SELECT 'tabel baru (harapan: 5)' AS cek, COUNT(*) AS hasil
FROM information_schema.tables
WHERE table_schema = 'itik'
  AND table_name IN ('tahun_ajaran', 'mapel', 'kelas', 'anggota_kelas', 'mapel_kelas');

SELECT 'role enum (harapan: admin,guru,siswa)' AS cek, COLUMN_TYPE AS hasil
FROM information_schema.columns
WHERE table_schema = 'itik' AND table_name = 'user' AND column_name = 'role';

SELECT 'user per role' AS cek, `role`, `status`, COUNT(*) AS hasil
FROM `user` GROUP BY `role`, `status`;

SELECT 'kolom user baru (harapan: 5)' AS cek, COUNT(*) AS hasil
FROM information_schema.columns
WHERE table_schema = 'itik' AND table_name = 'user'
  AND column_name IN ('status', 'wajib_ganti_password', 'dibuat_oleh', 'created_at', 'updated_at');

SELECT 'kuis tanpa mapel/tahun (harapan: 0)' AS cek, COUNT(*) AS hasil
FROM `kuis` WHERE `id_mapel` IS NULL OR `id_tahun_ajaran` IS NULL;

SELECT 'materi tanpa mapel (harapan: 0)' AS cek, COUNT(*) AS hasil
FROM `materi` WHERE `id_mapel` IS NULL;

SELECT 'sisa kategori pass-test (harapan: 0)' AS cek, COUNT(*) AS hasil
FROM `kuis` WHERE `kategori` = 'pass-test';

SELECT 'kategori kuis sekarang' AS cek, `kategori`, COUNT(*) AS hasil
FROM `kuis` GROUP BY `kategori`;

SELECT 'konten milik admin (harapan: 0)' AS cek,
       (SELECT COUNT(*) FROM `kuis` k JOIN `user` u ON u.id_user = k.id_user WHERE u.role = 'admin')
     + (SELECT COUNT(*) FROM `materi` m JOIN `user` u ON u.id_user = m.id_user WHERE u.role = 'admin') AS hasil;

SELECT 'tahun ajaran aktif (harapan: 1)' AS cek, COUNT(*) AS hasil
FROM `tahun_ajaran` WHERE `is_aktif` = 1;

SELECT 'kelas terdaftar' AS cek, k.nama_kelas, COUNT(a.id_user) AS jumlah_siswa
FROM `kelas` k LEFT JOIN `anggota_kelas` a ON a.id_kelas = k.id_kelas
GROUP BY k.id_kelas, k.nama_kelas;

SELECT 'mapel per kelas' AS cek, k.nama_kelas, m.nama_mapel
FROM `mapel_kelas` mk
JOIN `kelas` k ON k.id_kelas = mk.id_kelas
JOIN `mapel` m ON m.id_mapel = mk.id_mapel;
