<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Pengajaran;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GuruKuisPengajaranTest extends TestCase
{
    private User $guru1;
    private User $guru2;
    private Mapel $mapelTIK;
    private Mapel $mapelMTK;
    private Kuis $kuisTIKGuru1;
    private Kuis $kuisMTKGuru2;
    private Kuis $kuisTIKGuru2;

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
            $table->string('tampil_jawaban', 20)->default('setelah_submit');
            $table->timestamps();
        });

        Schema::create('mapel', function (Blueprint $table) {
            $table->string('id_mapel', 50)->primary();
            $table->string('nama_mapel', 100);
            $table->string('kode_mapel', 20)->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->string('tampil_jawaban', 20)->default('setelah_submit');
            $table->timestamps();
        });

        Schema::create('kelas', function (Blueprint $table) {
            $table->string('id_kelas', 50)->primary();
            $table->string('nama_kelas', 100);
            $table->string('tingkat', 20)->nullable();
            $table->string('id_tahun_ajaran', 50);
            $table->boolean('is_aktif')->default(true);
            $table->string('tampil_jawaban', 20)->default('setelah_submit');
            $table->timestamps();
        });

        Schema::create('mapel_kelas', function (Blueprint $table) {
            $table->increments('id_mapel_kelas');
            $table->string('id_mapel', 50);
            $table->string('id_kelas', 50);
        });

        Schema::create('pengajaran', function (Blueprint $table) {
            $table->increments('id_pengajaran');
            $table->string('id_user', 50);
            $table->string('id_kelas', 50);
            $table->string('id_mapel', 50);
            $table->timestamp('created_at')->nullable()->useCurrent();
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

        Schema::create('materi_kuis', function (Blueprint $table) {
            $table->increments('id_materi_kuis');
            $table->string('id_materi', 50);
            $table->string('id_kuis', 50);
        });

        Schema::create('remedial', function (Blueprint $table) {
            $table->increments('id_remedial');
            $table->string('id_kuis_remedial', 50)->unique();
            $table->string('id_kuis_asal', 50);
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
    }

    private function seedScenario(): void
    {
        $this->guru1 = User::create([
            'id_user' => 'G001',
            'nomor_induk' => '198101',
            'nama_lengkap' => 'Guru TIK Budi',
            'role' => 'guru',
            'status' => 'aktif',
            'password' => bcrypt('secret'),
        ]);

        $this->guru2 = User::create([
            'id_user' => 'G002',
            'nomor_induk' => '198202',
            'nama_lengkap' => 'Guru MTK Siti',
            'role' => 'guru',
            'status' => 'aktif',
            'password' => bcrypt('secret'),
        ]);

        TahunAjaran::create([
            'id_tahun_ajaran' => 'TA001',
            'nama' => '2026/2027',
            'is_aktif' => true,
        ]);

        TahunAjaran::create([
            'id_tahun_ajaran' => 'TA002',
            'nama' => '2025/2026',
            'is_aktif' => false,
        ]);

        $this->mapelTIK = Mapel::create([
            'id_mapel' => 'MP001',
            'kode_mapel' => 'TIK',
            'nama_mapel' => 'Teknologi Informasi',
            'is_aktif' => true,
        ]);

        $this->mapelMTK = Mapel::create([
            'id_mapel' => 'MP002',
            'kode_mapel' => 'MTK',
            'nama_mapel' => 'Matematika',
            'is_aktif' => true,
        ]);

        Kelas::create([
            'id_kelas' => 'KLS001',
            'nama_kelas' => 'X TKJ 1',
            'tingkat' => 'X',
            'id_tahun_ajaran' => 'TA001',
            'is_aktif' => true,
        ]);

        Kelas::create([
            'id_kelas' => 'KLS002',
            'nama_kelas' => 'XI TKJ 1',
            'tingkat' => 'XI',
            'id_tahun_ajaran' => 'TA002', // tahun lalu
            'is_aktif' => true,
        ]);

        // Guru 1 mengajar TIK pada tahun aktif
        Pengajaran::create([
            'id_user' => 'G001',
            'id_kelas' => 'KLS001',
            'id_mapel' => 'MP001',
        ]);

        // Guru 2 mengajar Matematika pada tahun aktif
        Pengajaran::create([
            'id_user' => 'G002',
            'id_kelas' => 'KLS001',
            'id_mapel' => 'MP002',
        ]);

        // Guru 1 juga mengajar MTK pada tahun lalu (tidak aktif)
        Pengajaran::create([
            'id_user' => 'G001',
            'id_kelas' => 'KLS002',
            'id_mapel' => 'MP002',
        ]);

        // Kuis
        $this->kuisTIKGuru1 = Kuis::create([
            'id_kuis' => 'K001',
            'judul' => 'Kuis TIK Bab 1',
            'kategori' => 'pre-test',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => 'MP001',
            'id_tahun_ajaran' => 'TA001',
            'alokasi_waktu' => 20,
            'kkm' => 75,
            'id_user' => 'G001',
            'status_publikasi' => 'draft',
            'is_aktif' => true,
        ]);

        $this->kuisMTKGuru2 = Kuis::create([
            'id_kuis' => 'K002',
            'judul' => 'Kuis MTK Aljabar',
            'kategori' => 'pre-test',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => 'MP002',
            'id_tahun_ajaran' => 'TA001',
            'alokasi_waktu' => 20,
            'kkm' => 75,
            'id_user' => 'G002',
            'status_publikasi' => 'draft',
            'is_aktif' => true,
        ]);

        $this->kuisTIKGuru2 = Kuis::create([
            'id_kuis' => 'K003',
            'judul' => 'Kuis TIK Cadangan',
            'kategori' => 'pre-test',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => 'MP001',
            'id_tahun_ajaran' => 'TA001',
            'alokasi_waktu' => 20,
            'kkm' => 75,
            'id_user' => 'G002',
            'status_publikasi' => 'draft',
            'is_aktif' => true,
        ]);
    }

    public function test_mapel_untuk_guru_hanya_mengambil_tahun_aktif(): void
    {
        $mapelG001 = Pengajaran::mapelUntukGuru($this->guru1);

        // Guru 1 mengajar MP001 di TA001 (aktif) dan MP002 di TA002 (tidak aktif)
        $this->assertTrue($mapelG001->contains('MP001'));
        $this->assertFalse($mapelG001->contains('MP002'));
    }

    public function test_guru_bisa_melihat_kuis_mapel_yang_ia_ajar(): void
    {
        // 1. Pada level pilih mapel (index tanpa query param), mapel TIK tampil
        $responseIndex = $this->actingAs($this->guru1)->get(route('kuis.index'));
        $responseIndex->assertOk();
        $responseIndex->assertSee('Teknologi Informasi');
        $responseIndex->assertDontSee('Matematika');

        // 2. Pada level daftar kuis mapel TIK (?mapel=MP001), kuis TIK tampil
        $response = $this->actingAs($this->guru1)->get(route('kuis.index', ['mapel' => $this->mapelTIK->id_mapel]));
        $response->assertOk();
        $response->assertSee('Kuis TIK Bab 1');
        $response->assertSee('Teknologi Informasi');
    }

    public function test_guru_tidak_bisa_melihat_kuis_mapel_lain(): void
    {
        // Di halaman index umum kuis, kuis MTK tidak muncul bagi Guru TIK
        $response = $this->actingAs($this->guru1)->get(route('kuis.index'));
        $response->assertOk();
        $response->assertDontSee('Kuis MTK Aljabar');

        // Mengakses filter kuis dengan mapel yang tidak diajarkan menghasilkan 403
        $responseMapelLain = $this->actingAs($this->guru1)->get(route('kuis.index', ['mapel' => 'MP002']));
        $responseMapelLain->assertForbidden();

        // Mengakses detail kuis dari mapel lain menghasilkan 403
        $responseShow = $this->actingAs($this->guru1)->get(route('kuis.show', $this->kuisMTKGuru2->id_kuis));
        $responseShow->assertForbidden();
    }

    public function test_guru_tidak_bisa_mengirim_mapel_lain_di_store(): void
    {
        $payload = [
            'judul' => 'Kuis Baru Coba-coba',
            'kategori' => 'pre-test',
            'id_mapel' => 'MP002', // Guru 1 hanya mengajar MP001
            'tingkat_kesulitan' => 'mudah',
            'alokasi_waktu' => 30,
            'kkm' => 75,
        ];

        $response = $this->actingAs($this->guru1)
            ->from(route('kuis.create'))
            ->post(route('kuis.store'), $payload);

        $response->assertRedirect(route('kuis.create'));
        $response->assertSessionHasErrors('id_mapel');

        $this->assertDatabaseMissing('kuis', [
            'judul' => 'Kuis Baru Coba-coba',
            'id_user' => 'G001',
        ]);
    }

    public function test_guru_tidak_bisa_mengubah_kuis_milik_guru_lain(): void
    {
        // Kuis K003 dibuat oleh Guru 2 (G002)
        // Guru 1 (G001) mencoba edit form
        $responseEdit = $this->actingAs($this->guru1)->get(route('kuis.edit', $this->kuisTIKGuru2->id_kuis));
        $responseEdit->assertForbidden();

        // Guru 1 mencoba update kuis milik Guru 2
        $responseUpdate = $this->actingAs($this->guru1)->put(route('kuis.update', $this->kuisTIKGuru2->id_kuis), [
            'judul' => 'Pembajakan Kuis',
            'kategori' => 'pre-test',
            'id_mapel' => 'MP001',
            'tingkat_kesulitan' => 'mudah',
        ]);
        $responseUpdate->assertForbidden();

        // Guru 1 mencoba menghapus (arsip) kuis milik Guru 2
        $responseDestroy = $this->actingAs($this->guru1)->delete(route('kuis.destroy', $this->kuisTIKGuru2->id_kuis));
        $responseDestroy->assertForbidden();

        // Pastikan judul kuis tidak berubah dan tetap aktif
        $this->kuisTIKGuru2->refresh();
        $this->assertSame('Kuis TIK Cadangan', $this->kuisTIKGuru2->judul);
        $this->assertTrue($this->kuisTIKGuru2->is_aktif);
    }
}
