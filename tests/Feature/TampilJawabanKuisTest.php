<?php

namespace Tests\Feature;

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

class TampilJawabanKuisTest extends TestCase
{
    private User $siswa;
    private User $guru;
    private Kuis $kuis;
    private Soal $soal1;
    private Soal $soal2;
    private TahunAjaran $tahunAjaran;
    private Mapel $mapel;

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
            // Kolom baru: kapan evaluasi soal ditampilkan ke siswa
            $table->string('tampil_jawaban', 20)->default('setelah_submit');
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
            'id_user'      => 'G001',
            'nama_lengkap' => 'Guru Pengajar',
            'role'         => 'guru',
            'status'       => 'aktif',
            'password'     => bcrypt('password'),
        ]);

        $this->siswa = User::create([
            'id_user'      => 'S001',
            'nama_lengkap' => 'Siswa Satu',
            'role'         => 'siswa',
            'status'       => 'aktif',
            'password'     => bcrypt('password'),
        ]);

        $this->tahunAjaran = TahunAjaran::create([
            'id_tahun_ajaran' => 'TA001',
            'nama'            => '2026/2027',
            'is_aktif'        => true,
        ]);

        $this->mapel = Mapel::create([
            'id_mapel'   => 'MP001',
            'kode_mapel' => 'DJK',
            'nama_mapel' => 'Dasar Jaringan Komputer',
            'is_aktif'   => true,
        ]);

        // Kuis dasar — tampil_jawaban diisi tiap tes
        $this->kuis = Kuis::create([
            'id_kuis'          => 'K001',
            'judul'            => 'Kuis Tes Tampil',
            'kategori'         => 'kuis harian',
            'tingkat_kesulitan'=> 'mudah',
            'id_mapel'         => 'MP001',
            'id_tahun_ajaran'  => 'TA001',
            'kkm'              => 70.00,
            'id_user'          => 'G001',
            'status_publikasi' => 'terbit',
            'is_aktif'         => true,
            'tampil_jawaban'   => 'setelah_submit', // default; di-override tiap tes
        ]);

        $this->soal1 = Soal::create([
            'id_soal'    => 'SOA001',
            'id_kuis'    => 'K001',
            'pertanyaan' => 'Apa itu IP Address?',
            'opsi_a'     => 'Alamat jaringan',
            'opsi_b'     => 'Nama komputer',
            'opsi_c'     => 'Protokol transfer',
            'opsi_d'     => 'Router',
            'jawaban'    => 'A',
            'bobot'      => 50,
        ]);

        $this->soal2 = Soal::create([
            'id_soal'    => 'SOA002',
            'id_kuis'    => 'K001',
            'pertanyaan' => 'Apa singkatan TCP?',
            'opsi_a'     => 'Transfer Control Protocol',
            'opsi_b'     => 'Transmission Control Protocol',
            'opsi_c'     => 'Technical Connection Protocol',
            'opsi_d'     => 'Total Channel Protocol',
            'jawaban'    => 'B',
            'bobot'      => 50,
        ]);
    }

    /**
     * Buat hasil kuis lengkap: sesi selesai, mengerjakan, draft jawaban.
     */
    private function buatHasilKuis(string $idUser, float $nilai): void
    {
        SesiKuis::create([
            'id_kuis'      => 'K001',
            'id_user'      => $idUser,
            'percobaan_ke' => 1,
            'waktu_mulai'  => now()->subMinutes(10),
            'waktu_selesai'=> now(),
        ]);

        Mengerjakan::create([
            'id_kuis'          => 'K001',
            'id_user'          => $idUser,
            'nilai'            => $nilai,
            'percobaan_ke'     => 1,
            'status_pengerjaan'=> 'final',
            'tanggal'          => now()->toDateString(),
        ]);

        // Jawaban: soal1 benar (A), soal2 salah (A alih-alih B)
        DraftJawaban::create([
            'id_kuis'         => 'K001',
            'id_user'         => $idUser,
            'id_soal'         => 'SOA001',
            'jawaban_dipilih' => 'A',
        ]);
        DraftJawaban::create([
            'id_kuis'         => 'K001',
            'id_user'         => $idUser,
            'id_soal'         => 'SOA002',
            'jawaban_dipilih' => 'A', // salah
        ]);
    }

    // ─── SKENARIO 1: tampil_jawaban = 'setelah_submit' ───────────────────────

    public function test_setelah_submit_menampilkan_evaluasi_soal(): void
    {
        $this->kuis->update(['tampil_jawaban' => 'setelah_submit']);
        $this->buatHasilKuis('S001', 50.00); // nilai < KKM tidak masalah

        $response = $this->actingAs($this->siswa)
            ->get(route('siswa.kuis.hasil', 'K001'));

        $response->assertOk();
        $response->assertViewHas('tampilDetail', true);
        $response->assertViewHas('soal', fn ($soal) => $soal->isNotEmpty());
        // Statistik benar/salah dikirim ke view
        $response->assertViewHas('totalBenar', 1);
        $response->assertViewHas('totalSalah', 1);
    }

    // ─── SKENARIO 2: tampil_jawaban = 'tidak' ────────────────────────────────

    public function test_tidak_menyembunyikan_evaluasi_soal(): void
    {
        $this->kuis->update(['tampil_jawaban' => 'tidak']);
        $this->buatHasilKuis('S001', 90.00);

        $response = $this->actingAs($this->siswa)
            ->get(route('siswa.kuis.hasil', 'K001'));

        $response->assertOk();
        $response->assertViewHas('tampilDetail', false);
        // Soal dikosongkan oleh controller — view tidak mendapat data evaluasi
        $response->assertViewHas('soal', fn ($soal) => $soal->isEmpty());
        $response->assertViewHas('totalBenar', null);
        $response->assertViewHas('totalSalah', null);
    }

    // ─── SKENARIO 3: tampil_jawaban = 'setelah_kkm', nilai >= KKM ────────────

    public function test_setelah_kkm_menampilkan_evaluasi_jika_lulus(): void
    {
        $this->kuis->update(['tampil_jawaban' => 'setelah_kkm']);
        $this->buatHasilKuis('S001', 80.00); // nilai >= KKM 70

        $response = $this->actingAs($this->siswa)
            ->get(route('siswa.kuis.hasil', 'K001'));

        $response->assertOk();
        $response->assertViewHas('tampilDetail', true);
        $response->assertViewHas('soal', fn ($soal) => $soal->isNotEmpty());
    }

    // ─── SKENARIO 4: tampil_jawaban = 'setelah_kkm', nilai < KKM ────────────

    public function test_setelah_kkm_menyembunyikan_evaluasi_jika_tidak_lulus(): void
    {
        $this->kuis->update(['tampil_jawaban' => 'setelah_kkm']);
        $this->buatHasilKuis('S001', 50.00); // nilai < KKM 70

        $response = $this->actingAs($this->siswa)
            ->get(route('siswa.kuis.hasil', 'K001'));

        $response->assertOk();
        $response->assertViewHas('tampilDetail', false);
        $response->assertViewHas('soal', fn ($soal) => $soal->isEmpty());
    }
}
