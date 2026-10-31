<?php

namespace Tests\Feature;

use App\Models\DraftJawaban;
use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Mengerjakan;
use App\Models\RemedialSiswa;
use App\Models\SesiKuis;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Tes aturan waktu kuis:
 *   1. Waktu berjalan sejak Mulai; menutup tab tidak mereset timer.
 *   2. Saat waktu habis, jawaban otomatis ter-submit; soal kosong = salah (nilai 0).
 *   3. Halaman hasil menampilkan durasi pengerjaan.
 *
 * Pola: SQLite :memory:, Schema::create manual, TANPA RefreshDatabase / migrate.
 */
class SiswaKuisWaktuTest extends TestCase
{
    private User $siswa;
    private User $guru;
    private Kuis $kuis;

    protected function setUp(): void
    {
        parent::setUp();

        // Guard fail-closed: hanya boleh jalan di SQLite :memory:
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

    // -------------------------------------------------------------------------
    // Schema
    // -------------------------------------------------------------------------

    private function createSchema(): void
    {
        Schema::create('user', function (Blueprint $table) {
            $table->string('id_user', 50)->primary();
            $table->string('nomor_induk', 50)->nullable()->unique();
            $table->string('nama_lengkap', 100);
            $table->enum('role', ['admin', 'guru', 'siswa']);
            $table->string('status', 20)->default('aktif');
            $table->string('kelas', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('password');
            $table->boolean('wajib_ganti_password')->default(false);
            $table->string('dibuat_oleh', 50)->nullable();
            $table->timestamps();
            $table->foreign('dibuat_oleh')->references('id_user')->on('user')->nullOnDelete();
        });

        Schema::create('tahun_ajaran', function (Blueprint $table) {
            $table->string('id_tahun_ajaran', 50)->primary();
            $table->string('nama', 20)->unique();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_aktif')->default(false);
            $table->timestamps();
        });

        Schema::create('mapel', function (Blueprint $table) {
            $table->string('id_mapel', 50)->primary();
            $table->string('kode_mapel', 20)->unique();
            $table->string('nama_mapel', 100);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('kuis', function (Blueprint $table) {
            $table->string('id_kuis', 50)->primary();
            $table->string('judul', 150);
            $table->text('deskripsi')->nullable();
            $table->string('kategori', 50);
            $table->enum('tingkat_kesulitan', ['mudah', 'sedang', 'sulit'])->default('mudah');
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
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->foreign('id_mapel')->references('id_mapel')->on('mapel')->restrictOnDelete();
            $table->foreign('id_tahun_ajaran')->references('id_tahun_ajaran')->on('tahun_ajaran')->restrictOnDelete();
            $table->foreign('id_user')->references('id_user')->on('user')->restrictOnDelete();
            $table->foreign('id_publisher')->references('id_user')->on('user')->nullOnDelete();
        });

        Schema::create('soal', function (Blueprint $table) {
            $table->string('id_soal', 50)->primary();
            $table->string('id_kuis', 50);
            $table->text('pertanyaan');
            foreach (['a', 'b', 'c', 'd', 'e'] as $option) {
                $table->string('opsi_'.$option)->nullable();
            }
            $table->string('jawaban', 5)->nullable();
            $table->decimal('bobot', 5, 2)->default(1);
            $table->string('tingkat_kesulitan', 50)->nullable();
            $table->foreign('id_kuis')->references('id_kuis')->on('kuis')->cascadeOnDelete();
        });

        Schema::create('sesi_kuis', function (Blueprint $table) {
            $table->increments('id_sesi');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->unsignedInteger('percobaan_ke')->default(1);
            $table->timestamp('waktu_mulai')->useCurrent();
            $table->timestamp('waktu_selesai')->nullable();
            $table->unique(['id_kuis', 'id_user', 'percobaan_ke']);
            $table->foreign('id_kuis')->references('id_kuis')->on('kuis')->cascadeOnDelete();
            $table->foreign('id_user')->references('id_user')->on('user')->cascadeOnDelete();
        });

        Schema::create('mengerjakan', function (Blueprint $table) {
            $table->increments('id_mengerjakan');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->decimal('nilai', 5, 2)->nullable();
            $table->date('tanggal')->nullable();
            $table->integer('percobaan_ke')->default(1);
            $table->string('status_pengerjaan', 20)->default('final');
            $table->string('status_validasi', 50)->nullable()->default('belum divalidasi');
            $table->string('id_validator', 50)->nullable();
            $table->unique(['id_kuis', 'id_user', 'percobaan_ke']);
            $table->foreign('id_kuis')->references('id_kuis')->on('kuis')->restrictOnDelete();
            $table->foreign('id_user')->references('id_user')->on('user')->cascadeOnDelete();
        });

        Schema::create('draft_jawaban', function (Blueprint $table) {
            $table->increments('id_draft');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->string('id_soal', 50);
            $table->string('jawaban_dipilih', 5)->nullable();
            $table->timestamps();
            $table->unique(['id_kuis', 'id_user', 'id_soal']);
            $table->foreign('id_kuis')->references('id_kuis')->on('kuis')->cascadeOnDelete();
            $table->foreign('id_user')->references('id_user')->on('user')->cascadeOnDelete();
            $table->foreign('id_soal')->references('id_soal')->on('soal')->cascadeOnDelete();
        });

        Schema::create('remedial', function (Blueprint $table) {
            $table->increments('id_remedial');
            $table->string('id_kuis_remedial', 50)->unique();
            $table->string('id_kuis_asal', 50);
            $table->foreign('id_kuis_remedial')->references('id_kuis')->on('kuis')->cascadeOnDelete();
            $table->foreign('id_kuis_asal')->references('id_kuis')->on('kuis')->restrictOnDelete();
        });

        Schema::create('remedial_siswa', function (Blueprint $table) {
            $table->increments('id_remedial_siswa');
            $table->integer('id_mengerjakan');
            $table->string('id_kuis_remedial', 50);
            $table->string('status', 20)->default('ditugaskan');
            $table->foreign('id_mengerjakan')->references('id_mengerjakan')->on('mengerjakan')->cascadeOnDelete();
            $table->foreign('id_kuis_remedial')->references('id_kuis_remedial')->on('remedial')->cascadeOnDelete();
        });
    }

    // -------------------------------------------------------------------------
    // Seed
    // -------------------------------------------------------------------------

    private function seedScenario(): void
    {
        $this->guru = User::create([
            'id_user' => 'U001', 'nama_lengkap' => 'Guru', 'role' => 'guru',
            'status' => 'aktif', 'email' => 'guru@test.test', 'password' => 'secret',
        ]);

        $this->siswa = User::create([
            'id_user' => 'U002', 'nama_lengkap' => 'Siswa', 'role' => 'siswa',
            'status' => 'aktif', 'email' => 'siswa@test.test', 'password' => 'secret',
        ]);

        TahunAjaran::create(['id_tahun_ajaran' => 'TA001', 'nama' => '2026/2027', 'is_aktif' => true]);

        Mapel::create([
            'id_mapel' => 'MP001', 'kode_mapel' => 'TIK', 'nama_mapel' => 'TIK', 'is_aktif' => true,
        ]);

        $this->kuis = Kuis::create([
            'id_kuis'          => 'K001',
            'judul'            => 'Kuis Algoritma',
            'kategori'         => 'pre-test',
            'tingkat_kesulitan'=> 'mudah',
            'id_mapel'         => 'MP001',
            'id_tahun_ajaran'  => 'TA001',
            'alokasi_waktu'    => 30, // 30 menit
            'kkm'              => 75,
            'id_user'          => 'U001',
            'status_publikasi' => 'terbit',
            'is_aktif'         => true,
        ]);

        // Satu soal bobot 100, jawaban benar = A
        DB::table('soal')->insert([
            'id_soal'    => 'S001',
            'id_kuis'    => 'K001',
            'pertanyaan' => 'Apa itu algoritma?',
            'opsi_a'     => 'Langkah logis',
            'opsi_b'     => 'Monitor',
            'jawaban'    => 'A',
            'bobot'      => 100,
        ]);
    }

    // -------------------------------------------------------------------------
    // Helper
    // -------------------------------------------------------------------------

    /** Buat sesi dengan waktu mulai yang bisa diatur. */
    private function buatSesi(string $idKuis, string $idUser, int $percobaanKe, string $waktuMulai, ?string $waktuSelesai = null): SesiKuis
    {
        return SesiKuis::create([
            'id_kuis'      => $idKuis,
            'id_user'      => $idUser,
            'percobaan_ke' => $percobaanKe,
            'waktu_mulai'  => $waktuMulai,
            'waktu_selesai'=> $waktuSelesai,
        ]);
    }

    /** Simpan draft jawaban siswa. */
    private function simpanDraft(string $idSoal, ?string $jawaban): void
    {
        DraftJawaban::updateOrCreate(
            ['id_kuis' => 'K001', 'id_user' => 'U002', 'id_soal' => $idSoal],
            ['jawaban_dipilih' => $jawaban]
        );
    }

    // -------------------------------------------------------------------------
    // Tests
    // -------------------------------------------------------------------------

    /**
     * Percobaan ke-2 memakai sesi aktif yang benar (bukan sesi paling lama).
     * Bug lama: firstOrFail() tanpa filter → ambil sesi pertama yang sudah selesai.
     */
    public function test_percobaan_kedua_memakai_sesi_aktif_terbaru(): void
    {
        // Sesi pertama sudah selesai (30 menit lalu)
        $this->buatSesi('K001', 'U002', 1, now()->subMinutes(60)->toDateTimeString(), now()->subMinutes(30)->toDateTimeString());
        Mengerjakan::create([
            'id_kuis' => 'K001', 'id_user' => 'U002', 'nilai' => 50,
            'tanggal' => now()->toDateString(), 'percobaan_ke' => 1,
            'status_pengerjaan' => 'final', 'status_validasi' => 'belum divalidasi',
        ]);

        // Sesi kedua aktif, mulai 5 menit lalu (masih dalam alokasi 30 menit)
        $sesi2 = $this->buatSesi('K001', 'U002', 2, now()->subMinutes(5)->toDateTimeString());

        $this->actingAs($this->siswa)
            ->get(route('siswa.kuis.kerjakan', 'K001'))
            ->assertOk()
            ->assertViewIs('siswa.kuis.kerjakan');

        // Memastikan sesi yang sedang berjalan adalah percobaan ke-2
        $this->assertSame(2, $sesi2->fresh()->percobaan_ke);
        $this->assertNull($sesi2->fresh()->waktu_selesai);
    }

    /**
     * Menutup tab tidak mereset timer: saat siswa kembali, waktu terus berjalan
     * dari waktu_mulai awal, bukan dari sekarang.
     */
    public function test_waktu_terus_berjalan_saat_siswa_kembali_setelah_menutup_tab(): void
    {
        // Siswa mulai kuis 10 menit lalu, belum submit
        $waktuMulai = now()->subMinutes(10);
        $this->buatSesi('K001', 'U002', 1, $waktuMulai->toDateTimeString());

        $response = $this->actingAs($this->siswa)
            ->get(route('siswa.kuis.kerjakan', 'K001'))
            ->assertOk()
            ->assertViewIs('siswa.kuis.kerjakan');

        // batasWaktu yang dikirim ke view = waktu_mulai + 30 menit (bukan now + 30 menit)
        $batasWaktu = $response->viewData('batasWaktu');
        $selisihDetik = abs($batasWaktu->diffInSeconds($waktuMulai->addMinutes(30)));
        $this->assertLessThan(5, $selisihDetik, 'batasWaktu harus dihitung dari waktu_mulai, bukan sekarang');
    }

    /**
     * Auto-submit saat sesi expired tanpa browser (via selesaikanKedaluwarsa).
     * Soal yang tidak dijawab dianggap salah (nilai 0).
     */
    public function test_auto_submit_via_scheduler_saat_expired_tanpa_jawaban_nilai_nol(): void
    {
        // Sesi sudah lewat 31 menit (lebih dari alokasi 30 menit)
        $this->buatSesi('K001', 'U002', 1, now()->subMinutes(31)->toDateTimeString());

        // Tidak ada draft jawaban sama sekali
        $this->assertDatabaseCount('draft_jawaban', 0);

        $service = app(\App\Services\KuisSubmissionService::class);
        $jumlah = $service->selesaikanKedaluwarsa();

        $this->assertSame(1, $jumlah);

        // Sesi harus sudah ditutup
        $this->assertDatabaseMissing('sesi_kuis', ['id_kuis' => 'K001', 'id_user' => 'U002', 'waktu_selesai' => null]);

        // Nilai harus 0 karena tidak ada jawaban
        $mengerjakan = Mengerjakan::where('id_kuis', 'K001')->where('id_user', 'U002')->first();
        $this->assertNotNull($mengerjakan);
        $this->assertSame(0.0, (float) $mengerjakan->nilai);
        $this->assertSame(1, $mengerjakan->percobaan_ke);
        $this->assertSame('final', $mengerjakan->status_pengerjaan);
    }

    /**
     * Auto-submit saat expired dengan jawaban benar → nilai 100.
     */
    public function test_auto_submit_saat_expired_dengan_jawaban_benar_nilai_100(): void
    {
        $this->buatSesi('K001', 'U002', 1, now()->subMinutes(31)->toDateTimeString());
        $this->simpanDraft('S001', 'A'); // jawaban benar

        $service = app(\App\Services\KuisSubmissionService::class);
        $service->selesaikanKedaluwarsa();

        $mengerjakan = Mengerjakan::where('id_kuis', 'K001')->where('id_user', 'U002')->first();
        $this->assertSame(100.0, (float) $mengerjakan->nilai);

        // Draft harus terhapus
        $this->assertDatabaseCount('draft_jawaban', 0);
    }

    /**
     * Tidak ada duplikat mengerjakan bila manual submit dan scheduler balapan.
     * submit() harus idempoten: panggilan kedua mengembalikan null.
     */
    public function test_tidak_ada_duplikat_jika_manual_submit_dan_scheduler_balapan(): void
    {
        $sesi = $this->buatSesi('K001', 'U002', 1, now()->subMinutes(5)->toDateTimeString());

        $service = app(\App\Services\KuisSubmissionService::class);

        // Submit pertama (manual)
        $hasil1 = $service->submit($sesi);
        $this->assertNotNull($hasil1);

        // Submit kedua (scheduler) harus mengembalikan null (sesi sudah ditutup)
        $hasil2 = $service->submit($sesi, true);
        $this->assertNull($hasil2);

        // Hanya ada 1 baris di mengerjakan
        $this->assertDatabaseCount('mengerjakan', 1);
    }

    /**
     * Jika sesi lama tertinggal dan data mengerjakan untuk percobaan itu sudah ada di DB,
     * submit() tidak boleh melempar UniqueConstraintViolationException, melainkan menutup sesi.
     */
    public function test_submit_sesi_lama_yang_sudah_memiliki_mengerjakan_tidak_duplicate_entry(): void
    {
        // Simulasi di database: pengerjaan sudah ada untuk K001, U002, percobaan 1
        Mengerjakan::create([
            'id_kuis' => 'K001', 'id_user' => 'U002', 'nilai' => 80,
            'tanggal' => now()->toDateString(), 'percobaan_ke' => 1,
            'status_pengerjaan' => 'final', 'status_validasi' => 'sudah divalidasi',
        ]);

        // Sesi tertinggal dengan waktu_selesai = null untuk percobaan yang sama
        $sesi = $this->buatSesi('K001', 'U002', 1, now()->subMinutes(60)->toDateTimeString());

        $service = app(\App\Services\KuisSubmissionService::class);
        $hasil = $service->submit($sesi, true);

        // Tidak crash, sesi tertutup, dan data mengerjakan tetap 1
        $this->assertNotNull($hasil);
        $this->assertNotNull($sesi->fresh()->waktu_selesai);
        $this->assertDatabaseCount('mengerjakan', 1);
    }

    /**
     * Autosave ditolak setelah deadline.
     */
    public function test_autosave_ditolak_setelah_deadline(): void
    {
        // Sesi sudah lewat 31 menit
        $this->buatSesi('K001', 'U002', 1, now()->subMinutes(31)->toDateTimeString());

        $response = $this->actingAs($this->siswa)
            ->postJson(route('siswa.kuis.jawaban', 'K001'), [
                'id_soal' => 'S001',
                'jawaban' => 'A',
            ]);

        $response->assertStatus(422);
        $this->assertDatabaseCount('draft_jawaban', 0);
    }

    /**
     * Autosave berhasil saat masih dalam waktu.
     */
    public function test_autosave_diterima_saat_masih_dalam_waktu(): void
    {
        $this->buatSesi('K001', 'U002', 1, now()->subMinutes(5)->toDateTimeString());

        $response = $this->actingAs($this->siswa)
            ->postJson(route('siswa.kuis.jawaban', 'K001'), [
                'id_soal' => 'S001',
                'jawaban' => 'A',
            ]);

        $response->assertOk()->assertJsonPath('success', true);
        $this->assertDatabaseCount('draft_jawaban', 1);
    }

    /**
     * Batas waktu ulangan memakai waktu_selesai kuis jika lebih cepat dari alokasi.
     */
    public function test_batas_waktu_ulangan_memakai_waktu_selesai_kuis_jika_lebih_cepat(): void
    {
        // Kuis ulangan dengan waktu_selesai 10 menit dari sekarang
        $kuisUlangan = Kuis::create([
            'id_kuis'          => 'K002',
            'judul'            => 'Ulangan',
            'kategori'         => 'ulangan',
            'tingkat_kesulitan'=> 'mudah',
            'id_mapel'         => 'MP001',
            'id_tahun_ajaran'  => 'TA001',
            'alokasi_waktu'    => 60, // alokasi 60 menit
            'kkm'              => 75,
            'id_user'          => 'U001',
            'status_publikasi' => 'terbit',
            'is_aktif'         => true,
            'waktu_mulai'      => now()->subMinutes(5),
            'waktu_selesai'    => now()->addMinutes(10), // habis 10 menit lagi
        ]);

        DB::table('soal')->insert([
            'id_soal' => 'S002', 'id_kuis' => 'K002', 'pertanyaan' => 'Soal ulangan',
            'jawaban' => 'A', 'bobot' => 100,
        ]);

        // Sesi mulai 5 menit lalu → alokasi = 60 menit → deadline normalnya +55 menit lagi
        // Tapi waktu_selesai kuis = +10 menit → seharusnya batasWaktu = waktu_selesai kuis
        $sesi = $this->buatSesi('K002', 'U002', 1, now()->subMinutes(5)->toDateTimeString());

        $service = app(\App\Services\KuisSubmissionService::class);
        $batasWaktu = $service->batasWaktu($sesi);

        $selisihDetik = abs($batasWaktu->diffInSeconds($kuisUlangan->waktu_selesai));
        $this->assertLessThan(5, $selisihDetik, 'batasWaktu harus menggunakan waktu_selesai kuis ulangan');
    }

    /**
     * Sesi kuis yang kuis-nya nonaktif (is_aktif = false) tidak ikut di-close
     * oleh selesaikanKedaluwarsa(), supaya riwayat tidak hilang.
     */
    public function test_sesi_kuis_nonaktif_tidak_ditutup_oleh_scheduler(): void
    {
        // Nonaktifkan kuis
        $this->kuis->update(['is_aktif' => false]);

        // Buat sesi yang sudah expired
        $this->buatSesi('K001', 'U002', 1, now()->subMinutes(60)->toDateTimeString());

        $service = app(\App\Services\KuisSubmissionService::class);
        $jumlah = $service->selesaikanKedaluwarsa();

        $this->assertSame(0, $jumlah);

        // Sesi masih terbuka
        $this->assertDatabaseHas('sesi_kuis', ['id_kuis' => 'K001', 'id_user' => 'U002', 'waktu_selesai' => null]);
        $this->assertDatabaseCount('mengerjakan', 0);
    }

    /**
     * selesaikanMilikSiswa() hanya menutup sesi yang memang sudah expired.
     * Sesi yang masih aktif tidak disentuh.
     */
    public function test_selesaikan_milik_siswa_tidak_menyentuh_sesi_yang_masih_aktif(): void
    {
        // Sesi masih aktif (mulai 5 menit lalu, alokasi 30 menit)
        $sesi = $this->buatSesi('K001', 'U002', 1, now()->subMinutes(5)->toDateTimeString());

        $service = app(\App\Services\KuisSubmissionService::class);
        $jumlah = $service->selesaikanMilikSiswa('U002');

        $this->assertSame(0, $jumlah);
        $this->assertNull($sesi->fresh()->waktu_selesai);
        $this->assertDatabaseCount('mengerjakan', 0);
    }

    /**
     * Halaman hasil menampilkan durasi pengerjaan (variabel $durasiTampil dikirim ke view).
     */
    public function test_halaman_hasil_menampilkan_durasi_pengerjaan(): void
    {
        $waktuMulai  = now()->subMinutes(15)->subSeconds(30); // 15 menit 30 detik lalu
        $waktuSelesai = now();

        // Buat sesi yang sudah selesai
        $sesi = $this->buatSesi(
            'K001', 'U002', 1,
            $waktuMulai->toDateTimeString(),
            $waktuSelesai->toDateTimeString()
        );

        Mengerjakan::create([
            'id_kuis' => 'K001', 'id_user' => 'U002', 'nilai' => 100,
            'tanggal' => now()->toDateString(), 'percobaan_ke' => 1,
            'status_pengerjaan' => 'final', 'status_validasi' => 'belum divalidasi',
        ]);

        $response = $this->actingAs($this->siswa)
            ->get(route('siswa.kuis.hasil', 'K001'))
            ->assertOk()
            ->assertViewIs('siswa.kuis.hasil');

        $durasiTampil = $response->viewData('durasiTampil');
        $this->assertNotNull($durasiTampil, '$durasiTampil harus dikirim ke view');
        $this->assertStringContainsString('menit', $durasiTampil);
        $this->assertStringContainsString('detik', $durasiTampil);
    }

    /**
     * Hasil lama tanpa sesi: $durasiTampil = null (view menampilkan "Belum tercatat").
     */
    public function test_halaman_hasil_tanpa_sesi_durasi_null(): void
    {
        // Langsung buat mengerjakan tanpa sesi
        Mengerjakan::create([
            'id_kuis' => 'K001', 'id_user' => 'U002', 'nilai' => 80,
            'tanggal' => now()->toDateString(), 'percobaan_ke' => 1,
            'status_pengerjaan' => 'final', 'status_validasi' => 'belum divalidasi',
        ]);

        $response = $this->actingAs($this->siswa)
            ->get(route('siswa.kuis.hasil', 'K001'))
            ->assertOk();

        $this->assertNull($response->viewData('durasiTampil'));
    }

    /**
     * Saat kerjakan() dipanggil tanpa sesi aktif, redirect ke daftar kuis.
     */
    public function test_kerjakan_tanpa_sesi_aktif_redirect_ke_daftar(): void
    {
        // Buat sesi yang sudah ditutup
        $this->buatSesi('K001', 'U002', 1, now()->subMinutes(60)->toDateTimeString(), now()->subMinutes(30)->toDateTimeString());

        $this->actingAs($this->siswa)
            ->get(route('siswa.kuis.kerjakan', 'K001'))
            ->assertRedirect(route('siswa.kuis.index'));
    }

    /**
     * Saat kerjakan() dipanggil dengan sesi aktif yang sudah expired,
     * auto-submit lalu redirect ke hasil (bukan ke daftar kuis).
     */
    public function test_kerjakan_sesi_expired_auto_submit_redirect_ke_hasil(): void
    {
        $this->simpanDraft('S001', 'A');
        $this->buatSesi('K001', 'U002', 1, now()->subMinutes(31)->toDateTimeString());

        $this->actingAs($this->siswa)
            ->get(route('siswa.kuis.kerjakan', 'K001'))
            ->assertRedirect(route('siswa.kuis.hasil', 'K001'));

        // Sesi sudah ditutup dan nilai masuk
        $this->assertDatabaseMissing('sesi_kuis', ['waktu_selesai' => null]);
        $this->assertDatabaseCount('mengerjakan', 1);
    }

    /**
     * Mulai() dengan sesi expired yang ada di DB → auto-submit → redirect ke hasil.
     */
    public function test_mulai_sesi_expired_auto_submit_redirect_ke_hasil(): void
    {
        $this->buatSesi('K001', 'U002', 1, now()->subMinutes(31)->toDateTimeString());

        $this->actingAs($this->siswa)
            ->get(route('siswa.kuis.mulai', 'K001'))
            ->assertRedirect(route('siswa.kuis.hasil', 'K001'));

        $this->assertDatabaseMissing('sesi_kuis', ['waktu_selesai' => null]);
        $this->assertDatabaseCount('mengerjakan', 1);
    }
}
