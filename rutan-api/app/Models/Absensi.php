<?php

namespace App\Models;

use App\Models\Pegawai;
use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $fillable = [
        'pegawai_id',
        'tanggal',
        'status',
    ];

    // public function pegawai()
    // {
    //     return $this->belongsTo(Pegawai::class);
    // }

    public function pegawai()
    {
        return $this->belongsTo(
            Pegawai::class,
            'pegawai_id',
            'id'
        );
    }
}
