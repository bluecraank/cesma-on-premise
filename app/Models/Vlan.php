<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vlan extends Model
{

    protected $fillable = [
        'name',
        'vid',
        'description',
        'location_id',
        'ip_range',
        'is_client_vlan',
        'is_synced',
        'is_scanned'
    ];

    public function location()
    {
        return $this->belongsTo(Location::class);
    }

    public function deviceVlans()
    {
        return $this->hasMany(DeviceVlan::class);
    }
}
