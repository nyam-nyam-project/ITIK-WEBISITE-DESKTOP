<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DraftJawaban extends Model
{
    protected $table = 'draft_jawaban';

    protected $primaryKey = 'id_draft';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_kuis',
        'id_user',
        'id_soal',
        'jawaban_dipilih',
        'updated_at',
    ];

    protected $casts = [
        'updated_at' => 'datetime',
    ];

    public function kuis()
    {
        return $this->belongsTo(Kuis::class,'id_kuis','id_kuis');
    }

    public function soal()
    {
        return $this->belongsTo(Soal::class,'id_soal','id_soal');
    }

    public function siswa()
    {
        return $this->belongsTo(User::class,'id_user','id_user');
    }
}
