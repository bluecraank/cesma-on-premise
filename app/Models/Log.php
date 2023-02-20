<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $fillable = [
        'category',
        'level',
        'user',
        'user_id',
        'device_id',
        'device_name',
        'description',
        'additional_info',
    ];
}
