<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\DevicePortStat;
use Illuminate\Support\Carbon;

class DevicePortStatController extends Controller
{
    public function index(Device $device, $port_id = null)
    {
        $timespan = request()->get('timespan', 180);
        if($timespan == "" || $timespan == null || $timespan < 15) { 
            $timespan = 180;
        }

        $current_port = $device->ports()->where('name', $port_id)->first();

        $vlans = $device->vlans()->get()->keyBy('id')->toArray();

        if($port_id == null || !$current_port) {
            abort(404, "Port $port_id not found.");
        }

        $port_stats = DevicePortStat::where('device_port_id', $current_port->id)->where('created_at', '>', 
        Carbon::now()->subMinutes($timespan)->toDateTimeString())->get();

        $last_stat = $port_stats->last();

        $all_port_stats = DevicePortStat::where('device_port_id', $current_port->id)->where('created_at', '>', 
        Carbon::now()->subMinutes($timespan)->toDateTimeString())->get();
        $avg_utilization_rx = number_format($all_port_stats->avg('port_rx_bps')*8/1024/1024 / $port_stats[0]->port_speed * 100, 2);
        $avg_utilization_tx = number_format($all_port_stats->avg('port_tx_bps')*8/1024/1024 / $port_stats[0]->port_speed * 100, 2);

        if(empty($port_stats) or count($port_stats) == 0 or $port_stats == null or count($port_stats->toArray()) == 0) {
            abort(404, "No data for port $port_id found.");
        }

        $ports = $device->ports()->get();

        $ports = $ports->sort(function ($a, $b) {
            return strnatcmp($a['name'], $b['name']);
        });

        $dataset = $this->getBpsData($port_stats);
        $dataset3 = $this->getBytesData($port_stats);
        $dataset2 = $this->getPacketsData($port_stats);
        
        $utilization_rx = (isset($port_stats[0]) && $port_stats[0]->port_rx_bps) != 0 ? number_format(($port_stats[0]->port_rx_bps*8/1024/1024) / $port_stats[0]->port_speed * 100, 2) : 0;
        $utilization_tx = (isset($port_stats[0]) && $port_stats[0]->port_tx_bps) != 0 ? number_format(($port_stats[0]->port_tx_bps*8/1024/1024) / $port_stats[0]->port_speed * 100, 2) : 0;
        $speed = (isset($port_stats[0])) ? $port_stats[0]->port_speed / 10 : 0;

        return view('switch.view_portstats', compact('vlans', 'device', 'dataset', 'port_stats', 'last_stat', 'current_port', 'ports', 'port_id', 'utilization_rx', 'utilization_tx', 'speed', 'dataset2', 'dataset3', 'avg_utilization_rx', 'avg_utilization_tx'));
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

        $port_data[] = ['label' => __('RX'), 'data' => $chartRX];
        $port_data[] = ['label' => __('TX'), 'data' => $chartTX];

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

        $port_data[] = ['label' => __('RX'), 'data' => $chartRX];
        $port_data[] = ['label' => __('TX'), 'data' => $chartTX];

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

        $port_data[] = ['label' => __('RX'), 'data' => $chartRX];
        $port_data[] = ['label' => __('TX'), 'data' => $chartTX];

        $dataset = [
            'datasets' => $port_data,
        ];

        $dataset = json_encode($dataset);

        return $dataset;       
    }
}
