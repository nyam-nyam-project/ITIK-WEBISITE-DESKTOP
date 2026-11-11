<?php

namespace Tests\Feature;

use App\Models\Kuis;
use App\Models\Mengerjakan;
use App\Models\NilaiEssay;
use App\Models\SesiKuis;
use App\Models\Soal;
use App\Models\User;
use App\Services\KuisSubmissionService;
use DOMDocument;
use DOMElement;
use DOMXPath;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Illuminate\Testing\TestResponse;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class EssayUiTest extends TestCase
{
    private User $guru;
    private User $guruLain;
    private User $siswa;
    private int $nomorKuis = 0;
    private int $nomorSoal = 0;

    protected function setUp(): void
    {
        parent::setUp();

        // Fail before any schema writes if the isolated test connection is not active.
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
            'id_user' => 'G1', 'nama_lengkap' => 'Pembuat', 'role' => 'guru',
            'password' => 'secret', 'status' => 'aktif', 'wajib_ganti_password' => false,
        ]);
        $this->guruLain = User::create([
            'id_user' => 'G2', 'nama_lengkap' => 'Guru lain', 'role' => 'guru',
            'password' => 'secret', 'status' => 'aktif', 'wajib_ganti_password' => false,
        ]);
        $this->siswa = User::create([
            'id_user' => 'U1', 'nama_lengkap' => 'Siswa', 'role' => 'siswa',
            'password' => 'secret', 'status' => 'aktif', 'wajib_ganti_password' => false,
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

    private function sesi(Kuis $kuis): SesiKuis
    {
        $kuis->update(['status_publikasi' => 'terbit']);

        return SesiKuis::create([
            'id_kuis' => $kuis->id_kuis, 'id_user' => $this->siswa->id_user,
            'percobaan_ke' => 1, 'waktu_mulai' => now()->subMinute(),
        ]);
    }

    private function draft(Soal $soal, ?string $jawaban = null, ?string $teks = null): void
    {
        DB::table('draft_jawaban')->insert([
            'id_kuis' => $soal->id_kuis, 'id_user' => $this->siswa->id_user,
            'id_soal' => $soal->id_soal, 'jawaban_dipilih' => $jawaban, 'jawaban_teks' => $teks,
        ]);
    }

    private function campuran(array $attributes = []): array
    {
        $kuis = $this->kuis($attributes);
        $pg = $this->soal($kuis);
        $essay1 = $this->soal($kuis, 'essay', 30);
        $essay2 = $this->soal($kuis, 'essay', 30);
        $this->draft($pg, 'A');
        $this->draft($essay1, null, "Jawaban snapshot tetap\nBaris kedua <b>bukan HTML</b>");
        $hasil = app(KuisSubmissionService::class)->submit($this->sesi($kuis));

        return [$kuis, $essay1, $essay2, $hasil];
    }

    private function halaman(TestResponse $response): DOMXPath
    {
        $response->assertOk();
        $document = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        try {
            $document->loadHTML('<?xml encoding="UTF-8">'.$response->getContent());
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return new DOMXPath($document);
    }

    private function elemen(DOMXPath $html, string $query): DOMElement
    {
        $nodes = $html->query($query);
        $this->assertCount(1, $nodes, $query);
        $node = $nodes->item(0);
        $this->assertInstanceOf(DOMElement::class, $node);

        return $node;
    }

    private function formNilai(Mengerjakan $hasil, Soal $soal): string
    {
        return '//form[@action="'.route('guru.nilai.essay.store', [$hasil, $soal]).'"]';
    }

    public function test_create_soal_menawarkan_pg_dan_essay_dan_memulihkan_old_essay(): void
    {
        $kuis = $this->kuis();
        $url = route('kuis.soal.create', $kuis);
        $html = $this->halaman($this->actingAs($this->guru)->get($url));
        $this->elemen($html, '//select[@name="tipe"]/option[@value="pilihan_ganda"][@selected]');
        $this->assertFalse($this->elemen($html, '//select[@name="tipe"]/option[@value="essay"]')->hasAttribute('disabled'));
        $this->assertTrue($this->elemen($html, '//input[@name="opsi_a"]')->hasAttribute('required'));

        $html = $this->halaman($this->withSession(['_old_input' => [
            'tipe' => 'essay', 'pertanyaan' => 'Pertanyaan setelah validasi',
        ]])->get($url));
        $this->assertFormEssay($html);
        $this->assertSame('Pertanyaan setelah validasi', $this->elemen($html, '//textarea[@name="pertanyaan"]')->textContent);
    }

    public function test_edit_soal_memakai_tipe_essay_tersimpan(): void
    {
        $kuis = $this->kuis();
        $essay = $this->soal($kuis, 'essay');
        $html = $this->halaman($this->actingAs($this->guru)->get(route('kuis.soal.edit', [$kuis, $essay])));
        $this->assertFormEssay($html);
        $this->assertSame($essay->pertanyaan, $this->elemen($html, '//textarea[@name="pertanyaan"]')->textContent);
    }

    public function test_edit_pg_memulihkan_old_essay_bukan_tipe_tersimpan(): void
    {
        $kuis = $this->kuis();
        $pg = $this->soal($kuis);
        $html = $this->halaman($this->actingAs($this->guru)->withSession(['_old_input' => [
            'tipe' => 'essay', 'pertanyaan' => 'Ubah menjadi essay',
        ]])->get(route('kuis.soal.edit', [$kuis, $pg])));
        $this->assertFormEssay($html);
        $this->assertSame('Ubah menjadi essay', $this->elemen($html, '//textarea[@name="pertanyaan"]')->textContent);
    }

    private function assertFormEssay(DOMXPath $html): void
    {
        $this->elemen($html, '//select[@name="tipe"]/option[@value="essay"][@selected]');
        foreach (['opsi_a', 'opsi_b', 'opsi_c', 'opsi_d', 'jawaban'] as $name) {
            $input = $this->elemen($html, '//input[@name="'.$name.'"]');
            $this->assertTrue($input->hasAttribute('disabled'));
            $this->assertFalse($input->hasAttribute('required'));
        }
        $this->assertGreaterThan(0, $html->query('//*[@data-pg-only][@hidden]')->length);
    }

    public static function kategoriTerlarang(): array
    {
        return [['pre-test'], ['post-test']];
    }

    #[DataProvider('kategoriTerlarang')]
    public function test_create_dan_edit_pre_post_menonaktifkan_essay_meski_ada_old_input(string $kategori): void
    {
        $kuis = $this->kuis(['kategori' => $kategori]);
        $pg = $this->soal($kuis);
        foreach ([route('kuis.soal.create', $kuis), route('kuis.soal.edit', [$kuis, $pg])] as $url) {
            $html = $this->halaman($this->actingAs($this->guru)
                ->withSession(['_old_input' => ['tipe' => 'essay']])->get($url));
            $option = $this->elemen($html, '//select[@name="tipe"]/option[@value="essay"]');
            $this->assertTrue($option->hasAttribute('disabled'));
            $this->assertFalse($option->hasAttribute('selected'));
            $this->elemen($html, '//select[@name="tipe"]/option[@value="pilihan_ganda"][@selected]');
        }
    }

    public function test_kerjakan_memuat_textarea_opsional_terhubung_submit_dan_radio_pg_tetap_ada(): void
    {
        $kuis = $this->kuis();
        $pg = $this->soal($kuis);
        $essay = $this->soal($kuis, 'essay', 60);
        $this->draft($pg, 'B');
        $this->draft($essay, null, "Draft essay\nBaris kedua");
        $this->sesi($kuis);
        $html = $this->halaman($this->actingAs($this->siswa)->get(route('siswa.kuis.kerjakan', $kuis)));
        $textarea = $this->elemen($html, '//textarea[@name="jawaban_teks['.$essay->id_soal.']"]');
        $this->assertSame('form-kuis', $textarea->getAttribute('form'));
        $this->assertTrue($textarea->hasAttribute('data-essay-answer'));
        $this->assertFalse($textarea->hasAttribute('required'));
        $this->assertFalse($textarea->hasAttribute('disabled'));
        $this->assertSame("Draft essay\nBaris kedua", $textarea->textContent);
        $form = $this->elemen($html, '//form[@id="form-kuis"]');
        $this->assertSame(route('siswa.kuis.submit', $kuis), $form->getAttribute('action'));
        $this->assertSame('post', strtolower($form->getAttribute('method')));
        $radios = '//input[@type="radio"][@name="jawaban['.$pg->id_soal.']"]';
        $this->assertCount(4, $html->query($radios));
        $this->elemen($html, $radios.'[@value="B"][@checked]');
        $this->assertCount(0, $html->query('//input[@type="radio"][@name="jawaban['.$essay->id_soal.']"]'));
    }

    public function test_kerjakan_memulihkan_old_textarea_mengalahkan_draft(): void
    {
        $kuis = $this->kuis();
        $essay = $this->soal($kuis, 'essay', 100);
        $this->draft($essay, null, 'Draft lama');
        $this->sesi($kuis);
        $teks = "Jawaban setelah validasi\n<script>tidak dieksekusi</script>";
        $response = $this->actingAs($this->siswa)->withSession(['_old_input' => [
            'jawaban_teks' => [$essay->id_soal => $teks],
        ]])->get(route('siswa.kuis.kerjakan', $kuis));
        $html = $this->halaman($response);
        $this->assertSame($teks, $this->elemen($html, '//textarea[@data-essay-answer]')->textContent);
        $response->assertDontSee('<script>tidak dieksekusi</script>', false);
    }

    public static function kategoriPending(): array
    {
        return [['kuis harian'], ['ulangan']];
    }

    #[DataProvider('kategoriPending')]
    public function test_hasil_pending_netral_tanpa_tidak_lulus_pengulangan_atau_remedial(string $kategori): void
    {
        [$kuis, $essay1, $essay2] = $this->campuran(['kategori' => $kategori]);
        $response = $this->actingAs($this->siswa)->get(route('siswa.kuis.hasil', $kuis));
        $response->assertOk()->assertSeeText('Menunggu penilaian')
            ->assertDontSeeText('Tidak Lulus')->assertDontSeeText('Kerjakan Ulang')
            ->assertDontSeeText('Lihat Tugas Remedial')
            ->assertViewHas('totalBenar', 1)->assertViewHas('totalSalah', 0);
        $html = $this->halaman($response);
        $this->assertCount(0, $html->query('//a[@href="'.route('siswa.kuis.mulai', $kuis).'"]'));
        $this->assertCount(0, $html->query('//a[@href="'.route('siswa.remedial.index').'"]'));
        foreach ([$essay1, $essay2] as $essay) {
            $card = $this->elemen($html, '//*[@data-essay-review="'.$essay->id_soal.'"]');
            $this->assertDoesNotMatchRegularExpression('/\b(benar|salah)\b/i', $card->getAttribute('class'));
            $this->assertDoesNotMatchRegularExpression('/\b(benar|salah)\b/i', $card->textContent);
            $this->assertStringContainsString($essay->pertanyaan, $card->textContent);
        }
        $this->assertCount(1, $html->query('//div[contains(concat(" ", normalize-space(@class), " "), " soal-card ")][contains(concat(" ", normalize-space(@class), " "), " benar ")]'));
    }

    public static function detailTersembunyi(): array
    {
        return [['tidak', null], ['tidak', 100], ['setelah_kkm', null], ['setelah_kkm', 40]];
    }

    #[DataProvider('detailTersembunyi')]
    public function test_pengaturan_tampil_jawaban_menyembunyikan_essay(string $pengaturan, ?int $nilai): void
    {
        [$kuis, $essay, , $hasil] = $this->campuran(['tampil_jawaban' => $pengaturan]);
        if ($nilai !== null) {
            $hasil->update(['nilai' => $nilai, 'status_validasi' => 'sudah divalidasi']);
        }
        $this->actingAs($this->siswa)->get(route('siswa.kuis.hasil', $kuis))
            ->assertOk()->assertViewHas('tampilDetail', false)
            ->assertDontSee('data-essay-review', false)->assertDontSeeText($essay->pertanyaan)
            ->assertDontSeeText('Jawaban snapshot tetap')
            ->assertSeeText('Evaluasi per soal tidak tersedia');
    }

    public function test_setelah_kkm_menampilkan_kartu_essay_saat_nilai_sudah_lulus(): void
    {
        [$kuis, $essay, , $hasil] = $this->campuran(['tampil_jawaban' => 'setelah_kkm']);
        $hasil->update(['nilai' => 100, 'status_validasi' => 'sudah divalidasi']);
        $html = $this->halaman($this->actingAs($this->siswa)->get(route('siswa.kuis.hasil', $kuis))
            ->assertViewHas('tampilDetail', true));
        $this->elemen($html, '//*[@data-essay-review="'.$essay->id_soal.'"]');
    }

    public function test_guru_melihat_snapshot_bukan_draft_baru_dan_form_penilaian_sesuai_route(): void
    {
        [, $essay, $kosong, $hasil] = $this->campuran();
        DB::table('draft_jawaban')->where('id_soal', $essay->id_soal)->update(['jawaban_teks' => 'Draft berubah sesudah submit']);
        $response = $this->actingAs($this->guru)->get(route('guru.nilai.show', $hasil));
        $response->assertOk()->assertSeeText('Jawaban snapshot tetap')
            ->assertSeeText('Tidak dijawab.')->assertDontSeeText('Draft berubah sesudah submit')
            ->assertDontSee('<b>bukan HTML</b>', false)->assertSeeText('Menunggu penilaian')
            ->assertDontSeeText('Tidak Lulus');
        $html = $this->halaman($response);
        foreach ([$essay, $kosong] as $item) {
            $path = $this->formNilai($hasil, $item);
            $this->assertSame('post', strtolower($this->elemen($html, $path)->getAttribute('method')));
            $input = $this->elemen($html, $path.'//input[@name="nilai"]');
            $this->assertSame('number', $input->getAttribute('type'));
            $this->assertSame('0', $input->getAttribute('min'));
            $this->assertSame((float) $item->bobot, (float) $input->getAttribute('max'));
            $this->assertSame('0.01', $input->getAttribute('step'));
            $this->assertSame('', $input->getAttribute('value'));
            $this->assertTrue($input->hasAttribute('required'));
            $this->assertFalse($this->elemen($html, $path.'//textarea[@name="catatan_guru"]')->hasAttribute('required'));
            $this->assertSame($item->id_soal, $this->elemen($html, $path.'//input[@name="essay_soal_id"]')->getAttribute('value'));
        }
        DB::table('draft_jawaban')->delete();
        $this->get(route('guru.nilai.show', $hasil))->assertOk()->assertSeeText('Jawaban snapshot tetap');
    }

    public function test_guru_membedakan_nilai_nol_dan_belum_dinilai(): void
    {
        [, $essay, $kosong, $hasil] = $this->campuran();
        NilaiEssay::where('id_soal', $essay->id_soal)->update(['nilai' => 0, 'catatan_guru' => 'Perlu diperbaiki']);
        $response = $this->actingAs($this->guru)->get(route('guru.nilai.show', $hasil));
        $response->assertOk()->assertSeeText('Sudah dinilai')->assertSeeText('Belum dinilai')
            ->assertSeeText('0.00 / 30')->assertSeeText('1 dari 2 soal essay belum dinilai');
        $html = $this->halaman($response);
        $this->assertMatchesRegularExpression('/^0(?:\.0+)?$/', $this->elemen($html, $this->formNilai($hasil, $essay).'//input[@name="nilai"]')->getAttribute('value'));
        $this->assertSame('', $this->elemen($html, $this->formNilai($hasil, $kosong).'//input[@name="nilai"]')->getAttribute('value'));
        $this->assertSame('Perlu diperbaiki', $this->elemen($html, $this->formNilai($hasil, $essay).'//textarea[@name="catatan_guru"]')->textContent);
    }

    public function test_old_input_dan_error_penilaian_hanya_di_form_essay_yang_dikirim(): void
    {
        [, $essay, $lain, $hasil] = $this->campuran();
        NilaiEssay::where('id_soal', $lain->id_soal)->update(['nilai' => 0, 'catatan_guru' => 'Catatan tersimpan']);
        $errors = (new ViewErrorBag)->put('default', new MessageBag([
            'nilai' => ['Nilai melebihi bobot.'], 'catatan_guru' => ['Catatan tidak valid.'],
        ]));
        $this->actingAs($this->guru)->withSession([
            '_old_input' => ['essay_soal_id' => $essay->id_soal, 'nilai' => '31', 'catatan_guru' => 'Catatan yang dikirim'],
            'errors' => $errors,
        ]);
        // Round-trip the error bag through Laravel's configured session serialization.
        $this->app['session']->save();
        $html = $this->halaman($this->get(route('guru.nilai.show', $hasil)));
        $target = $this->formNilai($hasil, $essay);
        $other = $this->formNilai($hasil, $lain);
        $this->assertSame('31', $this->elemen($html, $target.'//input[@name="nilai"]')->getAttribute('value'));
        $this->assertSame('Catatan yang dikirim', $this->elemen($html, $target.'//textarea[@name="catatan_guru"]')->textContent);
        $this->assertCount(2, $html->query($target.'//*[@aria-invalid="true"]'));
        $this->assertStringContainsString('Nilai melebihi bobot.', $this->elemen($html, $target)->textContent);
        $this->assertStringContainsString('Catatan tidak valid.', $this->elemen($html, $target)->textContent);
        $this->assertMatchesRegularExpression('/^0(?:\.0+)?$/', $this->elemen($html, $other.'//input[@name="nilai"]')->getAttribute('value'));
        $this->assertSame('Catatan tersimpan', $this->elemen($html, $other.'//textarea[@name="catatan_guru"]')->textContent);
        $this->assertCount(0, $html->query($other.'//*[@aria-invalid="true"]'));
        $this->assertStringNotContainsString('Nilai melebihi bobot.', $this->elemen($html, $other)->textContent);
        $this->assertStringNotContainsString('Catatan tidak valid.', $this->elemen($html, $other)->textContent);
    }

    public function test_guru_lain_hanya_melihat_snapshot_tanpa_form_penilaian(): void
    {
        [, $essay, $lain, $hasil] = $this->campuran();
        $response = $this->actingAs($this->guruLain)->get(route('guru.nilai.show', $hasil));
        $response->assertOk()->assertSeeText('Jawaban snapshot tetap')->assertSeeText('Menunggu penilaian');
        $html = $this->halaman($response);
        foreach ([$essay, $lain] as $item) {
            $this->assertCount(0, $html->query($this->formNilai($hasil, $item)));
        }
        $this->assertCount(0, $html->query('//input[@name="nilai"]'));
    }

    public function test_percobaan_bukan_final_tidak_memiliki_form_penilaian(): void
    {
        [, , , $hasil] = $this->campuran();
        $hasil->update(['status_pengerjaan' => 'draft']);
        $html = $this->halaman($this->actingAs($this->guru)->get(route('guru.nilai.show', $hasil)));
        $this->assertCount(0, $html->query('//input[@name="nilai"]'));
        $this->assertCount(0, $html->query('//textarea[@name="catatan_guru"]'));
    }

    public function test_penilaian_selesai_read_only_dengan_nilai_nol_dan_catatan_tersimpan(): void
    {
        [, $essay, , $hasil] = $this->campuran();
        NilaiEssay::where('id_mengerjakan', $hasil->id_mengerjakan)->update([
            'nilai' => 0, 'catatan_guru' => 'Catatan final tersimpan', 'id_penilai' => $this->guru->id_user,
            'dinilai_at' => now(),
        ]);
        $hasil->update(['nilai' => 40, 'status_validasi' => 'sudah divalidasi', 'id_validator' => $this->guru->id_user]);
        $response = $this->actingAs($this->guru)->withSession(['_old_input' => [
            'essay_soal_id' => $essay->id_soal, 'nilai' => '25', 'catatan_guru' => 'Old input usang',
        ]])->get(route('guru.nilai.show', $hasil));
        $response->assertOk()->assertSeeText('0.00 / 30')->assertSeeText('Catatan final tersimpan')
            ->assertSeeText('Tidak Lulus')->assertDontSeeText('Menunggu penilaian')
            ->assertDontSeeText('Belum dinilai')->assertDontSeeText('Old input usang')
            ->assertDontSeeText('Simpan Penilaian Essay');
        $html = $this->halaman($response);
        $this->assertCount(0, $html->query('//input[@name="nilai"]'));
        $this->assertCount(0, $html->query('//textarea[@name="catatan_guru"]'));
    }
}
