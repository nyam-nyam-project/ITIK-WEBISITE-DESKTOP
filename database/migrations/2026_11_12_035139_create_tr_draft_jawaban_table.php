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
        Schema::create('tr_draft_jawaban', function (Blueprint $table) {
            $table->increments('id_draft');

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

            // Unique constraint & Indexes
            $table->unique(['id_kuis', 'id_user', 'id_soal'], 'uq_draft');
            $table->index('id_user', 'fk_draft_user');
            $table->index('id_soal', 'fk_draft_soal');

            // Foreign Key ke tm_kuis
            $table->foreign('id_kuis', 'fk_draft_kuis')
                ->references('id_kuis')
                ->on('tm_kuis')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            // Foreign Key ke tm_soal
            $table->foreign('id_soal', 'fk_draft_soal')
                ->references('id_soal')
                ->on('tm_soal')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            // Foreign Key ke tm_user
            $table->foreign('id_user', 'fk_draft_user')
                ->references('id_user')
                ->on('tm_user')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tr_draft_jawaban');
    }
};
