<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mac extends Model
{
    protected $fillable = [
        'mac_address',
        'device_id',
        'port_id',
        'vlan_id',
    ];
}
