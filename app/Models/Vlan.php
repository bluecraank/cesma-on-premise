<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'vid',
        'description',
        'location_id',
        'ip_range',
        'scan',
        'sync',
    ];
}
