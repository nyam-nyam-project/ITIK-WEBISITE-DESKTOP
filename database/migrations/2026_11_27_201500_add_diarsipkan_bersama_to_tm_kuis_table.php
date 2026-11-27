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
        if (! Schema::hasColumn('tm_kuis', 'diarsipkan_bersama')) {
            Schema::table('tm_kuis', function (Blueprint $table): void {
                $table->string('diarsipkan_bersama', 50)->nullable()->after('is_aktif');
                $table->index('diarsipkan_bersama', 'idx_kuis_diarsipkan_bersama');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('tm_kuis', 'diarsipkan_bersama')) {
            Schema::table('tm_kuis', function (Blueprint $table): void {
                if (Schema::hasIndex('tm_kuis', 'idx_kuis_diarsipkan_bersama')) {
                    $table->dropIndex('idx_kuis_diarsipkan_bersama');
                }
                $table->dropColumn('diarsipkan_bersama');
            });
        }
    }
};
