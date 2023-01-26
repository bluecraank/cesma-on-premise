<?php

namespace App\Services;

use App\Models\Device;
use App\Devices\ArubaOS;
use App\Devices\ArubaCX;
use App\Models\DevicePortStats;
use Illuminate\Support\Facades\Crypt;

class DeviceService
{
    static $types = [
        'aruba-os' => ArubaOS::class,
        'aruba-cx' => ArubaCX::class,
    ];

    static function getApiData(Device $device) {
        $api_data = self::$types[$device->type]::API_REQUEST_ALL_DATA($device);
        if($api_data['success']) {
            self::storeApiData($api_data, $device);
        }
    }

    static function storeApiData($data, $device)
    {
        foreach ($data['vlans'] as $vid => $vname) {
            $device->vlans()->where('vlan_id', $vid)->updateOrCreate([
                'vlan_id' => $vid, 
                'name' => $vname, 
                'device_id' => $device->id
            ]);
        }

        foreach ($data['ports'] as $port) {
            $device->ports()->where('name', $port['id'])->updateOrCreate([
                'name' => $port['id'], 
                'link' => $port['link'], 
                'speed' => $port['speed'] ?? 0, 
                'device_id' => $device->id
            ]);
        }

        foreach ($data['uplinks'] as $port => $uplink) {
            $device->uplinks()->where('name', $uplink)->updateOrCreate([
                'name' => $uplink, 
                'device_port_id' => $device->ports()->where('name', $port)->first()->id, 
                'device_id' => $device->id
            ]);
        }

        foreach($data['vlanports'] as $vlanport) {
            $device->vlanports()->where('device_port_id', $device->ports()->where('name', $vlanport['port_id'])->first()->id)->updateOrCreate([
                'device_port_id' => $device->ports()->where('name', $vlanport['port_id'])->first()->id, 
                'device_vlan_id' => $device->vlans()->where('vlan_id', $vlanport['vlan_id'])->first()->id, 
                'device_id' => $device->id,
                'is_tagged' => $vlanport['is_tagged']
            ]);
        }

        // $table->unsignedBigInteger('port_speed')->nullable();
        // $table->unsignedDouble('port_rx_bps')->nullable();
        // $table->unsignedDouble('port_tx_bps')->nullable();
        // $table->unsignedDouble('port_rx_pps')->nullable();
        // $table->unsignedDouble('port_tx_pps')->nullable();
        // $table->unsignedBigInteger('port_rx_bytes')->nullable();
        // $table->unsignedBigInteger('port_tx_bytes')->nullable();
        // $table->unsignedBigInteger('port_rx_packets')->nullable();
        // $table->unsignedBigInteger('port_tx_packets')->nullable();
        // $table->unsignedBigInteger('port_rx_errors')->nullable();
        // $table->unsignedBigInteger('port_tx_errors')->nullable();

        foreach($data['statistics'] as $statistic) {
            DevicePortStats::create([
                'device_port_id' => $device->ports()->where('name', $statistic['id'])->first()->id, 
                'port_speed' => $statistic['port_speed_mbps'] ?? 0,
                'port_rx_bps' => $statistic['port_rx_bps'] ?? 0,
                'port_tx_bps' => $statistic['port_tx_bps'] ?? 0,
                'port_rx_pps' => $statistic['port_rx_pps'] ?? 0,
                'port_tx_pps' => $statistic['port_tx_pps'] ?? 0,
                'port_rx_bytes' => $statistic['port_rx_bytes'] ?? 0,
                'port_tx_bytes' => $statistic['port_tx_bytes'] ?? 0,
                'port_rx_packets' => $statistic['port_rx_packets'] ?? 0,
                'port_tx_packets' => $statistic['port_tx_packets'] ?? 0,
                'port_rx_errors' => $statistic['port_rx_errors'] ?? 0,
                'port_tx_errors' => $statistic['port_tx_errors'] ?? 0
            ]);
        }

        dd($data['vlans'], $data['ports'], $data['uplinks'], $data['vlanports']);
    }

    static function storeDevice($request) {

        // Get hostname from device else use ip
        $hostname = $request->input('hostname');
        if (filter_var($request->input('hostname'), FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $hostname = gethostbyaddr($request->input('hostname')) or $request->input('hostname');
        }
        $request->merge(['hostname' => $hostname]);

        // Encrypt password
        $request->merge(['password' => Crypt::encrypt($request->password)]);
            
        // Create device
        // dd($request->all());
        $device = Device::create($request->except('_token'));

        if ($device) {
            return $device;
        }

        return false;
    }

    static function newDataView(Device $device) {
        
        dd($device->uplinks, $device->ports, $device->vlans);
    }
}
