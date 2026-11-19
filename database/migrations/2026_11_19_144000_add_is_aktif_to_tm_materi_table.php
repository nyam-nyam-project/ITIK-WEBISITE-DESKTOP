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
        if (Schema::hasTable('tm_materi') && ! Schema::hasColumn('tm_materi', 'is_aktif')) {
            Schema::table('tm_materi', function (Blueprint $table) {
                $table->boolean('is_aktif')->default(true)->after('id_user');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tm_materi') && Schema::hasColumn('tm_materi', 'is_aktif')) {
            Schema::table('tm_materi', function (Blueprint $table) {
                $table->dropColumn('is_aktif');
            });
        }
    }
};
