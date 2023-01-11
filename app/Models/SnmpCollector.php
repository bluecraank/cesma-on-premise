<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SnmpCollector extends Model
{
    use HasFactory;

    protected $fillable = [
        'mac_address',
        'description',
        'hostname',
        'ip_address',
    ];
}
