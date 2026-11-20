<?php

namespace Tests\Feature;

use App\Models\DraftJawaban;
use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Mengerjakan;
use App\Models\NilaiEssay;
use App\Models\RemedialSiswa;
use App\Models\SesiKuis;
use App\Models\Soal;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AuditPerbaikanKuisTest extends TestCase
{
    private User $guru;

    private User $siswa1;

    private User $siswa2;

    private TahunAjaran $tahunAjaran;

    private Mapel $mapel;

    private Kelas $kelas;

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

        Schema::create('tp_kuis_kelas', function (Blueprint $table) {
            $table->string('id_kuis', 50);
            $table->string('id_kelas', 50);
            $table->primary(['id_kuis', 'id_kelas']);
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
            $table->timestamps();
        });

        Schema::create('tr_sesi_kuis', function (Blueprint $table) {
            $table->increments('id_sesi');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->unsignedInteger('percobaan_ke')->default(1);
            $table->dateTime('waktu_mulai');
            $table->dateTime('waktu_selesai')->nullable();
            $table->unsignedInteger('jumlah_pelanggaran')->default(0);
            $table->timestamps();
        });

        Schema::create('tr_draft_jawaban', function (Blueprint $table) {
            $table->increments('id_draft');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->string('id_soal', 50);
            $table->string('jawaban_dipilih', 5)->nullable();
            $table->text('jawaban_teks')->nullable();
            $table->timestamp('updated_at')->nullable();
            $table->unique(['id_kuis', 'id_user', 'id_soal']);
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

        Schema::create('tr_remedial_siswa', function (Blueprint $table) {
            $table->increments('id_remedial_siswa');
            $table->string('id_kuis_remedial', 50);
            $table->unsignedInteger('id_mengerjakan_asal');
            $table->string('id_guru', 50)->nullable();
            $table->date('tanggal_ditugaskan')->nullable();
            $table->string('status', 20)->default('ditugaskan');
        });

        Schema::create('tr_nilai_essay', function (Blueprint $table) {
            $table->increments('id_nilai_essay');
            $table->unsignedInteger('id_mengerjakan');
            $table->string('id_soal', 50);
            $table->text('jawaban_teks')->nullable();
            $table->decimal('nilai', 5, 2)->nullable();
            $table->text('catatan_guru')->nullable();
            $table->string('id_penilai', 50)->nullable();
            $table->timestamp('dinilai_at')->nullable();
        });
    }

    private function seedBaseData(): void
    {
        $this->tahunAjaran = TahunAjaran::create([
            'id_tahun_ajaran' => 'TA01',
            'nama' => '2026/2027',
            'is_aktif' => true,
        ]);

        $this->mapel = Mapel::create([
            'id_mapel' => 'MP01',
            'kode_mapel' => 'TIK',
            'nama_mapel' => 'Teknologi Informasi',
            'is_aktif' => true,
        ]);

        $this->kelas = Kelas::create([
            'id_kelas' => 'KLS01',
            'nama_kelas' => 'X TKJ 1',
            'tingkat' => 'X',
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'is_aktif' => true,
        ]);

        $this->guru = User::create([
            'id_user' => 'G001',
            'nomor_induk' => '19800101',
            'nama_lengkap' => 'Budi Guru',
            'role' => 'guru',
            'status' => 'aktif',
            'password' => bcrypt('password'),
        ]);

        $this->siswa1 = User::create([
            'id_user' => 'S001',
            'nomor_induk' => '202601',
            'nama_lengkap' => 'Ahmad Siswa',
            'role' => 'siswa',
            'status' => 'aktif',
            'kelas' => $this->kelas->id_kelas,
            'password' => bcrypt('password'),
        ]);

        $this->siswa2 = User::create([
            'id_user' => 'S002',
            'nomor_induk' => '202602',
            'nama_lengkap' => 'Bambang Siswa',
            'role' => 'siswa',
            'status' => 'aktif',
            'kelas' => $this->kelas->id_kelas,
            'password' => bcrypt('password'),
        ]);

        DB::table('tp_anggota_kelas')->insert([
            ['id_kelas' => $this->kelas->id_kelas, 'id_user' => $this->siswa1->id_user],
            ['id_kelas' => $this->kelas->id_kelas, 'id_user' => $this->siswa2->id_user],
        ]);

        DB::table('tp_mapel_kelas')->insert([
            ['id_mapel' => $this->mapel->id_mapel, 'id_kelas' => $this->kelas->id_kelas],
        ]);

        DB::table('tr_pengajaran')->insert([
            'id_user' => $this->guru->id_user,
            'id_kelas' => $this->kelas->id_kelas,
            'id_mapel' => $this->mapel->id_mapel,
        ]);
    }

    /**
     * C4: Penugasan remedial dicek ulang saat simpan dan submit.
     * Jika siswa tidak lagi punya penugasan remedial berstatus ditugaskan, simpanJawaban dan submit harus menolak (403).
     */
    public function test_c4_simpan_dan_submit_remedial_menolak_403_jika_status_bukan_ditugaskan(): void
    {
        $kuisRemedial = Kuis::create([
            'id_kuis' => 'KUIS_REM',
            'judul' => 'Kuis Remedial TIK',
            'kategori' => 'remedial',
            'tingkat_kesulitan' => 'sedang',
            'id_mapel' => $this->mapel->id_mapel,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'alokasi_waktu' => 30,
            'kkm' => 75,
            'id_user' => $this->guru->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);

        $soalRem = Soal::create([
            'id_soal' => 'SOAL_REM_1',
            'id_kuis' => $kuisRemedial->id_kuis,
            'pertanyaan' => 'Apa itu IP address?',
            'tipe' => 'pilihan_ganda',
            'opsi_a' => 'A',
            'opsi_b' => 'B',
            'opsi_c' => 'C',
            'opsi_d' => 'D',
            'opsi_e' => 'E',
            'jawaban' => 'A',
            'bobot' => 10,
        ]);

        $mengerjakanAsal = Mengerjakan::create([
            'id_kuis' => $kuisRemedial->id_kuis,
            'id_user' => $this->siswa1->id_user,
            'nilai' => 50,
            'tanggal' => now()->toDateString(),
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
            'status_validasi' => 'sudah divalidasi',
        ]);

        // Remedial berstatus 'selesai' (bukan 'ditugaskan')
        RemedialSiswa::create([
            'id_kuis_remedial' => $kuisRemedial->id_kuis,
            'id_mengerjakan_asal' => $mengerjakanAsal->id_mengerjakan,
            'id_guru' => $this->guru->id_user,
            'tanggal_ditugaskan' => now()->toDateString(),
            'status' => 'selesai',
        ]);

        SesiKuis::create([
            'id_kuis' => $kuisRemedial->id_kuis,
            'id_user' => $this->siswa1->id_user,
            'percobaan_ke' => 1,
            'waktu_mulai' => now(),
            'waktu_selesai' => null,
            'jumlah_pelanggaran' => 0,
        ]);

        // C4: simpanJawaban harus menolak (403)
        $responseSimpan = $this->actingAs($this->siswa1)
            ->postJson(route('siswa.kuis.jawaban', $kuisRemedial->id_kuis), [
                'id_soal' => $soalRem->id_soal,
                'jawaban' => 'A',
            ]);

        $responseSimpan->assertStatus(403);

        // C4: submit harus menolak (403)
        $responseSubmit = $this->actingAs($this->siswa1)
            ->post(route('siswa.kuis.submit', $kuisRemedial->id_kuis));

        $responseSubmit->assertStatus(403);
    }

    /**
     * C5: Soal essay tidak menerima jawaban A-E.
     * simpanJawaban harus menolak dengan 422 jika soal bertipe essay dan request berisi jawaban A-E.
     */
    public function test_c5_soal_essay_tidak_menerima_jawaban_opsi_a_sampai_e_menolak_422(): void
    {
        $kuis = Kuis::create([
            'id_kuis' => 'KUIS_ESSAY',
            'judul' => 'Kuis Campuran',
            'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'sedang',
            'id_mapel' => $this->mapel->id_mapel,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'alokasi_waktu' => 30,
            'kkm' => 75,
            'id_user' => $this->guru->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);

        DB::table('tp_kuis_kelas')->insert([
            'id_kuis' => $kuis->id_kuis,
            'id_kelas' => $this->kelas->id_kelas,
        ]);

        $soalEssay = Soal::create([
            'id_soal' => 'SOAL_ESSAY_1',
            'id_kuis' => $kuis->id_kuis,
            'pertanyaan' => 'Jelaskan cara kerja protokol TCP/IP!',
            'tipe' => 'essay',
            'bobot' => 20,
        ]);

        SesiKuis::create([
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $this->siswa1->id_user,
            'percobaan_ke' => 1,
            'waktu_mulai' => now(),
            'waktu_selesai' => null,
            'jumlah_pelanggaran' => 0,
        ]);

        // Kirim jawaban pilihan ganda (A) ke soal bertipe essay
        $response = $this->actingAs($this->siswa1)
            ->postJson(route('siswa.kuis.jawaban', $kuis->id_kuis), [
                'id_soal' => $soalEssay->id_soal,
                'jawaban' => 'A',
            ]);

        // Harus ditolak dengan status HTTP 422
        $response->assertStatus(422);
    }

    /**
     * C12: Hasil pengerjaan milik siswa lain tidak bisa dibuka.
     * Pastikan hasil hanya membaca pengerjaan milik Auth::user(), dan siswa lain mendapat 403 atau 404 (atau tidak bisa melihat pengerjaan siswa lain).
     */
    public function test_c12_hasil_pengerjaan_hanya_membaca_milik_auth_user_dan_siswa_lain_tidak_bisa_melihat(): void
    {
        $kuis = Kuis::create([
            'id_kuis' => 'KUIS_HASIL',
            'judul' => 'Ulangan Harian Jaringan',
            'kategori' => 'ulangan',
            'tingkat_kesulitan' => 'sedang',
            'id_mapel' => $this->mapel->id_mapel,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'alokasi_waktu' => 30,
            'kkm' => 75,
            'id_user' => $this->guru->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);

        DB::table('tp_kuis_kelas')->insert([
            'id_kuis' => $kuis->id_kuis,
            'id_kelas' => $this->kelas->id_kelas,
        ]);

        $soal = Soal::create([
            'id_soal' => 'SOAL_H_1',
            'id_kuis' => $kuis->id_kuis,
            'pertanyaan' => 'Topologi bus menggunakan terminator?',
            'tipe' => 'pilihan_ganda',
            'opsi_a' => 'Ya',
            'opsi_b' => 'Tidak',
            'jawaban' => 'A',
            'bobot' => 10,
        ]);

        // Siswa 1 telah mengerjakan dan mendapat nilai 100
        Mengerjakan::create([
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $this->siswa1->id_user,
            'nilai' => 100,
            'tanggal' => now()->toDateString(),
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
            'status_validasi' => 'sudah divalidasi',
        ]);

        DraftJawaban::create([
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $this->siswa1->id_user,
            'id_soal' => $soal->id_soal,
            'jawaban_dipilih' => 'A',
        ]);

        // Siswa 2 mengakses endpoint hasil kuis tersebut (siswa 2 belum mengerjakan kuis ini)
        $responseSiswa2 = $this->actingAs($this->siswa2)
            ->get(route('siswa.kuis.hasil', $kuis->id_kuis));

        // Karena Siswa 2 belum memiliki pengerjaan, harus redirect dengan error dan TIDAK menampilkan hasil/jawaban Siswa 1
        $responseSiswa2->assertRedirect(route('siswa.kuis.index'));
        $responseSiswa2->assertSessionHas('error', 'Hasil kuis belum tersedia.');

        // Ketika Siswa 2 sudah mengerjakan kuis ini sendiri dengan nilai 40
        Mengerjakan::create([
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $this->siswa2->id_user,
            'nilai' => 40,
            'tanggal' => now()->toDateString(),
            'percobaan_ke' => 1,
            'status_pengerjaan' => 'final',
            'status_validasi' => 'sudah divalidasi',
        ]);

        DraftJawaban::create([
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $this->siswa2->id_user,
            'id_soal' => $soal->id_soal,
            'jawaban_dipilih' => 'B',
        ]);

        // Sekarang Siswa 2 mengakses hasil kuis: hanya melihat nilainya sendiri (40), BUKAN 100 milik Siswa 1
        $responseSiswa2Final = $this->actingAs($this->siswa2)
            ->get(route('siswa.kuis.hasil', $kuis->id_kuis));

        $responseSiswa2Final->assertOk();
        $responseSiswa2Final->assertSee('40');
        $this->assertEquals($this->siswa2->id_user, $responseSiswa2Final->viewData('hasil')->id_user);
    }

    /**
     * C13: Jawaban essay yang terlambat tetap disimpan selama masih dalam batas toleransi (grace period 2 menit)
     * dan diberi penanda Terlambat beserta timestamp pengumpulannya.
     */
    public function test_c13_jawaban_essay_dalam_grace_period_tetap_disimpan_dan_diberi_penanda_terlambat(): void
    {
        $kuis = Kuis::create([
            'id_kuis' => 'KUIS_C13',
            'judul' => 'Kuis Essay Jaringan',
            'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'sedang',
            'id_mapel' => $this->mapel->id_mapel,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'alokasi_waktu' => 30,
            'kkm' => 75,
            'id_user' => $this->guru->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);

        DB::table('tp_kuis_kelas')->insert([
            'id_kuis' => $kuis->id_kuis,
            'id_kelas' => $this->kelas->id_kelas,
        ]);

        $soalEssay = Soal::create([
            'id_soal' => 'SOAL_C13_1',
            'id_kuis' => $kuis->id_kuis,
            'pertanyaan' => 'Jelaskan fungsi switch!',
            'tipe' => 'essay',
            'bobot' => 10,
        ]);

        // Mulai 31 menit yang lalu, dengan alokasi 30 menit (terlambat 1 menit, masih dalam grace period 2 menit)
        $sesi = SesiKuis::create([
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $this->siswa1->id_user,
            'percobaan_ke' => 1,
            'waktu_mulai' => now()->subMinutes(31),
            'waktu_selesai' => null,
            'jumlah_pelanggaran' => 0,
        ]);

        $response = $this->actingAs($this->siswa1)
            ->post(route('siswa.kuis.submit', $kuis->id_kuis), [
                'jawaban_teks' => [
                    $soalEssay->id_soal => 'Switch berfungsi menghubungkan perangkat jaringan.',
                ],
            ]);

        $response->assertRedirect(route('siswa.kuis.hasil', $kuis->id_kuis));

        // Jawaban essay harus tetap tersimpan di draft_jawaban
        $this->assertDatabaseHas('tr_draft_jawaban', [
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $this->siswa1->id_user,
            'id_soal' => $soalEssay->id_soal,
            'jawaban_teks' => 'Switch berfungsi menghubungkan perangkat jaringan.',
        ]);

        // Dan tersimpan di tr_nilai_essay dengan catatan penanda Terlambat
        $nilaiEssay = NilaiEssay::where('id_soal', $soalEssay->id_soal)->first();
        $this->assertNotNull($nilaiEssay);
        $this->assertSame('Switch berfungsi menghubungkan perangkat jaringan.', $nilaiEssay->jawaban_teks);
        $this->assertStringContainsString('Terlambat', (string) $nilaiEssay->catatan_guru);
    }

    /**
     * C13: Jika sudah melewati batas toleransi keterlambatan (lebih dari 2 menit),
     * submission ditolak.
     */
    public function test_c13_submit_melewati_grace_period_ditolak(): void
    {
        $kuis = Kuis::create([
            'id_kuis' => 'KUIS_C13_LATE',
            'judul' => 'Kuis Terlewat',
            'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'sedang',
            'id_mapel' => $this->mapel->id_mapel,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'alokasi_waktu' => 30,
            'kkm' => 75,
            'id_user' => $this->guru->id_user,
            'status_publikasi' => 'terbit',
            'is_aktif' => true,
        ]);

        DB::table('tp_kuis_kelas')->insert([
            'id_kuis' => $kuis->id_kuis,
            'id_kelas' => $this->kelas->id_kelas,
        ]);

        $soal = Soal::create([
            'id_soal' => 'SOAL_C13_2',
            'id_kuis' => $kuis->id_kuis,
            'pertanyaan' => 'Soal test',
            'tipe' => 'pilihan_ganda',
            'opsi_a' => 'A',
            'opsi_b' => 'B',
            'jawaban' => 'A',
            'bobot' => 10,
        ]);

        // Mulai 35 menit yang lalu (alokasi 30 menit, terlambat 5 menit, > toleransi 2 menit)
        SesiKuis::create([
            'id_kuis' => $kuis->id_kuis,
            'id_user' => $this->siswa1->id_user,
            'percobaan_ke' => 1,
            'waktu_mulai' => now()->subMinutes(35),
            'waktu_selesai' => null,
            'jumlah_pelanggaran' => 0,
        ]);

        $response = $this->actingAs($this->siswa1)
            ->post(route('siswa.kuis.submit', $kuis->id_kuis));

        // Harus ditolak dan dialihkan kembali dengan pesan error
        $response->assertRedirect(route('siswa.kuis.index'));
        $response->assertSessionHas('error');
    }
}
