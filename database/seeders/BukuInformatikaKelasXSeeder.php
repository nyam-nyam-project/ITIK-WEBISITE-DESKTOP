<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Soal;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Seeder Dummy Data Pembelajaran Informatika / TIK Kelas X
 * Diadaptasi dari Buku Resmi Siswa Informatika SMA/SMK Kelas X Kurikulum Merdeka
 * (Kemendikbudristek - Penulis: Mushthofa, dkk.)
 * Tautan referensi: https://online.fliphtml5.com/vvfdc/vsji/
 */
class BukuInformatikaKelasXSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Dapatkan atau inisialisasi Tahun Ajaran Aktif
        $tahunAjaran = TahunAjaran::where('is_aktif', true)->first()
            ?? TahunAjaran::firstOrCreate(
                ['id_tahun_ajaran' => 'TA2026'],
                ['nama' => '2026/2027', 'is_aktif' => true]
            );

        // 2. Dapatkan atau inisialisasi Mata Pelajaran TIK / Informatika
        $mapel = Mapel::where('kode_mapel', 'TIK')->first()
            ?? Mapel::firstOrCreate(
                ['id_mapel' => 'MP-TIK-X'],
                [
                    'kode_mapel' => 'TIK',
                    'nama_mapel' => 'Informatika & TIK',
                    'deskripsi' => 'Mata pelajaran Informatika Kelas X Kurikulum Merdeka',
                    'is_aktif' => true,
                ]
            );

        // 3. Dapatkan Guru Pengampu
        $guru = User::where('role', 'guru')->first()
            ?? User::where('role', 'admin')->first()
            ?? User::firstOrCreate(
                ['id_user' => 'GURU-TIK-01'],
                [
                    'nomor_induk' => '198501012010011001',
                    'nama_lengkap' => 'Guru Informatika, S.Kom.',
                    'role' => 'guru',
                    'status' => 'aktif',
                    'email' => 'guru.informatika@sekolah.sch.id',
                    'password' => bcrypt('password123'),
                ]
            );

        // 4. Pastikan ada Kelas dan ditautkan ke Mapel
        $daftarKelas = Kelas::where('is_aktif', true)->get();
        if ($daftarKelas->isEmpty()) {
            $kelasDefault = Kelas::firstOrCreate(
                ['id_kelas' => 'KLS-X-TKJ1'],
                [
                    'nama_kelas' => 'X TKJ 1',
                    'tingkat' => 'X',
                    'id_tahun_ajaran' => $tahunAjaran->id_tahun_ajaran,
                    'is_aktif' => true,
                ]
            );
            $daftarKelas = collect([$kelasDefault]);
        }

        foreach ($daftarKelas as $kelas) {
            if (method_exists($kelas, 'mapel')) {
                $kelas->mapel()->syncWithoutDetaching([$mapel->id_mapel]);
            }
        }

        $kelasIds = $daftarKelas->pluck('id_kelas')->all();

        // 5. Definisi Paket Materi & Kuis (Pre-Test & Post-Test Berjenjang Mudah -> Sulit)
        $paketPembelajaran = [
            // =========================================================================
            // BAB 2: BERPIKIR KOMPUTASIONAL
            // =========================================================================
            [
                'id_materi' => 'MAT-TIK-X-B2',
                'judul' => 'Berpikir Komputasional: Pencarian, Pengurutan, dan Struktur Data',
                'bab' => 'Bab 2',
                'kelas' => 'X',
                'deskripsi' => "Mempelajari 4 pilar computational thinking, metode pencarian (Linear & Binary Search), pengurutan (Bubble, Insertion, Selection Sort), serta struktur data Stack dan Queue.\n\nTopik Pembahasan:\n1. Algoritma Pencarian (Searching): Linear Search memeriksa satu per satu elemen data. Binary Search memerlukan data terurut untuk mencari dengan membagi dua area pencarian.\n2. Algoritma Pengurutan (Sorting): Mengatur elemen dalam urutan menaik atau menurun menggunakan Bubble Sort, Selection Sort, atau Insertion Sort.\n3. Struktur Data Tumpukan (Stack): Prinsip LIFO (Last In First Out) seperti tumpukan piring.\n4. Struktur Data Antrean (Queue): Prinsip FIFO (First In First Out) seperti antrean tiket.",
                'isi_materi' => null,
                'link_youtube' => 'https://www.youtube.com/watch?v=mUXo-S8gkws',
                'kuis' => [
                    // PRE-TEST BAB 2 (Tingkat: Mudah)
                    [
                        'id_kuis' => 'KUIS-TIK-B2-PRE',
                        'judul' => 'Pre-test Bab 2: Berpikir Komputasional',
                        'deskripsi' => 'Tes awal untuk menguji pemahaman konsep dasar berpikir komputasional sebelum memulai materi.',
                        'kategori' => 'pre-test',
                        'tingkat_kesulitan' => 'mudah',
                        'alokasi_waktu' => 10,
                        'soal' => [
                            [
                                'pertanyaan' => 'Kemampuan memecahkan masalah besar menjadi bagian-bagian yang lebih kecil dan mudah dikelola dalam berpikir komputasional disebut ...',
                                'opsi_a' => 'Dekomposisi',
                                'opsi_b' => 'Abstraksi',
                                'opsi_c' => 'Pengenalan Pola',
                                'opsi_d' => 'Algoritma',
                                'opsi_e' => 'Debugging',
                                'jawaban' => 'A',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                            [
                                'pertanyaan' => 'Struktur data tumpukan (Stack) bekerja berdasarkan prinsip dasar ...',
                                'opsi_a' => 'FIFO (First In First Out)',
                                'opsi_b' => 'LIFO (Last In First Out)',
                                'opsi_c' => 'LILO (Last In Last Out)',
                                'opsi_d' => 'Random Access',
                                'opsi_e' => 'Linear Sorting',
                                'jawaban' => 'B',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                            [
                                'pertanyaan' => 'Antrean loket pembelian tiket bioskop merupakan contoh nyata dalam kehidupan sehari-hari dari struktur data ...',
                                'opsi_a' => 'Tree',
                                'opsi_b' => 'Graph',
                                'opsi_c' => 'Stack',
                                'opsi_d' => 'Queue',
                                'opsi_e' => 'Array dua dimensi',
                                'jawaban' => 'D',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                            [
                                'pertanyaan' => 'Proses mengatur sekumpulan data menjadi urutan membesar (misal dari A ke Z atau dari 1 ke 100) disebut urutan ...',
                                'opsi_a' => 'Descending',
                                'opsi_b' => 'Ascending',
                                'opsi_c' => 'Filtering',
                                'opsi_d' => 'Indexing',
                                'opsi_e' => 'Merging',
                                'jawaban' => 'B',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                            [
                                'pertanyaan' => 'Metode pencarian data di mana setiap elemen diperiksa satu persatu secara berurutan dari awal sampai akhir disebut ...',
                                'opsi_a' => 'Binary Search',
                                'opsi_b' => 'Linear Search',
                                'opsi_c' => 'Interpolation Search',
                                'opsi_d' => 'Bubble Search',
                                'opsi_e' => 'Hashing',
                                'jawaban' => 'B',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                        ],
                    ],
                    // POST-TEST BAB 2 (Tingkat: Berjenjang Mudah -> Sedang -> Sulit)
                    [
                        'id_kuis' => 'KUIS-TIK-B2-POST',
                        'judul' => 'Post-test Bab 2: Berpikir Komputasional',
                        'deskripsi' => 'Uji pemahaman komprehensif konsep pencarian, pengurutan, stack, dan queue dengan tingkat kesulitan bertahap.',
                        'kategori' => 'post-test',
                        'tingkat_kesulitan' => 'sedang',
                        'alokasi_waktu' => 15,
                        'soal' => [
                            // Tingkat Mudah
                            [
                                'pertanyaan' => 'Karakteristik utama algoritma Binary Search yang membedakannya dari Linear Search adalah ...',
                                'opsi_a' => 'Dapat langsung dijalankan pada data acak yang belum terurut',
                                'opsi_b' => 'Data harus sudah dalam keadaan terurut terlebih dahulu',
                                'opsi_c' => 'Hanya bisa digunakan untuk data bertipe teks',
                                'opsi_d' => 'Selalu membutuhkan waktu lebih lama dibanding Linear Search',
                                'opsi_e' => 'Hanya bisa mencari angka genap',
                                'jawaban' => 'B',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                            [
                                'pertanyaan' => 'Operasi untuk memasukkan elemen baru ke dalam tumpukan (Stack) disebut ...',
                                'opsi_a' => 'Pop',
                                'opsi_b' => 'Push',
                                'opsi_c' => 'Enqueue',
                                'opsi_d' => 'Dequeue',
                                'opsi_e' => 'Peek',
                                'jawaban' => 'B',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                            // Tingkat Sedang
                            [
                                'pertanyaan' => 'Diberikan sekumpulan data angka: [12, 5, 8, 1, 9]. Jika dilakukan Bubble Sort (ascending), data yang akan berpindah paling cepat ke posisi paling kanan pada putaran (pass) pertama adalah ...',
                                'opsi_a' => '1',
                                'opsi_b' => '5',
                                'opsi_c' => '8',
                                'opsi_d' => '9',
                                'opsi_e' => '12',
                                'jawaban' => 'E',
                                'tingkat_kesulitan' => 'sedang',
                            ],
                            [
                                'pertanyaan' => 'Sebuah antrean (Queue) mula-mula kosong. Dilakukan operasi berturut-turut: Enqueue(A), Enqueue(B), Dequeue(), Enqueue(C), Dequeue(). Elemen yang tersisa dalam antrean saat ini adalah ...',
                                'opsi_a' => 'A',
                                'opsi_b' => 'B',
                                'opsi_c' => 'C',
                                'opsi_d' => 'B dan C',
                                'opsi_e' => 'Antrean kosong',
                                'jawaban' => 'C',
                                'tingkat_kesulitan' => 'sedang',
                            ],
                            // Tingkat Sulit (HOTS)
                            [
                                'pertanyaan' => 'Sebuah daftar terurut memiliki 64 elemen unik [1, 2, 3, ..., 64]. Jika kita mencari angka tertentu menggunakan algoritma Binary Search, berapa kali perbandingan maksimal (worst-case) yang dibutuhkan sampai elemen ditemukan atau dipastikan tidak ada?',
                                'opsi_a' => '5 kali',
                                'opsi_b' => '6 kali',
                                'opsi_c' => '7 kali',
                                'opsi_d' => '32 kali',
                                'opsi_e' => '64 kali',
                                'jawaban' => 'C',
                                'tingkat_kesulitan' => 'sulit',
                            ],
                        ],
                    ],
                ],
            ],

            // =========================================================================
            // BAB 3: TEKNOLOGI INFORMASI DAN KOMUNIKASI
            // =========================================================================
            [
                'id_materi' => 'MAT-TIK-X-B3',
                'judul' => 'Teknologi Informasi dan Komunikasi: Integrasi Aplikasi Perkantoran',
                'bab' => 'Bab 3',
                'kelas' => 'X',
                'deskripsi' => "Mempelajari integrasi konten antar aplikasi perkantoran (pengolah kata, angka, presentasi), fitur Mail Merge, Object Linking and Embedding (OLE), dan pembuatan laporan terstruktur.\n\nTopik Utama:\n1. Integrasi Konten: Memasukkan tabel atau grafik dari pengolah angka (Excel/Spreadsheet) ke dalam dokumen pengolah kata (Word) atau presentasi (PowerPoint).\n2. Teknik Cut/Copy-Paste vs Object Linking and Embedding (OLE): Pada OLE, perubahan data di berkas sumber dapat diperbarui secara otomatis pada berkas tujuan.\n3. Fitur Mail Merge (Surat Massal): Menghasilkan surat, label undangan, atau sertifikat dalam jumlah banyak dengan format sama namun data penerima berbeda secara otomatis.\n4. Fitur Lanjut: Pembuatan daftar isi otomatis (Table of Contents) menggunakan Heading Styles.",
                'isi_materi' => null,
                'link_youtube' => 'https://www.youtube.com/watch?v=e_04ZrNroTo',
                'kuis' => [
                    // PRE-TEST BAB 3 (Tingkat: Mudah)
                    [
                        'id_kuis' => 'KUIS-TIK-B3-PRE',
                        'judul' => 'Pre-test Bab 3: Integrasi Aplikasi Perkantoran',
                        'deskripsi' => 'Tes diagnostik awal mengenai fungsi dasar aplikasi perkantoran dan fitur integrasi dokumen.',
                        'kategori' => 'pre-test',
                        'tingkat_kesulitan' => 'mudah',
                        'alokasi_waktu' => 10,
                        'soal' => [
                            [
                                'pertanyaan' => 'Fitur pada aplikasi pengolah kata yang digunakan untuk membuat surat massal dengan data penerima berbeda secara otomatis adalah ...',
                                'opsi_a' => 'Track Changes',
                                'opsi_b' => 'Mail Merge',
                                'opsi_c' => 'Macro Script',
                                'opsi_d' => 'Page Break',
                                'opsi_e' => 'Table of Figures',
                                'jawaban' => 'B',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                            [
                                'pertanyaan' => 'Aplikasi perkantoran yang paling tepat digunakan untuk mengolah data tabel angka, rumus statistik, dan pembuatan grafik visual adalah ...',
                                'opsi_a' => 'Aplikasi pengolah kata',
                                'opsi_b' => 'Aplikasi lembar kerja (Spreadsheet)',
                                'opsi_c' => 'Aplikasi presentasi',
                                'opsi_d' => 'Aplikasi pemutar video',
                                'opsi_e' => 'Aplikasi editor gambar vektor',
                                'jawaban' => 'B',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                            [
                                'pertanyaan' => 'Kombinasi tombol pintasan (keyboard shortcut) standar untuk menyalin (copy) objek yang dipilih adalah ...',
                                'opsi_a' => 'Ctrl + V',
                                'opsi_b' => 'Ctrl + X',
                                'opsi_c' => 'Ctrl + C',
                                'opsi_d' => 'Ctrl + Z',
                                'opsi_e' => 'Ctrl + P',
                                'jawaban' => 'C',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                            [
                                'pertanyaan' => 'Agar daftar isi dalam dokumen pengolah kata dapat dibuat secara otomatis dan rapi, format judul dan subjudul sebaiknya diatur menggunakan ...',
                                'opsi_a' => 'Bold biasa',
                                'opsi_b' => 'Styles (Heading 1, Heading 2)',
                                'opsi_c' => 'Underline dan spasi ganda',
                                'opsi_d' => 'Bullet and Numbering biasa',
                                'opsi_e' => 'Font size manual',
                                'jawaban' => 'B',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                            [
                                'pertanyaan' => 'Format file dokumen standar yang dirancang agar tata letak dan tampilannya tidak berubah ketika dibuka di berbagai perangkat adalah ...',
                                'opsi_a' => '.txt',
                                'opsi_b' => '.pdf',
                                'opsi_c' => '.docx',
                                'opsi_d' => '.rtf',
                                'opsi_e' => '.bat',
                                'jawaban' => 'B',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                        ],
                    ],
                    // POST-TEST BAB 3 (Tingkat: Berjenjang Mudah -> Sedang -> Sulit)
                    [
                        'id_kuis' => 'KUIS-TIK-B3-POST',
                        'judul' => 'Post-test Bab 3: Integrasi Aplikasi Perkantoran',
                        'deskripsi' => 'Evaluasi penerapan integrasi data antar aplikasi pengolah kata, angka, dan presentasi.',
                        'kategori' => 'post-test',
                        'tingkat_kesulitan' => 'sedang',
                        'alokasi_waktu' => 15,
                        'soal' => [
                            // Tingkat Mudah
                            [
                                'pertanyaan' => 'Dua dokumen utama yang dibutuhkan saat menjalankan proses pembuatan surat dengan Mail Merge adalah ...',
                                'opsi_a' => 'Dokumen gambar dan dokumen suara',
                                'opsi_b' => 'Dokumen utama (template) dan sumber data (data source)',
                                'opsi_c' => 'Dokumen presentasi dan berkas video',
                                'opsi_d' => 'File installer dan file konfigurasi',
                                'opsi_e' => 'Dokumen PDF dan dokumen scan',
                                'jawaban' => 'B',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                            [
                                'pertanyaan' => 'Singkatan OLE dalam konteks integrasi konten aplikasi perkantoran adalah ...',
                                'opsi_a' => 'Online Learning Environment',
                                'opsi_b' => 'Object Linking and Embedding',
                                'opsi_c' => 'Output Line Evaluation',
                                'opsi_d' => 'Open Layout Element',
                                'opsi_e' => 'Operating Level Execution',
                                'jawaban' => 'B',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                            // Tingkat Sedang
                            [
                                'pertanyaan' => 'Andi menyalin tabel data dari Excel ke Word dengan opsi "Paste Link". Jika Andi mengubah nilai angka pada file Excel sumber, apa yang akan terjadi pada dokumen Word?',
                                'opsi_a' => 'Dokumen Word akan rusak (corrupt)',
                                'opsi_b' => 'Tabel di Word otomatis ikut terbarui menyesuaikan nilai di Excel',
                                'opsi_c' => 'Tabel di Word tidak berubah sama sekali sampai dihapus dan disalin ulang',
                                'opsi_d' => 'File Excel akan terhapus otomatis',
                                'opsi_e' => 'Tabel di Word berubah format menjadi gambar statis',
                                'jawaban' => 'B',
                                'tingkat_kesulitan' => 'sedang',
                            ],
                            [
                                'pertanyaan' => 'Perbedaan mendasar antara teknik "Embedding" dan "Linking" pada integrasi konten adalah ...',
                                'opsi_a' => 'Embedding menyimpan objek secara mandiri di dalam berkas tujuan, sedangkan Linking hanya menyimpan rujukan ke berkas sumber',
                                'opsi_b' => 'Linking membuat ukuran file tujuan menjadi jauh lebih besar dibanding Embedding',
                                'opsi_c' => 'Embedding hanya dapat digunakan pada teks, sedangkan Linking hanya untuk grafik',
                                'opsi_d' => 'Embedding memerlukan koneksi internet, sedangkan Linking bersifat offline',
                                'opsi_e' => 'Linking otomatis menghapus data sumber setelah ditempel',
                                'jawaban' => 'A',
                                'tingkat_kesulitan' => 'sedang',
                            ],
                            // Tingkat Sulit (HOTS)
                            [
                                'pertanyaan' => 'Panitia ujian sekolah ingin mencetak 500 kartu peserta ujian lengkap dengan foto, barcode nomor ujian, dan ruang kelas yang dinamis dari basis data spreadsheet. Masalah yang sering terjadi adalah foto peserta tidak muncul saat Mail Merge digabungkan. Solusi teknis yang paling tepat menurut panduan integrasi tingkat lanjut adalah ...',
                                'opsi_a' => 'Menempelkan foto secara manual satu per satu ke 500 halaman',
                                'opsi_b' => 'Menyimpan path direktori foto pada spreadsheet dan memanfaatkan field code { INCLUDEPICTURE } yang digabungkan dengan merge field',
                                'opsi_c' => 'Mengubah seluruh file spreadsheet menjadi berkas PDF terlebih dahulu',
                                'opsi_d' => 'Menonaktifkan proteksi sistem operasi agar foto terdeteksi otomatis',
                                'opsi_e' => 'Menghapus barcode agar ukuran foto sesuai dengan margin',
                                'jawaban' => 'B',
                                'tingkat_kesulitan' => 'sulit',
                            ],
                        ],
                    ],
                ],
            ],

            // =========================================================================
            // BAB 4: SISTEM KOMPUTER
            // =========================================================================
            [
                'id_materi' => 'MAT-TIK-X-B4',
                'judul' => 'Sistem Komputer: Perangkat Keras, Perangkat Lunak, dan Interaksi',
                'bab' => 'Bab 4',
                'kelas' => 'X',
                'deskripsi' => "Mempelajari komponen arsitektur komputer (von Neumann), siklus pemrosesan data (Fetch-Decode-Execute), perangkat keras, perangkat lunak, dan interaksi manusia-komputer (HCI).\n\nKomponen Pokok:\n1. Perangkat Keras (Hardware): Input Device (Keyboard, Mouse), Processing Unit (CPU, ALU, CU), Storage Device (RAM, ROM, SSD/HDD), dan Output Device (Monitor, Printer).\n2. Perangkat Lunak (Software): Sistem Operasi (Windows, Linux, macOS) yang mengelola sumber daya perangkat keras, dan Perangkat Lunak Aplikasi.\n3. Interaksi Manusia dan Komputer (IMK): Berbagai antarmuka seperti GUI (Graphical User Interface), CLI (Command Line Interface), dan antarmuka berbasis suara/gerak.\n4. Siklus Mesin CPU: Siklus Ambil-Terjemah-Eksekusi (Fetch, Decode, Execute).",
                'isi_materi' => null,
                'link_youtube' => 'https://www.youtube.com/watch?v=AkFi90lZmXA',
                'kuis' => [
                    // PRE-TEST BAB 4 (Tingkat: Mudah)
                    [
                        'id_kuis' => 'KUIS-TIK-B4-PRE',
                        'judul' => 'Pre-test Bab 4: Sistem Komputer',
                        'deskripsi' => 'Uji pemahaman dasar mengenai komponen utama komputer dan cara kerjanya.',
                        'kategori' => 'pre-test',
                        'tingkat_kesulitan' => 'mudah',
                        'alokasi_waktu' => 10,
                        'soal' => [
                            [
                                'pertanyaan' => 'Komponen perangkat keras komputer yang sering disebut sebagai "otak komputer" karena bertugas memproses semua instruksi adalah ...',
                                'opsi_a' => 'RAM',
                                'opsi_b' => 'Hard Disk',
                                'opsi_c' => 'CPU (Central Processing Unit)',
                                'opsi_d' => 'Power Supply',
                                'opsi_e' => 'VGA Card',
                                'jawaban' => 'C',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                            [
                                'pertanyaan' => 'Memori utama komputer yang bersifat sementara (volatile), di mana datanya akan hilang jika aliran listrik terputus adalah ...',
                                'opsi_a' => 'ROM',
                                'opsi_b' => 'RAM',
                                'opsi_c' => 'Flashdisk',
                                'opsi_d' => 'Hard Disk Drive',
                                'opsi_e' => 'Solid State Drive',
                                'jawaban' => 'B',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                            [
                                'pertanyaan' => 'Manakah di bawah ini yang seluruhnya tergolong sebagai perangkat masukan (input device)?',
                                'opsi_a' => 'Monitor, Printer, Speaker',
                                'opsi_b' => 'Keyboard, Mouse, Scanner',
                                'opsi_c' => 'Proyektor, Harddisk, RAM',
                                'opsi_d' => 'CPU, Motherboard, VGA',
                                'opsi_e' => 'Webcam, Speaker, Plotter',
                                'jawaban' => 'B',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                            [
                                'pertanyaan' => 'Perangkat lunak yang berfungsi sebagai perantara antara pengguna, aplikasi, dan perangkat keras komputer disebut ...',
                                'opsi_a' => 'Sistem Operasi (Operating System)',
                                'opsi_b' => 'Antivirus',
                                'opsi_c' => 'Compiler',
                                'opsi_d' => 'Web Browser',
                                'opsi_e' => 'Media Player',
                                'jawaban' => 'A',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                            [
                                'pertanyaan' => 'Antarmuka pengguna yang memanfaatkan elemen grafis seperti ikon, tombol, dan jendela visual disebut ...',
                                'opsi_a' => 'CLI (Command Line Interface)',
                                'opsi_b' => 'GUI (Graphical User Interface)',
                                'opsi_c' => 'API (Application Programming Interface)',
                                'opsi_d' => 'BIOS',
                                'opsi_e' => 'Terminal Prompt',
                                'jawaban' => 'B',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                        ],
                    ],
                    // POST-TEST BAB 4 (Tingkat: Berjenjang Mudah -> Sedang -> Sulit)
                    [
                        'id_kuis' => 'KUIS-TIK-B4-POST',
                        'judul' => 'Post-test Bab 4: Sistem Komputer',
                        'deskripsi' => 'Uji pemahaman komprehensif arsitektur komputer, mekanisme memori, dan interaksi sistem.',
                        'kategori' => 'post-test',
                        'tingkat_kesulitan' => 'sedang',
                        'alokasi_waktu' => 15,
                        'soal' => [
                            // Tingkat Mudah
                            [
                                'pertanyaan' => 'Unit pada prosesor (CPU) yang bertanggung jawab melakukan perhitungan aritmatika dan operasi logika biner adalah ...',
                                'opsi_a' => 'CU (Control Unit)',
                                'opsi_b' => 'ALU (Arithmetic Logic Unit)',
                                'opsi_c' => 'Register',
                                'opsi_d' => 'L3 Cache',
                                'opsi_e' => 'Bus Interface',
                                'jawaban' => 'B',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                            [
                                'pertanyaan' => 'Contoh sistem operasi sumber terbuka (open source) yang bebas dikembangkan dan dimodifikasi adalah ...',
                                'opsi_a' => 'Microsoft Windows 11',
                                'opsi_b' => 'Apple macOS',
                                'opsi_c' => 'Linux Ubuntu',
                                'opsi_d' => 'Apple iOS',
                                'opsi_e' => 'MS-DOS berlisensi komersial',
                                'jawaban' => 'C',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                            // Tingkat Sedang
                            [
                                'pertanyaan' => 'Tiga tahapan utama yang dilakukan prosesor secara berulang-ulang dalam siklus instruksi mesin adalah ...',
                                'opsi_a' => 'Booting - Formatting - Defragmenting',
                                'opsi_b' => 'Fetch (Ambil) - Decode (Terjemah) - Execute (Eksekusi)',
                                'opsi_c' => 'Compress - Encrypt - Upload',
                                'opsi_d' => 'Input - Print - Scan',
                                'opsi_e' => 'Read - Overwrite - Delete',
                                'jawaban' => 'B',
                                'tingkat_kesulitan' => 'sedang',
                            ],
                            [
                                'pertanyaan' => 'Mengapa memori Cache pada prosesor dibuat dengan kapasitas yang relatif kecil tetapi memiliki kecepatan transfer data tertinggi di antara jenis memori lainnya?',
                                'opsi_a' => 'Karena biaya produksi silikon statis (SRAM) sangat mahal dan letaknya menyatu di dalam inti CPU',
                                'opsi_b' => 'Agar panas yang dihasilkan prosesor tetap dingin',
                                'opsi_c' => 'Karena Cache hanya berfungsi saat komputer dimatikan',
                                'opsi_d' => 'Untuk membatasi jumlah aplikasi yang boleh diinstal pengguna',
                                'opsi_e' => 'Karena Cache hanya menyimpan file dokumen berformat teks',
                                'jawaban' => 'A',
                                'tingkat_kesulitan' => 'sedang',
                            ],
                            // Tingkat Sulit (HOTS)
                            [
                                'pertanyaan' => 'Sebuah komputer server mengalami penurunan performa drastis (*thrashing*), di mana aktivitas lampu indikator media penyimpanan (SSD/HDD) menyala terus-menerus dengan penggunaan CPU rendah. Berdasarkan konsep manajemen memori sistem operasi, penyebab utama fenomena ini dan tindakan korektif yang paling tepat adalah ...',
                                'opsi_a' => 'Terjadi overheat pada GPU; tindakan tepat adalah mengganti pasta pendingin prosesor grafis',
                                'opsi_b' => 'Kapasitas RAM fisik tidak mencukupi sehingga sistem operasi terlalu sering menukar data antara RAM dan Virtual Memory (paging); tindakan tepat adalah menambah kapasitas modul RAM',
                                'opsi_c' => 'Koneksi kabel LAN putus; tindakan tepat adalah memperbarui driver kartu jaringan',
                                'opsi_d' => 'Kapasitas monitor terlalu besar; tindakan tepat adalah menurunkan resolusi layar monitor',
                                'opsi_e' => 'Format partisi harddisk salah; tindakan tepat adalah membersihkan debu kipas pendingin',
                                'jawaban' => 'B',
                                'tingkat_kesulitan' => 'sulit',
                            ],
                        ],
                    ],
                ],
            ],

            // =========================================================================
            // BAB 5: JARINGAN KOMPUTER DAN INTERNET
            // =========================================================================
            [
                'id_materi' => 'MAT-TIK-X-B5',
                'judul' => 'Jaringan Komputer dan Internet: Konektivitas dan Keamanan Data',
                'bab' => 'Bab 5',
                'kelas' => 'X',
                'deskripsi' => "Mempelajari klasifikasi jaringan komputer (LAN, MAN, WAN), perangkat keras jaringan, protokol komunikasi (TCP/IP), mekanisme routing, dan proteksi data di internet.\n\nTopik Utama:\n1. Klasifikasi Jaringan Berdasarkan Cakupan: LAN (Local Area Network), MAN (Metropolitan Area Network), WAN (Wide Area Network), dan Internet.\n2. Topologi Jaringan: Bus, Star, Ring, Mesh, dan Tree beserta kelebihan dan kekurangannya.\n3. Perangkat Jaringan: Router (mengarahkan paket data antarjaringan berbeda), Switch (menghubungkan perangkat di jaringan lokal secara cerdas), Access Point (koneksi nirkabel).\n4. Keamanan Jaringan: Enkripsi data (HTTPS/SSL), bahaya phishing, malware, dan pentingnya penggunaan kata sandi yang kuat.",
                'isi_materi' => null,
                'link_youtube' => 'https://www.youtube.com/watch?v=0Q7bY8b3KjQ',
                'kuis' => [
                    // PRE-TEST BAB 5 (Tingkat: Mudah)
                    [
                        'id_kuis' => 'KUIS-TIK-B5-PRE',
                        'judul' => 'Pre-test Bab 5: Jaringan Komputer dan Internet',
                        'deskripsi' => 'Uji pemahaman dasar mengenai tipe jaringan, perangkat komunikasi, dan koneksi internet.',
                        'kategori' => 'pre-test',
                        'tingkat_kesulitan' => 'mudah',
                        'alokasi_waktu' => 10,
                        'soal' => [
                            [
                                'pertanyaan' => 'Jaringan komputer yang menghubungkan komputer dalam ruangan atau gedung yang sama, seperti lab komputer sekolah, disebut ...',
                                'opsi_a' => 'LAN (Local Area Network)',
                                'opsi_b' => 'MAN (Metropolitan Area Network)',
                                'opsi_c' => 'WAN (Wide Area Network)',
                                'opsi_d' => 'Internet',
                                'opsi_e' => 'PAN antarbenua',
                                'jawaban' => 'A',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                            [
                                'pertanyaan' => 'Topologi jaringan di mana semua perangkat komputer terhubung langsung ke satu perangkat konsentrator pusat (Switch/Hub) disebut ...',
                                'opsi_a' => 'Topologi Bus',
                                'opsi_b' => 'Topologi Star',
                                'opsi_c' => 'Topologi Ring',
                                'opsi_d' => 'Topologi Mesh',
                                'opsi_e' => 'Topologi Peer-to-Peer',
                                'jawaban' => 'B',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                            [
                                'pertanyaan' => 'Perangkat jaringan yang bertugas meneruskan dan mengarahkan paket data antara dua atau lebih jaringan yang berbeda alamat IP adalah ...',
                                'opsi_a' => 'Router',
                                'opsi_b' => 'Monitor',
                                'opsi_c' => 'Sound Card',
                                'opsi_d' => 'VGA Card',
                                'opsi_e' => 'RAM',
                                'jawaban' => 'A',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                            [
                                'pertanyaan' => 'Protokol pada peramban web (browser) yang menandakan bahwa komunikasi data antara pengguna dan situs web telah terenkripsi secara aman adalah ...',
                                'opsi_a' => 'HTTP',
                                'opsi_b' => 'HTTPS',
                                'opsi_c' => 'FTP',
                                'opsi_d' => 'SMTP',
                                'opsi_e' => 'Telnet',
                                'jawaban' => 'B',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                            [
                                'pertanyaan' => 'Nomor identitas unik yang diberikan kepada setiap perangkat yang terhubung dalam jaringan komputer berbasis protokol TCP/IP disebut ...',
                                'opsi_a' => 'Serial Number',
                                'opsi_b' => 'IP Address',
                                'opsi_c' => 'Barcode',
                                'opsi_d' => 'ZIP Code',
                                'opsi_e' => 'IMEI Layar',
                                'jawaban' => 'B',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                        ],
                    ],
                    // POST-TEST BAB 5 (Tingkat: Berjenjang Mudah -> Sedang -> Sulit)
                    [
                        'id_kuis' => 'KUIS-TIK-B5-POST',
                        'judul' => 'Post-test Bab 5: Jaringan Komputer dan Internet',
                        'deskripsi' => 'Uji pemahaman komprehensif arsitektur topologi jaringan, pengalamatan IP, dan teknik proteksi data.',
                        'kategori' => 'post-test',
                        'tingkat_kesulitan' => 'sedang',
                        'alokasi_waktu' => 15,
                        'soal' => [
                            // Tingkat Mudah
                            [
                                'pertanyaan' => 'Kelebihan utama topologi Star dibandingkan topologi Bus konvensional adalah ...',
                                'opsi_a' => 'Tidak membutuhkan kabel sama sekali',
                                'opsi_b' => 'Jika satu kabel komputer klien putus, jaringan komputer lainnya tetap dapat beroperasi normal',
                                'opsi_c' => 'Biaya instalasi selalu paling murah',
                                'opsi_d' => 'Tidak membutuhkan perangkat switch atau hub',
                                'opsi_e' => 'Kecepatan internet otomatis naik 100 kali lipat',
                                'jawaban' => 'B',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                            [
                                'pertanyaan' => 'Metode penipuan digital di mana pelaku memalsukan halaman web login bank atau media sosial untuk mencuri kata sandi pengguna disebut ...',
                                'opsi_a' => 'Phishing',
                                'opsi_b' => 'Dekomposisi',
                                'opsi_c' => 'Sorting',
                                'opsi_d' => 'Tethering',
                                'opsi_e' => 'Overclocking',
                                'jawaban' => 'A',
                                'tingkat_kesulitan' => 'mudah',
                            ],
                            // Tingkat Sedang
                            [
                                'pertanyaan' => 'Perbedaan mendasar antara perangkat Switch dan Hub pada jaringan lokal adalah ...',
                                'opsi_a' => 'Hub memeriksa alamat MAC tujuan sebelum mengirim data, sedangkan Switch membroadcast data ke semua port',
                                'opsi_b' => 'Switch membaca MAC address tujuan dan hanya meneruskan frame ke port tujuan, sedangkan Hub meneruskan sinyal ke seluruh port tanpa filter',
                                'opsi_c' => 'Hub hanya bisa digunakan untuk jaringan nirkabel (wireless)',
                                'opsi_d' => 'Switch tidak memerlukan sumber daya listrik sama sekali',
                                'opsi_e' => 'Hub selalu memiliki kecepatan transfer lebih tinggi daripada Switch',
                                'jawaban' => 'B',
                                'tingkat_kesulitan' => 'sedang',
                            ],
                            [
                                'pertanyaan' => 'Dua komputer berada pada jaringan yang sama dengan subnet mask 255.255.255.0. Komputer A memiliki IP 192.168.1.10. Manakah di bawah ini alamat IP yang valid agar Komputer B dapat berkomunikasi langsung tanpa router?',
                                'opsi_a' => '192.168.2.10',
                                'opsi_b' => '192.168.1.25',
                                'opsi_c' => '10.0.0.1',
                                'opsi_d' => '172.16.1.10',
                                'opsi_e' => '192.168.1.255',
                                'jawaban' => 'B',
                                'tingkat_kesulitan' => 'sedang',
                            ],
                            // Tingkat Sulit (HOTS)
                            [
                                'pertanyaan' => 'Sebuah instansi perbankan ingin menghubungkan 6 kantor cabang utama dengan tingkat keandalan tertinggi (fault tolerance maksimal), di mana kegagalan beberapa jalur kabel tidak boleh memutus komunikasi antar kantor manapun. Jika instansi menggunakan topologi Full Mesh, berapa total jumlah jalur kabel fisik langsung yang harus dipasang antar ke-6 cabang tersebut?',
                                'opsi_a' => '6 jalur',
                                'opsi_b' => '12 jalur',
                                'opsi_c' => '15 jalur',
                                'opsi_d' => '30 jalur',
                                'opsi_e' => '36 jalur',
                                'jawaban' => 'C',
                                'tingkat_kesulitan' => 'sulit',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        // 6. Jalankan Penyimpanan Materi, Kuis, Soal, dan Relasi Kelas
        foreach ($paketPembelajaran as $itemMateri) {
            $materi = Materi::firstOrCreate(
                ['id_materi' => $itemMateri['id_materi']],
                [
                    'judul' => $itemMateri['judul'],
                    'bab' => $itemMateri['bab'],
                    'kelas' => $itemMateri['kelas'],
                    'id_mapel' => $mapel->id_mapel,
                    'deskripsi' => $itemMateri['deskripsi'],
                    'isi_materi' => $itemMateri['isi_materi'],
                    'link_youtube' => $itemMateri['link_youtube'],
                    'id_user' => $guru->id_user,
                    'is_aktif' => true,
                ]
            );

            foreach ($itemMateri['kuis'] as $dataKuis) {
                $kuis = Kuis::firstOrCreate(
                    ['id_kuis' => $dataKuis['id_kuis']],
                    [
                        'judul' => $dataKuis['judul'],
                        'deskripsi' => $dataKuis['deskripsi'],
                        'kategori' => $dataKuis['kategori'],
                        'tingkat_kesulitan' => $dataKuis['tingkat_kesulitan'],
                        'id_mapel' => $mapel->id_mapel,
                        'id_tahun_ajaran' => $tahunAjaran->id_tahun_ajaran,
                        'alokasi_waktu' => $dataKuis['alokasi_waktu'],
                        'kkm' => 75,
                        'waktu_mulai' => null,
                        'waktu_selesai' => null,
                        'id_user' => $guru->id_user,
                        'status_publikasi' => 'terbit',
                        'id_publisher' => $guru->id_user,
                        'is_aktif' => true,
                        'tampil_jawaban' => 'setelah_submit',
                    ]
                );

                // Tautkan kuis ke materi (tp_materi_kuis)
                $materi->kuis()->syncWithoutDetaching([$kuis->id_kuis]);

                // Tautkan kuis ke kelas-kelas yang aktif (tp_kuis_kelas)
                if (Schema::hasTable('tp_kuis_kelas')) {
                    foreach ($kelasIds as $idKelas) {
                        $ignore = DB::getDriverName() === 'sqlite' ? 'OR IGNORE' : 'IGNORE';
                        DB::statement("INSERT {$ignore} INTO tp_kuis_kelas (id_kuis, id_kelas) VALUES (?, ?)", [$kuis->id_kuis, $idKelas]);
                    }
                }

                // Masukkan butir-butir soal
                foreach ($dataKuis['soal'] as $index => $dataSoal) {
                    $idSoal = $dataKuis['id_kuis'].'-S'.str_pad($index + 1, 2, '0', STR_PAD_LEFT);

                    Soal::firstOrCreate(
                        ['id_soal' => $idSoal],
                        [
                            'id_kuis' => $kuis->id_kuis,
                            'pertanyaan' => $dataSoal['pertanyaan'],
                            'tipe' => 'pilihan_ganda',
                            'opsi_a' => $dataSoal['opsi_a'],
                            'opsi_b' => $dataSoal['opsi_b'],
                            'opsi_c' => $dataSoal['opsi_c'],
                            'opsi_d' => $dataSoal['opsi_d'],
                            'opsi_e' => $dataSoal['opsi_e'],
                            'jawaban' => $dataSoal['jawaban'],
                            'bobot' => 20,
                            'tingkat_kesulitan' => $dataSoal['tingkat_kesulitan'],
                        ]
                    );
                }
            }
        }
    }
}
