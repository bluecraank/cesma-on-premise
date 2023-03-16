<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VlanTemplate extends Model
{

    protected $fillable = [
        'name',
        'vlans',
        'type'
    ];
}
