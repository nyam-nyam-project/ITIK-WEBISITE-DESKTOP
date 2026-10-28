<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JawabanSiswa extends Model
{
    protected $table = 'jawaban_siswa';

    protected $primaryKey = 'id_jawaban';

    public $timestamps = false;

    protected $fillable = [
        'id_mengerjakan',
        'id_soal',
        'jawaban_dipilih',
        'jawaban_text',
    ];

    public function pengerjaan()
    {
        return $this->belongsTo(
            Mengerjakan::class,
            'id_mengerjakan',
            'id_mengerjakan'
        );
    }

    public function soal()
    {
        return $this->belongsTo(Soal::class, 'id_soal', 'id_soal');
    }
}
