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
        Schema::create('tr_remedial_siswa', function (Blueprint $table) {
            $table->increments('id_remedial_siswa');
            $table->string('id_kuis_remedial', 50);

            // WAJIB unsignedInteger agar kompatibel dengan increments('id_mengerjakan') di atas!
            $table->unsignedInteger('id_mengerjakan_asal');

            $table->string('id_guru', 50);
            $table->date('tanggal_ditugaskan');
            $table->string('status', 20)->default('ditugaskan');

            $table->unique(['id_kuis_remedial', 'id_mengerjakan_asal'], 'uq_remedial_siswa');

            $table->foreign('id_guru', 'fk_remedialsiswa_guru')->references('id_user')->on('tm_user')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('id_kuis_remedial', 'fk_remedialsiswa_kuis')->references('id_kuis')->on('tm_kuis')->onDelete('cascade')->onUpdate('cascade');

            // Relasi aman karena tipe datanya sudah sama persis (INT UNSIGNED)
            $table->foreign('id_mengerjakan_asal', 'fk_remedialsiswa_mengerjakan')
                ->references('id_mengerjakan')
                ->on('tr_mengerjakan')
                ->onDelete('cascade')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tr_remedial_siswa');
    }
};
