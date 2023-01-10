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
            echo "Created new mac address: ".$mac."\n";
            return true;
        }
        return false;
    }
}
