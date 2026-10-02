<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SesiKuis extends Model
{
    protected $table = 'sesi_kuis';

    protected $primaryKey = 'id_sesi';
    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_kuis',
        'id_user',
        'percobaan_ke',
        'waktu_mulai',
        'waktu_selesai',
    ];

    protected $casts = [
        'percobaan_ke' => 'integer',
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
    ];

    public function kuis()
    {
        return $this->belongsTo(Kuis::class, 'id_kuis','id_kuis');
    }

    public function siswa()
    {
        return $this->belongsTo(User::class,'id_user','id_user');
    }
}
