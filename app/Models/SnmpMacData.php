<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SnmpMacData extends Model
{
    protected $fillable = [
        'mac_address',
        'ip_address',
        'router',
    ];
}
