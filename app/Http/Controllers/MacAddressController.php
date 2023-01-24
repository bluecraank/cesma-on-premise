<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\MacAddress;
use App\Models\MacVendors;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MacAddressController extends Controller
{
    static function store($mac, $port, $vlan, $device_id) {
        $newMacAddress = MacAddress::create([
            'mac_address' => $mac,
            'device_id' => $device_id,
            'port_id' => $port,
            'vlan_id' => $vlan,
        ]);

        if ($newMacAddress) {
            return true;
        }

        return false;
    }

    static function refreshMacDataFromSwitch($id, $data, $json_uplinks) {
        $uplinks = json_decode($json_uplinks, true);
        foreach ($data as $mac) {
            try {
                $delete = MacAddress::where('mac_address', $mac['mac'])->where('device_id', $id)->delete();

                if (!in_array($mac['port'], $uplinks)) {
                    MacAddressController::store($mac['mac'], $mac['port'], $mac['vlan'], $id);
                }
            } catch (\Exception $e) {
                Log::error('Database error, trying next time');
            }
        }
    }

    static function getMacVendor() {
        $macs = Client::all();
        $vendors = MacVendors::all()->keyBy('mac_prefix');

        $mac_prefixes = [];
        foreach ($macs as $mac) {
            $mac_prefix = substr($mac->mac_address, 0, 6);
            if (!isset($vendors[$mac_prefix])) {
                $mac_prefixes[$mac_prefix] = true;
            }
        }

        echo "Try to fetch " . count($mac_prefixes) . " unique mac prefixes\n";

        foreach ($mac_prefixes as $vendor => $useless) {
            try {
                $vendor_res = Http::connectTimeout(3)->get("https://api.macvendors.com/" . $vendor);

                if ($vendor_res->successful() and $vendor_res->status() == 200) {
                    $vendor_res = $vendor_res->body();

                    MacVendors::firstOrCreate([
                        'mac_prefix' => $vendor,
                        'vendor_name' => $vendor_res,
                    ]);

                    echo "Fetched " . $vendor_res . " for " . $vendor . "\n";
                } else {
                    echo "Could not fetch vendor for " . $vendor . " (" . $vendor_res->status() . ")\n";
                }
            } catch (\Exception $e) {

                echo "EXC: Could not fetch vendor for " . $vendor . " (" . $e->getMessage(), ")\n";
            }

            usleep(600000);
        }
    }
}
