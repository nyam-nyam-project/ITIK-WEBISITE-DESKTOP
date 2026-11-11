<?php

namespace Tests\Feature;

use App\Models\Kuis;
use App\Models\Mapel;
use App\Models\Materi;
use App\Models\TahunAjaran;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Pengujian constraint: satu materi hanya boleh punya 1 pre-test & 1 post-test aktif.
 * Dan materi dikunci saat edit pre-test/post-test.
 */
class KuisPrePostTestUnikTest extends TestCase
{
    private User $guru;
    private Mapel $mapel;
    private Materi $materi;
    private TahunAjaran $ta;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertTrue($this->app->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));

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

        Schema::create('pengajaran', function (Blueprint $table) {
            $table->increments('id_pengajaran');
            $table->string('id_user', 50);
            $table->string('id_mapel', 50);
            $table->string('id_tahun_ajaran', 50)->nullable();
            $table->timestamps();
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
            $table->enum('tipe', ['pilihan_ganda', 'essay'])->default('pilihan_ganda');
            $table->text('pertanyaan');
            $table->string('jawaban', 5)->nullable();
            $table->decimal('bobot', 5, 2)->default(1);
            $table->string('tingkat_kesulitan', 50)->nullable();
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
    }

    private function seedBaseData(): void
    {
        $this->ta = TahunAjaran::create([
            'id_tahun_ajaran' => 'TA01',
            'nama'            => '2026/2027 Ganjil',
            'is_aktif'        => true,
        ]);

        $this->mapel = Mapel::create([
            'id_mapel'   => 'MP01',
            'nama_mapel' => 'TIK',
            'kode_mapel' => 'TIK01',
            'is_aktif'   => true,
        ]);

        $this->guru = User::create([
            'id_user'      => 'GURU01',
            'nama_lengkap' => 'Guru Test',
            'role'         => 'guru',
            'status'       => 'aktif',
            'password'     => bcrypt('password'),
        ]);

        // Hubungkan guru ke mapel (pengajaran)
        DB::table('pengajaran')->insert([
            'id_user'         => 'GURU01',
            'id_mapel'        => 'MP01',
            'id_tahun_ajaran' => 'TA01',
        ]);

        $this->materi = Materi::create([
            'id_materi' => 'MAT01',
            'judul'     => 'Bab 1 - Pengenalan TIK',
            'bab'       => '1',
            'id_mapel'  => 'MP01',
            'id_user'   => 'GURU01',
        ]);
    }

    private function buatKuisPretest(string $idKuis = 'K01'): Kuis
    {
        $kuis = Kuis::create([
            'id_kuis'          => $idKuis,
            'judul'            => 'Pre-test Bab 1',
            'kategori'         => 'pre-test',
            'tingkat_kesulitan'=> 'mudah',
            'id_mapel'         => 'MP01',
            'id_tahun_ajaran'  => 'TA01',
            'id_user'          => 'GURU01',
            'is_aktif'         => true,
            'status_publikasi' => 'draft',
        ]);
        $kuis->materi()->sync(['MAT01']);
        return $kuis;
    }

    // ─────────────────────────────────────────────────────────
    // 1. STORE: Duplikat pre-test ditolak
    // ─────────────────────────────────────────────────────────

    public function test_store_menolak_pretest_kedua_untuk_materi_yang_sama(): void
    {
        // Buat pre-test pertama
        $this->buatKuisPretest('K01');

        $response = $this->actingAs($this->guru)->post(route('kuis.store'), [
            'judul'             => 'Pre-test Duplikat',
            'kategori'          => 'pre-test',
            'id_mapel'          => 'MP01',
            'tingkat_kesulitan' => 'mudah',
            'materi'            => ['MAT01'],
        ]);

        $response->assertSessionHasErrors('materi');
        $this->assertStringContainsString(
            'Pre-test aktif',
            session('errors')->get('materi')[0]
        );

        // Hanya 1 pre-test yang seharusnya ada
        $this->assertSame(1, Kuis::where('kategori', 'pre-test')->count());
    }

    public function test_store_menolak_posttest_kedua_untuk_materi_yang_sama(): void
    {
        // Buat post-test pertama langsung via DB
        $kuis = Kuis::create([
            'id_kuis'          => 'K01',
            'judul'            => 'Post-test Pertama',
            'kategori'         => 'post-test',
            'tingkat_kesulitan'=> 'mudah',
            'id_mapel'         => 'MP01',
            'id_tahun_ajaran'  => 'TA01',
            'id_user'          => 'GURU01',
            'is_aktif'         => true,
            'status_publikasi' => 'draft',
        ]);
        $kuis->materi()->sync(['MAT01']);

        $response = $this->actingAs($this->guru)->post(route('kuis.store'), [
            'judul'             => 'Post-test Duplikat',
            'kategori'          => 'post-test',
            'id_mapel'          => 'MP01',
            'tingkat_kesulitan' => 'mudah',
            'materi'            => ['MAT01'],
        ]);

        $response->assertSessionHasErrors('materi');
        $this->assertSame(1, Kuis::where('kategori', 'post-test')->count());
    }

    public function test_store_mengizinkan_pretest_dan_posttest_untuk_materi_yang_sama(): void
    {
        // Pre-test dan post-test boleh berdampingan untuk materi yang sama
        $this->buatKuisPretest('K01');

        $response = $this->actingAs($this->guru)->post(route('kuis.store'), [
            'judul'             => 'Post-test Bab 1',
            'kategori'          => 'post-test',
            'id_mapel'          => 'MP01',
            'tingkat_kesulitan' => 'mudah',
            'materi'            => ['MAT01'],
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('kuis.index', ['mapel' => 'MP01']));
        $this->assertSame(1, Kuis::where('kategori', 'pre-test')->count());
        $this->assertSame(1, Kuis::where('kategori', 'post-test')->count());
    }

    public function test_store_mengizinkan_pretest_jika_yang_lama_nonaktif(): void
    {
        // Pre-test lama dinonaktifkan → boleh buat pre-test baru
        $kuis = Kuis::create([
            'id_kuis'          => 'K_LAMA',
            'judul'            => 'Pre-test Lama (nonaktif)',
            'kategori'         => 'pre-test',
            'tingkat_kesulitan'=> 'mudah',
            'id_mapel'         => 'MP01',
            'id_tahun_ajaran'  => 'TA01',
            'id_user'          => 'GURU01',
            'is_aktif'         => false, // nonaktif!
            'status_publikasi' => 'draft',
        ]);
        $kuis->materi()->sync(['MAT01']);

        $response = $this->actingAs($this->guru)->post(route('kuis.store'), [
            'judul'             => 'Pre-test Baru',
            'kategori'          => 'pre-test',
            'id_mapel'          => 'MP01',
            'tingkat_kesulitan' => 'mudah',
            'materi'            => ['MAT01'],
        ]);

        $response->assertSessionHasNoErrors();
        $this->assertSame(2, Kuis::where('kategori', 'pre-test')->count());
    }

    // ─────────────────────────────────────────────────────────
    // 2. CREATE: Redirect jika sudah ada pre-test/post-test
    // ─────────────────────────────────────────────────────────

    public function test_create_redirect_jika_pretest_sudah_ada_untuk_materi(): void
    {
        $this->buatKuisPretest('K01');

        $response = $this->actingAs($this->guru)->get(route('kuis.create', [
            'materi'   => 'MAT01',
            'kategori' => 'pre-test',
            'mapel'    => 'MP01',
        ]));

        $response->assertRedirect(route('materi.show', 'MAT01'));
        $response->assertSessionHas('error');
        $this->assertStringContainsString(
            'Pre-test',
            session('error')
        );
    }

    public function test_create_menampilkan_form_jika_belum_ada_pretest(): void
    {
        $response = $this->actingAs($this->guru)->get(route('kuis.create', [
            'materi'   => 'MAT01',
            'kategori' => 'pre-test',
            'mapel'    => 'MP01',
        ]));

        $response->assertOk();
        $response->assertViewIs('guru.kuis.create');
    }

    // ─────────────────────────────────────────────────────────
    // 3. EDIT: Materi dikunci untuk pre-test/post-test
    // ─────────────────────────────────────────────────────────

    public function test_edit_mengirim_flag_materi_dikunci_untuk_pretest(): void
    {
        $kuis = $this->buatKuisPretest('K01');

        $response = $this->actingAs($this->guru)->get(route('kuis.edit', $kuis->id_kuis));

        $response->assertOk();
        $response->assertViewHas('materiDikunci', true);
    }

    public function test_edit_tidak_mengirim_flag_materi_dikunci_untuk_kuis_harian(): void
    {
        $kuis = Kuis::create([
            'id_kuis'          => 'K01',
            'judul'            => 'Kuis Harian',
            'kategori'         => 'kuis harian',
            'tingkat_kesulitan'=> 'mudah',
            'id_mapel'         => 'MP01',
            'id_tahun_ajaran'  => 'TA01',
            'id_user'          => 'GURU01',
            'is_aktif'         => true,
            'status_publikasi' => 'draft',
        ]);

        $response = $this->actingAs($this->guru)->get(route('kuis.edit', $kuis->id_kuis));

        $response->assertOk();
        $response->assertViewHas('materiDikunci', false);
    }

    // ─────────────────────────────────────────────────────────
    // 4. UPDATE: Materi pre-test tidak berubah meskipun dikirim
    // ─────────────────────────────────────────────────────────

    public function test_update_tidak_mengubah_materi_untuk_pretest(): void
    {
        $kuis = $this->buatKuisPretest('K01');

        // Buat materi kedua
        $materi2 = Materi::create([
            'id_materi' => 'MAT02',
            'judul'     => 'Bab 2 - Jaringan',
            'bab'       => '2',
            'id_mapel'  => 'MP01',
            'id_user'   => 'GURU01',
        ]);

        // Coba ubah materi ke MAT02 lewat update
        $this->actingAs($this->guru)->put(route('kuis.update', $kuis->id_kuis), [
            'judul'             => 'Pre-test Bab 1 (Edit)',
            'kategori'          => 'pre-test',
            'id_mapel'          => 'MP01',
            'tingkat_kesulitan' => 'mudah',
            'materi'            => ['MAT02'], // coba ganti
        ]);

        // Materi harus tetap MAT01, bukan MAT02
        $kuis->refresh();
        $materiIds = $kuis->materi->pluck('id_materi')->toArray();

        $this->assertContains('MAT01', $materiIds, 'MAT01 harus tetap terhubung setelah update');
        $this->assertNotContains('MAT02', $materiIds, 'MAT02 tidak boleh ditambahkan ke pre-test via update');
    }

    public function test_update_memperbarui_materi_untuk_kuis_harian(): void
    {
        $kuis = Kuis::create([
            'id_kuis'          => 'K01',
            'judul'            => 'Kuis Harian',
            'kategori'         => 'kuis harian',
            'tingkat_kesulitan'=> 'mudah',
            'id_mapel'         => 'MP01',
            'id_tahun_ajaran'  => 'TA01',
            'id_user'          => 'GURU01',
            'is_aktif'         => true,
            'status_publikasi' => 'draft',
        ]);
        $kuis->materi()->sync(['MAT01']);

        $materi2 = Materi::create([
            'id_materi' => 'MAT02',
            'judul'     => 'Bab 2',
            'bab'       => '2',
            'id_mapel'  => 'MP01',
            'id_user'   => 'GURU01',
        ]);

        $this->actingAs($this->guru)->put(route('kuis.update', $kuis->id_kuis), [
            'judul'             => 'Kuis Harian (Edit)',
            'kategori'          => 'kuis harian',
            'id_mapel'          => 'MP01',
            'tingkat_kesulitan' => 'mudah',
            'materi'            => ['MAT02'],
        ]);

        $kuis->refresh();
        $materiIds = $kuis->materi->pluck('id_materi')->toArray();

        $this->assertContains('MAT02', $materiIds, 'Kuis harian boleh ganti materi');
        $this->assertNotContains('MAT01', $materiIds, 'MAT01 lama harus digantikan');
    }
}
