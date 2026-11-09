<?php

namespace Tests\Feature;

use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Mengerjakan;
use App\Models\TahunAjaran;
use App\Models\User;
use App\Services\RekapNilaiGuruService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RekapNilaiGuruUnduhTest extends TestCase
{
    private User $guru;
    private User $siswa1;
    private User $siswa2;
    private Mapel $mapelDiajar;
    private Mapel $mapelLain;
    private Kuis $kuis1;
    private Kuis $kuis2;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertTrue($this->app->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());

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
            $table->timestamps();
        });

        Schema::create('mapel', function (Blueprint $table) {
            $table->string('id_mapel', 50)->primary();
            $table->string('nama_mapel', 100);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('kelas', function (Blueprint $table) {
            $table->string('id_kelas', 50)->primary();
            $table->string('nama_kelas', 100);
            $table->string('tingkat', 20)->nullable();
            $table->string('id_tahun_ajaran', 50);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('anggota_kelas', function (Blueprint $table) {
            $table->increments('id_anggota_kelas');
            $table->string('id_kelas', 50);
            $table->string('id_user', 50);
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
            $table->timestamp('created_at')->nullable()->useCurrent();
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

        Schema::create('pengajaran', function (Blueprint $table) {
            $table->increments('id_pengajaran');
            $table->string('id_user', 50);
            $table->string('id_kelas', 50)->nullable();
            $table->string('id_mapel', 50);
        });

        // Tabel pendukung yang digunakan oleh ValidasiNilaiController@index
        Schema::create('remedial_siswa', function (Blueprint $table) {
            $table->increments('id_remedial_siswa');
            $table->string('id_kuis_remedial', 50);
            $table->unsignedInteger('id_mengerjakan_asal');
            $table->string('id_guru', 50);
            $table->date('tanggal_ditugaskan');
            $table->string('status', 20)->default('ditugaskan');
        });

        Schema::create('mapel_kelas', function (Blueprint $table) {
            $table->increments('id_mapel_kelas');
            $table->string('id_kelas', 50);
            $table->string('id_mapel', 50);
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
    }

    private function seedScenario(): void
    {
        $this->guru = User::create([
            'id_user'               => 'G001',
            'nomor_induk'           => '19800101001',
            'nama_lengkap'          => 'Budi Santoso',
            'role'                  => 'guru',
            'status'                => 'aktif',
            'wajib_ganti_password'  => false,
            'password'              => bcrypt('password'),
        ]);

        $this->siswa1 = User::create([
            'id_user'               => 'S001',
            'nomor_induk'           => '0123456789',   // nol di depan
            'nama_lengkap'          => 'Ani Rahayu',
            'role'                  => 'siswa',
            'status'                => 'aktif',
            'wajib_ganti_password'  => false,
            'password'              => bcrypt('password'),
        ]);

        $this->siswa2 = User::create([
            'id_user'               => 'S002',
            'nomor_induk'           => '9876543210',
            'nama_lengkap'          => 'Budi Prasetyo',
            'role'                  => 'siswa',
            'status'                => 'aktif',
            'wajib_ganti_password'  => false,
            'password'              => bcrypt('password'),
        ]);

        TahunAjaran::create([
            'id_tahun_ajaran' => 'TA001',
            'nama'            => '2026/2027',
            'tanggal_mulai'   => '2026-07-01',
            'tanggal_selesai' => '2027-06-30',
            'is_aktif'        => true,
        ]);

        $this->mapelDiajar = Mapel::create([
            'id_mapel'   => 'MP001',
            'nama_mapel' => 'TIK',
            'is_aktif'   => true,
        ]);

        $this->mapelLain = Mapel::create([
            'id_mapel'   => 'MP002',
            'nama_mapel' => 'Matematika',
            'is_aktif'   => true,
        ]);

        // Guru hanya mengajar MP001
        DB::table('pengajaran')->insert([
            'id_user'  => 'G001',
            'id_kelas' => null,
            'id_mapel' => 'MP001',
        ]);

        $this->kuis1 = Kuis::create([
            'id_kuis'          => 'K001',
            'judul'            => 'Ulangan Harian TIK',
            'kategori'         => 'ulangan',
            'tingkat_kesulitan'=> 'sedang',
            'id_mapel'         => 'MP001',
            'id_tahun_ajaran'  => 'TA001',
            'kkm'              => 75.00,
            'id_user'          => 'G001',
            'status_publikasi' => 'terbit',
            'is_aktif'         => true,
        ]);

        $this->kuis2 = Kuis::create([
            'id_kuis'          => 'K002',
            'judul'            => 'Ulangan Matematika',
            'kategori'         => 'ulangan',
            'tingkat_kesulitan'=> 'sedang',
            'id_mapel'         => 'MP002',  // mapel lain
            'id_tahun_ajaran'  => 'TA001',
            'kkm'              => 70.00,
            'id_user'          => 'G001',
            'status_publikasi' => 'terbit',
            'is_aktif'         => true,
        ]);
    }

    // =========================================================================
    // TEST RekapNilaiGuruService
    // =========================================================================

    public function test_rekap_hanya_mapel_dari_pengajaran_guru(): void
    {
        // Siswa 1 mengerjakan kuis mapel TIK (MP001 — diajar guru)
        Mengerjakan::create([
            'id_user'           => 'S001',
            'id_kuis'           => 'K001',
            'nilai'             => 80.00,
            'status_pengerjaan' => 'final',
            'percobaan_ke'      => 1,
            'tanggal'           => now(),
        ]);

        // Siswa 2 mengerjakan kuis mapel Matematika (MP002 — BUKAN mapel guru)
        Mengerjakan::create([
            'id_user'           => 'S002',
            'id_kuis'           => 'K002',
            'nilai'             => 90.00,
            'status_pengerjaan' => 'final',
            'percobaan_ke'      => 1,
            'tanggal'           => now(),
        ]);

        $service = app(RekapNilaiGuruService::class);
        $rekap   = $service->rekapUntukGuru($this->guru, null);

        // Hanya 1 baris: kuis TIK siswa 1
        $this->assertCount(1, $rekap);
        $this->assertEquals('S001', $rekap->first()->siswa->id_user);
        $this->assertEquals('K001', $rekap->first()->kuis->id_kuis);
    }

    public function test_rekap_nilai_terbaik_diambil_dari_banyak_percobaan(): void
    {
        // Siswa 1 mencoba 3 kali — percobaan ke-2 adalah nilai terbaik
        Mengerjakan::create([
            'id_user'           => 'S001',
            'id_kuis'           => 'K001',
            'nilai'             => 60.00,
            'status_pengerjaan' => 'final',
            'percobaan_ke'      => 1,
            'tanggal'           => '2026-10-01 08:00:00',
        ]);
        Mengerjakan::create([
            'id_user'           => 'S001',
            'id_kuis'           => 'K001',
            'nilai'             => 85.00,
            'status_pengerjaan' => 'final',
            'percobaan_ke'      => 2,
            'tanggal'           => '2026-10-02 08:00:00',
        ]);
        Mengerjakan::create([
            'id_user'           => 'S001',
            'id_kuis'           => 'K001',
            'nilai'             => 70.00,
            'status_pengerjaan' => 'final',
            'percobaan_ke'      => 3,
            'tanggal'           => '2026-10-03 08:00:00',
        ]);

        $service = app(RekapNilaiGuruService::class);
        $rekap   = $service->rekapUntukGuru($this->guru, null);

        $this->assertCount(1, $rekap);
        $item = $rekap->first();
        $this->assertEquals(85.00, (float) $item->nilai_terbaik);
        $this->assertEquals(3, $item->percobaan);
    }

    public function test_rekap_tidak_masukkan_pengerjaan_batal(): void
    {
        // Satu percobaan berstatus 'batal' — harus diabaikan
        Mengerjakan::create([
            'id_user'           => 'S001',
            'id_kuis'           => 'K001',
            'nilai'             => 95.00,
            'status_pengerjaan' => 'batal',  // bukan final
            'percobaan_ke'      => 1,
            'tanggal'           => now(),
        ]);

        // Satu percobaan final
        Mengerjakan::create([
            'id_user'           => 'S001',
            'id_kuis'           => 'K001',
            'nilai'             => 70.00,
            'status_pengerjaan' => 'final',
            'percobaan_ke'      => 2,
            'tanggal'           => now(),
        ]);

        $service = app(RekapNilaiGuruService::class);
        $rekap   = $service->rekapUntukGuru($this->guru, null);

        $this->assertCount(1, $rekap);
        // Nilai yang diambil adalah 70.00 (dari percobaan final), bukan 95.00 (dari yang dibatalkan)
        $this->assertEquals(70.00, (float) $rekap->first()->nilai_terbaik);
    }

    public function test_rekap_filter_kelas(): void
    {
        // Buat kelas dan isi anggota
        DB::table('kelas')->insert([
            'id_kelas'        => 'KLS001',
            'nama_kelas'      => 'X RPL 1',
            'tingkat'         => 'X',
            'id_tahun_ajaran' => 'TA001',
            'is_aktif'        => true,
        ]);

        // Hanya siswa1 masuk kelas
        DB::table('anggota_kelas')->insert([
            'id_kelas' => 'KLS001',
            'id_user'  => 'S001',
        ]);

        // Kedua siswa mengerjakan kuis
        Mengerjakan::create([
            'id_user'           => 'S001',
            'id_kuis'           => 'K001',
            'nilai'             => 80.00,
            'status_pengerjaan' => 'final',
            'percobaan_ke'      => 1,
            'tanggal'           => now(),
        ]);

        Mengerjakan::create([
            'id_user'           => 'S002',
            'id_kuis'           => 'K001',
            'nilai'             => 90.00,
            'status_pengerjaan' => 'final',
            'percobaan_ke'      => 1,
            'tanggal'           => now(),
        ]);

        $service = app(RekapNilaiGuruService::class);

        // Filter dengan KLS001: hanya siswa1 yang ada di kelas itu
        $rekap = $service->rekapUntukGuru($this->guru, 'KLS001');
        $this->assertCount(1, $rekap);
        $this->assertEquals('S001', $rekap->first()->siswa->id_user);

        // Tanpa filter: kedua siswa muncul
        $rekap2 = $service->rekapUntukGuru($this->guru, null);
        $this->assertCount(2, $rekap2);
    }

    public function test_rekap_mengembalikan_koleksi_kosong_jika_tidak_ada_pengajaran(): void
    {
        // Guru baru tanpa pengajaran
        $guruBaru = User::create([
            'id_user'               => 'G002',
            'nomor_induk'           => '19900202002',
            'nama_lengkap'          => 'Guru Baru',
            'role'                  => 'guru',
            'status'                => 'aktif',
            'wajib_ganti_password'  => false,
            'password'              => bcrypt('password'),
        ]);

        $service = app(RekapNilaiGuruService::class);
        $rekap   = $service->rekapUntukGuru($guruBaru, null);

        $this->assertCount(0, $rekap);
    }

    // =========================================================================
    // TEST Route unduh CSV
    // =========================================================================

    public function test_unduh_mengembalikan_csv_dengan_bom_utf8(): void
    {
        Mengerjakan::create([
            'id_user'           => 'S001',
            'id_kuis'           => 'K001',
            'nilai'             => 80.00,
            'status_pengerjaan' => 'final',
            'percobaan_ke'      => 1,
            'tanggal'           => now(),
        ]);

        $response = $this->actingAs($this->guru)
            ->get(route('guru.nilai.unduh'));

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');

        // Periksa BOM UTF-8 ada di awal body
        $content = $response->streamedContent();
        $this->assertStringStartsWith("\xEF\xBB\xBF", $content);
    }

    public function test_nisn_ditulis_dengan_format_excel_agar_nol_terjaga(): void
    {
        // siswa1 punya NISN '0123456789' (nol di depan)
        Mengerjakan::create([
            'id_user'           => 'S001',
            'id_kuis'           => 'K001',
            'nilai'             => 80.00,
            'status_pengerjaan' => 'final',
            'percobaan_ke'      => 1,
            'tanggal'           => now(),
        ]);

        $response = $this->actingAs($this->guru)
            ->get(route('guru.nilai.unduh'));

        $response->assertOk();

        $content = $response->streamedContent();
        // fputcsv mengquote nilai yang mengandung tanda kutip:
        // nilai ="0123456789" menjadi "=""0123456789""" di output CSV
        // Kita pastikan format Excel (=") ada dalam output
        $this->assertStringContainsString('=""0123456789""', $content);
    }

    public function test_unduh_csv_hanya_data_mapel_diajar_guru(): void
    {
        // Siswa 1 mengerjakan kuis TIK (diajarkan guru)
        Mengerjakan::create([
            'id_user'           => 'S001',
            'id_kuis'           => 'K001',
            'nilai'             => 80.00,
            'status_pengerjaan' => 'final',
            'percobaan_ke'      => 1,
            'tanggal'           => now(),
        ]);

        // Siswa 2 mengerjakan kuis Matematika (TIDAK diajarkan guru)
        Mengerjakan::create([
            'id_user'           => 'S002',
            'id_kuis'           => 'K002',
            'nilai'             => 90.00,
            'status_pengerjaan' => 'final',
            'percobaan_ke'      => 1,
            'tanggal'           => now(),
        ]);

        $response = $this->actingAs($this->guru)
            ->get(route('guru.nilai.unduh'));

        $response->assertOk();
        $content = $response->streamedContent();

        // TIK ada di CSV
        $this->assertStringContainsString('Ulangan Harian TIK', $content);
        // Matematika tidak ada
        $this->assertStringNotContainsString('Ulangan Matematika', $content);
    }

    public function test_unduh_csv_dengan_filter_kelas(): void
    {
        DB::table('kelas')->insert([
            'id_kelas'        => 'KLS001',
            'nama_kelas'      => 'X RPL 1',
            'tingkat'         => 'X',
            'id_tahun_ajaran' => 'TA001',
            'is_aktif'        => true,
        ]);

        // Hanya siswa1 anggota kelas
        DB::table('anggota_kelas')->insert([
            'id_kelas' => 'KLS001',
            'id_user'  => 'S001',
        ]);

        // Kedua siswa mengerjakan
        Mengerjakan::create([
            'id_user'           => 'S001',
            'id_kuis'           => 'K001',
            'nilai'             => 80.00,
            'status_pengerjaan' => 'final',
            'percobaan_ke'      => 1,
            'tanggal'           => now(),
        ]);

        Mengerjakan::create([
            'id_user'           => 'S002',
            'id_kuis'           => 'K001',
            'nilai'             => 90.00,
            'status_pengerjaan' => 'final',
            'percobaan_ke'      => 1,
            'tanggal'           => now(),
        ]);

        // Filter ke KLS001
        $response = $this->actingAs($this->guru)
            ->get(route('guru.nilai.unduh', ['kelas' => 'KLS001']));

        $response->assertOk();
        $content = $response->streamedContent();

        // Ani Rahayu (siswa1 — anggota kelas) ada
        $this->assertStringContainsString('Ani Rahayu', $content);
        // Budi Prasetyo (siswa2 — bukan anggota kelas) tidak ada
        $this->assertStringNotContainsString('Budi Prasetyo', $content);
    }

    public function test_halaman_nilai_menampilkan_tombol_unduh_csv(): void
    {
        $response = $this->actingAs($this->guru)
            ->get(route('guru.nilai.index'));

        $response->assertOk();
        $response->assertSee(route('guru.nilai.unduh'), false);
        $response->assertSee('Unduh CSV');
    }

    public function test_halaman_nilai_tombol_unduh_menyertakan_filter_kelas_aktif(): void
    {
        // Tambahkan kelas agar kelasList tidak kosong
        DB::table('kelas')->insert([
            'id_kelas'        => 'KLS001',
            'nama_kelas'      => 'X RPL 1',
            'tingkat'         => 'X',
            'id_tahun_ajaran' => 'TA001',
            'is_aktif'        => true,
        ]);

        DB::table('anggota_kelas')->insert([
            'id_kelas' => 'KLS001',
            'id_user'  => 'S001',
        ]);

        $response = $this->actingAs($this->guru)
            ->get(route('guru.nilai.index', ['kelas' => 'KLS001']));

        $response->assertOk();

        // Link unduh harus menyertakan ?kelas=KLS001
        $expected = route('guru.nilai.unduh', ['kelas' => 'KLS001']);
        $response->assertSee(e($expected), false);
    }

    public function test_siswa_tidak_bisa_akses_route_unduh(): void
    {
        $response = $this->actingAs($this->siswa1)
            ->get(route('guru.nilai.unduh'));

        // role:guru middleware harus menolak akses (redirect atau 403)
        $this->assertNotEquals(200, $response->status());
    }
}
