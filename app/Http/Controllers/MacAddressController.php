<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\MacAddress;
use App\Models\MacVendor;
use App\Models\MacVendors;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MacAddressController extends Controller
{
    static function getMacVendor()
    {
        $macs = Client::all();
        $vendors = MacVendor::all()->keyBy('mac_prefix');

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

                    MacVendor::firstOrCreate([
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
