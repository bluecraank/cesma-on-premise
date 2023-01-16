<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MacVendors extends Model
{
    protected $table = 'mac_vendors';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $fillable = [
        'mac_prefix',
        'vendor_name',
    ];
}
