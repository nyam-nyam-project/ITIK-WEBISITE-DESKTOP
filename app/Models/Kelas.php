<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';

    protected $primaryKey = 'id_kelas';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_kelas',
        'nama_kelas',
        'tingkat',
        'id_tahun_ajaran',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    public function mapel()
    {
        return $this->belongsToMany(
            Mapel::class,
            'mapel_kelas',
            'id_kelas',
            'id_mapel',
            'id_kelas',
            'id_mapel'
        );
    }

    public function siswa()
    {
        return $this->belongsToMany(
            User::class,
            'anggota_kelas',
            'id_kelas',
            'id_user',
            'id_kelas',
            'id_user'
        );
    }

    public function anggota()
    {
        return $this->hasMany(AnggotaKelas::class, 'id_kelas', 'id_kelas');
    }

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', 1);
    }
}
