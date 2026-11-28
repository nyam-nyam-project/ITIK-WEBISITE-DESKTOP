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
        if (! Schema::hasColumn('tm_kuis', 'izinkan_perulangan')) {
            Schema::table('tm_kuis', function (Blueprint $table) {
                $table->boolean('izinkan_perulangan')->default(true)->after('tampil_jawaban');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tm_kuis', 'izinkan_perulangan')) {
            Schema::table('tm_kuis', function (Blueprint $table) {
                $table->dropColumn('izinkan_perulangan');
            });
        }
    }
};
