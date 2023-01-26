<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceUplink extends Model
{
    protected $fillable = [
        'device_id',
        'device_port_id',
        'name'
    ];


    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function devicePort()
    {
        return $this->belongsTo(DevicePort::class);
    }
}
