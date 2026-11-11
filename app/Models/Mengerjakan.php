<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

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


    public function dikelolaOleh(User $guru): bool
    {
        return static::query()
            ->whereKey($this->getKey())
            ->dikelolaOlehGuru($guru)
            ->exists();
    }

    public function scopeDikelolaOlehGuru(Builder $query, User $guru): Builder
    {
        return $query->where(function (Builder $query) use ($guru) {
            $query->whereHas('kuis', fn (Builder $kuis) => $kuis->where('id_user', $guru->id_user))
                ->orWhereExists(function ($subQuery) use ($guru) {
                    $subQuery->selectRaw('1')
                        ->from('pengajaran')
                        ->join('kuis as kuis_dikelola', 'kuis_dikelola.id_mapel', '=', 'pengajaran.id_mapel')
                        ->whereColumn('kuis_dikelola.id_kuis', 'mengerjakan.id_kuis')
                        ->where('pengajaran.id_user', $guru->id_user)
                        ->where(function ($assignment) {
                            $assignment->whereNull('pengajaran.id_kelas')
                                ->orWhereExists(function ($classMembership) {
                                    $classMembership->selectRaw('1')
                                        ->from('anggota_kelas')
                                        ->whereColumn('anggota_kelas.id_kelas', 'pengajaran.id_kelas')
                                        ->whereColumn('anggota_kelas.id_user', 'mengerjakan.id_user');
                                });
                        });
                });
        });
    }
}