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
        Schema::create('tp_materi_kuis', function (Blueprint $table) {
            $table->increments('id_materi_kuis');
            $table->string('id_materi', 50);
            $table->string('id_kuis', 50);

            $table->unique(['id_materi', 'id_kuis'], 'uq_materi_kuis');

            $table->foreign('id_materi', 'fk_materikuis_materi')->references('id_materi')->on('tm_materi')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_kuis', 'fk_materikuis_kuis')->references('id_kuis')->on('tm_kuis')->onDelete('cascade')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tp_materi_kuis');
    }
};
