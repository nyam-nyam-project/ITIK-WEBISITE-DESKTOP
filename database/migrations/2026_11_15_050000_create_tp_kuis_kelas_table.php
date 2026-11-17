<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tp_kuis_kelas', function (Blueprint $table) {
            $table->string('id_kuis', 50);
            $table->string('id_kelas', 50);
            $table->primary(['id_kuis', 'id_kelas']);

            $table->foreign('id_kuis')->references('id_kuis')->on('tm_kuis')
                ->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('id_kelas')->references('id_kelas')->on('tm_kelas')
                ->restrictOnDelete()->cascadeOnUpdate();
        });

        // Backfill: hubungkan kuis lama ke semua kelas yang punya mapel yang sama.
        $ignore = DB::getDriverName() === 'sqlite' ? 'OR IGNORE' : 'IGNORE';
        DB::statement("
            INSERT {$ignore} INTO tp_kuis_kelas (id_kuis, id_kelas)
            SELECT k.id_kuis, mk.id_kelas
            FROM tm_kuis k
            JOIN tp_mapel_kelas mk ON mk.id_mapel = k.id_mapel
        ");
    }

    public function down(): void
    {
        Schema::dropIfExists('tp_kuis_kelas');
    }
};
