<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mapel extends Model
{
    protected $table = 'tm_mapel';

    protected $primaryKey = 'id_mapel';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_mapel',
        'kode_mapel',
        'nama_mapel',
        'deskripsi',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    public function kelas()
    {
        return $this->belongsToMany(
            Kelas::class,
            'tp_mapel_kelas',
            'id_mapel',
            'id_kelas',
            'id_mapel',
            'id_kelas'
        );
    }

    public function kuis()
    {
        return $this->hasMany(Kuis::class, 'id_mapel', 'id_mapel');
    }

    public function materi()
    {
        return $this->hasMany(Materi::class, 'id_mapel', 'id_mapel');
    }

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }
}
