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
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class KuisHapusOtorisasiTest extends TestCase
{
    private User $guruA;

    private User $guruBSamaMapelKelas;

    private User $guruCBedaKelas;

    private User $guruDBedaMapel;

    private User $admin;

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

        Schema::create('tp_materi_kelas', function (Blueprint $table): void {
            $table->increments('id_materi_kelas');
            $table->string('id_materi', 50);
            $table->string('id_kelas', 50);
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
            $table->string('tingkat_kesulitan', 50)->nullable();
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
    }

    private function seedScenario(): void
    {
        $password = Hash::make('password');

        $this->guruA = User::create([
            'id_user' => 'G001',
            'nama_lengkap' => 'Guru A Pembuat',
            'role' => 'guru',
            'status' => 'aktif',
            'password' => $password,
        ]);

        $this->guruBSamaMapelKelas = User::create([
            'id_user' => 'G002',
            'nama_lengkap' => 'Guru B Sama Mapel Kelas',
            'role' => 'guru',
            'status' => 'aktif',
            'password' => $password,
        ]);

        $this->guruCBedaKelas = User::create([
            'id_user' => 'G003',
            'nama_lengkap' => 'Guru C Beda Kelas',
            'role' => 'guru',
            'status' => 'aktif',
            'password' => $password,
        ]);

        $this->guruDBedaMapel = User::create([
            'id_user' => 'G004',
            'nama_lengkap' => 'Guru D Beda Mapel',
            'role' => 'guru',
            'status' => 'aktif',
            'password' => $password,
        ]);

        $this->admin = User::create([
            'id_user' => 'ADM01',
            'nama_lengkap' => 'Admin Sekolah',
            'role' => 'admin',
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

        Mapel::create([
            'id_mapel' => 'MP02',
            'nama_mapel' => 'Matematika',
            'kode_mapel' => 'MTK',
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

        Kelas::create([
            'id_kelas' => 'K02',
            'nama_kelas' => 'X TKJ 2',
            'tingkat' => 'X',
            'jurusan' => 'TKJ',
            'id_tahun_ajaran' => 'TA01',
            'is_aktif' => true,
        ]);

        // Guru A mengajar MP01 di K01
        Pengajaran::create([
            'id_user' => 'G001',
            'id_mapel' => 'MP01',
            'id_kelas' => 'K01',
        ]);

        // Guru B juga mengajar MP01 di K01 (sama mapel dan sama kelas)
        Pengajaran::create([
            'id_user' => 'G002',
            'id_mapel' => 'MP01',
            'id_kelas' => 'K01',
        ]);

        // Guru C mengajar MP01 di K02 (sama mapel tetapi beda kelas)
        Pengajaran::create([
            'id_user' => 'G003',
            'id_mapel' => 'MP01',
            'id_kelas' => 'K02',
        ]);

        // Guru D mengajar MP02 di K01 (beda mapel)
        Pengajaran::create([
            'id_user' => 'G004',
            'id_mapel' => 'MP02',
            'id_kelas' => 'K01',
        ]);

        // Kuis aktif buatan Guru A untuk MP01 kelas K01
        Kuis::create([
            'id_kuis' => 'K_AKTIF',
            'judul' => 'Kuis TIK Kelas X TKJ 1',
            'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => 'MP01',
            'id_tahun_ajaran' => 'TA01',
            'alokasi_waktu' => 20,
            'kkm' => 75,
            'id_user' => 'G001',
            'status_publikasi' => 'draft',
            'is_aktif' => true,
        ]);
        DB::table('tp_kuis_kelas')->insert([
            'id_kuis' => 'K_AKTIF',
            'id_kelas' => 'K01',
        ]);

        // Kuis arsip buatan Guru A untuk MP01 kelas K01 dengan riwayat siswa
        Kuis::create([
            'id_kuis' => 'K_ARSIP_RIWAYAT',
            'judul' => 'Kuis Arsip Berriwayat X TKJ 1',
            'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => 'MP01',
            'id_tahun_ajaran' => 'TA01',
            'alokasi_waktu' => 20,
            'kkm' => 75,
            'id_user' => 'G001',
            'status_publikasi' => 'terbit',
            'is_aktif' => false,
        ]);
        DB::table('tp_kuis_kelas')->insert([
            'id_kuis' => 'K_ARSIP_RIWAYAT',
            'id_kelas' => 'K01',
        ]);

        Mengerjakan::create([
            'id_kuis' => 'K_ARSIP_RIWAYAT',
            'id_user' => 'S001',
            'nilai' => 88,
            'tanggal' => now(),
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
        ]);
    }

    public function test_guru_mapel_dan_kelas_sama_dapat_mengarsipkan_dan_menghapus_permanen_kuis_guru_lain(): void
    {
        // 1. Daftar kuis aktif menampilkan tombol Arsip untuk Guru B
        $resIndex = $this->actingAs($this->guruBSamaMapelKelas)->get(route('kuis.index', ['mapel' => 'MP01']));
        $resIndex->assertOk();
        $resIndex->assertSee('Kuis TIK Kelas X TKJ 1');
        $resIndex->assertSee(route('kuis.destroy', 'K_AKTIF'));

        // 2. Guru B berhasil mengarsipkan kuis buatan Guru A
        $resDestroy = $this->actingAs($this->guruBSamaMapelKelas)->delete(route('kuis.destroy', 'K_AKTIF'));
        $resDestroy->assertRedirect(route('kuis.index', ['mapel' => 'MP01']));
        $resDestroy->assertSessionHas('success');
        $this->assertDatabaseHas('tm_kuis', ['id_kuis' => 'K_AKTIF', 'is_aktif' => 0]);

        // 3. Halaman Kotak Arsip menampilkan kuis arsip milik Guru A kepada Guru B
        $resArsip = $this->actingAs($this->guruBSamaMapelKelas)->get(route('guru.arsip.index', ['tab' => 'kuis']));
        $resArsip->assertOk();
        $resArsip->assertSee('Kuis TIK Kelas X TKJ 1');
        $resArsip->assertSee('Kuis Arsip Berriwayat X TKJ 1');
        $resArsip->assertSee(route('guru.arsip.kuis.konfirmasi-hapus', 'K_ARSIP_RIWAYAT'));

        // 4. Guru B dapat membuka halaman konfirmasi hapus permanen kuis berriwayat
        $resKonfirmasi = $this->actingAs($this->guruBSamaMapelKelas)->get(route('guru.arsip.kuis.konfirmasi-hapus', 'K_ARSIP_RIWAYAT'));
        $resKonfirmasi->assertOk();
        $resKonfirmasi->assertSee('Kuis Arsip Berriwayat X TKJ 1');

        // 5. Guru B berhasil menghapus permanen kuis berriwayat setelah konfirmasi
        $resHapusPermanen = $this->actingAs($this->guruBSamaMapelKelas)->delete(
            route('guru.arsip.kuis.hapus-permanen', 'K_ARSIP_RIWAYAT'),
            ['konfirmasi_hapus_nilai' => '1']
        );
        $resHapusPermanen->assertRedirect(route('guru.arsip.index', ['tab' => 'kuis']));
        $resHapusPermanen->assertSessionHas('success');
        $this->assertDatabaseMissing('tm_kuis', ['id_kuis' => 'K_ARSIP_RIWAYAT']);
        $this->assertDatabaseMissing('tr_mengerjakan', ['id_kuis' => 'K_ARSIP_RIWAYAT']);
    }

    public function test_guru_mapel_sama_tetapi_kelas_berbeda_ditolak_403_saat_menghapus_kuis_guru_lain(): void
    {
        // Daftar kuis aktif tidak menampilkan tombol Arsip untuk kuis di luar kelas Guru C
        $resIndex = $this->actingAs($this->guruCBedaKelas)->get(route('kuis.index', ['mapel' => 'MP01']));
        $resIndex->assertOk();
        $resIndex->assertDontSee('Arsipkan kuis ini?');

        // Kotak Arsip tidak menampilkan kuis arsip di luar kelas Guru C
        $resArsip = $this->actingAs($this->guruCBedaKelas)->get(route('guru.arsip.index', ['tab' => 'kuis']));
        $resArsip->assertOk();
        $resArsip->assertDontSee('Kuis Arsip Berriwayat X TKJ 1');

        // Mengarsipkan kuis ditolak 403
        $this->actingAs($this->guruCBedaKelas)
            ->delete(route('kuis.destroy', 'K_AKTIF'))
            ->assertForbidden();

        // Konfirmasi hapus permanen ditolak 403
        $this->actingAs($this->guruCBedaKelas)
            ->get(route('guru.arsip.kuis.konfirmasi-hapus', 'K_ARSIP_RIWAYAT'))
            ->assertForbidden();

        // Hapus permanen ditolak 403
        $this->actingAs($this->guruCBedaKelas)
            ->delete(route('guru.arsip.kuis.hapus-permanen', 'K_ARSIP_RIWAYAT'), ['konfirmasi_hapus_nilai' => '1'])
            ->assertForbidden();

        $this->assertDatabaseHas('tm_kuis', ['id_kuis' => 'K_AKTIF', 'is_aktif' => 1]);
        $this->assertDatabaseHas('tm_kuis', ['id_kuis' => 'K_ARSIP_RIWAYAT', 'is_aktif' => 0]);
    }

    public function test_guru_mapel_berbeda_ditolak_403_saat_menghapus_kuis_guru_lain(): void
    {
        $this->actingAs($this->guruDBedaMapel)
            ->delete(route('kuis.destroy', 'K_AKTIF'))
            ->assertForbidden();

        $this->actingAs($this->guruDBedaMapel)
            ->get(route('guru.arsip.kuis.konfirmasi-hapus', 'K_ARSIP_RIWAYAT'))
            ->assertForbidden();

        $this->actingAs($this->guruDBedaMapel)
            ->delete(route('guru.arsip.kuis.hapus-permanen', 'K_ARSIP_RIWAYAT'), ['konfirmasi_hapus_nilai' => '1'])
            ->assertForbidden();

        $this->assertDatabaseHas('tm_kuis', ['id_kuis' => 'K_AKTIF', 'is_aktif' => 1]);
        $this->assertDatabaseHas('tm_kuis', ['id_kuis' => 'K_ARSIP_RIWAYAT', 'is_aktif' => 0]);
    }

    public function test_admin_diizinkan_pada_otorisasi_hapus_kuis_siapa_saja(): void
    {
        $kuis = Kuis::findOrFail('K_AKTIF');

        $this->assertTrue($kuis->dapatDihapusOleh($this->admin));
    }
}
