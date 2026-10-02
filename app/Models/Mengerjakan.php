<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mengerjakan extends Model
{
    protected $table = 'mengerjakan';

    protected $primaryKey = 'id_mengerjakan';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_kuis',
        'id_user',
        'nilai',
        'tanggal',
        'percobaan_ke',
        'status_pengerjaan',
        'status_validasi',
        'id_validator',
    ];

    protected $casts = [
        'nilai' => 'decimal:2',
        'tanggal' => 'date',
    ];

    public function kuis()
    {
        return $this->belongsTo(Kuis::class,'id_kuis','id_kuis');
    }

    public function siswa()
    {
        return $this->belongsTo(User::class,'id_user','id_user');
    }

    public function validator()
    {
        return $this->belongsTo(User::class,'id_validator','id_user');
    }

    public function remedialSiswa()
    {
        return $this->hasMany(RemedialSiswa::class,'id_mengerjakan_asal','id_mengerjakan');
    }
}
