<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UplinkClient extends Model
{
    protected $fillable = [
        'hostname',
        'ip_address',
        'mac_address',
        'port_id',
        'vlan_id',
        'switch_id',
    ];
}
