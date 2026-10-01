<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FacilityType extends Model
{
   protected $fillable = ['name'];

    public function reports()
    {
        return $this->hasMany(FacilityReport::class);
    }
}
