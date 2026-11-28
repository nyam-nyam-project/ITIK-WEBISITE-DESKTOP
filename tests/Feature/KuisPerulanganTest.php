<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Mengerjakan;
use App\Models\Pengajaran;
use App\Models\Soal;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class KuisPerulanganTest extends TestCase
{
    private User $guru;

    private User $siswa;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertTrue($this->app->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

        $this->withoutVite();
        $this->createSchema();
        $this->seedScenario();
    }

    private function createSchema(): void
    {
        Schema::create('tm_user', function (Blueprint $table): void {
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

        Schema::create('tm_tahun_ajaran', function (Blueprint $table): void {
            $table->string('id_tahun_ajaran', 50)->primary();
            $table->string('nama', 50);
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('tm_mapel', function (Blueprint $table): void {
            $table->string('id_mapel', 50)->primary();
            $table->string('nama_mapel', 100);
            $table->string('kode_mapel', 20)->nullable();
            $table->text('deskripsi')->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('tm_kelas', function (Blueprint $table): void {
            $table->string('id_kelas', 50)->primary();
            $table->string('nama_kelas', 100);
            $table->string('tingkat', 20)->nullable();
            $table->string('jurusan', 50)->nullable();
            $table->string('id_tahun_ajaran', 50);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });

        Schema::create('tp_mapel_kelas', function (Blueprint $table): void {
            $table->increments('id_mapel_kelas');
            $table->string('id_mapel', 50);
            $table->string('id_kelas', 50);
        });

        Schema::create('tr_anggota_kelas', function (Blueprint $table): void {
            $table->increments('id_anggota_kelas');
            $table->string('id_kelas', 50);
            $table->string('id_user', 50);
        });

        Schema::create('tr_pengajaran', function (Blueprint $table): void {
            $table->increments('id_pengajaran');
            $table->string('id_user', 50);
            $table->string('id_kelas', 50);
            $table->string('id_mapel', 50);
            $table->timestamp('created_at')->nullable()->useCurrent();
        });

        Schema::create('tm_materi', function (Blueprint $table): void {
            $table->string('id_materi', 50)->primary();
            $table->string('judul', 150);
            $table->string('bab', 50)->nullable();
            $table->string('kelas', 50)->nullable();
            $table->string('id_mapel', 50);
            $table->text('deskripsi')->nullable();
            $table->string('isi_materi')->nullable();
            $table->string('link_youtube')->nullable();
            $table->string('id_user', 50);
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
            $table->string('diarsipkan_bersama', 50)->nullable();
            $table->string('tampil_jawaban', 20)->default('setelah_submit');
            $table->boolean('izinkan_perulangan')->default(true);
            $table->timestamp('created_at')->nullable()->useCurrent();
        });

        Schema::create('tp_kuis_kelas', function (Blueprint $table): void {
            $table->increments('id_kuis_kelas');
            $table->string('id_kuis', 50);
            $table->string('id_kelas', 50);
        });

        Schema::create('tp_materi_kuis', function (Blueprint $table): void {
            $table->increments('id_materi_kuis');
            $table->string('id_materi', 50);
            $table->string('id_kuis', 50);
        });

        Schema::create('tm_soal', function (Blueprint $table): void {
            $table->string('id_soal', 50)->primary();
            $table->string('id_kuis', 50);
            $table->text('pertanyaan');
            $table->string('tipe', 20)->default('pilihan_ganda');
            $table->string('opsi_a')->nullable();
            $table->string('opsi_b')->nullable();
            $table->string('opsi_c')->nullable();
            $table->string('opsi_d')->nullable();
            $table->string('opsi_e')->nullable();
            $table->string('jawaban', 5)->nullable();
            $table->decimal('bobot', 5, 2)->default(100);
            $table->string('gambar')->nullable();
            $table->string('tingkat_kesulitan', 50)->nullable();
        });

        Schema::create('tr_sesi_kuis', function (Blueprint $table): void {
            $table->increments('id_sesi');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->integer('percobaan_ke')->default(1);
            $table->dateTime('waktu_mulai')->nullable();
            $table->dateTime('waktu_selesai')->nullable();
            $table->integer('jumlah_pelanggaran')->default(0);
            $table->boolean('is_locked')->default(false);
        });

        Schema::create('tr_draft_jawaban', function (Blueprint $table): void {
            $table->increments('id_draft');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->string('id_soal', 50);
            $table->string('jawaban_dipilih', 10)->nullable();
            $table->text('jawaban_teks')->nullable();
            $table->timestamps();
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

        Schema::create('tr_nilai_essay', function (Blueprint $table): void {
            $table->increments('id_nilai_essay');
            $table->unsignedInteger('id_mengerjakan');
            $table->string('id_soal', 50);
            $table->text('jawaban_teks')->nullable();
            $table->decimal('nilai_diperoleh', 5, 2)->nullable();
        });

        Schema::create('tr_remedial', function (Blueprint $table): void {
            $table->increments('id_remedial');
            $table->string('id_kuis_remedial', 50)->unique();
            $table->string('id_kuis_asal', 50);
        });

        Schema::create('tr_remedial_siswa', function (Blueprint $table): void {
            $table->increments('id_remedial_siswa');
            $table->string('id_kuis_remedial', 50);
            $table->string('id_user', 50);
            $table->unsignedInteger('id_mengerjakan_asal')->nullable();
            $table->string('status', 30)->default('ditugaskan');
        });
    }

    private function seedScenario(): void
    {
        $password = Hash::make('password');

        $this->guru = User::create([
            'id_user' => 'G001',
            'nama_lengkap' => 'Guru Satu',
            'role' => 'guru',
            'status' => 'aktif',
            'password' => $password,
        ]);

        $this->siswa = User::create([
            'id_user' => 'S001',
            'nama_lengkap' => 'Siswa Satu',
            'role' => 'siswa',
            'status' => 'aktif',
            'password' => $password,
        ]);

        TahunAjaran::create([
            'id_tahun_ajaran' => 'TA01',
            'nama' => '2026/2027 Ganjil',
            'is_aktif' => true,
        ]);

        Mapel::create([
            'id_mapel' => 'MP01',
            'nama_mapel' => 'Teknologi Informasi',
            'kode_mapel' => 'TIK',
            'is_aktif' => true,
        ]);

        Kelas::create([
            'id_kelas' => 'K01',
            'nama_kelas' => 'X TKJ 1',
            'tingkat' => 'X',
            'jurusan' => 'TKJ',
            'id_tahun_ajaran' => 'TA01',
            'is_aktif' => true,
        ]);

        DB::table('tp_mapel_kelas')->insert([
            'id_mapel' => 'MP01',
            'id_kelas' => 'K01',
        ]);

        DB::table('tr_anggota_kelas')->insert([
            'id_kelas' => 'K01',
            'id_user' => 'S001',
        ]);

        Pengajaran::create([
            'id_user' => 'G001',
            'id_mapel' => 'MP01',
            'id_kelas' => 'K01',
        ]);
    }

    public function test_guru_dapat_mengatur_checkbox_izinkan_perulangan_saat_buat_dan_edit_kuis(): void
    {
        $resCreate = $this->actingAs($this->guru)->get(route('kuis.create'));
        $resCreate->assertOk();
        $resCreate->assertSee('name="izinkan_perulangan"', false);

        // Simpan kuis baru tanpa mengizinkan perulangan (0)
        $resStore = $this->actingAs($this->guru)->post(route('kuis.store'), [
            'judul' => 'Kuis Sekali Coba',
            'kategori' => 'kuis harian',
            'id_mapel' => 'MP01',
            'kelas' => ['K01'],
            'tingkat_kesulitan' => 'mudah',
            'alokasi_waktu' => 15,
            'kkm' => 75,
            'izinkan_perulangan' => '0',
        ]);
        $resStore->assertRedirect(route('kuis.index', ['mapel' => 'MP01']));

        $kuis = Kuis::where('judul', 'Kuis Sekali Coba')->firstOrFail();
        $this->assertFalse($kuis->bolehDiulang());

        // Edit menjadi izinkan perulangan (1)
        $resEdit = $this->actingAs($this->guru)->get(route('kuis.edit', $kuis->id_kuis));
        $resEdit->assertOk();
        $resEdit->assertSee('name="izinkan_perulangan"', false);

        $resUpdate = $this->actingAs($this->guru)->put(route('kuis.update', $kuis->id_kuis), [
            'judul' => 'Kuis Boleh Ulang',
            'kategori' => 'kuis harian',
            'id_mapel' => 'MP01',
            'kelas' => ['K01'],
            'tingkat_kesulitan' => 'mudah',
            'alokasi_waktu' => 15,
            'kkm' => 75,
            'izinkan_perulangan' => '1',
        ]);
        $resUpdate->assertRedirect(route('kuis.index', ['mapel' => 'MP01']));
        $this->assertTrue($kuis->fresh()->bolehDiulang());
    }

    public function test_siswa_tidak_dapat_mengulang_kuis_jika_perulangan_dinonaktifkan(): void
    {
        $kuis = Kuis::create([
            'id_kuis' => 'K_SEKALI',
            'judul' => 'Kuis Hanya 1 Kali',
            'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => 'MP01',
            'id_tahun_ajaran' => 'TA01',
            'alokasi_waktu' => 20,
            'kkm' => 75,
            'id_user' => 'G001',
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
            'izinkan_perulangan' => false,
        ]);

        DB::table('tp_kuis_kelas')->insert([
            'id_kuis' => 'K_SEKALI',
            'id_kelas' => 'K01',
        ]);

        Soal::create([
            'id_soal' => 'SOAL_1',
            'id_kuis' => 'K_SEKALI',
            'pertanyaan' => 'Apa itu CPU?',
            'tipe' => 'pilihan_ganda',
            'opsi_a' => 'Prosesor',
            'opsi_b' => 'Memori',
            'opsi_c' => 'Monitor',
            'opsi_d' => 'Keyboard',
            'jawaban' => 'A',
            'bobot' => 100,
        ]);

        // Siswa sudah menyelesaikan percobaan ke-1 dengan nilai di bawah KKM
        Mengerjakan::create([
            'id_kuis' => 'K_SEKALI',
            'id_user' => 'S001',
            'nilai' => 50,
            'tanggal' => now(),
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
        ]);

        // 1. Daftar kuis siswa tidak menampilkan tombol Kerjakan Ulang
        $resIndex = $this->actingAs($this->siswa)->get(route('siswa.kuis.index', ['mapel' => 'MP01']));
        $resIndex->assertOk();
        $resIndex->assertSee('Sudah Dikerjakan (Lihat Hasil)');
        $resIndex->assertDontSee('Kerjakan Ulang');

        // 2. Halaman hasil tidak menampilkan tombol Kerjakan Ulang Kuis
        $resHasil = $this->actingAs($this->siswa)->get(route('siswa.kuis.hasil', 'K_SEKALI'));
        $resHasil->assertOk();
        $resHasil->assertDontSee('Kerjakan Ulang Kuis');

        // 3. Akses langsung route mulai ditolak dan diarahkan ke halaman hasil
        $resMulai = $this->actingAs($this->siswa)->get(route('siswa.kuis.mulai', 'K_SEKALI'));
        $resMulai->assertRedirect(route('siswa.kuis.hasil', 'K_SEKALI'));
        $resMulai->assertSessionHas('error');

        $this->assertDatabaseCount('tr_sesi_kuis', 0);
    }

    public function test_siswa_dapat_mengulang_kuis_jika_perulangan_diizinkan(): void
    {
        $kuis = Kuis::create([
            'id_kuis' => 'K_ULANG',
            'judul' => 'Kuis Boleh Ulang',
            'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => 'MP01',
            'id_tahun_ajaran' => 'TA01',
            'alokasi_waktu' => 20,
            'kkm' => 75,
            'id_user' => 'G001',
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
            'izinkan_perulangan' => true,
        ]);

        DB::table('tp_kuis_kelas')->insert([
            'id_kuis' => 'K_ULANG',
            'id_kelas' => 'K01',
        ]);

        Soal::create([
            'id_soal' => 'SOAL_2',
            'id_kuis' => 'K_ULANG',
            'pertanyaan' => 'Apa itu RAM?',
            'tipe' => 'pilihan_ganda',
            'opsi_a' => 'Memori',
            'opsi_b' => 'CPU',
            'opsi_c' => 'Harddisk',
            'opsi_d' => 'Mouse',
            'jawaban' => 'A',
            'bobot' => 100,
        ]);

        Mengerjakan::create([
            'id_kuis' => 'K_ULANG',
            'id_user' => 'S001',
            'nilai' => 50,
            'tanggal' => now(),
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
        ]);

        $resMulai = $this->actingAs($this->siswa)->get(route('siswa.kuis.mulai', 'K_ULANG'));
        $resMulai->assertRedirect(route('siswa.kuis.kerjakan', 'K_ULANG'));

        $this->assertDatabaseHas('tr_sesi_kuis', [
            'id_kuis' => 'K_ULANG',
            'id_user' => 'S001',
            'percobaan_ke' => 2,
        ]);
    }
}
