<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MacTypeIcon extends Model
{
    protected $fillable = [
        'mac_type_id',
        'mac_icon',
    ];

    public function mac_type()
    {
        return $this->belongsTo(MacType::class);
    }
}
