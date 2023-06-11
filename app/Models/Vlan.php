<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vlan extends Model
{
    protected $fillable = [
        'name',
        'vid',
        'description',
        'site_id',
        'ip_range',
        'is_client_vlan',
        'is_synced',
        'is_scanned'
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function deviceVlans()
    {
        return $this->hasMany(DeviceVlan::class);
    }
}
