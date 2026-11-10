<?php

namespace Tests\Feature;

use App\Http\Controllers\SiswaKuisController;
use App\Models\DraftJawaban;
use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Mengerjakan;
use App\Models\SesiKuis;
use App\Models\Soal;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PelanggaranKuisTest extends TestCase
{
    private User $siswa;
    private User $siswaLain;
    private User $guru;
    private Kuis $kuis;
    private Soal $soal1;
    private Soal $soal2;

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

        Schema::create('tahun_ajaran', function (Blueprint $table) {
            $table->string('id_tahun_ajaran', 50)->primary();
            $table->string('nama', 20)->unique();
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_aktif')->default(true);
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
            $table->timestamp('waktu_mulai')->useCurrent();
            $table->timestamp('waktu_selesai')->nullable();
            $table->tinyInteger('jumlah_pelanggaran')->unsigned()->default(0);
            $table->unique(['id_kuis', 'id_user', 'percobaan_ke']);
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
        });

        Schema::create('draft_jawaban', function (Blueprint $table) {
            $table->increments('id_draft');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->string('id_soal', 50);
            $table->string('jawaban_dipilih', 5)->nullable();
            $table->timestamps();
            $table->unique(['id_kuis', 'id_user', 'id_soal']);
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
        $this->guru = User::create([
            'id_user' => 'G001',
            'nama_lengkap' => 'Guru Pengajar',
            'role' => 'guru',
            'status' => 'aktif',
            'password' => bcrypt('password'),
        ]);

        $this->siswa = User::create([
            'id_user' => 'S001',
            'nomor_induk' => '1001',
            'nama_lengkap' => 'Siswa CBT',
            'role' => 'siswa',
            'status' => 'aktif',
            'password' => bcrypt('password'),
        ]);

        $this->siswaLain = User::create([
            'id_user' => 'S002',
            'nomor_induk' => '1002',
            'nama_lengkap' => 'Siswa Lain',
            'role' => 'siswa',
            'status' => 'aktif',
            'password' => bcrypt('password'),
        ]);

        TahunAjaran::create([
            'id_tahun_ajaran' => 'TA001',
            'nama' => '2026/2027',
            'is_aktif' => true,
        ]);

        Mapel::create([
            'id_mapel' => 'MP001',
            'kode_mapel' => 'TIK',
            'nama_mapel' => 'Teknologi Informasi',
            'is_aktif' => true,
        ]);

        $this->kuis = Kuis::create([
            'id_kuis' => 'K001',
            'judul' => 'Ulangan Harian CBT',
            'kategori' => 'ulangan',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => 'MP001',
            'id_tahun_ajaran' => 'TA001',
            'alokasi_waktu' => 30,
            'kkm' => 75,
            'id_user' => 'G001',
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);

        $this->soal1 = Soal::create([
            'id_soal' => 'SOAL001',
            'id_kuis' => 'K001',
            'pertanyaan' => 'Pertanyaan 1',
            'opsi_a' => 'Pilihan A',
            'opsi_b' => 'Pilihan B',
            'jawaban' => 'A',
            'bobot' => 50,
        ]);

        $this->soal2 = Soal::create([
            'id_soal' => 'SOAL002',
            'id_kuis' => 'K001',
            'pertanyaan' => 'Pertanyaan 2',
            'opsi_a' => 'Pilihan A',
            'opsi_b' => 'Pilihan B',
            'jawaban' => 'B',
            'bobot' => 50,
        ]);
    }

    public function test_satu_pelanggaran_menaikkan_penghitung_menjadi_1_dan_belum_auto_submit(): void
    {
        $sesi = SesiKuis::create([
            'id_kuis' => $this->kuis->id_kuis,
            'id_user' => $this->siswa->id_user,
            'percobaan_ke' => 1,
            'waktu_mulai' => now(),
            'waktu_selesai' => null,
            'jumlah_pelanggaran' => 0,
        ]);

        $response = $this->actingAs($this->siswa)
            ->postJson(route('siswa.kuis.pelanggaran', $this->kuis->id_kuis));

        $response->assertOk()
            ->assertJson([
                'jumlah' => 1,
                'batas' => SiswaKuisController::BATAS_PELANGGARAN,
                'autoSubmitted' => false,
            ]);

        $this->assertDatabaseHas('sesi_kuis', [
            'id_sesi' => $sesi->id_sesi,
            'jumlah_pelanggaran' => 1,
            'waktu_selesai' => null,
        ]);

        // Belum ada submit ke tabel mengerjakan
        $this->assertDatabaseMissing('mengerjakan', [
            'id_kuis' => $this->kuis->id_kuis,
            'id_user' => $this->siswa->id_user,
        ]);
    }

    public function test_pelanggaran_ke_tiga_memicu_submit_sesi_ditutup_dan_draft_dipakai(): void
    {
        $sesi = SesiKuis::create([
            'id_kuis' => $this->kuis->id_kuis,
            'id_user' => $this->siswa->id_user,
            'percobaan_ke' => 1,
            'waktu_mulai' => now(),
            'waktu_selesai' => null,
            'jumlah_pelanggaran' => 2, // Sudah 2 pelanggaran sebelumnya
        ]);

        // Simpan draft jawaban: soal 1 dijawab benar (A), soal 2 dijawab salah (A)
        DraftJawaban::create([
            'id_kuis' => $this->kuis->id_kuis,
            'id_user' => $this->siswa->id_user,
            'id_soal' => $this->soal1->id_soal,
            'jawaban_dipilih' => 'A',
        ]);

        DraftJawaban::create([
            'id_kuis' => $this->kuis->id_kuis,
            'id_user' => $this->siswa->id_user,
            'id_soal' => $this->soal2->id_soal,
            'jawaban_dipilih' => 'A', // Salah, kunci B
        ]);

        $response = $this->actingAs($this->siswa)
            ->postJson(route('siswa.kuis.pelanggaran', $this->kuis->id_kuis));

        $response->assertOk()
            ->assertJson([
                'jumlah' => 3,
                'batas' => SiswaKuisController::BATAS_PELANGGARAN,
                'autoSubmitted' => true,
            ]);

        // Sesi ditutup (waktu_selesai terisi) dan jumlah pelanggaran 3
        $sesiUpdated = SesiKuis::find($sesi->id_sesi);
        $this->assertSame(3, $sesiUpdated->jumlah_pelanggaran);
        $this->assertNotNull($sesiUpdated->waktu_selesai);

        // Nilai terhitung dari draft jawaban: 50 dari 100
        $this->assertDatabaseHas('mengerjakan', [
            'id_kuis' => $this->kuis->id_kuis,
            'id_user' => $this->siswa->id_user,
            'nilai' => 50.00,
            'status_pengerjaan' => 'final',
        ]);

        // Draft jawaban tersimpan untuk evaluasi pembahasan
        $this->assertDatabaseHas('draft_jawaban', [
            'id_kuis' => $this->kuis->id_kuis,
            'id_user' => $this->siswa->id_user,
            'id_soal' => $this->soal1->id_soal,
            'jawaban_dipilih' => 'A',
        ]);
    }

    public function test_siswa_lain_tidak_bisa_mencatat_pelanggaran_untuk_sesi_yang_bukan_miliknya(): void
    {
        // Hanya siswa 1 yang punya sesi aktif
        SesiKuis::create([
            'id_kuis' => $this->kuis->id_kuis,
            'id_user' => $this->siswa->id_user,
            'percobaan_ke' => 1,
            'waktu_mulai' => now(),
            'waktu_selesai' => null,
            'jumlah_pelanggaran' => 0,
        ]);

        // Siswa lain (S002) mencoba catat pelanggaran
        $response = $this->actingAs($this->siswaLain)
            ->postJson(route('siswa.kuis.pelanggaran', $this->kuis->id_kuis));

        $response->assertNotFound();

        // Jumlah pelanggaran sesi siswa 1 tetap 0
        $this->assertDatabaseHas('sesi_kuis', [
            'id_user' => $this->siswa->id_user,
            'jumlah_pelanggaran' => 0,
        ]);
    }

    public function test_tanpa_sesi_aktif_respons_404(): void
    {
        // Siswa tidak memiliki sesi kuis sama sekali
        $response = $this->actingAs($this->siswa)
            ->postJson(route('siswa.kuis.pelanggaran', $this->kuis->id_kuis));

        $response->assertNotFound();
    }
}
