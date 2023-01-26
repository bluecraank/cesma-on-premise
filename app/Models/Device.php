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
        'building_id',
        'location_id',
        'location_number',
        'location_desc',
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
}
