<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

    public $incrementing = false;
    protected $primaryKey = 'id';

    protected $fillable = [
        'id',
        'device_id',
        'vlan_id',
        'site_id',
        'port_id',
        'mac_address',
        'ip_address',
        'hostname',
        'type',
        'online'
    ];
}
