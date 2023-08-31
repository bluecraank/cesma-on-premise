<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use SoftDeletes;

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

    protected $appends = [
        'type_icon'
    ];

    public function getTypeIconAttribute()
    {
        $type = $this->type;
        $icon = MacTypeIcon::where('mac_type', $type)->first();

        if(!$icon) {
            return 'mdi-desktop-classic';
        }

        return $icon->mac_icon;
    }
}
