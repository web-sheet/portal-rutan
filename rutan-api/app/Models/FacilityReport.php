<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilityReport extends Model
{
    protected $fillable = [
        'reporter_id',
        'location_id',
        'facility_type_id',
        'description',
        'photo_before',
        'status',
        'repaired_at',
        'handler_id',
        'budget_source',
        'cost',
        'photo_after',
        'repair_notes',
        'handled_at',
    ];

    protected $casts = [
        'cost' => 'float',
        'handled_at' => 'datetime',
        'repaired_at' => 'datetime',
    ];

    // Pelapor
    // Ubah fungsi relasinya ke Model Pegawai
    public function reporter()
    {
        return $this->belongsTo(Pegawai::class, 'reporter_id');
    }

    // Kaur Perlengkapan yang menangani
    public function handler()
    {
        return $this->belongsTo(User::class, 'handler_id');
    }

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function facilityType()
    {
        return $this->belongsTo(FacilityType::class);
    }
}
