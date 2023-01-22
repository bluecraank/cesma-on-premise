<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MacTypeIcon extends Model
{
    protected $fillable = [
        'mac_type',
        'mac_icon',
    ];
}
