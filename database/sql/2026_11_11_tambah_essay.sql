ALTER TABLE `soal`
  ADD `tipe` enum('pilihan_ganda','essay') NOT NULL DEFAULT 'pilihan_ganda' AFTER `id_kuis`;

ALTER TABLE `draft_jawaban`
  ADD `jawaban_teks` text AFTER `jawaban_dipilih`;

CREATE TABLE `nilai_essay` (
  `id_nilai_essay` int NOT NULL,
  `id_mengerjakan` int NOT NULL,
  `id_soal` varchar(50) NOT NULL,
  `jawaban_teks` text,
  `nilai` decimal(5,2) DEFAULT NULL,
  `catatan_guru` text,
  `id_penilai` varchar(50) DEFAULT NULL,
  `dinilai_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

ALTER TABLE `nilai_essay`
  ADD PRIMARY KEY (`id_nilai_essay`),
  ADD UNIQUE KEY `uq_nilai_essay` (`id_mengerjakan`,`id_soal`),
  ADD KEY `fk_nilai_essay_soal` (`id_soal`),
  ADD KEY `fk_nilai_essay_penilai` (`id_penilai`);

ALTER TABLE `nilai_essay`
  MODIFY `id_nilai_essay` int NOT NULL AUTO_INCREMENT;

ALTER TABLE `nilai_essay`
  ADD CONSTRAINT `fk_nilai_essay_mengerjakan` FOREIGN KEY (`id_mengerjakan`) REFERENCES `mengerjakan` (`id_mengerjakan`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_nilai_essay_soal` FOREIGN KEY (`id_soal`) REFERENCES `soal` (`id_soal`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_nilai_essay_penilai` FOREIGN KEY (`id_penilai`) REFERENCES `user` (`id_user`) ON DELETE SET NULL ON UPDATE CASCADE;
