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
        Schema::create('remedial_siswa', function (Blueprint $table) {
            $table->integer('id_remedial_siswa')->autoIncrement();
            $table->string('id_kuis_remedial', 50);
            $table->integer('id_mengerjakan_asal')
                ->comment('Baris nilai siswa yang di bawah KKM (alasan diberi remedial)');
            $table->string('id_guru', 50)
                ->comment('Guru yang menugaskan remedial ini');
            $table->date('tanggal_ditugaskan');
            $table->string('status', 20)
                ->default('ditugaskan')
                ->comment('ditugaskan/selesai');

            
            $table->unique(
                ['id_kuis_remedial', 'id_mengerjakan_asal'],
                'uq_remedial_siswa'
            );
            $table->index('id_mengerjakan_asal', 'fk_remedialsiswa_mengerjakan');
            $table->index('id_guru', 'fk_remedialsiswa_guru');

            $table->foreign('id_guru', 'fk_remedialsiswa_guru')
                ->references('id_user')
                ->on('users')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_kuis_remedial', 'fk_remedialsiswa_kuis')
                ->references('id_kuis')
                ->on('kuis')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_mengerjakan_asal', 'fk_remedialsiswa_mengerjakan')
                ->references('id_mengerjakan')
                ->on('mengerjakans')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remedial_siswa');
    }
};
