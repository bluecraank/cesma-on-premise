<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceVlanPort extends Model
{
    protected $fillable = [
        'device_id',
        'device_vlan_id',
        'device_port_id',
        'is_tagged',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function deviceVlan()
    {
        return $this->belongsTo(DeviceVlan::class);
    }

    public function devicePort()
    {
        return $this->belongsTo(DevicePort::class);
    }
}
