<?php

namespace Tests\Feature\Api;

use App\Models\Materi;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class GuruMateriControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertTrue($this->app->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame('sqlite', DB::connection()->getDriverName());
        $this->assertSame(':memory:', DB::connection()->getDatabaseName());

        Schema::create('tm_mapel', function (Blueprint $table): void {
            $table->string('id_mapel', 50)->primary();
            $table->string('kode_mapel', 20);
            $table->string('nama_mapel', 100);
            $table->boolean('is_aktif')->default(true);
        });

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
            $table->string('id_mapel', 50)->nullable();
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

        DB::table('tm_tahun_ajaran')->insert([
            'id_tahun_ajaran' => 'TA001',
            'is_aktif' => true,
        ]);
        DB::table('tm_kelas')->insert([
            'id_kelas' => 'KLS001',
            'nama_kelas' => 'X TKJ 1',
            'tingkat' => 'X',
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
            'id_kelas' => 'KLS001',
            'id_mapel' => 'MP001',
        ]);
    }

    public function test_api_rejects_duplicate_bab_on_create(): void
    {
        $this->createMateri('M001', 'Bab 1', 'KLS001');

        $response = $this->actingAs($this->guru(), 'sanctum')
            ->postJson('/api/guru/materi', [
                'id_mapel' => 'MP001',
                'judul' => 'Materi berbeda',
                'bab' => 'Bab 1',
                'id_kelas' => ['KLS001'],
            ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.bab.0', 'Bab tersebut sudah digunakan untuk mata pelajaran dan salah satu kelas yang dipilih.');
        $this->assertDatabaseCount('tm_materi', 1);
    }

    public function test_api_rejects_duplicate_bab_on_update_without_changing_the_record(): void
    {
        $this->createMateri('M001', 'Bab 1', 'KLS001');
        $materi = $this->createMateri('M002', 'Bab 2', 'KLS001');

        $response = $this->actingAs($this->guru(), 'sanctum')
            ->putJson('/api/guru/materi/'.$materi->id_materi, [
                'id_mapel' => 'MP001',
                'judul' => $materi->judul,
                'bab' => 'Bab 1',
                'id_kelas' => ['KLS001'],
            ]);

        $response->assertUnprocessable()
            ->assertJsonPath('errors.bab.0', 'Bab tersebut sudah digunakan untuk mata pelajaran dan salah satu kelas yang dipilih.');
        $this->assertDatabaseHas('tm_materi', [
            'id_materi' => 'M002',
            'judul' => 'Materi M002',
            'bab' => 'Bab 2',
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

    private function createMateri(string $id, string $bab, string $idKelas): Materi
    {
        $materi = Materi::create([
            'id_materi' => $id,
            'judul' => 'Materi '.$id,
            'bab' => $bab,
            'id_mapel' => 'MP001',
            'id_user' => 'G001',
        ]);
        $materi->kelasTerkait()->sync([$idKelas]);

        return $materi;
    }
}
