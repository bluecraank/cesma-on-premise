<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevicePort extends Model
{
    protected $fillable = [
        'device_id',
        'name',
        'description',
        'link',
        'speed'
    ];

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function deviceVlanPorts()
    {
        return $this->hasMany(DeviceVlanPort::class);
    }

    public function deviceUplink()
    {
        return $this->hasOne(DeviceUplink::class)->where('device_id', $this->device->id);
    }

    public function devicePortStats()
    {
        return $this->hasMany(DevicePortStats::class);
    }

    public function untaggedVlan() {
        return $this->deviceVlanPorts->where('is_tagged', false)->first()->device_vlan_id ?? null;
    }

    public function isMemberOfTrunk() {
        return $this->deviceUplink()->exists();
    }

    public function trunkName() {
        return $this->deviceUplink()->first()->name ?? null;
    }

    public function trunkId() {
        return $this->deviceUplink()->first()->id ?? null;
    }
}
