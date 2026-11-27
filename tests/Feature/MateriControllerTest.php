<?php

namespace Tests\Feature;

use App\Models\Materi;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MateriControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertTrue($this->app->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());

        $this->withoutVite();

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

    public function test_create_form_shows_only_classes_assigned_to_the_teacher_as_checkboxes(): void
    {
        $this->seedFormData();

        $response = $this->actingAs($this->guru())
            ->get(route('materi.create', ['mapel' => 'MP001']));

        $response->assertOk()
            ->assertSee('<fieldset', false)
            ->assertSee('<legend>Kelas', false)
            ->assertSee('name="id_kelas[]"', false)
            ->assertSee('value="KLS001"', false)
            ->assertSee('value="KLS002"', false)
            ->assertDontSee('value="XI TKJ 1"', false)
            ->assertDontSee('value="KLS004"', false)
            ->assertDontSee('value="KLS005"', false);
    }

    public function test_edit_form_preselects_the_material_classes(): void
    {
        $this->seedFormData();
        $materi = $this->createMateri('M001', 'Bab 1', ['KLS001', 'KLS002']);

        $response = $this->actingAs($this->guru())
            ->get(route('materi.edit', $materi->id_materi));

        $response->assertOk()
            ->assertSee('value="KLS001"', false)
            ->assertSee('value="KLS002"', false)
            ->assertSee('checked', false);
    }

    public function test_store_rejects_a_bab_already_used_for_the_same_subject_and_class(): void
    {
        $this->seedFormData();
        $this->createMateri('M001', 'Bab 1', ['KLS001']);

        $response = $this->actingAs($this->guru())
            ->from(route('materi.create'))
            ->post(route('materi.store'), [
                'id_mapel' => 'MP001',
                'judul' => 'Materi berbeda',
                'bab' => 'Bab 1',
                'id_kelas' => ['KLS001'],
            ]);

        $response->assertRedirect(route('materi.create'))
            ->assertSessionHasErrors('bab');
        $this->assertDatabaseCount('tm_materi', 1);
    }

    public function test_store_accepts_the_same_bab_for_a_different_class(): void
    {
        $this->seedFormData();
        $this->createMateri('M001', 'Bab 1', ['KLS001']);

        $response = $this->actingAs($this->guru())
            ->from(route('materi.create'))
            ->post(route('materi.store'), [
                'id_mapel' => 'MP001',
                'judul' => 'Materi kelas kedua',
                'bab' => 'Bab 1',
                'id_kelas' => ['KLS002'],
            ]);

        $response->assertRedirect(route('materi.index', ['mapel' => 'MP001']));
        $this->assertDatabaseHas('tm_materi', [
            'id_materi' => 'M002',
            'id_mapel' => 'MP001',
            'bab' => 'Bab 1',
        ]);
        $this->assertDatabaseHas('tp_materi_kelas', ['id_materi' => 'M002', 'id_kelas' => 'KLS002']);
    }

    public function test_store_rejects_a_class_that_is_not_available(): void
    {
        $this->seedFormData();

        $response = $this->actingAs($this->guru())
            ->from(route('materi.create'))
            ->post(route('materi.store'), [
                'id_mapel' => 'MP001',
                'judul' => 'Materi kelas tidak valid',
                'bab' => 'Bab 1',
                'id_kelas' => ['KLS005'],
            ]);

        $response->assertRedirect(route('materi.create'))
            ->assertSessionHasErrors('id_kelas.0');
        $this->assertDatabaseCount('tm_materi', 0);
    }

    public function test_update_rejects_a_bab_used_by_another_materi_without_changing_the_record(): void
    {
        $this->seedFormData();
        $this->createMateri('M001', 'Bab 1', ['KLS001']);
        $materi = $this->createMateri('M002', 'Bab 2', ['KLS001']);

        $response = $this->actingAs($this->guru())
            ->from(route('materi.edit', $materi->id_materi))
            ->put(route('materi.update', $materi->id_materi), [
                'id_mapel' => 'MP001',
                'judul' => $materi->judul,
                'bab' => 'Bab 1',
                'id_kelas' => ['KLS001'],
            ]);

        $response->assertRedirect(route('materi.edit', $materi->id_materi))
            ->assertSessionHasErrors('bab');
        $this->assertDatabaseHas('tm_materi', [
            'id_materi' => 'M002',
            'judul' => 'Materi M002',
            'bab' => 'Bab 2',
        ]);
    }

    private function seedFormData(): void
    {
        DB::table('tm_tahun_ajaran')->insert([
            ['id_tahun_ajaran' => 'TA001', 'is_aktif' => true],
            ['id_tahun_ajaran' => 'TA002', 'is_aktif' => false],
        ]);

        DB::table('tm_kelas')->insert([
            ['id_kelas' => 'KLS001', 'nama_kelas' => 'X TKJ 1', 'tingkat' => 'X', 'id_tahun_ajaran' => 'TA001', 'is_aktif' => true],
            ['id_kelas' => 'KLS002', 'nama_kelas' => 'X TKJ 2', 'tingkat' => 'X', 'id_tahun_ajaran' => 'TA001', 'is_aktif' => true],
            ['id_kelas' => 'KLS003', 'nama_kelas' => 'XI TKJ 1', 'tingkat' => 'XI', 'id_tahun_ajaran' => 'TA002', 'is_aktif' => true],
            ['id_kelas' => 'KLS004', 'nama_kelas' => 'X TKJ 3', 'tingkat' => 'X', 'id_tahun_ajaran' => 'TA001', 'is_aktif' => false],
            ['id_kelas' => 'KLS005', 'nama_kelas' => 'X TKJ 4', 'tingkat' => 'X', 'id_tahun_ajaran' => 'TA001', 'is_aktif' => true],
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
        DB::table('tr_pengajaran')->insert([
            'id_user' => 'G001',
            'id_kelas' => 'KLS002',
            'id_mapel' => 'MP001',
        ]);
    }

    private function guru(): User
    {
        $guru = new User;
        $guru->id_user = 'G001';
        $guru->role = 'guru';
        $guru->status = 'aktif';

        return $guru;
    }

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
}
