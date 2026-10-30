<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'user';

    protected $primaryKey = 'id_user';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_user',
        'nomor_induk',
        'nama_lengkap',
        'role',
        'status',
        'kelas',
        'email',
        'password',
        'wajib_ganti_password',
        'dibuat_oleh',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'wajib_ganti_password' => 'boolean',
        ];
    }

    /*
    RELASI DENGAN USER
    */

    public function materi()
    {
        return $this->hasMany(Materi::class, 'id_user', 'id_user');
    }

    public function kuis()
    {
        return $this->hasMany(Kuis::class, 'id_user', 'id_user');
    }

    public function progressMateri()
    {
        return $this->hasMany(Progress::class,'id_user','id_user');
    }

    public function kelasDiikuti()
    {
        return $this->belongsToMany(
            Kelas::class,
            'anggota_kelas',
            'id_user',
            'id_kelas',
            'id_user',
            'id_kelas'
        );
    }

    public function pembuatAkun()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh', 'id_user');
    }

    public function isAktif(): bool
    {
        return $this->status === 'aktif';
    }

    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif');
    }

    public function scopeSiswa($query)
    {
        return $query->where('role', 'siswa');
    }

    public function scopeGuru($query)
    {
        return $query->where('role', 'guru');
    }
}
