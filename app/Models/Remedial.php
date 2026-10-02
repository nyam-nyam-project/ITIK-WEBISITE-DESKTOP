<?php

namespace App\Models;

use App\Models\Kuis;
use App\Models\RemedialSiswa;
use Illuminate\Database\Eloquent\Model;

class Remedial extends Model
{
    protected $table = 'remedial';

    protected $primaryKey = 'id_remedial';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_kuis_remedial',
        'id_kuis_asal',
    ];

    /*
     * Kuis yang dibuat sebagai kuis remedial.
     */
    public function kuisRemedial()
    {
        return $this->belongsTo(Kuis::class,'id_kuis_remedial','id_kuis');
    }

    /*
     * Kuis asal yang menjadi alasan dibuatnya remedial.
     */
    public function kuisAsal()
    {
        return $this->belongsTo(Kuis::class,'id_kuis_asal','id_kuis');
    }

    /*
     * Daftar siswa yang diberi remedial.
     */
    public function remedialSiswa()
    {
        return $this->hasMany(RemedialSiswa::class,'id_kuis_remedial','id_kuis_remedial');
    }
}
