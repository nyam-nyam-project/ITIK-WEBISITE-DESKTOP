<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
        'deskripsi',
        'isi_materi',
        'link_youtube',
        'id_user',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'id_user', 'id_user');
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
}
