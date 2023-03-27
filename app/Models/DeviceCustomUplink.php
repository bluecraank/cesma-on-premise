<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceCustomUplink extends Model
{

    protected $fillable = [
        'device_id',
        'uplinks',
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
