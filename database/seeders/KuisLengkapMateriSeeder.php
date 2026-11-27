<?php

namespace Database\Seeders;

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Materi;
use App\Models\Soal;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class KuisLengkapMateriSeeder extends Seeder
{
    /**
     * Lengkapi kuis (pre-test, post-test, kuis harian, ulangan) beserta soalnya
     * untuk setiap materi aktif yang belum memilikinya.
     *
     * Bersifat non-destruktif & idempoten: kuis aktif yang sudah ada tidak
     * ditimpa atau diduplikasi.
     */
    public function run(): void
    {
        $tahunAjaran = TahunAjaran::where('is_aktif', true)->first();

        if (! $tahunAjaran) {
            $this->command?->warn('Tidak ada tahun ajaran aktif. Seeder dilewati.');

            return;
        }

        $fallbackUser = User::where('role', 'guru')->first()
            ?? User::where('role', 'admin')->first();

        if (! $fallbackUser) {
            $this->command?->warn('Tidak ada user guru/admin. Seeder dilewati.');

            return;
        }

        /** @var Collection<int, Materi> $daftarMateri */
        /** @var \Illuminate\Database\Eloquent\Collection<int, Materi> $daftarMateri */
        $daftarMateri = Materi::aktif()
            ->with(['mapel', 'kelasTerkait'])
            ->orderBy('id_materi')
            ->get();

        $kategoriConfig = [
            'pre-test' => [
                'kode' => 'PRE',
                'label' => 'Pre-test',
                'alokasi_waktu' => 10,
                'tingkat' => 'mudah',
            ],
            'post-test' => [
                'kode' => 'POST',
                'label' => 'Post-test',
                'alokasi_waktu' => 15,
                'tingkat' => 'sedang',
            ],
            'kuis harian' => [
                'kode' => 'KH',
                'label' => 'Kuis Harian',
                'alokasi_waktu' => 15,
                'tingkat' => 'sedang',
            ],
            'ulangan' => [
                'kode' => 'UL',
                'label' => 'Ulangan',
                'alokasi_waktu' => 30,
                'tingkat' => 'sulit',
            ],
        ];

        $kuisDibuat = 0;
        $soalDibuat = 0;

        foreach ($daftarMateri as $materi) {
            /** @var Materi $materi */
            $pembuatId = $materi->id_user ?: $fallbackUser->id_user;
            $kelasIds = $this->tentukanKelasIds($materi, $tahunAjaran->id_tahun_ajaran);
            $judulBersih = str_replace('_', ' ', $materi->judul);

            foreach ($kategoriConfig as $kategori => $cfg) {
                // Lewati jika materi sudah punya kuis aktif di kategori ini
                $sudahAda = $materi->kuis()
                    ->where('is_aktif', true)
                    ->where('kategori', $kategori)
                    ->exists();

                if ($sudahAda) {
                    continue;
                }

                $idKuis = $this->buatIdKuis($materi->id_materi, $cfg['kode']);
                $isUlangan = $kategori === 'ulangan';

                $kuis = Kuis::firstOrCreate(
                    ['id_kuis' => $idKuis],
                    [
                        'judul' => $cfg['label'].': '.$judulBersih,
                        'deskripsi' => $cfg['label'].' untuk materi '.$judulBersih.'.',
                        'kategori' => $kategori,
                        'tingkat_kesulitan' => $cfg['tingkat'],
                        'id_mapel' => $materi->id_mapel,
                        'id_tahun_ajaran' => $tahunAjaran->id_tahun_ajaran,
                        'alokasi_waktu' => $cfg['alokasi_waktu'],
                        'kkm' => 75,
                        'waktu_mulai' => $isUlangan ? now()->subDay() : null,
                        'waktu_selesai' => $isUlangan ? now()->addMonths(6) : null,
                        'id_user' => $pembuatId,
                        'status_publikasi' => 'terbit',
                        'id_publisher' => $pembuatId,
                        'is_aktif' => true,
                        'tampil_jawaban' => 'setelah_submit',
                    ]
                );

                if ($kuis->wasRecentlyCreated) {
                    $kuisDibuat++;
                }

                $materi->kuis()->syncWithoutDetaching([$kuis->id_kuis]);

                if (Schema::hasTable('tp_kuis_kelas') && ! empty($kelasIds)) {
                    $kuis->kelas()->syncWithoutDetaching($kelasIds);
                }

                $daftarSoal = $this->ambilBankSoal($materi, $kategori, $judulBersih);
                $bobotPerSoal = (int) floor(100 / count($daftarSoal));

                foreach ($daftarSoal as $idx => $itemSoal) {
                    $idSoal = $idKuis.'-S'.str_pad((string) ($idx + 1), 2, '0', STR_PAD_LEFT);

                    $soal = Soal::firstOrCreate(
                        ['id_soal' => $idSoal],
                        [
                            'id_kuis' => $kuis->id_kuis,
                            'tipe' => 'pilihan_ganda',
                            'pertanyaan' => $itemSoal['pertanyaan'],
                            'opsi_a' => $itemSoal['opsi_a'],
                            'opsi_b' => $itemSoal['opsi_b'],
                            'opsi_c' => $itemSoal['opsi_c'],
                            'opsi_d' => $itemSoal['opsi_d'],
                            'opsi_e' => $itemSoal['opsi_e'] ?? null,
                            'jawaban' => $itemSoal['jawaban'],
                            'bobot' => $bobotPerSoal,
                            'tingkat_kesulitan' => $cfg['tingkat'],
                        ]
                    );

                    if ($soal->wasRecentlyCreated) {
                        $soalDibuat++;
                    }
                }
            }
        }

        $this->command?->info("KuisLengkapMateriSeeder selesai: {$kuisDibuat} kuis dan {$soalDibuat} soal baru ditambahkan.");
    }

    /**
     * @return array<int, string>
     */
    private function tentukanKelasIds(Materi $materi, string $idTahunAjaran): array
    {
        $kelasIds = $materi->kelasTerkait->pluck('id_kelas')->all();

        if (! empty($kelasIds)) {
            return $kelasIds;
        }

        return Kelas::where('is_aktif', true)
            ->where('id_tahun_ajaran', $idTahunAjaran)
            ->pluck('id_kelas')
            ->all();
    }

    private function buatIdKuis(string $idMateri, string $kodeKategori): string
    {
        $dasar = 'SD-'.preg_replace('/[^A-Za-z0-9\-]/', '', $idMateri).'-'.$kodeKategori;

        return Str::limit($dasar, 40, '');
    }

    /**
     * @return array<int, array<string, string|null>>
     */
    private function ambilBankSoal(Materi $materi, string $kategori, string $judulBersih): array
    {
        $judulLower = Str::lower($judulBersih);
        $offset = match ($kategori) {
            'pre-test' => 0,
            'post-test' => 1,
            'kuis harian' => 2,
            'ulangan' => 3,
            default => 0,
        };

        $kumpulan = $this->bankSoalPerTopik($judulLower);

        if ($kumpulan !== null) {
            // Rotasi urutan soal agar setiap kategori memiliki variasi susunan/penekanan
            $total = count($kumpulan);
            $hasil = [];
            for ($i = 0; $i < 4; $i++) {
                $hasil[] = $kumpulan[($i + $offset) % $total];
            }

            return $hasil;
        }

        $namaMapel = $materi->mapel?->nama_mapel ?? 'Mata Pelajaran';

        return [
            [
                'pertanyaan' => "Apa fokus utama pembahasan pada materi \"{$judulBersih}\" ({$namaMapel})?",
                'opsi_a' => "Memahami konsep dasar dan penerapannya dalam {$judulBersih}",
                'opsi_b' => 'Menghafal daftar istilah tanpa memahami konteksnya',
                'opsi_c' => 'Mengabaikan keterkaitan antar konsep pada bab ini',
                'opsi_d' => 'Menyalin catatan tanpa melakukan latihan soal',
                'opsi_e' => null,
                'jawaban' => 'A',
            ],
            [
                'pertanyaan' => "Langkah yang tepat sebelum menyimpulkan materi \"{$judulBersih}\" adalah ...",
                'opsi_a' => 'Melewati bagian inti pembelajaran',
                'opsi_b' => 'Mengkaji contoh kasus dan konsep kunci secara sistematis',
                'opsi_c' => 'Hanya membaca judul bab tanpa isi materi',
                'opsi_d' => 'Menggunakan data yang tidak relevan',
                'opsi_e' => null,
                'jawaban' => 'B',
            ],
            [
                'pertanyaan' => "Manfaat mempelajari topik \"{$judulBersih}\" dalam pembelajaran {$namaMapel} adalah ...",
                'opsi_a' => 'Mengurangi kemampuan analisis siswa',
                'opsi_b' => 'Membuat materi tidak relevan dengan kehidupan nyata',
                'opsi_c' => 'Meningkatkan pemahaman konsep dan keterampilan pemecahan masalah',
                'opsi_d' => 'Menghilangkan kebutuhan evaluasi belajar',
                'opsi_e' => null,
                'jawaban' => 'C',
            ],
            [
                'pertanyaan' => "Evaluasi pemahaman pada materi \"{$judulBersih}\" bertujuan untuk ...",
                'opsi_a' => 'Mengukur pencapaian kompetensi setelah mempelajari materi',
                'opsi_b' => 'Menghindari diskusi kelas',
                'opsi_c' => 'Membatasi akses siswa ke bahan ajar',
                'opsi_d' => 'Mengganti seluruh kegiatan praktikum',
                'opsi_e' => null,
                'jawaban' => 'A',
            ],
        ];
    }

    /**
     * @return array<int, array<string, string|null>>|null
     */
    private function bankSoalPerTopik(string $judulLower): ?array
    {
        if (str_contains($judulLower, 'eksponen')) {
            return [
                [
                    'pertanyaan' => 'Hasil dari 2^3 × 2^4 adalah ...',
                    'opsi_a' => '2^7 (128)',
                    'opsi_b' => '2^12 (4096)',
                    'opsi_c' => '4^7',
                    'opsi_d' => '2^1 (2)',
                    'opsi_e' => '16',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Nilai dari a^0 untuk setiap bilangan real a ≠ 0 adalah ...',
                    'opsi_a' => '0',
                    'opsi_b' => '1',
                    'opsi_c' => 'a',
                    'opsi_d' => '-1',
                    'opsi_e' => 'Tidak terdefinisi',
                    'jawaban' => 'B',
                ],
                [
                    'pertanyaan' => 'Bentuk sederhana dari (3^2)^3 adalah ...',
                    'opsi_a' => '3^5',
                    'opsi_b' => '3^6',
                    'opsi_c' => '3^8',
                    'opsi_d' => '9^5',
                    'opsi_e' => '6^3',
                    'jawaban' => 'B',
                ],
                [
                    'pertanyaan' => 'Jika 5^x = 125, maka nilai x yang memenuhi adalah ...',
                    'opsi_a' => '2',
                    'opsi_b' => '3',
                    'opsi_c' => '4',
                    'opsi_d' => '5',
                    'opsi_e' => '25',
                    'jawaban' => 'B',
                ],
                [
                    'pertanyaan' => 'Bentuk pangkat positif dari 4^(-2) adalah ...',
                    'opsi_a' => '1/8',
                    'opsi_b' => '-8',
                    'opsi_c' => '1/16',
                    'opsi_d' => '-16',
                    'opsi_e' => '16',
                    'jawaban' => 'C',
                ],
            ];
        }

        if (str_contains($judulLower, 'barisan') || str_contains($judulLower, 'deret')) {
            return [
                [
                    'pertanyaan' => 'Pada barisan aritmetika 3, 7, 11, 15, ..., nilai beda (b) adalah ...',
                    'opsi_a' => '2',
                    'opsi_b' => '3',
                    'opsi_c' => '4',
                    'opsi_d' => '5',
                    'opsi_e' => '7',
                    'jawaban' => 'C',
                ],
                [
                    'pertanyaan' => 'Suku ke-5 dari barisan aritmetika dengan a = 2 dan b = 3 adalah ...',
                    'opsi_a' => '11',
                    'opsi_b' => '14',
                    'opsi_c' => '15',
                    'opsi_d' => '17',
                    'opsi_e' => '20',
                    'jawaban' => 'B',
                ],
                [
                    'pertanyaan' => 'Rasio (r) dari barisan geometri 2, 6, 18, 54, ... adalah ...',
                    'opsi_a' => '2',
                    'opsi_b' => '3',
                    'opsi_c' => '4',
                    'opsi_d' => '6',
                    'opsi_e' => '12',
                    'jawaban' => 'B',
                ],
                [
                    'pertanyaan' => 'Rumus suku ke-n barisan aritmetika adalah ...',
                    'opsi_a' => 'Un = a + (n - 1)b',
                    'opsi_b' => 'Un = a × r^(n - 1)',
                    'opsi_c' => 'Un = n/2 × (a + b)',
                    'opsi_d' => 'Un = a + n × b',
                    'opsi_e' => 'Un = a × b^n',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Jumlah 4 suku pertama deret 1 + 2 + 4 + 8 + ... adalah ...',
                    'opsi_a' => '12',
                    'opsi_b' => '14',
                    'opsi_c' => '15',
                    'opsi_d' => '16',
                    'opsi_e' => '31',
                    'jawaban' => 'C',
                ],
            ];
        }

        if (str_contains($judulLower, 'trigonometri')) {
            return [
                [
                    'pertanyaan' => 'Pada segitiga siku-siku, perbandingan sisi depan sudut dengan sisi miring disebut ...',
                    'opsi_a' => 'Cosinus (cos)',
                    'opsi_b' => 'Sinus (sin)',
                    'opsi_c' => 'Tangen (tan)',
                    'opsi_d' => 'Secan (sec)',
                    'opsi_e' => 'Cotangen (cot)',
                    'jawaban' => 'B',
                ],
                [
                    'pertanyaan' => 'Nilai dari sin 30° adalah ...',
                    'opsi_a' => '0',
                    'opsi_b' => '1/2',
                    'opsi_c' => '1/2 √2',
                    'opsi_d' => '1/2 √3',
                    'opsi_e' => '1',
                    'jawaban' => 'B',
                ],
                [
                    'pertanyaan' => 'Nilai dari cos 0° adalah ...',
                    'opsi_a' => '0',
                    'opsi_b' => '1/2',
                    'opsi_c' => '1',
                    'opsi_d' => '-1',
                    'opsi_e' => 'Tak hingga',
                    'jawaban' => 'C',
                ],
                [
                    'pertanyaan' => 'Nilai dari tan 45° adalah ...',
                    'opsi_a' => '0',
                    'opsi_b' => '1/2',
                    'opsi_c' => '1',
                    'opsi_d' => '√3',
                    'opsi_e' => '2',
                    'jawaban' => 'C',
                ],
                [
                    'pertanyaan' => 'Identitas trigonometri dasar yang benar adalah ...',
                    'opsi_a' => 'sin²θ + cos²θ = 1',
                    'opsi_b' => 'sin²θ - cos²θ = 1',
                    'opsi_c' => 'sin θ + cos θ = 1',
                    'opsi_d' => 'tan θ = cos θ / sin θ',
                    'opsi_e' => 'sin²θ + tan²θ = 1',
                    'jawaban' => 'A',
                ],
            ];
        }

        if (str_contains($judulLower, 'fakta alam') || str_contains($judulLower, 'objektif')) {
            return [
                [
                    'pertanyaan' => 'Teks yang menyajikan informasi tentang suatu objek berdasarkan hasil pengamatan secara objektif dan sistematis disebut ...',
                    'opsi_a' => 'Teks Laporan Hasil Observasi',
                    'opsi_b' => 'Teks Anekdot',
                    'opsi_c' => 'Teks Cerpen',
                    'opsi_d' => 'Teks Puisi',
                    'opsi_e' => 'Teks Negosiasi',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Struktur teks laporan hasil observasi secara berurutan adalah ...',
                    'opsi_a' => 'Orientasi – Krisis – Reaksi',
                    'opsi_b' => 'Pernyataan Umum – Deskripsi Bagian – Deskripsi Manfaat',
                    'opsi_c' => 'Abstrak – Orientasi – Koda',
                    'opsi_d' => 'Tesis – Argumentasi – Penegasan Ulang',
                    'opsi_e' => 'Judul – Alur – Amanat',
                    'jawaban' => 'B',
                ],
                [
                    'pertanyaan' => 'Sifat utama informasi yang disajikan dalam teks laporan hasil observasi adalah ...',
                    'opsi_a' => 'Fiktif dan imajinatif',
                    'opsi_b' => 'Objektif dan sesuai fakta',
                    'opsi_c' => 'Subjektif berdasarkan opini pribadi',
                    'opsi_d' => 'Mengandung sindiran lucu',
                    'opsi_e' => 'Berbentuk dialog drama',
                    'jawaban' => 'B',
                ],
                [
                    'pertanyaan' => 'Kalimat yang menjelaskan pengertian umum suatu objek dengan kata "adalah" atau "merupakan" disebut kalimat ...',
                    'opsi_a' => 'Kalimat perintah',
                    'opsi_b' => 'Kalimat definisi',
                    'opsi_c' => 'Kalimat tanya',
                    'opsi_d' => 'Kalimat seruan',
                    'opsi_e' => 'Kalimat majas',
                    'jawaban' => 'B',
                ],
            ];
        }

        if (str_contains($judulLower, 'kritik') || str_contains($judulLower, 'humor')) {
            return [
                [
                    'pertanyaan' => 'Cerita singkat yang lucu dan menarik serta mengandung kritik atau sindiran terhadap fenomena sosial disebut ...',
                    'opsi_a' => 'Teks Eksposisi',
                    'opsi_b' => 'Teks Anekdot',
                    'opsi_c' => 'Teks Prosedur',
                    'opsi_d' => 'Teks Biografi',
                    'opsi_e' => 'Teks Deskripsi',
                    'jawaban' => 'B',
                ],
                [
                    'pertanyaan' => 'Bagian dalam struktur teks anekdot yang memunculkan masalah atau hal unik yang memicu kelucuan disebut ...',
                    'opsi_a' => 'Abstraksi',
                    'opsi_b' => 'Orientasi',
                    'opsi_c' => 'Krisis',
                    'opsi_d' => 'Reaksi',
                    'opsi_e' => 'Koda',
                    'jawaban' => 'C',
                ],
                [
                    'pertanyaan' => 'Tujuan utama penggunaan humor dalam teks anekdot adalah ...',
                    'opsi_a' => 'Menghibur semata tanpa pesan moral',
                    'opsi_b' => 'Menyampaikan kritik secara halus dan mengena',
                    'opsi_c' => 'Menjelaskan langkah kerja alat',
                    'opsi_d' => 'Melaporkan hasil penelitian ilmiah',
                    'opsi_e' => 'Membuat pembaca bingung',
                    'jawaban' => 'B',
                ],
                [
                    'pertanyaan' => 'Bagian penutup dalam teks anekdot yang menunjukkan perubahan situasi atau kesimpulan cerita disebut ...',
                    'opsi_a' => 'Koda',
                    'opsi_b' => 'Krisis',
                    'opsi_c' => 'Abstraksi',
                    'opsi_d' => 'Orientasi',
                    'opsi_e' => 'Tesis',
                    'jawaban' => 'A',
                ],
            ];
        }

        if (str_contains($judulLower, 'kisah lintas zaman') || str_contains($judulLower, 'hikayat')) {
            return [
                [
                    'pertanyaan' => 'Karya sastra lama berbentuk prosa yang mengisahkan kehidupan raja, bangsawan, atau tokoh sakti disebut ...',
                    'opsi_a' => 'Hikayat',
                    'opsi_b' => 'Artikel opini',
                    'opsi_c' => 'Laporan observasi',
                    'opsi_d' => 'Resensi',
                    'opsi_e' => 'Teks prosedur',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Salah satu ciri khas hikayat adalah adanya kemustahilan, yang berarti ...',
                    'opsi_a' => 'Semua kejadian sesuai logika ilmiah modern',
                    'opsi_b' => 'Terdapat peristiwa di luar nalar atau kesaktian tokoh',
                    'opsi_c' => 'Menggunakan bahasa pemrograman',
                    'opsi_d' => 'Berisi data statistik kependudukan',
                    'opsi_e' => 'Selalu ditulis dalam bentuk tabel',
                    'jawaban' => 'B',
                ],
                [
                    'pertanyaan' => 'Bahasa yang umumnya digunakan dalam naskah hikayat asli adalah ...',
                    'opsi_a' => 'Bahasa Melayu Klasik',
                    'opsi_b' => 'Bahasa gaul modern',
                    'opsi_c' => 'Bahasa teknis komputer',
                    'opsi_d' => 'Bahasa jurnalistik singkat',
                    'opsi_e' => 'Bahasa hukum internasional',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Nilai yang berkaitan dengan ajaran baik dan buruk perilaku manusia dalam hikayat disebut nilai ...',
                    'opsi_a' => 'Nilai moral',
                    'opsi_b' => 'Nilai ekonomi',
                    'opsi_c' => 'Nilai teknologi',
                    'opsi_d' => 'Nilai geometris',
                    'opsi_e' => 'Nilai komersial',
                    'jawaban' => 'A',
                ],
            ];
        }

        if (str_contains($judulLower, 'talking about self')) {
            return [
                [
                    'pertanyaan' => 'Which expression is appropriate for introducing yourself in a formal situation?',
                    'opsi_a' => 'Let me introduce myself, my name is Andi.',
                    'opsi_b' => 'Who are you?',
                    'opsi_c' => 'See you later!',
                    'opsi_d' => 'I do not care.',
                    'opsi_e' => 'Close the door, please.',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Complete the sentence: "I ___ a vocational high school student majoring in TKJ."',
                    'opsi_a' => 'is',
                    'opsi_b' => 'am',
                    'opsi_c' => 'are',
                    'opsi_d' => 'were',
                    'opsi_e' => 'be',
                    'jawaban' => 'B',
                ],
                [
                    'pertanyaan' => 'Which question is used to ask about someone\'s hobby?',
                    'opsi_a' => 'Where do you live?',
                    'opsi_b' => 'What do you like to do in your free time?',
                    'opsi_c' => 'How old is your school?',
                    'opsi_d' => 'What time is it?',
                    'opsi_e' => 'How much does it cost?',
                    'jawaban' => 'B',
                ],
                [
                    'pertanyaan' => '"Nice to meet you." The best response to this expression is ...',
                    'opsi_a' => 'Good night.',
                    'opsi_b' => 'Nice to meet you too.',
                    'opsi_c' => 'I am sorry.',
                    'opsi_d' => 'Thank you for nothing.',
                    'opsi_e' => 'You are wrong.',
                    'jawaban' => 'B',
                ],
            ];
        }

        if (str_contains($judulLower, 'complimenting') || str_contains($judulLower, 'showing care')) {
            return [
                [
                    'pertanyaan' => 'Which sentence is an expression of complimenting?',
                    'opsi_a' => 'Your presentation is very clear and impressive!',
                    'opsi_b' => 'Why did you come late today?',
                    'opsi_c' => 'Please submit your homework tomorrow.',
                    'opsi_d' => 'I forgot to bring my notebook.',
                    'opsi_e' => 'Turn off the computer now.',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Your friend looks sad because they failed a test. What should you say to show care?',
                    'opsi_a' => 'That is funny!',
                    'opsi_b' => 'I am sorry to hear that. Do not give up, I am here to help you study.',
                    'opsi_c' => 'You should leave the class.',
                    'opsi_d' => 'It is none of my business.',
                    'opsi_e' => 'Congratulations on your failure.',
                    'jawaban' => 'B',
                ],
                [
                    'pertanyaan' => 'How do you respond politely when someone says "Great job on your project!"?',
                    'opsi_a' => 'Thank you, I really appreciate it.',
                    'opsi_b' => 'No, you are lying.',
                    'opsi_c' => 'Go away.',
                    'opsi_d' => 'I do not know you.',
                    'opsi_e' => 'Forget it.',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => '"What a wonderful drawing!" is an example of ...',
                    'opsi_a' => 'Giving a warning',
                    'opsi_b' => 'Giving a compliment',
                    'opsi_c' => 'Asking for permission',
                    'opsi_d' => 'Making an appointment',
                    'opsi_e' => 'Refusing an offer',
                    'jawaban' => 'B',
                ],
            ];
        }

        if (str_contains($judulLower, 'expressing intention')) {
            return [
                [
                    'pertanyaan' => 'Which modal/phrase is commonly used to express a planned intention?',
                    'opsi_a' => 'Be going to / Would like to',
                    'opsi_b' => 'Used to',
                    'opsi_c' => 'Had better not',
                    'opsi_d' => 'Might have been',
                    'opsi_e' => 'Must not',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Complete the sentence: "Next week, we ___ visit the computer lab for network practice."',
                    'opsi_a' => 'are going to',
                    'opsi_b' => 'was going to',
                    'opsi_c' => 'were',
                    'opsi_d' => 'did',
                    'opsi_e' => 'have been',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Which sentence expresses an intention?',
                    'opsi_a' => 'I am going to finish my coding assignment tonight.',
                    'opsi_b' => 'I finished my assignment yesterday.',
                    'opsi_c' => 'The computer is on the table.',
                    'opsi_d' => 'She likes reading novels.',
                    'opsi_e' => 'The weather was cold last night.',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => '"What are you going to do this weekend?" is a question to ask about someone\'s ...',
                    'opsi_a' => 'Past experience',
                    'opsi_b' => 'Future intention or plan',
                    'opsi_c' => 'Physical appearance',
                    'opsi_d' => 'Home address',
                    'opsi_e' => 'Favorite food',
                    'jawaban' => 'B',
                ],
            ];
        }

        if (str_contains($judulLower, 'hak asasi manusia')) {
            return [
                [
                    'pertanyaan' => 'Hak dasar yang dimiliki setiap manusia sejak lahir sebagai anugerah Tuhan Yang Maha Esa disebut ...',
                    'opsi_a' => 'Hak Asasi Manusia (HAM)',
                    'opsi_b' => 'Hak pilih sementara',
                    'opsi_c' => 'Hak istimewa kelompok',
                    'opsi_d' => 'Hak milik perusahaan',
                    'opsi_e' => 'Hak paten',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Salah satu sifat Hak Asasi Manusia adalah universal, artinya ...',
                    'opsi_a' => 'Hanya berlaku di kota besar',
                    'opsi_b' => 'Berlaku untuk semua manusia tanpa membedakan suku, agama, atau status',
                    'opsi_c' => 'Dapat dicabut sewaktu-waktu secara sepihak',
                    'opsi_d' => 'Hanya berlaku bagi warga negara tertentu',
                    'opsi_e' => 'Dapat diperjualbelikan',
                    'jawaban' => 'B',
                ],
                [
                    'pertanyaan' => 'Lembaga independen di Indonesia yang bertugas melaksanakan pengkajian, penelitian, dan pemantauan HAM adalah ...',
                    'opsi_a' => 'Komnas HAM',
                    'opsi_b' => 'BPK',
                    'opsi_c' => 'KPU',
                    'opsi_d' => 'OJK',
                    'opsi_e' => 'BMKG',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Contoh kewajiban warga negara dalam menghormati HAM di lingkungan sekolah adalah ...',
                    'opsi_a' => 'Menghargai pendapat teman dan tidak melakukan perundungan',
                    'opsi_b' => 'Memaksakan kehendak pribadi kepada teman',
                    'opsi_c' => 'Mengabaikan tata tertib sekolah',
                    'opsi_d' => 'Memilih-milih teman berdasarkan status ekonomi',
                    'opsi_e' => 'Menyebarkan berita bohong di kelas',
                    'jawaban' => 'A',
                ],
            ];
        }

        if (str_contains($judulLower, 'kaidah fundamental')) {
            return [
                [
                    'pertanyaan' => 'Pokok kaidah negara yang fundamental (Staatsfundamentalnorm) dalam sistem ketatanegaraan Indonesia terdapat pada ...',
                    'opsi_a' => 'Pembukaan UUD NRI Tahun 1945',
                    'opsi_b' => 'Peraturan Daerah',
                    'opsi_c' => 'Tata tertib sekolah',
                    'opsi_d' => 'Keputusan Menteri',
                    'opsi_e' => 'Peraturan Desa',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Rumusan dasar negara Pancasila tercantum secara resmi dalam Pembukaan UUD 1945 alinea ke- ...',
                    'opsi_a' => 'Pertama',
                    'opsi_b' => 'Kedua',
                    'opsi_c' => 'Ketiga',
                    'opsi_d' => 'Keempat',
                    'opsi_e' => 'Kelima',
                    'jawaban' => 'D',
                ],
                [
                    'pertanyaan' => 'Makna alinea pertama Pembukaan UUD 1945 menegaskan bahwa kemerdekaan adalah hak segala bangsa dan oleh sebab itu ...',
                    'opsi_a' => 'Penjajahan di atas dunia harus dihapuskan',
                    'opsi_b' => 'Negara harus menutup diri dari dunia luar',
                    'opsi_c' => 'Peraturan daerah lebih tinggi dari UUD',
                    'opsi_d' => 'Pemerintahan bersifat monarki absolut',
                    'opsi_e' => 'Tidak diperlukan kerja sama internasional',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Hubungan antara Pembukaan UUD 1945 dan Pancasila bersifat ...',
                    'opsi_a' => 'Kausal-organis dan tidak dapat dipisahkan',
                    'opsi_b' => 'Sementara dan dapat diubah tiap tahun',
                    'opsi_c' => 'Bertentangan satu sama lain',
                    'opsi_d' => 'Hanya berlaku di masa darurat',
                    'opsi_e' => 'Tidak memiliki kekuatan hukum',
                    'jawaban' => 'A',
                ],
            ];
        }

        if (str_contains($judulLower, 'keutuhan negara') || str_contains($judulLower, 'nkri')) {
            return [
                [
                    'pertanyaan' => 'Bentuk negara Indonesia sebagaimana ditegaskan dalam Pasal 1 ayat (1) UUD NRI Tahun 1945 adalah ...',
                    'opsi_a' => 'Negara Kesatuan yang berbentuk Republik',
                    'opsi_b' => 'Negara Serikat',
                    'opsi_c' => 'Negara Monarki Konstitusional',
                    'opsi_d' => 'Konfederasi',
                    'opsi_e' => 'Negara Bagian',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Semboyan yang menjadi pengikat persatuan dalam keberagaman bangsa Indonesia adalah ...',
                    'opsi_a' => 'Bhinneka Tunggal Ika',
                    'opsi_b' => 'Divide et Impera',
                    'opsi_c' => 'Laissez Faire',
                    'opsi_d' => 'Homo Homini Lupus',
                    'opsi_e' => 'Veni Vidi Vici',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Perilaku yang mendukung keutuhan NKRI di era digital adalah ...',
                    'opsi_a' => 'Menyaring informasi sebelum membagikan agar tidak menyebar hoaks dan ujaran kebencian',
                    'opsi_b' => 'Membuat akun anonim untuk memprovokasi antar-kelompok',
                    'opsi_c' => 'Mengabaikan etika bermedia sosial',
                    'opsi_d' => 'Menyebarkan data pribadi orang lain tanpa izin',
                    'opsi_e' => 'Merendahkan budaya daerah lain',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Upaya membela negara merupakan hak dan kewajiban setiap warga negara sebagaimana diatur dalam Pasal ...',
                    'opsi_a' => 'Pasal 27 ayat (3) UUD NRI Tahun 1945',
                    'opsi_b' => 'Pasal 1 ayat (1) KUHP',
                    'opsi_c' => 'Pasal 36B saja',
                    'opsi_d' => 'Aturan lalu lintas',
                    'opsi_e' => 'Peraturan kelurahan',
                    'jawaban' => 'A',
                ],
            ];
        }

        if (str_contains($judulLower, 'pemerintah pusat dan daerah') || str_contains($judulLower, 'harmonisasi')) {
            return [
                [
                    'pertanyaan' => 'Penyerahan wewenang pemerintahan oleh pemerintah pusat kepada daerah otonom untuk mengatur dan mengurus urusan pemerintahan sendiri disebut ...',
                    'opsi_a' => 'Desentralisasi',
                    'opsi_b' => 'Sentralisasi penuh',
                    'opsi_c' => 'Dekonsentrasi mutlak',
                    'opsi_d' => 'Privatisasi',
                    'opsi_e' => 'Federalisasi',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Urusan pemerintahan yang tetap menjadi kewenangan mutlak pemerintah pusat adalah ...',
                    'opsi_a' => 'Pertahanan, keamanan, moneter dan fiskal nasional, politik luar negeri, yustisi, dan agama',
                    'opsi_b' => 'Pengelolaan pasar tradisional tingkat kabupaten',
                    'opsi_c' => 'Kebersihan taman kota',
                    'opsi_d' => 'Pengelolaan parkir daerah',
                    'opsi_e' => 'Izin mendirikan bangunan rumah tinggal',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Tujuan utama otonomi daerah dalam kerangka NKRI adalah ...',
                    'opsi_a' => 'Meningkatkan kesejahteraan masyarakat dan kualitas pelayanan publik di daerah',
                    'opsi_b' => 'Memisahkan daerah dari Negara Kesatuan Republik Indonesia',
                    'opsi_c' => 'Menghilangkan koordinasi antara pusat dan daerah',
                    'opsi_d' => 'Membebani masyarakat dengan aturan yang bertentangan',
                    'opsi_e' => 'Mengganti konstitusi negara di tiap provinsi',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Asas penyelenggaraan pemerintahan daerah yang merupakan pelimpahan sebagian urusan pemerintah pusat kepada gubernur sebagai wakil pemerintah pusat adalah ...',
                    'opsi_a' => 'Dekonsentrasi',
                    'opsi_b' => 'Otonomi bebas',
                    'opsi_c' => 'Swastanisasi',
                    'opsi_d' => 'Sentralisasi absolut',
                    'opsi_e' => 'Separatisme',
                    'jawaban' => 'A',
                ],
            ];
        }

        if (str_contains($judulLower, 'berpikir komputasional')) {
            return [
                [
                    'pertanyaan' => 'Algoritma pencarian yang membagi dua area pencarian secara berulang pada data yang sudah terurut disebut ...',
                    'opsi_a' => 'Linear Search',
                    'opsi_b' => 'Binary Search',
                    'opsi_c' => 'Bubble Sort',
                    'opsi_d' => 'Stack Push',
                    'opsi_e' => 'Queue Dequeue',
                    'jawaban' => 'B',
                ],
                [
                    'pertanyaan' => 'Struktur data antrean (Queue) bekerja dengan prinsip ...',
                    'opsi_a' => 'LIFO (Last In First Out)',
                    'opsi_b' => 'FIFO (First In First Out)',
                    'opsi_c' => 'Random Access',
                    'opsi_d' => 'Sorting Descending',
                    'opsi_e' => 'Hashing',
                    'jawaban' => 'B',
                ],
                [
                    'pertanyaan' => 'Pilar berpikir komputasional yang menyaring detail yang tidak penting dan fokus pada informasi inti disebut ...',
                    'opsi_a' => 'Abstraksi',
                    'opsi_b' => 'Dekomposisi',
                    'opsi_c' => 'Pengenalan pola',
                    'opsi_d' => 'Iterasi',
                    'opsi_e' => 'Kompilasi',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Syarat utama agar Binary Search dapat bekerja dengan benar adalah data harus dalam keadaan ...',
                    'opsi_a' => 'Teracak',
                    'opsi_b' => 'Terurut',
                    'opsi_c' => 'Berupa gambar',
                    'opsi_d' => 'Kosong',
                    'opsi_e' => 'Duplikat semua',
                    'jawaban' => 'B',
                ],
            ];
        }

        if (str_contains($judulLower, 'integrasi aplikasi perkantoran')) {
            return [
                [
                    'pertanyaan' => 'Fitur pada pengolah kata untuk membuat surat massal dengan mengambil data penerima dari tabel pengolah angka adalah ...',
                    'opsi_a' => 'Mail Merge',
                    'opsi_b' => 'Find and Replace',
                    'opsi_c' => 'Header & Footer',
                    'opsi_d' => 'Watermark',
                    'opsi_e' => 'Page Break',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Aplikasi perkantoran yang paling tepat untuk mengolah data numerik, rumus, dan grafik adalah ...',
                    'opsi_a' => 'Pengolah kata (Word Processor)',
                    'opsi_b' => 'Pengolah lembar kerja (Spreadsheet)',
                    'opsi_c' => 'Pemutar media',
                    'opsi_d' => 'Peramban web',
                    'opsi_e' => 'Editor audio',
                    'jawaban' => 'B',
                ],
                [
                    'pertanyaan' => 'Keuntungan menyematkan (embed/link) grafik dari spreadsheet ke slide presentasi adalah ...',
                    'opsi_a' => 'Data visual dapat diperbarui mengikuti perubahan data sumber',
                    'opsi_b' => 'Ukuran teks menjadi hilang',
                    'opsi_c' => 'Presentasi tidak bisa dibuka',
                    'opsi_d' => 'Rumus spreadsheet terhapus otomatis',
                    'opsi_e' => 'Slide tidak membutuhkan judul',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Fungsi pada aplikasi spreadsheet untuk menjumlahkan rentang sel adalah ...',
                    'opsi_a' => '=SUM()',
                    'opsi_b' => '=COUNTIF()',
                    'opsi_c' => '=CONCAT()',
                    'opsi_d' => '=UPPER()',
                    'opsi_e' => '=TRIM()',
                    'jawaban' => 'A',
                ],
            ];
        }

        if (str_contains($judulLower, 'sistem komputer')) {
            return [
                [
                    'pertanyaan' => 'Komponen perangkat keras yang bertugas menjalankan instruksi dan mengolah data utama komputer adalah ...',
                    'opsi_a' => 'CPU (Central Processing Unit)',
                    'opsi_b' => 'Monitor',
                    'opsi_c' => 'Printer',
                    'opsi_d' => 'Speaker',
                    'opsi_e' => 'Scanner',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Memori komputer yang bersifat volatile (data hilang saat listrik padam) dan digunakan untuk menyimpan data sementara program berjalan adalah ...',
                    'opsi_a' => 'RAM',
                    'opsi_b' => 'SSD',
                    'opsi_c' => 'Hard Disk',
                    'opsi_d' => 'Flashdisk',
                    'opsi_e' => 'DVD-ROM',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Perangkat lunak utama yang mengelola sumber daya perangkat keras dan menjadi jembatan antara pengguna dengan komputer adalah ...',
                    'opsi_a' => 'Sistem Operasi',
                    'opsi_b' => 'Aplikasi Desain Grafis',
                    'opsi_c' => 'Pengolah Kata',
                    'opsi_d' => 'Game',
                    'opsi_e' => 'Pemutar Video',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Contoh perangkat masukan (input device) pada sistem komputer adalah ...',
                    'opsi_a' => 'Keyboard dan Mouse',
                    'opsi_b' => 'Printer dan Proyektor',
                    'opsi_c' => 'Speaker dan Monitor',
                    'opsi_d' => 'Plotter dan Headphone',
                    'opsi_e' => 'Monitor LCD',
                    'jawaban' => 'A',
                ],
            ];
        }

        if (str_contains($judulLower, 'jaringan komputer')) {
            return [
                [
                    'pertanyaan' => 'Perangkat jaringan yang bertugas meneruskan paket data antar jaringan berbeda berdasarkan alamat IP adalah ...',
                    'opsi_a' => 'Router',
                    'opsi_b' => 'Keyboard',
                    'opsi_c' => 'Monitor',
                    'opsi_d' => 'Printer',
                    'opsi_e' => 'Mouse',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Protokol yang mengamankan komunikasi web dengan enkripsi SSL/TLS ditunjukkan oleh awalan ...',
                    'opsi_a' => 'HTTPS',
                    'opsi_b' => 'HTTP',
                    'opsi_c' => 'FTP',
                    'opsi_d' => 'Telnet',
                    'opsi_e' => 'ICMP',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Jaringan komputer yang mencakup area terbatas seperti satu gedung atau ruang laboratorium disebut ...',
                    'opsi_a' => 'LAN (Local Area Network)',
                    'opsi_b' => 'WAN (Wide Area Network)',
                    'opsi_c' => 'MAN (Metropolitan Area Network)',
                    'opsi_d' => 'Internet Global',
                    'opsi_e' => 'VPN Antarbenua',
                    'jawaban' => 'A',
                ],
                [
                    'pertanyaan' => 'Langkah menjaga keamanan data saat menggunakan jaringan publik adalah ...',
                    'opsi_a' => 'Menggunakan koneksi terenkripsi (HTTPS/VPN) dan tidak membagikan kata sandi',
                    'opsi_b' => 'Menonaktifkan kata sandi semua akun',
                    'opsi_c' => 'Mengklik semua tautan mencurigakan',
                    'opsi_d' => 'Membagikan kode OTP ke orang lain',
                    'opsi_e' => 'Mematikan pembaruan keamanan sistem',
                    'jawaban' => 'A',
                ],
            ];
        }

        return null;
    }
}
