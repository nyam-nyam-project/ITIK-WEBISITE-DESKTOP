<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NilaiEssay extends Model
{
    protected $table = 'tr_nilai_essay';

    protected $primaryKey = 'id_nilai_essay';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_mengerjakan',
        'id_soal',
        'jawaban_teks',
        'nilai',
        'catatan_guru',
        'id_penilai',
        'dinilai_at',
    ];

    protected $casts = [
        'nilai' => 'decimal:2',
        'dinilai_at' => 'datetime',
    ];

    public function mengerjakan()
    {
        return $this->belongsTo(Mengerjakan::class, 'id_mengerjakan', 'id_mengerjakan');
    }

    public function soal()
    {
        return $this->belongsTo(Soal::class, 'id_soal', 'id_soal');
    }

    public function penilai()
    {
        return $this->belongsTo(User::class, 'id_penilai', 'id_user');
    }
}
