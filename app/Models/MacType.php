<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MacType extends Model
{
    protected $fillable = [
        'mac_prefix',
        'type',
        'description'
    ];
}
