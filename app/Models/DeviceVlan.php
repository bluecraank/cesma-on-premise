<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceVlan extends Model
{
    protected $fillable = [
        'device_id',
        'name',
        'vlan_id'
    ];
    
    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function deviceVlanPorts()
    {
        return $this->hasMany(DeviceVlanPort::class);
    }
}
