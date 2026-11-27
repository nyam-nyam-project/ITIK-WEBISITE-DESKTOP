<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('tp_materi_kelas')) {
            $labelKelasLama = DB::table('tm_materi')
                ->whereNotNull('kelas')
                ->distinct()
                ->pluck('kelas');
            $kelasIdsPerLabel = [];

            foreach ($labelKelasLama as $labelKelas) {
                $labelKelas = trim($labelKelas);
                $kelasIds = DB::table('tm_kelas')
                    ->where('tingkat', $labelKelas)
                    ->pluck('id_kelas');

                if ($kelasIds->isEmpty()) {
                    $kelasIds = DB::table('tm_kelas')
                        ->where('nama_kelas', $labelKelas)
                        ->pluck('id_kelas');
                }

                if ($kelasIds->isEmpty()) {
                    throw new RuntimeException(
                        "Backfill materi-kelas gagal: nilai kelas lama '{$labelKelas}' tidak cocok dengan tingkat atau nama_kelas di tm_kelas."
                    );
                }

                $kelasIdsPerLabel[$labelKelas] = $kelasIds->all();
            }

            Schema::create('tp_materi_kelas', function (Blueprint $table): void {
                $table->string('id_materi', 50);
                $table->string('id_kelas', 50);
                $table->primary(['id_materi', 'id_kelas']);

                $table->foreign('id_materi', 'fk_materi_kelas_materi')
                    ->references('id_materi')
                    ->on('tm_materi')
                    ->cascadeOnDelete()
                    ->cascadeOnUpdate();
                $table->foreign('id_kelas', 'fk_materi_kelas_kelas')
                    ->references('id_kelas')
                    ->on('tm_kelas')
                    ->restrictOnDelete()
                    ->cascadeOnUpdate();
            });

            $backfill = [];

            foreach (DB::table('tm_materi')->whereNotNull('kelas')->get(['id_materi', 'kelas']) as $materi) {
                foreach ($kelasIdsPerLabel[trim($materi->kelas)] as $idKelas) {
                    $backfill[] = [
                        'id_materi' => $materi->id_materi,
                        'id_kelas' => $idKelas,
                    ];
                }
            }

            if ($backfill !== []) {
                DB::table('tp_materi_kelas')->insertOrIgnore($backfill);
            }
        }

        // Pastikan kolom id_mapel tetap memiliki index mandiri sebelum uq_materi_mapel_kelas_bab dihapus,
        // karena MySQL membutuhkan index pada id_mapel untuk foreign key fk_materi_mapel.
        if (! Schema::hasIndex('tm_materi', 'idx_materi_mapel') && ! Schema::hasIndex('tm_materi', 'fk_materi_mapel')) {
            Schema::table('tm_materi', function (Blueprint $table): void {
                $table->index('id_mapel', 'idx_materi_mapel');
            });
        }

        Schema::table('tm_materi', function (Blueprint $table): void {
            if (Schema::hasIndex('tm_materi', 'uq_materi_judul')) {
                $table->dropUnique('uq_materi_judul');
            }
            if (Schema::hasIndex('tm_materi', 'uq_materi_mapel_kelas_bab')) {
                $table->dropUnique('uq_materi_mapel_kelas_bab');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $idMateriDenganBanyakKelas = DB::table('tp_materi_kelas')
            ->select('id_materi')
            ->groupBy('id_materi')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($idMateriDenganBanyakKelas) {
            throw new RuntimeException(
                'Rollback relasi materi-kelas dibatalkan karena ada materi yang terhubung ke lebih dari satu kelas.'
            );
        }

        $materiKelas = DB::table('tp_materi_kelas')
            ->join('tm_kelas', 'tp_materi_kelas.id_kelas', '=', 'tm_kelas.id_kelas')
            ->select('tp_materi_kelas.id_materi', 'tm_kelas.nama_kelas')
            ->get();

        foreach ($materiKelas as $relasi) {
            DB::table('tm_materi')
                ->where('id_materi', $relasi->id_materi)
                ->update(['kelas' => $relasi->nama_kelas]);
        }

        Schema::dropIfExists('tp_materi_kelas');

        Schema::table('tm_materi', function (Blueprint $table): void {
            if (! Schema::hasIndex('tm_materi', 'uq_materi_judul')) {
                $table->unique(['kelas', 'bab', 'judul'], 'uq_materi_judul');
            }
            if (! Schema::hasIndex('tm_materi', 'uq_materi_mapel_kelas_bab')) {
                $table->unique(['id_mapel', 'kelas', 'bab'], 'uq_materi_mapel_kelas_bab');
            }
            if (Schema::hasIndex('tm_materi', 'idx_materi_mapel')) {
                $table->dropIndex('idx_materi_mapel');
            }
        });
    }
};
