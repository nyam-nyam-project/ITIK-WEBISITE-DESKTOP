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
        Schema::create('tr_sesi_kuis', function (Blueprint $table) {
            $table->id('id_sesi');

            $table->string('id_kuis', 50);
            $table->string('id_user', 50);

            $table->unsignedInteger('percobaan_ke')->default(1);

            $table->timestamp('waktu_mulai')->useCurrent();

            $table->timestamp('waktu_selesai')->nullable();

            // Menyesuaikan referensi ke tabel master kuis yang baru (tm_kuis)
            $table->foreign('id_kuis', 'fk_sesi_kuis')
                ->references('id_kuis')
                ->on('tm_kuis')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            // Menyesuaikan referensi ke tabel master user yang baru (tm_user)
            $table->foreign('id_user', 'fk_sesi_user')
                ->references('id_user')
                ->on('tm_user')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            // Mencegah duplikasi sesi kuis aktif untuk siswa & percobaan yang sama
            $table->unique(
                ['id_kuis', 'id_user', 'percobaan_ke'],
                'uq_sesi_kuis_siswa_percobaan'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tr_sesi_kuis');
    }
};
