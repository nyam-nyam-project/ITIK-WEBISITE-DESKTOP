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
        'tingkat_ujian',
        'alokasi_waktu',
        'kkm',
        'maks_percobaan',
        'waktu_mulai',
        'waktu_selesai',
        'waktu_publish',
        'id_user',
        'status_publikasi',
        'id_publisher',
        'is_aktif',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'waktu_publish' => 'datetime',
        'is_aktif' => 'boolean',
        'kkm' => 'integer',
        'maks_percobaan' => 'integer',
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

    public function getKategoriLabelAttribute(): string
    {
        return match ($this->kategori) {
            'pre-test' => 'Pre-test',
            'post-test' => 'Post-test',
            'kuis harian' => 'Kuis Harian',
            'ulangan' => 'Ulangan',
            'remedial' => 'Remedial',
            default => ucfirst($this->kategori),
        };
    }

    public function getTingkatUjianLabelAttribute(): string
    {
        return match ($this->tingkat_ujian) {
            'mudah' => 'Mudah',
            'sedang' => 'Sedang',
            'sulit' => 'Sulit',
            default => 'Belum ditentukan',
        };
    }

    public function getTingkatSebelumnyaLabelAttribute(): ?string
    {
        return match ($this->tingkat_ujian) {
            'sedang' => 'Mudah',
            'sulit' => 'Sedang',
            default => null,
        };
    }
}
