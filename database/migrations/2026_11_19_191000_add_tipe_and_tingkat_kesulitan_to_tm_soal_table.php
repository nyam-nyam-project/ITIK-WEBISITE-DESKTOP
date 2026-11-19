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
        if (Schema::hasTable('tm_soal')) {
            Schema::table('tm_soal', function (Blueprint $table) {
                if (! Schema::hasColumn('tm_soal', 'tipe')) {
                    $table->enum('tipe', ['pilihan_ganda', 'essay'])
                        ->default('pilihan_ganda')
                        ->after('id_kuis');
                }

                if (! Schema::hasColumn('tm_soal', 'tingkat_kesulitan')) {
                    $table->string('tingkat_kesulitan', 50)
                        ->nullable()
                        ->after('bobot');
                }
            });
        }

        if (Schema::hasTable('tr_draft_jawaban')) {
            Schema::table('tr_draft_jawaban', function (Blueprint $table) {
                if (! Schema::hasColumn('tr_draft_jawaban', 'jawaban_teks')) {
                    $table->text('jawaban_teks')
                        ->nullable()
                        ->after('jawaban_dipilih');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('tm_soal')) {
            Schema::table('tm_soal', function (Blueprint $table) {
                if (Schema::hasColumn('tm_soal', 'tipe')) {
                    $table->dropColumn('tipe');
                }
                if (Schema::hasColumn('tm_soal', 'tingkat_kesulitan')) {
                    $table->dropColumn('tingkat_kesulitan');
                }
            });
        }

        if (Schema::hasTable('tr_draft_jawaban')) {
            Schema::table('tr_draft_jawaban', function (Blueprint $table) {
                if (Schema::hasColumn('tr_draft_jawaban', 'jawaban_teks')) {
                    $table->dropColumn('jawaban_teks');
                }
            });
        }
    }
};
