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
        Schema::create('mengerjakans', function (Blueprint $table) {
            $table->integer('id_mengerjakan')->autoIncrement();
            $table->string('id_kuis', 50);
            $table->string('id_user', 50)
                ->comment('Siswa yang mengerjakan');
            $table->decimal('nilai', 5, 2)->nullable();
            $table->date('tanggal')->nullable();
            $table->integer('percobaan_ke')
                ->default(1)
                ->comment('Percobaan ke berapa (mendukung kerjakan ulang)');
            $table->string('status_pengerjaan', 20)
                ->default('final')
                ->comment('final/dibatalkan');
            $table->string('status_validasi', 50)
                ->nullable()
                ->default('belum divalidasi')
                ->comment('Validasi NILAI oleh guru');
            $table->string('id_validator', 50)
                ->nullable()
                ->comment('Guru yang memvalidasi nilai ini');

            
            $table->unique(
                ['id_kuis', 'id_user', 'percobaan_ke'],
                'uq_kuis_siswa_percobaan'
            );
            $table->index('id_user', 'fk_mengerjakan_user');
            $table->index('id_validator', 'fk_mengerjakan_validator');

            $table->foreign('id_kuis', 'fk_mengerjakan_kuis')
                ->references('id_kuis')
                ->on('kuis')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_user', 'fk_mengerjakan_user')
                ->references('id_user')
                ->on('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_validator', 'fk_mengerjakan_validator')
                ->references('id_user')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mengerjakans');
    }
};
