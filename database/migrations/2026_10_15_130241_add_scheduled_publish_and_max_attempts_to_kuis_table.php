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
        Schema::table('kuis', function (Blueprint $table): void {
            $table->dateTime('waktu_publish')
                ->nullable()
                ->after('waktu_selesai');
            $table->unsignedSmallInteger('maks_percobaan')
                ->nullable()
                ->after('kkm');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kuis', function (Blueprint $table): void {
            $table->dropColumn(['waktu_publish', 'maks_percobaan']);
        });
    }
};
