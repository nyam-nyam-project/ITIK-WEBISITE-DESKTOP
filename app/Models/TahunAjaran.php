<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    protected $table = 'tahun_ajaran';

    protected $primaryKey = 'id_tahun_ajaran';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_tahun_ajaran',
        'nama',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_aktif',
    ];

    protected $casts = [
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
        'is_aktif' => 'boolean',
    ];

    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    public function kuis()
    {
        return $this->hasMany(Kuis::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
    }

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', 1);
    }

    public static function aktif(): ?self
    {
        return static::where('is_aktif', 1)->first();
    }
}
