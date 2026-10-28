<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';

    protected $primaryKey = 'id_user';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id_user',
        'nomor_induk',
        'nama_lengkap',
        'role',
        'kelas',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
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
        return $this->hasMany(Progress::class, 'id_user', 'id_user');
    }
}
