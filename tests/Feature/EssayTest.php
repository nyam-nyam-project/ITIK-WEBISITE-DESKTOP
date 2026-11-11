<?php

namespace Tests\Feature;

use App\Models\Kuis;
use App\Models\Mengerjakan;
use App\Models\NilaiEssay;
use App\Models\SesiKuis;
use App\Models\Soal;
use App\Models\User;
use App\Services\KuisSubmissionService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EssayTest extends TestCase
{
    private User $guru;
    private User $guruLain;
    private User $siswa;
    private int $nomorKuis = 0;
    private int $nomorSoal = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->assertTrue($this->app->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame('sqlite', config('database.connections.sqlite.driver'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertEmpty(config('database.connections.sqlite.url'));
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->assertSame(1, (int) DB::selectOne('PRAGMA foreign_keys')->foreign_keys);
        $this->assertSame('array', config('cache.default'));
        $this->assertSame('array', config('session.driver'));

        $this->withoutVite();
        $this->createSchema();
        $this->guru = User::create([
            'id_user' => 'G1', 'nama_lengkap' => 'Pembuat', 'role' => 'guru', 'password' => 'secret', 'status' => 'aktif', 'wajib_ganti_password' => false,
        ]);
        $this->guruLain = User::create([
            'id_user' => 'G2', 'nama_lengkap' => 'Guru lain', 'role' => 'guru', 'password' => 'secret', 'status' => 'aktif', 'wajib_ganti_password' => false,
        ]);
        $this->siswa = User::create([
            'id_user' => 'U1', 'nama_lengkap' => 'Siswa', 'role' => 'siswa', 'password' => 'secret', 'status' => 'aktif', 'wajib_ganti_password' => false,
        ]);
        DB::table('tahun_ajaran')->insert(['id_tahun_ajaran' => 'TA1', 'nama' => '2026/2027', 'is_aktif' => true]);
        DB::table('mapel')->insert(['id_mapel' => 'MP1', 'kode_mapel' => 'TIK', 'nama_mapel' => 'TIK']);
        DB::table('pengajaran')->insert([
            ['id_user' => 'G1', 'id_mapel' => 'MP1'],
            ['id_user' => 'G2', 'id_mapel' => 'MP1'],
        ]);
    }

    private function createSchema(): void
    {
        Schema::create('user', function (Blueprint $table) {
            $table->string('id_user', 50)->primary();
            $table->string('nama_lengkap', 100);
            $table->string('role', 20);
            $table->string('password');
            $table->string('status', 20)->default('aktif');
            $table->boolean('wajib_ganti_password')->default(false);
            $table->timestamps();
        });
        Schema::create('tahun_ajaran', function (Blueprint $table) {
            $table->string('id_tahun_ajaran', 50)->primary();
            $table->string('nama', 20)->unique();
            $table->boolean('is_aktif')->default(false);
            $table->timestamps();
        });
        Schema::create('mapel', function (Blueprint $table) {
            $table->string('id_mapel', 50)->primary();
            $table->string('kode_mapel', 20)->unique();
            $table->string('nama_mapel', 100);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
        Schema::create('pengajaran', function (Blueprint $table) {
            $table->increments('id_pengajaran');
            $table->string('id_user', 50);
            $table->string('id_mapel', 50);
        });
        Schema::create('kuis', function (Blueprint $table) {
            $table->string('id_kuis', 50)->primary();
            $table->string('judul', 150);
            $table->text('deskripsi')->nullable();
            $table->string('kategori', 50);
            $table->string('tingkat_kesulitan', 50)->default('mudah');
            $table->string('id_mapel', 50);
            $table->string('id_tahun_ajaran', 50);
            $table->integer('alokasi_waktu')->nullable();
            $table->decimal('kkm', 5, 2)->nullable();
            $table->timestamp('waktu_mulai')->nullable();
            $table->timestamp('waktu_selesai')->nullable();
            $table->string('id_user', 50);
            $table->string('status_publikasi', 20)->default('draft');
            $table->string('id_publisher', 50)->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->string('tampil_jawaban', 20)->default('setelah_submit');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->foreign('id_user')->references('id_user')->on('user')->restrictOnDelete();
        });
        Schema::create('materi', function (Blueprint $table) {
            $table->string('id_materi', 50)->primary();
            $table->string('judul');
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
            foreach (['a', 'b', 'c', 'd', 'e'] as $opsi) {
                $table->string('opsi_'.$opsi)->nullable();
            }
            $table->string('jawaban', 5)->nullable();
            $table->decimal('bobot', 5, 2)->default(1);
            $table->string('tingkat_kesulitan', 50)->nullable();
            $table->foreign('id_kuis')->references('id_kuis')->on('kuis')->cascadeOnDelete();
        });
        Schema::create('sesi_kuis', function (Blueprint $table) {
            $table->increments('id_sesi');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->integer('percobaan_ke')->default(1);
            $table->timestamp('waktu_mulai')->useCurrent();
            $table->timestamp('waktu_selesai')->nullable();
            $table->unsignedTinyInteger('jumlah_pelanggaran')->default(0);
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
            $table->foreign('id_kuis')->references('id_kuis')->on('kuis')->restrictOnDelete();
            $table->foreign('id_user')->references('id_user')->on('user')->cascadeOnDelete();
        });
        Schema::create('draft_jawaban', function (Blueprint $table) {
            $table->increments('id_draft');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->string('id_soal', 50);
            $table->string('jawaban_dipilih', 5)->nullable();
            $table->text('jawaban_teks')->nullable();
            $table->timestamp('updated_at')->useCurrent();
            $table->unique(['id_kuis', 'id_user', 'id_soal']);
            $table->foreign('id_soal')->references('id_soal')->on('soal')->cascadeOnDelete();
        });
        Schema::create('nilai_essay', function (Blueprint $table) {
            $table->increments('id_nilai_essay');
            $table->integer('id_mengerjakan');
            $table->string('id_soal', 50);
            $table->text('jawaban_teks')->nullable();
            $table->decimal('nilai', 5, 2)->nullable();
            $table->text('catatan_guru')->nullable();
            $table->string('id_penilai', 50)->nullable();
            $table->timestamp('dinilai_at')->nullable();
            $table->unique(['id_mengerjakan', 'id_soal']);
            $table->foreign('id_mengerjakan')->references('id_mengerjakan')->on('mengerjakan')->cascadeOnDelete();
            $table->foreign('id_soal')->references('id_soal')->on('soal')->restrictOnDelete();
            $table->foreign('id_penilai')->references('id_user')->on('user')->nullOnDelete();
        });
        Schema::create('remedial_siswa', function (Blueprint $table) {
            $table->increments('id_remedial_siswa');
            $table->integer('id_mengerjakan_asal');
            $table->string('id_kuis_remedial', 50);
            $table->string('status', 20)->default('ditugaskan');
        });
    }

    private function kuis(array $attributes = []): Kuis
    {
        return Kuis::create(array_replace([
            'id_kuis' => 'K'.++$this->nomorKuis, 'judul' => 'Kuis essay', 'kategori' => 'kuis harian',
            'tingkat_kesulitan' => 'mudah', 'id_mapel' => 'MP1', 'id_tahun_ajaran' => 'TA1',
            'alokasi_waktu' => 30, 'kkm' => 75, 'id_user' => $this->guru->id_user,
            'status_publikasi' => 'draft', 'is_aktif' => true,
        ], $attributes));
    }

    private function soal(Kuis $kuis, string $tipe = 'pilihan_ganda', float $bobot = 40): Soal
    {
        $id = 'S'.++$this->nomorSoal;

        return Soal::create([
            'id_soal' => $id, 'id_kuis' => $kuis->id_kuis, 'pertanyaan' => 'Pertanyaan '.$id,
            'tipe' => $tipe, 'bobot' => $bobot,
            'opsi_a' => $tipe === 'essay' ? null : 'A', 'opsi_b' => $tipe === 'essay' ? null : 'B',
            'opsi_c' => $tipe === 'essay' ? null : 'C', 'opsi_d' => $tipe === 'essay' ? null : 'D',
            'jawaban' => $tipe === 'essay' ? null : 'A',
        ]);
    }

    private function sesi(Kuis $kuis, int $menit = 1): SesiKuis
    {
        $kuis->update(['status_publikasi' => 'terbit']);

        return SesiKuis::create([
            'id_kuis' => $kuis->id_kuis, 'id_user' => $this->siswa->id_user,
            'percobaan_ke' => 1, 'waktu_mulai' => now()->subMinutes($menit),
        ]);
    }

    private function draft(Soal $soal, ?string $jawaban = null, ?string $teks = null): void
    {
        DB::table('draft_jawaban')->insert([
            'id_kuis' => $soal->id_kuis, 'id_user' => $this->siswa->id_user,
            'id_soal' => $soal->id_soal, 'jawaban_dipilih' => $jawaban, 'jawaban_teks' => $teks,
        ]);
    }

    private function campuran(): array
    {
        $kuis = $this->kuis();
        $pg = $this->soal($kuis);
        $essay1 = $this->soal($kuis, 'essay', 30);
        $essay2 = $this->soal($kuis, 'essay', 30);
        $this->draft($pg, 'A');
        $this->draft($essay1, null, "Jawaban tetap\nBaris kedua");
        $hasil = app(KuisSubmissionService::class)->submit($this->sesi($kuis));

        return [$kuis, $essay1, $essay2, $hasil];
    }

    private function urlNilai(Mengerjakan $hasil, Soal $soal): string
    {
        return route('guru.nilai.essay.store', [$hasil->id_mengerjakan, $soal->id_soal]);
    }

    public static function kategoriTerlarang(): array
    {
        return [['pre-test'], ['post-test']];
    }

    public static function kategoriDiizinkan(): array
    {
        return [['kuis harian'], ['ulangan'], ['remedial']];
    }

    #[DataProvider('kategoriTerlarang')]
    public function test_store_dan_update_soal_menolak_essay_di_kategori_terlarang(string $kategori): void
    {
        $kuis = $this->kuis(['kategori' => $kategori]);
        $pg = $this->soal($kuis);
        $payload = ['tipe' => 'essay', 'pertanyaan' => 'Essay baru'];
        $this->actingAs($this->guru)->postJson(route('kuis.soal.store', $kuis), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('tipe');
        $this->putJson(route('kuis.soal.update', [$kuis, $pg]), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('tipe');
        $this->assertSame('pilihan_ganda', $pg->fresh()->tipe);
        $this->assertDatabaseCount('soal', 1);
    }

    #[DataProvider('kategoriDiizinkan')]
    public function test_store_dan_update_essay_diterima_pada_kategori_yang_diizinkan(string $kategori): void
    {
        $kuis = $this->kuis(['kategori' => $kategori]);
        $this->actingAs($this->guru)->post(route('kuis.soal.store', $kuis), [
            'tipe' => 'essay', 'pertanyaan' => 'Essay baru', 'opsi_a' => 'A', 'jawaban' => 'A',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $essay = $kuis->soal()->sole();
        $this->assertSame('essay', $essay->tipe);
        $this->assertNull($essay->jawaban);
        $this->assertNull($essay->opsi_a);
        $this->assertSame(100.0, (float) $essay->bobot);
        $this->put(route('kuis.soal.update', [$kuis, $essay]), ['pertanyaan' => 'Essay diubah'])
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('essay', $essay->fresh()->tipe);
        $this->assertSame('Essay diubah', $essay->fresh()->pertanyaan);
    }

    #[DataProvider('kategoriTerlarang')]
    public function test_store_kuis_tidak_menerima_sisipan_soal_dan_update_menolak_perubahan_kategori(string $kategori): void
    {
        $kuis = $this->kuis();
        $this->soal($kuis, 'essay');
        $payload = [
            'judul' => 'Ubah kategori', 'kategori' => $kategori, 'id_mapel' => 'MP1',
            'tingkat_kesulitan' => 'mudah', 'alokasi_waktu' => 30, 'kkm' => 75,
        ];
        $this->actingAs($this->guru)->postJson(route('kuis.store'), $payload + [
            'soal' => [['tipe' => 'essay', 'pertanyaan' => 'Tidak boleh']],
        ])->assertUnprocessable()->assertJsonValidationErrors('soal');
        $this->putJson(route('kuis.update', $kuis), $payload)
            ->assertUnprocessable()->assertJsonValidationErrors('kategori');
        $this->assertSame('kuis harian', $kuis->fresh()->kategori);
        $this->assertDatabaseCount('kuis', 1);
    }

    #[DataProvider('kategoriTerlarang')]
    public function test_publish_menolak_data_essay_di_kategori_terlarang(string $kategori): void
    {
        $kuis = $this->kuis(['kategori' => $kategori]);
        $this->soal($kuis, 'essay');
        $this->actingAs($this->guru)->patch(route('kuis.publish', $kuis))
            ->assertRedirect()->assertSessionHasErrors('publish');
        $this->assertSame('draft', $kuis->fresh()->status_publikasi);
    }

    public function test_publish_essay_tidak_memerlukan_opsi_dan_kunci(): void
    {
        $kuis = $this->kuis();
        $this->soal($kuis, 'essay');
        $this->actingAs($this->guru)->patch(route('kuis.publish', $kuis))
            ->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('terbit', $kuis->fresh()->status_publikasi);
    }

    public function test_submit_campuran_pending_dengan_snapshot_termasuk_jawaban_kosong(): void
    {
        [$kuis, $essay1, $essay2, $hasil] = $this->campuran();
        $this->assertNull($hasil->nilai);
        $this->assertNull($hasil->id_validator);
        $this->assertSame('menunggu penilaian', $hasil->status_validasi);
        $this->assertSame('final', $hasil->status_pengerjaan);
        $this->assertNotNull($kuis->sesi()->sole()->waktu_selesai);
        $this->assertDatabaseCount('nilai_essay', 2);
        $this->assertDatabaseHas('nilai_essay', [
            'id_mengerjakan' => $hasil->id_mengerjakan, 'id_soal' => $essay1->id_soal,
            'jawaban_teks' => "Jawaban tetap\nBaris kedua", 'nilai' => null,
        ]);
        $this->assertDatabaseHas('nilai_essay', [
            'id_mengerjakan' => $hasil->id_mengerjakan, 'id_soal' => $essay2->id_soal,
            'jawaban_teks' => null, 'nilai' => null,
        ]);
    }

    public function test_submit_payload_essay_disimpan_ke_draft_dan_snapshot(): void
    {
        $kuis = $this->kuis();
        $pg = $this->soal($kuis);
        $essay1 = $this->soal($kuis, 'essay', 20);
        $essay2 = $this->soal($kuis, 'essay', 20);
        $kosong = $this->soal($kuis, 'essay', 20);
        $this->draft($pg, 'A');
        $this->draft($essay1, null, 'Draft lama');
        $sesi = $this->sesi($kuis);
        $jawaban = [$essay1->id_soal => "Jawaban dari payload\nBaris kedua", $essay2->id_soal => 'Jawaban baru'];

        $this->actingAs($this->siswa)->post(route('siswa.kuis.submit', $kuis), [
            'jawaban_teks' => $jawaban,
        ])->assertRedirect(route('siswa.kuis.hasil', $kuis))->assertSessionHasNoErrors();

        $hasil = $kuis->mengerjakan()->sole();
        $this->assertNull($hasil->nilai);
        $this->assertSame('menunggu penilaian', $hasil->status_validasi);
        $this->assertNotNull($sesi->fresh()->waktu_selesai);
        foreach ($jawaban as $idSoal => $teks) {
            $this->assertDatabaseHas('draft_jawaban', [
                'id_kuis' => $kuis->id_kuis, 'id_user' => $this->siswa->id_user,
                'id_soal' => $idSoal, 'jawaban_teks' => $teks,
            ]);
            $this->assertDatabaseHas('nilai_essay', [
                'id_mengerjakan' => $hasil->id_mengerjakan, 'id_soal' => $idSoal,
                'jawaban_teks' => $teks, 'nilai' => null,
            ]);
        }
        $this->assertDatabaseHas('draft_jawaban', ['id_soal' => $pg->id_soal, 'jawaban_dipilih' => 'A']);
        $this->assertDatabaseHas('nilai_essay', ['id_soal' => $kosong->id_soal, 'jawaban_teks' => null]);
        $this->assertDatabaseCount('nilai_essay', 3);
        $this->assertDatabaseCount('draft_jawaban', 3);
    }

    public function test_payload_essay_menolak_soal_asing_pg_dan_id_tidak_ada(): void
    {
        $kuis = $this->kuis();
        $essay = $this->soal($kuis, 'essay');
        $pg = $this->soal($kuis);
        $asing = $this->soal($this->kuis(), 'essay');
        $sesi = $this->sesi($kuis);
        $this->draft($essay, null, 'Tetap');

        foreach ([$asing->id_soal, $pg->id_soal, 'TIDAK_ADA'] as $idSoal) {
            $this->actingAs($this->siswa)->postJson(route('siswa.kuis.submit', $kuis), [
                'jawaban_teks' => [$essay->id_soal => 'Tidak boleh tersimpan', $idSoal => 'Ditolak'],
            ])->assertUnprocessable()->assertJsonValidationErrors('jawaban_teks');
        }
        $this->assertDatabaseCount('draft_jawaban', 1);
        $this->assertDatabaseHas('draft_jawaban', ['id_soal' => $essay->id_soal, 'jawaban_teks' => 'Tetap']);
        $this->assertDatabaseCount('mengerjakan', 0);
        $this->assertDatabaseCount('nilai_essay', 0);
        $this->assertNull($sesi->fresh()->waktu_selesai);
    }

    public function test_payload_essay_memvalidasi_bentuk_dan_menerima_jawaban_kosong(): void
    {
        $kuis = $this->kuis();
        $essay = $this->soal($kuis, 'essay', 100);
        $sesi = $this->sesi($kuis);
        $this->draft($essay, null, 'Dikosongkan oleh siswa');

        $this->actingAs($this->siswa)->postJson(route('siswa.kuis.submit', $kuis), [
            'jawaban_teks' => 'Bukan array',
        ])->assertUnprocessable()->assertJsonValidationErrors('jawaban_teks');
        foreach ([['nested'], 123, true] as $teks) {
            $this->postJson(route('siswa.kuis.submit', $kuis), [
                'jawaban_teks' => [$essay->id_soal => $teks],
            ])->assertUnprocessable()->assertJsonValidationErrors('jawaban_teks.'.$essay->id_soal);
        }
        $this->assertNull($sesi->fresh()->waktu_selesai);
        $this->assertDatabaseCount('mengerjakan', 0);
        $this->assertDatabaseHas('draft_jawaban', ['jawaban_teks' => 'Dikosongkan oleh siswa']);

        $this->post(route('siswa.kuis.submit', $kuis), [
            'jawaban_teks' => [$essay->id_soal => ''],
        ])->assertRedirect(route('siswa.kuis.hasil', $kuis))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('draft_jawaban', ['id_soal' => $essay->id_soal, 'jawaban_teks' => null]);
        $this->assertDatabaseHas('nilai_essay', ['id_soal' => $essay->id_soal, 'jawaban_teks' => null]);
    }

    public function test_submit_payload_ulang_tidak_menimpa_draft_atau_snapshot(): void
    {
        [$kuis, $essay, , $hasil] = $this->campuran();
        $sesi = $kuis->sesi()->sole();
        $payload = ['jawaban_teks' => [$essay->id_soal => 'Tidak boleh menimpa']];
        $this->actingAs($this->siswa)->post(route('siswa.kuis.submit', $kuis), $payload)
            ->assertRedirect(route('siswa.kuis.index'));
        $sesi->update(['waktu_selesai' => null]);
        $this->post(route('siswa.kuis.submit', $kuis), $payload)
            ->assertRedirect(route('siswa.kuis.hasil', $kuis));

        $this->assertNotNull($sesi->fresh()->waktu_selesai);
        $this->assertDatabaseCount('mengerjakan', 1);
        $this->assertDatabaseCount('nilai_essay', 2);
        $this->assertDatabaseHas('draft_jawaban', [
            'id_soal' => $essay->id_soal, 'jawaban_teks' => "Jawaban tetap\nBaris kedua",
        ]);
        $this->assertDatabaseHas('nilai_essay', [
            'id_mengerjakan' => $hasil->id_mengerjakan, 'id_soal' => $essay->id_soal,
            'jawaban_teks' => "Jawaban tetap\nBaris kedua",
        ]);
    }

    public function test_submit_payload_setelah_batas_waktu_memakai_draft_tersimpan(): void
    {
        $kuis = $this->kuis();
        $essay = $this->soal($kuis, 'essay', 100);
        $this->draft($essay, null, 'Sebelum waktu habis');
        $sesi = $this->sesi($kuis, 31);
        $this->actingAs($this->siswa)->post(route('siswa.kuis.submit', $kuis), [
            'jawaban_teks' => [$essay->id_soal => 'Jawaban terlambat'],
        ])->assertRedirect(route('siswa.kuis.hasil', $kuis));

        $this->assertDatabaseHas('draft_jawaban', ['id_soal' => $essay->id_soal, 'jawaban_teks' => 'Sebelum waktu habis']);
        $this->assertDatabaseHas('nilai_essay', ['id_soal' => $essay->id_soal, 'jawaban_teks' => 'Sebelum waktu habis']);
        $this->assertNull($kuis->mengerjakan()->sole()->nilai);
        $this->assertTrue($sesi->fresh()->waktu_selesai->eq($sesi->waktu_mulai->copy()->addMinutes(30)));
    }

    public function test_statistik_benar_salah_siswa_dan_guru_melewati_essay(): void
    {
        $kuis = $this->kuis();
        $benar = $this->soal($kuis, 'pilihan_ganda', 25);
        $salah = $this->soal($kuis, 'pilihan_ganda', 25);
        $this->soal($kuis, 'essay', 50);
        $this->draft($benar, 'A');
        $this->draft($salah, 'B');
        $hasil = app(KuisSubmissionService::class)->submit($this->sesi($kuis));

        $this->actingAs($this->siswa)->get(route('siswa.kuis.hasil', $kuis))
            ->assertOk()->assertViewHas('totalBenar', 1)->assertViewHas('totalSalah', 1);
        $this->actingAs($this->guru)->get(route('guru.nilai.show', $hasil))
            ->assertOk()->assertViewHas('totalBenar', 1)->assertViewHas('totalSalah', 1);
    }

    public function test_skor_nol_sah_dan_nilai_final_hanya_setelah_semua_essay_dinilai(): void
    {
        [, $essay1, $essay2, $hasil] = $this->campuran();
        $this->actingAs($this->guru)->post($this->urlNilai($hasil, $essay1), [
            'nilai' => 0, 'catatan_guru' => 'Belum tepat', 'jawaban_teks' => 'Tidak boleh menimpa snapshot',
        ])->assertRedirect(route('guru.nilai.show', $hasil));
        $this->assertNull($hasil->fresh()->nilai);
        $this->assertSame('menunggu penilaian', $hasil->fresh()->status_validasi);
        $this->assertDatabaseHas('nilai_essay', [
            'id_soal' => $essay1->id_soal, 'nilai' => 0, 'id_penilai' => 'G1',
            'catatan_guru' => 'Belum tepat', 'jawaban_teks' => "Jawaban tetap\nBaris kedua",
        ]);
        $this->assertNotNull(NilaiEssay::where('id_soal', $essay1->id_soal)->sole()->dinilai_at);
        $this->post($this->urlNilai($hasil, $essay2), ['nilai' => 15])->assertRedirect();
        $this->assertSame(55.0, (float) $hasil->fresh()->nilai);
        $this->assertSame('sudah divalidasi', $hasil->fresh()->status_validasi);
        $this->assertSame('G1', $hasil->fresh()->id_validator);
    }

    public static function skorTidakValid(): array
    {
        return [[-1], [30.01], [null], ['bukan angka'], [1.234]];
    }

    #[DataProvider('skorTidakValid')]
    public function test_skor_tidak_valid_ditolak_tanpa_mengubah_penilaian(mixed $nilai): void
    {
        [, $essay1, , $hasil] = $this->campuran();
        $this->actingAs($this->guru)->postJson($this->urlNilai($hasil, $essay1), ['nilai' => $nilai])
            ->assertUnprocessable()->assertJsonValidationErrors('nilai');
        $this->assertNull(NilaiEssay::where('id_soal', $essay1->id_soal)->sole()->nilai);
        $this->assertNull($hasil->fresh()->nilai);
    }

    public function test_hanya_pembuat_kuis_yang_bisa_menilai(): void
    {
        [, $essay1, , $hasil] = $this->campuran();
        foreach ([$this->guruLain, $this->siswa] as $user) {
            $this->actingAs($user)->postJson($this->urlNilai($hasil, $essay1), ['nilai' => 20])->assertForbidden();
        }
        $this->assertNull(NilaiEssay::where('id_soal', $essay1->id_soal)->sole()->nilai);
    }

    public function test_soal_dari_kuis_lain_dan_soal_pg_tidak_bisa_dinilai_sebagai_essay(): void
    {
        [$kuis, , , $hasil] = $this->campuran();
        $asing = $this->soal($this->kuis(), 'essay');
        $pg = $kuis->soal()->where('tipe', 'pilihan_ganda')->sole();
        foreach ([$asing, $pg] as $soal) {
            $this->actingAs($this->guru)->postJson($this->urlNilai($hasil, $soal), ['nilai' => 10])->assertNotFound();
        }
    }

    public function test_validasi_lama_tidak_bisa_melewati_penilaian_essay(): void
    {
        [, , , $hasil] = $this->campuran();
        $this->actingAs($this->guru)->patch(route('guru.nilai.validasi', $hasil))
            ->assertRedirect()->assertSessionHasErrors('nilai');
        $this->assertSame('menunggu penilaian', $hasil->fresh()->status_validasi);
        $this->assertNull($hasil->fresh()->nilai);
    }

    public function test_submit_berulang_tidak_membuat_hasil_atau_snapshot_ganda(): void
    {
        [$kuis, , , $hasil] = $this->campuran();
        $sesi = $kuis->sesi()->sole();
        $this->assertNull(app(KuisSubmissionService::class)->submit($sesi));
        $sesi->update(['waktu_selesai' => null]);
        $ulang = app(KuisSubmissionService::class)->submit($sesi);
        $this->assertSame($hasil->id_mengerjakan, $ulang->id_mengerjakan);
        $this->assertDatabaseCount('mengerjakan', 1);
        $this->assertDatabaseCount('nilai_essay', 2);
    }

    public function test_kunci_soal_dan_bobot_berlaku_selama_pending(): void
    {
        [$kuis, $essay1] = $this->campuran();
        $kuis->update(['status_publikasi' => 'draft']);
        $sebelum = $kuis->soal()->orderBy('id_soal')->get()->toArray();
        $this->actingAs($this->guru)->postJson(route('kuis.soal.store', $kuis), [
            'tipe' => 'essay', 'pertanyaan' => 'Tambahan',
        ])->assertUnprocessable()->assertJsonValidationErrors('kuis');
        $this->putJson(route('kuis.soal.update', [$kuis, $essay1]), [
            'tipe' => 'essay', 'pertanyaan' => 'Diubah', 'bobot' => 10,
        ])->assertUnprocessable()->assertJsonValidationErrors('kuis');
        $this->deleteJson(route('kuis.soal.destroy', [$kuis, $essay1]))
            ->assertUnprocessable()->assertJsonValidationErrors('kuis');
        $this->patchJson(route('kuis.publish', $kuis))->assertUnprocessable()->assertJsonValidationErrors('kuis');
        $this->putJson(route('kuis.update', $kuis), [
            'judul' => 'Diubah', 'kategori' => 'kuis harian', 'id_mapel' => 'MP1', 'tingkat_kesulitan' => 'mudah',
        ])->assertUnprocessable()->assertJsonValidationErrors('kuis');
        $this->assertSame($sebelum, $kuis->soal()->orderBy('id_soal')->get()->toArray());
    }

    public function test_pengulangan_dikunci_lalu_dibuka_tanpa_menghapus_snapshot(): void
    {
        [$kuis, $essay1, $essay2, $hasil] = $this->campuran();
        $this->actingAs($this->siswa)->getJson(route('siswa.kuis.mulai', $kuis))
            ->assertUnprocessable()->assertJsonValidationErrors('kuis');
        $this->assertDatabaseCount('sesi_kuis', 1);
        $this->assertDatabaseCount('draft_jawaban', 2);
        $this->actingAs($this->guru)->post($this->urlNilai($hasil, $essay1), ['nilai' => 30])->assertRedirect();
        $this->post($this->urlNilai($hasil, $essay2), ['nilai' => 30])->assertRedirect();
        $this->assertSame(100.0, (float) $hasil->fresh()->nilai);
        $this->actingAs($this->siswa)->get(route('siswa.kuis.mulai', $kuis))
            ->assertRedirect(route('siswa.kuis.kerjakan', $kuis));
        $this->assertDatabaseCount('sesi_kuis', 2);
        $this->assertDatabaseCount('draft_jawaban', 0);
        $this->assertDatabaseHas('nilai_essay', [
            'id_soal' => $essay1->id_soal, 'jawaban_teks' => "Jawaban tetap\nBaris kedua", 'nilai' => 30,
        ]);
        $this->actingAs($this->guru)->postJson($this->urlNilai($hasil, $essay1), ['nilai' => 0])
            ->assertUnprocessable();
        $this->assertSame(100.0, (float) $hasil->fresh()->nilai);
    }

    public function test_essay_saja_bisa_dinilai_dari_snapshot_setelah_draft_dihapus(): void
    {
        $kuis = $this->kuis();
        $essay = $this->soal($kuis, 'essay', 100);
        $this->draft($essay, null, 'Salinan permanen');
        $hasil = app(KuisSubmissionService::class)->submit($this->sesi($kuis));
        DB::table('draft_jawaban')->delete();
        $this->actingAs($this->guru)->post($this->urlNilai($hasil, $essay), ['nilai' => 0])->assertRedirect();
        $this->assertSame(0.0, (float) $hasil->fresh()->nilai);
        $this->assertSame('sudah divalidasi', $hasil->fresh()->status_validasi);
        $this->assertDatabaseHas('nilai_essay', ['jawaban_teks' => 'Salinan permanen', 'nilai' => 0]);
    }

    public function test_pg_mempertahankan_rumus_bobot_pembulatan_dan_jawaban_kosong(): void
    {
        $kuis = $this->kuis(['kategori' => 'pre-test']);
        $benar = $this->soal($kuis, 'pilihan_ganda', 2);
        $this->soal($kuis, 'pilihan_ganda', 1);
        $this->draft($benar, 'a');
        $hasil = app(KuisSubmissionService::class)->submit($this->sesi($kuis));
        $this->assertSame(66.67, (float) $hasil->nilai);
        $this->assertSame('sudah divalidasi', $hasil->status_validasi);
        $this->assertSame('G1', $hasil->id_validator);
        $this->assertDatabaseCount('nilai_essay', 0);
    }

    public function test_soal_tanpa_tipe_memakai_default_pg(): void
    {
        $kuis = $this->kuis();
        $this->actingAs($this->guru)->post(route('kuis.soal.store', $kuis), [
            'pertanyaan' => 'Pilihan ganda lama', 'opsi_a' => 'A', 'opsi_b' => 'B',
            'opsi_c' => 'C', 'opsi_d' => 'D', 'jawaban' => 'A',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame('pilihan_ganda', $kuis->soal()->sole()->tipe);
    }

    public function test_timer_mengumpulkan_essay_kosong_sebagai_pending(): void
    {
        $kuis = $this->kuis();
        $this->soal($kuis, 'essay', 100);
        $sesi = $this->sesi($kuis, 31);
        $this->assertSame(1, app(KuisSubmissionService::class)->selesaikanKedaluwarsa());
        $this->assertNull($kuis->mengerjakan()->sole()->nilai);
        $this->assertSame('menunggu penilaian', $kuis->mengerjakan()->sole()->status_validasi);
        $this->assertTrue($sesi->fresh()->waktu_selesai->eq($sesi->waktu_mulai->copy()->addMinutes(30)));
        $this->assertDatabaseHas('nilai_essay', ['jawaban_teks' => null, 'nilai' => null]);
    }

    public function test_pelanggaran_ketiga_mengumpulkan_snapshot_essay(): void
    {
        $kuis = $this->kuis();
        $essay = $this->soal($kuis, 'essay', 100);
        $this->draft($essay, null, 'Sudah tersimpan');
        $sesi = $this->sesi($kuis);
        $this->actingAs($this->siswa);
        for ($i = 1; $i <= 3; $i++) {
            $this->postJson(route('siswa.kuis.pelanggaran', $kuis))
                ->assertOk()->assertJsonPath('autoSubmitted', $i === 3);
        }
        $this->assertNotNull($sesi->fresh()->waktu_selesai);
        $this->assertNull($kuis->mengerjakan()->sole()->nilai);
        $this->assertDatabaseHas('nilai_essay', ['jawaban_teks' => 'Sudah tersimpan', 'nilai' => null]);
    }
}
