<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
    protected $table = 'soals';

    protected $primaryKey = 'id_soal';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id_soal',
        'id_kuis',
        'tipe_soal',
        'pertanyaan',
        'gambar_pertanyaan',
        'opsi_a',
        'gambar_opsi_a',
        'opsi_b',
        'gambar_opsi_b',
        'opsi_c',
        'gambar_opsi_c',
        'opsi_d',
        'gambar_opsi_d',
        'opsi_e',
        'gambar_opsi_e',
        'jawaban',
        'bobot',
        'tingkat_kesulitan',
    ];

    protected $casts = [
        'bobot' => 'decimal:2',
    ];

    public function kuis()
    {
        return $this->belongsTo(
            Kuis::class,
            'id_kuis',
            'id_kuis'
        );
    }

    public function jawabanSiswa()
    {
        return $this->hasMany(
            JawabanSiswa::class,
            'id_soal',
            'id_soal'
        );
    }
}
