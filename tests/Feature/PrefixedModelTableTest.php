<?php

namespace Tests\Feature;

use App\Models\Mengerjakan;
use App\Models\NilaiEssay;
use App\Models\Pengajaran;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class PrefixedModelTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('tm_tahun_ajaran', function (Blueprint $table): void {
            $table->string('id_tahun_ajaran', 50)->primary();
            $table->boolean('is_aktif')->default(false);
        });

        Schema::create('tr_pengajaran', function (Blueprint $table): void {
            $table->increments('id_pengajaran');
            $table->string('id_user', 50);
            $table->string('id_kelas', 50);
            $table->string('id_mapel', 50);
        });

        Schema::create('tm_kuis', function (Blueprint $table): void {
            $table->string('id_kuis', 50)->primary();
            $table->string('id_mapel', 50);
            $table->string('id_user', 50);
        });

        Schema::create('tr_mengerjakan', function (Blueprint $table): void {
            $table->increments('id_mengerjakan');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
        });

        Schema::create('tp_anggota_kelas', function (Blueprint $table): void {
            $table->string('id_kelas', 50);
            $table->string('id_user', 50);
        });

        Schema::create('tr_nilai_essay', function (Blueprint $table): void {
            $table->increments('id_nilai_essay');
            $table->unsignedInteger('id_mengerjakan');
            $table->string('id_soal', 50);
            $table->text('jawaban_teks')->nullable();
            $table->decimal('nilai', 5, 2)->nullable();
            $table->text('catatan_guru')->nullable();
            $table->string('id_penilai', 50)->nullable();
            $table->timestamp('dinilai_at')->nullable();
        });
    }

    public function test_pengajaran_lookup_uses_the_prefixed_table(): void
    {
        DB::table('tr_pengajaran')->insert([
            'id_user' => 'G001',
            'id_kelas' => 'K001',
            'id_mapel' => 'MP001',
        ]);

        $guru = new User;
        $guru->id_user = 'G001';

        $this->assertSame(['MP001'], Pengajaran::mapelUntukGuru($guru)->all());
    }

    public function test_teacher_scope_uses_the_prefixed_pengajaran_table(): void
    {
        DB::table('tm_kuis')->insert([
            'id_kuis' => 'Q001',
            'id_mapel' => 'MP001',
            'id_user' => 'G002',
        ]);
        DB::table('tr_mengerjakan')->insert([
            'id_kuis' => 'Q001',
            'id_user' => 'S001',
        ]);
        DB::table('tr_pengajaran')->insert([
            'id_user' => 'G001',
            'id_kelas' => 'K001',
            'id_mapel' => 'MP001',
        ]);
        DB::table('tp_anggota_kelas')->insert([
            'id_kelas' => 'K001',
            'id_user' => 'S001',
        ]);

        $guru = new User;
        $guru->id_user = 'G001';

        $this->assertSame(
            [1],
            Mengerjakan::query()->dikelolaOlehGuru($guru)->pluck('id_mengerjakan')->all()
        );
    }

    public function test_nilai_essay_persists_to_the_prefixed_table(): void
    {
        $nilaiEssay = NilaiEssay::create([
            'id_mengerjakan' => 1,
            'id_soal' => 'S001',
            'jawaban_teks' => 'Jawaban siswa',
        ]);

        $this->assertDatabaseHas('tr_nilai_essay', [
            'id_nilai_essay' => $nilaiEssay->id_nilai_essay,
            'jawaban_teks' => 'Jawaban siswa',
        ]);
    }
}
