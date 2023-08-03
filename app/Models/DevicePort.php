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
        'vlan_mode',
        'snmp_if_index'
    ];

    protected $appends = [
        'untagged',
        'tagged',
        'taggedId'
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
    
    public function getUntaggedAttribute() {
        $id = $this->deviceVlanPorts->where('is_tagged', false)->first()->device_vlan_id ?? null;
        return DeviceVlan::where('id', $id)->first() ?? null;
    }
    
    public function getTaggedAttribute() {
        $ids = $this->deviceVlanPorts->where('is_tagged', true)->all() ?? null;
        $vlans = [];
        foreach($ids as $id) {
            $vlans[] = DeviceVlan::where('id', $id->device_vlan_id)->first() ?? null;
        }
        
        return $vlans;
    }
    
    public function getTaggedIdAttribute() {
        $ids = $this->deviceVlanPorts->where('is_tagged', true)->all() ?? null;
        $vlans = [];
        foreach($ids as $id) {
            $vlans[] = DeviceVlan::where('id', $id->device_vlan_id)->first()->id ?? null;
        }
        
        return $vlans;
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
        
        public function taggedVlanNames() {
            $ids = $this->deviceVlanPorts->where('is_tagged', true)->all() ?? null;
            $vlans = [];
            foreach($ids as $id) {
                $vlans[] = DeviceVlan::where('id', $id->device_vlan_id)->first()->name ?? null;
            }
            return $vlans;
        }
        
        public function isMemberOfTrunk() {
            $uplinks = DeviceUplink::where('device_id', $this->device_id)->get();
            foreach($uplinks as $uplink) {
                $ports = $uplink->ports ?? [];
                if(in_array($this->name, $ports)) {
                    return $uplink;
                }
            }
        }
        
        public function isUplink() {
            $uplinks = DeviceUplink::where('device_id', $this->device_id)->get();
            foreach($uplinks as $uplink) {
                if($this->name == $uplink->name) {
                    return true;
                }
            }
            return false;
        }

        public function getChildrenOfUplink()
        {
            $uplink = DeviceUplink::where('device_id', $this->device_id)->where('name', $this->name)->first();
            if(!$uplink || !isset($uplink->ports[0])) return null;
            $firstPort = $uplink->ports[0];
            return DevicePort::where('device_id', $this->device_id)->where('name', $firstPort)->first();
        }
        
        public function stats() {
            return $this->devicePortStats()->first();
        }
}
