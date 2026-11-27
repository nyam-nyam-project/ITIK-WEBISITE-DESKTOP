<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ArsipMateriKuisTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());

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

        Schema::create('tp_mapel_kelas', function (Blueprint $table): void {
            $table->string('id_mapel', 50);
            $table->string('id_kelas', 50);
            $table->primary(['id_mapel', 'id_kelas']);
        });

        Schema::create('tp_anggota_kelas', function (Blueprint $table): void {
            $table->bigIncrements('id_anggota_kelas');
            $table->string('id_kelas', 50);
            $table->string('id_user', 50);
            $table->timestamp('created_at')->nullable();
            $table->unique(['id_kelas', 'id_user']);
        });

        Schema::create('tr_pengajaran', function (Blueprint $table): void {
            $table->increments('id_pengajaran');
            $table->string('id_user', 50);
            $table->string('id_kelas', 50);
            $table->string('id_mapel', 50);
            $table->timestamp('created_at')->nullable();
            $table->unique(['id_user', 'id_kelas', 'id_mapel']);
        });

        Schema::create('tm_materi', function (Blueprint $table): void {
            $table->string('id_materi', 50)->primary();
            $table->string('judul', 150);
            $table->string('bab', 50)->nullable();
            $table->string('kelas', 50)->nullable();
            $table->string('id_mapel', 50);
            $table->text('deskripsi')->nullable();
            $table->string('isi_materi', 255)->nullable();
            $table->string('link_youtube', 255)->nullable();
            $table->string('id_user', 50);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('tp_materi_kelas', function (Blueprint $table): void {
            $table->string('id_materi', 50);
            $table->string('id_kelas', 50);
            $table->primary(['id_materi', 'id_kelas']);
        });

        Schema::create('tm_kuis', function (Blueprint $table): void {
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
            $table->string('diarsipkan_bersama', 50)->nullable();
            $table->string('tampil_jawaban', 30)->default('setelah_submit');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('tp_materi_kuis', function (Blueprint $table): void {
            $table->string('id_materi', 50);
            $table->string('id_kuis', 50);
            $table->primary(['id_materi', 'id_kuis']);
        });

        Schema::create('tp_kuis_kelas', function (Blueprint $table): void {
            $table->string('id_kuis', 50);
            $table->string('id_kelas', 50);
            $table->primary(['id_kuis', 'id_kelas']);
        });

        Schema::create('tm_soal', function (Blueprint $table): void {
            $table->string('id_soal', 50)->primary();
            $table->text('pertanyaan');
            $table->string('tipe', 30)->default('pilihan_ganda');
            $table->string('id_kuis', 50);
            $table->decimal('bobot', 5, 2)->default(100);
        });

        Schema::create('tr_mengerjakan', function (Blueprint $table): void {
            $table->increments('id_mengerjakan');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->decimal('nilai', 5, 2)->nullable();
            $table->date('tanggal')->nullable();
            $table->integer('percobaan_ke')->default(1);
            $table->string('status_pengerjaan', 20)->default('final');
            $table->string('status_validasi', 50)->default('sudah divalidasi');
            $table->string('id_validator', 50)->nullable();
        });

        Schema::create('tr_progress', function (Blueprint $table): void {
            $table->string('id_progress', 50)->primary();
            $table->string('status', 50)->nullable();
            $table->date('tanggal')->nullable();
            $table->string('id_materi', 50);
            $table->string('id_user', 50);
        });
    }

    public function test_arsip_materi_mengarsipkan_pre_post_test_dan_menjaga_kuis_harian_bersama(): void
    {
        $guru = $this->seedSkenario();

        $this->actingAs($guru)
            ->delete(route('materi.destroy', 'M01'))
            ->assertRedirect(route('materi.index', ['mapel' => 'MP01']))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tm_materi', ['id_materi' => 'M01', 'is_aktif' => 0]);

        // Pre-test, Post-test, dan Kuis Harian eksklusif M01 ikut diarsipkan dengan tanda diarsipkan_bersama = M01
        $this->assertDatabaseHas('tm_kuis', ['id_kuis' => 'K_PRE', 'is_aktif' => 0, 'diarsipkan_bersama' => 'M01']);
        $this->assertDatabaseHas('tm_kuis', ['id_kuis' => 'K_POST', 'is_aktif' => 0, 'diarsipkan_bersama' => 'M01']);
        $this->assertDatabaseHas('tm_kuis', ['id_kuis' => 'K_HARIAN_UNIK', 'is_aktif' => 0, 'diarsipkan_bersama' => 'M01']);

        // Kuis harian yang masih dipakai M02 (aktif) tetap aktif
        $this->assertDatabaseHas('tm_kuis', ['id_kuis' => 'K_HARIAN_SHARED', 'is_aktif' => 1, 'diarsipkan_bersama' => null]);

        // Nilai & progress siswa tidak hilang
        $this->assertDatabaseHas('tr_mengerjakan', ['id_kuis' => 'K_PRE', 'id_user' => 'S001', 'nilai' => 85]);
        $this->assertDatabaseHas('tr_progress', ['id_materi' => 'M01', 'id_user' => 'S001', 'status' => 'selesai']);
    }

    public function test_pulihkan_materi_mengembalikan_kuis_yang_diarsipkan_bersamanya(): void
    {
        $guru = $this->seedSkenario();

        // Kuis arsip manual sebelumnya tidak boleh ikut dipulihkan oleh materi M01
        DB::table('tm_kuis')->insert([
            'id_kuis' => 'K_ARSIP_MANUAL',
            'judul' => 'Kuis Arsip Manual',
            'kategori' => 'kuis harian',
            'id_mapel' => 'MP01',
            'id_tahun_ajaran' => 'TA01',
            'id_user' => 'G001',
            'status_publikasi' => 'draft',
            'is_aktif' => false,
            'diarsipkan_bersama' => null,
        ]);
        DB::table('tp_materi_kuis')->insert(['id_materi' => 'M01', 'id_kuis' => 'K_ARSIP_MANUAL']);

        $this->actingAs($guru)->delete(route('materi.destroy', 'M01'))->assertRedirect();

        $this->actingAs($guru)
            ->patch(route('guru.arsip.materi.pulihkan', 'M01'))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('tm_materi', ['id_materi' => 'M01', 'is_aktif' => 1]);
        $this->assertDatabaseHas('tm_kuis', ['id_kuis' => 'K_PRE', 'is_aktif' => 1, 'diarsipkan_bersama' => null]);
        $this->assertDatabaseHas('tm_kuis', ['id_kuis' => 'K_POST', 'is_aktif' => 1, 'diarsipkan_bersama' => null]);
        $this->assertDatabaseHas('tm_kuis', ['id_kuis' => 'K_HARIAN_UNIK', 'is_aktif' => 1, 'diarsipkan_bersama' => null]);
        $this->assertDatabaseHas('tm_kuis', ['id_kuis' => 'K_ARSIP_MANUAL', 'is_aktif' => 0, 'diarsipkan_bersama' => null]);
    }

    public function test_hapus_permanen_ditolak_untuk_kuis_berriwayat(): void
    {
        $guru = $this->seedSkenario();

        $this->actingAs($guru)->delete(route('materi.destroy', 'M01'))->assertRedirect();

        $this->actingAs($guru)
            ->delete(route('guru.arsip.kuis.hapus-permanen', 'K_PRE'))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('tm_kuis', ['id_kuis' => 'K_PRE']);
        $this->assertDatabaseHas('tr_mengerjakan', ['id_kuis' => 'K_PRE', 'id_user' => 'S001']);
    }

    public function test_konfirmasi_arsip_menampilkan_jumlah_kuis_dan_siswa_terdampak(): void
    {
        $guru = $this->seedSkenario();

        $response = $this->actingAs($guru)->get(route('materi.konfirmasi-arsip', 'M01'));

        $response->assertOk();
        $response->assertSee('Konfirmasi Arsip Materi');
        // 3 kuis ikut diarsipkan (K_PRE, K_POST, K_HARIAN_UNIK) dan 2 siswa aktif terdampak
        $response->assertSee('3 kuis');
        $response->assertSee('2 siswa');
        $response->assertSee('Pre-test Bab 1');
        $response->assertSee('Post-test Bab 1');
        $response->assertDontSee('Kuis Harian Bersama');

        $isiView = file_get_contents(resource_path('views/guru/materi/konfirmasi-arsip.blade.php'));
        $this->assertIsString($isiView);
        $this->assertStringNotContainsString('<script', $isiView);
        $this->assertStringNotContainsString('<style', $isiView);
        $this->assertStringNotContainsString('style=', $isiView);
    }

    private function seedSkenario(): User
    {
        $guru = User::query()->create([
            'id_user' => 'G001',
            'nama_lengkap' => 'Guru Satu',
            'role' => 'guru',
            'status' => 'aktif',
            'password' => Hash::make('password'),
        ]);

        User::query()->create([
            'id_user' => 'S001',
            'nomor_induk' => '202401',
            'nama_lengkap' => 'Siswa Satu',
            'role' => 'siswa',
            'status' => 'aktif',
            'password' => Hash::make('password'),
        ]);

        User::query()->create([
            'id_user' => 'S002',
            'nomor_induk' => '202402',
            'nama_lengkap' => 'Siswa Dua',
            'role' => 'siswa',
            'status' => 'aktif',
            'password' => Hash::make('password'),
        ]);

        // Siswa nonaktif tidak boleh ikut dihitung sebagai kehilangan akses
        User::query()->create([
            'id_user' => 'S003',
            'nomor_induk' => '202403',
            'nama_lengkap' => 'Siswa Nonaktif',
            'role' => 'siswa',
            'status' => 'nonaktif',
            'password' => Hash::make('password'),
        ]);

        DB::table('tm_tahun_ajaran')->insert([
            'id_tahun_ajaran' => 'TA01',
            'nama' => '2026/2027',
            'is_aktif' => true,
        ]);

        DB::table('tm_kelas')->insert([
            'id_kelas' => 'K01',
            'nama_kelas' => 'X TKJ 1',
            'tingkat' => 'X',
            'id_tahun_ajaran' => 'TA01',
            'is_aktif' => true,
        ]);

        DB::table('tm_mapel')->insert([
            'id_mapel' => 'MP01',
            'kode_mapel' => 'TJ',
            'nama_mapel' => 'Teknologi Jaringan',
            'is_aktif' => true,
        ]);

        DB::table('tp_mapel_kelas')->insert(['id_mapel' => 'MP01', 'id_kelas' => 'K01']);
        DB::table('tp_anggota_kelas')->insert([
            ['id_kelas' => 'K01', 'id_user' => 'S001'],
            ['id_kelas' => 'K01', 'id_user' => 'S002'],
            ['id_kelas' => 'K01', 'id_user' => 'S003'],
        ]);
        DB::table('tr_pengajaran')->insert([
            'id_user' => 'G001',
            'id_kelas' => 'K01',
            'id_mapel' => 'MP01',
        ]);

        DB::table('tm_materi')->insert([
            [
                'id_materi' => 'M01',
                'judul' => 'Materi Bab 1',
                'bab' => 'Bab 1',
                'id_mapel' => 'MP01',
                'id_user' => 'G001',
                'is_aktif' => true,
            ],
            [
                'id_materi' => 'M02',
                'judul' => 'Materi Bab 2',
                'bab' => 'Bab 2',
                'id_mapel' => 'MP01',
                'id_user' => 'G001',
                'is_aktif' => true,
            ],
        ]);

        DB::table('tp_materi_kelas')->insert([
            ['id_materi' => 'M01', 'id_kelas' => 'K01'],
            ['id_materi' => 'M02', 'id_kelas' => 'K01'],
        ]);

        DB::table('tm_kuis')->insert([
            [
                'id_kuis' => 'K_PRE',
                'judul' => 'Pre-test Bab 1',
                'kategori' => 'pre-test',
                'id_mapel' => 'MP01',
                'id_tahun_ajaran' => 'TA01',
                'id_user' => 'G001',
                'status_publikasi' => 'terbit',
                'is_aktif' => true,
            ],
            [
                'id_kuis' => 'K_POST',
                'judul' => 'Post-test Bab 1',
                'kategori' => 'post-test',
                'id_mapel' => 'MP01',
                'id_tahun_ajaran' => 'TA01',
                'id_user' => 'G001',
                'status_publikasi' => 'terbit',
                'is_aktif' => true,
            ],
            [
                'id_kuis' => 'K_HARIAN_UNIK',
                'judul' => 'Kuis Harian Bab 1',
                'kategori' => 'kuis harian',
                'id_mapel' => 'MP01',
                'id_tahun_ajaran' => 'TA01',
                'id_user' => 'G001',
                'status_publikasi' => 'terbit',
                'is_aktif' => true,
            ],
            [
                'id_kuis' => 'K_HARIAN_SHARED',
                'judul' => 'Kuis Harian Bersama',
                'kategori' => 'kuis harian',
                'id_mapel' => 'MP01',
                'id_tahun_ajaran' => 'TA01',
                'id_user' => 'G001',
                'status_publikasi' => 'terbit',
                'is_aktif' => true,
            ],
        ]);

        DB::table('tp_materi_kuis')->insert([
            ['id_materi' => 'M01', 'id_kuis' => 'K_PRE'],
            ['id_materi' => 'M01', 'id_kuis' => 'K_POST'],
            ['id_materi' => 'M01', 'id_kuis' => 'K_HARIAN_UNIK'],
            ['id_materi' => 'M01', 'id_kuis' => 'K_HARIAN_SHARED'],
            ['id_materi' => 'M02', 'id_kuis' => 'K_HARIAN_SHARED'],
        ]);

        DB::table('tr_mengerjakan')->insert([
            'id_kuis' => 'K_PRE',
            'id_user' => 'S001',
            'nilai' => 85,
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
        ]);

        DB::table('tr_progress')->insert([
            'id_progress' => 'P001',
            'status' => 'selesai',
            'id_materi' => 'M01',
            'id_user' => 'S001',
        ]);

        return $guru;
    }
}
