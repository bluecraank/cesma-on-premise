<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MacTypeIcon extends Model
{
    protected $fillable = [
        'mac_type',
        'mac_icon',
    ];

    public function prefixes()
    {
        return $this->hasMany(MacType::class, 'type', 'mac_type');
    }
}
