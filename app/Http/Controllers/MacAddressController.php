<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\MacAddress;
use App\Models\MacVendors;
use Illuminate\Support\Facades\Http;

class MacAddressController extends Controller
{   
    public function index() {
        $macs = MacAddress::all();
        $wifi_macs = explode(",", config('app.wifi_macs'));
        foreach($macs as $mac) {
            if(in_array(substr($mac->mac_address, 0, 6), $wifi_macs)) {
                echo $mac->mac_address . " is a wifi mac<br>";
            }
        }
        return count($macs);
    }

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
        // MacAddress::where("device_id", $id)->delete();

        $uplinks = json_decode($json_uplinks, true);
        foreach($data as $mac) {
            $delete = MacAddress::where('mac_address', $mac['mac'])->where('device_id', $id)->delete();
            if(!in_array($mac['port'], $uplinks)) {

                MacAddressController::store($mac['mac'], $mac['port'], $mac['vlan'], $id);
            }            
        }
    }

    static function getMacVendor() {
        $macs = Client::all();
        $vendors = MacVendors::all()->keyBy('mac_prefix');

        $mac_prefixes = [];
        foreach($macs as $mac) {
            $mac_prefix = substr($mac->mac_address, 0, 6);
            if(!isset($vendors[$mac_prefix])) {
                $mac_prefixes[$mac_prefix] = true;
            }
        }
        
        echo "Try to fetch ".count($mac_prefixes)." unique mac prefixes\n";

        foreach($mac_prefixes as $vendor => $useless) {
                try {
                    $vendor_res = Http::connectTimeout(3)->get("https://api.macvendors.com/".$vendor);

                    if($vendor_res->successful() and $vendor_res->status() == 200) {
                        $vendor_res = $vendor_res->body();
            
                        MacVendors::firstOrCreate([
                            'mac_prefix' => $vendor,
                            'vendor_name' => $vendor_res,
                        ]);
                
                        echo "Fetched ".$vendor_res." for " . $vendor . "\n";
                    } else {
                        echo "Could not fetch vendor for " . $vendor. " (".$vendor_res->status().")\n";
                    }

                } catch(\Exception $e) {

                    echo "EXC: Could not fetch vendor for " . $vendor. " (".$e->getMessage(),")\n";
                }

                usleep(600000);
        }
    }

    static function debugQuery() {
        $mactable = MacAddress::all()->sortBy('vlan_id');
        foreach($mactable as $cl) {
            echo $cl->vlan_id . "\n";
        }
        echo count($mactable);
    }
}