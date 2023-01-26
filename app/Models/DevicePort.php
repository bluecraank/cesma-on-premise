<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevicePort extends Model
{
    protected $fillable = [
        'device_id',
        'name',
        'link',
        'speed'
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function deviceVlanPorts()
    {
        return $this->hasMany(DeviceVlanPort::class);
    }

    public function deviceUplink()
    {
        return $this->hasOne(DeviceUplink::class);
    }

    public function devicePortStats()
    {
        return $this->hasMany(DevicePortStats::class);
    }
}
