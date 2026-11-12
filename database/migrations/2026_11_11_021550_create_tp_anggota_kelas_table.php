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
        Schema::create('tp_anggota_kelas', function (Blueprint $table) {
            $table->bigIncrements('id_anggota_kelas');
            $table->string('id_kelas', 50);
            $table->string('id_user', 50);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['id_kelas', 'id_user'], 'uq_anggota_kelas');

            $table->foreign('id_kelas', 'fk_anggota_kelas_kelas')->references('id_kelas')->on('tm_kelas')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('id_user', 'fk_anggota_kelas_user')->references('id_user')->on('tm_user')->onDelete('restrict')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tp_anggota_kelas');
    }
};
