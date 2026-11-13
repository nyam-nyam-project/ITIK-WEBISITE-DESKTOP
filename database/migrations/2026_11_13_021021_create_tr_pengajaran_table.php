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
        Schema::create('tr_pengajaran', function (Blueprint $table) {
            $table->increments('id_pengajaran');
            $table->string('id_user', 50);
            $table->string('id_kelas', 50);
            $table->string('id_mapel', 50);
            $table->timestamp('created_at')->nullable()->useCurrent();

            $table->unique(['id_user', 'id_kelas', 'id_mapel'], 'uq_pengajaran');
            $table->index(['id_mapel', 'id_kelas'], 'fk_pengajaran_mapel_kelas');

            $table->foreign(['id_mapel', 'id_kelas'], 'fk_pengajaran_mapel_kelas')
                ->references(['id_mapel', 'id_kelas'])
                ->on('tp_mapel_kelas')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->foreign('id_user', 'fk_pengajaran_user')
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
        Schema::dropIfExists('tr_pengajaran');
    }
};
