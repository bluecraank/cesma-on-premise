<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceApiSession extends Model
{
    protected $fillable = [
        'device_id',
        'cookie',
        'valid_until',
    ];
}
