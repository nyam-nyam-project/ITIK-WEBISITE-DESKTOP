<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengajaran extends Model
{
    protected $table = 'pengajaran';

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
}
