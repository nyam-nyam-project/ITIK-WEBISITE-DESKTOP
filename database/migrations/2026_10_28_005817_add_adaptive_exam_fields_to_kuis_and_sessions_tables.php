<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('kuis', function (Blueprint $table): void {
            $table->string('tingkat_ujian', 20)
                ->default('mudah')
                ->after('kategori');
        });

        Schema::table('sesi_kuis', function (Blueprint $table): void {
            $table->unsignedInteger('durasi_detik')
                ->nullable()
                ->after('waktu_selesai');
        });

        DB::table('kuis')
            ->where('kategori', 'pass-test')
            ->update(['kategori' => 'pre-test']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('kuis')
            ->where('kategori', 'pre-test')
            ->update(['kategori' => 'pass-test']);

        Schema::table('sesi_kuis', function (Blueprint $table): void {
            $table->dropColumn('durasi_detik');
        });

        Schema::table('kuis', function (Blueprint $table): void {
            $table->dropColumn('tingkat_ujian');
        });
    }
};
