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
        Schema::create('tp_mapel_kelas', function (Blueprint $table) {
            $table->bigIncrements('id_mapel_kelas');
            $table->string('id_mapel', 50);
            $table->string('id_kelas', 50);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['id_mapel', 'id_kelas'], 'uq_mapel_kelas');

            $table->foreign('id_mapel', 'fk_mapelkelas_mapel')->references('id_mapel')->on('tm_mapel')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('id_kelas', 'fk_mapelkelas_kelas')->references('id_kelas')->on('tm_kelas')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tp_mapel_kelas');
    }
};
