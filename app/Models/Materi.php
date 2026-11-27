<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

class Materi extends Model
{
    protected $table = 'tm_materi';

    protected $primaryKey = 'id_materi';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_materi',
        'judul',
        'bab',
        'id_mapel',
        'deskripsi',
        'isi_materi',
        'link_youtube',
        'id_user',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
    ];

    public function scopeAktif($query)
    {
        return $query->where('is_aktif', true);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'id_mapel', 'id_mapel');
    }

    public function kelasTerkait(): BelongsToMany
    {
        return $this->belongsToMany(
            Kelas::class,
            'tp_materi_kelas',
            'id_materi',
            'id_kelas',
            'id_materi',
            'id_kelas'
        );
    }

    public function kuis()
    {
        return $this->belongsToMany(
            Kuis::class,
            'tp_materi_kuis',
            'id_materi',
            'id_kuis',
            'id_materi',
            'id_kuis'
        );
    }

    public function progress()
    {
        return $this->hasMany(Progress::class, 'id_materi', 'id_materi');
    }

    public function pdfUrl(): ?string
    {
        if (! $this->isi_materi) {
            return null;
        }

        if (Storage::disk('public')->exists($this->isi_materi)) {
            return route('materi.pdf', $this->id_materi, false);
        }

        if (file_exists(public_path($this->isi_materi))) {
            return '/'.ltrim($this->isi_materi, '/');
        }

        return null;
    }
}
