<?php

namespace Tests\Feature;

use App\Models\Kelas;
use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Soal;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SoalGambarTest extends TestCase
{
    private User $guru;

    private User $siswa;

    private TahunAjaran $tahunAjaran;

    private Mapel $mapel;

    private Kelas $kelas;

    private Kuis $kuis;

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
        Schema::dropIfExists('tr_draft_jawaban');
        Schema::dropIfExists('tr_mengerjakan');
        Schema::dropIfExists('tr_sesi_kuis');
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
            'nama_lengkap' => 'Guru Pengampu',
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

        DB::table('tr_pengajaran')->insert([
            'id_user' => $this->guru->id_user,
            'id_kelas' => $this->kelas->id_kelas,
            'id_mapel' => $this->mapel->id_mapel,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('tp_anggota_kelas')->insert([
            'id_kelas' => $this->kelas->id_kelas,
            'id_user' => $this->siswa->id_user,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->kuis = Kuis::create([
            'id_kuis' => 'KUIS01',
            'judul' => 'Kuis Algoritma',
            'deskripsi' => 'Deskripsi kuis',
            'kategori' => 'kuis',
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => $this->mapel->id_mapel,
            'id_tahun_ajaran' => $this->tahunAjaran->id_tahun_ajaran,
            'alokasi_waktu' => 30,
            'kkm' => 75.00,
            'id_user' => $this->guru->id_user,
            'status_publikasi' => 'draft',
            'is_aktif' => true,
        ]);

        DB::table('tp_kuis_kelas')->insert([
            'id_kuis' => $this->kuis->id_kuis,
            'id_kelas' => $this->kelas->id_kelas,
        ]);
    }

    public function test_upload_file_non_gambar_ditolak(): void
    {
        Storage::fake('public');

        $filePdf = UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf');

        $response = $this->actingAs($this->guru)->post(route('kuis.soal.store', $this->kuis->id_kuis), [
            'tipe' => 'pilihan_ganda',
            'pertanyaan' => 'Pertanyaan soal dengan file dokumen bukan gambar?',
            'opsi_a' => 'Opsi A',
            'opsi_b' => 'Opsi B',
            'opsi_c' => 'Opsi C',
            'opsi_d' => 'Opsi D',
            'jawaban' => 'A',
            'gambar' => $filePdf,
        ]);

        $response->assertSessionHasErrors('gambar');
        $this->assertDatabaseMissing('tm_soal', [
            'pertanyaan' => 'Pertanyaan soal dengan file dokumen bukan gambar?',
        ]);
    }

    public function test_upload_file_terlalu_besar_ditolak(): void
    {
        Storage::fake('public');

        // Batas maksimal adalah 2048 KB (2 MB)
        $fileBesar = UploadedFile::fake()->image('gambar_raksasa.png')->size(2049);

        $response = $this->actingAs($this->guru)->post(route('kuis.soal.store', $this->kuis->id_kuis), [
            'tipe' => 'pilihan_ganda',
            'pertanyaan' => 'Pertanyaan soal dengan gambar melebihi batas ukuran?',
            'opsi_a' => 'Opsi A',
            'opsi_b' => 'Opsi B',
            'opsi_c' => 'Opsi C',
            'opsi_d' => 'Opsi D',
            'jawaban' => 'A',
            'gambar' => $fileBesar,
        ]);

        $response->assertSessionHasErrors('gambar');
        $this->assertDatabaseMissing('tm_soal', [
            'pertanyaan' => 'Pertanyaan soal dengan gambar melebihi batas ukuran?',
        ]);
    }

    public function test_gambar_tersimpan_di_disk_public(): void
    {
        Storage::fake('public');

        $gambar = UploadedFile::fake()->image('diagram_jaringan.jpg', 600, 400);

        $response = $this->actingAs($this->guru)->post(route('kuis.soal.store', $this->kuis->id_kuis), [
            'tipe' => 'pilihan_ganda',
            'pertanyaan' => 'Perhatikan gambar diagram jaringan berikut, apa jenis topologinya?',
            'opsi_a' => 'Star',
            'opsi_b' => 'Bus',
            'opsi_c' => 'Ring',
            'opsi_d' => 'Mesh',
            'jawaban' => 'A',
            'gambar' => $gambar,
        ]);

        $response->assertRedirect(route('kuis.soal.index', $this->kuis->id_kuis));
        $response->assertSessionHas('success');

        $soal = Soal::where('pertanyaan', 'Perhatikan gambar diagram jaringan berikut, apa jenis topologinya?')->first();
        $this->assertNotNull($soal);
        $this->assertNotNull($soal->gambar);
        $this->assertStringStartsWith('soal/', $soal->gambar);

        Storage::disk('public')->assertExists($soal->gambar);
    }

    public function test_gambar_lama_terhapus_saat_diganti(): void
    {
        Storage::fake('public');

        // 1. Simpan file awal di storage fake
        $pathLama = 'soal/gambar_lama_123.jpg';
        Storage::disk('public')->put($pathLama, 'konten-gambar-lama');

        $soal = Soal::create([
            'id_soal' => 'S01',
            'id_kuis' => $this->kuis->id_kuis,
            'tipe' => 'pilihan_ganda',
            'pertanyaan' => 'Soal untuk pengujian penggantian gambar',
            'opsi_a' => 'A',
            'opsi_b' => 'B',
            'opsi_c' => 'C',
            'opsi_d' => 'D',
            'jawaban' => 'B',
            'gambar' => $pathLama,
        ]);

        Storage::disk('public')->assertExists($pathLama);

        // 2. Update dengan file baru
        $gambarBaru = UploadedFile::fake()->image('gambar_baru_456.png', 400, 300);

        $response = $this->actingAs($this->guru)->put(route('kuis.soal.update', [$this->kuis->id_kuis, $soal->id_soal]), [
            'tipe' => 'pilihan_ganda',
            'pertanyaan' => 'Soal setelah gambarnya diperbarui',
            'opsi_a' => 'A',
            'opsi_b' => 'B',
            'opsi_c' => 'C',
            'opsi_d' => 'D',
            'jawaban' => 'B',
            'gambar' => $gambarBaru,
        ]);

        $response->assertRedirect(route('kuis.soal.index', $this->kuis->id_kuis));
        $response->assertSessionHas('success');

        $soal->refresh();
        $this->assertNotSame($pathLama, $soal->gambar);
        $this->assertStringStartsWith('soal/', $soal->gambar);

        // File lama wajib terhapus
        Storage::disk('public')->assertMissing($pathLama);
        // File baru wajib ada di disk public
        Storage::disk('public')->assertExists($soal->gambar);
    }

    public function test_gambar_terhapus_saat_soal_dihapus(): void
    {
        Storage::fake('public');

        $pathGambar = 'soal/gambar_akan_dihapus.png';
        Storage::disk('public')->put($pathGambar, 'konten-gambar');

        $soal = Soal::create([
            'id_soal' => 'S02',
            'id_kuis' => $this->kuis->id_kuis,
            'tipe' => 'pilihan_ganda',
            'pertanyaan' => 'Soal yang akan dihapus beserta gambarnya',
            'opsi_a' => 'A',
            'opsi_b' => 'B',
            'opsi_c' => 'C',
            'opsi_d' => 'D',
            'jawaban' => 'A',
            'gambar' => $pathGambar,
        ]);

        Storage::disk('public')->assertExists($pathGambar);

        $response = $this->actingAs($this->guru)->delete(route('kuis.soal.destroy', [$this->kuis->id_kuis, $soal->id_soal]));
        $response->assertRedirect(route('kuis.soal.index', $this->kuis->id_kuis));

        $this->assertDatabaseMissing('tm_soal', ['id_soal' => 'S02']);
        Storage::disk('public')->assertMissing($pathGambar);
    }

    public function test_gambar_tidak_muncul_di_respons_api(): void
    {
        $soal = Soal::create([
            'id_soal' => 'S03',
            'id_kuis' => $this->kuis->id_kuis,
            'tipe' => 'pilihan_ganda',
            'pertanyaan' => 'Soal rahasia ber-gambar untuk tes proteksi API',
            'opsi_a' => 'A',
            'opsi_b' => 'B',
            'opsi_c' => 'C',
            'opsi_d' => 'D',
            'jawaban' => 'C',
            'gambar' => 'soal/secret_image_123.jpg',
        ]);

        // 1. Uji serialisasi model langsung (toArray / jsonSerialize)
        $arrayModel = $soal->toArray();
        $this->assertArrayNotHasKey('gambar', $arrayModel, 'Kolom gambar wajib disembunyikan lewat $hidden pada model Soal');

        // 2. Uji endpoint API Guru: GET /api/guru/kuis/{kuis}
        $responseGuru = $this->actingAs($this->guru, 'sanctum')->getJson('/api/guru/kuis/'.$this->kuis->id_kuis);
        $responseGuru->assertOk();
        $responseGuru->assertJsonMissing(['gambar' => 'soal/secret_image_123.jpg']);
        $guruData = $responseGuru->json('data.soal');
        $this->assertNotEmpty($guruData);
        foreach ($guruData as $item) {
            $this->assertArrayNotHasKey('gambar', $item, 'Respons API guru tidak boleh memuat field gambar');
        }

        // 3. Uji endpoint API Siswa: GET /api/siswa/kuis/{kuis}
        $this->kuis->update(['status_publikasi' => 'terbit']);

        DB::table('tr_sesi_kuis')->insert([
            'id_kuis' => $this->kuis->id_kuis,
            'id_user' => $this->siswa->id_user,
            'percobaan_ke' => 1,
            'waktu_mulai' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $responseSiswa = $this->actingAs($this->siswa, 'sanctum')->getJson('/api/siswa/kuis/'.$this->kuis->id_kuis);
        $responseSiswa->assertOk();
        $responseSiswa->assertJsonMissing(['gambar' => 'soal/secret_image_123.jpg']);
        $siswaData = $responseSiswa->json('data.soal');
        $this->assertNotEmpty($siswaData);
        foreach ($siswaData as $item) {
            $this->assertArrayNotHasKey('gambar', $item, 'Respons API siswa tidak boleh memuat field gambar');
        }
    }
}
