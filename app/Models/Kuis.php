<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Kuis extends Model
{
    protected $table = 'tm_kuis';

    protected $primaryKey = 'id_kuis';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id_kuis',
        'judul',
        'deskripsi',
        'kategori',
        'tingkat_kesulitan',
        'id_mapel',
        'id_tahun_ajaran',
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

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'id_mapel', 'id_mapel');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'id_tahun_ajaran', 'id_tahun_ajaran');
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
            'tp_materi_kuis',
            'id_kuis',
            'id_materi',
            'id_kuis',
            'id_materi'
        );
    }

    public function sesi()
    {
        return $this->hasMany(SesiKuis::class, 'id_kuis', 'id_kuis');
    }

    public function remedial()
    {
        return $this->hasOne(Remedial::class, 'id_kuis_remedial', 'id_kuis');
    }

    public function remedialSebagaiAsal()
    {
        return $this->hasMany(Remedial::class, 'id_kuis_asal', 'id_kuis');
    }

    public function remedialSiswa()
    {
        return $this->hasMany(RemedialSiswa::class, 'id_kuis_remedial', 'id_kuis');
    }

    /**
     * Sinkronisasi status publikasi kuis secara otomatis:
     * - Terbit otomatis jika waktu_mulai sudah tiba (<= now)
     * - Kembali ke draft otomatis jika waktu_selesai sudah lewat (<= now)
     */
    public static function sinkronisasiStatus(?Carbon $now = null): void
    {
        $now = $now ?? Carbon::now();

        // 1. Auto terbit: draft, waktu_mulai <= now, dan (waktu_selesai is null atau waktu_selesai > now)
        static::where('status_publikasi', 'draft')
            ->whereNotNull('waktu_mulai')
            ->where('waktu_mulai', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->whereNull('waktu_selesai')
                    ->orWhere('waktu_selesai', '>', $now);
            })
            ->where('is_aktif', true)
            ->update([
                'status_publikasi' => 'terbit',
            ]);

        // 2. Auto draft: terbit, waktu_selesai <= now
        static::where('status_publikasi', 'terbit')
            ->whereNotNull('waktu_selesai')
            ->where('waktu_selesai', '<=', $now)
            ->where('is_aktif', true)
            ->update([
                'status_publikasi' => 'draft',
                'id_publisher' => null,
            ]);
    }
}
