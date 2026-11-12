<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
     * Mengembalikan id_mapel dari mapel_kelas untuk kelas siswa di tahun aktif,
     * via anggota_kelas.
     */
    public static function mapelUntukSiswa(User $siswa): \Illuminate\Support\Collection
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('anggota_kelas') ||
            ! \Illuminate\Support\Facades\Schema::hasTable('mapel_kelas')) {
            return collect();
        }

        $tahunAktif = TahunAjaran::aktif();

        if (! $tahunAktif || ! \Illuminate\Support\Facades\Schema::hasTable('kelas')) {
            return \Illuminate\Support\Facades\DB::table('anggota_kelas')
                ->join('mapel_kelas', 'anggota_kelas.id_kelas', '=', 'mapel_kelas.id_kelas')
                ->where('anggota_kelas.id_user', $siswa->id_user)
                ->distinct()
                ->pluck('mapel_kelas.id_mapel');
        }

        return \Illuminate\Support\Facades\DB::table('anggota_kelas')
            ->join('kelas', 'anggota_kelas.id_kelas', '=', 'kelas.id_kelas')
            ->join('mapel_kelas', 'kelas.id_kelas', '=', 'mapel_kelas.id_kelas')
            ->where('anggota_kelas.id_user', $siswa->id_user)
            ->where('kelas.id_tahun_ajaran', $tahunAktif->id_tahun_ajaran)
            ->where('kelas.is_aktif', true)
            ->distinct()
            ->pluck('mapel_kelas.id_mapel');
    }

    /**
     * Mengembalikan id_kelas siswa di tahun aktif jika terdaftar.
     */
    public static function kelasAktifSiswa(User $siswa): ?string
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('anggota_kelas')) {
            return null;
        }

        $tahunAktif = TahunAjaran::aktif();

        if (! $tahunAktif || ! \Illuminate\Support\Facades\Schema::hasTable('kelas')) {
            return \Illuminate\Support\Facades\DB::table('anggota_kelas')
                ->where('id_user', $siswa->id_user)
                ->value('id_kelas');
        }

        return \Illuminate\Support\Facades\DB::table('anggota_kelas')
            ->join('kelas', 'anggota_kelas.id_kelas', '=', 'kelas.id_kelas')
            ->where('anggota_kelas.id_user', $siswa->id_user)
            ->where('kelas.id_tahun_ajaran', $tahunAktif->id_tahun_ajaran)
            ->where('kelas.is_aktif', true)
            ->value('kelas.id_kelas');
    }
}
