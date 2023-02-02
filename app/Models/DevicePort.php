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
        'speed',
        'vlan_mode'
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
        return $this->hasMany(DevicePortStat::class);
    }

    public function untaggedVlan() {
        return $this->deviceVlanPorts->where('is_tagged', false)->first()->device_vlan_id ?? null;
    }
    
    public function untaggedVlanId() {
        $id = $this->deviceVlanPorts->where('is_tagged', false)->first()->device_vlan_id ?? null;
        return DeviceVlan::where('id', $id)->first()->vlan_id ?? null;    }

    public function untaggedVlanName() {
        $id = $this->deviceVlanPorts->where('is_tagged', false)->first()->device_vlan_id ?? null;
        return DeviceVlan::where('id', $id)->first()->name ?? null;
    }

    public function taggedVlans() {
        return $this->deviceVlanPorts->where('is_tagged', true)->where('device_port_id', $this->id);
    }

    public function isMemberOfTrunk() {
        return $this->deviceUplink()->exists();
    }

    public function trunkName() {
        return $this->deviceUplink()->first()->name ?? null;
    }

    public function trunkTaggedVlans() {
        $id = DevicePort::where('device_id', $this->device_id)->where('name', $this->trunkName())->first()->id;
        return DeviceVlanPort::where('device_port_id', $id)->where('device_id', $this->device_id)->where('is_tagged', true);
    }

    public function trunkId() {
        return $this->deviceUplink()->first()->id ?? null;
    }

    public function stats() {
        return $this->devicePortStats()->first();
    }
    
    public function clientsList() {
        return Client::where('device_id', $this->device_id)->where('port_id', $this->name)->get();
    }
}
