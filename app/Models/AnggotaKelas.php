<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnggotaKelas extends Model
{
    protected $table = 'anggota_kelas';

    protected $primaryKey = 'id_anggota_kelas';

    public $timestamps = false;

    protected $fillable = [
        'id_kelas',
        'id_user',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'id_kelas', 'id_kelas');
    }

    public function siswa()
    {
        return $this->belongsTo(User::class, 'id_user', 'id_user');
    }
}
