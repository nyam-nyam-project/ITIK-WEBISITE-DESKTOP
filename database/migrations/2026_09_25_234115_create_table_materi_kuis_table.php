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
        Schema::create('materi_kuis', function (Blueprint $table) {
            $table->integer('id_materi_kuis')->autoIncrement();
            $table->string('id_materi', 50);
            $table->string('id_kuis', 50);

            // HAPUS $table->primary('id_materi_kuis');

            $table->unique(['id_materi', 'id_kuis'], 'uq_materi_kuis');
            $table->index('id_kuis', 'fk_materikuis_kuis');

            $table->foreign('id_kuis', 'fk_materikuis_kuis')
                ->references('id_kuis')
                ->on('kuis')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_materi', 'fk_materikuis_materi')
                ->references('id_materi')
                ->on('materis')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materi_kuis');
    }
};