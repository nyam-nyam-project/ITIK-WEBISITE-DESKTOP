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
        Schema::create('tr_progress', function (Blueprint $table) {
            $table->string('id_progress', 50)->primary();

            $table->string('status', 50)
                ->nullable()
                ->comment('Contoh: belum mulai, sedang belajar, selesai');

            $table->date('tanggal')->nullable();

            $table->string('id_materi', 50)->index('fk_progress_materi');

            $table->string('id_user', 50)
                ->index('fk_progress_user')
                ->comment('Siswa pemilik progress ini');

            $table->unique(
                ['id_materi', 'id_user'],
                'uq_progress_materi_user'
            );

            $table->foreign('id_materi', 'fk_progress_materi')
                ->references('id_materi')
                ->on('tm_materi')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_user', 'fk_progress_user')
                ->references('id_user')
                ->on('tm_user')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
