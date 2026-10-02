<?php

namespace Database\Seeders;

use App\Models\Kuis;
use App\Models\Materi;
use App\Models\Soal;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['id_user' => 'U001'],
            [
                'nomor_induk' => '198001',
                'nama_lengkap' => 'Guru Demo',
                'role' => 'guru',
                'kelas' => null,
                'email' => 'guru@example.com',
                'password' => 'password123',
            ]
        );

        User::firstOrCreate(
            ['id_user' => 'U002'],
            [
                'nomor_induk' => '25001',
                'nama_lengkap' => 'Siswa Demo',
                'role' => 'siswa',
                'kelas' => 'X',
                'email' => 'siswa@example.com',
                'password' => 'password123',
            ]
        );

        User::firstOrCreate(
            ['id_user' => 'U003'],
            [
                'nomor_induk' => 'OP001',
                'nama_lengkap' => 'Operator Demo',
                'role' => 'operator',
                'kelas' => null,
                'email' => 'operator@example.com',
                'password' => 'password123',
            ]
        );

        $materi1 = Materi::firstOrCreate(
            ['id_materi' => 'M001'],
            [
                'judul' => 'Pengenalan HTML',
                'bab' => '1',
                'kelas' => 'X',
                'deskripsi' => 'Materi dasar tentang struktur halaman HTML.',
                'isi_materi' => 'HTML adalah bahasa markup untuk struktur halaman web.',
                'link_youtube' => 'https://www.youtube.com/watch?v=ok-plXXHlWw',
                'id_user' => 'U001',
            ]
        );

        $materi2 = Materi::firstOrCreate(
            ['id_materi' => 'M002'],
            [
                'judul' => 'CSS Dasar',
                'bab' => '2',
                'kelas' => 'X',
                'deskripsi' => 'Materi dasar tentang styling elemen halaman.',
                'isi_materi' => 'CSS digunakan untuk mengatur tampilan dan layout halaman web.',
                'link_youtube' => 'https://www.youtube.com/watch?v=1Rs2ND1ryYc',
                'id_user' => 'U001',
            ]
        );

        $materi3 = Materi::firstOrCreate(
            ['id_materi' => 'M003'],
            [
                'judul' => 'JavaScript Fundamental',
                'bab' => '3',
                'kelas' => 'XI',
                'deskripsi' => 'Dasar-dasar JavaScript untuk interaksi halaman.',
                'isi_materi' => 'JavaScript membuat halaman web menjadi interaktif.',
                'link_youtube' => 'https://www.youtube.com/watch?v=W6NZfCO5SIk',
                'id_user' => 'U001',
            ]
        );

        $kuis1 = Kuis::firstOrCreate(
            ['id_kuis' => 'K001'],
            [
                'judul' => 'Kuis HTML Dasar',
                'deskripsi' => 'Kuis untuk menguji pemahaman dasar struktur HTML.',
                'kategori' => 'kuis harian',
                'alokasi_waktu' => 30,
                'kkm' => 75,
                'waktu_mulai' => null,
                'waktu_selesai' => null,
                'id_user' => 'U001',
                'status_publikasi' => 'terbit',
                'id_publisher' => 'U001',
                'is_aktif' => true,
            ]
        );

        $kuis2 = Kuis::firstOrCreate(
            ['id_kuis' => 'K002'],
            [
                'judul' => 'Kuis CSS & Layout',
                'deskripsi' => 'Latihan untuk memahami selector, warna, dan layout dengan CSS.',
                'kategori' => 'post-test',
                'alokasi_waktu' => 45,
                'kkm' => 80,
                'waktu_mulai' => null,
                'waktu_selesai' => null,
                'id_user' => 'U001',
                'status_publikasi' => 'draft',
                'id_publisher' => null,
                'is_aktif' => true,
            ]
        );

        $kuis3 = Kuis::firstOrCreate(
            ['id_kuis' => 'K003'],
            [
                'judul' => 'Ulangan Harian JavaScript',
                'deskripsi' => 'Ulangan untuk materi JavaScript dasar.',
                'kategori' => 'ulangan',
                'alokasi_waktu' => 60,
                'kkm' => 70,
                'waktu_mulai' => now()->subHour(),
                'waktu_selesai' => now()->addHour(),
                'id_user' => 'U001',
                'status_publikasi' => 'terbit',
                'id_publisher' => 'U001',
                'is_aktif' => true,
            ]
        );

        $kuis1->materi()->syncWithoutDetaching([$materi1->id_materi, $materi2->id_materi]);
        $kuis2->materi()->syncWithoutDetaching([$materi2->id_materi]);
        $kuis3->materi()->syncWithoutDetaching([$materi3->id_materi]);

        Soal::firstOrCreate(
            ['id_soal' => 'S001'],
            [
                'id_kuis' => 'K001',
                'pertanyaan' => 'Tag HTML yang digunakan untuk membuat judul utama adalah?',
                'opsi_a' => '<h1>',
                'opsi_b' => '<p>',
                'opsi_c' => '<div>',
                'opsi_d' => '<span>',
                'opsi_e' => null,
                'jawaban' => 'A',
                'bobot' => 50,
                'tingkat_kesulitan' => 'mudah',
            ]
        );

        Soal::firstOrCreate(
            ['id_soal' => 'S002'],
            [
                'id_kuis' => 'K001',
                'pertanyaan' => 'Atribut HTML untuk menentukan alamat tautan adalah?',
                'opsi_a' => 'src',
                'opsi_b' => 'href',
                'opsi_c' => 'alt',
                'opsi_d' => 'class',
                'opsi_e' => null,
                'jawaban' => 'B',
                'bobot' => 50,
                'tingkat_kesulitan' => 'mudah',
            ]
        );

        Soal::firstOrCreate(
            ['id_soal' => 'S003'],
            [
                'id_kuis' => 'K002',
                'pertanyaan' => 'Properti CSS untuk mengubah warna teks adalah?',
                'opsi_a' => 'background-color',
                'opsi_b' => 'font-size',
                'opsi_c' => 'color',
                'opsi_d' => 'text-style',
                'opsi_e' => null,
                'jawaban' => 'C',
                'bobot' => 50,
                'tingkat_kesulitan' => 'mudah',
            ]
        );

        Soal::firstOrCreate(
            ['id_soal' => 'S004'],
            [
                'id_kuis' => 'K002',
                'pertanyaan' => 'Properti CSS yang digunakan untuk mengatur jarak dalam elemen adalah?',
                'opsi_a' => 'margin',
                'opsi_b' => 'padding',
                'opsi_c' => 'border',
                'opsi_d' => 'display',
                'opsi_e' => null,
                'jawaban' => 'B',
                'bobot' => 50,
                'tingkat_kesulitan' => 'sedang',
            ]
        );

        Soal::firstOrCreate(
            ['id_soal' => 'S005'],
            [
                'id_kuis' => 'K003',
                'pertanyaan' => 'Kata kunci untuk mendeklarasikan variabel yang nilainya dapat berubah adalah?',
                'opsi_a' => 'const',
                'opsi_b' => 'static',
                'opsi_c' => 'let',
                'opsi_d' => 'fixed',
                'opsi_e' => null,
                'jawaban' => 'C',
                'bobot' => 50,
                'tingkat_kesulitan' => 'mudah',
            ]
        );

        Soal::firstOrCreate(
            ['id_soal' => 'S006'],
            [
                'id_kuis' => 'K003',
                'pertanyaan' => 'Method JavaScript untuk menampilkan pesan ke console adalah?',
                'opsi_a' => 'console.log()',
                'opsi_b' => 'print.console()',
                'opsi_c' => 'message.log()',
                'opsi_d' => 'write.console()',
                'opsi_e' => null,
                'jawaban' => 'A',
                'bobot' => 50,
                'tingkat_kesulitan' => 'mudah',
            ]
        );
    }
}
