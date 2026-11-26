<?php

namespace Tests\Feature;

use App\Http\Middleware\RiwayatHalaman;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class TombolKembaliTest extends TestCase
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

        Schema::create('tp_anggota_kelas', function (Blueprint $table): void {
            $table->bigIncrements('id_anggota_kelas');
            $table->string('id_kelas', 50);
            $table->string('id_user', 50);
            $table->timestamp('created_at')->nullable();
            $table->unique(['id_kelas', 'id_user']);
        });

        Schema::create('tm_mapel', function (Blueprint $table): void {
            $table->string('id_mapel', 50)->primary();
            $table->string('kode_mapel', 20)->unique();
            $table->string('nama_mapel', 100);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
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
            $table->string('tampil_jawaban', 30)->default('setelah_submit');
            $table->timestamp('created_at')->nullable();
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
            $table->string('tingkat_kesulitan', 50)->nullable();
            $table->text('opsi_a')->nullable();
            $table->text('opsi_b')->nullable();
            $table->text('opsi_c')->nullable();
            $table->text('opsi_d')->nullable();
            $table->text('opsi_e')->nullable();
            $table->string('jawaban', 10)->nullable();
            $table->decimal('bobot', 5, 2)->default(100);
            $table->string('id_kuis', 50);
            $table->string('gambar', 255)->nullable();
        });

        Schema::create('tr_sesi_kuis', function (Blueprint $table): void {
            $table->increments('id_sesi');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->dateTime('waktu_mulai');
            $table->dateTime('waktu_selesai')->nullable();
            $table->unsignedTinyInteger('jumlah_pelanggaran')->default(0);
        });

        Schema::create('tr_mengerjakan', function (Blueprint $table): void {
            $table->increments('id_mengerjakan');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->decimal('nilai', 5, 2)->nullable();
            $table->date('tanggal')->nullable();
            $table->integer('percobaan_ke')->default(1);
            $table->string('status_pengerjaan', 20)->default('final');
            $table->string('status_validasi', 50)->default('tervalidasi');
            $table->string('id_validator', 50)->nullable();
            $table->unique(['id_kuis', 'id_user', 'percobaan_ke']);
        });

        Schema::create('tr_draft_jawaban', function (Blueprint $table): void {
            $table->increments('id_draft');
            $table->unsignedInteger('id_sesi');
            $table->string('id_soal', 50);
            $table->string('jawaban_dipilih', 10)->nullable();
            $table->text('jawaban_teks')->nullable();
            $table->timestamp('updated_at')->nullable();
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

        Schema::create('tr_progress', function (Blueprint $table): void {
            $table->string('id_progress', 50)->primary();
            $table->string('status', 50)->nullable();
            $table->date('tanggal')->nullable();
            $table->string('id_materi', 50);
            $table->string('id_user', 50);
            $table->unique(['id_materi', 'id_user']);
        });

        Schema::create('tr_pengajaran', function (Blueprint $table): void {
            $table->increments('id_pengajaran');
            $table->string('id_user', 50);
            $table->string('id_kelas', 50);
            $table->string('id_mapel', 50);
            $table->timestamp('created_at')->nullable();
            $table->unique(['id_user', 'id_kelas', 'id_mapel']);
        });

        Schema::create('tr_remedial_siswa', function (Blueprint $table): void {
            $table->increments('id_remedial_siswa');
            $table->string('id_kuis_remedial', 50);
            $table->unsignedInteger('id_mengerjakan_asal');
            $table->string('id_guru', 50);
            $table->date('tanggal_ditugaskan');
            $table->string('status', 20)->default('ditugaskan');
            $table->unique(['id_kuis_remedial', 'id_mengerjakan_asal']);
        });
    }

    public function test_setelah_membuka_halaman_a_lalu_b_tombol_kembali_di_b_menunjuk_ke_a(): void
    {
        $admin = $this->buatAdmin();

        $this->actingAs($admin)->get(route('admin.siswa.index'))->assertOk();
        $response = $this->actingAs($admin)->get(route('admin.profil'));

        $response->assertOk();
        $response->assertSee('href="'.route('admin.siswa.index').'"', false);
    }

    public function test_tanpa_riwayat_tombol_kembali_menunjuk_ke_fallback(): void
    {
        $admin = $this->buatAdmin();

        $response = $this->actingAs($admin)->get(route('admin.profil'));

        $response->assertOk();
        $response->assertSee('href="'.route('admin.dashboard').'"', false);
    }

    public function test_halaman_login_dan_kerjakan_tidak_masuk_riwayat(): void
    {
        $siswa = $this->buatSiswaDenganKuis();

        $this->get(route('login'))->assertOk();
        $this->assertSame([], session(RiwayatHalaman::KUNCI_SESSION, []));

        $this->actingAs($siswa)
            ->get(route('siswa.kuis.kerjakan', 'Q001'))
            ->assertOk();

        $riwayat = session(RiwayatHalaman::KUNCI_SESSION, []);
        $this->assertNotContains(route('login'), $riwayat);
        $this->assertNotContains(route('siswa.kuis.kerjakan', 'Q001'), $riwayat);
    }

    public function test_request_post_tidak_masuk_riwayat(): void
    {
        $admin = $this->buatAdmin();

        $this->actingAs($admin)->get(route('admin.mapel.index'))->assertOk();
        $this->actingAs($admin)->post(route('admin.mapel.store'), [
            'kode_mapel' => 'TIK99',
            'nama_mapel' => 'Jaringan Komputer',
        ]);

        $riwayat = session(RiwayatHalaman::KUNCI_SESSION, []);
        $this->assertCount(1, $riwayat);
        $this->assertSame(route('admin.mapel.index'), $riwayat[0]);
    }

    public function test_halaman_kerjakan_tidak_memuat_tombol_kembali(): void
    {
        $siswa = $this->buatSiswaDenganKuis();

        $response = $this->actingAs($siswa)->get(route('siswa.kuis.kerjakan', 'Q001'));

        $response->assertOk();
        $response->assertDontSee('arrow-left');
        $response->assertDontSee('href="'.route('siswa.kuis.index').'"', false);
        $this->assertStringNotContainsString('<a ', $response->getContent());
    }

    public function test_tidak_ada_script_atau_style_inline_di_view_tombol_kembali(): void
    {
        $isiKomponen = file_get_contents(resource_path('views/components/tombol-kembali.blade.php'));
        $this->assertIsString($isiKomponen);
        $this->assertStringNotContainsString('<script', $isiKomponen);
        $this->assertStringNotContainsString('<style', $isiKomponen);
        $this->assertStringNotContainsString('style=', $isiKomponen);
        $this->assertStringNotContainsString('onclick=', $isiKomponen);
    }

    private function buatAdmin(): User
    {
        return User::query()->create([
            'id_user' => 'A001',
            'nama_lengkap' => 'Admin Test',
            'email' => 'admin@test.local',
            'role' => 'admin',
            'status' => 'aktif',
            'password' => Hash::make('rahasia123'),
            'wajib_ganti_password' => false,
        ]);
    }

    private function buatSiswaDenganKuis(): User
    {
        DB::table('tm_tahun_ajaran')->insert([
            'id_tahun_ajaran' => 'TA001',
            'nama' => '2026/2027',
            'is_aktif' => true,
        ]);
        DB::table('tm_kelas')->insert([
            'id_kelas' => 'K001',
            'nama_kelas' => 'X TKJ 1',
            'tingkat' => 'X',
            'id_tahun_ajaran' => 'TA001',
            'is_aktif' => true,
        ]);
        DB::table('tm_mapel')->insert([
            'id_mapel' => 'MP001',
            'kode_mapel' => 'TIK',
            'nama_mapel' => 'Teknologi Informasi',
            'is_aktif' => true,
        ]);

        $siswa = User::query()->create([
            'id_user' => 'S001',
            'nomor_induk' => '112233',
            'nama_lengkap' => 'Siswa Test',
            'role' => 'siswa',
            'status' => 'aktif',
            'kelas' => 'X TKJ 1',
            'password' => Hash::make('rahasia123'),
            'wajib_ganti_password' => false,
        ]);

        DB::table('tp_anggota_kelas')->insert([
            'id_kelas' => 'K001',
            'id_user' => 'S001',
        ]);

        DB::table('tm_kuis')->insert([
            'id_kuis' => 'Q001',
            'judul' => 'Kuis Uji Kembali',
            'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => 'MP001',
            'id_tahun_ajaran' => 'TA001',
            'alokasi_waktu' => 30,
            'kkm' => 75,
            'id_user' => 'G001',
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);

        DB::table('tp_kuis_kelas')->insert([
            'id_kuis' => 'Q001',
            'id_kelas' => 'K001',
        ]);

        DB::table('tr_sesi_kuis')->insert([
            'id_kuis' => 'Q001',
            'id_user' => 'S001',
            'waktu_mulai' => now()->subMinutes(5),
        ]);

        return $siswa;
    }
}
