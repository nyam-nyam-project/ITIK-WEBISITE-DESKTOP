-- Mengosongkan data email untuk akun dengan role siswa dan guru
-- Siswa dan guru login murni menggunakan nomor induk (NISN/NIP)
UPDATE user
SET email = NULL
WHERE role IN ('siswa', 'guru');
