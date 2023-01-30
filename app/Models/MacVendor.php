<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class MacVendor extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'mac_prefix',
        'vendor_name',
    ];
}
