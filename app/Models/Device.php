<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $primaryKey = 'id';

    protected $fillable = [
        'name',
        'hostname',
        'mac_address',
        'building_id',
        'site_id',
        'location_description',
        'room_id',
        'password',
        'serialnumber',
        'firmware',
        'hardware',
        'named',
        'model',
        'type',
        'last_pubkey_sync'
    ];

    protected $dates = [
        'last_seen',
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

    public function site() {
        return $this->belongsTo(Site::class);
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

    public function vlanports() {
        return $this->hasMany(DeviceVlanPort::class);
    }

    public function getPortById($id) {
        return $this->ports()->where('id', $id)->first()->name;
    }

    public function clients() {
        return $this->hasMany(Client::class);
    }

    public function topology() {
        return Topology::whereNot('local_device', 0)->whereNot('remote_device', 0)->where(function($query) {
            $query->where('local_device', $this->id)->orWhere('remote_device', $this->id);
        });
    }

    public function active() {
        try {
            if ($fp = fsockopen($this->hostname, 22, $errCode, $errStr, 0.2)) {
                fclose($fp);
                return true;
            }
            fclose($fp);
        } catch (\Exception $e) {
        }

        return false;
    }
}
