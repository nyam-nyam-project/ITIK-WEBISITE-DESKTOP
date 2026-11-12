-- Menambahkan kolom jumlah_pelanggaran pada tabel sesi_kuis untuk mencatat pelanggaran CBT
ALTER TABLE sesi_kuis ADD COLUMN jumlah_pelanggaran TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER waktu_selesai;
