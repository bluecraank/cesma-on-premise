<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    public $incrementing = false;
    protected $primaryKey = 'id';

    protected $fillable = [
        'switch_id',
        'vlan_id',
        'port_id',
        'mac_address',
        'ip_address',
        'hostname',
        'id',
        'type',
        'created_at',
        'updated_at',
        'online'
    ];
}
