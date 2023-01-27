<?php

namespace App\Services;

use App\Models\Device;
use App\Devices\ArubaOS;
use App\Devices\ArubaCX;
use App\Models\Client;
use App\Models\DeviceBackup;
use App\Models\DeviceCustomUplink;
use App\Models\DevicePort;
use App\Models\DevicePortStat;
use App\Models\DeviceUplink;
use App\Models\DeviceVlan;
use App\Models\DeviceVlanPort;
use App\Models\Mac;
use Illuminate\Support\Facades\Crypt;
use App\Services\VlanService;
use Illuminate\Http\Request;

class DeviceService
{
    static $types = [
        'aruba-os' => ArubaOS::class,
        'aruba-cx' => ArubaCX::class,
    ];

    static function refreshDevice(Device $device) {
        $api_data = self::$types[$device->type]::API_REQUEST_ALL_DATA($device);
        if($api_data['success']) {
            self::storeApiData($api_data, $device);
        }
    }

    static function storeApiData($data, $device)
    {
        foreach ($data['vlans'] as $vid => $vname) {
            $device->vlans()->updateOrCreate([
                'vlan_id' => $vid, 
                'device_id' => $device->id
            ],
            [
                'name' => $vname, 
            ]);

            // Store vlan in global vlans table
            VlanService::createIfNotExists($device, $vid, $vname);
        }

        foreach ($data['ports'] as $port) {
            $device->ports()->updateOrCreate([
                'name' => $port['id'], 
                'device_id' => $device->id
            ],
            [
                'description' => $port['name'],
                'link' => $port['link'], 
                'speed' => $port['speed'] ?? 0, 
            ]);
        }

        foreach ($data['uplinks'] as $port => $uplink) {
            $device->uplinks()->updateOrCreate([
                'name' => $uplink, 
                'device_id' => $device->id,
                'device_port_id' => $device->ports()->where('name', $port)->first()->id, 
            ]);
        }

        foreach($data['vlanports'] as $vlanport) {
            $device->vlanports()->updateOrCreate([
                'device_port_id' => $device->ports()->where('name', $vlanport['port_id'])->first()->id, 
                'device_vlan_id' => $device->vlans()->where('vlan_id', $vlanport['vlan_id'])->first()->id, 
                'device_id' => $device->id,
            ],
            [
                'is_tagged' => $vlanport['is_tagged']
            ]);
        }

        foreach($data['statistics'] as $statistic) {
            DevicePortStat::create([
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

        $device->named = $data['informations']['name'] ?? NULL;
        $device->model = $data['informations']['model'] ?? NULL;
        $device->serial = $data['informations']['serial'] ?? NULL;
        $device->hardware = $data['informations']['hardware'] ?? NULL;
        $device->mac_address = $data['informations']['mac'] ?? NULL;
        $device->firmware = $data['informations']['firmware'] ?? NULL;
        $device->update();

        // dd($data['informations'], $data['macs']);
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
        $device = Device::create($request->except('_token'));

        if ($device) {
            return $device;
        }

        return false;
    }

    static function updateUplinks($device, $uplinks) {
        $uplinks = explode(',', $uplinks);
        $uplinks = str_replace(' ', '', $uplinks);

        foreach ($uplinks as $uplink) {
            // $device->uplinks()->updateOrCreate([
            //     'name' => $uplink, 
            //     'device_id' => $device->id
            // ]);

            // TODO: Update or create uplink
            // Own table? DeviceUplinkCustom?
        }
    }

    static function deleteDeviceData(Device $device) {
        DeviceBackup::where('device_id', $device->id)->delete();
        DevicePortStat::where('device_id', $device->id)->delete();
        DevicePort::where('device_id', $device->id)->delete();
        DeviceVlan::where('device_id', $device->id)->delete();
        DeviceUplink::where('device_id', $device->id)->delete();
        DeviceVlanPort::where('device_id', $device->id)->delete();
        Client::where('device_id', $device->id)->delete();
        Mac::where('device_id', $device->id)->delete();
    }

    static function isOnline($hostname)
    {
        try {
            if ($fp = fsockopen($hostname, 22, $errCode, $errStr, 0.2)) {
                fclose($fp);
                return true;
            }
            fclose($fp);

        } catch (\Exception $e) {
        }

        return false;
    }

    static function storeCustomUplinks(Request $request) {
        if($request->has('uplinks') and $request->uplinks != NULL and $request->device_id != '') {

            if (preg_match("/[^A-Za-z0-9\,\-]/", $request->uplinks))
            {
                return back()->withErrors(['error' => 'Format error (allowed: 1-10 or 1,2,3,4,5)']);
            }

            // Wenn eine Range angegeben wurde (z.B 49-52)
            if(str_contains($request->uplinks, "-")) {
                $explode_range = preg_split('@-@', $request->uplinks, -1, PREG_SPLIT_NO_EMPTY);
                $uplinks = range($explode_range[0], $explode_range[1]);

            // Sonst einfach kommasepariert (z.B 49,50,51,52)
            } else {
                $explode_range = preg_split('@,@', $request->uplinks, -1, PREG_SPLIT_NO_EMPTY);
                $uplinks = $explode_range;
            }

            $uplinks = str_replace(' ', '', $uplinks);

            $uplinks = json_encode($uplinks);

            DeviceCustomUplink::updateOrCreate([
                'device_id' => $request->device_id
            ],
            [
                'uplinks' => $uplinks
            ]);

            return back()->with('success', 'Uplinks wurden gespeichert');
        }

        return back()->with('error', 'Uplinks konnten nicht gespeichert werden');
    }
}
