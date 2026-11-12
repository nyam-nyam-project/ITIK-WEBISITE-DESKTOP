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
        Schema::create('tm_kuis', function (Blueprint $table) {
            $table->string('id_kuis', 50)->primary();
            $table->string('judul', 150);
            $table->text('deskripsi')->nullable();
            $table->string('kategori', 50);
            $table->enum('tingkat_kesulitan', ['mudah', 'sedang', 'sulit'])->default('mudah');
            $table->string('id_mapel', 50);
            $table->string('id_tahun_ajaran', 50);
            $table->unsignedInteger('alokasi_waktu')->nullable();
            $table->decimal('kkm', 5, 2)->nullable();
            $table->dateTime('waktu_mulai')->nullable();
            $table->dateTime('waktu_selesai')->nullable();
            $table->string('id_user', 50);
            $table->string('status_publikasi', 20)->default('draft');
            $table->string('id_publisher', 50)->nullable();
            $table->boolean('is_aktif')->default(true);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('id_mapel', 'fk_kuis_mapel')->references('id_mapel')->on('tm_mapel')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('id_tahun_ajaran', 'fk_kuis_tahun')->references('id_tahun_ajaran')->on('tm_tahun_ajaran')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('id_user', 'fk_kuis_user')->references('id_user')->on('tm_user')->onDelete('restrict')->onUpdate('cascade');
            $table->foreign('id_publisher', 'fk_kuis_publisher')->references('id_user')->on('tm_user')->onDelete('set null')->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tm_kuis');
    }
};
