<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Kelas extends Model
{
    protected $table = 'tm_kelas';

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
            'tp_mapel_kelas',
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
            'tp_anggota_kelas',
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

    public function pengajaran()
    {
        return $this->hasMany(Pengajaran::class, 'id_kelas', 'id_kelas');
    }

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }

    /**
     * Mengembalikan id_mapel dari tp_mapel_kelas untuk kelas siswa di tahun aktif,
     * via tp_anggota_kelas.
     */
    public static function mapelUntukSiswa(User $siswa): Collection
    {
        if (! Schema::hasTable('tp_anggota_kelas') ||
            ! Schema::hasTable('tp_mapel_kelas')) {
            return collect();
        }

        $tahunAktif = TahunAjaran::aktif();

        if (! $tahunAktif || ! Schema::hasTable('tm_kelas')) {
            return DB::table('tp_anggota_kelas')
                ->join('tp_mapel_kelas', 'tp_anggota_kelas.id_kelas', '=', 'tp_mapel_kelas.id_kelas')
                ->where('tp_anggota_kelas.id_user', $siswa->id_user)
                ->distinct()
                ->pluck('tp_mapel_kelas.id_mapel');
        }

        return DB::table('tp_anggota_kelas')
            ->join('tm_kelas', 'tp_anggota_kelas.id_kelas', '=', 'tm_kelas.id_kelas')
            ->join('tp_mapel_kelas', 'tm_kelas.id_kelas', '=', 'tp_mapel_kelas.id_kelas')
            ->where('tp_anggota_kelas.id_user', $siswa->id_user)
            ->where('tm_kelas.id_tahun_ajaran', $tahunAktif->id_tahun_ajaran)
            ->where('tm_kelas.is_aktif', true)
            ->distinct()
            ->pluck('tp_mapel_kelas.id_mapel');
    }

    /**
     * Mengembalikan id_kelas siswa di tahun aktif jika terdaftar.
     */
    public static function kelasAktifSiswa(User $siswa): ?string
    {
        if (! Schema::hasTable('tp_anggota_kelas')) {
            return null;
        }

        $tahunAktif = TahunAjaran::aktif();

        if (! $tahunAktif || ! Schema::hasTable('tm_kelas')) {
            return DB::table('tp_anggota_kelas')
                ->where('id_user', $siswa->id_user)
                ->value('id_kelas');
        }

        return DB::table('tp_anggota_kelas')
            ->join('tm_kelas', 'tp_anggota_kelas.id_kelas', '=', 'tm_kelas.id_kelas')
            ->where('tp_anggota_kelas.id_user', $siswa->id_user)
            ->where('tm_kelas.id_tahun_ajaran', $tahunAktif->id_tahun_ajaran)
            ->where('tm_kelas.is_aktif', true)
            ->value('tm_kelas.id_kelas');
    }
}
