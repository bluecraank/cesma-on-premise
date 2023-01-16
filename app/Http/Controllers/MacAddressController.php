<?php

namespace App\Http\Controllers;

use App\Models\MacAddress;
use Illuminate\Http\Request;

class MacAddressController extends Controller
{
    static function store($mac, $port, $vlan, $device_id) {

        $newMacAddress = MacAddress::create([
            'mac_address' => $mac,
            'device_id' => $device_id,
            'port_id' => $port,
            'vlan_id' => $vlan,
        ]);

        if($newMacAddress) {
            return true;
        }
        
        return false;
    }

    static function refreshMacDataFromSwitch($id, $data, $json_uplinks) {
        MacAddress::where("device_id", $id)->delete();

        $uplinks = json_decode($json_uplinks, true);
        foreach($data as $mac) {
            if(!in_array($mac['port'], $uplinks)) {

                MacAddressController::store($mac['mac'], $mac['port'], $mac['vlan'], $id);
            
            }            
        }
    }
}
