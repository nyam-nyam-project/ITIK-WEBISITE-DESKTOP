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
        Schema::create('tr_mengerjakan', function (Blueprint $table) {
            $table->increments('id_mengerjakan');
            $table->string('id_kuis', 50);
            $table->string('id_user', 50);
            $table->decimal('nilai', 5, 2)->nullable();
            $table->date('tanggal')->nullable();
            $table->integer('percobaan_ke')->default(1);
            $table->string('status_pengerjaan', 20)->default('final');
            $table->string('status_validasi', 50)->default('belum divalidasi');
            $table->string('id_validator', 50)->nullable();

            $table->unique(['id_kuis', 'id_user', 'percobaan_ke'], 'uq_kuis_siswa_percobaan');

            $table->foreign('id_kuis', 'fk_mengerjakan_kuis')->references('id_kuis')->on('tm_kuis')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('id_user', 'fk_mengerjakan_user')->references('id_user')->on('tm_user')->onDelete('cascade')->onUpdate('cascade');
            $table->foreign('id_validator', 'fk_mengerjakan_validator')->references('id_user')->on('tm_user')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tr_mengerjakan');
    }
};
