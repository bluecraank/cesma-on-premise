<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MacTypeFilter extends Model
{
    protected $fillable = [
        'mac_prefix',
        'mac_type',
        'mac_desc'
    ];
}
