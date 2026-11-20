# Wasiat AI (Handoff Document)

> Terakhir diperbarui: 2026-11-20 (Optimasi Dashboard Siswa — Filter Materi/Kuis Belum Selesai, Banner Notifikasi, Perbaikan Cakupan Materi Guru). Proyek: **ITIK** — web media pembelajaran TIK untuk SMK TKJ (Laravel 13.34, MySQL 8.4.3 via Laragon).
> Pemilik proyek adalah pelajar berbahasa Indonesia — jelaskan langkah demi langkah dan pakai bahasa Indonesia.

---

## Status Terakhir

**2026-11-20 — Optimasi Dashboard Siswa & Perbaikan Statistik Guru (Cakupan Materi).**
- **Filter Materi Terbaru di Dashboard Siswa (`SiswaDashboardController`):**
  - Query `materiTerbaru` kini hanya menampilkan materi yang progresnya berstatus `'belum mulai'` atau `'sedang belajar'` (kecualikan status `'selesai'`).
  - Jika semua materi sudah selesai dibaca, dashboard menampilkan pesan ramah: "Hebat! Semua materi sudah selesai dibaca."
- **Filter Kuis Terbaru di Dashboard Siswa:**
  - Query `kuisTerbaru` kini hanya menampilkan kuis yang belum pernah dikerjakan oleh siswa yang sedang login (menggunakan `whereDoesntHave('mengerjakan')`).
  - Jika semua kuis sudah dikerjakan, dashboard menampilkan pesan ramah: "Semua kuis sudah dikerjakan."
- **Badge 'Sedang Belajar' Menjadi Kuning (`resources/views/siswa/dashboard.blade.php`):**
  - Badge status `Sedang Belajar` diubah dari `class="badge"` menjadi `class="badge badge-warning"` agar konsisten dengan warna kuning di halaman daftar materi siswa.
  - CSS `.badge-warning` sudah terdefinisi di `public/css/app.css`.
- **Banner Notifikasi Kuning Dinamis:**
  - Ditambahkan banner berwarna kuning (class `.banner-pengingat`) tepat di bawah deretan 4 card statistik atas dashboard siswa.
  - Banner hanya tampil jika siswa masih memiliki materi belum selesai (`$materiBelumSelesai > 0`) atau kuis belum dikerjakan (`$jumlahKuisBelum > 0`).
  - Jika semuanya sudah selesai, banner otomatis disembunyikan.
  - CSS banner ditambahkan di `public/css/app.css` (class `.banner-pengingat`, `.banner-pengingat-content`, `.banner-pengingat-icon`, `.banner-pengingat-actions`).
- **Perbaikan Perhitungan Cakupan Materi di Statistik Guru (`app/Services/DashboardKelasService.php`):**
  - Metode `detailSiswa()`: query `readMaterialsCount` ditambahkan `->where('status', 'selesai')` agar hanya materi yang benar-benar selesai yang terhitung (bukan `'sedang belajar'`).
  - Metode `dataSiswaKelas()`: query `$progress` untuk daftar siswa ditambahkan `->where('status', 'selesai')` agar persentase cakupan materi per siswa akurat.
- **Pengujian Terotomasi:**
  - `tests/Feature/SiswaDashboardOptimasiTest.php` (baru): 4 test — filter materi belum selesai, filter kuis belum dikerjakan, banner tampil jika ada yang belum selesai, banner tersembunyi jika semua selesai.
  - `tests/Feature/DashboardKelasServiceTest.php` (diperbarui): ditambahkan test `test_detail_siswa_and_daftar_siswa_only_counts_completed_materials_progress()`.
  - Seluruh 27 tes lulus 100%.

**2026-11-20 — Peningkatan Pemilih Jadwal Kuis (Flatpickr 24 Jam Bahasa Indonesia) & Validasi Ketat Jadwal.**
- **Integrasi Flatpickr 24 Jam & Bahasa Indonesia via CDN:**
  - Dipasang pada form tambah kuis (`resources/views/guru/kuis/create.blade.php`) dan form edit kuis (`resources/views/guru/kuis/edit.blade.php`) melalui assets `public/js/guru/kuis/form.js`, `public/css/guru/kuis/create.css`, dan `public/css/guru/kuis/edit.css`.
  - Format pengiriman ke backend: `Y-m-d H:i`, dengan format tampilan ramah pengguna: `d M Y, H:i` (contoh: "20 Nov 2026, 14:00").
  - Menghindari inline scripts/styles/event-handlers demi kepatuhan penuh terhadap standar pemisahan view (`ViewSeparationTest`).
  - Menambahkan tombol kecil ikon silang (×) di samping kolom input untuk mengosongkan jadwal terbit (`waktu_mulai`) atau selesai (`waktu_selesai`) dengan mudah.
  - Memastikan nilai `waktu_mulai` dan `waktu_selesai` yang sudah ada di database termuat dengan tepat saat membuka halaman edit kuis.
- **Blokir Tanggal & Jam di Masa Lalu (`waktu_mulai`):**
  - Mengunci `minDate: 'today'` pada pemilih waktu terbit agar tanggal di masa lalu tidak bisa diklik.
  - Logika dinamis pada event `onOpen` dan `onChange`: jika memilih hari ini, batas minimal waktu (`minTime`) otomatis dikunci ke jam dan menit saat ini (`HH:mm`), sehingga jam yang sudah lewat tidak bisa dipilih. Jika memilih hari besok atau seterusnya, `minTime` kembali ke `00:00`.
- **Kunci & Sinkronisasi `waktu_selesai` terhadap `waktu_mulai`:**
  - Jika `waktu_mulai` terisi, batas minimal tanggal pada `waktu_selesai` dikunci mengikuti `waktu_mulai`. Jika memilih hari yang sama, jam selesai dikunci minimal 1 menit setelah jam terbit.
  - Jika `waktu_mulai` kosong, batas minimal `waktu_selesai` memakai waktu saat ini (`now`).
  - Jika guru mengubah `waktu_mulai` menjadi lebih lambat dari `waktu_selesai` yang sebelumnya sudah diisi, `waktu_selesai` otomatis dikosongkan untuk mencegah jadwal terbalik.
- **Validasi Backend Ketat di `KuisController@store` & `KuisController@update`:**
  - Berlaku untuk seluruh kategori kuis (bukan hanya Ulangan):
    - Jika `waktu_mulai` diisi, ditolak jika `Carbon::parse($request->waktu_mulai)->lt(now()->startOfMinute())` dengan pesan "Waktu mulai tidak boleh di masa lalu.".
    - Jika `waktu_selesai` diisi, ditolak jika `Carbon::parse($request->waktu_selesai)->lt(now()->startOfMinute())` dengan pesan "Waktu selesai tidak boleh di masa lalu.".
    - Jika `waktu_mulai` dan `waktu_selesai` sama-sama diisi, ditolak jika `waktu_selesai <= waktu_mulai` dengan pesan "Waktu selesai harus setelah waktu mulai.".
    - Khusus kategori Ulangan, kedua kolom jadwal tetap wajib diisi.
- **Pengujian Terotomasi (`tests/Feature/KuisJadwalValidationTest.php`):**
  - Menguji penolakan `waktu_mulai` di masa lalu pada `store` dan `update`.
  - Menguji penolakan `waktu_selesai` di masa lalu pada `store`.
  - Menguji penolakan jadwal terbalik (`waktu_selesai <= waktu_mulai`) pada `store` dan `update`.
  - Menguji penerimaan jadwal yang valid.
  - Seluruh pengujian lulus 100%.


**2026-11-19 — Perbaikan Auto-Publish & Auto-Draft Jadwal Kuis (Sinkronisasi Timezone WIB / Asia/Jakarta).**
- **Masalah Timezone (Selisih 7 Jam):**
  - Sebelumnya `config/app.php` menggunakan `'timezone' => 'UTC'`.
  - Ketika guru menyetel waktu di browser (WIB / UTC+7), jam yang tersimpan di MySQL adalah waktu lokal (misal 19:18). Namun Laravel mengevaluasi waktu saat ini dalam UTC (12:18).
  - Akibatnya, sistem menganggap jam mulai kuis masih 7 jam di masa depan, sehingga kuis tidak kunjung terbit otomatis. Begitu pula saat waktu selesai lewat, kuis tidak otomatis kembali ke draft.
- **Perbaikan yang Diterapkan:**
  - `config/app.php`: Diubah menjadi `'timezone' => env('APP_TIMEZONE', 'Asia/Jakarta')`.
  - `.env` & `.env.example`: Ditambahkan `APP_TIMEZONE=Asia/Jakarta`.
  - `KuisController`: Menambahkan panggilan `Kuis::sinkronisasiStatus()` pada method `store()` dan `update()` agar status langsung tersinkron seketika guru menyimpan form kuis.
  - `SiswaKuisController`: Menambahkan panggilan `Kuis::sinkronisasiStatus()` pada method `mulai()` agar kuis yang jadwalnya baru tiba langsung terbit dan dapat dikerjakan siswa.
  - Perbaikan fixture test `KuisAutoPublishTest.php` memakai `RefreshDatabase` dengan skema `tm_` (2 tes lulus 100%).

**2026-11-19 — Migrasi Kolom tipe & tingkat_kesulitan pada tm_soal, jawaban_teks pada tr_draft_jawaban, dan Sistem Kotak Arsip Terpusat.**
- **Migrasi Kolom `tipe` & `tingkat_kesulitan` (`tm_soal`) serta `jawaban_teks` (`tr_draft_jawaban`):**
  - Menambahkan migrasi `database/migrations/2026_11_19_191000_add_tipe_and_tingkat_kesulitan_to_tm_soal_table.php`.
  - Pada `tm_soal`: menambahkan kolom `tipe` (`enum('pilihan_ganda', 'essay')` default `'pilihan_ganda'`) dan `tingkat_kesulitan` (`varchar(50)` nullable).
  - Pada `tr_draft_jawaban`: menambahkan kolom `jawaban_teks` (`text` nullable) untuk menampung draf jawaban soal essay siswa sebelum di-submit.
  - Mengatasi error `SQLSTATE[42S22]: Unknown column 'tipe' in 'field list'` saat guru menambahkan butir soal baru (pilihan ganda maupun essay).
- **Migrasi Kolom `tampil_jawaban` (`tm_kuis`):**
  - Menambahkan migrasi `database/migrations/2026_11_19_141500_add_tampil_jawaban_to_tm_kuis_table.php` dengan tipe `enum('tidak', 'setelah_submit', 'setelah_kkm')` default `'setelah_submit'` pada tabel `tm_kuis`.
  - Mengatasi error `SQLSTATE[42S22]: Unknown column 'tampil_jawaban'` saat guru membuat kuis baru.
- **Sistem Kotak Arsip Terpusat (Standar Industri Recycle Bin):**
  - **Migrasi `is_aktif` pada `tm_materi`:** Menambahkan kolom `is_aktif` (boolean default true) pada tabel `tm_materi` via migrasi `database/migrations/2026_11_19_144000_add_is_aktif_to_tm_materi_table.php`.
  - **Mekanisme Soft Delete:**
    - Kuis dan Materi di daftar utama sekarang menggunakan aksi **"Arsip"** (`is_aktif = false`), bukan langsung dihapus permanen.
    - File fisik PDF materi dan data kuis tetap tersimpan aman saat diarsip agar tidak merusak riwayat belajar siswa maupun memicu error *Foreign Key Constraint*.
  - **Penempatan Tombol Akses Arsip:**
    - Tombol **"📁 Kotak Arsip"** diletakkan di baris judul halaman (pojok kanan atas `.page-heading`) pada halaman daftar Materi (`resources/views/guru/materi/index.blade.php`) dan daftar Kuis (`resources/views/guru/kuis/index.blade.php`), berdampingan dengan tombol tambah data.
  - **Halaman Kotak Arsip (`guru.arsip.index`):**
    - File controller: `app/Http/Controllers/Guru/ArsipController.php`.
    - File view: `resources/views/guru/arsip/index.blade.php`.
    - File css: `public/css/guru/arsip/index.css`.
    - Fitur tab terpisah: **📝 Kuis Diarsipkan (X)** dan **📚 Materi Diarsipkan (Y)** dengan filter mata pelajaran.
    - Aksi per item:
      - **Pulihkan (*Restore*):** Mengembalikan kuis/materi ke daftar aktif dengan `is_aktif = true`.
      - **Hapus Permanen (*Force Delete*):**
        - Untuk Materi: Menghapus data dari tabel `tm_materi` sekaligus membersihkan file fisik PDF dari storage.
        - Untuk Kuis: Dilindungi validasi `$kuis->mengerjakan()->exists()`. Jika sudah ada siswa yang mengerjakan, kuis **tidak bisa dihapus permanen** agar nilai siswa tidak rusak, dan guru diarahkan untuk membiarkannya tetap di arsip. Kuis tanpa riwayat siswa dapat dihapus tuntas beserta relasi soalnya.
  - **Penyesuaian Filter Siswa & Dashboard:**
    - `ProgressController`: Hanya menampilkan dan membolehkan akses siswa ke materi dengan `is_aktif = true`.
    - `DashboardKelasService`: Menghitung capaian progres literasi materi hanya dari materi dengan `is_aktif = true`.
  - **Pengujian Otomatis:**
    - Dibuat pengujian fitur lengkap di `tests/Feature/ArsipPembelajaranTest.php` (5 pengujian, 20 assertions — 100% HIJAU lulus).
    - `DashboardKelasPageTest` (4 pengujian, 42 assertions — 100% HIJAU lulus).

**2026-11-19 — Perbaikan PDF Viewer 1 Halaman Ala SLearn, Stream Route PDF, dan Penataan Layout Materi.**
- **Route Stream PDF (`/materi/{id}/pdf`):** Menambahkan route `GET /materi/{id}/pdf` via `MateriController@streamPdf` yang langsung membaca file fisik dari storage server dan mengembalikannya dengan header `Content-Type: application/pdf`. Ini menyelesaikan masalah CORS dan akses statis `/storage/...` pada lingkungan `php artisan serve` maupun virtual host Laragon.
- **`Materi::pdfUrl()`:** Diperbarui agar mengembalikan URL route relatif `route('materi.pdf', $this->id_materi, false)` (same-origin).
- **PDF Viewer 1 Halaman Aktif (`pdf-viewer.js`):**
  - Mengubah mode render dari semua halaman berurutan menjadi mode 1 halaman aktif per tampilan (`#pdf-canvas`).
  - Menerapkan pembatalan render task (`currentRenderTask.cancel()`) dengan penanganan `RenderingCancelledException` agar perpindahan halaman cepat tidak bentrok.
  - Setiap pergantian halaman otomatis mereset scroll container ke bagian atas (`scrollTop = 0`).
  - Menampilkan pesan error detail (`err.message`) dan log konsol jika dokumen PDF gagal dimuat.
- **Bilah Navigasi Bawah & UX Viewer:**
  - Toolbar atas: Status jumlah halaman + tombol **⛶ Layar Penuh**.
  - Area kanvas: Kertas kanvas putih berbayang halus (`box-shadow: 0 2px 8px rgba(0,0,0,0.12)`) di atas latar abu-abu terang (`#eef1f5`).
  - Bilah navigasi bawah: Tombol **Kembali** (disabled di hal. 1), Indikator dinamis `Halaman X / Y`, dan tombol **Selanjutnya** (disabled di hal. akhir).
  - Berlaku seragam untuk portal Siswa (`resources/views/siswa/materi/show.blade.php`, `public/css/siswa/materi/show.css`) dan portal Guru (`resources/views/guru/materi/show.blade.php`, `public/css/guru/materi/show.css`).
- **Tata Letak Kartu Materi:**
  - Kotak "Video Pembelajaran" (YouTube) dipindahkan dari bawah kanvas PDF (kolom kiri) ke kolom sebelah kanan (di bawah kartu Informasi Materi).
  - Kolom kiri kini berfokus penuh pada dokumen materi (PDF/teks), menghasilkan tinggi kedua kolom yang seimbang dan simetris di tampilan desktop.
- **Dashboard Guru:**
  - Warna kartu grafik progres kelas diselaraskan ke tema putih bersih + aksen ungu ITIK.
  - Hapus halaman `welcome.blade.php` & `welcome.css` lama karena root path (`/`) telah diarahkan langsung ke login/dashboard.

**2026-11-15 — Dashboard Guru per Kelas, Kuis per Kelas via tp_kuis_kelas, Materi per Mapel, dan Penanganan Essay Pending.**
- **Pembagian kuis per kelas (`tp_kuis_kelas`):** Query kuis pada `DashboardKelasService::dataSiswaKelas` dan `detailSiswa` menggabungkan `tm_kuis` dengan pivot `tp_kuis_kelas`. Kuis yang berada di mata pelajaran atau tahun ajaran yang sama tetapi tidak ditugaskan ke kelas yang dipilih tidak lagi ikut terhitung ke total peluang kuis maupun progres siswa.
- **Cakupan materi per mata pelajaran:** Filter kolom teks `kelas` pada query `$materials` dihilangkan di `dataSiswaKelas` dan `detailSiswa`. Cakupan materi dihitung penuh terhadap seluruh materi pada mata pelajaran tersebut.
- **Status essay pending ("Menunggu penilaian"):** Di `DashboardController::siapkanDetailSiswa`, pengerjaan kuis essay final yang belum dinilai (`nilai === null`) tidak lagi dihitung sebagai "belum lulus", melainkan dipisahkan ke metrik `'menunggu'`. Pada tampilan detail siswa Blade, ditampilkan teks rincian `· X menunggu penilaian` secara transparan jika ada essay yang menunggu penilaian guru.
- **Progres dan kelulusan kategori (Opsi A - Ketuntasan terhadap Total Target Kuis):**
  - `jumlah_peluang` = jumlah siswa aktif di kelas × jumlah kuis aktif kelas itu pada kategori/mapel/tahun ajaran.
  - `jumlah_dikerjakan` = kesempatan siswa-kuis unik yang memiliki pengerjaan `final`. Pengerjaan dibatalkan tidak dihitung; pengerjaan final dengan nilai `NULL` tetap dihitung sebagai progres.
  - **Persentase kelulusan kategori** dihitung terhadap total target peluang kuis: `(jumlah_lulus / jumlah_peluang) * 100%`.
  - Contoh: 1 siswa di kelas dengan 3 kuis aktif, baru menyelesaikan 1 kuis dan lulus KKM: persentase kelulusan = **33.33%** (1 dari 3 kesempatan tuntas), bukan 100%.
- **Migration & Database:** Tabel `tp_kuis_kelas` sudah aktif di database MySQL `itik` (batch 5, 6 baris backfill). File migration `database/migrations/2026_11_15_050000_create_tp_kuis_kelas_table.php` disesuaikan agar query backfill kompatibel ganda (`INSERT IGNORE` untuk MySQL dan `INSERT OR IGNORE` untuk SQLite).
- **Grafik Progres Kelas Utama (Harmonis Tema Putih & Ungu ITIK):**
  - Panel card `.dashboard-progres-card` pada dashboard kelas guru diselaraskan warnanya dengan tema sistem (`white`, `var(--purple)`, `var(--border)`, `var(--ink)`):
    - **Latar & Bingkai:** Background putih bersih dengan border halus `var(--border)` dan rounded corners 16px, senada dengan kartu ringkasan dan tabel siswa.
    - **Sisi Kiri:** Cincin bulat SVG materi kelas dengan aksen ungu `var(--purple)` dan background ring `var(--purple-light)` (`$progresKelas['persentase_materi']%`).
    - **Sisi Kanan:** Judul `"Progres kuis per kategori"` dengan font gelap `var(--ink)`, daftar kategori dengan rincian `X dari Y · A lulus · B belum`, dan progress bar horizontal ungu `var(--purple)` di atas track abu-abu terang `#f0eff4`.
    - Menampilkan progres agregat kelas tanpa nama/NISN individu.
- **Pengujian & Verifikasi (PHP 8.4.27):**
  - `DashboardKelasServiceTest`: 6 tes / 26 assertion lulus.
  - `DashboardKelasPageTest`: 4 tes / 46 assertion lulus.
  - `DetailSiswaDashboardTest`: 5 tes / 38 assertion lulus.
  - Total: **15 tes / 110 assertion lulus 100%**.
  - Linter Laravel Pint lulus untuk semua file PHP yang diubah.

**2026-11-13 — Form Materi: Dropdown Kelas dan Bab Unik (migration belum diterapkan).**
- Form tambah dan edit materi memakai dropdown kelas aktif dari tahun ajaran aktif; nilai kelas lama tetap tersedia saat mengedit materi lama.
- Backend web dan API menolak bab yang sama untuk kombinasi mata pelajaran dan kelas yang sama. Bab yang sama tetap diperbolehkan pada kelas atau mata pelajaran lain.
- Migration `2026_11_13_031104_add_unique_bab_index_to_tm_materi_table.php` menambahkan unique index `uq_materi_mapel_kelas_bab` sebagai perlindungan database. Migration ini belum dijalankan; setelah backup, jalankan `php artisan migrate`.
- Tes regresi baru untuk form web dan endpoint API lulus: 8 tes, 72 assertion. Tes lama `ApiGuruMateriTest` masih memiliki fixture tabel tanpa prefiks dan gagal sebelum tes dijalankan; fixture tersebut tidak diubah dalam pekerjaan ini.

**2026-11-13 — Persiapan Migration Tabel yang Tertinggal (BELUM diterapkan ke database).**
- Dibuat migration untuk `tm_materis`, `tr_nilai_essay`, `tr_pengajaran`, dan `password_reset_tokens`.
- Model `Pengajaran` dan `NilaiEssay` dipetakan ke tabel berprefiks; query pada `Mengerjakan` dan pemeriksaan skema pada `ValidasiNilaiController` juga memakai `tr_pengajaran`.
- Pemeriksaan read-only terhadap database MySQL `itik` menunjukkan empat tabel target dan tabel lama `pengajaran`, `nilai_essay`, `materis` belum ada. `password_reset_tokens` tercantum di `database/itik.sql`, tetapi belum ada di database aktif.
- Semua migration masih **Pending** menurut `migrate:status`. `php artisan migrate --pretend` terhadap MySQL menghasilkan DDL tanpa error; **belum menjalankan `php artisan migrate`**.
- Ketiga tes regresi di `tests/Feature/PrefixedModelTableTest.php` lulus; semua migration juga berhasil dijalankan pada SQLite in-memory. Setelah backup database, migration biasa dapat dijalankan dengan `php artisan migrate`. Jangan jalankan `migrate:fresh` atau rollback pada database aktif.
- Catatan skema: `tr_pengajaran.id_kelas` tetap `NOT NULL` sesuai SQL, walau beberapa query lama menangani nilai `NULL` untuk penugasan global. Hal ini tidak menghalangi migration, tetapi perlu keputusan terpisah jika penugasan global memang digunakan.

**2026-11-11 — Satu Tahun Aktif Dijaga Database Constraint & Fitur Salin Tahun Ajaran (B2. Poin 3).**
- **Database Constraint Satu Tahun Aktif:**
  - Ditambahkan generated column dan unique key pada MySQL production dan file migrasi `database/sql/2026_11_11_tahun_ajaran_aktif_unik.sql` serta skema `database/itik.sql`:
    ```sql
    ALTER TABLE tahun_ajaran
      ADD COLUMN aktif_unik TINYINT GENERATED ALWAYS AS (IF(is_aktif = 1, 1, NULL)) STORED,
      ADD UNIQUE KEY uq_hanya_satu_aktif (aktif_unik);
    ```
  - Memastikan secara mutlak di level engine basis data bahwa tidak mungkin ada lebih dari 1 tahun ajaran yang bernilai `is_aktif = 1` bersamaan. Nilai `NULL` untuk tahun nonaktif tetap diperbolehkan jamak oleh unique index.
- **Fitur Salin ke Tahun Baru (`Admin\TahunAjaranController@salin` & `resources/views/admin/tahun-ajaran/index.blade.php`):**
  - **Route:** `POST /admin/tahun-ajaran/{sumber}/salin` dengan nama `admin.tahun-ajaran.salin`.
  - **Controller Logic:**
    - Berjalan dalam `DB::transaction`.
    - Membuat ID baru via trait `MenghasilkanId` (`idBerikutnya('tahun_ajaran', ...)`).
    - Menetapkan nama baru yang aman dari batas kolom `varchar(20)` dengan suffix `(Salinan)` / `(Sal.X)`.
    - Menyalin semua kelas milik tahun ajaran sumber (`nama_kelas` dan `tingkat`) dengan ID baru dan tahun baru, status `is_aktif = true`.
    - Menyalin relasi kurikulum mata pelajaran `mapel_kelas` untuk tiap kelas baru yang terbuat.
    - **Tidak menyalin data penugasan `pengajaran` maupun `anggota_kelas`**, sehingga admin dapat menentukan guru pengajar dan siswa secara terpisah untuk tahun ajaran baru.
    - Status tahun baru diinisialisasi `is_aktif = 0` (nonaktif).
  - **Tampilan View:** Tombol aksi `Salin` pada setiap baris tahun ajaran dilengkapi dialog konfirmasi `data-confirm` tanpa inline JS/CSS.
- **Pengujian (`tests/Feature/AdminPanelTest.php`):**
  - Skema SQLite in-memory test disesuaikan dengan generated column `storedAs('CASE WHEN is_aktif = 1 THEN 1 ELSE NULL END')->unique()`.
  - `test_salin_tahun_ajaran_menyalin_kelas_dan_mapel_tanpa_pengajaran_atau_anggota`: Menguji penyalinan kelas dan kurikulum mapel_kelas berjalan tuntas, sedangkan pengajaran dan anggota_kelas tidak ikut disalin.
  - `test_dua_tahun_tidak_bisa_aktif_bersamaan_di_database`: Menguji constraint unique index menolak penulisan tahun aktif kedua di level database.
  - Test suite non-API: **299 passed, 0 failed, 4.545 assertions (100% HIJAU)**.

---

**2026-11-11 — Penugasan Pengajaran Multi-Kelas & Multi-Mapel (Checkbox & Kombinasi Perkalian).**
- **8. Penugasan Pengajaran Banyak Kelas & Mapel Sekaligus (`admin/pengajaran/index.blade.php` & `Admin\PengajaranController`):**
  - **Tampilan Form (`admin/pengajaran/index.blade.php`):**
    - Mengganti satu select dropdown `mapel_kelas` dengan dua blok checkbox berjejer: pilihan Kelas (`id_kelas[]`) dan pilihan Mata Pelajaran (`id_mapel[]`) menggunakan kelas CSS `.check-list` dan `.check`.
    - Dilengkapi pesan instruksi dan penanganan error validasi per field (`id_kelas` dan `id_mapel`).
  - **Logika Penyimpanan (`Admin\PengajaranController@store`):**
    - Mendukung input `id_kelas[]` dan `id_mapel[]` (serta tetap kompatibel dengan single ID maupun format `mapel_kelas`).
    - Membuat kombinasi perkalian (Cartesian product) antara setiap kelas dan mata pelajaran yang dipilih.
    - Setiap pasangan diverifikasi terhadap tabel kurikulum `mapel_kelas`. Jika ada pasangan yang belum terdaftar di kurikulum kelas, pasangan tersebut otomatis dilewati.
    - Kombinasi guru-kelas-mapel yang sudah pernah ditugaskan sebelumnya otomatis **dilewati (skip)** tanpa menghasilkan error.
    - Menyimpan satu baris `pengajaran` untuk setiap pasangan yang valid dan belum ada.
    - Mengembalikan notifikasi flash success yang merinci jumlah penugasan yang dibuat dan yang dilewati: `"Penugasan pengajaran berhasil diproses: X dibuat, Y dilewati."`
  - **Test Suite (`tests/Feature/AdminPanelTest.php`):**
    - Menambahkan test `test_tiga_pasangan_dibuat_dan_duplikat_dilewati` (memverifikasi kombinasi 2 kelas x 2 mapel menghasilkan 3 baris penugasan baru, 1 duplikat dilewati tanpa error, dan flash message menampilkan "3 dibuat, 1 dilewati").
    - Menyesuaikan `test_admin_cannot_duplicate_pengajaran` agar memvalidasi behavior baru bahwa duplikat dilewati dengan aman dan jumlah baris tetap utuh.
    - Non-API test suite: **297 passed, 0 failed, 4.514 assertions (100% HIJAU)**.

---

**2026-11-11 — Integrasi Tugas Remedial ke Halaman Nilai Siswa & Pembersihan Menu Sidebar.**
- **7. Remedial di Menu Nilai Siswa (`siswa/nilai/index.blade.php`):**
  - **Service Layer (`app/Services/RemedialService.php`):** Dibuat method `untukSiswa(string $idUser)` yang memuat daftar tugas remedial siswa dengan relasi `kuisRemedial` dan `mengerjakanAsal.kuis`, terurut berdasarkan tanggal penugasan terbaru. Dilengkapi proteksi `Schema::hasTable('remedial_siswa')`.
  - **Controller (`RemedialSiswaController` & `SiswaKuisController`):**
    - `RemedialSiswaController@index`: Query dipindahkan menggunakan `RemedialService::untukSiswa()`.
    - `SiswaKuisController@daftarNilai`: Mengambil data remedial siswa via `RemedialService` dan meneruskannya ke view `siswa.nilai.index` melalui variabel `$tugas`.
  - **Partial View Bersama (`resources/views/siswa/remedial/_tabel.blade.php`):**
    - Potongan tabel tugas remedial diekstrak menjadi partial reusable `_tabel.blade.php` yang dipakai bersama oleh `resources/views/siswa/remedial/index.blade.php` dan `resources/views/siswa/nilai/index.blade.php`.
    - Memenuhi standar SoC: 100% bebas dari inline style CSS maupun inline JS.
  - **Sidebar Menu Siswa (`resources/views/layouts/app.blade.php`):**
    - Item menu sidebar "Remedial" dihapus dari daftar `$menuItems` portal siswa, menyederhanakan navigasi siswa agar fokus pada menu "Nilai Saya".
    - Route `siswa.remedial.index` dan `siswa.remedial.kerjakan` tetap dipertahankan untuk kebutuhan tautan langsung (seperti dari alert peringatan dashboard siswa).
- **Test:**
  - Ditambahkan test `test_siswa_yang_punya_remedial_melihatnya_di_halaman_nilai` pada `tests/Feature/NilaiGuruDanSiswaTest.php` (memastikan tabel remedial tampil di halaman nilai, item menu sidebar Remedial tidak ada, dan route remedial tetap berfungsi).
  - Non-API test suite: **296 passed, 0 failed, 4.496 assertions (100% HIJAU)**.

---

**2026-11-11 — Constraint Unik Pre-test/Post-test per Materi & Kunci Edit Materi.**
- **5. Constraint: 1 Pre-test & 1 Post-test per Materi:**
  - **Backend `KuisController@create`:** Sebelum menampilkan form, cek apakah materi sudah punya pre-test/post-test aktif (`is_aktif=true`). Jika sudah → redirect ke `materi.show` dengan pesan error yang jelas.
  - **Backend `KuisController@store`:** Setelah membuat kuis, sebelum sync materi, validasi setiap materi yang dipilih tidak sudah punya pre-test/post-test aktif. Jika konflik → kuis baru dihapus + `ValidationException` dengan field error `materi`.
  - Kuis lama yang dinonaktifkan (`is_aktif=false`) **tidak dihitung** sebagai konflik — guru boleh membuat pre-test/post-test baru jika yang lama dinonaktifkan.
- **6. Kunci Materi saat Edit Pre-test/Post-test:**
  - **Backend `KuisController@edit`:** Menambah flag `$materiDikunci = true` jika kategori kuis adalah `pre-test` atau `post-test`. Dikirim ke view.
  - **Backend `KuisController@update`:** Materi pre-test/post-test dikunci — `$kuis->materi()->sync()` **tidak dipanggil** sehingga relasi materi tidak berubah meskipun request membawa `materi[]`.
  - **View `guru/kuis/edit.blade.php`:** Jika `$materiDikunci`, tampilkan materi terpilih sebagai checkbox disabled + hidden input (tidak bisa diubah), dengan pesan bantuan. Jika tidak terkunci (kuis harian, ulangan, remedial), tetap tampilkan `<select multiple>` biasa.
- **Test Baru:** `tests/Feature/KuisPrePostTestUnikTest.php` — 10 test, 58 assertions.
- **Penyesuaian Test Lama:** `tests/Feature/KuisMateriMapelTest.php` — `makeKuis()` dan `payload()` diubah default kategori dari `pre-test` ke `kuis harian` agar tidak konflik dengan constraint baru. Assertion pada `test_edit_forms_include...` disesuaikan.
- **Hasil Test Suite:**
  - Non-API test suite: **295 passed, 0 failed, 4.473 assertions (100% HIJAU)**.
  - Test file baru: `tests/Feature/KuisPrePostTestUnikTest.php` (10 passed, 58 assertions).
  - ApiTest (15 failure pre-existing, bukan karena perubahan kita) tetap diabaikan.

---

**2026-11-11 — Gerbang Adaptif Pre-test → Materi → Post-test, Tombol Cepat Kuis di Materi Guru, & Pembersihan Login Email Siswa/Guru.**
- **1. Aktivasi/Nonaktivasi Mata Pelajaran di Admin (`admin/mapel/index.blade.php` & `Admin\MapelController`):**
  - Tombol aksi toggle status aktif/nonaktif langsung pada tabel daftar mata pelajaran.
  - Method `Admin\MapelController@update` mendukung perubahan status parsial tanpa menimpa nama/kode mapel.
- **2. Pembersihan Total Email Siswa & Guru:**
  - Database: file SQL `database/sql/2026_11_11_kosongkan_email_siswa_guru.sql` (`UPDATE user SET email = NULL WHERE role IN ('siswa', 'guru');`) dan sinkronisasi `database/itik.sql`.
  - Backend: Hapus blok fallback login via email pada `AuthController@login` (Siswa wajib login via NISN, Guru wajib login via NIP, hanya Admin yang login via Email).
  - Admin form: Input email dihilangkan dari form tambah/edit siswa (`admin/siswa/_form.blade.php`) dan guru (`admin/guru/_form.blade.php`), serta kolom email dibersihkan dari tabel index dan profil siswa/guru.
- **3. Tombol Cepat "Buat Pre-test" & "Buat Post-test" di Materi Guru:**
  - View `guru/materi/index.blade.php` dan `guru/materi/show.blade.php` menyediakan 2 tombol terpisah dengan query parameter `kategori=pre-test` dan `kategori=post-test`.
  - `KuisController@create` memvalidasi dan mengunci (disabled) pilihan dropdown kategori saat dibuat dari materi.
- **4. Gerbang Adaptif Pembelajaran Siswa (4 Skenario Pre-test → Materi → Post-test):**
  - **Skenario 1 (Ada Pre-test & Post-test):** Siswa wajib submit Pre-test terlebih dahulu sebelum Dokumen Materi terbuka. Post-test terkunci sampai siswa menandai materi selesai (`status = 'selesai'`).
  - **Skenario 2 (Hanya Pre-test):** Siswa wajib submit Pre-test sebelum membaca Materi. Setelah Pre-test selesai, materi bebas diakses (tanpa post-test).
  - **Skenario 3 (Hanya Post-test):** Siswa langsung dapat membaca Dokumen Materi (tanpa pre-test). Post-test terkunci sampai materi ditandai selesai.
  - **Skenario 4 (Tanpa Pre/Post-test):** Siswa bebas membaca dan menyelesaikan materi langsung tanpa hambatan.
  - **Proteksi Backend:**
    - `ProgressController@show`: Menolak pembukaan materi dan redirect dengan pesan error jika Pre-test aktif belum selesai.
    - `ProgressController@selesai`: Menolak tanda selesai jika Pre-test belum selesai.
    - `SiswaKuisController@mulai` & `kerjakan`: Menolak pengerjaan Post-test jika materi terkait belum berstatus 'selesai'.
    - `SiswaKuisController@hitungStatusDanProgresiKuis`: Otomatis menandai Post-test sebagai `🔒 Terkunci` dengan alasan penguncian informatif di daftar kuis siswa.
  - **Tampilan Siswa:**
    - `resources/views/siswa/materi/index.blade.php`: Status badge dan tombol dinamis ("Mulai Pre-test", "Baca Materi", "Kerjakan Post-test").
    - `resources/views/siswa/materi/show.blade.php`: Notifikasi & tombol aksi cepat "→ Lanjutkan ke Post-test" setelah menandai materi selesai.
    - `resources/views/siswa/kuis/hasil.blade.php`: Tombol aksi cepat "Buka Materi Pembelajaran →" setelah siswa menyelesaikan Pre-test.
- **Hasil Test Suite:**
  - Non-API test suite: **283 passed, 0 failed, 4.412 assertions (100% HIJAU)**.
  - Test baru terverifikasi: `tests/Feature/SiswaMateriPrePostTest.php` (8 passed, 86 assertions).

---

**2026-11-10 — Mode Layar Penuh & Penghitung Pelanggaran CBT (Auto-Submit Sanksi).**
- **Database & Model:**
  - File SQL dibuat: `database/sql/2026_11_10_sesi_pelanggaran.sql` (`ALTER TABLE sesi_kuis ADD COLUMN jumlah_pelanggaran TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER waktu_selesai;`).
  - Kolom diterapkan ke database MySQL `itik` dan dump skema `database/itik.sql` disinkronkan.
  - Model `SesiKuis` diperbarui mencakup `jumlah_pelanggaran` pada `$fillable` dan `$casts`.
- **Backend (`SiswaKuisController` & `routes/web.php`):**
  - Ditambahkan konstanta `BATAS_PELANGGARAN = 3` pada `SiswaKuisController`.
  - Route baru: `POST /siswa/kuis/{kuis}/pelanggaran` (`siswa.kuis.pelanggaran`).
  - Method `catatPelanggaran(Request $request, Kuis $kuis)`:
    - Mengambil sesi aktif siswa saat ini (menolak 404 jika bukan milik siswa yang login).
    - Menambah hitungan atomik di DB: `UPDATE sesi_kuis SET jumlah_pelanggaran = jumlah_pelanggaran + 1 WHERE id_sesi = ? AND waktu_selesai IS NULL`.
    - Jika hitungan mencapai batas (>= 3), otomatis memanggil `$this->submission->submit($sesi)` berbasis draft jawaban dan mengembalikan `autoSubmitted: true`.
- **Frontend & Tampilan (`kerjakan.blade.php`, `kerjakan.css`, `kerjakan.js`):**
  - Gerbang modal awal "Mulai Ujian & Kunci Layar Penuh" sebelum layout soal ditampilkan; memanggil `requestFullscreen()` dan `navigator.keyboard.lock(['Escape'])`.
  - Deteksi tombol sistem (`Alt`, `Meta`, `OS`) pada keydown dicegah (`preventDefault()`) dan memunculkan peringatan dialog eksplisit bahwa siswa terdeteksi mencoba keluar dari halaman ujian, disertai dokumentasi bahwa penanganan level OS hanya bisa dicegah sebagian oleh browser web.
  - Deteksi pelanggaran dari 3 kejadian: `fullscreenchange` (keluar fullscreen), `visibilitychange` (tab disembunyikan/minimize), dan `window.blur` (kehilangan fokus jendela).
  - Mekanisme debounce 1500 ms untuk mencegah trigger ganda saat Alt+Tab atau perpindahan window.
  - Mengirim POST ke endpoint yang dibaca dari form data attribute (`data-pelanggaran-url`, `data-hasil-url`, `data-batas`).
  - Overlay merah peringatan: *"⚠️ PERINGATAN (jumlah/3): Anda terdeteksi keluar dari layar penuh atau berpindah jendela. Segera kembali ke mode layar penuh."* beserta tombol pengaktifan kembali layar penuh.
  - Jika `autoSubmitted`, langsung redirect ke halaman hasil kuis.
  - Patuh pada aturan Separation of Concerns: zero inline CSS dan zero inline JS di file Blade.
- **Hasil Test Suite:**
  - Non-API test suite: **269 passed, 0 failed, 4.214 assertions (100% HIJAU)** (`artisan test --filter "/^(?!.*ApiTest).*$/"`).
  - Test baru terverifikasi: `tests/Feature/PelanggaranKuisTest.php` (4 passed, 29 assertions).

---

- Sebanyak 26 blok CSS dan 271 atribut style sudah dipisahkan menjadi 27 file di `public/css/`, termasuk 153 utility rules. JavaScript dipisahkan menjadi 12 file di `public/js/`; nilai dinamis Blade dikirim melalui data atribut. Tidak ada CSS internal/inline, isi script inline, atau atribut event handler inline dalam Blade.
- View `kuis`, `materi`, `soal`, dan `remedial` dipindahkan ke `resources/views/guru/`, profil bersama ke `resources/views/shared/profil.blade.php`. Controller dan route hanya mengganti nama view; aturan bisnis, URL route, validasi, dan DB asli tidak diubah. Script kuis guru bersama bernama `public/js/guru/kuis/form.js`, bukan `create.js`.
- PHPUnit non-API: **222 passed, 3740 assertions** (`php artisan test --filter '/^(?!.*ApiTest).*$/'`); pemeriksaan sintaks 12 JS dan `git diff --check` lulus. Full suite memiliki 15 kegagalan API bawaan (14 error tabel `user` tidak tersedia di setup SQLite API, 1 expected 401 tetapi 404), juga direproduksi pada baseline `47e96c2`; tidak diperbaiki dalam refaktor ini.
- Perbandingan browser 70 halaman/varian dan 17.910 element-state hanya menemukan 5 selisih opacity sangat kecil akibat animasi welcome, tanpa perubahan geometri. Pengujian tambahan menghasilkan 16 pemeriksaan browser lulus: pencarian/jumlah anggota, reset filter admin, menu seluler, pilihan kunci soal, filter materi dan nilai, modal validasi password, renderer/navigasi PDF dengan fixture lokal, PDF tidak ditemukan, autosave, pemulihan waktu mulai/jawaban setelah reload, auto-submit tanpa dialog, penutupan sesi server, dan tidak ada JavaScript page errors. Screenshot admin/guru/siswa/PDF sudah diperiksa.
- **Batasan PDF:** GET dokumen asli melalui browser uji mendapat HTTP 204 pada baseline dan refaktor, sedangkan curl mendapat 200 dan dokumen 264.073 byte. Renderer PDF dan navigasi halaman diuji dengan intersepsi request di scratch yang mengembalikan byte dokumen asli (SHA-256 identik), tanpa mengubah kode aplikasi. End-to-end unduhan PDF melalui browser normal masih belum dapat diverifikasi di lingkungan ini; masalah tersebut bukan diperkenalkan refaktor. Baseline memerlukan junction scratch `storage/app/public/materi` ke dokumen lokal agar `pdfUrl()` dapat memuat viewer; junction sudah dibuat hanya di worktree uji.
- Browser menggunakan SQLite terisolasi dari backup lokal di `C:/Users/DesuDesu/.qoder/tmp/soc-diff`, bukan DB `itik`; tidak menjalankan migration atau DDL. Server uji baseline port 8101/PID 13496 dan refaktor port 8102/PID 20208 **sudah dihentikan**; kedua port dipastikan tidak lagi memiliki listener. Browser diagnostik juga sudah ditutup. Jangan menghapus junction scratch secara rekursif yang dapat menyentuh vendor/storage aplikasi.

Semua status/branch/test lama di bawah adalah catatan historis, bukan kondisi saat ini.

Fase 0–10 **selesai** dan teruji secara penuh.
Test suite: **217 passed, 3537 assertions** (100% hijau).
**Working tree siap di-commit manual oleh pemilik proyek** di branch `sadan`.

Commit terakhir:
```
82f60b3 Fase 9: integrasi remedial ke menu nilai guru, filter pills whatsapp-like, dan hirarki nilai siswa ala foto 2
```

| Fase | Isi | Status |
|---|---|---|
| 0 | Backup DB (`storage/backup/itik-backup-20261029-023228.sql`) + branch `feat/admin-backend` | ✅ |
| 1 | DDL: tabel master + kolom baru (lihat "Skema DB") — sudah diterapkan ke DB live `itik` | ✅ |
| 2 | Model Eloquent baru (`TahunAjaran`, `Mapel`, `Kelas`, `AnggotaKelas`) + update `User`, `Kuis`, `Materi` | ✅ |
| 3 | Panel admin (dashboard, profil, tahun ajaran, mapel, kelas + anggota, siswa, guru, rekap nilai) | ✅ |
| 4 | Guru wajib memilih mapel saat membuat kuis/materi, tahun ajaran kuis otomatis, `pass-test` → `pre-test` | ✅ |
| 4b | Aturan waktu kuis (timer server-side, auto-submit, durasi di halaman hasil) | ✅ |
| 5 | Login NISN/NIP (siswa/guru), halaman login terpisah Admin (/admin/login via email), middleware paksa ganti password, modal pop-up profil | ✅ |
| 6 | Navigasi kuis berbasis Mapel (guru+siswa), progresi level KKM, penjadwalan+unpublish, bobot otomatis, redesign soal/show, filter materi per mapel, navigasi kembali per mapel, klasifikasi bab materi (3 tingkat kesulitan), pembersihan DB dummy & seeding kuis terstruktur (3, 5, 7 soal) | ✅ |
| 7 | Remedial hanya untuk kategori Ulangan, CBT Mode penguncian navigasi, evaluasi soal benar/salah (siswa & guru), validasi otomatis, retake langsung tanpa validasi manual guru | ✅ |
| 8 | Seeding Kuis Ulangan (K005) & Remedial (K006) masing-masing 5 butir soal, desain UI panel Remedial Guru (index, create, siswa, show) | ✅ |
| 9 | Pemberian remedial terintegrasi langsung di menu Nilai Guru (`/guru/nilai`), filter WhatsApp-like pills (kategori, percobaan, kelulusan, tanggal), dan redesign menu "Nilai Saya" Siswa 3-level ala Foto 2 | ✅ |
| 10 | Materi 3-level (Mapel → Bab → Materi), viewer PDF.js interaktif dengan navigasi halaman, klik opsi langsung jadi kunci hijau pada butir soal create/edit, opsi E warna seragam, kotak bobot dihapus, kuis terbit & draft otomatis real-time, tombol "← Kembali" berkotak seragam di seluruh halaman 3 role, nilai guru per-kelas dengan 4 indikator, dan penyembunyian ID internal sistem | ✅ |
| 11 | Audit & Pembersihan Kode Bertahap (Dead code, unused logic, scope standardization, maintainability) | 🔄 Sedang Berjalan |

**Merge `origin/main` → `sadan` (2026-11-02, commit `899183e`, lokal, BELUM di-push):** atas keputusan pemilik, isi kode dipertahankan 100% dari `sadan` (tree identik dengan `024e90a`; 218 test lulus). Fitur `main` (API Sanctum, soal essay/gambar, ujian adaptif, jadwal publish, migration 2026_10_09…2026_10_28) **sengaja tidak ikut** karena DB live & vendor belum mendukung; `main` juga mengganti tabel `Mengerjakan` → `mengerjakans` dan timezone UTC → Asia/Jakarta (merusak data waktu). Kalau fitur itu mau dipakai, ambil per file dari commit `ce2e4ce`, jangan merge ulang. Setelah `sadan` di-push, PR `sadan` → `main` tidak akan konflik.

---

## 🔍 Audit Bertahap (Dead Code, Unused Logic, & Maintainability)

Audit kode dilakukan secara terisolasi per bagian dan diverifikasi langsung dengan unit test suite (218 tests).

### Checklist Modul / Direktori:
- [x] **Bagian 1: Autentikasi & Password**
  - Files: `app/Http/Controllers/AuthController.php`, `app/Http/Controllers/PasswordController.php`, `app/Http/Middleware/EnsurePasswordChanged.php`, `app/Http/Middleware/EnsureAccountActive.php`.
  - **Temuan & Pembersihan:**
    - Di `PasswordController.php`: Redirection guru diarahkan ke `dashboard` (standar) bukan route usang `materi.index`.
    - Di `AuthController.php`: Redundant manual role branching di method `login()` disederhanakan dengan memanggil `$this->redirectSesuaiRole($user)`.
  - **Status:** Selesai & Lolos Pengujian (218 passed).

- [x] **Bagian 2: Siswa Area**
  - Files: `app/Http/Controllers/SiswaDashboardController.php`, `app/Http/Controllers/ProgressController.php`, `app/Http/Controllers/SiswaKuisController.php`, `app/Http/Controllers/RemedialSiswaController.php`.
  - **Temuan & Pembersihan:**
    - Di `SiswaDashboardController.php`: Ditambahkan pemanggilan `Kuis::sinkronisasiStatus()` pada awal `index()` agar kuis dengan jadwal terbit otomatis langsung tersinkronisasi saat siswa membuka dashboard.
    - Di `SiswaKuisController.php`: Menghapus unused import `use Illuminate\Support\Facades\DB;`. Standarisasi query `Mapel::aktif()`. Refaktorisasi ekstraksi method privat `hitungStatusDanProgresiKuis()` untuk menyelesaikan peringatan linter tipe inferensi kompleks dan typehint incompatible.
    - Di `ProgressController.php`: Relasi progress dan query mapel aman dan efisien.
    - Di `RemedialSiswaController.php`: Logic ramping dan terhubung rapi dengan alur pengerjaan kuis siswa.
  - **Status:** Selesai & Lolos Pengujian (218 passed).

- [x] **Bagian 3: Guru - Materi, Kuis, & Bank Soal**
  - Files: `app/Http/Controllers/DashboardController.php`, `app/Http/Controllers/MateriController.php`, `app/Http/Controllers/KuisController.php`, `app/Http/Controllers/SoalController.php`.
  - **Temuan & Pembersihan:**
    - Di `DashboardController.php`: Menambahkan sinkronisasi status real-time `Kuis::sinkronisasiStatus()` pada dashboard guru agar penghitungan dan daftar kuis aktif selalu up-to-date saat jadwal auto-publish tiba.
    - Di `KuisController.php`: Menstandarisasi query `$mapelAktif` menggunakan scope `Mapel::aktif()` alih-alih klausa manual `where('is_aktif', 1)`.
    - Di `SoalController.php`: Helper perataan bobot nilai soal otomatis (`seimbangkanBobot`) dan verifikasi kepemilikan soal kuis (`checkSoalBelongsToKuis`) sudah solid dan terisolasi dengan rapi.
    - Di `MateriController.php`: Validasi duplikasi bab/kelas/judul materi, upload PDF, dan penanganan file storage sudah optimal.
  - **Status:** Selesai & Lolos Pengujian (218 passed).

- [x] **Bagian 4: Guru - Nilai & Remedial**
  - Files: `app/Http/Controllers/ValidasiNilaiController.php`, `app/Http/Controllers/RemedialController.php`.
  - **Temuan & Pembersihan:**
    - Di `ValidasiNilaiController.php`: Menghapus unused import `use App\Models\Soal;`. Mengganti pembuatan ID kuis manual berbasis regex dengan trait standar `MenghasilkanId` (`idBerikutnya`).
    - Di `RemedialController.php`: Menghapus method privat duplikat `generateKuisId()` (dead code) dan menstandarisasi pembuatan ID kuis remedial menggunakan trait `MenghasilkanId` (`idBerikutnya`).
    - Proteksi keamanan: Validasi kelayakan remedial (hanya untuk kategori Ulangan dan nilai di bawah KKM) serta transaksi database berjalan sempurna.
  - **Status:** Selesai & Lolos Pengujian (218 passed).

- [x] **Bagian 5: Panel Admin**
  - Directory: `app/Http/Controllers/Admin/*` (`DashboardController`, `TahunAjaranController`, `MapelController`, `KelasController`, `SiswaController`, `GuruController`, `RekapNilaiController`).
  - **Temuan & Pembersihan:**
    - Seluruh controller panel Admin (`app/Http/Controllers/Admin/`) diaudit baris per baris.
    - Struktur kode terbukti bersih dan patuh pada prinsip arsitektur:
      - Penanganan soft-toggle status `aktif`/`nonaktif` untuk Siswa dan Guru (menjaga integritas relasi foreign key CASCADE agar riwayat pengerjaan nilai kuis tidak terhapus).
      - Pembuatan ID seragam via `MenghasilkanId` (`TA`, `MP`, `KLS`, `U`).
      - Pembungkusan transaksi database `DB::transaction()` dengan `lockForUpdate()` pada pergantian Tahun Ajaran aktif dan penugasan anggota kelas.
      - Logika agregasi rekap nilai per-kelas menggunakan perolehan nilai terbaik siswa tanpa duplikasi multi-attempt.
      - Tidak ditemukan dead code, import tak terpakai, atau fungsi usang.
  - **Status:** Selesai & Lolos Pengujian (218 passed).

- [x] **Bagian 6: Models, Services, & Console Commands**
  - Directory: `app/Models/*`, `app/Services/*`, `app/Console/Commands/*`.
  - **Temuan & Pembersihan:**
    - Di `Remedial.php`: Menghapus 2 `use` redundan (`use App\Models\Kuis;` dan `use App\Models\RemedialSiswa;`).
    - Di `Mapel.php`, `Kelas.php`, `TahunAjaran.php`: Standarisasi `scopeAktif()` dan `aktif()` dari `where('is_aktif', 1)` menjadi `where('is_aktif', true)` agar konsisten dengan cast boolean.
    - `KuisSubmissionService.php`: Logic solid, terbungkus `DB::transaction()` + `lockForUpdate()`. Tidak ada dead code.
    - `KuisAutoPublish.php`: Terdaftar & terjadwal benar via `Schedule::command('kuis:auto-publish')->everyMinute()->withoutOverlapping()`.
    - `MenghasilkanId.php`: Trait terpasang benar di semua 9 controller yang membutuhkan ID generation.
    - Semua 13 Model diaudit: relasi Eloquent, fillable, casts, scopes benar — tidak ada dead code.
  - **Status:** Selesai & Lolos Pengujian (218 passed).

### 📊 Ringkasan & Kesimpulan Hasil Audit (Bagian 1 – 5)

Berikut adalah rangkuman hasil pembersihan, standarisasi, dan optimasi yang telah berhasil dilakukan pada Bagian 1 sampai dengan Bagian 6:

1. **Pembersihan Dead Code & Unused Logic:**
   - Menghapus method privat redundan `generateKuisId()` (25 baris) di `RemedialController`.
   - Menghapus import tidak terpakai: `use Illuminate\Support\Facades\DB;` di `SiswaKuisController` dan `use App\Models\Soal;` di `ValidasiNilaiController`.
   - Memangkas percabangan logika login manual di `AuthController` dan mengalihkan ke fungsi terpusat `$this->redirectSesuaiRole($user)`.

2. **Standarisasi Pola Kode & Architecture Consistency:**
   - **ID Generation:** Mengganti regex generator ID di `ValidasiNilaiController` dan `RemedialController` dengan Trait resmi `MenghasilkanId` (`$this->idBerikutnya()`), memastikan penomoran ID konsisten di seluruh entitas (`TA`, `MP`, `KLS`, `U`, `M`, `K`).
   - **Query Scopes:** Menstandarisasi query status mapel dari `Mapel::where('is_aktif', 1)` menjadi scope seragam `Mapel::aktif()`.
   - **Redirection:** Memperbaiki redirect usang guru pasca ganti password dari `materi.index` ke route standar `dashboard`.

3. **Sinkronisasi Status Real-time:**
   - Memastikan `Kuis::sinkronisasiStatus()` terpanggil di semua titik temu pengguna (Dashboard Guru, Beranda Siswa, Daftar Kuis Guru, dan Daftar Kuis Siswa), sehingga jadwal auto-publish dan expired kuis terperbarui secara instan.

4. **Keamanan & Integritas Data:**
   - Memastikan transaksi kritis (pergantian tahun ajaran aktif, pembagian anggota kelas, penugasan remedial) terlindungi dengan `DB::transaction()` dan `lockForUpdate()`.
   - Mempertahankan integritas `CASCADE FK` pada akun Siswa & Guru melalui mekanisme soft-toggle (`aktif`/`nonaktif`) alih-alih penghapusan fisik.

5. **Verifikasi Test Suite:**
   - Seluruh **218 tests berhasil lulus 100% (passed)** dengan **3.554 assertions** valid tanpa ada regresi sedikit pun.

---

## ✅ Fase 10 — UI/UX Refinement, Viewer PDF.js, Auto-Publish Real-time, & Nilai Guru Per-Kelas (SELESAI 2026-11-01)

Fitur yang diimplementasikan dan diuji secara menyeluruh (217 passed, 3537 assertions):

1. **Struktur Materi Berbasis Mata Pelajaran (Mapel → Materi Langsung ala Foto 2):**
   - **Role Guru (`/materi`):**
     - **Level 1:** Memilih kartu Mata Pelajaran aktif (dengan ringkasan total materi).
     - **Level 2 (`?mapel={id_mapel}`):** Langsung menampilkan daftar materi pembelajaran untuk mata pelajaran tersebut dalam format kartu horizontal ala Foto 2 (badge rounded hitam `01`, `02`, dst., judul materi, bab & kelas, serta tombol aksi Lihat, Edit, dan Hapus).
   - **Role Siswa (`/siswa/materi`):**
     - **Level 1:** Siswa memilih kartu Mata Pelajaran aktif.
     - **Level 2 (`?mapel={id_mapel}`):** Langsung menyajikan daftar materi pembelajaran ala Foto 2 (badge `01`, `02`, status progress belajar `Belum Mulai` / `Sedang Belajar` / `✓ Selesai`, dan tombol buka materi).

2. **Viewer PDF.js Interaktif dengan Navigasi Halaman (`materi/show` & `siswa/materi/show`):**
   - Menggantikan `<iframe>` browser biasa dengan renderer PDF.js HTML5 canvas yang cepat dan responsif.
   - Dilengkapi toolbar navigasi: tombol `← Sebelumnya`, counter `Halaman X dari Y`, dan tombol `Selanjutnya →`.

3. **Input Soal Klik Opsi Langsung (Create & Edit Soal):**
   - Dropdown `select[name="jawaban"]` telah dihapus.
   - Guru cukup mengklik baris pilihan A/B/C/D/E secara visual; baris opsi yang diklik otomatis berubah warna hijau cerah (`#10b981`) dan ditandai sebagai kunci jawaban via hidden input.
   - Opsi E memiliki warna yang sama dengan opsi A/B/C/D (tidak lagi abu-abu/berbeda sendiri).
   - Kotak informasi hijau "Bobot Nilai Otomatis" telah dihapus dari form tambah & edit soal.

4. **Kuis Terbit & Draft Otomatis Berbasis Waktu Nyata (Real-time Sync):**
   - Menambahkan method `Kuis::sinkronisasiStatus()` yang otomatis berjalan setiap kali ada request ke kuis (sehingga langsung terbit/draft tanpa bergantung pada scheduler background di lingkungan lokal Windows/Laragon).
   - Jadwal terbit dan selesai otomatis dibuka untuk seluruh kategori kuis di form tambah dan edit kuis.

5. **Tombol "← Kembali" Berseragam dengan Kotak Jelas:**
   - Seluruh teks tombol "← Kembali ke ..." diubah menjadi kata ringkas "← Kembali".
   - Dibungkus dalam tombol berkotak standar `btn btn-secondary` di seluruh halaman siswa, guru, dan admin.

6. **Daftar Nilai Guru Berbasis Per-Kelas (`/guru/nilai`):**
   - Menampilkan grid kartu kelas pembelajaran di bagian atas.
   - Tiap kartu kelas menampilkan 4 indikator lengkap: Total Siswa, Total Materi, Kuis Aktif, dan Rata-rata Nilai.
   - Guru dapat mengklik kelas untuk menyaring tabel nilai khusus kelas tersebut, dengan filter WhatsApp-like pills tetap aktif dan tersinkronisasi.

7. **Penyembunyian ID Internal Sistem:**
   - Menghapus tampilan `id_user`, `id_materi`, `id_soal`, dan `id_mapel` dari antarmuka guru dan siswa (halaman profil, detail materi, edit/detail soal, kuis).

---

## ✅ Fase 9 — Integrasi Remedial di Menu Nilai, Filter WhatsApp-Like, & Nilai Siswa Ala Foto 2 (SELESAI 2026-10-31)

Fitur yang diimplementasikan dan diuji:

1. **Pemberian Tugas Remedial Langsung dari Menu Nilai Guru (`/guru/nilai`):**
   - Di halaman Nilai Guru, tombol **"⚡ Berikan Remedial"** otomatis tampil **hanya** pada baris pengerjaan kuis berkategori **Ulangan** yang siswanya tidak mencapai KKM (`nilai < kkm`).
   - Kuis kategori lain (Kuis Harian, Pre-test, Post-test) atau yang sudah lulus tidak menampilkan tombol ini.
   - Jika remedial sudah pernah diberikan ke siswa terkait, tampil badge informatif **"✓ Remedial Ditugaskan"**.
   - Ketika guru klik tombol "⚡ Berikan Remedial", sistem mengeksekusi request `POST guru/nilai/{mengerjakan}/remedial` dan langsung mencatat penugasan di `remedial_siswa` (serta otomatis menyiapkan kuis remedial jika belum ada).

2. **Klasifikasi & Filter Pills Ala WhatsApp di Menu Nilai Guru:**
   - Terinspirasi dari filter chips WhatsApp (Unread, Groups, Favourites), disediakan 4 tombol pill interaktif di atas tabel nilai:
     - **🏷️ Kategori:** Dropdown pilihan kategori (Semua, Ulangan, Kuis Harian, Pre-test, Post-test, Remedial).
     - **🔄 Percobaan:** Tombol berubah menjadi input form inline angka (`type="number" min="1"`) yang hanya menerima angka untuk mencari percobaan ke berapa.
     - **🎓 Kelulusan:** Dropdown pilihan status kelulusan (Semua, Lulus &ge; KKM, Tidak Lulus &lt; KKM).
     - **📅 Tanggal:** Date picker interaktif untuk menyaring riwayat berdasarkan tanggal pengerjaan.
     - **✕ Reset Filter:** Tombol reset cepat yang otomatis muncul ketika ada filter yang sedang aktif.
   - Filter bekerja seketika (real-time instant) di sisi browser tanpa jeda, serta tersinkronisasi dengan query parameters URL.

3. **Menu "Nilai Saya" Siswa Berhirarki 3 Level (Ala Menu Kuis & Foto 2):**
   - **Level 1 (`/siswa/nilai`):** Siswa melihat kartu Mata Pelajaran aktif beserta ringkasan jumlah kuis selesai dan rata-rata nilai, dengan tombol "Lihat Nilai Bab Materi →".
   - **Level 2 (`/siswa/nilai?mapel={id_mapel}`):** Menampilkan daftar Bab Materi dengan format kartu horizontal persis seperti Foto 2 referensi:
     - Badge nomor hitam solid rounded di sisi kiri: `01`, `02`, `03`, dst.
     - Judul Bab tebal (contoh: *Bab 1 — Dasar Jaringan Komputer dan Topologi Jaringan*).
     - Subtitle ringkasan di bawah judul: *Bab 1 • 3 Kuis Tersedia • Nilai Tertinggi: 85.7 (3/3 Kuis Selesai)*.
     - Panah kanan halus `→` di sisi kanan kartu.
     - Dilengkapi bagian khusus untuk riwayat kuis Ulangan Umum di tingkat mata pelajaran.
   - **Level 3 (`/siswa/nilai?mapel={id_mapel}&materi={id_materi}`):** Menampilkan rincian tabel hasil kuis pada bab terpilih (No, Nama Kuis, Kategori, Tingkat Kesulitan, Percobaan ke, Nilai, KKM, Kelulusan, Tanggal, dan tombol "📋 Evaluasi Soal").

---

## ✅ Fase 8 — Kuis Ulangan & Remedial serta Panel Guru (SELESAI 2026-10-31)

Fitur dan data yang telah ditambahkan:
1. **Kuis Ulangan (`K005`)**:
   - Judul: *Ulangan Harian 1 - Dasar Jaringan Komputer*
   - Kategori: `ulangan`, Tingkat: `sedang`, Alokasi: 20 menit, KKM: 75.00
   - Mapel: `MP001` (Teknologi Informasi dan Komunikasi), Materi: `M001` (Bab 1)
   - Status: `terbit`, Aktif: Ya
   - 5 Soal Pilihan Ganda (`S019` - `S023`), masing-masing berbobot 20.00% (Total 100%).
2. **Kuis Remedial (`K006`)**:
   - Judul: *Remedial Ulangan Harian 1 - Dasar Jaringan Komputer*
   - Kategori: `remedial`, Tingkat: `sedang`, Alokasi: 20 menit, KKM: 75.00
   - Kuis Asal: `K005` (terdaftar di tabel `remedial`)
   - Status: `terbit`, Aktif: Ya
   - 5 Soal Pilihan Ganda (`S024` - `S028`), masing-masing berbobot 20.00% (Total 100%).
3. **Panel Remedial Guru Terpadu (`/remedial`)**:
   - `remedial/create.blade.php`: Form pembuatan kuis remedial baru (hanya kuis ulangan yang dapat dipilih sebagai asal).
   - `remedial/index.blade.php`: Daftar kuis remedial dengan desain modern ITIK, status publikasi, jumlah soal, dan tombol navigasi.
   - `remedial/siswa.blade.php`: Halaman penugasan siswa dengan tabel perbandingan nilai ulangan vs KKM dan checklist penugasan.
   - `remedial/show.blade.php`: Detail hasil evaluasi nilai remedial siswa (komparasi nilai asal vs remedial).

---

## ✅ Fase 6 — Penyempurnaan Kelola Kuis & Klasifikasi Bab Materi (SELESAI 2026-10-31)

Fitur yang disempurnakan dan telah diuji (seluruh 211 test lolos):

1. **Alur Bertahap Halaman Kuis Siswa (Mapel → Bab Materi → 3 Level Kuis):**
   - **Level 1 (`/siswa/kuis`):** Siswa disajikan pilihan kartu Mata Pelajaran aktif terlebih dahulu.
   - **Level 2 (`/siswa/kuis?mapel={id_mapel}`):** Setelah memilih mata pelajaran, siswa disajikan daftar kartu **Bab Materi** (misal: *Bab 1 - Dasar Jaringan Komputer* dan *Bab 2 - Pengkabelan Jaringan*). Setiap kartu bab menampilkan ringkasan kuis yang tersedia (Mudah, Sedang, Sulit) dan tombol "Buka Bab Ini →".
   - **Level 3 (`/siswa/kuis?mapel={id_mapel}&materi={id_materi}`):** Siswa masuk ke bab spesifik yang dipilih. Menampilkan breadcrumb navigasi mapel/bab, header deskripsi materi, dan grid 3 kartu kuis (**Mudah**, **Sedang**, **Sulit**).
   - Progresi KKM berjalan otomatis: Kuis Sedang dan Sulit berstatus `🔒 Terkunci` sampai siswa lulus kuis level sebelumnya dengan nilai ≥ KKM.

2. **Pembersihan Data DB Live & Seeding Contoh Kuis Terstruktur (3, 5, 7 Soal):**
   - Data materi & kuis dummy lama yang tidak terpakai di DB live `itik` telah dibersihkan secara aman (backup tersimpan di `storage/backup/itik-backup-fase6-sebelum-bersihkan.sql`).
   - Disiapkan materi rapi untuk SMK TKJ pada mapel `MP001` (Teknologi Informasi dan Komunikasi):
     - `M001`: Bab 1 - *Dasar Jaringan Komputer dan Topologi Jaringan*
     - `M002`: Bab 2 - *Pengkabelan Jaringan dan Pengalamatan IP Address*
   - Dibuat kuis berstatus **Terbit** untuk Bab 1 (`M001`):
     - **Kuis Tingkat Mudah (`K001`)**: 3 Soal Pilihan Ganda, KKM 75, Alokasi 15 menit, bobot otomatis seimbang 33.34%, 33.33%, 33.33% (Total 100%).
     - **Kuis Tingkat Sedang (`K002`)**: 5 Soal Pilihan Ganda, KKM 75, Alokasi 20 menit, bobot otomatis seimbang 20.00% per soal (Total 100%).
     - **Kuis Tingkat Sulit (`K003`)**: 7 Soal Pilihan Ganda, KKM 75, Alokasi 25 menit, bobot otomatis seimbang 14.32% + 6 x 14.28% (Total 100%).
   - Dibuat kuis pembuka untuk Bab 2 (`M002`):
     - **Kuis Tingkat Mudah (`K004`)**: 3 Soal Pilihan Ganda, KKM 75, Alokasi 15 menit (Total 100%).

3. **Pilihan Materi Disesuaikan Otomatis dengan Mata Pelajaran:**
   - Pada form tambah kuis (`kuis/create.blade.php`) dan edit kuis (`kuis/edit.blade.php`), option materi dilengkapi atribut `data-mapel`.
   - JavaScript secara real-time menyaring materi sehingga guru hanya dapat melihat dan memilih materi milik mata pelajaran yang dipilih.
   - Pilihan materi dari mapel lain otomatis dinonaktifkan (`disabled`) dan disembunyikan.
   - Ditambahkan indikator peringatan jika mata pelajaran yang dipilih belum memiliki materi pembelajaran.
   - Dropdown `#id_mapel` otomatis terpilih (`pre-selected`) jika form dibuka dari tombol "+ Tambah Kuis" di halaman kuis mapel tertentu (`?mapel=...`).

4. **Redirect & Notifikasi Kembali ke Mapel Masing-Masing:**
   - Saat kuis berhasil dibuat (`KuisController@store`): diarahkan kembali ke `route('kuis.index', ['mapel' => $kuis->id_mapel])` disertai pesan sukses.
   - Saat kuis diperbarui (`KuisController@update`): diarahkan kembali ke `route('kuis.index', ['mapel' => $kuis->id_mapel])` disertai pesan sukses.
   - Saat kuis diarsipkan (`KuisController@destroy`): diarahkan kembali ke `route('kuis.index', ['mapel' => $idMapel])` disertai pesan sukses.
   - Guru langsung mendarat di mapel terkait dan langsung melihat kuis yang baru dikerjakannya.

5. **Tombol "Kembali" dari "Detail Kuis" Mengarah ke Mapel Terkait:**
   - Tombol "← Kembali ke Kuis" di `kuis/show.blade.php` sekarang mengarah ke `route('kuis.index', ['mapel' => $kuis->id_mapel])`.
   - Tombol "← Kembali ke Kuis" di `kuis/create.blade.php` mengarah ke mapel yang sedang dikelola (`$mapelTerpilih`).
   - Tombol "Batal" di `kuis/edit.blade.php` mengarah ke `route('kuis.index', ['mapel' => $kuis->id_mapel])`.
   - Breadcrumb di `soal/show.blade.php` mengarah ke `route('kuis.index', ['mapel' => $kuis->id_mapel])`.
   - Halaman detail kuis (`kuis/show.blade.php`) kini menampilkan informasi Mata Pelajaran dan Tingkat Kesulitan di kartu info.

6. **Proteksi & Notifikasi Kuis Terbit Tidak Dapat Diedit:**
   - Pada halaman daftar kuis (`kuis/index.blade.php`), saat seseorang memencet tombol "Edit" pada baris kuis yang berstatus `terbit`, muncul notifikasi pop-up dialog: *"Kuis tersebut tidak dapat diedit karena berstatus terbit. Ubah ke status draft terlebih dahulu."*
   - Pada backend (`KuisController@edit` & `@update`), jika kuis berstatus `terbit`, request otomatis diblokir dan diarahkan kembali dengan notifikasi flash error dan `assertSessionHasErrors('kuis')`.
   - Tombol "Edit" di header halaman detail kuis (`kuis/show.blade.php`) juga dipasangi proteksi dan peringatan yang sama.

7. **Ketentuan Remedial Khusus Kategori Ulangan (Poin 1):**
   - Remedial sekarang secara ketat **hanya berlaku untuk kuis ber-kategori Ulangan** (`RemedialController@create`, `@store`, `@siswa`).
   - Kuis dengan kategori lain (`pre-test`, `pass-test`, `kuis harian`) tidak masuk dalam daftar asal remedial, melainkan menggunakan mekanisme pengerjaan ulang (retake) langsung oleh siswa.

8. **Penguncian Navigasi Ujian / CBT Mode Siswa (Poin 2):**
   - Halaman pengerjaan kuis (`resources/views/siswa/kuis/kerjakan.blade.php`) kini sepenuhnya menutup semua tautan navigasi keluar. Sidebar diubah menjadi panel "Moda Ujian CBT Terkunci".
   - Dilengkapi proteksi JavaScript:
     - `beforeunload`: mencegah reload/penutupan tab browser tanpa sengaja.
     - `popstate` / pushState lock: memblokir navigasi tombol Back/Forward browser.
     - Blokir keyboard shortcut reload (`F5`, `Ctrl+R`, `Alt+Left`).
     - `visibilitychange` listener: mendeteksi dan memperingatkan siswa jika mencoba berpindah tab browser.
     - Form submit terproteksi flag `formSubmitted` agar proses pengumpulan resmi berjalan mulus.

9. **Evaluasi Butir Soal Benar & Salah untuk Siswa & Guru (Poin 3):**
   - Draft jawaban siswa tidak dihapus saat submit, melainkan disimpan hingga siswa memutuskan untuk mengulang pengerjaan kuis baru (`KuisSubmissionService@submit`, `SiswaKuisController@mulai`).
   - **Pada Halaman Siswa (`siswa/kuis/hasil.blade.php`):** Ditambahkan panel *"📋 Evaluasi Soal (Benar & Salah)"* yang menampilkan review butir demi butir: pertanyaan, jawaban yang dipilih siswa, kunci jawaban sistem, badge Benar/Salah, dan bobot poin tiap soal.
   - **Pada Halaman Guru (`guru/nilai/show.blade.php`):** Guru dapat melihat detail evaluasi jawaban siswa per butir soal secara lengkap (jawaban siswa vs kunci jawaban) beserta ringkasan statistik benar dan salah.

10. **Otomatisasi Validasi & Pengulangan Pengerjaan (Poin 4):**
    - Karena soal adalah pilihan ganda objektif, sistem otomatis menetapkan status `sudah divalidasi` saat kuis dikumpulkan (`KuisSubmissionService@submit`). Siswa dan guru tidak perlu lagi menunggu atau memvalidasi manual.
    - Pada kategori `pre-test`, `pass-test`, dan `kuis harian`: jika siswa tidak mencapai KKM (`nilai < kkm`), sistem langsung menyediakan tombol **"↻ Kerjakan Ulang"** baik di halaman daftar kuis (`siswa/kuis/index.blade.php`) maupun di halaman hasil kuis (`siswa/kuis/hasil.blade.php`).

**File yang diubah pada sesi ini:**
- `app/Http/Controllers/RemedialController.php` (filter kuis asal remedial hanya kategori ulangan)
- `app/Services/KuisSubmissionService.php` (otomatis validasi, pertahankan draft jawaban untuk evaluasi benar/salah)
- `app/Http/Controllers/SiswaKuisController.php` (reset draft hanya saat sesi baru dibuat, data evaluasi soal di halaman hasil)
- `app/Http/Controllers/ValidasiNilaiController.php` (data evaluasi soal untuk guru)
- `resources/views/siswa/kuis/kerjakan.blade.php` (penguncian total navigasi CBT mode & event listener guard)
- `resources/views/siswa/kuis/hasil.blade.php` (panel evaluasi benar/salah per butir & tombol kerjakan ulang)
- `resources/views/siswa/kuis/index.blade.php` (tombol kerjakan ulang untuk pre-test, pass-test, kuis harian)
- `resources/views/guru/nilai/show.blade.php` (panel evaluasi butir soal siswa & keterangan validasi otomatis)
- `tests/Feature/SiswaKuisWaktuTest.php` (penyesuaian assertion draft jawaban tetap ada untuk evaluasi)
- `WASIAT.md` (pembaruan dokumentasi handoff)

---

## ✅ Fase 5 — Login NISN/NIP & Pemisahan Portal Admin (SELESAI 2026-10-31)

Fitur yang diimplementasikan dan diuji (19 test di `LoginNisnNipTest.php`):

1. **Pemisahan Portal Login Siswa/Guru vs Administrator:**
   - **Portal Siswa & Guru (`/login`):**
     - Siswa login hanya menggunakan **NISN** (`nomor_induk`).
     - Guru login hanya menggunakan **NIP** (`nomor_induk`).
     - Input berlabel `NISN / NIP` dengan placeholder `Masukkan NISN (Siswa) atau NIP (Guru)`.
     - Kotak Akun Demo **hanya** menampilkan akun Guru (NIP 198001) dan Siswa (NISN 202401). Informasi admin sudah dibersihkan dari halaman ini.
     - Di bawah kotak akun demo terdapat tombol khusus `🛡️ Login sebagai Administrator` yang mengarahkan ke portal admin.
     - Jika akun dengan peran `admin` mencoba masuk lewat form ini, sistem akan menolak dan menginstruksikan untuk menggunakan halaman admin.
   - **Portal Khusus Administrator (`/admin/login`):**
     - Dibuatkan halaman login khusus bertema administrator (`resources/views/auth/admin-login.blade.php`).
     - Input wajib berupa **Email Administrator** dan **Password**.
     - Menampilkan kotak Akun Demo khusus admin (`andi@sekolah.sch.id` / `123456`).
     - Terdapat tombol `← Kembali ke Login Siswa & Guru`.
     - Akun non-admin otomatis ditolak jika mencoba masuk lewat portal ini.

2. **Middleware & Alur Paksa Ganti Password (`wajib_ganti_password`):**
   - Saat admin membuat atau mereset password akun guru/siswa, kolom `wajib_ganti_password = true`.
   - Middleware `EnsurePasswordChanged` (`app/Http/Middleware/EnsurePasswordChanged.php`) dipasang di grup `web` (`bootstrap/app.php`).
   - Pengguna dengan flag `wajib_ganti_password = true` otomatis dicegat dan diarahkan ke rute `password.wajib` (`/password/wajib`).
   - Formulir di `resources/views/auth/wajib-ganti-password.blade.php` meminta password baru (min. 6 karakter + konfirmasi).
   - Setelah berhasil mengganti password, flag `wajib_ganti_password` diubah menjadi `false` dan pengguna diarahkan ke dashboard masing-masing.

3. **Pop-up Modal Ganti Password di Halaman Profil Siswa & Guru:**
   - Halaman profil (`resources/views/guru/profil.blade.php`) ditambahkan tombol **🔑 Ganti Password**.
   - Menampilkan modal pop-up:
     - `password_lama`: Password saat ini (diverifikasi dengan `Hash::check`).
     - `password_baru`: Password baru (minimal 6 karakter, konfirmasi, harus berbeda dari password lama).
   - Rute `PUT /profil/password` (`PasswordController@updateProfilPassword`).
   - Dilengkapi alert notifikasi sukses/error dan JavaScript pembuka modal otomatis bila terjadi kesalahan input.

---

## 🔍 Audit Bertahap (Dead Code, Unused Logic, & Maintainability)

Checklist modul untuk audit dan pembersihan kode:

- [x] **Bagian 1: Autentikasi & Password** (`app/Http/Controllers/AuthController.php`, `app/Http/Controllers/PasswordController.php`, `app/Http/Middleware/EnsurePasswordChanged.php`, `app/Http/Middleware/EnsureAccountActive.php`)
  - *Status:* ✅ Selesai dibersihkan. Duplikasi dihilangkan (`AuthController@login` sekarang memakai `$this->redirectSesuaiRole`) dan tujuan redirect Guru di `PasswordController` diseragamkan ke `dashboard`. Test 218/218 lolos.
- [ ] **Bagian 2: Siswa Area** (`app/Http/Controllers/SiswaDashboardController.php`, `app/Http/Controllers/ProgressController.php`, `app/Http/Controllers/SiswaKuisController.php`)
- [ ] **Bagian 3: Guru - Materi, Kuis, & Bank Soal** (`app/Http/Controllers/DashboardController.php`, `app/Http/Controllers/MateriController.php`, `app/Http/Controllers/KuisController.php`, `app/Http/Controllers/SoalController.php`)
- [ ] **Bagian 4: Guru - Nilai & Remedial** (`app/Http/Controllers/ValidasiNilaiController.php`, `app/Http/Controllers/RemedialController.php`, `app/Http/Controllers/RemedialSiswaController.php`)
- [ ] **Bagian 5: Panel Admin** (`app/Http/Controllers/Admin/*`)
- [x] **Bagian 6: Models, Services, & Console Commands** (`app/Models/*`, `app/Services/*`, `app/Console/Commands/*`)

---

## ⏳ PEKERJAAN YANG BELUM SELESAI (urut prioritas)

### 0. Pertanyaan terbuka — tanyakan ke pemilik dulu, jangan putuskan sendiri

- Rename user U003 "Andi Operator" → "Andi Admin", dan kartu demo "Operator" di halaman login.
- Apakah menjalankan `php artisan boost:install` (akan menimpa `AGENTS.md`).
- Nasib branch `coba_cicd` dan `revert/cicd`.
- Kapan & bagaimana commit pekerjaan di `feat/admin-backend`.

### 1. Fase 6 — Lanjutan Minor

- **Halaman `kuis/show.blade.php`**: Tampilkan juga informasi `id_mapel` / nama mapel di info grid kuis.
- **Test khusus Fase 6**: Buat `tests/Feature/KuisProgressionAndSubjectTest.php` untuk memverifikasi:
  - Guru & siswa navigasi kuis via Mata Pelajaran.
  - Progresi Kuis Harian terkunci dan terbuka setelah siswa mencapai KKM.
  - Bobot soal otomatis seimbang (total 100).
  - Publish & Unpublish kuis.
  - Penjadwalan kuis (`waktu_mulai` di masa depan menolak pengerjaan).

### 2. Halaman Siswa — Profil

- Profil siswa saat ini menggunakan file yang sama dengan guru (`resources/views/guru/profil.blade.php`).
  Perlu diverifikasi apakah sudah ada conditional untuk role siswa vs guru.

### 3. Fase 7 — Review Menyeluruh

- Keamanan, konsistensi, pesan validasi bahasa Indonesia di semua form.

### 4. Countdown Timer Kuis (sisi siswa)

- Cek implementasi JavaScript countdown timer di `siswa/kuis/kerjakan.blade.php`.

### 5. Export Excel Rekap Nilai — DITUNDA

- Catatan: dependensi `maatwebsite/excel ^1.1` di composer.json mencurigakan (versi sangat lama), periksa sebelum dipakai.

### 6. Import Soal dari Excel — DITUNDA

- Atas permintaan pemilik ("aku perlu mempelajarinya dulu").

---

## Catatan/Peringatan

### Aturan WAJIB
- ✅ **Migration Laravel:** per 2026-11-13, migration tabel tertinggal sudah dipersiapkan dan diperiksa terhadap database `itik`; setelah backup, jalankan `php artisan migrate` biasa. **Jangan jalankan `migrate:fresh` atau rollback** pada database aktif.
- 💾 **Backup DB sebelum DDL** yang tidak bisa dibatalkan (contoh: `mysqldump -uroot itik > storage/backup/itik-backup-YYYYMMDD-HHMMSS.sql`).
- 🧑‍🏫 **Jangan sampai fungsi guru & siswa bergeser** karena penambahan admin. Jika memang perlu menyentuh kode guru/siswa, **minta izin pemilik dulu**.
- ✉️ Jangan ubah validasi `email` di `Admin\SiswaController`/`Admin\GuruController` dari `nullable` menjadi `required` (belum disetujui pemilik; nanti login pakai NISN/NIP).
- 🌿 Git: jelaskan cara rollback dulu, kerja di branch, batalkan dengan `git revert`. **Jangan force-push, jangan push/merge, jangan commit tanpa izin eksplisit.**
- 🧹 Setelah menjalankan dev server untuk uji coba, laporkan PID/port yang masih hidup dan tawarkan untuk mematikannya.

### Lingkungan
- PHP: `C:/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe` (tidak ada di PATH Git Bash).
- MySQL: `C:/laragon/bin/mysql/mysql-8.4.3-winx64/bin/mysql.exe`, user `root`, password kosong, database `itik`. MySQL **bukan Windows service** — harus di-start lewat Laragon dulu.
- Lokasi proyek: `C:\laragon\www\ITIK-WEBISITE-DESKTOP`.

### Testing
- Jalankan: `php artisan test` (dari folder proyek, pakai path PHP di atas).
- Test memakai **SQLite `:memory:`** (lihat `phpunit.xml`) dan membangun skemanya sendiri dengan `Schema::create` — **tidak menyentuh DB live**. Test punya guard yang gagal jika bukan SQLite.
- Hasil terakhir (2026-10-31 sesi keempat): **211 passed, 3466 assertions**.

### Skema DB (setelah Fase 1)
- Tabel baru: `tahun_ajaran`, `mapel`, `kelas`, `anggota_kelas`, `mapel_kelas`.
- `user`: + `status` (default `aktif`), `wajib_ganti_password`, `dibuat_oleh`, `created_at`, `updated_at`; enum `role` = `admin`, `guru`, `siswa`.
- `kuis`: + `tingkat_kesulitan` enum (default `mudah`), `id_mapel` NOT NULL FK, `id_tahun_ajaran` NOT NULL FK; `kategori` = `post-test`, `pre-test`, `kuis harian`, `ulangan`, `remedial`.
- `materi`: + `id_mapel` NOT NULL FK.
- Data master awal: `MP001` TIK (aktif), `TA001` 2026/2027 (aktif).

### Keputusan Desain (jangan dibalik tanpa izin)
- Admin **hanya entri data**: tidak bisa menghapus, tidak mengelola materi/kuis (guru-only routes tetap `role:guru`).
- Data dinonaktifkan, **bukan dihapus**, karena FK memakai CASCADE.
- Penjadwalan kuis (`waktu_mulai`, `waktu_selesai`): **opsional untuk semua kategori**, wajib untuk `ulangan`. Jika kategori berubah dari ulangan ke lain, jadwal tetap disimpan jika ada di payload.
- Progresi level Kuis Harian berlaku **per mata pelajaran**: mudah → sedang → sulit (hanya kategori `kuis harian`). Kategori lain hanya tampilan.
- Bobot soal dihitung otomatis merata (100 / jumlah soal). Guru tidak perlu mengisi bobot.
- Kolom `sesi_kuis.durasi_detik` **tidak ada di DB live**. Durasi dihitung dari selisih `waktu_mulai` → `waktu_selesai`.

---

## Status Terakhir

Fase 0–5 **selesai** dan teruji.
Test suite: **211 passed, 3466 assertions** (sebelumnya 210/3453).
**Belum ada yang di-commit** — semua perubahan masih di working tree branch `feat/admin-backend`.

| Fase | Isi | Status |
|---|---|---|
| 0 | Backup DB (`storage/backup/itik-backup-20261029-023228.sql`) + branch `feat/admin-backend` | ✅ |
| 1 | DDL: tabel master + kolom baru (lihat "Skema DB") — sudah diterapkan ke DB live `itik` | ✅ |
| 2 | Model Eloquent baru (`TahunAjaran`, `Mapel`, `Kelas`, `AnggotaKelas`) + update `User`, `Kuis`, `Materi` | ✅ |
| 3 | Panel admin (dashboard, profil, tahun ajaran, mapel, kelas + anggota, siswa, guru, rekap nilai) | ✅ |
| 4 | Guru wajib memilih mapel saat membuat kuis/materi, tahun ajaran kuis otomatis, `pass-test` → `pre-test` | ✅ |
| 4b | Aturan waktu kuis (timer server-side, auto-submit, durasi di halaman hasil) | ✅ |
| 5 | Login NISN/NIP (siswa/guru), halaman login terpisah Admin (/admin/login via email), middleware paksa ganti password, modal pop-up profil | ✅ |

---

## ✅ Fase 5 — Login NISN/NIP & Pemisahan Portal Admin (SELESAI 2026-10-31)

Fitur yang diimplementasikan dan diuji (19 test di `LoginNisnNipTest.php`):

1. **Pemisahan Portal Login Siswa/Guru vs Administrator:**
   - **Portal Siswa & Guru (`/login`):**
     - Siswa login hanya menggunakan **NISN** (`nomor_induk`).
     - Guru login hanya menggunakan **NIP** (`nomor_induk`).
     - Input berlabel `NISN / NIP` dengan placeholder `Masukkan NISN (Siswa) atau NIP (Guru)`.
     - Kotak Akun Demo **hanya** menampilkan akun Guru (NIP 198001) dan Siswa (NISN 202401). Informasi admin sudah dibersihkan dari halaman ini.
     - Di bawah kotak akun demo terdapat tombol khusus `🛡️ Login sebagai Administrator` yang mengarahkan ke portal admin.
     - Jika akun dengan peran `admin` mencoba masuk lewat form ini, sistem akan menolak dan menginstruksikan untuk menggunakan halaman admin.
   - **Portal Khusus Administrator (`/admin/login`):**
     - Dibuatkan halaman login khusus bertema administrator (`resources/views/auth/admin-login.blade.php`).
     - Input wajib berupa **Email Administrator** dan **Password**.
     - Menampilkan kotak Akun Demo khusus admin (`andi@sekolah.sch.id` / `123456`).
     - Terdapat tombol `← Kembali ke Login Siswa & Guru`.
     - Akun non-admin otomatis ditolak jika mencoba masuk lewat portal ini.

2. **Middleware & Alur Paksa Ganti Password (`wajib_ganti_password`):**
   - Saat admin membuat atau mereset password akun guru/siswa, kolom `wajib_ganti_password = true`.
   - Middleware `EnsurePasswordChanged` (`app/Http/Middleware/EnsurePasswordChanged.php`) dipasang di grup `web` (`bootstrap/app.php`).
   - Pengguna dengan flag `wajib_ganti_password = true` otomatis dicegat dan diarahkan ke rute `password.wajib` (`/password/wajib`).
   - Formulir di `resources/views/auth/wajib-ganti-password.blade.php` meminta password baru (min. 6 karakter + konfirmasi).
   - Setelah berhasil mengganti password, flag `wajib_ganti_password` diubah menjadi `false` dan pengguna diarahkan ke dashboard masing-masing.

3. **Pop-up Modal Ganti Password di Halaman Profil Siswa & Guru:**
   - Halaman profil (`resources/views/guru/profil.blade.php`) ditambahkan tombol **🔑 Ganti Password**.
   - Menampilkan modal pop-up:
     - `password_lama`: Password saat ini (diverifikasi dengan `Hash::check`).
     - `password_baru`: Password baru (minimal 6 karakter, konfirmasi, harus berbeda dari password lama).
   - Rute `PUT /profil/password` (`PasswordController@updateProfilPassword`).
   - Dilengkapi alert notifikasi sukses/error dan JavaScript pembuka modal otomatis bila terjadi kesalahan input.

**File yang diubah/dibuat di Fase 5:**
- `app/Http/Controllers/AuthController.php` (ditambah `showAdminLogin`, `adminLogin`, filter role)
- `app/Http/Controllers/PasswordController.php` (baru)
- `app/Http/Middleware/EnsurePasswordChanged.php` (baru)
- `bootstrap/app.php`
- `routes/web.php` (`/admin/login`, rute password)
- `resources/views/auth/login.blade.php`
- `resources/views/auth/admin-login.blade.php` (baru)
- `resources/views/auth/wajib-ganti-password.blade.php` (baru)
- `resources/views/guru/profil.blade.php`
- `tests/Feature/LoginNisnNipTest.php` (19 test baru, SQLite `:memory:`)

---

## ✅ Fase 4b — Aturan Waktu Kuis (SELESAI 2026-10-31)

Tiga aturan yang diminta pemilik berhasil diimplementasikan dan diuji (15 test baru):

1. **Waktu berjalan sejak Mulai, menutup tab tidak mereset timer** — `waktu_mulai` di tabel `sesi_kuis` menjadi acuan tunggal. `KuisSubmissionService::batasWaktu()` selalu dihitung dari `sesi.waktu_mulai + kuis.alokasi_waktu`.
2. **Auto-submit saat waktu habis, soal kosong = salah (nilai 0)** — auto-submit terjadi di tiga titik:
   - Saat siswa membuka `siswa.kuis.index`, `siswa.nilai.index`, dan `SiswaDashboard@index` (via `selesaikanMilikSiswa()`).
   - Saat halaman `kerjakan()` atau `mulai()` diakses dengan sesi yang sudah expired (langsung `submit($sesi, true)` lalu redirect ke hasil dengan pesan "Waktu habis, jawaban otomatis dikumpulkan.").
   - Via scheduler `kuis:selesaikan-kedaluwarsa` (setiap menit, hanya kuis `is_aktif = true`).
3. **Halaman hasil menampilkan durasi** — selisih `waktu_mulai` → `waktu_selesai` dari `sesi_kuis` yang cocok `(id_kuis, id_user, percobaan_ke)`. Tampil "X menit Y detik". Nilai lama tanpa sesi → "Belum tercatat".

**Bug lama yang ikut diperbaiki:**
- `SiswaKuisController@kerjakan` sebelumnya pakai `firstOrFail()` tanpa filter → ambil sesi paling lama → percobaan ke-2 langsung ditolak "Waktu habis". Sekarang pakai `KuisSubmissionService::sesiAktif()` (`whereNull('waktu_selesai')->latest('id_sesi')`).
- `submit()` controller punya duplikasi logika penilaian yang sekarang didelegasikan ke `KuisSubmissionService::submit()`.

**File yang diubah di Fase 4b:**
- `app/Http/Controllers/SiswaKuisController.php`
- `app/Http/Controllers/SiswaDashboardController.php`
- `resources/views/siswa/kuis/hasil.blade.php`
- `tests/Feature/SiswaKuisWaktuTest.php` (**BARU** — 15 test, SQLite `:memory:`)

**Catatan scheduler (penting!):**
Di Laragon tidak ada cron → `php artisan schedule:work` harus dijalankan manual di terminal terpisah agar auto-submit berjalan berbasis waktu nyata. Tanpa itu, auto-submit tetap terjadi saat siswa membuka halaman (sudah cukup untuk kebanyakan kasus).

---

## 🔍 Audit Bertahap (Dead Code, Unused Logic, & Maintainability)

Checklist modul untuk audit dan pembersihan kode:

- [x] **Bagian 1: Autentikasi & Password** (`app/Http/Controllers/AuthController.php`, `app/Http/Controllers/PasswordController.php`, `app/Http/Middleware/EnsurePasswordChanged.php`, `app/Http/Middleware/EnsureAccountActive.php`)
  - *Status:* ✅ Selesai dibersihkan. Duplikasi dihilangkan (`AuthController@login` sekarang memakai `$this->redirectSesuaiRole`) dan tujuan redirect Guru di `PasswordController` diseragamkan ke `dashboard`. Test 218/218 lolos.
- [ ] **Bagian 2: Siswa Area** (`app/Http/Controllers/SiswaDashboardController.php`, `app/Http/Controllers/ProgressController.php`, `app/Http/Controllers/SiswaKuisController.php`)
- [ ] **Bagian 3: Guru - Materi, Kuis, & Bank Soal** (`app/Http/Controllers/DashboardController.php`, `app/Http/Controllers/MateriController.php`, `app/Http/Controllers/KuisController.php`, `app/Http/Controllers/SoalController.php`)
- [ ] **Bagian 4: Guru - Nilai & Remedial** (`app/Http/Controllers/ValidasiNilaiController.php`, `app/Http/Controllers/RemedialController.php`, `app/Http/Controllers/RemedialSiswaController.php`)
- [ ] **Bagian 5: Panel Admin** (`app/Http/Controllers/Admin/*`)
- [x] **Bagian 6: Models, Services, & Console Commands** (`app/Models/*`, `app/Services/*`, `app/Console/Commands/*`)

---

## ⏳ PEKERJAAN YANG BELUM SELESAI (urut prioritas)

Urutan kerja yang disepakati: database → backend → login NISN/NIP (Fase 5 selesai) → Fase 6.

### 0. Pertanyaan terbuka — tanyakan ke pemilik dulu, jangan putuskan sendiri

- Rename user U003 "Andi Operator" → "Andi Admin", dan kartu demo "Operator" di halaman login (`resources/views/auth/login.blade.php` sekitar baris 649).
- Apakah menjalankan `php artisan boost:install` (akan menimpa `CLAUDE.md`/`AGENTS.md`).
- Nasib branch `coba_cicd` dan `revert/cicd`.
- Kapan & bagaimana commit pekerjaan di `feat/admin-backend`.

### 1. Fase 6 — SoalController (prioritas berikutnya)

- Sesuaikan dengan `kuis.tingkat_kesulitan`, lalu `DROP COLUMN soal.tingkat_kesulitan` (backup DB dulu!).
- Di form buat/edit soal guru, hapus dropdown tingkat kesulitan soal karena tingkat kesulitan sekarang ditentukan per kuis.

### 2. Fase 7 — Review menyeluruh

- Keamanan, konsistensi, pesan validasi bahasa Indonesia di semua form.

### 3. Progresi level berbasis KKM

- mudah → sedang → sulit (permintaan klien, belum dikerjakan).

### 4. Countdown timer kuis (sisi siswa)

- Cek implementasi JavaScript countdown timer di `siswa/kuis/kerjakan.blade.php`.

### 5. Export Excel rekap nilai — DITUNDA

- Catatan: dependensi `maatwebsite/excel ^1.1` di composer.json mencurigakan (versi sangat lama), periksa sebelum dipakai.

### 6. Import soal dari Excel — DITUNDA

- Atas permintaan pemilik ("aku perlu mempelajarinya dulu").

---

## File yang Terlibat

**Fase 5 (Login NISN/NIP & Password — terakhir dikerjakan):**
- `app/Http/Controllers/AuthController.php`
- `app/Http/Controllers/PasswordController.php` (baru)
- `app/Http/Middleware/EnsurePasswordChanged.php` (baru)
- `bootstrap/app.php`
- `routes/web.php`
- `resources/views/auth/login.blade.php`
- `resources/views/auth/wajib-ganti-password.blade.php` (baru)
- `resources/views/guru/profil.blade.php`
- `tests/Feature/LoginNisnNipTest.php` (baru)

**Fase 4b (Aturan Waktu Kuis):**
- `app/Services/KuisSubmissionService.php` — `sesiAktif()`, `batasWaktu()`, `submit()`, `selesaikanMilikSiswa()`, `selesaikanKedaluwarsa()`
- `app/Http/Controllers/SiswaKuisController.php`
- `app/Http/Controllers/SiswaDashboardController.php`
- `resources/views/siswa/kuis/hasil.blade.php`
- `routes/console.php` — command `kuis:selesaikan-kedaluwarsa` + schedule `everyMinute()->withoutOverlapping()`
- `tests/Feature/SiswaKuisWaktuTest.php`

**Tampilan siswa (2026-10-30):**
- `app/Http/Controllers/SiswaDashboardController.php` (baru) — data beranda siswa; route `siswa.dashboard` di `routes/web.php`
- `resources/views/partials/sidebar.blade.php` (baru) — menu sesuai role
- `resources/views/siswa/dashboard.blade.php`, `siswa/kuis/{index,kerjakan,hasil}.blade.php`, `siswa/materi/{index,show}.blade.php`, `siswa/nilai/index.blade.php`, `siswa/remedial/index.blade.php`. Label "Portal Guru" salinan Kevin sudah diganti "Portal Siswa".
- `resources/views/guru/profil.blade.php` — sekarang mengikuti role (menu dan tombol kembali untuk siswa).

**Fase 4:**
- `app/Http/Controllers/KuisController.php` — `pilihanMapel()`, `aturanMapel()`, kategori `pre-test`, tahun ajaran aktif, `tingkat_kesulitan`
- `app/Http/Controllers/MateriController.php` — validasi & dropdown mapel, ID via `MenghasilkanId`
- `resources/views/kuis/create.blade.php`, `resources/views/kuis/edit.blade.php`
- `resources/views/materi/create.blade.php`, `resources/views/materi/edit.blade.php`
- `tests/Feature/KuisMateriMapelTest.php` (baru)

**Fase 1–3:**
- `database/sql/2026_10_29_fase1_admin_schema.sql` + `2026_10_29_fase1_verify.sql` — DDL yang sudah diterapkan
- `database/itik.sql` — dump skema terbaru (**sumber kebenaran skema**)
- `database/seeders/DatabaseSeeder.php`
- `app/Models/TahunAjaran.php` (`TahunAjaran::aktif()` = method statis, return `?self`), `Mapel.php` (`Mapel::aktif()` = scope), `Kelas.php`, `AnggotaKelas.php`, update `User.php`, `Kuis.php`, `Materi.php`
- `app/Support/MenghasilkanId.php` — `idBerikutnya($tabel, $kolom, $prefix, $panjang = 3)`
- `app/Http/Middleware/EnsureAccountActive.php` (dipasang di grup `web` lewat `bootstrap/app.php`) — akun berstatus nonaktif otomatis di-logout
- `app/Http/Controllers/Admin/` — `DashboardController`, `TahunAjaranController` (+`aktifkan`), `MapelController`, `KelasController` (+`tambahAnggota`, sync mapel per kelas), `SiswaController` & `GuruController` (+`aktifkan`/`nonaktifkan`), `RekapNilaiController`
- `app/Http/Controllers/AuthController.php` — redirect admin ke `admin.dashboard`
- `routes/web.php` — grup `auth` + `role:admin`, prefix `/admin`, nama `admin.*`; **tidak ada route destroy**
- `resources/views/admin/**`, `resources/views/layouts/admin.blade.php`
- `tests/Feature/AdminPanelTest.php`

### Skema DB (setelah Fase 1)
- Tabel baru: `tahun_ajaran`, `mapel`, `kelas`, `anggota_kelas`, `mapel_kelas`.
- `user`: + `status` (default `aktif`), `wajib_ganti_password`, `dibuat_oleh`, `created_at`, `updated_at`; enum `role` = `admin`, `guru`, `siswa` (`operator` sudah dimigrasi ke `admin`).
- `kuis`: + `tingkat_kesulitan` enum (default `mudah`), `id_mapel` NOT NULL FK, `id_tahun_ajaran` NOT NULL FK; `kategori` = `post-test`, `pre-test`, `kuis harian`, `ulangan`, `remedial`.
- `materi`: + `id_mapel` NOT NULL FK.
- Data master awal: `MP001` TIK (aktif), `TA001` 2026/2027 (aktif).

### Keputusan desain (jangan dibalik tanpa izin)
- Admin **hanya entri data**: tidak bisa menghapus, tidak mengelola materi/kuis (guru-only routes tetap `role:guru`).
- Data dinonaktifkan, **bukan dihapus**, karena FK memakai CASCADE (hapus = riwayat nilai ikut hilang).
- Rekap nilai: per kelas (X TKJ 1 dan X TKJ 2 terpisah), filter tahun ajaran, ambil **nilai terbaik per kuis**, **tanpa pembobotan persentase**.
- Satu siswa hanya satu kelas per tahun ajaran. Fitur "keluarkan anggota" sengaja dihapus.
- Tahun ajaran kuis dikunci saat kuis dibuat.
- Kolom `sesi_kuis.durasi_detik` **tidak ada di DB live** (hanya ada di `origin/main`). Jangan tambahkan. Durasi dihitung dari selisih `waktu_mulai` → `waktu_selesai`.

---

## Catatan/Peringatan

### Aturan WAJIB
- ✅ **Migration Laravel:** per 2026-11-13, migration tabel tertinggal sudah dipersiapkan dan diperiksa terhadap database `itik`; setelah backup, jalankan `php artisan migrate` biasa. **Jangan jalankan `migrate:fresh` atau rollback** pada database aktif.
- 💾 **Backup DB sebelum DDL** yang tidak bisa dibatalkan (contoh: `mysqldump -uroot itik > storage/backup/itik-backup-YYYYMMDD-HHMMSS.sql`).
- 🧑‍🏫 **Jangan sampai fungsi guru & siswa bergeser** karena penambahan admin. Jika memang perlu menyentuh kode guru/siswa, **minta izin pemilik dulu**.
- ✉️ Jangan ubah validasi `email` di `Admin\SiswaController`/`Admin\GuruController` dari `nullable` menjadi `required` (belum disetujui pemilik; nanti login pakai NISN/NIP).
- 🌿 Git: jelaskan cara rollback dulu, kerja di branch, batalkan dengan `git revert`. **Jangan force-push, jangan push/merge, jangan commit tanpa izin eksplisit.**
- 🧹 Setelah menjalankan dev server untuk uji coba, laporkan PID/port yang masih hidup dan tawarkan untuk mematikannya.

### Lingkungan
- PHP: `C:/laragon/bin/php/php-8.3.30-Win32-vs16-x64/php.exe` (tidak ada di PATH Git Bash).
- MySQL: `C:/laragon/bin/mysql/mysql-8.4.3-winx64/bin/mysql.exe`, user `root`, password kosong, database `itik`. MySQL **bukan Windows service** — harus di-start lewat Laragon dulu (kalau tidak, muncul `ERROR 2003 ... (10061)`).
- Lokasi proyek: `C:\laragon\www\ITIK-WEBISITE-DESKTOP`.

### Testing
- Jalankan: `php artisan test` (dari folder proyek, pakai path PHP di atas).
- Test memakai **SQLite `:memory:`** (lihat `phpunit.xml`) dan membangun skemanya sendiri dengan `Schema::create` — **tidak menyentuh DB live**. Test punya guard yang gagal jika bukan SQLite.
- Hasil terakhir (2026-10-31 sesi ketiga): **205 passed, 3378 assertions**.


---

## ✅ Fitur: Kontrol Tampil Jawaban Evaluasi Kuis (SELESAI 2026-11-10)

### Deskripsi
Tambahan kolom `kuis.tampil_jawaban` untuk mengontrol kapan siswa boleh melihat evaluasi benar/salah per soal setelah mengerjakan kuis. Tiga opsi:
- `setelah_submit` (default) — evaluasi langsung muncul setelah submit
- `setelah_kkm` — evaluasi hanya muncul jika nilai ≥ KKM
- `tidak` — evaluasi tidak pernah ditampilkan ke siswa

Keputusan dibuat di **controller** (bukan Blade), sehingga data tidak bocor ke sumber halaman.
Guru tidak terpengaruh — halaman evaluasi guru tetap selalu menampilkan detail.

### File yang Berubah
| File | Perubahan |
|---|---|
| `database/sql/2026_11_10_tampil_jawaban_kuis.sql` | File SQL baru — ALTER TABLE kuis ADD COLUMN tampil_jawaban |
| `database/itik.sql` | Kolom `tampil_jawaban` ditambahkan ke DDL tabel `kuis` |
| `app/Models/Kuis.php` | `'tampil_jawaban'` ke `$fillable`, konstanta `PILIHAN_TAMPIL_JAWABAN` |
| `app/Http/Controllers/KuisController.php` | Konstanta `PILIHAN_TAMPIL_JAWABAN`, validasi di `store()` dan `update()`, default fallback |
| `resources/views/guru/kuis/create.blade.php` | Dropdown `tampil_jawaban` sebelum form-actions |
| `resources/views/guru/kuis/edit.blade.php` | Dropdown `tampil_jawaban` sebelum form-actions |
| `app/Http/Controllers/SiswaKuisController.php` | Method `hasil()`: hitung `$tampilDetail`, filter data evaluasi jika false |
| `resources/views/siswa/kuis/hasil.blade.php` | Panel eval-stats dan evaluasi soal dibungkus `@if($tampilDetail ?? false)` |
| `tests/Feature/TampilJawabanKuisTest.php` | File tes baru — 4 skenario, 32 assertions |
| `tests/Feature/KuisMateriMapelTest.php` + 8 file tes lain | Schema SQLite ditambah kolom `tampil_jawaban` |

### Hasil Tes
- `TampilJawabanKuisTest`: **4/4 passed, 32 assertions** ✅
- Full suite (non-ApiTest): **273 passed, 4247 assertions** (sebelumnya 269)
- Satu-satunya gagal: `ApiTest::test_protected_api_requires_a_token` yang sudah pre-exist



---

## ✅ Fitur: Aktivasi Mata Pelajaran di Index Admin (SELESAI 2026-11-11)

### Deskripsi
Menambahkan tombol toggle status ("Aktifkan" / "Nonaktifkan") di kolom Tindakan pada tabel katalog mata pelajaran (`resources/views/admin/mapel/index.blade.php`), tepat di samping tombol "Ubah". Memanfaatkan route PATCH yang sudah ada (`admin.mapel.update` / `MapelController@update`).
- Label tombol otomatis menyesuaikan dengan status saat ini: jika aktif menampilkan tombol "Nonaktifkan" (`btn-warning`), jika nonaktif menampilkan tombol "Aktifkan" (`btn`).
- Dilengkapi atribut `data-confirm` standar admin sebelum submit form.
- Pada `MapelController@update`, jika request tidak mengirimkan `nama_mapel`, controller hanya memperbarui kolom `is_aktif` tanpa memvalidasi atau mengubah atribut lain (`kode_mapel`, `nama_mapel`, `deskripsi`).
- Mengembalikan redirect ke `admin.mapel.index` dengan pesan flash sukses.

### File yang Berubah
| File | Perubahan |
|---|---|
| `resources/views/admin/mapel/index.blade.php` | Menambahkan form tombol toggle PATCH status di samping tombol Ubah dalam `<div class="actions">` |
| `app/Http/Controllers/Admin/MapelController.php` | Memperbarui method `update()` agar mengenali update status saja tanpa menyentuh field lain |
| `tests/Feature/AdminPanelTest.php` | Menambahkan test `test_admin_can_toggle_mapel_status_from_index_action_and_redirects` |

### Hasil Tes
- `AdminPanelTest`: **93/93 passed, 1451 assertions** ✅
- Full suite (non-ApiTest): **274 passed, 4270 assertions** (sebelumnya 273)


---

## ✅ Fitur: Hapus Fallback Login Email Siswa & Guru (SELESAI 2026-11-11)

### Deskripsi
Menghapus blok fallback login menggunakan `email` pada `AuthController@login` untuk siswa dan guru. Siswa dan guru kini secara ketat hanya dapat masuk menggunakan NISN atau NIP (`nomor_induk`).
- Jika siswa atau guru mencoba masuk menggunakan alamat email pada portal siswa/guru, autentikasi ditolak dengan pesan galat `"NISN, NIP, atau password salah."`.
- Portal login administrator (`/admin/login` via `adminLogin()`) tidak berubah dan tetap menggunakan Email & Password administrator.
- Pengujian di `LoginNisnNipTest.php` ditambahkan untuk memastikan login siswa & guru menggunakan email ditolak.
- Pengujian di `AdminPanelTest.php` yang sebelumnya menggunakan email untuk login siswa & guru diperbarui menggunakan `nomor_induk`.

### File yang Berubah
| File | Perubahan |
|---|---|
| `app/Http/Controllers/AuthController.php` | Menghapus blok fallback email di method `login()` |
| `tests/Feature/LoginNisnNipTest.php` | Menambahkan test `test_email_cannot_be_used_to_login_for_siswa_and_guru()` |
| `tests/Feature/AdminPanelTest.php` | Mengubah payload login siswa/guru di test `test_account_activation_and_deactivation_cycle` & `test_an_already_authenticated_session_is_denied_after_account_becomes_inactive` menggunakan `nomor_induk` |

### Hasil Tes
- `LoginNisnNipTest`: **20/20 passed, 305 assertions** ✅
- `AdminPanelTest`: **93/93 passed, 1453 assertions** ✅
- Full suite (non-ApiTest): **275 passed, 4292 assertions** (sebelumnya 274)


---

## ✅ Pembersihan: Penghapusan Input & Data Email Siswa & Guru (SELESAI 2026-11-11)

### Deskripsi
Pembersihan menyeluruh seluruh input, tampilan, dan data `email` untuk akun siswa dan guru:
- **Database**: Menjalankan query `UPDATE user SET email = NULL WHERE role IN ('siswa', 'guru');` dan memperbarui `database/itik.sql`. File migrasi manual dicatat di `database/sql/2026_11_11_kosongkan_email_siswa_guru.sql`.
- **Formulir Admin**: Menghapus input field `email` pada formulir tambah & edit siswa (`resources/views/admin/siswa/_form.blade.php`) dan guru (`resources/views/admin/guru/_form.blade.php`).
- **Tabel Admin**: Menghapus tampilan baris email pada tabel daftar siswa (`admin/siswa/index.blade.php`) dan guru (`admin/guru/index.blade.php`), judul kolom disederhanakan menjadi `"Nama lengkap"`.
- **Halaman Profil Siswa & Guru**: Menghapus baris email pada `resources/views/shared/profil.blade.php`.
- **Backend Controller**: Memperbarui `Admin\SiswaController` dan `Admin\GuruController` pada method `store()` dan `update()` agar menyetel `email = null`.

### File yang Berubah
| File | Perubahan |
|---|---|
| `database/sql/2026_11_11_kosongkan_email_siswa_guru.sql` | Script SQL untuk mengosongkan email siswa dan guru |
| `database/itik.sql` | Memperbarui dump database tabel `user` baris siswa & guru |
| `app/Http/Controllers/Admin/SiswaController.php` | Set `email = null` pada `store()` dan `update()` |
| `app/Http/Controllers/Admin/GuruController.php` | Set `email = null` pada `store()` dan `update()` |
| `resources/views/admin/siswa/_form.blade.php` | Menghapus input `email` |
| `resources/views/admin/siswa/index.blade.php` | Menghapus tampilan email di bawah nama |
| `resources/views/admin/guru/_form.blade.php` | Menghapus input `email` |
| `resources/views/admin/guru/index.blade.php` | Menghapus tampilan email di bawah nama |
| `resources/views/shared/profil.blade.php` | Menghapus info email di profil siswa & guru |

### Hasil Tes
- `LoginNisnNipTest`: **20/20 passed, 305 assertions** ✅
- `AdminPanelTest`: **93/93 passed, 1453 assertions** ✅
- Full suite (non-ApiTest): **275 passed, 4292 assertions**


---

## ✅ Fitur: Tombol Post-test dan Pre-test Langsung di Materi (SELESAI 2026-11-11)

### Deskripsi
Mengganti tombol tunggal "Buat Kuis" di halaman katalog materi (`guru/materi/index.blade.php`) dan detail materi (`guru/materi/show.blade.php`) menjadi dua tombol langsung:
- **"Buat Post-test"**: Mengarah ke rute `kuis.create` dengan parameter query `mapel`, `materi`, dan `kategori=post-test`.
- **"Buat Pre-test"**: Mengarah ke rute `kuis.create` dengan parameter query `mapel`, `materi`, dan `kategori=pre-test`.

Di sisi backend:
- `KuisController@create` membaca parameter query `kategori`, memvalidasi nilainya terhadap daftar konstanta `self::KATEGORI`.
- Jika kategori tidak valid, request ditolak dan diredirect kembali ke `materi.index` dengan pesan flash error.
- Jika valid, kategori di-preselect dan dikunci di view `guru/kuis/create.blade.php` (menampilkan dropdown disabled dan hidden input agar nilai tetap terkirim saat form disubmit).

### File yang Berubah
| File | Perubahan |
|---|---|
| `resources/views/guru/materi/index.blade.php` | Mengganti tombol "Buat Kuis" menjadi tombol "Buat Post-test" dan "Buat Pre-test" |
| `resources/views/guru/materi/show.blade.php` | Mengganti tombol "Buat Kuis" menjadi tombol "Buat Post-test" dan "Buat Pre-test" |
| `app/Http/Controllers/KuisController.php` | Mengubah visibilitas konstanta `KATEGORI` menjadi `public`, membaca query `kategori` di method `create()`, validasi, dan passing `$kategoriDipreselect` ke view |
| `resources/views/guru/kuis/create.blade.php` | Preselect dan mengunci opsi kategori jika `$kategoriDipreselect` tersedia |
| `tests/Feature/KuisMateriMapelTest.php` | Mengupdate test tautan tombol dan menambah test preselect kategori & penolakan kategori tidak valid |

### Hasil Tes
- `KuisMateriMapelTest`: **102/102 passed, 1813 assertions** ✅
- Full suite (non-ApiTest): **277 passed, 4329 assertions** (sebelumnya 275)

## Progress Audit: Penilaian Guru & Essay (ValidasiNilaiController)

Tanggal audit: 2026-11-12 (berdasarkan zip `ITIK-WEBISITE-DESKTOP_5e5f83`, branch `sadan`, commit `6e46a1c`)

### Temuan
- [ ] S1 (Tinggi) `show()` tanpa cek kepemilikan, IDOR lintas guru
- [ ] S2 (Tinggi) `validasi()` tanpa cek kepemilikan, guru lain bisa memvalidasi
- [ ] S3 (Tinggi) `tugaskanRemedial()` tanpa cek kepemilikan, kuis remedial dibuat atas nama pemberi tugas
- [ ] S4 (Tinggi) `index()` menampilkan semua kelas, tidak difilter pengajaran (tidak konsisten dengan `unduh()`)
- [ ] L1 (Tinggi) `Api\GuruNilaiController@validasi` tidak mengecek "menunggu penilaian"
- [ ] L2 (Sedang) `tugaskanRemedial()` mempublikasikan kuis remedial kosong
- [ ] P1 (Sedang) `index()` N+1 per kelas (3 query per kelas)
- [ ] E1 (Sedang) `tugaskanRemedial()` tanpa transaksi, risiko kuis yatim
- [ ] E2 (Rendah) `nilaiEssay()` 404 generik jika `nilai_essay` tidak ada
- [ ] V2 (Rendah) filter `index()` belum dicek validasinya
- [ ] A1 (Tinggi) logika penilaian belum di service, API harus menduplikasi
- [ ] A2 (Tinggi) `routes/api.php` belum didaftarkan di `bootstrap/app.php`
- [ ] A3 (Sedang) middleware role route API belum diperiksa

### Sudah benar (acuan)
- [x] `nilaiEssay()` cek kepemilikan `kuis.id_user`, `lockForUpdate`, validasi skor
- [x] `show()` eager loading, tidak ada N+1
- [x] Keputusan akses: pembuat kuis dan guru yang ditugaskan pada mapel/kelas terkait dapat mengelola nilai.
- [x] Scope `Mengerjakan::dikelolaOlehGuru()` dan pemeriksaan `dikelolaOleh()` dipakai untuk daftar, detail, validasi, dan penugasan remedial.
- [x] `index()` dan ekspor rekap dibatasi pada cakupan pengajaran guru.
- [x] Alur penilaian essay dipusatkan di `KuisEssayGradingService`; API menolak status selain `belum divalidasi`; penugasan remedial berjalan dalam transaksi.
- [x] Tes regresi IDOR membuktikan guru lain tidak dapat membaca atau mengubah nilai.
- [x] Tes regresi API membuktikan nilai `menunggu penilaian` tidak dapat divalidasi langsung.
- [x] A2 diverifikasi: `routes/api.php` belum didaftarkan di `bootstrap/app.php` dan tetap tidak diaktifkan pada branch ini karena Sanctum tidak tersedia serta fitur API memang dikecualikan dalam catatan proyek. Uji HTTP API belum terverifikasi.