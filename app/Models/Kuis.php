<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Kuis extends Model
{
    protected $table = 'tm_kuis';

    protected $primaryKey = 'id_kuis';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    public const PILIHAN_TAMPIL_JAWABAN = ['tidak', 'setelah_submit', 'setelah_kkm'];

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
        'diarsipkan_bersama',
        'tampil_jawaban',
        'izinkan_perulangan',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'is_aktif' => 'boolean',
        'izinkan_perulangan' => 'boolean',
        'kkm' => 'decimal:2',
    ];

    /**
     * Format serialisasi timestamp API konsisten dalam WIB (Asia/Jakarta)
     * agar tidak bergeser ke UTC di sisi klien.
     */
    protected function serializeDate(\DateTimeInterface $date): string
    {
        return Carbon::instance($date)->timezone('Asia/Jakarta')->format('Y-m-d H:i:s');
    }

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

    public function mengerjakan()
    {
        return $this->hasMany(Mengerjakan::class, 'id_kuis', 'id_kuis');
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

    public function kelas()
    {
        return $this->belongsToMany(
            Kelas::class,
            'tp_kuis_kelas',
            'id_kuis',
            'id_kelas',
            'id_kuis',
            'id_kelas'
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

    /**
     * Menentukan apakah kuis mengizinkan siswa mengerjakan ulang (percobaan > 1).
     */
    public function bolehDiulang(): bool
    {
        return (bool) ($this->izinkan_perulangan ?? true);
    }

    /**
     * Menentukan apakah user berhak menghapus atau mengarsipkan kuis ini.
     * Diizinkan untuk admin, guru pembuat kuis, atau guru dengan penugasan
     * mengajar pada mata pelajaran dan kelas yang sama di tahun ajaran aktif.
     */
    public function dapatDihapusOleh(User $user): bool
    {
        if ($user->role !== 'guru') {
            return true;
        }

        if ($this->id_user === $user->id_user) {
            return true;
        }

        if (! Schema::hasTable('tr_pengajaran') || ! Schema::hasTable('tp_kuis_kelas')) {
            return false;
        }

        $kuisKelasIds = $this->relationLoaded('kelas')
            ? $this->kelas->pluck('id_kelas')
            : DB::table('tp_kuis_kelas')
                ->where('id_kuis', $this->id_kuis)
                ->pluck('id_kelas');

        if ($kuisKelasIds->isEmpty()) {
            return false;
        }

        $tahunAktif = TahunAjaran::aktif();

        $guruKelasQuery = Pengajaran::query()
            ->where('tr_pengajaran.id_user', $user->id_user)
            ->where('tr_pengajaran.id_mapel', $this->id_mapel)
            ->whereIn('tr_pengajaran.id_kelas', $kuisKelasIds);

        if ($tahunAktif && Schema::hasTable('tm_kelas')) {
            $guruKelasQuery->join('tm_kelas', 'tr_pengajaran.id_kelas', '=', 'tm_kelas.id_kelas')
                ->where('tm_kelas.id_tahun_ajaran', $tahunAktif->id_tahun_ajaran)
                ->where('tm_kelas.is_aktif', true);
        }

        return $guruKelasQuery->exists();
    }

    /**
     * Scope kuis yang dapat dikelola oleh guru di Kotak Arsip:
     * buatan guru sendiri atau memiliki kecocokan mapel dan kelas ajar aktif.
     */
    public function scopeDapatDikelolaGuru(Builder $query, User $guru): Builder
    {
        $tahunAktif = TahunAjaran::aktif();
        $hasPengajaran = Schema::hasTable('tr_pengajaran');
        $hasKuisKelas = Schema::hasTable('tp_kuis_kelas');

        return $query->where(function (Builder $sub) use ($guru, $tahunAktif, $hasPengajaran, $hasKuisKelas): void {
            $sub->where('tm_kuis.id_user', $guru->id_user);

            if ($hasPengajaran && $hasKuisKelas) {
                $sub->orWhereExists(function ($exists) use ($guru, $tahunAktif): void {
                    $exists->select(DB::raw(1))
                        ->from('tp_kuis_kelas')
                        ->join('tr_pengajaran', function ($join) use ($guru): void {
                            $join->on('tr_pengajaran.id_kelas', '=', 'tp_kuis_kelas.id_kelas')
                                ->where('tr_pengajaran.id_user', '=', $guru->id_user);
                        })
                        ->whereColumn('tp_kuis_kelas.id_kuis', 'tm_kuis.id_kuis')
                        ->whereColumn('tr_pengajaran.id_mapel', 'tm_kuis.id_mapel');

                    if ($tahunAktif && Schema::hasTable('tm_kelas')) {
                        $exists->join('tm_kelas', 'tr_pengajaran.id_kelas', '=', 'tm_kelas.id_kelas')
                            ->where('tm_kelas.id_tahun_ajaran', $tahunAktif->id_tahun_ajaran)
                            ->where('tm_kelas.is_aktif', true);
                    }
                });
            }
        });
    }
}
