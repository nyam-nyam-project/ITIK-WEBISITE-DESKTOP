<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tm_materi', function (Blueprint $table) {
            $table->unique(['id_mapel', 'kelas', 'bab'], 'uq_materi_mapel_kelas_bab');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tm_materi', function (Blueprint $table) {
            $table->dropUnique('uq_materi_mapel_kelas_bab');
        });
    }
};
