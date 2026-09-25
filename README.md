# 🎓 ITIK - Sistem Terintegrasi Pendidikan SMKN 2 Jember

## 📌 Deskripsi Project
**ITIK** adalah platform manajemen dan evaluasi pendidikan yang dikembangkan khusus untuk studi kasus **SMK Negeri 2 Jember**. Sistem ini bertujuan untuk meminimalisir *human error* dalam manajemen ujian dan memberikan kemudahan akses bagi guru serta siswa melalui ekosistem Web (Dashboard Admin/Guru) dan Mobile (Aplikasi Siswa).

## ✨ Fitur Utama
* **Manajemen materi dan Soal Massal:** Guru dapat mengunduh *template* Excel, mengisinya secara *offline*, dan mengunggahnya (import) kembali ke sistem.
* **Dashboard Terintegrasi:** Web admin berbasis Laravel untuk manajemen data akademik.
* **Aplikasi Ujian Siswa:** Aplikasi *mobile* berbasis Flutter yang ringan dan mudah digunakan siswa.

## 🛠️ Teknologi yang Digunakan
* **Backend & API:** Laravel (PHP)
* **Frontend Web:** Bootstrap 5 / Blade Template
* **Mobile App:** Flutter (Dart)
* **Database:** MySQL

## 👥 Tim Pengembang (Kelompok 1)
Berikut adalah pembagian peran dalam pengembangan sistem ITIK:

| Nama Anggota | Peran / Tugas Spesifik |
| :--- | :--- |
| **Sa'dan Arya Diputra** | **Project Manager & System Analyst** (Merancang ERD, Flowchart, API, & mengawasi repository) |
| **Achamd Kevin Arisandi** | **Mobile Developer** (Mengembangkan aplikasi Flutter dan integrasi REST API) |
| **Marshanda Gracella Andryanta** | **Backend Developer** (Membuat logika Laravel, Database Migration, & fitur Import Excel) |
| **Natasya Mei Dista** | **Frontend Web Developer** (Melakukan Slicing UI dan integrasi layout Blade Laravel) |
| **Muhammad Ryo Fabriyansah** | **UI/UX Designer & QA** (Merancang desain Figma awal dan melakukan *testing* sistem) |

## 🚀 Cara Instalasi (Local Development)

### Persyaratan:
* PHP >= 8.3 (Laragon)
* Composer
* Flutter

### Langkah-langkah (Web Laravel):
1. Clone repository ini: `git clone https://github.com/nyam-nyam-project/ITIK-WEBISITE-DESKTOP.git`
2. Masuk ke folder project: `cd ITIK-WEBISITE-DESKTOP `
3. Install dependencies: `composer install`
4. Copy file environment: `cp .env.example .env` 
5. Generate app key: `php artisan key:generate`
6. Jalankan migrasi database: `php artisan migrate`
7. Jalankan server lokal: `php artisan serve`

### 🔄 SOP Harian GitHub (Penggunaan Branch)
1. Ambil Kode Terbaru Dulu:
   `git checkout main`
   `git pull origin main`
2. Buat Cabang (Branch) Baru
   `git checkout -b (perlu isi nama-tugas/halaman/fitur)`
3. mulailah coding 
4. jika sudah Mulai Ngoding & Simpan (Commit)
   `git add .`
   `git commit -m "(perlu isi nama-tugas/halaman/fitur)"`
5. Kirim Branch ke GitHub (Push)
   `git push origin (perlu isi nama-tugas/halaman/fitur)`

### 📝 Aturan Penulisan Pesan Commit
Agar riwayat pengembangan proyek tetap rapi dan mudah dilacak, tim wajib menggunakan standar awalan (*prefix*) berikut saat melakukan `git commit -m "..."`:

| Awalan (Prefix) | Fungsi / Penjelasan | Contoh Penggunaan |
| :--- | :--- | :--- |
| **`feat:`** | **(Feature)** Menambah fitur, halaman, atau fungsi baru pada sistem. | `feat: membuat halaman dashboard siswa` |
| **`fix:`** | **(Bug Fix)** Memperbaiki *error* atau *bug* pada aplikasi. | `fix: memperbaiki tombol login yang tidak berfungsi` |
| **`docs:`** | **(Documentation)** Mengubah dokumen non-kode seperti file `README.md`. | `docs: menambahkan cara instalasi database` |
| **`style:`** | **(Styling)** Merapikan format kode (spasi, indentasi) tanpa mengubah logika. | `style: merapikan spasi pada file controller` |
| **`refactor:`** | **(Refactoring)** Merombak atau mengoptimalkan struktur kode agar lebih bersih. | `refactor: menyederhanakan logika perhitungan nilai` |
| **`chore:`** | **(Chores)** Tugas pemeliharaan teknis (install package, ubah konfigurasi). | `chore: install package bootstrap untuk frontend` |