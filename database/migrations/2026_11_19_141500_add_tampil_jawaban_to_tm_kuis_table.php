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
        if (Schema::hasTable('tm_kuis') && ! Schema::hasColumn('tm_kuis', 'tampil_jawaban')) {
            Schema::table('tm_kuis', function (Blueprint $table) {
                $table->enum('tampil_jawaban', ['tidak', 'setelah_submit', 'setelah_kkm'])
                    ->default('setelah_submit')
                    ->after('status_publikasi');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tm_kuis') && Schema::hasColumn('tm_kuis', 'tampil_jawaban')) {
            Schema::table('tm_kuis', function (Blueprint $table) {
                $table->dropColumn('tampil_jawaban');
            });
        }
    }
};
