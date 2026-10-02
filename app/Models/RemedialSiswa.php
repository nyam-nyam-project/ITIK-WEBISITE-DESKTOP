<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RemedialSiswa extends Model
{
    protected $table = 'remedial_siswa';

    protected $primaryKey = 'id_remedial_siswa';

    public $incrementing = true;

    protected $keyType = 'int';

    public $timestamps = false;

    protected $fillable = [
        'id_kuis_remedial',
        'id_mengerjakan_asal',
        'id_guru',
        'tanggal_ditugaskan',
        'status',
    ];

    protected $casts = [
        'tanggal_ditugaskan' => 'date',
    ];

    /*
     * Kuis remedial yang ditugaskan.
     */
    public function kuisRemedial()
    {
        return $this->belongsTo(Kuis::class,'id_kuis_remedial','id_kuis');
    }

    /*
     * Data pengerjaan asal yang menyebabkan siswa
     * mendapatkan remedial.
     */
    public function mengerjakanAsal()
    {
        return $this->belongsTo(Mengerjakan::class,'id_mengerjakan_asal','id_mengerjakan'
        );
    }

    /*
     * Guru yang memberikan remedial.
     */
    public function guru()
    {
        return $this->belongsTo(User::class,'id_guru','id_user');
    }
}
