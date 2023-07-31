<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topology extends Model
{
    use HasFactory;

    protected $fillable = [
        'local_device',
        'remote_device',
        'local_port',
        'remote_port',
        'remote_mac',
    ];
}
