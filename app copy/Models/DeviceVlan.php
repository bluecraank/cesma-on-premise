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

    public function devicePorts() {
        return $this->hasManyThrough(DevicePort::class, DeviceVlanPort::class, 'device_vlan_id', 'id', 'id', 'device_port_id'); 
    }
}
