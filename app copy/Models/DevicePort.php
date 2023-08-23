<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class DevicePort extends Model
{
    protected $fillable = [
        'device_id',
        'name',
        'description',
        'link',
        'speed',
        'vlan_mode',
        'snmp_if_index'
    ];

    protected $appends = [
        'untagged',
        'tagged',
        'speedAsTag',
        'memberOfTrunk',
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
        return $this->hasOne(DeviceUplink::class);
    }


    public function devicePortStats()
    {
        return $this->hasMany(DevicePortStat::class);
    }

    public function getUntaggedAttribute()
    {
        $id = $this->deviceVlanPorts->where('is_tagged', false)->first()->device_vlan_id ?? null;
        return DeviceVlan::where('id', $id)->first() ?? null;
    }

    public function getTaggedAttribute()
    {
        $vlans = $this->deviceVlanPorts->where('is_tagged', true) ?? new Collection();
        $vlans = $vlans->map->only('device_vlan_id');
        return $vlans;
    }

    public function clients() {
        return Client::where('port_id', $this->name)->where('device_id', $this->device_id)->get();
    }

    public function clients_count() {
        return Client::where('port_id', $this->name)->where('device_id', $this->device_id)->count();
    }

    public function getSpeedAsTagAttribute() {
        if ($this->speed == 10) {
            $label = '10M';
            $color = 'is-danger';
        } else if ($this->speed == 100) {
            $label = '100M';
            $color = 'is-warning';
        } elseif ($this->speed == 1000) {
            $label = '1G';
            $color = 'is-success';
        } else if ($this->speed == 10000) {
            $label = '10G';
            $color = 'is-primary';
        } else {
            $label = $this->speed;
            $color = 'is-info';
        }

        return "<span style='min-width:50px;' class='has-text-centered tag $color'>$label</span>";
    }

    // public function getTaggedIdAttribute()
    // {
    //     $ids = $this->deviceVlanPorts->where('is_tagged', true)->all() ?? null;
    //     $vlans = [];
    //     foreach ($ids as $id) {
    //         $vlans[] = DeviceVlan::where('id', $id->device_vlan_id)->first()->id ?? null;
    //     }

    //     return $vlans;
    // }

    public function untaggedVlan()
    {
        return $this->deviceVlanPorts->where('is_tagged', false)->first()->device_vlan_id ?? new Collection();
    }

    public function taggedVlans() {
        return $this->deviceVlanPorts->where('is_tagged', true) ?? new Collection();
    }

    // public function untaggedVlanId()
    // {
    //     $id = $this->deviceVlanPorts->where('is_tagged', false)->first()->device_vlan_id ?? null;
    //     return DeviceVlan::where('id', $id)->first()->vlan_id ?? null;
    // }

    public function untaggedVlanName()
    {
        $id = $this->deviceVlanPorts->where('is_tagged', false)->first()->device_vlan_id ?? null;
        return DeviceVlan::where('id', $id)->first()->name ?? null;
    }

    // public function taggedVlanNames()
    // {
    //     $ids = $this->deviceVlanPorts->where('is_tagged', true)->all() ?? null;
    //     $vlans = [];
    //     foreach ($ids as $id) {
    //         $vlans[] = DeviceVlan::where('id', $id->device_vlan_id)->first()->name ?? null;
    //     }
    //     return $vlans;
    // }

    public function getMemberOfTrunkAttribute()
    {
        $uplinks = DeviceUplink::where('device_id', $this->device_id)->get();
        foreach ($uplinks as $uplink) {
            $ports = $uplink->ports ?? [];
            if (in_array($this->name, $ports)) {
                return $uplink;
            }
        }
    }

    public function isUplink()
    {
        $uplinks = DeviceUplink::where('device_id', $this->device_id)->get();
        foreach ($uplinks as $uplink) {
            if ($this->name == $uplink->name) {
                return true;
            }
        }
        return false;
    }

    public function getChildrenOfUplink()
    {
        $uplink = DeviceUplink::where('device_id', $this->device_id)->where('name', $this->name)->first();
        if (!$uplink || !isset($uplink->ports[0])) return null;
        $firstPort = $uplink->ports[0];
        return DevicePort::where('device_id', $this->device_id)->where('name', $firstPort)->first();
    }

    public function stats()
    {
        return $this->devicePortStats()->first();
    }
}
