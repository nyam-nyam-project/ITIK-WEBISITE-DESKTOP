<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Soal extends Model
{
    protected $table = 'soal';

    protected $primaryKey = 'id_soal';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id_soal',
        'id_kuis',
        'pertanyaan',
        'opsi_a',
        'opsi_b',
        'opsi_c',
        'opsi_d',
        'opsi_e',
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
}
