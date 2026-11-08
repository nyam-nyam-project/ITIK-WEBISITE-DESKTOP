CREATE TABLE
    pengajaran (
        id_pengajaran INT UNSIGNED NOT NULL AUTO_INCREMENT,
        id_user VARCHAR(50) NOT NULL,
        id_kelas VARCHAR(50) NOT NULL,
        id_mapel VARCHAR(50) NOT NULL,
        created_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id_pengajaran),
        UNIQUE KEY uq_pengajaran (id_user, id_kelas, id_mapel),
        CONSTRAINT fk_pengajaran_user FOREIGN KEY (id_user) REFERENCES user (id_user) ON UPDATE CASCADE ON DELETE RESTRICT,
        CONSTRAINT fk_pengajaran_mapel_kelas FOREIGN KEY (id_mapel, id_kelas) REFERENCES mapel_kelas (id_mapel, id_kelas) ON UPDATE CASCADE ON DELETE RESTRICT
    ) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;