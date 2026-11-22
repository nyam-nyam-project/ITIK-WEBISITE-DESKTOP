<?php

namespace Tests\Feature;

use App\Models\Kuis;
use App\Models\Materi;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MateriPrePostButtonTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertTrue($this->app->environment('testing'));
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());

        $this->withoutVite();
    }

    public function test_materi_index_only_shows_buttons_for_missing_active_pretest_or_posttest(): void
    {
        $this->seedScenario();

        $response = $this->actingAs($this->guru())
            ->get(route('materi.index', ['mapel' => 'MP001']));

        $response->assertOk()
            ->assertSee($this->createQuizUrl('M001', 'post-test'), false)
            ->assertDontSee($this->createQuizUrl('M001', 'pre-test'), false)
            ->assertSee($this->createQuizUrl('M002', 'pre-test'), false)
            ->assertSee($this->createQuizUrl('M002', 'post-test'), false);
    }

    public function test_materi_detail_hides_buttons_for_existing_active_categories(): void
    {
        $this->seedScenario();
        $this->createQuiz('K003', 'M002', 'pre-test');
        $this->createQuiz('K004', 'M002', 'post-test');

        $incompleteMateriResponse = $this->actingAs($this->guru())
            ->get(route('materi.show', 'M001'));

        $incompleteMateriResponse->assertOk()
            ->assertDontSee($this->createQuizUrl('M001', 'pre-test'), false)
            ->assertSee($this->createQuizUrl('M001', 'post-test'), false);

        $response = $this->actingAs($this->guru())
            ->get(route('materi.show', 'M002'));

        $response->assertOk()
            ->assertDontSee($this->createQuizUrl('M002', 'pre-test'), false)
            ->assertDontSee($this->createQuizUrl('M002', 'post-test'), false)
            ->assertSee('Edit Materi');
    }

    private function seedScenario(): void
    {
        $this->createSchema();

        DB::table('tm_tahun_ajaran')->insert([
            'id_tahun_ajaran' => 'TA001',
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
            'id_kelas' => 'K001',
            'id_mapel' => 'MP001',
        ]);

        $this->createMateri('M001');
        $this->createMateri('M002');

        $this->createQuiz('K001', 'M001', 'pre-test');
        $this->createQuiz('K002', 'M001', 'post-test', false);
    }

    private function createSchema(): void
    {
        Schema::create('tm_tahun_ajaran', function (Blueprint $table): void {
            $table->string('id_tahun_ajaran', 50)->primary();
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
            $table->string('kategori', 50);
            $table->boolean('is_aktif')->default(true);
        });

        Schema::create('tp_materi_kuis', function (Blueprint $table): void {
            $table->increments('id_materi_kuis');
            $table->string('id_materi', 50);
            $table->string('id_kuis', 50);
            $table->unique(['id_materi', 'id_kuis']);
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

    private function createMateri(string $idMateri): Materi
    {
        return Materi::create([
            'id_materi' => $idMateri,
            'judul' => 'Materi '.$idMateri,
            'bab' => 'Bab '.$idMateri,
            'kelas' => 'X',
            'id_mapel' => 'MP001',
            'id_user' => 'G001',
        ]);
    }

    private function createQuiz(string $idKuis, string $idMateri, string $kategori, bool $isAktif = true): Kuis
    {
        $kuis = new Kuis;
        $kuis->id_kuis = $idKuis;
        $kuis->judul = 'Kuis '.$idKuis;
        $kuis->kategori = $kategori;
        $kuis->is_aktif = $isAktif;
        $kuis->save();
        $kuis->materi()->attach($idMateri);

        return $kuis;
    }

    private function createQuizUrl(string $idMateri, string $kategori): string
    {
        return e(route('kuis.create', [
            'mapel' => 'MP001',
            'materi' => $idMateri,
            'kategori' => $kategori,
        ]));
    }
}
