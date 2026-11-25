<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Mengerjakan;
use App\Models\Pengajaran;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class KuisKelasFormTest extends TestCase
{
    private User $guru1;

    private User $guru2;

    private User $siswa;

    private TahunAjaran $tahunAktif;

    private TahunAjaran $tahunNonAktif;

    private Mapel $mapel1;

    private Mapel $mapel2;

    private Kelas $kelas1;

    private Kelas $kelas2;

    private Kelas $kelasLain;

    private Kelas $kelasTahunLalu;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertTrue($this->app->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('sqlite', DB::connection()->getDriverName());

        $this->withoutVite();
        $this->createSchema();
        $this->seedBaseData();
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('tr_mengerjakan');
        Schema::dropIfExists('tm_soal');
        Schema::dropIfExists('tp_kuis_kelas');
        Schema::dropIfExists('tp_materi_kuis');
        Schema::dropIfExists('tr_remedial');
        Schema::dropIfExists('tm_materi');
        Schema::dropIfExists('tm_kuis');
        Schema::dropIfExists('tr_pengajaran');
        Schema::dropIfExists('tp_mapel_kelas');
        Schema::dropIfExists('tp_anggota_kelas');
        Schema::dropIfExists('tm_kelas');
        Schema::dropIfExists('tm_mapel');
        Schema::dropIfExists('tm_tahun_ajaran');
        Schema::dropIfExists('tm_user');

        parent::tearDown();
    }

    private function createSchema(): void
    {
        Schema::create('tm_user', function (Blueprint $table) {
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

        Schema::create('tm_tahun_ajaran', function (Blueprint $table) {
            $table->string('id_tahun_ajaran', 50)->primary();
            $table->string('nama', 20)->unique();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_aktif')->default(false);
            $table->timestamps();
        });

        Schema::create('tm_kelas', function (Blueprint $table) {
            $table->string('id_kelas', 50)->primary();
            $table->string('nama_kelas', 50);
            $table->string('tingkat', 10);
            $table->string('id_tahun_ajaran', 50);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('tm_mapel', function (Blueprint $table) {
            $table->string('id_mapel', 50)->primary();
            $table->string('kode_mapel', 20)->unique();
            $table->string('nama_mapel', 100);
            $table->text('deskripsi')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('tp_anggota_kelas', function (Blueprint $table) {
            $table->string('id_kelas', 50);
            $table->string('id_user', 50);
            $table->timestamps();
            $table->primary(['id_kelas', 'id_user']);
        });

        Schema::create('tp_mapel_kelas', function (Blueprint $table) {
            $table->string('id_mapel', 50);
            $table->string('id_kelas', 50);
            $table->primary(['id_mapel', 'id_kelas']);
        });

        Schema::create('tr_pengajaran', function (Blueprint $table) {
            $table->increments('id_pengajaran');
            $table->string('id_user', 50);
            $table->string('id_kelas', 50);
            $table->string('id_mapel', 50);
            $table->timestamps();
        });

        Schema::create('tm_materi', function (Blueprint $table) {
            $table->string('id_materi', 50)->primary();
            $table->string('judul', 150);
            $table->string('bab', 50)->nullable();
            $table->string('kelas', 50)->nullable();
            $table->string('id_mapel', 50);
            $table->text('deskripsi')->nullable();
            $table->longText('isi_materi')->nullable();
            $table->string('link_youtube')->nullable();
            $table->string('file_pdf')->nullable();
            $table->string('id_user', 50);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('tm_kuis', function (Blueprint $table) {
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
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('tr_remedial', function (Blueprint $table) {
            $table->increments('id_remedial');
            $table->string('id_kuis_remedial', 50);
            $table->string('id_kuis_asal', 50);
            $table->timestamps();
        });

        Schema::create('tp_kuis_kelas', function (Blueprint $table) {
            $table->string('id_kuis', 50);
            $table->string('id_kelas', 50);
            $table->primary(['id_kuis', 'id_kelas']);
        });

        Schema::create('tp_materi_kuis', function (Blueprint $table) {
            $table->string('id_kuis', 50);
            $table->string('id_materi', 50);
            $table->primary(['id_kuis', 'id_materi']);
        });

        Schema::create('tm_soal', function (Blueprint $table) {
            $table->string('id_soal', 50)->primary();
            $table->string('id_kuis', 50);
            $table->text('pertanyaan');
            $table->string('tipe', 20)->default('pilihan_ganda');
            $table->string('opsi_a', 255)->nullable();
            $table->string('opsi_b', 255)->nullable();
            $table->string('opsi_c', 255)->nullable();
            $table->string('opsi_d', 255)->nullable();
            $table->string('opsi_e', 255)->nullable();
            $table->string('jawaban', 10)->nullable();
            $table->integer('bobot')->default(1);
            $table->string('tingkat_kesulitan', 20)->default('mudah');
            $table->string('gambar', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('tr_mengerjakan', function (Blueprint $table) {
            $table->increments('id_mengerjakan');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->decimal('nilai', 5, 2)->nullable();
            $table->date('tanggal')->nullable();
            $table->unsignedInteger('percobaan_ke')->default(1);
            $table->string('status_pengerjaan', 20)->default('final');
            $table->string('status_validasi', 30)->default('belum divalidasi');
            $table->string('id_validator', 50)->nullable();
            $table->timestamps();
        });
    }

    private function seedBaseData(): void
    {
        $this->tahunAktif = TahunAjaran::create([
            'id_tahun_ajaran' => 'TA01',
            'nama' => '2026/2027',
            'is_aktif' => true,
        ]);

        $this->tahunNonAktif = TahunAjaran::create([
            'id_tahun_ajaran' => 'TA02',
            'nama' => '2025/2026',
            'is_aktif' => false,
        ]);

        $this->mapel1 = Mapel::create([
            'id_mapel' => 'MP01',
            'kode_mapel' => 'TIK',
            'nama_mapel' => 'Teknologi Informasi',
            'is_aktif' => true,
        ]);

        $this->mapel2 = Mapel::create([
            'id_mapel' => 'MP02',
            'kode_mapel' => 'BINDO',
            'nama_mapel' => 'Bahasa Indonesia',
            'is_aktif' => true,
        ]);

        $this->kelas1 = Kelas::create([
            'id_kelas' => 'KLS01',
            'nama_kelas' => 'X TKJ 1',
            'tingkat' => 'X',
            'id_tahun_ajaran' => $this->tahunAktif->id_tahun_ajaran,
            'is_aktif' => true,
        ]);

        $this->kelas2 = Kelas::create([
            'id_kelas' => 'KLS02',
            'nama_kelas' => 'X TKJ 2',
            'tingkat' => 'X',
            'id_tahun_ajaran' => $this->tahunAktif->id_tahun_ajaran,
            'is_aktif' => true,
        ]);

        $this->kelasLain = Kelas::create([
            'id_kelas' => 'KLS03',
            'nama_kelas' => 'X TKJ 3',
            'tingkat' => 'X',
            'id_tahun_ajaran' => $this->tahunAktif->id_tahun_ajaran,
            'is_aktif' => true,
        ]);

        $this->kelasTahunLalu = Kelas::create([
            'id_kelas' => 'KLS04',
            'nama_kelas' => 'X TKJ Lama',
            'tingkat' => 'X',
            'id_tahun_ajaran' => $this->tahunNonAktif->id_tahun_ajaran,
            'is_aktif' => false,
        ]);

        $this->guru1 = User::create([
            'id_user' => 'G001',
            'nomor_induk' => '19800101',
            'nama_lengkap' => 'Guru Satu',
            'role' => 'guru',
            'status' => 'aktif',
            'password' => bcrypt('password'),
        ]);

        $this->guru2 = User::create([
            'id_user' => 'G002',
            'nomor_induk' => '19800102',
            'nama_lengkap' => 'Guru Dua',
            'role' => 'guru',
            'status' => 'aktif',
            'password' => bcrypt('password'),
        ]);

        $this->siswa = User::create([
            'id_user' => 'S001',
            'nomor_induk' => '10001',
            'nama_lengkap' => 'Siswa Satu',
            'role' => 'siswa',
            'status' => 'aktif',
            'password' => bcrypt('password'),
        ]);

        // Guru 1 mengajar Mapel 1 di Kelas 1 dan Kelas 2
        Pengajaran::create([
            'id_user' => $this->guru1->id_user,
            'id_kelas' => $this->kelas1->id_kelas,
            'id_mapel' => $this->mapel1->id_mapel,
        ]);

        Pengajaran::create([
            'id_user' => $this->guru1->id_user,
            'id_kelas' => $this->kelas2->id_kelas,
            'id_mapel' => $this->mapel1->id_mapel,
        ]);

        // Guru 2 mengajar Mapel 1 di Kelas Lain (Kelas 3)
        Pengajaran::create([
            'id_user' => $this->guru2->id_user,
            'id_kelas' => $this->kelasLain->id_kelas,
            'id_mapel' => $this->mapel1->id_mapel,
        ]);

        // Siswa adalah anggota Kelas 1
        DB::table('tp_anggota_kelas')->insert([
            'id_kelas' => $this->kelas1->id_kelas,
            'id_user' => $this->siswa->id_user,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_guru_hanya_memilih_kelas_yang_ia_ajar(): void
    {
        $response = $this->actingAs($this->guru1)->post(route('kuis.store'), [
            'judul' => 'Kuis Jaringan Dasar',
            'deskripsi' => 'Deskripsi kuis',
            'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => $this->mapel1->id_mapel,
            'alokasi_waktu' => 30,
            'kkm' => 75.00,
            'kelas' => [$this->kelas1->id_kelas, $this->kelas2->id_kelas],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $kuis = Kuis::where('judul', 'Kuis Jaringan Dasar')->first();
        $this->assertNotNull($kuis);

        $this->assertDatabaseHas('tp_kuis_kelas', [
            'id_kuis' => $kuis->id_kuis,
            'id_kelas' => $this->kelas1->id_kelas,
        ]);
        $this->assertDatabaseHas('tp_kuis_kelas', [
            'id_kuis' => $kuis->id_kuis,
            'id_kelas' => $this->kelas2->id_kelas,
        ]);
    }

    public function test_pembuatan_kuis_tanpa_kelas_ditolak(): void
    {
        $response = $this->actingAs($this->guru1)->post(route('kuis.store'), [
            'judul' => 'Kuis Tanpa Kelas',
            'deskripsi' => 'Deskripsi kuis',
            'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => $this->mapel1->id_mapel,
            'alokasi_waktu' => 30,
            'kkm' => 75.00,
            'kelas' => [],
        ]);

        $response->assertSessionHasErrors('kelas');
        $this->assertDatabaseMissing('tm_kuis', ['judul' => 'Kuis Tanpa Kelas']);
    }

    public function test_kelas_di_luar_pengajaran_guru_ditolak(): void
    {
        // Guru 1 mencoba memilih Kelas 3 (KLS03) yang diajar oleh Guru 2
        $response = $this->actingAs($this->guru1)->post(route('kuis.store'), [
            'judul' => 'Kuis Kelas Ilegal',
            'deskripsi' => 'Deskripsi kuis',
            'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => $this->mapel1->id_mapel,
            'alokasi_waktu' => 30,
            'kkm' => 75.00,
            'kelas' => [$this->kelas1->id_kelas, $this->kelasLain->id_kelas],
        ]);

        $response->assertSessionHasErrors('kelas');
        $this->assertDatabaseMissing('tm_kuis', ['judul' => 'Kuis Kelas Ilegal']);
    }

    public function test_kelas_di_tahun_tidak_aktif_ditolak(): void
    {
        // Kelas 4 berada di tahun ajaran tidak aktif
        $response = $this->actingAs($this->guru1)->post(route('kuis.store'), [
            'judul' => 'Kuis Tahun Tidak Aktif',
            'deskripsi' => 'Deskripsi kuis',
            'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => $this->mapel1->id_mapel,
            'alokasi_waktu' => 30,
            'kkm' => 75.00,
            'kelas' => [$this->kelasTahunLalu->id_kelas],
        ]);

        $response->assertSessionHasErrors('kelas');
        $this->assertDatabaseMissing('tm_kuis', ['judul' => 'Kuis Tahun Tidak Aktif']);
    }

    public function test_sinkronisasi_saat_edit(): void
    {
        $kuis = Kuis::create([
            'id_kuis' => 'KUIS_EDIT_01',
            'judul' => 'Kuis Awal',
            'deskripsi' => 'Deskripsi',
            'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => $this->mapel1->id_mapel,
            'id_tahun_ajaran' => $this->tahunAktif->id_tahun_ajaran,
            'alokasi_waktu' => 30,
            'kkm' => 75.00,
            'id_user' => $this->guru1->id_user,
            'status_publikasi' => 'draft',
            'is_aktif' => true,
        ]);

        // Awalnya terhubung ke Kelas 1 dan Kelas 2
        $kuis->kelas()->sync([$this->kelas1->id_kelas, $this->kelas2->id_kelas]);

        $this->assertDatabaseHas('tp_kuis_kelas', [
            'id_kuis' => $kuis->id_kuis,
            'id_kelas' => $this->kelas1->id_kelas,
        ]);
        $this->assertDatabaseHas('tp_kuis_kelas', [
            'id_kuis' => $kuis->id_kuis,
            'id_kelas' => $this->kelas2->id_kelas,
        ]);

        // Edit kuis: lepaskan Kelas 1, pertahankan Kelas 2 saja
        $response = $this->actingAs($this->guru1)->put(route('kuis.update', $kuis->id_kuis), [
            'judul' => 'Kuis Diperbarui',
            'deskripsi' => 'Deskripsi',
            'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'sedang',
            'id_mapel' => $this->mapel1->id_mapel,
            'alokasi_waktu' => 45,
            'kkm' => 80.00,
            'kelas' => [$this->kelas2->id_kelas],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Kelas 1 harus terlepas dari tp_kuis_kelas
        $this->assertDatabaseMissing('tp_kuis_kelas', [
            'id_kuis' => $kuis->id_kuis,
            'id_kelas' => $this->kelas1->id_kelas,
        ]);

        // Kelas 2 tetap ada
        $this->assertDatabaseHas('tp_kuis_kelas', [
            'id_kuis' => $kuis->id_kuis,
            'id_kelas' => $this->kelas2->id_kelas,
        ]);
    }

    public function test_nilai_tidak_terhapus_saat_kelas_dilepas(): void
    {
        $kuis = Kuis::create([
            'id_kuis' => 'KUIS_NILAI_01',
            'judul' => 'Kuis Evaluasi Nilai',
            'deskripsi' => 'Deskripsi',
            'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => $this->mapel1->id_mapel,
            'id_tahun_ajaran' => $this->tahunAktif->id_tahun_ajaran,
            'alokasi_waktu' => 30,
            'kkm' => 75.00,
            'id_user' => $this->guru1->id_user,
            'status_publikasi' => 'draft',
            'is_aktif' => true,
        ]);

        // Kuis awalnya ditugaskan ke Kelas 1
        $kuis->kelas()->sync([$this->kelas1->id_kelas]);

        // Siswa sudah mengerjakan kuis ini dan memiliki nilai
        $mengerjakan = Mengerjakan::create([
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $this->siswa->id_user,
            'nilai' => 85.00,
            'tanggal' => now()->toDateString(),
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
            'status_validasi' => 'divalidasi',
        ]);

        $this->assertDatabaseHas('tr_mengerjakan', [
            'id_mengerjakan' => $mengerjakan->id_mengerjakan,
            'nilai' => 85.00,
        ]);

        // Guru mengedit kuis dan mengganti target kelas dari Kelas 1 ke Kelas 2
        $response = $this->actingAs($this->guru1)->put(route('kuis.update', $kuis->id_kuis), [
            'judul' => 'Kuis Evaluasi Nilai',
            'deskripsi' => 'Deskripsi',
            'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => $this->mapel1->id_mapel,
            'alokasi_waktu' => 30,
            'kkm' => 75.00,
            'kelas' => [$this->kelas2->id_kelas],
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        // Kelas 1 terlepas dari kuis di tp_kuis_kelas
        $this->assertDatabaseMissing('tp_kuis_kelas', [
            'id_kuis' => $kuis->id_kuis,
            'id_kelas' => $this->kelas1->id_kelas,
        ]);

        // Namun nilai siswa di tr_mengerjakan tetap aman dan tidak terhapus
        $this->assertDatabaseHas('tr_mengerjakan', [
            'id_mengerjakan' => $mengerjakan->id_mengerjakan,
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $this->siswa->id_user,
            'nilai' => 85.00,
        ]);
    }
}
