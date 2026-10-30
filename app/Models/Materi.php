<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Materi extends Model
{
    protected $table = 'materi';

    protected $primaryKey = 'id_materi';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_materi',
        'judul',
        'bab',
        'kelas',
        'id_mapel',
        'deskripsi',
        'isi_materi',
        'link_youtube',
        'id_user',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }

    public function mapel()
    {
        return $this->belongsTo(Mapel::class, 'id_mapel', 'id_mapel');
    }

    public function kuis()
    {
        return $this->belongsToMany(
            Kuis::class,
            'materi_kuis',
            'id_materi',
            'id_kuis',
            'id_materi',
            'id_kuis'
        );
    }

    public function progress()
    {
        return $this->hasMany(Progress::class,'id_materi','id_materi');
    }

    public function pdfUrl(): ?string
    {
        if (!$this->isi_materi) {
            return null;
        }

        if (Storage::disk('public')->exists($this->isi_materi)) {
            return Storage::disk('public')->url($this->isi_materi);
        }

        if (file_exists(public_path($this->isi_materi))) {
            return asset($this->isi_materi);
        }

        return null;
    }
}
