<?php

namespace Tests\Feature;

use App\Models\Kuis;
use App\Models\Materi;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class KuisPrePostUniquenessTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertTrue($this->app->environment('testing'));
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());

        $this->withoutVite();
    }

    public static function prePostCategories(): array
    {
        return [
            'pre-test' => ['pre-test'],
            'post-test' => ['post-test'],
        ];
    }

    #[DataProvider('prePostCategories')]
    public function test_creation_requires_a_material_for_pretest_and_posttest(string $kategori): void
    {
        $this->seedScenario();

        $response = $this->actingAs($this->guru())->post(route('kuis.store'), $this->kuisPayload($kategori));

        $response->assertSessionHasErrors('materi');
        $this->assertDatabaseCount('tm_kuis', 0);
    }

    #[DataProvider('prePostCategories')]
    public function test_creation_rejects_multiple_materials_for_pretest_and_posttest(string $kategori): void
    {
        $this->seedScenario();
        Materi::create([
            'id_materi' => 'M002',
            'judul' => 'Bab 2',
            'bab' => '2',
            'kelas' => 'X',
            'id_mapel' => 'MP001',
            'id_user' => 'G001',
        ]);

        $response = $this->actingAs($this->guru())
            ->post(route('kuis.store'), $this->kuisPayload($kategori, ['M001', 'M002']));

        $response->assertSessionHasErrors('materi');
        $this->assertDatabaseCount('tm_kuis', 0);
    }

    #[DataProvider('prePostCategories')]
    public function test_creation_rejects_a_second_active_quiz_of_the_same_category_for_a_materi(string $kategori): void
    {
        $this->seedScenario();
        $this->createQuiz('K001', $kategori)->materi()->attach('M001');

        $response = $this->actingAs($this->guru())
            ->post(route('kuis.store'), $this->kuisPayload($kategori, ['M001']));

        $response->assertSessionHasErrors('materi');
        $this->assertDatabaseCount('tm_kuis', 1);
    }

    #[DataProvider('prePostCategories')]
    public function test_updating_to_pretest_or_posttest_rejects_an_existing_active_category(string $kategori): void
    {
        $this->seedScenario();
        $this->createQuiz('K001', $kategori)->materi()->attach('M001');
        $kuisYangDiedit = $this->createQuiz('K002', 'kuis harian');
        $kuisYangDiedit->materi()->attach('M001');

        $response = $this->actingAs($this->guru())
            ->from(route('kuis.edit', $kuisYangDiedit->id_kuis))
            ->put(route('kuis.update', $kuisYangDiedit->id_kuis), $this->kuisPayload($kategori, ['M001']));

        $response->assertRedirect(route('kuis.edit', $kuisYangDiedit->id_kuis))
            ->assertSessionHasErrors('materi');
        $this->assertDatabaseHas('tm_kuis', [
            'id_kuis' => 'K002',
            'kategori' => 'kuis harian',
        ]);
    }

    #[DataProvider('prePostCategories')]
    public function test_updating_to_pretest_or_posttest_rejects_multiple_materials(string $kategori): void
    {
        $this->seedScenario();
        Materi::create([
            'id_materi' => 'M002',
            'judul' => 'Bab 2',
            'bab' => '2',
            'kelas' => 'X',
            'id_mapel' => 'MP001',
            'id_user' => 'G001',
        ]);
        $kuisYangDiedit = $this->createQuiz('K002', 'kuis harian');
        $kuisYangDiedit->materi()->attach('M001');

        $response = $this->actingAs($this->guru())
            ->from(route('kuis.edit', $kuisYangDiedit->id_kuis))
            ->put(route('kuis.update', $kuisYangDiedit->id_kuis), $this->kuisPayload($kategori, ['M001', 'M002']));

        $response->assertRedirect(route('kuis.edit', $kuisYangDiedit->id_kuis))
            ->assertSessionHasErrors('materi');
    }

    public function test_inactive_pretest_can_be_replaced_for_the_same_materi(): void
    {
        $this->seedScenario();
        $kuisNonaktif = $this->createQuiz('K001', 'pre-test');
        $kuisNonaktif->update(['is_aktif' => false]);
        $kuisNonaktif->materi()->attach('M001');

        $response = $this->actingAs($this->guru())
            ->post(route('kuis.store'), $this->kuisPayload('pre-test', ['M001']));

        $response->assertRedirect(route('kuis.index', ['mapel' => 'MP001']))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tm_kuis', [
            'id_kuis' => 'K001',
            'is_aktif' => false,
        ]);
        $this->assertDatabaseHas('tm_kuis', [
            'id_kuis' => 'K002',
            'kategori' => 'pre-test',
            'is_aktif' => true,
        ]);
    }

    #[DataProvider('prePostCategories')]
    public function test_api_requires_a_material_for_pretest_and_posttest(string $kategori): void
    {
        $this->seedScenario();

        $this->actingAs($this->guru(), 'sanctum')
            ->postJson('/api/guru/kuis', $this->kuisPayload($kategori))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('materi');

        $this->assertDatabaseCount('tm_kuis', 0);
    }

    #[DataProvider('prePostCategories')]
    public function test_api_rejects_multiple_materials_for_pretest_and_posttest(string $kategori): void
    {
        $this->seedScenario();
        Materi::create([
            'id_materi' => 'M002',
            'judul' => 'Bab 2',
            'bab' => '2',
            'kelas' => 'X',
            'id_mapel' => 'MP001',
            'id_user' => 'G001',
        ]);

        $this->actingAs($this->guru(), 'sanctum')
            ->postJson('/api/guru/kuis', $this->kuisPayload($kategori, ['M001', 'M002']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('materi');

        $this->assertDatabaseCount('tm_kuis', 0);
    }

    #[DataProvider('prePostCategories')]
    public function test_api_rejects_a_second_active_prepost_quiz_for_the_same_materi(string $kategori): void
    {
        $this->seedScenario();
        $guru = $this->guru();

        $this->actingAs($guru, 'sanctum')
            ->postJson('/api/guru/kuis', $this->kuisPayload($kategori, ['M001']))
            ->assertCreated();

        $this->postJson('/api/guru/kuis', $this->kuisPayload($kategori, ['M001']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('materi');

        $this->assertDatabaseCount('tm_kuis', 1);
    }

    public function test_api_allows_replacing_an_inactive_pretest_for_the_same_materi(): void
    {
        $this->seedScenario();

        $this->actingAs($this->guru(), 'sanctum')
            ->postJson('/api/guru/kuis', $this->kuisPayload('pre-test', ['M001']))
            ->assertCreated();
        $this->deleteJson('/api/guru/kuis/K001')->assertOk();

        $this->postJson('/api/guru/kuis', $this->kuisPayload('pre-test', ['M001']))
            ->assertCreated();

        $this->assertDatabaseHas('tm_kuis', [
            'id_kuis' => 'K001',
            'is_aktif' => false,
        ]);
        $this->assertDatabaseHas('tm_kuis', [
            'id_kuis' => 'K002',
            'kategori' => 'pre-test',
            'is_aktif' => true,
        ]);
    }

    public function test_api_rejects_changing_a_quiz_to_pretest_when_the_materi_already_has_one(): void
    {
        $this->seedScenario();
        $this->createQuiz('K001', 'pre-test')->materi()->attach('M001');
        $kuisYangDiedit = $this->createQuiz('K002', 'kuis harian');
        $kuisYangDiedit->materi()->attach('M001');

        $this->actingAs($this->guru(), 'sanctum')
            ->putJson('/api/guru/kuis/K002', $this->kuisPayload('pre-test', ['M001']))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('materi');

        $this->assertDatabaseHas('tm_kuis', [
            'id_kuis' => 'K002',
            'kategori' => 'kuis harian',
        ]);
    }

    public function test_pretest_and_posttest_can_each_be_created_once_for_the_same_material(): void
    {
        $this->seedScenario();
        $this->createQuiz('K001', 'pre-test')->materi()->attach('M001');

        $response = $this->actingAs($this->guru())
            ->post(route('kuis.store'), $this->kuisPayload('post-test', ['M001']));

        $response->assertRedirect(route('kuis.index', ['mapel' => 'MP001']))
            ->assertSessionHasNoErrors();
        $this->assertDatabaseHas('tm_kuis', [
            'id_kuis' => 'K002',
            'kategori' => 'post-test',
        ]);
        $this->assertDatabaseHas('tp_materi_kuis', [
            'id_materi' => 'M001',
            'id_kuis' => 'K002',
        ]);
    }

    private function seedScenario(): void
    {
        $this->createSchema();

        DB::table('tm_tahun_ajaran')->insert([
            'id_tahun_ajaran' => 'TA001',
            'nama' => '2026/2027',
            'is_aktif' => true,
        ]);
        DB::table('tm_mapel')->insert([
            'id_mapel' => 'MP001',
            'kode_mapel' => 'TIK',
            'nama_mapel' => 'Teknologi Informasi',
            'is_aktif' => true,
        ]);
        DB::table('tr_pengajaran')->insert([
            'id_user' => 'G001',
            'id_kelas' => 'KLS001',
            'id_mapel' => 'MP001',
        ]);
        Materi::create([
            'id_materi' => 'M001',
            'judul' => 'Bab 1',
            'bab' => '1',
            'kelas' => 'X',
            'id_mapel' => 'MP001',
            'id_user' => 'G001',
        ]);
    }

    private function createSchema(): void
    {
        Schema::create('tm_tahun_ajaran', function (Blueprint $table): void {
            $table->string('id_tahun_ajaran', 50)->primary();
            $table->string('nama', 50);
            $table->boolean('is_aktif')->default(false);
        });

        Schema::create('tm_mapel', function (Blueprint $table): void {
            $table->string('id_mapel', 50)->primary();
            $table->string('kode_mapel', 20);
            $table->string('nama_mapel', 100);
            $table->boolean('is_aktif')->default(true);
        });

        Schema::create('tr_pengajaran', function (Blueprint $table): void {
            $table->increments('id_pengajaran');
            $table->string('id_user', 50);
            $table->string('id_kelas', 50);
            $table->string('id_mapel', 50);
        });

        Schema::create('tm_materi', function (Blueprint $table): void {
            $table->string('id_materi', 50)->primary();
            $table->string('judul', 150);
            $table->string('bab', 50)->nullable();
            $table->string('kelas', 50)->nullable();
            $table->string('id_mapel', 50);
            $table->text('deskripsi')->nullable();
            $table->string('isi_materi', 255)->nullable();
            $table->string('link_youtube', 255)->nullable();
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
            $table->string('tampil_jawaban', 20)->default('setelah_submit');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('tp_materi_kuis', function (Blueprint $table): void {
            $table->increments('id_materi_kuis');
            $table->string('id_materi', 50);
            $table->string('id_kuis', 50);
            $table->unique(['id_materi', 'id_kuis']);
        });

        Schema::create('tm_soal', function (Blueprint $table): void {
            $table->string('id_soal', 50)->primary();
            $table->string('id_kuis', 50);
            $table->string('tipe', 30);
        });

        Schema::create('tr_mengerjakan', function (Blueprint $table): void {
            $table->increments('id_mengerjakan');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->string('status_pengerjaan', 30);
            $table->string('status_validasi', 30);
        });

        Schema::create('tr_remedial', function (Blueprint $table): void {
            $table->increments('id_remedial');
            $table->string('id_kuis_remedial', 50);
            $table->string('id_kuis_asal', 50);
        });
    }

    private function guru(): User
    {
        $guru = new User;
        $guru->id_user = 'G001';
        $guru->role = 'guru';
        $guru->status = 'aktif';

        return $guru;
    }

    private function createQuiz(string $idKuis, string $kategori): Kuis
    {
        return Kuis::create([
            'id_kuis' => $idKuis,
            'judul' => 'Kuis '.$idKuis,
            'kategori' => $kategori,
            'tingkat_kesulitan' => 'mudah',
            'id_mapel' => 'MP001',
            'id_tahun_ajaran' => 'TA001',
            'id_user' => 'G001',
            'status_publikasi' => 'draft',
            'is_aktif' => true,
        ]);
    }

    private function kuisPayload(string $kategori, array $materi = []): array
    {
        return [
            'judul' => 'Kuis Baru',
            'kategori' => $kategori,
            'id_mapel' => 'MP001',
            'tingkat_kesulitan' => 'mudah',
            'materi' => $materi,
        ];
    }
}
