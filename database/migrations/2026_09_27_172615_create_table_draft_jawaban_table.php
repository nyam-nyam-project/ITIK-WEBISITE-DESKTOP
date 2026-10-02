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
        Schema::create('draft_jawaban', function (Blueprint $table) {
            $table->integer('id_draft')->autoIncrement();
            $table->string('id_kuis', 50);
            $table->string('id_user', 50)
                ->comment('Siswa yang mengerjakan');
            $table->string('id_soal', 50);
            $table->string('jawaban_dipilih', 5)
                ->nullable()
                ->comment('A/B/C/D/E');
            $table->timestamp('updated_at')
                ->useCurrent()
                ->useCurrentOnUpdate();

            
            $table->unique(['id_kuis', 'id_user', 'id_soal'], 'uq_draft');
            $table->index('id_user', 'fk_draft_user');
            $table->index('id_soal', 'fk_draft_soal');

            $table->foreign('id_kuis', 'fk_draft_kuis')
                ->references('id_kuis')
                ->on('kuis')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_soal', 'fk_draft_soal')
                ->references('id_soal')
                ->on('soals')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_user', 'fk_draft_user')
                ->references('id_user')
                ->on('users')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('draft_jawaban');
    }
};
