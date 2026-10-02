<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Progress extends Model
{
    protected $table = 'progress';

    protected $primaryKey = 'id_progress';

    public $incrementing = false;

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id_progress',
        'status',
        'tanggal',
        'id_materi',
        'id_user',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function materi()
    {
        return $this->belongsTo(Materi::class,'id_materi','id_materi');
    }

    public function siswa()
    {
        return $this->belongsTo(User::class,'id_user','id_user');
    }
}
