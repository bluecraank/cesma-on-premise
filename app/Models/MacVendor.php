<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class MacVendor extends Model
{
    protected $fillable = [
        'mac_prefix',
        'vendor_name',
    ];
}
