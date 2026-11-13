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
        Schema::create('tr_nilai_essay', function (Blueprint $table) {
            $table->increments('id_nilai_essay');
            $table->unsignedInteger('id_mengerjakan');
            $table->string('id_soal', 50);
            $table->text('jawaban_teks')->nullable();
            $table->decimal('nilai', 5, 2)->nullable();
            $table->text('catatan_guru')->nullable();
            $table->string('id_penilai', 50)->nullable();
            $table->timestamp('dinilai_at')->nullable();

            $table->unique(['id_mengerjakan', 'id_soal'], 'uq_nilai_essay');
            $table->index('id_soal', 'fk_nilai_essay_soal');
            $table->index('id_penilai', 'fk_nilai_essay_penilai');

            $table->foreign('id_mengerjakan', 'fk_nilai_essay_mengerjakan')
                ->references('id_mengerjakan')
                ->on('tr_mengerjakan')
                ->onDelete('cascade')
                ->onUpdate('cascade');
            $table->foreign('id_soal', 'fk_nilai_essay_soal')
                ->references('id_soal')
                ->on('tm_soal')
                ->onDelete('restrict')
                ->onUpdate('cascade');
            $table->foreign('id_penilai', 'fk_nilai_essay_penilai')
                ->references('id_user')
                ->on('tm_user')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tr_nilai_essay');
    }
};
