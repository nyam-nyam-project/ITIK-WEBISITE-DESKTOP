<?php

namespace Tests\Feature;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class MateriKelasMigrationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->assertTrue($this->app->environment('testing'));
        $this->assertSame('sqlite', config('database.default'));
        $this->assertSame(':memory:', config('database.connections.sqlite.database'));
        $this->assertSame(1, (int) DB::selectOne('PRAGMA foreign_keys')->foreign_keys);
    }

    public function test_backfills_grade_labels_and_class_names_to_matching_classes(): void
    {
        $this->createLegacySchema();
        DB::table('tm_kelas')->insert([
            ['id_kelas' => 'KLS001', 'nama_kelas' => 'X TKJ 1', 'tingkat' => 'X', 'id_tahun_ajaran' => 'TA001'],
            ['id_kelas' => 'KLS002', 'nama_kelas' => 'X TKJ 2', 'tingkat' => 'X', 'id_tahun_ajaran' => 'TA001'],
            ['id_kelas' => 'KLS003', 'nama_kelas' => 'XI TKJ 1', 'tingkat' => 'XI', 'id_tahun_ajaran' => 'TA001'],
            ['id_kelas' => 'KLS004', 'nama_kelas' => 'X TKJ 1', 'tingkat' => 'X', 'id_tahun_ajaran' => 'TA002'],
        ]);
        DB::table('tm_materi')->insert([
            ['id_materi' => 'M001', 'judul' => 'Materi nama kelas', 'bab' => 'Bab 1', 'kelas' => 'X TKJ 1', 'id_mapel' => 'MP001'],
            ['id_materi' => 'M002', 'judul' => 'Materi tingkat X', 'bab' => 'Bab 2', 'kelas' => 'X', 'id_mapel' => 'MP001'],
            ['id_materi' => 'M003', 'judul' => 'Materi tingkat XI', 'bab' => 'Bab 3', 'kelas' => 'XI', 'id_mapel' => 'MP001'],
            ['id_materi' => 'M004', 'judul' => 'Materi tanpa kelas', 'bab' => 'Bab 4', 'kelas' => null, 'id_mapel' => 'MP001'],
        ]);

        $this->migration()->up();

        $actual = DB::table('tp_materi_kelas')
            ->orderBy('id_materi')
            ->orderBy('id_kelas')
            ->get(['id_materi', 'id_kelas'])
            ->map(fn (object $row): array => [$row->id_materi, $row->id_kelas])
            ->all();

        $this->assertSame([
            ['M001', 'KLS001'],
            ['M001', 'KLS004'],
            ['M002', 'KLS001'],
            ['M002', 'KLS002'],
            ['M002', 'KLS004'],
            ['M003', 'KLS003'],
        ], $actual);
    }

    public function test_unmatched_legacy_class_fails_before_creating_the_pivot_table(): void
    {
        $this->createLegacySchema();
        DB::table('tm_kelas')->insert([
            'id_kelas' => 'KLS001',
            'nama_kelas' => 'X TKJ 1',
            'tingkat' => 'X',
            'id_tahun_ajaran' => 'TA001',
        ]);
        DB::table('tm_materi')->insert([
            'id_materi' => 'M001',
            'judul' => 'Materi kelas tidak dikenal',
            'bab' => 'Bab 1',
            'kelas' => 'Diploma',
            'id_mapel' => 'MP001',
        ]);

        try {
            $this->migration()->up();
            $this->fail('The migration should reject an unmapped legacy class.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('Diploma', $exception->getMessage());
        }

        $this->assertFalse(Schema::hasTable('tp_materi_kelas'));
    }

    private function createLegacySchema(): void
    {
        Schema::create('tm_kelas', function (Blueprint $table): void {
            $table->string('id_kelas', 50)->primary();
            $table->string('nama_kelas', 50);
            $table->string('tingkat', 10);
            $table->string('id_tahun_ajaran', 50);
            $table->unique(['nama_kelas', 'id_tahun_ajaran'], 'uq_kelas_nama_tahun');
        });

        Schema::create('tm_materi', function (Blueprint $table): void {
            $table->string('id_materi', 50)->primary();
            $table->string('judul', 150);
            $table->string('bab', 50)->nullable();
            $table->string('kelas', 50)->nullable();
            $table->string('id_mapel', 50);
            $table->unique(['kelas', 'bab', 'judul'], 'uq_materi_judul');
            $table->unique(['id_mapel', 'kelas', 'bab'], 'uq_materi_mapel_kelas_bab');
        });
    }

    private function migration(): Migration
    {
        return require database_path('migrations/2026_11_27_192330_create_tp_materi_kelas_table.php');
    }
}
