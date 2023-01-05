<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Endpoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'switch_id',
        'vlan_id',
        'port_id',
        'mac_address',
        'ip_address',
        'hostname',
    ];
}
