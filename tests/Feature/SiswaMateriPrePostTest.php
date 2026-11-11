<?php

namespace Tests\Feature;

use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\Mengerjakan;
use App\Models\Progress;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Pengujian gerbang adaptif Pre-test -> Materi -> Post-test untuk siswa
 * pada 4 skenario.
 */
class SiswaMateriPrePostTest extends TestCase
{
    private User $siswa;
    private User $guru;
    private Mapel $mapel;
    private TahunAjaran $ta;

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

        Schema::create('kelas', function (Blueprint $table) {
            $table->string('id_kelas', 50)->primary();
            $table->string('nama_kelas', 100);
            $table->string('tingkat', 20)->nullable();
            $table->string('id_tahun_ajaran', 50)->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->string('tampil_jawaban', 20)->default('setelah_submit');
            $table->timestamps();
        });

        Schema::create('anggota_kelas', function (Blueprint $table) {
            $table->increments('id_anggota_kelas');
            $table->string('id_kelas', 50);
            $table->string('id_user', 50);
            $table->timestamp('created_at')->nullable()->useCurrent();
        });

        Schema::create('mapel_kelas', function (Blueprint $table) {
            $table->increments('id_mapel_kelas');
            $table->string('id_mapel', 50);
            $table->string('id_kelas', 50);
            $table->timestamp('created_at')->nullable()->useCurrent();
        });

        Schema::create('progress', function (Blueprint $table) {
            $table->string('id_progress', 50)->primary();
            $table->string('status', 50)->nullable();
            $table->date('tanggal')->nullable();
            $table->string('id_materi', 50);
            $table->string('id_user', 50);
        });

        Schema::create('kuis', function (Blueprint $table) {
            $table->string('id_kuis', 50)->primary();
            $table->string('judul', 150);
            $table->text('deskripsi')->nullable();
            $table->string('kategori', 50);
            $table->string('tingkat_kesulitan', 20)->default('mudah');
            $table->string('id_mapel', 50);
            $table->string('id_tahun_ajaran', 50)->nullable();
            $table->unsignedInteger('alokasi_waktu')->nullable();
            $table->decimal('kkm', 5, 2)->nullable();
            $table->dateTime('waktu_mulai')->nullable();
            $table->dateTime('waktu_selesai')->nullable();
            $table->string('id_user', 50)->nullable();
            $table->string('status_publikasi', 20)->default('draft');
            $table->string('id_publisher', 50)->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->string('tampil_jawaban', 20)->default('setelah_submit');
            $table->timestamp('created_at')->nullable()->useCurrent();
        });

        Schema::create('materi_kuis', function (Blueprint $table) {
            $table->increments('id_materi_kuis');
            $table->string('id_materi', 50);
            $table->string('id_kuis', 50);
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
            $table->dateTime('waktu_mulai');
            $table->dateTime('waktu_selesai')->nullable();
            $table->timestamps();
        });

        Schema::create('draft_jawaban', function (Blueprint $table) {
            $table->increments('id_draft');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->string('id_soal', 50);
            $table->string('jawaban_dipilih', 10)->nullable();
            $table->timestamps();
        });

        Schema::create('mengerjakan', function (Blueprint $table) {
            $table->increments('id_mengerjakan');
            $table->string('id_user', 50);
            $table->string('id_kuis', 50);
            $table->decimal('nilai', 5, 2)->nullable();
            $table->string('status_pengerjaan', 20)->default('final');
            $table->unsignedInteger('percobaan_ke')->default(1);
            $table->dateTime('tanggal');
            $table->string('status_validasi', 30)->default('belum divalidasi');
            $table->string('id_validator', 50)->nullable();
        });

        Schema::create('remedial_siswa', function (Blueprint $table) {
            $table->increments('id_remedial_siswa');
            $table->string('id_kuis_remedial', 50);
            $table->unsignedInteger('id_mengerjakan_asal');
            $table->string('id_guru', 50)->nullable();
            $table->date('tanggal_ditugaskan')->nullable();
            $table->string('status', 20)->default('ditugaskan');
            $table->timestamps();
        });
    }

    private function seedBaseData(): void
    {
        $this->ta = TahunAjaran::create([
            'id_tahun_ajaran' => 'TA01',
            'nama' => '2026/2027 Ganjil',
            'is_aktif' => true,
        ]);

        $this->mapel = Mapel::create([
            'id_mapel' => 'MP01',
            'nama_mapel' => 'Teknologi Informasi dan Komunikasi',
            'kode_mapel' => 'TIK01',
            'is_aktif' => true,
        ]);

        $this->guru = User::create([
            'id_user' => 'GURU01',
            'nama_lengkap' => 'Guru Pengampu',
            'role' => 'guru',
            'status' => 'aktif',
            'password' => bcrypt('password'),
        ]);

        $this->siswa = User::create([
            'id_user' => 'SISWA01',
            'nama_lengkap' => 'Siswa Pintar',
            'role' => 'siswa',
            'status' => 'aktif',
            'password' => bcrypt('password'),
        ]);

        // Daftarkan siswa ke kelas dan mapel
        DB::table('kelas')->insert([
            'id_kelas' => 'KLS01',
            'nama_kelas' => 'X-A',
            'id_tahun_ajaran' => 'TA01',
            'is_aktif' => true,
        ]);

        DB::table('anggota_kelas')->insert([
            'id_kelas' => 'KLS01',
            'id_user' => 'SISWA01',
        ]);

        DB::table('mapel_kelas')->insert([
            'id_mapel' => 'MP01',
            'id_kelas' => 'KLS01',
        ]);
    }

    private function buatKuis(string $idKuis, string $judul, string $kategori, ?string $idMateri = null): Kuis
    {
        $kuis = Kuis::create([
            'id_kuis' => $idKuis,
            'judul' => $judul,
            'kategori' => $kategori,
            'id_mapel' => $this->mapel->id_mapel,
            'id_tahun_ajaran' => $this->ta->id_tahun_ajaran,
            'alokasi_waktu' => 30,
            'kkm' => 75,
            'is_aktif' => true,
            'status_publikasi' => 'terbit',
        ]);

        if ($idMateri) {
            DB::table('materi_kuis')->insert([
                'id_materi' => $idMateri,
                'id_kuis' => $idKuis,
            ]);
        }

        return $kuis;
    }

    /**
     * Skenario 1: Pre-test & Post-test ada di Materi
     * - Materi terkunci sebelum Pre-test diselesaikan
     * - Selesai materi tidak bisa diproses sebelum Pre-test selesai
     * - Post-test terkunci sebelum materi ditandai selesai
     * - Setelah Pre-test selesai -> Materi terbuka
     * - Setelah Materi ditandai selesai -> Post-test terbuka
     */
    public function test_skenario_1_pre_dan_post_test_lengkap(): void
    {
        $materi = Materi::create([
            'id_materi' => 'MAT01',
            'judul' => 'Materi Skenario 1',
            'id_mapel' => $this->mapel->id_mapel,
            'id_user' => $this->guru->id_user,
            'isi_materi' => 'Konten teks pembelajaran',
        ]);

        $preTest = $this->buatKuis('KUIS_PRE_1', 'Pre-test Materi 1', 'pre-test', $materi->id_materi);
        $postTest = $this->buatKuis('KUIS_POST_1', 'Post-test Materi 1', 'post-test', $materi->id_materi);

        $this->actingAs($this->siswa);

        // 1. Coba buka materi saat pre-test belum selesai -> Ditolak / redirect
        $responseShow = $this->get(route('siswa.materi.show', $materi->id_materi));
        $responseShow->assertRedirect(route('siswa.materi.index', ['mapel' => $materi->id_mapel]));
        $responseShow->assertSessionHas('error');

        // 2. Coba tandai selesai materi saat pre-test belum selesai -> Ditolak
        $responseSelesai = $this->post(route('siswa.materi.selesai', $materi->id_materi));
        $responseSelesai->assertRedirect(route('siswa.materi.index', ['mapel' => $materi->id_mapel]));
        $responseSelesai->assertSessionHas('error');

        // 3. Coba akses post-test saat materi belum selesai -> Ditolak
        $responsePostTest = $this->get(route('siswa.kuis.mulai', $postTest->id_kuis));
        $responsePostTest->assertSessionHas('error');

        // 4. Siswa menyelesaikan Pre-test
        Mengerjakan::create([
            'id_user' => $this->siswa->id_user,
            'id_kuis' => $preTest->id_kuis,
            'nilai' => 80,
            'status_pengerjaan' => 'final',
            'tanggal' => now(),
        ]);

        // 5. Materi sekarang TERBUKA
        $responseShowUnlocked = $this->get(route('siswa.materi.show', $materi->id_materi));
        $responseShowUnlocked->assertOk();
        $responseShowUnlocked->assertSee('Materi Skenario 1');

        // 6. Post-test MASIH terkunci karena materi belum ditandai selesai
        $responsePostTest2 = $this->get(route('siswa.kuis.mulai', $postTest->id_kuis));
        $responsePostTest2->assertSessionHas('error');

        // 7. Tandai materi selesai
        $responseTandai = $this->post(route('siswa.materi.selesai', $materi->id_materi));
        $responseTandai->assertRedirect(route('siswa.materi.show', $materi->id_materi));
        $responseTandai->assertSessionHas('success');

        $this->assertDatabaseHas('progress', [
            'id_materi' => $materi->id_materi,
            'id_user' => $this->siswa->id_user,
            'status' => 'selesai',
        ]);

        // 8. Post-test sekarang TERBUKA
        $responsePostTestUnlocked = $this->get(route('siswa.kuis.mulai', $postTest->id_kuis));
        $responsePostTestUnlocked->assertRedirect(route('siswa.kuis.kerjakan', $postTest->id_kuis));
    }

    /**
     * Skenario 2: Hanya Pre-test (tanpa Post-test)
     * - Materi terkunci sebelum Pre-test diselesaikan
     * - Setelah Pre-test diselesaikan -> Materi bebas diakses
     */
    public function test_skenario_2_hanya_pre_test(): void
    {
        $materi = Materi::create([
            'id_materi' => 'MAT02',
            'judul' => 'Materi Skenario 2',
            'id_mapel' => $this->mapel->id_mapel,
            'id_user' => $this->guru->id_user,
            'isi_materi' => 'Konten teks skenario 2',
        ]);

        $preTest = $this->buatKuis('KUIS_PRE_2', 'Pre-test Materi 2', 'pre-test', $materi->id_materi);

        $this->actingAs($this->siswa);

        // Sebelum pre-test dikerjakan
        $response = $this->get(route('siswa.materi.show', $materi->id_materi));
        $response->assertRedirect(route('siswa.materi.index', ['mapel' => $materi->id_mapel]));
        $response->assertSessionHas('error');

        // Siswa selesaikan pre-test
        Mengerjakan::create([
            'id_user' => $this->siswa->id_user,
            'id_kuis' => $preTest->id_kuis,
            'nilai' => 70,
            'status_pengerjaan' => 'final',
            'tanggal' => now(),
        ]);

        // Materi terbuka
        $response2 = $this->get(route('siswa.materi.show', $materi->id_materi));
        $response2->assertOk();
        $response2->assertSee('Materi Skenario 2');
    }

    /**
     * Skenario 3: Hanya Post-test (tanpa Pre-test)
     * - Materi bebas diakses dari awal (tidak ada pre-test)
     * - Post-test terkunci sampai materi ditandai selesai
     * - Setelah materi selesai -> Post-test terbuka
     */
    public function test_skenario_3_hanya_post_test(): void
    {
        $materi = Materi::create([
            'id_materi' => 'MAT03',
            'judul' => 'Materi Skenario 3',
            'id_mapel' => $this->mapel->id_mapel,
            'id_user' => $this->guru->id_user,
            'isi_materi' => 'Konten skenario 3',
        ]);

        $postTest = $this->buatKuis('KUIS_POST_3', 'Post-test Materi 3', 'post-test', $materi->id_materi);

        $this->actingAs($this->siswa);

        // Materi langsung bisa diakses tanpa halangan pre-test
        $response = $this->get(route('siswa.materi.show', $materi->id_materi));
        $response->assertOk();
        $response->assertSee('Materi Skenario 3');

        // Post-test belum bisa diakses
        $responsePost = $this->get(route('siswa.kuis.mulai', $postTest->id_kuis));
        $responsePost->assertSessionHas('error');

        // Tandai materi selesai
        $this->post(route('siswa.materi.selesai', $materi->id_materi));

        // Post-test sekarang terbuka
        $responsePost2 = $this->get(route('siswa.kuis.mulai', $postTest->id_kuis));
        $responsePost2->assertRedirect(route('siswa.kuis.kerjakan', $postTest->id_kuis));
    }

    /**
     * Skenario 4: Tanpa Pre-test maupun Post-test
     * - Materi bebas diakses kapan saja
     * - Tidak ada kuis yang mengunci atau terkunci
     */
    public function test_skenario_4_tanpa_pre_maupun_post_test(): void
    {
        $materi = Materi::create([
            'id_materi' => 'MAT04',
            'judul' => 'Materi Bebas Skenario 4',
            'id_mapel' => $this->mapel->id_mapel,
            'id_user' => $this->guru->id_user,
            'isi_materi' => 'Konten teks materi bebas',
        ]);

        $this->actingAs($this->siswa);

        // Langsung bisa dibuka
        $response = $this->get(route('siswa.materi.show', $materi->id_materi));
        $response->assertOk();
        $response->assertSee('Materi Bebas Skenario 4');

        // Bisa ditandai selesai
        $responseSelesai = $this->post(route('siswa.materi.selesai', $materi->id_materi));
        $responseSelesai->assertRedirect(route('siswa.materi.show', $materi->id_materi));
        $responseSelesai->assertSessionHas('success');
    }

    /**
     * Memastikan index materi menampilkan badge dan status tombol yang adaptif
     */
    public function test_tampilan_index_materi_adaptif(): void
    {
        $materi = Materi::create([
            'id_materi' => 'MAT05',
            'judul' => 'Materi Tes Index',
            'id_mapel' => $this->mapel->id_mapel,
            'id_user' => $this->guru->id_user,
        ]);

        $preTest = $this->buatKuis('KUIS_PRE_5', 'Pre-test 5', 'pre-test', $materi->id_materi);
        $postTest = $this->buatKuis('KUIS_POST_5', 'Post-test 5', 'post-test', $materi->id_materi);

        $this->actingAs($this->siswa);

        // Saat belum mengerjakan pre-test:
        // Tampil tombol Mulai Pre-test dan tombol Baca Materi disable
        $resp1 = $this->get(route('siswa.materi.index', ['mapel' => $this->mapel->id_mapel]));
        $resp1->assertOk();
        $resp1->assertSee('Pre-test Wajib');
        $resp1->assertSee('Mulai Pre-test');
        $resp1->assertSee('Materi Terkunci');

        // Setelah pre-test selesai:
        Mengerjakan::create([
            'id_user' => $this->siswa->id_user,
            'id_kuis' => $preTest->id_kuis,
            'nilai' => 85,
            'status_pengerjaan' => 'final',
            'tanggal' => now(),
        ]);

        $resp2 = $this->get(route('siswa.materi.index', ['mapel' => $this->mapel->id_mapel]));
        $resp2->assertOk();
        $resp2->assertSee('Pre-test Selesai');
        $resp2->assertSee('Baca Materi');
        $resp2->assertSee('Post-test Terkunci');

        // Setelah materi selesai:
        Progress::create([
            'id_progress' => 'PROG05',
            'id_materi' => $materi->id_materi,
            'id_user' => $this->siswa->id_user,
            'status' => 'selesai',
            'tanggal' => now()->toDateString(),
        ]);

        $resp3 = $this->get(route('siswa.materi.index', ['mapel' => $this->mapel->id_mapel]));
        $resp3->assertOk();
        $resp3->assertSee('Post-test Siap');
        $resp3->assertSee('Kerjakan Post-test');
    }

    /**
     * Memastikan Post-test terkunci di halaman kuis index jika materi belum selesai,
     * dan terbuka setelah materi selesai.
     */
    public function test_post_test_terkunci_di_kuis_index_jika_materi_belum_selesai(): void
    {
        $materi = Materi::create([
            'id_materi' => 'MAT06',
            'judul' => 'Materi Tes Kuis Index',
            'id_mapel' => $this->mapel->id_mapel,
            'id_user' => $this->guru->id_user,
        ]);

        $postTest = $this->buatKuis('KUIS_POST_6', 'Post-test 6', 'post-test', $materi->id_materi);

        $this->actingAs($this->siswa);

        // Kuis index level bab
        $resp = $this->get(route('siswa.kuis.index', ['mapel' => $this->mapel->id_mapel, 'materi' => $materi->id_materi]));
        $resp->assertOk();
        $resp->assertSee('🔒 Terkunci');
        $resp->assertSee('Materi Tes Kuis Index');
        $resp->assertSee('terlebih dahulu sebelum mengerjakan Post-test');

        // Selesaikan materi
        Progress::create([
            'id_progress' => 'PROG06',
            'id_materi' => $materi->id_materi,
            'id_user' => $this->siswa->id_user,
            'status' => 'selesai',
            'tanggal' => now()->toDateString(),
        ]);

        $resp2 = $this->get(route('siswa.kuis.index', ['mapel' => $this->mapel->id_mapel, 'materi' => $materi->id_materi]));
        $resp2->assertOk();
        $resp2->assertDontSee('terlebih dahulu sebelum mengerjakan Post-test');
    }

    /**
     * Memastikan route kerjakan langsung menolak pengerjaan Post-test jika materi belum selesai.
     */
    public function test_post_test_pada_endpoint_kerjakan_ditolak_jika_materi_belum_selesai(): void
    {
        $materi = Materi::create([
            'id_materi' => 'MAT07',
            'judul' => 'Materi Tes Direct Kerjakan',
            'id_mapel' => $this->mapel->id_mapel,
            'id_user' => $this->guru->id_user,
        ]);

        $postTest = $this->buatKuis('KUIS_POST_7', 'Post-test 7', 'post-test', $materi->id_materi);

        $this->actingAs($this->siswa);

        $resp = $this->get(route('siswa.kuis.kerjakan', $postTest->id_kuis));
        $resp->assertRedirect(route('siswa.kuis.index'));
        $resp->assertSessionHas('error');
    }

    /**
     * Memastikan halaman hasil kuis pre-test menampilkan tombol direct ke materi pembelajaran.
     */
    public function test_hasil_pre_test_menampilkan_tombol_ke_materi(): void
    {
        $materi = Materi::create([
            'id_materi' => 'MAT08',
            'judul' => 'Materi Hasil Pre-test',
            'id_mapel' => $this->mapel->id_mapel,
            'id_user' => $this->guru->id_user,
        ]);

        $preTest = $this->buatKuis('KUIS_PRE_8', 'Pre-test 8', 'pre-test', $materi->id_materi);

        Mengerjakan::create([
            'id_user' => $this->siswa->id_user,
            'id_kuis' => $preTest->id_kuis,
            'nilai' => 80,
            'status_pengerjaan' => 'final',
            'tanggal' => now(),
        ]);

        $this->actingAs($this->siswa);

        $resp = $this->get(route('siswa.kuis.hasil', $preTest->id_kuis));
        $resp->assertOk();
        $resp->assertSee('Buka Materi Pembelajaran →');
        $resp->assertSee(route('siswa.materi.show', $materi->id_materi));
    }
}
