-- Migration: Tambahkan kolom generated aktif_unik dan unique key pada tahun_ajaran
-- Memastikan hanya ada maksimal 1 baris tahun_ajaran dengan is_aktif = 1 (NULL diperbolehkan banyak di UNIQUE KEY)
ALTER TABLE tahun_ajaran
  ADD COLUMN aktif_unik TINYINT GENERATED ALWAYS AS (IF(is_aktif = 1, 1, NULL)) STORED,
  ADD UNIQUE KEY uq_hanya_satu_aktif (aktif_unik);
