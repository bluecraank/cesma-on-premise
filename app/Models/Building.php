<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'site_id'
    ];

    public function site()
    {
        return $this->belongsTo(Site::class);
    }

    public function rooms()
    {
        return $this->hasMany(Room::class);
    }

    public function devices()
    {
        return $this->hasManyThrough(Device::class, Room::class);
    }
}
