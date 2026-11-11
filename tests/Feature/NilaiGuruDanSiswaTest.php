<?php

namespace Tests\Feature;

use App\Models\DraftJawaban;
use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Mengerjakan;
use App\Models\NilaiEssay;
use App\Models\Remedial;
use App\Models\RemedialSiswa;
use App\Models\SesiKuis;
use App\Models\Soal;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Http\Controllers\Api\GuruNilaiController;
use App\Services\KuisEssayGradingService;
use App\Services\KuisPenilaianService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class NilaiGuruDanSiswaTest extends TestCase
{
    private User $siswa;
    private User $guru;
    private Mapel $mapel;
    private Materi $materi;
    private Kuis $kuisUlangan;
    private Kuis $kuisHarian;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertTrue($this->app->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame('sqlite', config('database.connections.sqlite.driver'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertEmpty(config('database.connections.sqlite.url'));
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->assertSame(1, (int) DB::selectOne('PRAGMA foreign_keys')->foreign_keys);
        $this->assertSame('array', config('cache.default'));
        $this->assertSame('array', config('session.driver'));

        $this->withoutVite();
        $this->createSchema();
        $this->seedScenario();
    }

    private function createSchema(): void
    {
        Schema::create('user', function (Blueprint $table) {
            $table->string('id_user', 50)->primary();
            $table->string('nomor_induk', 50)->nullable();
            $table->string('nama_lengkap', 100);
            $table->string('role', 20);
            $table->string('status', 20)->default('aktif');
            $table->string('kelas', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('password', 255);
            $table->boolean('wajib_ganti_password')->default(false);
            $table->string('dibuat_oleh', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('tahun_ajaran', function (Blueprint $table) {
            $table->string('id_tahun_ajaran', 50)->primary();
            $table->string('nama', 50);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->boolean('is_aktif')->default(true);
            $table->string('tampil_jawaban', 20)->default('setelah_submit');
            $table->timestamps();
        });

        Schema::create('mapel', function (Blueprint $table) {
            $table->string('id_mapel', 50)->primary();
            $table->string('nama_mapel', 100);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->string('tampil_jawaban', 20)->default('setelah_submit');
            $table->timestamps();
        });

        Schema::create('materi', function (Blueprint $table) {
            $table->string('id_materi', 50)->primary();
            $table->string('judul', 150);
            $table->string('bab', 50)->nullable();
            $table->string('kelas', 50)->nullable();
            $table->string('id_mapel', 50);
            $table->text('deskripsi')->nullable();
            $table->string('isi_materi')->nullable();
            $table->string('link_youtube')->nullable();
            $table->string('id_user', 50);
            $table->timestamps();
        });

        Schema::create('kuis', function (Blueprint $table) {
            $table->string('id_kuis', 50)->primary();
            $table->string('judul', 150);
            $table->text('deskripsi')->nullable();
            $table->string('kategori', 50);
            $table->string('tingkat_kesulitan', 20)->default('mudah');
            $table->string('id_mapel', 50);
            $table->string('id_tahun_ajaran', 50);
            $table->unsignedInteger('alokasi_waktu')->nullable();
            $table->decimal('kkm', 5, 2)->nullable();
            $table->dateTime('waktu_mulai')->nullable();
            $table->dateTime('waktu_selesai')->nullable();
            $table->string('id_user', 50);
            $table->string('status_publikasi', 20)->default('draft');
            $table->string('id_publisher', 50)->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->string('tampil_jawaban', 20)->default('setelah_submit');
            $table->timestamp('created_at')->nullable()->useCurrent();
        });

        Schema::create('materi_kuis', function (Blueprint $table) {
            $table->increments('id_materi_kuis');
            $table->string('id_materi', 50);
            $table->string('id_kuis', 50);
        });

        Schema::create('soal', function (Blueprint $table) {
            $table->string('id_soal', 50)->primary();
            $table->string('id_kuis', 50);
            $table->string('tipe', 20)->default('pilihan ganda');
            $table->text('pertanyaan');
            $table->string('opsi_a')->nullable();
            $table->string('opsi_b')->nullable();
            $table->string('opsi_c')->nullable();
            $table->string('opsi_d')->nullable();
            $table->string('opsi_e')->nullable();
            $table->string('jawaban', 5)->nullable();
            $table->decimal('bobot', 5, 2)->default(1);
            $table->string('tingkat_kesulitan', 50)->nullable();
        });

        Schema::create('sesi_kuis', function (Blueprint $table) {
            $table->increments('id_sesi');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->dateTime('waktu_mulai');
            $table->dateTime('waktu_selesai')->nullable();
            $table->dateTime('waktu_seharusnya_selesai');
            $table->string('status', 20)->default('berjalan');
            $table->unsignedInteger('sisa_waktu_detik')->nullable();
            $table->timestamps();
        });

        Schema::create('draft_jawaban', function (Blueprint $table) {
            $table->increments('id_draft');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->string('id_soal', 50);
            $table->string('jawaban_dipilih', 10)->nullable();
            $table->dateTime('disimpan_pada');
        });

        Schema::create('mengerjakan', function (Blueprint $table) {
            $table->increments('id_mengerjakan');
            $table->string('id_user', 50);
            $table->string('id_kuis', 50);
            $table->decimal('nilai', 5, 2)->nullable();
            $table->string('status_pengerjaan', 20)->default('final');
            $table->unsignedInteger('percobaan_ke')->default(1);
            $table->dateTime('tanggal');
            $table->string('status_validasi', 30)->default('belum divalidasi');
            $table->string('id_validator', 50)->nullable();
        });

        Schema::create('nilai_essay', function (Blueprint $table) {
            $table->increments('id_nilai_essay');
            $table->unsignedInteger('id_mengerjakan');
            $table->string('id_soal', 50);
            $table->text('jawaban_teks')->nullable();
            $table->decimal('nilai', 5, 2)->nullable();
            $table->text('catatan_guru')->nullable();
            $table->string('id_penilai', 50)->nullable();
            $table->timestamp('dinilai_at')->nullable();
        });

        Schema::create('remedial', function (Blueprint $table) {
            $table->increments('id_remedial');
            $table->string('id_kuis_remedial', 50)->unique();
            $table->string('id_kuis_asal', 50);
        });

        Schema::create('remedial_siswa', function (Blueprint $table) {
            $table->increments('id_remedial_siswa');
            $table->string('id_kuis_remedial', 50);
            $table->unsignedInteger('id_mengerjakan_asal');
            $table->string('id_guru', 50);
            $table->date('tanggal_ditugaskan');
            $table->string('status', 20)->default('ditugaskan');
        });

        Schema::create('progress', function (Blueprint $table) {
            $table->string('id_progress', 50)->primary();
            $table->string('status', 50)->nullable();
            $table->date('tanggal')->nullable();
            $table->string('id_materi', 50);
            $table->string('id_user', 50);
        });

        Schema::create('kelas', function (Blueprint $table) {
            $table->string('id_kelas', 50)->primary();
            $table->string('nama_kelas', 100);
            $table->string('tingkat', 20)->nullable();
            $table->string('id_tahun_ajaran', 50)->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->string('tampil_jawaban', 20)->default('setelah_submit');
            $table->timestamps();
        });

        Schema::create('anggota_kelas', function (Blueprint $table) {
            $table->increments('id_anggota_kelas');
            $table->string('id_kelas', 50);
            $table->string('id_user', 50);
            $table->timestamp('created_at')->nullable()->useCurrent();
        });

        Schema::create('mapel_kelas', function (Blueprint $table) {
            $table->increments('id_mapel_kelas');
            $table->string('id_mapel', 50);
            $table->string('id_kelas', 50);
            $table->timestamp('created_at')->nullable()->useCurrent();
        });

        Schema::create('pengajaran', function (Blueprint $table) {
            $table->increments('id_pengajaran');
            $table->string('id_user', 50);
            $table->string('id_kelas', 50);
            $table->string('id_mapel', 50);
        });
    }

    private function seedScenario(): void
    {
        $this->guru = User::create([
            'id_user' => 'U001',
            'nomor_induk' => '198001',
            'nama_lengkap' => 'Budi Guru',
            'role' => 'guru',
            'status' => 'aktif',
            'wajib_ganti_password' => false,
            'password' => bcrypt('password'),
        ]);

        $this->siswa = User::create([
            'id_user' => 'U002',
            'nomor_induk' => '202401',
            'nama_lengkap' => 'Siti Siswa',
            'role' => 'siswa',
            'status' => 'aktif',
            'wajib_ganti_password' => false,
            'password' => bcrypt('password'),
        ]);

        TahunAjaran::create([
            'id_tahun_ajaran' => 'TA001',
            'nama' => '2026/2027',
            'tanggal_mulai' => '2026-07-01',
            'tanggal_selesai' => '2027-06-30',
            'is_aktif' => true,
        ]);

        $this->mapel = Mapel::create([
            'id_mapel' => 'MP001',
            'nama_mapel' => 'Teknologi Informasi dan Komunikasi',
            'deskripsi' => 'Mapel TIK SMK',
            'is_aktif' => true,
        ]);

        $this->materi = Materi::create([
            'id_materi' => 'M001',
            'judul' => 'Dasar Jaringan Komputer',
            'bab' => 'Bab 1',
            'kelas' => 'X',
            'id_mapel' => 'MP001',
            'id_user' => 'U001',
        ]);

        $this->kuisUlangan = Kuis::create([
            'id_kuis' => 'K005',
            'judul' => 'Ulangan Harian 1',
            'kategori' => 'ulangan',
            'tingkat_kesulitan' => 'sedang',
            'id_mapel' => 'MP001',
            'id_tahun_ajaran' => 'TA001',
            'alokasi_waktu' => 20,
            'kkm' => 75.00,
            'id_user' => 'U001',
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);

        $this->kuisHarian = Kuis::create([
            'id_kuis' => 'K001',
            'judul' => 'Kuis Harian 1',
            'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => 'MP001',
            'id_tahun_ajaran' => 'TA001',
            'alokasi_waktu' => 15,
            'kkm' => 75.00,
            'id_user' => 'U001',
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);

        DB::table('materi_kuis')->insert([
            ['id_materi' => 'M001', 'id_kuis' => 'K005'],
            ['id_materi' => 'M001', 'id_kuis' => 'K001'],
        ]);

        // Kelas, anggota, dan mapel_kelas — dibutuhkan oleh ProgressController::mapelSiswa()
        DB::table('kelas')->insert([
            'id_kelas'        => 'KLS001',
            'nama_kelas'      => 'X RPL 1',
            'tingkat'         => 'X',
            'id_tahun_ajaran' => 'TA001',
            'is_aktif'        => true,
        ]);

        DB::table('anggota_kelas')->insert([
            'id_kelas' => 'KLS001',
            'id_user'  => 'U002',    // $this->siswa
        ]);

        DB::table('mapel_kelas')->insert([
            'id_mapel' => 'MP001',
            'id_kelas' => 'KLS001',
        ]);
    }

    public function test_guru_bisa_akses_halaman_nilai_dengan_filter_pills(): void
    {
        $this->actingAs($this->guru);

        $response = $this->get(route('guru.nilai.index'));
        $response->assertOk();
        $response->assertSee('Daftar Nilai Siswa');
        $response->assertSee('🏷️ Kategori:');
        $response->assertSee('🔄 Percobaan:');
        $response->assertSee('🎓 Kelulusan:');
        $response->assertSee('📅 Tanggal:');

        // Uji dengan query parameter filter
        $resFilter = $this->get(route('guru.nilai.index', [
            'kategori' => 'ulangan',
            'percobaan' => '1',
            'kelulusan' => 'tidak_lulus',
            'tanggal' => '2026-10-31',
        ]));
        $resFilter->assertOk();
    }

    public function test_ringkasan_kelas_menggunakan_agregat_guru(): void
    {
        DB::table('pengajaran')->insert([
            'id_user' => $this->guru->id_user,
            'id_kelas' => 'KLS001',
            'id_mapel' => $this->mapel->id_mapel,
        ]);
        Mengerjakan::create([
            'id_user' => $this->siswa->id_user,
            'id_kuis' => $this->kuisUlangan->id_kuis,
            'nilai' => 80,
            'status_pengerjaan' => 'final',
            'percobaan_ke' => 1,
            'tanggal' => now(),
            'status_validasi' => 'sudah divalidasi',
        ]);

        $this->actingAs($this->guru)
            ->get(route('guru.nilai.index'))
            ->assertOk()
            ->assertViewHas('kelasList', function ($kelasList): bool {
                $kelas = $kelasList->firstWhere('id_kelas', 'KLS001');

                return $kelas !== null
                    && $kelas->total_siswa === 1
                    && $kelas->total_materi === 1
                    && $kelas->kuis_aktif === 2
                    && $kelas->rata_nilai === 80.0;
            });
    }

    public function test_scope_nilai_hanya_mengembalikan_pengerjaan_yang_dikelola_guru(): void
    {
        $pengerjaanGuru = Mengerjakan::create([
            'id_user' => $this->siswa->id_user,
            'id_kuis' => $this->kuisUlangan->id_kuis,
            'nilai' => 80,
            'status_pengerjaan' => 'final',
            'percobaan_ke' => 1,
            'tanggal' => now(),
            'status_validasi' => 'belum divalidasi',
        ]);

        $guruLain = User::create([
            'id_user' => 'U003',
            'nama_lengkap' => 'Guru Lain',
            'role' => 'guru',
            'status' => 'aktif',
            'wajib_ganti_password' => false,
            'password' => bcrypt('password'),
        ]);

        $kuisGuruLain = Kuis::create([
            'id_kuis' => 'K002',
            'judul' => 'Kuis Guru Lain',
            'kategori' => 'ulangan',
            'tingkat_kesulitan' => 'sedang',
            'id_mapel' => $this->mapel->id_mapel,
            'id_tahun_ajaran' => 'TA001',
            'alokasi_waktu' => 20,
            'kkm' => 75,
            'id_user' => $guruLain->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);

        $pengerjaanGuruLain = Mengerjakan::create([
            'id_user' => $this->siswa->id_user,
            'id_kuis' => $kuisGuruLain->id_kuis,
            'nilai' => 50,
            'status_pengerjaan' => 'final',
            'percobaan_ke' => 1,
            'tanggal' => now(),
            'status_validasi' => 'belum divalidasi',
        ]);

        $terlihat = Mengerjakan::query()->dikelolaOlehGuru($this->guru)->pluck('id_mengerjakan');

        $this->assertTrue($terlihat->contains($pengerjaanGuru->id_mengerjakan));
        $this->assertFalse($terlihat->contains($pengerjaanGuruLain->id_mengerjakan));

        DB::table('pengajaran')->insert([
            'id_user' => $this->guru->id_user,
            'id_kelas' => 'KLS001',
            'id_mapel' => $this->mapel->id_mapel,
        ]);

        $terlihatSetelahDitugaskan = Mengerjakan::query()->dikelolaOlehGuru($this->guru)->pluck('id_mengerjakan');
        $this->assertTrue($terlihatSetelahDitugaskan->contains($pengerjaanGuruLain->id_mengerjakan));
    }

    public function test_guru_lain_tidak_bisa_membaca_atau_mengubah_nilai(): void
    {
        $pengerjaan = Mengerjakan::create([
            'id_user' => $this->siswa->id_user,
            'id_kuis' => $this->kuisUlangan->id_kuis,
            'nilai' => 40,
            'status_pengerjaan' => 'final',
            'percobaan_ke' => 1,
            'tanggal' => now(),
            'status_validasi' => 'belum divalidasi',
        ]);

        $guruLain = User::create([
            'id_user' => 'U003',
            'nama_lengkap' => 'Guru Lain',
            'role' => 'guru',
            'status' => 'aktif',
            'wajib_ganti_password' => false,
            'password' => bcrypt('password'),
        ]);

        $this->actingAs($guruLain);
        $this->get(route('guru.nilai.index'))->assertDontSee($this->kuisUlangan->judul);
        $this->get(route('guru.nilai.show', $pengerjaan->id_mengerjakan))->assertForbidden();
        $this->patch(route('guru.nilai.validasi', $pengerjaan->id_mengerjakan))->assertForbidden();
        $this->post(route('guru.nilai.remedial', $pengerjaan->id_mengerjakan))->assertForbidden();

        $api = new GuruNilaiController();
        $request = Request::create('/api/guru/nilai', 'GET');
        $request->setUserResolver(fn () => $guruLain);

        $this->assertSame([], $api->index($request)->getData(true)['data']);
        try {
            $api->show($request, $pengerjaan);
            $this->fail('Guru lain tidak boleh membaca detail nilai.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }

        try {
            $api->validasi($request, $pengerjaan);
            $this->fail('Guru lain tidak boleh memvalidasi nilai.');
        } catch (HttpException $exception) {
            $this->assertSame(403, $exception->getStatusCode());
        }
    }

    public function test_api_guru_hanya_dapat_memvalidasi_nilai_yang_belum_divalidasi(): void
    {
        $pengerjaan = Mengerjakan::create([
            'id_user' => $this->siswa->id_user,
            'id_kuis' => $this->kuisUlangan->id_kuis,
            'nilai' => null,
            'status_pengerjaan' => 'final',
            'percobaan_ke' => 1,
            'tanggal' => now(),
            'status_validasi' => 'menunggu penilaian',
        ]);

        $request = Request::create('/api/guru/nilai/'.$pengerjaan->id_mengerjakan.'/validasi', 'PATCH');
        $request->setUserResolver(fn () => $this->guru);

        try {
            (new GuruNilaiController())->validasi($request, $pengerjaan);
            $this->fail('Nilai yang menunggu penilaian tidak boleh divalidasi langsung.');
        } catch (HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
        }

        $this->assertDatabaseHas('mengerjakan', [
            'id_mengerjakan' => $pengerjaan->id_mengerjakan,
            'status_validasi' => 'menunggu penilaian',
        ]);
    }

    public function test_web_dan_api_memakai_alur_penilaian_essay_yang_sama(): void
    {
        $kuis = Kuis::create([
            'id_kuis' => 'K_ESSAY',
            'judul' => 'Kuis Essay',
            'kategori' => 'ulangan',
            'tingkat_kesulitan' => 'sedang',
            'id_mapel' => $this->mapel->id_mapel,
            'id_tahun_ajaran' => 'TA001',
            'alokasi_waktu' => 20,
            'kkm' => 75,
            'id_user' => $this->guru->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);
        $soal = Soal::create([
            'id_soal' => 'SE001',
            'id_kuis' => $kuis->id_kuis,
            'tipe' => 'essay',
            'pertanyaan' => 'Jelaskan konsepnya.',
            'bobot' => 50,
        ]);

        $buatPengerjaan = function (int $percobaan) use ($kuis): Mengerjakan {
            return Mengerjakan::create([
                'id_user' => $this->siswa->id_user,
                'id_kuis' => $kuis->id_kuis,
                'nilai' => null,
                'status_pengerjaan' => 'final',
                'percobaan_ke' => $percobaan,
                'tanggal' => now(),
                'status_validasi' => 'menunggu penilaian',
            ]);
        };

        $pengerjaanApi = $buatPengerjaan(1);
        $essayApi = NilaiEssay::create([
            'id_mengerjakan' => $pengerjaanApi->id_mengerjakan,
            'id_soal' => $soal->id_soal,
            'jawaban_teks' => 'Jawaban siswa.',
        ]);
        $request = Request::create('/api/guru/nilai/'.$pengerjaanApi->id_mengerjakan.'/essay/'.$soal->id_soal, 'POST', [
            'nilai' => '35',
            'catatan_guru' => 'Cukup baik.',
        ]);
        $request->setUserResolver(fn () => $this->guru);

        $responseApi = (new GuruNilaiController())->nilaiEssay(
            $request,
            $pengerjaanApi,
            $soal,
            new KuisEssayGradingService(new KuisPenilaianService())
        );

        $this->assertSame('70.00', $responseApi->getData(true)['data']['nilai']);
        $this->assertDatabaseHas('nilai_essay', [
            'id_nilai_essay' => $essayApi->id_nilai_essay,
            'id_penilai' => $this->guru->id_user,
        ]);

        $pengerjaanWeb = $buatPengerjaan(2);
        NilaiEssay::create([
            'id_mengerjakan' => $pengerjaanWeb->id_mengerjakan,
            'id_soal' => $soal->id_soal,
            'jawaban_teks' => 'Jawaban siswa kedua.',
        ]);

        $this->actingAs($this->guru)
            ->post(route('guru.nilai.essay.store', [
                'mengerjakan' => $pengerjaanWeb->id_mengerjakan,
                'soal' => $soal->id_soal,
            ]), ['nilai' => '40'])
            ->assertRedirect();

        $this->assertDatabaseHas('mengerjakan', [
            'id_mengerjakan' => $pengerjaanWeb->id_mengerjakan,
            'nilai' => 80,
            'status_validasi' => 'sudah divalidasi',
        ]);
    }

    public function test_penilaian_essay_membatasi_panjang_catatan_guru(): void
    {
        $kuis = Kuis::create([
            'id_kuis' => 'K_ESSAY',
            'judul' => 'Kuis Essay',
            'kategori' => 'ulangan',
            'tingkat_kesulitan' => 'sedang',
            'id_mapel' => $this->mapel->id_mapel,
            'id_tahun_ajaran' => 'TA001',
            'alokasi_waktu' => 20,
            'kkm' => 75,
            'id_user' => $this->guru->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);
        $soal = Soal::create([
            'id_soal' => 'SE001',
            'id_kuis' => $kuis->id_kuis,
            'tipe' => 'essay',
            'pertanyaan' => 'Jelaskan konsepnya.',
            'bobot' => 50,
        ]);
        $pengerjaan = Mengerjakan::create([
            'id_user' => $this->siswa->id_user,
            'id_kuis' => $kuis->id_kuis,
            'nilai' => null,
            'status_pengerjaan' => 'final',
            'percobaan_ke' => 1,
            'tanggal' => now(),
            'status_validasi' => 'menunggu penilaian',
        ]);
        NilaiEssay::create([
            'id_mengerjakan' => $pengerjaan->id_mengerjakan,
            'id_soal' => $soal->id_soal,
            'jawaban_teks' => 'Jawaban siswa.',
        ]);

        $this->actingAs($this->guru)
            ->post(route('guru.nilai.essay.store', [
                'mengerjakan' => $pengerjaan->id_mengerjakan,
                'soal' => $soal->id_soal,
            ]), [
                'nilai' => '40',
                'catatan_guru' => str_repeat('x', 10001),
            ])
            ->assertSessionHasErrors('catatan_guru');

        $this->assertDatabaseHas('mengerjakan', [
            'id_mengerjakan' => $pengerjaan->id_mengerjakan,
            'status_validasi' => 'menunggu penilaian',
        ]);
    }

    public function test_guru_bisa_tugaskan_remedial_dari_menu_nilai_untuk_ulangan_tidak_lulus(): void
    {
        $mengerjakan = Mengerjakan::create([
            'id_user' => $this->siswa->id_user,
            'id_kuis' => $this->kuisUlangan->id_kuis,
            'nilai' => 40.00,
            'status_pengerjaan' => 'final',
            'percobaan_ke' => 1,
            'tanggal' => now(),
            'status_validasi' => 'sudah divalidasi',
        ]);

        $this->actingAs($this->guru);

        // Akses halaman nilai, pastikan tombol 'Berikan Remedial' tampil
        $resIndex = $this->get(route('guru.nilai.index'));
        $resIndex->assertOk();
        $resIndex->assertSee('⚡ Berikan Remedial');

        // Submit penugasan remedial
        $response = $this->post(route('guru.nilai.remedial', $mengerjakan->id_mengerjakan));
        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Verifikasi database: remedial_siswa terisi
        $this->assertDatabaseHas('remedial_siswa', [
            'id_mengerjakan_asal' => $mengerjakan->id_mengerjakan,
            'id_guru' => $this->guru->id_user,
            'status' => 'ditugaskan',
        ]);

        // Setelah ditugaskan, tombol berubah jadi badge '✓ Remedial Ditugaskan'
        $resAfter = $this->get(route('guru.nilai.index'));
        $resAfter->assertSee('✓ Remedial Ditugaskan');
    }

    public function test_guru_tidak_bisa_tugaskan_remedial_jika_bukan_ulangan(): void
    {
        $mengerjakan = Mengerjakan::create([
            'id_user' => $this->siswa->id_user,
            'id_kuis' => $this->kuisHarian->id_kuis,
            'nilai' => 40.00,
            'status_pengerjaan' => 'final',
            'percobaan_ke' => 1,
            'tanggal' => now(),
            'status_validasi' => 'sudah divalidasi',
        ]);

        $this->actingAs($this->guru);

        // Kuis harian tidak boleh diberikan remedial (karena siswa langsung retake)
        $response = $this->post(route('guru.nilai.remedial', $mengerjakan->id_mengerjakan));
        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseMissing('remedial_siswa', [
            'id_mengerjakan_asal' => $mengerjakan->id_mengerjakan,
        ]);
    }

    public function test_siswa_nilai_tampil_berhirarki_mapel_bab_kuis(): void
    {
        $this->actingAs($this->siswa);

        // 1. Level 1: Pilihan Mapel
        $resL1 = $this->get(route('siswa.nilai'));
        $resL1->assertOk();
        $resL1->assertSee('Pilih Mata Pelajaran');
        $resL1->assertSee($this->mapel->nama_mapel);

        // 2. Level 2: Daftar Bab Materi ala Foto 2
        $resL2 = $this->get(route('siswa.nilai', ['mapel' => $this->mapel->id_mapel]));
        $resL2->assertOk();
        $resL2->assertSee('Daftar Bab Materi');
        $resL2->assertSee($this->materi->judul);
        $resL2->assertSee('bab-num-badge');

        // 3. Level 3: Rincian Nilai Kuis Bab Terpilih
        $resL3 = $this->get(route('siswa.nilai', [
            'mapel' => $this->mapel->id_mapel,
            'materi' => $this->materi->id_materi,
        ]));
        $resL3->assertOk();
        $resL3->assertSee('Hasil Kuis — ' . $this->materi->judul);
    }

    public function test_siswa_materi_tampil_berbasis_mapel_lalu_materi(): void
    {
        $this->actingAs($this->siswa);

        // 1. Level 1: Pilih Mapel
        $resL1 = $this->get(route('siswa.materi.index'));
        $resL1->assertOk();
        $resL1->assertSee('Materi Pembelajaran');
        $resL1->assertSee($this->mapel->nama_mapel);

        // 2. Level 2: Daftar Materi per Mapel
        $resL2 = $this->get(route('siswa.materi.index', ['mapel' => $this->mapel->id_mapel]));
        $resL2->assertOk();
        $resL2->assertSee($this->mapel->nama_mapel);
        $resL2->assertSee($this->materi->judul);
        $resL2->assertSee('materi-num-badge');
    }

    public function test_siswa_materi_menampilkan_tombol_dan_daftar_kuis_terkait(): void
    {
        $this->actingAs($this->siswa);

        // Hubungkan materi ke kuis yang berstatus terbit dan aktif
        DB::table('materi_kuis')->insert([
            'id_materi' => $this->materi->id_materi,
            'id_kuis' => $this->kuisUlangan->id_kuis,
        ]);

        // Cek halaman index materi level 2
        $resIndex = $this->get(route('siswa.materi.index', ['mapel' => $this->mapel->id_mapel]));
        $resIndex->assertOk();
        $resIndex->assertSee('Kuis Materi');
        $expectedKuisLink = e(route('siswa.kuis.index', ['mapel' => $this->mapel->id_mapel, 'materi' => $this->materi->id_materi]));
        $resIndex->assertSee($expectedKuisLink, false);

        // Cek halaman show materi
        $resShow = $this->get(route('siswa.materi.show', $this->materi->id_materi));
        $resShow->assertOk();
        $resShow->assertSee('Kerjakan Kuis');
        $resShow->assertSee($expectedKuisLink, false);
        $resShow->assertSee($this->kuisUlangan->judul);
        $resShow->assertSee(route('siswa.kuis.mulai', $this->kuisUlangan->id_kuis));
    }

    public function test_siswa_yang_punya_remedial_melihatnya_di_halaman_nilai(): void
    {
        // 1. Buat kuis remedial
        $kuisRemedial = Kuis::create([
            'id_kuis' => 'K_REM_01',
            'judul' => 'Remedial Ulangan Harian 1',
            'kategori' => 'remedial',
            'tingkat_kesulitan' => 'sedang',
            'id_mapel' => $this->mapel->id_mapel,
            'id_tahun_ajaran' => 'TA001',
            'alokasi_waktu' => 20,
            'kkm' => 75.00,
            'id_user' => $this->guru->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);

        Remedial::create([
            'id_kuis_remedial' => $kuisRemedial->id_kuis,
            'id_kuis_asal' => $this->kuisUlangan->id_kuis,
        ]);

        // 2. Buat riwayat pengerjaan siswa yang gagal
        $mengerjakan = Mengerjakan::create([
            'id_user' => $this->siswa->id_user,
            'id_kuis' => $this->kuisUlangan->id_kuis,
            'nilai' => 50.00,
            'status_pengerjaan' => 'final',
            'percobaan_ke' => 1,
            'tanggal' => now(),
            'status_validasi' => 'divalidasi',
        ]);

        // 3. Tugaskan remedial ke siswa
        $remedialSiswa = RemedialSiswa::create([
            'id_kuis_remedial' => $kuisRemedial->id_kuis,
            'id_mengerjakan_asal' => $mengerjakan->id_mengerjakan,
            'id_guru' => $this->guru->id_user,
            'tanggal_ditugaskan' => now()->toDateString(),
            'status' => 'ditugaskan',
        ]);

        $this->actingAs($this->siswa);

        // 4. Kunjungi halaman nilai siswa
        $response = $this->get(route('siswa.nilai'));
        $response->assertOk();

        // 5. Pastikan bagian tugas remedial tampil dengan data yang tepat
        $response->assertSee('Tugas Remedial Saya');
        $response->assertSee($kuisRemedial->judul);
        $response->assertSee($this->kuisUlangan->judul);
        $response->assertSee('50.00'); // Nilai awal
        $response->assertSee(route('siswa.remedial.kerjakan', $remedialSiswa->id_remedial_siswa));
        $response->assertSee('Kerjakan Remedial');

        // 6. Pastikan menu sidebar 'Remedial' sudah tidak ada lagi
        $response->assertDontSee('<span>Remedial</span>', false);
        $response->assertDontSee('href="' . route('siswa.remedial.index') . '"', false);

        // 7. Route siswa.remedial.index tetap ada dan dapat diakses (misal dari dashboard)
        $resRemedialPage = $this->get(route('siswa.remedial.index'));
        $resRemedialPage->assertOk();
        $resRemedialPage->assertSee('Tugas Remedial Saya');
        $resRemedialPage->assertSee($kuisRemedial->judul);
    }
}
