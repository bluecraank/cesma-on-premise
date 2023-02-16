<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Room extends Model
{

    protected $fillable = [
        'name',
        'building_id',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function getBuildingName()
    {
        return $this->building->name;
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }
}
