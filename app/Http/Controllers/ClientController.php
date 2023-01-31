<?php

namespace App\Http\Controllers;

use App\ClientProviders\Baramundi;
use App\ClientProviders\SNMP_Routers;
use App\Models\Device;
use App\Models\Client;
use App\Models\Mac;
use App\Models\MacAddress;
use App\Models\MacType;
use App\Models\MacTypeIcon;
use App\Models\Vlan;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class ClientController extends Controller
{

    public function index()
    {

        $clients = Client::all()->keyBy('mac_address');
        $devices = Device::all()->keyBy('id');

        return view('client.client-overview', compact('clients', 'devices'));
    }

    static function checkOnlineStatus()
    {
        $clients = Client::all()->keyBy('id');

        $start = microtime(true);

        $clients_ips = [];
        foreach ($clients as $key => $client) {
            $clients_ips[$key] = $client->ip_address;
        }

        $client_ip_addresses = implode(" ", $clients_ips);

        $result = exec("fping -i 50 " . $client_ip_addresses . " 2> /dev/null", $output, $return);

        foreach ($output as $client) {
            $data = explode(" ", $client);
            $key = array_search($data[0], $clients_ips);
            if ($key !== false or $key == 0) {
                if ($data[2] == "alive") {
                    $clients[$key]->online = 1;
                } else {
                    $clients[$key]->online = 0;
                }

                if ($clients[$key]->created_at->diffInDays(Carbon::now()) > 7) {
                    $clients[$key]->online = 2;
                }

                $clients[$key]->save();
            }
        }

        $elapsed = microtime(true) - $start;

        Log::info('Clients pinged in ' . $elapsed . " seconds");
    }

    static function deleteClientsOnUplinks($device)
    {
        Client::where('switch_id', $device->id)->where(function ($query) use ($device) {
            $uplinks = json_decode($device->uplinks, true) ?? [];
            foreach ($uplinks as $uplink) {
                $query->orWhere('port_id', $uplink);
            }
        })->delete();
    }
}
