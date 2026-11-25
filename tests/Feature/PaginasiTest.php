<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PaginasiTest extends TestCase
{
    private User $admin;

    private User $guru;

    private Kelas $kelas;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->withoutVite();

        Schema::create('tm_user', function (Blueprint $table): void {
            $table->string('id_user', 50)->primary();
            $table->string('nomor_induk', 50)->nullable()->unique();
            $table->string('nama_lengkap', 100);
            $table->string('role', 20);
            $table->string('status', 20)->default('aktif');
            $table->string('kelas', 50)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('password');
            $table->boolean('wajib_ganti_password')->default(false);
            $table->string('dibuat_oleh', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('tm_tahun_ajaran', function (Blueprint $table): void {
            $table->string('id_tahun_ajaran', 50)->primary();
            $table->string('nama', 20)->unique();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_aktif')->default(false);
            $table->timestamps();
        });

        Schema::create('tm_kelas', function (Blueprint $table): void {
            $table->string('id_kelas', 50)->primary();
            $table->string('nama_kelas', 50);
            $table->string('tingkat', 10);
            $table->string('id_tahun_ajaran', 50);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('tm_mapel', function (Blueprint $table): void {
            $table->string('id_mapel', 50)->primary();
            $table->string('kode_mapel', 20)->unique();
            $table->string('nama_mapel', 100);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('tp_anggota_kelas', function (Blueprint $table): void {
            $table->string('id_kelas', 50);
            $table->string('id_user', 50);
        });

        Schema::create('tp_mapel_kelas', function (Blueprint $table): void {
            $table->string('id_mapel', 50);
            $table->string('id_kelas', 50);
        });

        Schema::create('tr_pengajaran', function (Blueprint $table): void {
            $table->string('id_pengajaran', 50)->primary();
            $table->string('id_user', 50);
            $table->string('id_kelas', 50)->nullable();
            $table->string('id_mapel', 50);
            $table->string('id_tahun_ajaran', 50)->nullable();
            $table->timestamps();
        });

        Schema::create('tm_materi', function (Blueprint $table): void {
            $table->string('id_materi', 50)->primary();
            $table->string('id_user', 50);
            $table->string('id_mapel', 50);
            $table->string('judul', 150);
            $table->string('bab', 100)->nullable();
            $table->string('kelas', 50)->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('tm_kuis', function (Blueprint $table): void {
            $table->string('id_kuis', 50)->primary();
            $table->string('id_user', 50);
            $table->string('id_mapel', 50);
            $table->string('id_tahun_ajaran', 50)->nullable();
            $table->string('judul', 150);
            $table->string('kategori', 50)->default('kuis harian');
            $table->integer('alokasi_waktu')->default(15);
            $table->decimal('kkm', 5, 2)->nullable();
            $table->string('status', 30)->default('terbit');
            $table->string('status_publikasi', 30)->default('terbit');
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('tr_mengerjakan', function (Blueprint $table): void {
            $table->string('id_mengerjakan', 50)->primary();
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->integer('percobaan_ke')->default(1);
            $table->string('status_pengerjaan', 30)->default('final');
            $table->string('status_validasi', 40)->nullable();
            $table->string('id_validator', 50)->nullable();
            $table->decimal('nilai', 5, 2)->nullable();
            $table->timestamp('tanggal')->nullable();
            $table->timestamp('mulai_pada')->nullable();
            $table->timestamp('selesai_pada')->nullable();
            $table->timestamps();
        });

        Schema::create('tr_remedial_siswa', function (Blueprint $table): void {
            $table->string('id_remedial', 50);
            $table->string('id_user', 50);
            $table->string('id_mengerjakan_asal', 50)->nullable();
            $table->string('id_kuis_remedial', 50)->nullable();
            $table->string('status', 30)->default('ditugaskan');
        });

        $this->admin = User::create([
            'id_user' => 'ADM_PAG_1',
            'nomor_induk' => 'ADM01',
            'nama_lengkap' => 'Admin Paginasi',
            'role' => 'admin',
            'status' => 'aktif',
            'password' => Hash::make('password123'),
            'wajib_ganti_password' => false,
        ]);

        $this->guru = User::create([
            'id_user' => 'G_PAG_1',
            'nomor_induk' => '198001',
            'nama_lengkap' => 'Guru Paginasi',
            'role' => 'guru',
            'status' => 'aktif',
            'password' => Hash::make('password123'),
            'wajib_ganti_password' => false,
        ]);

        $ta = TahunAjaran::create([
            'id_tahun_ajaran' => 'TA_PAG',
            'nama' => '2026/2027 Ganjil',
            'is_aktif' => true,
        ]);

        $this->kelas = Kelas::create([
            'id_kelas' => 'KLS_PAG',
            'nama_kelas' => 'XI TKJ 1',
            'tingkat' => '11',
            'id_tahun_ajaran' => $ta->id_tahun_ajaran,
            'is_aktif' => true,
        ]);

        $mapel = Mapel::create([
            'id_mapel' => 'MP_PAG',
            'kode_mapel' => 'TIK',
            'nama_mapel' => 'Dasar TIK',
            'is_aktif' => true,
        ]);

        DB::table('tp_mapel_kelas')->insert([
            'id_mapel' => $mapel->id_mapel,
            'id_kelas' => $this->kelas->id_kelas,
        ]);

        DB::table('tr_pengajaran')->insert([
            'id_pengajaran' => 'PJ_PAG',
            'id_user' => $this->guru->id_user,
            'id_kelas' => $this->kelas->id_kelas,
            'id_mapel' => $mapel->id_mapel,
            'id_tahun_ajaran' => $ta->id_tahun_ajaran,
        ]);

        $kuis = Kuis::create([
            'id_kuis' => 'KUIS_PAG',
            'id_user' => $this->guru->id_user,
            'id_mapel' => $mapel->id_mapel,
            'id_tahun_ajaran' => $ta->id_tahun_ajaran,
            'judul' => 'Kuis Paginasi',
            'kategori' => 'kuis harian',
            'alokasi_waktu' => 15,
            'kkm' => 75,
            'status' => 'terbit',
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);

        for ($i = 1; $i <= 31; $i++) {
            $kode = str_pad((string) $i, 3, '0', STR_PAD_LEFT);
            $siswa = User::create([
                'id_user' => 'S_PAG_'.$kode,
                'nomor_induk' => '2026'.$kode,
                'nama_lengkap' => 'Siswa '.$kode,
                'role' => 'siswa',
                'status' => $i <= 28 ? 'aktif' : 'nonaktif',
                'password' => Hash::make('password123'),
                'wajib_ganti_password' => false,
            ]);

            DB::table('tp_anggota_kelas')->insert([
                'id_kelas' => $this->kelas->id_kelas,
                'id_user' => $siswa->id_user,
            ]);

            DB::table('tr_mengerjakan')->insert([
                'id_mengerjakan' => 'MK_PAG_'.$kode,
                'id_kuis' => $kuis->id_kuis,
                'id_user' => $siswa->id_user,
                'percobaan_ke' => 1,
                'status_pengerjaan' => 'final',
                'status_validasi' => 'menunggu validasi',
                'nilai' => $i <= 25 ? 80 : 50,
                'tanggal' => now()->subMinutes(31 - $i),
            ]);
        }
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('tr_remedial_siswa');
        Schema::dropIfExists('tr_mengerjakan');
        Schema::dropIfExists('tm_kuis');
        Schema::dropIfExists('tm_materi');
        Schema::dropIfExists('tr_pengajaran');
        Schema::dropIfExists('tp_mapel_kelas');
        Schema::dropIfExists('tp_anggota_kelas');
        Schema::dropIfExists('tm_mapel');
        Schema::dropIfExists('tm_kelas');
        Schema::dropIfExists('tm_tahun_ajaran');
        Schema::dropIfExists('tm_user');

        parent::tearDown();
    }

    public function test_halaman_pertama_berisi_25_baris_dan_halaman_kedua_sisanya(): void
    {
        $halaman1 = $this->actingAs($this->admin)->get(route('admin.siswa.index'));
        $halaman1->assertOk();
        $this->assertCount(25, $halaman1->viewData('siswa'));
        $halaman1->assertSee('menampilkan 1 sampai 25 dari 31 data', false);
        $halaman1->assertSee('aria-label="Navigasi halaman"', false);
        $halaman1->assertSee('aria-current="page"', false);

        $halaman2 = $this->actingAs($this->admin)->get(route('admin.siswa.index', ['page' => 2]));
        $halaman2->assertOk();
        $this->assertCount(6, $halaman2->viewData('siswa'));
        $halaman2->assertSee('menampilkan 26 sampai 31 dari 31 data', false);
    }

    public function test_per_page_yang_tidak_diizinkan_dikembalikan_ke_25(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.siswa.index', ['per_page' => 999]));
        $response->assertOk();
        $this->assertSame(25, $response->viewData('siswa')->perPage());
        $this->assertCount(25, $response->viewData('siswa'));

        $response50 = $this->actingAs($this->admin)->get(route('admin.siswa.index', ['per_page' => 50]));
        $response50->assertOk();
        $this->assertSame(50, $response50->viewData('siswa')->perPage());
        $this->assertCount(31, $response50->viewData('siswa'));

        $response10 = $this->actingAs($this->admin)->get(route('admin.siswa.index', ['per_page' => 10]));
        $response10->assertOk();
        $this->assertSame(10, $response10->viewData('siswa')->perPage());
        $this->assertCount(10, $response10->viewData('siswa'));
        $response10->assertSee('menampilkan 1 sampai 10 dari 31 data', false);
    }

    public function test_filter_tetap_berlaku_di_halaman_kedua(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.siswa.index', [
            'status' => 'aktif',
            'page' => 2,
        ]));

        $response->assertOk();
        $paginator = $response->viewData('siswa');
        $this->assertSame(28, $paginator->total());
        $this->assertCount(3, $paginator);
        $response->assertSee('status=aktif', false);
        foreach ($paginator as $siswa) {
            $this->assertSame('aktif', $siswa->status);
        }
    }

    public function test_total_dan_rata_rata_tidak_berubah_dibanding_sebelum_paginasi(): void
    {
        // Rata-rata keseluruhan dari 31 data: (25 * 80 + 6 * 50) / 31 = 2300 / 31 = 74.2
        $rataKeseluruhan = round((25 * 80 + 6 * 50) / 31, 1);

        $halaman1 = $this->actingAs($this->guru)->get(route('guru.nilai.index'));
        $halaman1->assertOk();
        $hasil1 = $halaman1->viewData('hasil');
        $kelas1 = $halaman1->viewData('kelasList')->firstWhere('id_kelas', $this->kelas->id_kelas);

        $this->assertCount(25, $hasil1);
        $this->assertSame(31, $hasil1->total());
        $this->assertSame($rataKeseluruhan, $kelas1->rata_nilai);

        $halaman2 = $this->actingAs($this->guru)->get(route('guru.nilai.index', ['page' => 2]));
        $halaman2->assertOk();
        $hasil2 = $halaman2->viewData('hasil');
        $kelas2 = $halaman2->viewData('kelasList')->firstWhere('id_kelas', $this->kelas->id_kelas);

        $this->assertCount(6, $hasil2);
        $this->assertSame(31, $hasil2->total());
        $this->assertSame($rataKeseluruhan, $kelas2->rata_nilai);
    }
}
