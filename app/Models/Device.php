<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{

    protected $fillable = [
        'name',
        'hostname',
        'mac_address',
        'building_id',
        'location_id',
        'location_number',
        'room_id',
        'password',
        'serialnumber',
        'firmware',
        'hardware',
        'named',
        'model',
        'type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];

    public function uplinks() {
        return $this->hasMany(DeviceUplink::class);
    }

    public function ports() {
        return $this->hasMany(DevicePort::class);
    }

    public function vlans() {
        return $this->hasMany(DeviceVlan::class);
    }

    public function location() {
        return $this->belongsTo(Location::class);
    }

    public function building() {
        return $this->belongsTo(Building::class);
    } 

    public function room() {
        return $this->belongsTo(Room::class);
    }
    
    public function backups() {
        return $this->hasMany(DeviceBackup::class);
    }

    public function firmwareOrUnknown() {
        return $this->firmware ?? 'Unknown';
    }

    public function hardwareOrUnknown() {
        return $this->hardware ?? 'Unknown';
    }

    public function serialOrUnknown() {
        return $this->serial ?? 'Unknown';
    }

    public function modelOrUnknown() {
        return $this->model ?? 'Unknown';
    }

    public function vlanports() {
        return $this->hasMany(DeviceVlanPort::class);
    }

    public function portsOnline() {
        return $this->ports()->where('link', true)->get();
    }

    public function vlanPortsUntagged() {
        return $this->vlanports()->where('is_tagged', '0')->get()->keyBy('device_port_id');
    }

    public function vlanPortsTagged() {

        return $this->vlanports()->where('is_tagged', '1')->get()->groupBy('device_port_id');
    }

    public function uplinksGroupedKeyByNameArray() {
        return $this->uplinks()->get()->groupBy('name')->toArray();
    }

    public function getPortById($id) {
        return $this->ports()->where('id', $id)->first()->name;
    }

    public function deviceCustomUplinks() {
        return $this->hasOne(DeviceCustomUplink::class);
    }
}
