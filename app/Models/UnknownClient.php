<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UnknownClient extends Model
{
    use HasFactory;

    protected $fillable = [
        'mac_address',
        'device_id',
        'port_id',
        'vlan_id',
        'hostname',
        'ip_address'
    ];
}
