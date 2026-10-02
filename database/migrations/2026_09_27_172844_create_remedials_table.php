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
        Schema::create('remedials', function (Blueprint $table) {
            $table->integer('id_remedial')->autoIncrement();
            $table->string('id_kuis_remedial', 50)
                ->comment('Kuis dengan kategori=remedial');
            $table->string('id_kuis_asal', 50)
                ->comment('Kuis asal (misal pass-test) yang jadi alasan remedial');

            
            $table->unique('id_kuis_remedial', 'uq_kuis_remedial');
            $table->index('id_kuis_asal', 'fk_remedial_kuisasal');

            $table->foreign('id_kuis_asal', 'fk_remedial_kuisasal')
                ->references('id_kuis')
                ->on('kuis')
                ->restrictOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('id_kuis_remedial', 'fk_remedial_kuisremedial')
                ->references('id_kuis')
                ->on('kuis')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('remedials');
    }
};
