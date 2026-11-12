<?php

namespace Database\Seeders;

use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\TahunAjaran;
use Illuminate\Database\Seeder;

class Bab1TIKKuisSeeder extends Seeder
{
    public function run(): void
    {
        $mapel = Mapel::where('kode_mapel', 'TIK')
            ->where('nama_mapel', 'Teknologi Informasi dan Komunikasi')
            ->firstOrFail();

        $materi = Materi::where('id_mapel', $mapel->id_mapel)
            ->whereRaw('LOWER(TRIM(bab)) IN (?, ?)', ['bab 1', '1'])
            ->orderBy('id_materi')
            ->firstOrFail();

        $tahunAjaran = TahunAjaran::where('is_aktif', true)->firstOrFail();

        $paketKuis = [
            [
                'id_kuis' => 'DUMMY-TIK-B1-PRE',
                'judul' => 'Pre-test Bab 1: Dasar Jaringan Komputer',
                'deskripsi' => 'Tes awal pemahaman dasar jaringan komputer dan topologi sebelum mempelajari Bab 1.',
                'kategori' => 'pre-test',
                'tingkat_kesulitan' => 'mudah',
                'alokasi_waktu' => 10,
                'soal' => [
                    [
                        'pertanyaan' => 'Apa tujuan utama menghubungkan beberapa komputer dalam sebuah jaringan?',
                        'opsi_a' => 'Berbagi data dan sumber daya',
                        'opsi_b' => 'Menghilangkan kebutuhan sistem operasi',
                        'opsi_c' => 'Membuat setiap komputer bekerja tanpa listrik',
                        'opsi_d' => 'Mengubah semua file menjadi gambar',
                        'opsi_e' => 'Menghapus seluruh virus secara otomatis',
                        'jawaban' => 'A',
                        'tingkat_kesulitan' => 'mudah',
                    ],
                    [
                        'pertanyaan' => 'Jaringan komputer yang mencakup area terbatas seperti laboratorium sekolah disebut ...',
                        'opsi_a' => 'WAN',
                        'opsi_b' => 'MAN',
                        'opsi_c' => 'LAN',
                        'opsi_d' => 'Internet',
                        'opsi_e' => 'PAN antarprovinsi',
                        'jawaban' => 'C',
                        'tingkat_kesulitan' => 'mudah',
                    ],
                    [
                        'pertanyaan' => 'Perangkat yang umumnya menjadi titik pusat pada topologi Star adalah ...',
                        'opsi_a' => 'Printer',
                        'opsi_b' => 'Scanner',
                        'opsi_c' => 'Modem analog',
                        'opsi_d' => 'Switch',
                        'opsi_e' => 'Proyektor',
                        'jawaban' => 'D',
                        'tingkat_kesulitan' => 'mudah',
                    ],
                    [
                        'pertanyaan' => 'Perangkat jaringan yang menghubungkan jaringan berbeda dan meneruskan paket berdasarkan alamat IP adalah ...',
                        'opsi_a' => 'Keyboard',
                        'opsi_b' => 'Hub',
                        'opsi_c' => 'Router',
                        'opsi_d' => 'Monitor',
                        'opsi_e' => 'Kartu suara',
                        'jawaban' => 'C',
                        'tingkat_kesulitan' => 'mudah',
                    ],
                    [
                        'pertanyaan' => 'Topologi yang menggunakan satu kabel utama sebagai jalur komunikasi bersama disebut ...',
                        'opsi_a' => 'Bus',
                        'opsi_b' => 'Star',
                        'opsi_c' => 'Mesh',
                        'opsi_d' => 'Tree',
                        'opsi_e' => 'Hybrid Star',
                        'jawaban' => 'A',
                        'tingkat_kesulitan' => 'mudah',
                    ],
                ],
            ],
            [
                'id_kuis' => 'DUMMY-TIK-B1-POST',
                'judul' => 'Post-test Bab 1: Dasar Jaringan Komputer',
                'deskripsi' => 'Evaluasi pemahaman konsep jaringan komputer, perangkat jaringan, dan topologi pada Bab 1.',
                'kategori' => 'post-test',
                'tingkat_kesulitan' => 'sedang',
                'alokasi_waktu' => 15,
                'soal' => [
                    [
                        'pertanyaan' => 'Jaringan yang menghubungkan beberapa lokasi dalam satu wilayah metropolitan umumnya disebut ...',
                        'opsi_a' => 'LAN',
                        'opsi_b' => 'PAN',
                        'opsi_c' => 'SAN',
                        'opsi_d' => 'MAN',
                        'opsi_e' => 'CAN',
                        'jawaban' => 'D',
                        'tingkat_kesulitan' => 'mudah',
                    ],
                    [
                        'pertanyaan' => 'Pada topologi Star, apa dampak yang biasanya terjadi jika kabel dari satu komputer ke switch terputus?',
                        'opsi_a' => 'Komputer tersebut terputus, sedangkan komputer lain tetap dapat berkomunikasi',
                        'opsi_b' => 'Seluruh jaringan selalu berhenti bekerja',
                        'opsi_c' => 'Router menghapus semua alamat IP',
                        'opsi_d' => 'Semua komputer berubah menjadi topologi Bus',
                        'opsi_e' => 'Koneksi internet otomatis menjadi lebih cepat',
                        'jawaban' => 'A',
                        'tingkat_kesulitan' => 'sedang',
                    ],
                    [
                        'pertanyaan' => 'Lima perangkat dihubungkan dengan topologi Full Mesh. Berapa jumlah koneksi langsung yang diperlukan?',
                        'opsi_a' => '5 koneksi',
                        'opsi_b' => '8 koneksi',
                        'opsi_c' => '10 koneksi',
                        'opsi_d' => '15 koneksi',
                        'opsi_e' => '20 koneksi',
                        'jawaban' => 'C',
                        'tingkat_kesulitan' => 'sedang',
                    ],
                    [
                        'pertanyaan' => 'Kelebihan utama topologi Mesh dibandingkan topologi Bus adalah ...',
                        'opsi_a' => 'Tidak memerlukan kabel sama sekali',
                        'opsi_b' => 'Hanya membutuhkan satu perangkat pusat',
                        'opsi_c' => 'Biaya pemasangan selalu paling murah',
                        'opsi_d' => 'Semua perangkat berbagi satu jalur utama',
                        'opsi_e' => 'Memiliki jalur alternatif sehingga lebih tahan terhadap putusnya satu koneksi',
                        'jawaban' => 'E',
                        'tingkat_kesulitan' => 'sedang',
                    ],
                    [
                        'pertanyaan' => 'Sekolah ingin menghubungkan jaringan beberapa gedung yang berjarak jauh antar kota. Jenis jaringan yang paling sesuai adalah ...',
                        'opsi_a' => 'LAN',
                        'opsi_b' => 'WAN',
                        'opsi_c' => 'PAN',
                        'opsi_d' => 'Topologi Bus',
                        'opsi_e' => 'Topologi Ring',
                        'jawaban' => 'B',
                        'tingkat_kesulitan' => 'sedang',
                    ],
                ],
            ],
        ];

        foreach ($paketKuis as $dataKuis) {
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
                    'id_user' => $materi->id_user,
                    'status_publikasi' => 'draft',
                    'id_publisher' => null,
                    'is_aktif' => true,
                    'tampil_jawaban' => 'setelah_submit',
                ]
            );

            $materi->kuis()->syncWithoutDetaching([$kuis->id_kuis]);

            foreach ($dataKuis['soal'] as $index => $dataSoal) {
                $idSoal = $dataKuis['id_kuis'].'-S'.str_pad($index + 1, 2, '0', STR_PAD_LEFT);

                $kuis->soal()->firstOrCreate(
                    ['id_soal' => $idSoal],
                    array_merge($dataSoal, [
                        'tipe' => 'pilihan_ganda',
                        'bobot' => 20,
                    ])
                );
            }
        }
    }
}
