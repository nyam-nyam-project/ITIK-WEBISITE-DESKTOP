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
        Schema::create('tm_materis', function (Blueprint $table) {
            $table->string('id_materi', 50)->primary();
            $table->string('judul', 150);
            $table->string('bab', 50)->nullable();
            $table->string('kelas', 50)->nullable();
            $table->text('deskripsi')->nullable();
            $table->string('isi_materi', 255)->nullable()->comment('Path/nama file PDF materi');
            $table->string('link_youtube', 255)->nullable();
            $table->string('id_user', 50)->comment('Guru/operator yang mengelola materi ini');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable()->useCurrent()->useCurrentOnUpdate();

            $table->unique(['kelas', 'bab', 'judul'], 'uq_materis_judul');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tm_materis');
    }
};
