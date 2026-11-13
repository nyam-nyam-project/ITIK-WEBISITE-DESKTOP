<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class Pengajaran extends Model
{
    protected $table = 'tr_pengajaran';

    protected $primaryKey = 'id_pengajaran';

    public $timestamps = false;

    protected $fillable = [
        'id_user',
        'id_kelas',
        'id_mapel',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public function guru()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'id_mapel', 'id_mapel');
    }

    /**
     * Mengembalikan id_mapel unik dari pengajaran guru,
     * dengan join kelas dan filter id_tahun_ajaran tahun aktif.
     */
    public static function mapelUntukGuru(User $guru): Collection
    {
        if (Schema::hasTable('tr_pengajaran') === false) {
            return collect();
        }

        $tahunAktif = TahunAjaran::aktif();

        if (! $tahunAktif || Schema::hasTable('tm_kelas') === false) {
            return static::where('id_user', $guru->id_user)
                ->distinct()
                ->pluck('id_mapel');
        }

        return static::join('tm_kelas', 'tr_pengajaran.id_kelas', '=', 'tm_kelas.id_kelas')
            ->where('tr_pengajaran.id_user', $guru->id_user)
            ->where('tm_kelas.id_tahun_ajaran', $tahunAktif->id_tahun_ajaran)
            ->distinct()
            ->pluck('tr_pengajaran.id_mapel');
    }
}
