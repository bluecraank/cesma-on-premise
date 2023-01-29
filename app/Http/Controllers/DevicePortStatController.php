<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DevicePortStat;
use Illuminate\Support\Carbon;

class DevicePortStatController extends Controller
{
    public function index(Device $device, $port_id = null)
    {
        $port = $device->ports()->where('name', $port_id)->first();
        
        if($port_id == null || !$port->stats()) {
            abort(404, "Port $port_id not found.");
        }
        
        // dd($port);
        $port_stats = DevicePortStat::where('device_port_id', $port->id)->where('created_at', '>', 
        Carbon::now()->subHours(3)->toDateTimeString())->get();

        $ports = $device->ports()->get();

        if(empty($port_stats)) {
            abort(404, "No data for port $port_id found.");
        }

        $dataset = $this->getBpsData($port_stats);
        $dataset3 = $this->getBytesData($port_stats);
        $dataset2 = $this->getPacketsData($port_stats);
        
        $utilization_rx = $port_stats[0] && $port_stats[0]->port_rx_bps != 0 ? number_format(($port_stats[0]->port_rx_bps*8/1024/1024) / $port_stats[0]->port_speed * 100, 2) : 0;
        $utilization_tx = $port_stats[0] && $port_stats[0]->port_tx_bps != 0 ? number_format(($port_stats[0]->port_tx_bps*8/1024/1024) / $port_stats[0]->port_speed * 100, 2) : 0;
        $speed = $port_stats[0] ? $port_stats[0]->port_speed / 10 : 0;

        return view('switch.view_portstats', compact('device', 'dataset', 'port_stats', 'port', 'ports', 'port_id', 'utilization_rx', 'utilization_tx', 'speed', 'dataset2', 'dataset3'));
    }

    public function getPacketsData($ports) {
        $chartRX = $chartTX = $port_data = [];

        foreach($ports as $port) {
            $chartRX[] = [
                'x' => $port->created_at->format('H:i'),
                'y' => $port->port_rx_packets,
            ];
            $chartTX[] = [
                'x' => $port->created_at->format('H:i'),
                'y' => $port->port_tx_packets,
            ];
        }

        $port_data[] = ['label' => 'Empfangen', 'data' => $chartRX];
        $port_data[] = ['label' => 'Gesendet', 'data' => $chartTX];

        $dataset = [
            'datasets' => $port_data,
        ];

        $dataset = json_encode($dataset);

        return $dataset;
    }

    public function getBytesData($ports) {
        $chartRX = $chartTX = $port_data = [];

        foreach($ports as $port) {
            $chartRX[] = [
                'x' => $port->created_at->format('H:i'),
                'y' => (double) ($port->port_rx_bytes / 1000 / 1000 * 8),
            ];
            $chartTX[] = [
                'x' => $port->created_at->format('H:i'),
                'y' => (double) ($port->port_tx_bytes / 1000 / 1000 * 8),
            ];
        }

        $port_data[] = ['label' => 'Empfangen', 'data' => $chartRX];
        $port_data[] = ['label' => 'Gesendet', 'data' => $chartTX];

        $dataset = [
            'datasets' => $port_data,
        ];

        $dataset = json_encode($dataset);

        return $dataset;      
    }

    public function getBpsData($ports) {
        $chartRX = $chartTX = $port_data = [];

        foreach($ports as $port) {
            $chartRX[] = [
                'x' => $port->created_at->format('H:i'),
                'y' => (double) ($port->port_rx_bps / 1000 / 1000 * 8),
            ];
            $chartTX[] = [
                'x' => $port->created_at->format('H:i'),
                'y' => (double) ($port->port_tx_bps / 1000 / 1000 * 8),
            ];
        }

        $port_data[] = ['label' => 'Empfangen', 'data' => $chartRX];
        $port_data[] = ['label' => 'Gesendet', 'data' => $chartTX];

        $dataset = [
            'datasets' => $port_data,
        ];

        $dataset = json_encode($dataset);

        return $dataset;       
    }
}
