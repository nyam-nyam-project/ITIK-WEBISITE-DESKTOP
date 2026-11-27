<?php

namespace Tests\Feature;

use App\Models\Materi;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MateriMultiKelasTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertTrue($this->app->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());
        $this->assertSame(1, (int) DB::selectOne('PRAGMA foreign_keys')->foreign_keys);

        $this->withoutVite();
        $this->createSchema();
        $this->seedScenario();
    }

    public function test_teacher_only_sees_assigned_classes_and_old_choices_are_restored(): void
    {
        $this->actingAs($this->guru());

        $response = $this->get(route('materi.create', ['mapel' => 'MP001']))
            ->assertOk()
            ->assertSee('<fieldset', false)
            ->assertSee('<legend>Kelas', false)
            ->assertSee('Pilih semua kelas X')
            ->assertSee('Pilih semua kelas XI')
            ->assertSee('Pilih semua kelas XII')
            ->assertSee('value="KLS001"', false)
            ->assertSee('value="KLS002"', false)
            ->assertSee('value="KLS003"', false)
            ->assertDontSee('value="KLS004"', false);

        $this->assertSame(3, substr_count($response->getContent(), 'data-pilih-semua='));

        $response = $this->withSession(['_old_input' => [
            'id_mapel' => 'MP001',
            'id_kelas' => ['KLS001', 'KLS003'],
        ]])->get(route('materi.create'))->assertOk();

        $this->assertMatchesRegularExpression(
            '/<input(?=[^>]*value="KLS001")(?=[^>]*checked)[^>]*>/',
            $response->getContent(),
        );
        $this->assertMatchesRegularExpression(
            '/<input(?=[^>]*value="KLS003")(?=[^>]*checked)[^>]*>/',
            $response->getContent(),
        );
    }

    public function test_server_rejects_a_class_the_teacher_does_not_teach(): void
    {
        $response = $this->actingAs($this->guru())
            ->from(route('materi.create', ['mapel' => 'MP001']))
            ->post(route('materi.store'), [
                'id_mapel' => 'MP001',
                'judul' => 'Materi kelas asing',
                'bab' => 'Bab 1',
                'id_kelas' => ['KLS004'],
            ]);

        $response->assertRedirect(route('materi.create', ['mapel' => 'MP001']))
            ->assertSessionHasErrors('id_kelas.0')
            ->assertSessionHasInput('id_kelas', ['KLS004']);
        $this->assertDatabaseCount('tm_materi', 0);
    }

    public function test_at_least_one_class_is_required(): void
    {
        $response = $this->actingAs($this->guru())
            ->from(route('materi.create', ['mapel' => 'MP001']))
            ->post(route('materi.store'), [
                'id_mapel' => 'MP001',
                'judul' => 'Materi tanpa kelas',
                'bab' => 'Bab 1',
                'id_kelas' => [],
            ]);

        $response->assertRedirect(route('materi.create', ['mapel' => 'MP001']))
            ->assertSessionHasErrors('id_kelas');
        $this->assertDatabaseCount('tm_materi', 0);
    }

    public function test_duplicate_bab_is_rejected_for_any_selected_class_but_allowed_for_another_class(): void
    {
        $this->createMateri('M001', 'Bab 1', ['KLS001']);

        $this->actingAs($this->guru())
            ->from(route('materi.create', ['mapel' => 'MP001']))
            ->post(route('materi.store'), [
                'id_mapel' => 'MP001',
                'judul' => 'Materi bab ganda',
                'bab' => 'Bab 1',
                'id_kelas' => ['KLS002', 'KLS001'],
            ])
            ->assertRedirect(route('materi.create', ['mapel' => 'MP001']))
            ->assertSessionHasErrors('bab')
            ->assertSessionHasInput('id_kelas', ['KLS002', 'KLS001']);

        $this->actingAs($this->guru())
            ->post(route('materi.store'), [
                'id_mapel' => 'MP001',
                'judul' => 'Materi bab untuk kelas lain',
                'bab' => 'Bab 1',
                'id_kelas' => ['KLS002'],
            ])
            ->assertRedirect(route('materi.index', ['mapel' => 'MP001']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('tp_materi_kelas', ['id_materi' => 'M002', 'id_kelas' => 'KLS002']);
    }

    public function test_edit_synchronizes_selected_classes(): void
    {
        $materi = $this->createMateri('M001', 'Bab 1', ['KLS001', 'KLS002']);

        $response = $this->actingAs($this->guru())
            ->get(route('materi.edit', $materi->id_materi))
            ->assertOk();

        $response->assertSee('value="KLS001"', false)
            ->assertSee('value="KLS002"', false);
        $this->assertMatchesRegularExpression(
            '/<input(?=[^>]*value="KLS001")(?=[^>]*checked)[^>]*>/',
            $response->getContent(),
        );
        $this->assertMatchesRegularExpression(
            '/<input(?=[^>]*value="KLS002")(?=[^>]*checked)[^>]*>/',
            $response->getContent(),
        );

        $this->put(route('materi.update', $materi->id_materi), [
            'id_mapel' => 'MP001',
            'judul' => 'Materi diperbarui',
            'bab' => 'Bab 1',
            'id_kelas' => ['KLS002', 'KLS003'],
        ])->assertRedirect(route('materi.index', ['mapel' => 'MP001']))->assertSessionHasNoErrors();

        $this->assertSame('Materi diperbarui', $materi->fresh()->judul);
        $this->assertEqualsCanonicalizing(
            ['KLS002', 'KLS003'],
            $materi->fresh()->kelasTerkait()->pluck('tm_kelas.id_kelas')->all(),
        );
    }

    public function test_material_and_class_sync_roll_back_together_when_pivot_sync_fails(): void
    {
        $materi = $this->createMateri('M001', 'Bab 1', ['KLS001']);
        DB::unprepared(
            "CREATE TRIGGER reject_test_class BEFORE INSERT ON tp_materi_kelas
            WHEN NEW.id_kelas = 'KLS003'
            BEGIN SELECT RAISE(ABORT, 'test pivot failure'); END"
        );

        $this->actingAs($this->guru());
        $this->withoutExceptionHandling();

        try {
            $this->put(route('materi.update', $materi->id_materi), [
                'id_mapel' => 'MP001',
                'judul' => 'Perubahan harus batal',
                'bab' => 'Bab 1',
                'id_kelas' => ['KLS002', 'KLS003'],
            ]);
            $this->fail('The forced pivot failure did not propagate.');
        } catch (QueryException $exception) {
            $this->assertStringContainsString('test pivot failure', $exception->getMessage());
            $this->assertSame('Materi M001', $materi->fresh()->judul);
            $this->assertEqualsCanonicalizing(
                ['KLS001'],
                $materi->fresh()->kelasTerkait()->pluck('tm_kelas.id_kelas')->all(),
            );
        }
    }

    public function test_create_and_edit_views_do_not_contain_inline_scripts_or_styles(): void
    {
        foreach (['create', 'edit'] as $view) {
            $source = file_get_contents(resource_path("views/guru/materi/{$view}.blade.php"));

            $this->assertIsString($source);
            $this->assertDoesNotMatchRegularExpression('/<script\b(?![^>]*\bsrc=)[^>]*>/i', $source);
            $this->assertDoesNotMatchRegularExpression('/<style\b/i', $source);
            $this->assertDoesNotMatchRegularExpression('/\sstyle\s*=/i', $source);
            $this->assertDoesNotMatchRegularExpression('/\son[a-z]+\s*=/i', $source);
        }
    }

    private function createSchema(): void
    {
        Schema::create('tm_tahun_ajaran', function (Blueprint $table): void {
            $table->string('id_tahun_ajaran', 50)->primary();
            $table->boolean('is_aktif')->default(false);
        });

        Schema::create('tm_kelas', function (Blueprint $table): void {
            $table->string('id_kelas', 50)->primary();
            $table->string('nama_kelas', 50);
            $table->string('tingkat', 10);
            $table->string('id_tahun_ajaran', 50);
            $table->boolean('is_aktif')->default(true);
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

        Schema::create('tp_materi_kelas', function (Blueprint $table): void {
            $table->string('id_materi', 50);
            $table->string('id_kelas', 50);
            $table->primary(['id_materi', 'id_kelas']);
            $table->foreign('id_materi')->references('id_materi')->on('tm_materi')->cascadeOnDelete();
            $table->foreign('id_kelas')->references('id_kelas')->on('tm_kelas')->restrictOnDelete();
        });
    }

    private function seedScenario(): void
    {
        DB::table('tm_tahun_ajaran')->insert([
            ['id_tahun_ajaran' => 'TA001', 'is_aktif' => true],
            ['id_tahun_ajaran' => 'TA002', 'is_aktif' => false],
        ]);
        DB::table('tm_kelas')->insert([
            ['id_kelas' => 'KLS001', 'nama_kelas' => 'X TKJ 1', 'tingkat' => 'X', 'id_tahun_ajaran' => 'TA001', 'is_aktif' => true],
            ['id_kelas' => 'KLS002', 'nama_kelas' => 'X TKJ 2', 'tingkat' => 'X', 'id_tahun_ajaran' => 'TA001', 'is_aktif' => true],
            ['id_kelas' => 'KLS003', 'nama_kelas' => 'XI TKJ 1', 'tingkat' => 'XI', 'id_tahun_ajaran' => 'TA001', 'is_aktif' => true],
            ['id_kelas' => 'KLS004', 'nama_kelas' => 'XII TKJ 1', 'tingkat' => 'XII', 'id_tahun_ajaran' => 'TA001', 'is_aktif' => true],
            ['id_kelas' => 'KLS005', 'nama_kelas' => 'X TKJ lama', 'tingkat' => 'X', 'id_tahun_ajaran' => 'TA002', 'is_aktif' => true],
        ]);
        DB::table('tm_mapel')->insert([
            ['id_mapel' => 'MP001', 'kode_mapel' => 'TIK', 'nama_mapel' => 'Teknologi Informasi', 'is_aktif' => true],
        ]);
        DB::table('tr_pengajaran')->insert([
            ['id_user' => 'G001', 'id_kelas' => 'KLS001', 'id_mapel' => 'MP001'],
            ['id_user' => 'G001', 'id_kelas' => 'KLS002', 'id_mapel' => 'MP001'],
            ['id_user' => 'G001', 'id_kelas' => 'KLS003', 'id_mapel' => 'MP001'],
        ]);
    }

    /**
     * @param  array<int, string>  $kelasIds
     */
    private function createMateri(string $id, string $bab, array $kelasIds): Materi
    {
        $materi = Materi::create([
            'id_materi' => $id,
            'judul' => 'Materi '.$id,
            'bab' => $bab,
            'id_mapel' => 'MP001',
            'id_user' => 'G001',
        ]);
        $materi->kelasTerkait()->sync($kelasIds);

        return $materi;
    }

    private function guru(): User
    {
        $guru = new User;
        $guru->id_user = 'G001';
        $guru->role = 'guru';
        $guru->status = 'aktif';

        return $guru;
    }
}
