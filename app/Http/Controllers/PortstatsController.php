<?php

namespace App\Http\Controllers;

use App\Models\Device;
use App\Models\PortStat;

class PortstatsController extends Controller
{
    public function index($id, $port_id = 1)
    {
        $device = Device::find($id);
        $ports = json_decode($device->port_data, true);
        $port_stats = json_decode($device->port_statistic_data, true);
        $port_stats_details = PortStat::where('device_id', $id)->where('port_id', $port_id)->latest()->take(30)->get()->reverse();


        $dataset = $this->getBpsData($port_stats_details);
        $dataset3 = $this->getBytesData($port_stats_details);
        $dataset2 = $this->getPacketsData($port_stats_details);
        
        if(!isset($port_stats_details[0])) {
            abort(404, "No data for port $port_id found.");
        }

        $utilization_rx = $port_stats_details[0] && $port_stats_details[0]->port_rx_bps != 0 ? number_format(($port_stats_details[0]->port_rx_bps*8/1024/1024) / $port_stats_details[0]->port_speed * 100, 2) : 0;
        $utilization_tx = $port_stats_details[0] && $port_stats_details[0]->port_tx_bps != 0 ? number_format(($port_stats_details[0]->port_tx_bps*8/1024/1024) / $port_stats_details[0]->port_speed * 100, 2) : 0;
        $speed = $port_stats_details[0] ? $port_stats_details[0]->port_speed / 10 : 0;

        return view('switch.view_portstats', compact('device', 'dataset', 'ports', 'port_stats', 'port_id', 'utilization_rx', 'utilization_tx', 'speed', 'dataset2', 'dataset3'));
    }

    static function store($data, $device_id) {
        if(!isset($device_id) || $device_id == NULL || $device_id == 0 ) {
            return true;
        }

        foreach($data as $port) {
            PortStat::create([
                'device_id' => $device_id,
                'port_id' => $port['id'],
                'port_name' => $port['name'],
                'port_speed' => $port['port_speed_mbps'],
                'port_rx_bps' => $port['port_rx_bps'],
                'port_tx_bps' => $port['port_tx_bps'],
                'port_rx_pps' => $port['port_rx_pps'] ?? 0,
                'port_tx_pps' => $port['port_tx_pps'] ?? 0,
                'port_rx_bytes' => $port['port_rx_bytes'],
                'port_tx_bytes' => $port['port_tx_bytes'],
                'port_rx_packets' => $port['port_rx_packets'],
                'port_tx_packets' => $port['port_tx_packets'],
                'port_rx_errors' => abs($port['port_rx_errors']),
                'port_tx_errors' => abs($port['port_tx_errors']),
            ]);
        }
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
