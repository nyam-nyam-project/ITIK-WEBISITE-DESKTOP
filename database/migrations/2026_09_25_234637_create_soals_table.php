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
        Schema::create('soals', function (Blueprint $table) {
            $table->string('id_soal', 50)->primary();
            $table->string('id_kuis', 50);
            $table->text('pertanyaan');
            $table->string('opsi_a', 255)->nullable(false);
            $table->string('opsi_b', 255)->nullable(false);
            $table->string('opsi_c', 255)->nullable(false);
            $table->string('opsi_d', 255)->nullable(false);
            $table->string('opsi_e', 255)->nullable();
            $table->string('jawaban', 5)
                ->comment('Contoh: A/B/C/D/E');
            $table->string('tingkat_kesulitan', 50)->nullable();

            $table->index('id_kuis', 'fk_soal_kuis');

            $table->foreign('id_kuis', 'fk_soal_kuis')
                ->references('id_kuis')
                ->on('kuis')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soals');
    }
};
