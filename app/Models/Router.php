<?php

namespace App\Models;


use Illuminate\Database\Eloquent\Model;

class Router extends Model
{

    protected $fillable = [
        'ip',
        'desc',
        'check',
    ];

    public function entries() {
        return $this->hasMany(SnmpMacData::class, 'router', 'id');
    }
}
