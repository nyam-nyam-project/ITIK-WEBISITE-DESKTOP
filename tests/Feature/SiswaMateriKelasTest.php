<?php

namespace Tests\Feature;

use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Soal;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Memastikan akses materi dan kuis dibatasi berdasarkan kelas siswa di tahun aktif.
 */
class SiswaMateriKelasTest extends TestCase
{
    private User $siswa;        // punya kelas A (KLS001 → MP001)
    private User $siswaLain;    // punya kelas B (KLS002 → MP002)
    private User $siswaTanpaKelas;
    private Materi $materiMP001;
    private Materi $materiMP002;
    private Kuis $kuisMP001;
    private Kuis $kuisMP002;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertTrue($this->app->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('sqlite', DB::connection()->getDriverName());

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
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('mapel', function (Blueprint $table) {
            $table->string('id_mapel', 50)->primary();
            $table->string('nama_mapel', 100);
            $table->string('kode_mapel', 20)->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_aktif')->default(true);
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

        Schema::create('kelas', function (Blueprint $table) {
            $table->string('id_kelas', 50)->primary();
            $table->string('nama_kelas', 100);
            $table->string('tingkat', 20)->nullable();
            $table->string('id_tahun_ajaran', 50)->nullable();
            $table->boolean('is_aktif')->default(true);
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

        Schema::create('progress', function (Blueprint $table) {
            $table->string('id_progress', 50)->primary();
            $table->string('status', 50)->nullable();
            $table->date('tanggal')->nullable();
            $table->string('id_materi', 50);
            $table->string('id_user', 50);
        });

        Schema::create('kuis', function (Blueprint $table) {
            $table->string('id_kuis', 50)->primary();
            $table->string('judul', 150);
            $table->text('deskripsi')->nullable();
            $table->string('kategori', 50);
            $table->string('tingkat_kesulitan', 20)->default('mudah');
            $table->string('id_mapel', 50);
            $table->string('id_tahun_ajaran', 50)->nullable();
            $table->unsignedInteger('alokasi_waktu')->nullable();
            $table->decimal('kkm', 5, 2)->nullable();
            $table->dateTime('waktu_mulai')->nullable();
            $table->dateTime('waktu_selesai')->nullable();
            $table->string('id_user', 50)->nullable();
            $table->string('status_publikasi', 20)->default('draft');
            $table->string('id_publisher', 50)->nullable();
            $table->boolean('is_aktif')->default(true);
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
            $table->unsignedInteger('percobaan_ke')->default(1);
            $table->dateTime('waktu_mulai');
            $table->dateTime('waktu_selesai')->nullable();
            $table->timestamps();
        });

        Schema::create('draft_jawaban', function (Blueprint $table) {
            $table->increments('id_draft');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->string('id_soal', 50);
            $table->string('jawaban_dipilih', 10)->nullable();
            $table->timestamps();
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

        Schema::create('remedial_siswa', function (Blueprint $table) {
            $table->increments('id_remedial_siswa');
            $table->string('id_kuis_remedial', 50);
            $table->unsignedInteger('id_mengerjakan_asal');
            $table->string('id_guru', 50)->nullable();
            $table->date('tanggal_ditugaskan')->nullable();
            $table->string('status', 20)->default('ditugaskan');
        });
    }

    private function seedScenario(): void
    {
        // Guru pembuat materi & kuis
        User::create([
            'id_user'               => 'G001',
            'nama_lengkap'          => 'Guru Satu',
            'role'                  => 'guru',
            'status'                => 'aktif',
            'wajib_ganti_password'  => false,
            'password'              => bcrypt('password'),
        ]);

        // Siswa Kelas A (KLS001 → MP001)
        $this->siswa = User::create([
            'id_user'               => 'S001',
            'nomor_induk'           => '001',
            'nama_lengkap'          => 'Siswa Kelas A',
            'role'                  => 'siswa',
            'status'                => 'aktif',
            'wajib_ganti_password'  => false,
            'password'              => bcrypt('password'),
        ]);

        // Siswa Kelas B (KLS002 → MP002)
        $this->siswaLain = User::create([
            'id_user'               => 'S002',
            'nomor_induk'           => '002',
            'nama_lengkap'          => 'Siswa Kelas B',
            'role'                  => 'siswa',
            'status'                => 'aktif',
            'wajib_ganti_password'  => false,
            'password'              => bcrypt('password'),
        ]);

        // Siswa tanpa kelas
        $this->siswaTanpaKelas = User::create([
            'id_user'               => 'S003',
            'nomor_induk'           => '003',
            'nama_lengkap'          => 'Siswa Tanpa Kelas',
            'role'                  => 'siswa',
            'status'                => 'aktif',
            'wajib_ganti_password'  => false,
            'password'              => bcrypt('password'),
        ]);

        TahunAjaran::create([
            'id_tahun_ajaran' => 'TA001',
            'nama'            => '2026/2027',
            'is_aktif'        => true,
        ]);

        // Mapel
        Mapel::create(['id_mapel' => 'MP001', 'nama_mapel' => 'TIK', 'is_aktif' => true]);
        Mapel::create(['id_mapel' => 'MP002', 'nama_mapel' => 'Matematika', 'is_aktif' => true]);

        // Kelas
        DB::table('kelas')->insert([
            ['id_kelas' => 'KLS001', 'nama_kelas' => 'X RPL 1', 'tingkat' => 'X', 'id_tahun_ajaran' => 'TA001', 'is_aktif' => true],
            ['id_kelas' => 'KLS002', 'nama_kelas' => 'X RPL 2', 'tingkat' => 'X', 'id_tahun_ajaran' => 'TA001', 'is_aktif' => true],
        ]);

        // Anggota kelas
        DB::table('anggota_kelas')->insert([
            ['id_kelas' => 'KLS001', 'id_user' => 'S001'],
            ['id_kelas' => 'KLS002', 'id_user' => 'S002'],
        ]);

        // Mapel kelas
        DB::table('mapel_kelas')->insert([
            ['id_mapel' => 'MP001', 'id_kelas' => 'KLS001'],
            ['id_mapel' => 'MP002', 'id_kelas' => 'KLS002'],
        ]);

        // Materi
        $this->materiMP001 = Materi::create([
            'id_materi'   => 'M001',
            'judul'       => 'Dasar Jaringan',
            'id_mapel'    => 'MP001',
            'id_user'     => 'G001',
        ]);

        $this->materiMP002 = Materi::create([
            'id_materi'   => 'M002',
            'judul'       => 'Aljabar',
            'id_mapel'    => 'MP002',
            'id_user'     => 'G001',
        ]);

        // Kuis
        $this->kuisMP001 = Kuis::create([
            'id_kuis'          => 'K001',
            'judul'            => 'Kuis TIK Kelas A',
            'kategori'         => 'pre-test',
            'tingkat_kesulitan'=> 'mudah',
            'id_mapel'         => 'MP001',
            'id_tahun_ajaran'  => 'TA001',
            'alokasi_waktu'    => 20,
            'kkm'              => 75,
            'id_user'          => 'G001',
            'status_publikasi' => 'terbit',
            'is_aktif'         => true,
        ]);

        $this->kuisMP002 = Kuis::create([
            'id_kuis'          => 'K002',
            'judul'            => 'Kuis MTK Kelas B',
            'kategori'         => 'pre-test',
            'tingkat_kesulitan'=> 'mudah',
            'id_mapel'         => 'MP002',
            'id_tahun_ajaran'  => 'TA001',
            'alokasi_waktu'    => 20,
            'kkm'              => 75,
            'id_user'          => 'G001',
            'status_publikasi' => 'terbit',
            'is_aktif'         => true,
        ]);

        Soal::create([
            'id_soal'    => 'S001',
            'id_kuis'    => 'K001',
            'pertanyaan' => 'Soal 1 TIK',
            'opsi_a'     => 'A',
            'opsi_b'     => 'B',
            'jawaban'    => 'A',
            'bobot'      => 100,
        ]);

        Soal::create([
            'id_soal'    => 'S002',
            'id_kuis'    => 'K002',
            'pertanyaan' => 'Soal 1 MTK',
            'opsi_a'     => 'A',
            'opsi_b'     => 'B',
            'jawaban'    => 'A',
            'bobot'      => 100,
        ]);
    }

    // =========================================================================
    // 1. Siswa kelas A tidak melihat materi atau kuis mapel kelas B
    // =========================================================================

    public function test_siswa_kelas_a_tidak_melihat_materi_mapel_kelas_b(): void
    {
        $response = $this->actingAs($this->siswa)->get(route('siswa.materi.index'));

        $response->assertOk();
        $response->assertSee('TIK');
        $response->assertDontSee('Matematika');
    }

    public function test_siswa_kelas_a_tidak_melihat_kuis_mapel_kelas_b(): void
    {
        $response = $this->actingAs($this->siswa)->get(route('siswa.kuis.index'));

        $response->assertOk();
        $response->assertSee('TIK');
        $response->assertDontSee('Matematika');
        $response->assertDontSee('Kuis MTK Kelas B');
    }

    // =========================================================================
    // 2. URL langsung ditolak (403)
    // =========================================================================

    public function test_url_langsung_materi_mapel_kelas_b_ditolak(): void
    {
        // Siswa A mencoba akses halaman list materi mapel MP002
        $responseIndex = $this->actingAs($this->siswa)
            ->get(route('siswa.materi.index', ['mapel' => 'MP002']));
        $responseIndex->assertForbidden();

        // Siswa A mencoba akses langsung detail materi M002 (milik MP002)
        $responseShow = $this->actingAs($this->siswa)
            ->get(route('siswa.materi.show', $this->materiMP002->id_materi));
        $responseShow->assertForbidden();
    }

    public function test_url_langsung_kuis_mapel_kelas_b_ditolak(): void
    {
        // Siswa A mencoba akses halaman list kuis mapel MP002
        $responseIndex = $this->actingAs($this->siswa)
            ->get(route('siswa.kuis.index', ['mapel' => 'MP002']));
        $responseIndex->assertForbidden();

        // Siswa A mencoba mulai kuis K002 (milik MP002)
        $responseMulai = $this->actingAs($this->siswa)
            ->get(route('siswa.kuis.mulai', $this->kuisMP002->id_kuis));
        $responseMulai->assertForbidden();

        // Siswa A mencoba akses langsung halaman kerjakan kuis K002
        $responseKerjakan = $this->actingAs($this->siswa)
            ->get(route('siswa.kuis.kerjakan', $this->kuisMP002->id_kuis));
        $responseKerjakan->assertForbidden();

        // Siswa A mencoba submit kuis K002
        $responseSubmit = $this->actingAs($this->siswa)
            ->post(route('siswa.kuis.submit', $this->kuisMP002->id_kuis));
        $responseSubmit->assertForbidden();
    }

    // =========================================================================
    // 3. Siswa tanpa kelas tidak melihat apa pun & melihat pesan
    // =========================================================================

    public function test_siswa_tanpa_kelas_tidak_melihat_apa_pun_di_materi(): void
    {
        $response = $this->actingAs($this->siswaTanpaKelas)->get(route('siswa.materi.index'));

        $response->assertOk();
        $response->assertSee('Kamu Belum Terdaftar di Kelas');
        $response->assertDontSee('mapel-card-s', false);
        $response->assertDontSee('Dasar Jaringan');
        $response->assertDontSee('Aljabar');

        // URL langsung materi ditolak
        $responseDirect = $this->actingAs($this->siswaTanpaKelas)
            ->get(route('siswa.materi.show', $this->materiMP001->id_materi));
        $responseDirect->assertForbidden();
    }

    public function test_siswa_tanpa_kelas_tidak_melihat_apa_pun_di_kuis(): void
    {
        $response = $this->actingAs($this->siswaTanpaKelas)->get(route('siswa.kuis.index'));

        $response->assertOk();
        $response->assertSee('Kamu Belum Terdaftar di Kelas');
        $response->assertDontSee('mapel-card-s', false);
        $response->assertDontSee('Kuis TIK Kelas A');
        $response->assertDontSee('Kuis MTK Kelas B');

        // URL langsung kuis ditolak
        $responseMulai = $this->actingAs($this->siswaTanpaKelas)
            ->get(route('siswa.kuis.mulai', $this->kuisMP001->id_kuis));
        $responseMulai->assertForbidden();

        $responseIndex = $this->actingAs($this->siswaTanpaKelas)
            ->get(route('siswa.kuis.index', ['mapel' => 'MP001']));
        $responseIndex->assertForbidden();
    }

    public function test_kuis_remedial_tetap_dapat_diakses_sesuai_penugasan(): void
    {
        // Buat kuis remedial untuk MP002 yang bukan mapel kelas siswa A
        $kuisRemedial = Kuis::create([
            'id_kuis'          => 'K003_REM',
            'judul'            => 'Remedial MTK',
            'kategori'         => 'remedial',
            'tingkat_kesulitan'=> 'mudah',
            'id_mapel'         => 'MP002',
            'id_tahun_ajaran'  => 'TA001',
            'alokasi_waktu'    => 20,
            'kkm'              => 75,
            'id_user'          => 'G001',
            'status_publikasi' => 'terbit',
            'is_aktif'         => true,
        ]);

        Soal::create([
            'id_soal'    => 'S003',
            'id_kuis'    => 'K003_REM',
            'pertanyaan' => 'Soal Remedial',
            'opsi_a'     => 'A',
            'opsi_b'     => 'B',
            'jawaban'    => 'A',
            'bobot'      => 100,
        ]);

        // Rekam pengerjaan asal
        $idMengerjakan = DB::table('mengerjakan')->insertGetId([
            'id_kuis'          => $this->kuisMP002->id_kuis,
            'id_user'          => $this->siswa->id_user,
            'nilai'            => 50,
            'status_pengerjaan'=> 'final',
            'percobaan_ke'     => 1,
            'tanggal'          => now(),
        ]);

        // Tugas remedial
        DB::table('remedial_siswa')->insert([
            'id_mengerjakan_asal' => $idMengerjakan,
            'id_kuis_remedial'   => 'K003_REM',
            'status'             => 'ditugaskan',
        ]);

        // Siswa A (meski MP002 bukan mapel kelasnya) tetap bisa melihat di halaman remedial dan memulai kuisnya
        $responseRemedial = $this->actingAs($this->siswa)->get(route('siswa.remedial.index'));
        $responseRemedial->assertOk();
        $responseRemedial->assertSee('Remedial MTK');

        $responseMulai = $this->actingAs($this->siswa)
            ->get(route('siswa.kuis.mulai', $kuisRemedial->id_kuis));
        $responseMulai->assertRedirect(route('siswa.kuis.kerjakan', $kuisRemedial->id_kuis));
    }
}
