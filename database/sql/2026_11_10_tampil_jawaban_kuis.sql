-- Menambahkan kolom tampil_jawaban pada tabel kuis
-- Mengontrol kapan siswa boleh melihat evaluasi benar/salah per soal
ALTER TABLE kuis
  ADD COLUMN tampil_jawaban ENUM('tidak','setelah_submit','setelah_kkm')
    NOT NULL DEFAULT 'setelah_submit'
    COMMENT 'Kapan siswa boleh melihat benar/salah per soal' AFTER is_aktif;
