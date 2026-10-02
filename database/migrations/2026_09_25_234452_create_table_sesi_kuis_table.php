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
        Schema::create('sesi_kuis', function (Blueprint $table) {
            $table->id('id_sesi');

            $table->string('id_kuis', 50);
            $table->string('id_user', 50);

            $table->unsignedInteger('percobaan_ke')->default(1);

            $table->timestamp('waktu_mulai')->useCurrent();

            $table->timestamp('waktu_selesai')->nullable();

            $table->foreign('id_kuis')
                ->references('id_kuis')
                ->on('kuis')
                ->cascadeOnDelete();

            $table->foreign('id_user')
                ->references('id_user')
                ->on('users')
                ->cascadeOnDelete();

            $table->unique([
                'id_kuis',
                'id_user',
                'percobaan_ke'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesi_kuis');
    }
};
