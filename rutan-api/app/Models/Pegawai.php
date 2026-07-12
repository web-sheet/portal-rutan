<?php

namespace App\Models;

use App\Models\Absensi;
use Illuminate\Database\Eloquent\Model;

class Pegawai extends Model
{
    protected $fillable = [
        'nama',
        'nip',
        'jabatan',
        'pangkat',
        'golongan',
        'status',
        'ttd'
    ];

  

    public function absensi()
    {
        return $this->hasMany(
            Absensi::class,
            'pegawai_id',
            'id'
        );
    }
}
