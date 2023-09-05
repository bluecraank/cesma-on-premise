<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceBackup extends Model
{

    protected $fillable = [
        'device_id',
        'data',
        'restore_data',
        'status'
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }
}
