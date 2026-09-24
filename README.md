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

## 👥 Tim Pengembang (Kelompok 5)
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