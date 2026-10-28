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
        Schema::table('soals', function (Blueprint $table) {
            $table->string('tipe_soal', 30)
                ->default('pilihan_ganda')
                ->after('pertanyaan');
            $table->string('gambar_pertanyaan')->nullable();
            $table->string('gambar_opsi_a')->nullable();
            $table->string('gambar_opsi_b')->nullable();
            $table->string('gambar_opsi_c')->nullable();
            $table->string('gambar_opsi_d')->nullable();
            $table->string('gambar_opsi_e')->nullable();
            $table->string('opsi_a', 255)->nullable()->change();
            $table->string('opsi_b', 255)->nullable()->change();
            $table->string('opsi_c', 255)->nullable()->change();
            $table->string('opsi_d', 255)->nullable()->change();
            $table->string('jawaban', 5)->nullable()->change();
        });

        Schema::table('draft_jawaban', function (Blueprint $table) {
            $table->text('jawaban_text')->nullable();
        });

        Schema::create('jawaban_siswa', function (Blueprint $table) {
            $table->id('id_jawaban');
            $table->integer('id_mengerjakan');
            $table->string('id_soal', 50);
            $table->string('jawaban_dipilih', 5)->nullable();
            $table->text('jawaban_text')->nullable();

            $table->unique(
                ['id_mengerjakan', 'id_soal'],
                'uq_jawaban_siswa_soal'
            );

            $table->foreign('id_mengerjakan', 'fk_jawaban_siswa_mengerjakan')
                ->references('id_mengerjakan')
                ->on('mengerjakans')
                ->cascadeOnDelete();

            $table->foreign('id_soal', 'fk_jawaban_siswa_soal')
                ->references('id_soal')
                ->on('soals')
                ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_siswa');

        Schema::table('draft_jawaban', function (Blueprint $table) {
            $table->dropColumn('jawaban_text');
        });

        Schema::table('soals', function (Blueprint $table) {
            $table->dropColumn([
                'tipe_soal',
                'gambar_pertanyaan',
                'gambar_opsi_a',
                'gambar_opsi_b',
                'gambar_opsi_c',
                'gambar_opsi_d',
                'gambar_opsi_e',
            ]);
        });
    }
};
