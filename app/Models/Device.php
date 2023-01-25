<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'hostname',
        'building',
        'location',
        'number',
        'details',
        'password',
        'vlan_data',
        'port_statistic_data',
        'vlan_port_data',
        'system_data',
        'port_data',
        'mac_table_data',
        'type',
        'uplinks'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    public function getBuilding()
    {
        return $this->belongsTo(Building::class, 'building');
    }

    public function getLocation()
    {
        return $this->belongsTo(Location::class, 'location');
    }

    public function getVlans()
    {
        return $this->hasMany(Vlan::class, 'device_id');
    }

    public function getPortStatistics()
    {
        return $this->hasMany(PortStatistic::class, 'device_id');
    }

    public function getVlanPorts()
    {
        return $this->hasMany(VlanPort::class, 'device_id');
    }
}
