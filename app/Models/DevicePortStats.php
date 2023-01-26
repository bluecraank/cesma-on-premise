<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevicePortStats extends Model
{
    protected $fillable = [
        'device_port_id',
        'port_speed',
        'port_rx_bps',
        'port_tx_bps',
        'port_rx_pps',
        'port_tx_pps',
        'port_rx_bytes',
        'port_tx_bytes',
        'port_rx_packets',
        'port_tx_packets',
        'port_rx_errors',
        'port_tx_errors',
    ];


    public function devicePort()
    {
        return $this->belongsTo(DevicePort::class);
    }
}
