<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PelanggaranOtomatisTest extends TestCase
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
            $table->decimal('bobot', 5, 2)->default(50);
            $table->string('id_kuis', 50);
            $table->string('gambar', 255)->nullable();
        });

        Schema::create('tr_sesi_kuis', function (Blueprint $table): void {
            $table->increments('id_sesi');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->unsignedInteger('percobaan_ke')->default(1);
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
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->string('id_soal', 50);
            $table->string('jawaban_dipilih', 10)->nullable();
            $table->text('jawaban_teks')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('tr_nilai_essay', function (Blueprint $table): void {
            $table->increments('id_nilai_essay');
            $table->unsignedInteger('id_mengerjakan');
            $table->string('id_soal', 50);
            $table->text('jawaban_teks')->nullable();
            $table->decimal('nilai', 5, 2)->nullable();
            $table->text('catatan_guru')->nullable();
            $table->timestamps();
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
    }

    public function test_pelanggaran_pertama_dan_kedua_hanya_menambah_penghitung_belum_mengumpulkan(): void
    {
        $siswa = $this->buatSiswaDanKuis();

        $resp1 = $this->actingAs($siswa)->postJson(route('siswa.kuis.pelanggaran', 'Q001'));
        $resp1->assertOk()->assertJson([
            'jumlah' => 1,
            'batas' => 3,
            'autoSubmitted' => false,
        ]);

        $resp2 = $this->actingAs($siswa)->postJson(route('siswa.kuis.pelanggaran', 'Q001'));
        $resp2->assertOk()->assertJson([
            'jumlah' => 2,
            'batas' => 3,
            'autoSubmitted' => false,
        ]);

        $this->assertDatabaseHas('tr_sesi_kuis', [
            'id_kuis' => 'Q001',
            'id_user' => 'S001',
            'jumlah_pelanggaran' => 2,
            'waktu_selesai' => null,
        ]);
        $this->assertDatabaseCount('tr_mengerjakan', 0);
    }

    public function test_pelanggaran_ketiga_mengumpulkan_kuis_walaupun_semua_jawaban_kosong_dengan_nilai_nol(): void
    {
        $siswa = $this->buatSiswaDanKuis();

        $this->actingAs($siswa)->postJson(route('siswa.kuis.pelanggaran', 'Q001'))->assertOk();
        $this->actingAs($siswa)->postJson(route('siswa.kuis.pelanggaran', 'Q001'))->assertOk();
        $resp3 = $this->actingAs($siswa)->postJson(route('siswa.kuis.pelanggaran', 'Q001'));

        $resp3->assertOk()->assertJson([
            'jumlah' => 3,
            'batas' => 3,
            'autoSubmitted' => true,
        ]);
        $resp3->assertSessionHas('error');

        $sesi = DB::table('tr_sesi_kuis')->where('id_kuis', 'Q001')->where('id_user', 'S001')->first();
        $this->assertNotNull($sesi->waktu_selesai);

        $hasil = DB::table('tr_mengerjakan')->where('id_kuis', 'Q001')->where('id_user', 'S001')->first();
        $this->assertNotNull($hasil);
        $this->assertEquals(0.0, (float) $hasil->nilai);
        $this->assertSame('final', $hasil->status_pengerjaan);
    }

    public function test_pelanggaran_ketiga_dengan_essay_kosong_tetap_menunggu_penilaian(): void
    {
        $siswa = $this->buatSiswaDanKuis(denganEssay: true);

        $this->actingAs($siswa)->postJson(route('siswa.kuis.pelanggaran', 'Q001'))->assertOk();
        $this->actingAs($siswa)->postJson(route('siswa.kuis.pelanggaran', 'Q001'))->assertOk();
        $this->actingAs($siswa)->postJson(route('siswa.kuis.pelanggaran', 'Q001'))
            ->assertOk()
            ->assertJson(['autoSubmitted' => true]);

        $hasil = DB::table('tr_mengerjakan')->where('id_kuis', 'Q001')->where('id_user', 'S001')->first();
        $this->assertNotNull($hasil);
        $this->assertNull($hasil->nilai);
        $this->assertSame('menunggu penilaian', $hasil->status_validasi);

        $this->assertDatabaseHas('tr_nilai_essay', [
            'id_mengerjakan' => $hasil->id_mengerjakan,
            'id_soal' => 'SOAL_ESSAY',
            'jawaban_teks' => null,
            'nilai' => null,
        ]);
    }

    public function test_pengumpulan_tidak_terjadi_dua_kali(): void
    {
        $siswa = $this->buatSiswaDanKuis();

        $this->actingAs($siswa)->postJson(route('siswa.kuis.pelanggaran', 'Q001'))->assertOk();
        $this->actingAs($siswa)->postJson(route('siswa.kuis.pelanggaran', 'Q001'))->assertOk();
        $this->actingAs($siswa)->postJson(route('siswa.kuis.pelanggaran', 'Q001'))
            ->assertOk()
            ->assertJson(['autoSubmitted' => true]);

        // Pelanggaran keempat setelah sesi selesai harus ditolak 404 tanpa membuat duplikat
        $this->actingAs($siswa)->postJson(route('siswa.kuis.pelanggaran', 'Q001'))->assertNotFound();

        $this->assertDatabaseCount('tr_mengerjakan', 1);
    }

    private function buatSiswaDanKuis(bool $denganEssay = false): User
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
            'judul' => 'Kuis Pelanggaran',
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

        DB::table('tm_soal')->insert([
            [
                'id_soal' => 'SOAL1',
                'pertanyaan' => 'Pertanyaan pertama?',
                'tipe' => 'pilihan_ganda',
                'opsi_a' => 'A1',
                'opsi_b' => 'B1',
                'opsi_c' => 'C1',
                'opsi_d' => 'D1',
                'jawaban' => 'A',
                'bobot' => 50,
                'id_kuis' => 'Q001',
            ],
            [
                'id_soal' => 'SOAL2',
                'pertanyaan' => 'Pertanyaan kedua?',
                'tipe' => 'pilihan_ganda',
                'opsi_a' => 'A2',
                'opsi_b' => 'B2',
                'opsi_c' => 'C2',
                'opsi_d' => 'D2',
                'jawaban' => 'B',
                'bobot' => 50,
                'id_kuis' => 'Q001',
            ],
        ]);

        if ($denganEssay) {
            DB::table('tm_soal')->insert([
                'id_soal' => 'SOAL_ESSAY',
                'pertanyaan' => 'Jelaskan jaringan komputer!',
                'tipe' => 'essay',
                'bobot' => 50,
                'id_kuis' => 'Q001',
            ]);
        }

        DB::table('tr_sesi_kuis')->insert([
            'id_kuis' => 'Q001',
            'id_user' => 'S001',
            'percobaan_ke' => 1,
            'waktu_mulai' => now()->subMinutes(5),
        ]);

        return $siswa;
    }
}
