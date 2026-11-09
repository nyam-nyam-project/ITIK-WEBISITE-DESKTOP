<?php

namespace Tests\Feature;

use App\Models\Mapel;
use App\Models\Materi;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Memastikan ProgressController membatasi akses materi berdasarkan kelas siswa.
 */
class SiswaMateriKelasTest extends TestCase
{
    private User $siswa;        // punya kelas, mapel MP001
    private User $siswaLain;    // punya kelas, mapel MP002
    private User $siswaTanpaKelas;
    private Materi $materiMP001;
    private Materi $materiMP002;

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
            $table->string('id_user', 50)->nullable();
            $table->string('status_publikasi', 20)->default('draft');
            $table->boolean('is_aktif')->default(true);
            $table->timestamp('created_at')->nullable()->useCurrent();
        });

        Schema::create('materi_kuis', function (Blueprint $table) {
            $table->increments('id_materi_kuis');
            $table->string('id_materi', 50);
            $table->string('id_kuis', 50);
        });
    }

    private function seedScenario(): void
    {
        // Guru pembuat materi
        User::create([
            'id_user'               => 'G001',
            'nama_lengkap'          => 'Guru Satu',
            'role'                  => 'guru',
            'status'                => 'aktif',
            'wajib_ganti_password'  => false,
            'password'              => bcrypt('password'),
        ]);

        // Siswa dengan kelas (KLS001 → MP001)
        $this->siswa = User::create([
            'id_user'               => 'S001',
            'nomor_induk'           => '001',
            'nama_lengkap'          => 'Siswa Kelas A',
            'role'                  => 'siswa',
            'status'                => 'aktif',
            'wajib_ganti_password'  => false,
            'password'              => bcrypt('password'),
        ]);

        // Siswa dengan kelas berbeda (KLS002 → MP002)
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

        // Mapel
        Mapel::create(['id_mapel' => 'MP001', 'nama_mapel' => 'TIK', 'is_aktif' => true]);
        Mapel::create(['id_mapel' => 'MP002', 'nama_mapel' => 'Matematika', 'is_aktif' => true]);

        // Kelas
        DB::table('kelas')->insert([
            ['id_kelas' => 'KLS001', 'nama_kelas' => 'X RPL 1', 'tingkat' => 'X', 'is_aktif' => true],
            ['id_kelas' => 'KLS002', 'nama_kelas' => 'X RPL 2', 'tingkat' => 'X', 'is_aktif' => true],
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
    }

    // =========================================================================
    // TEST index()
    // =========================================================================

    public function test_index_hanya_menampilkan_mapel_kelas_siswa(): void
    {
        $response = $this->actingAs($this->siswa)
            ->get(route('siswa.materi.index'));

        $response->assertOk();
        // Kartu MP001 (kelas siswa) tampil — cek nama mapel di dalam mapel-title-s
        $response->assertSee('mapel-card-s', false);
        // MP002 (bukan kelas siswa) tidak tampil
        $response->assertDontSee('Matematika');
    }

    public function test_index_siswa_tanpa_kelas_melihat_pesan(): void
    {
        $response = $this->actingAs($this->siswaTanpaKelas)
            ->get(route('siswa.materi.index'));

        $response->assertOk();
        $response->assertSee('Belum Terdaftar di Kelas');
    }

    public function test_index_siswa_tanpa_kelas_tidak_melihat_mapel_manapun(): void
    {
        $response = $this->actingAs($this->siswaTanpaKelas)
            ->get(route('siswa.materi.index'));

        $response->assertOk();
        // Kartu mapel (class mapel-card-s) tidak dirender sama sekali
        $response->assertDontSee('mapel-card-s', false);
        // Judul nama mapel (dari db, bukan dari nav) tidak tampil
        $response->assertDontSee('Dasar Jaringan');
        $response->assertDontSee('Aljabar');
    }

    // =========================================================================
    // TEST show()
    // =========================================================================

    public function test_show_materi_yang_sesuai_kelas_bisa_diakses(): void
    {
        $response = $this->actingAs($this->siswa)
            ->get(route('siswa.materi.show', $this->materiMP001->id_materi));

        $response->assertOk();
        $response->assertSee('Dasar Jaringan');
    }

    public function test_show_materi_di_luar_kelas_ditolak_403(): void
    {
        // siswa hanya punya MP001; coba akses materi MP002
        $response = $this->actingAs($this->siswa)
            ->get(route('siswa.materi.show', $this->materiMP002->id_materi));

        $response->assertForbidden();
    }

    public function test_show_siswa_tanpa_kelas_ditolak_403(): void
    {
        $response = $this->actingAs($this->siswaTanpaKelas)
            ->get(route('siswa.materi.show', $this->materiMP001->id_materi));

        $response->assertForbidden();
    }

    public function test_show_siswa_lain_bisa_akses_mapel_kelasnya_sendiri(): void
    {
        // siswaLain hanya punya MP002
        $response = $this->actingAs($this->siswaLain)
            ->get(route('siswa.materi.show', $this->materiMP002->id_materi));

        $response->assertOk();
        $response->assertSee('Aljabar');
    }

    public function test_show_siswa_lain_tidak_bisa_akses_mapel_kelas_berbeda(): void
    {
        $response = $this->actingAs($this->siswaLain)
            ->get(route('siswa.materi.show', $this->materiMP001->id_materi));

        $response->assertForbidden();
    }
}
