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
        Schema::create('tm_materi', function (Blueprint $table) {
            $table->string('id_materi', 50)->primary();
            $table->string('judul', 150);
            $table->string('bab', 50)->nullable();
            $table->string('kelas', 50)->nullable();
            $table->string('id_mapel', 50);
            $table->text('deskripsi')->nullable();
            $table->string('isi_materi', 255)->nullable();
            $table->string('link_youtube', 255)->nullable();
            $table->string('id_user', 50);
            $table->timestamps();

            $table->unique(['kelas', 'bab', 'judul'], 'uq_materi_judul');

            $table->foreign('id_mapel', 'fk_materi_mapel')
                ->references('id_mapel')
                ->on('tm_mapel')
                ->onDelete('restrict')
                ->onUpdate('cascade');

            $table->foreign('id_user', 'fk_materi_user')
                ->references('id_user')
                ->on('tm_user')
                ->onDelete('restrict')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tm_materi');
    }
};
