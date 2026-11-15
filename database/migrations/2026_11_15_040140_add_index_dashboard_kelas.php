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
        Schema::table('tr_mengerjakan', function (Blueprint $table): void {
            $table->index(
                ['id_user', 'id_kuis', 'status_pengerjaan'],
                'idx_mengerjakan_user_kuis_status'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tr_mengerjakan', function (Blueprint $table): void {
            $table->dropIndex('idx_mengerjakan_user_kuis_status');
        });
    }
};
