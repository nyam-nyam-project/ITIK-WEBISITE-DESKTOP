<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kuis extends Model
{
    protected $table = 'kuis';

    protected $primaryKey = 'id_kuis';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id_kuis',
        'judul',
        'deskripsi',
        'kategori',
        'alokasi_waktu',
        'kkm',
        'waktu_mulai',
        'waktu_selesai',
        'id_user',
        'status_publikasi',
        'id_publisher',
        'is_aktif',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'is_aktif' => 'boolean',
        'kkm' => 'decimal:2',
    ];

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function publisher()
    {
        return $this->belongsTo(User::class, 'id_publisher', 'id_user');
    }

    public function soal()
    {
        return $this->hasMany(Soal::class, 'id_kuis', 'id_kuis');
    }

    public function materi()
    {
        return $this->belongsToMany(Materi::class,
            'materi_kuis',
            'id_kuis',
            'id_materi',
            'id_kuis',
            'id_materi'
        );
    }

    public function sesi()
    {
        return $this->hasMany(SesiKuis::class,'id_kuis','id_kuis');
    }

    public function remedial()
    {
        return $this->hasOne(Remedial::class,'id_kuis_remedial','id_kuis');
    }

    public function remedialSebagaiAsal()
    {
        return $this->hasMany(Remedial::class,'id_kuis_asal','id_kuis');
    }

    public function remedialSiswa()
    {
        return $this->hasMany(RemedialSiswa::class,'id_kuis_remedial','id_kuis');
    }
}
