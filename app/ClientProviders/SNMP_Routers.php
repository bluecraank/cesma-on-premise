<?php

namespace App\ClientProviders;

use App\Interfaces\IClientProvider;
use App\Models\Router;
use Illuminate\Support\Facades\Log;
use App\Models\SnmpMacData;

class SNMP_Routers implements IClientProvider
{
    static function queryClientData(): Array {
        $ip_to_mac = [];
        
        $routers = Router::all()->pluck('ip')->toArray();

        foreach($routers as $router) {
                try {
                    $snmp_data = snmp2_real_walk($router, 'public', '.1.3.6.1.2.1.4.22.1.2', 5000000, 1);
                    
                    foreach($snmp_data as $ip => $mac) {
                        if(count($snmp_data) > 1) {
                            Router::where('ip', $router)->update(['check' => true]);
                        } else {
                            Router::where('ip', $router)->update(['check' => false]);
                            continue;
                        }

                        $filtered_ip = explode(".", $ip);
                        $filtered_ip = $filtered_ip[11] . "." . $filtered_ip[12] . "." . $filtered_ip[13] . "." . $filtered_ip[14];
                        $filtered_mac = strtolower(str_replace(" ", "", strstr($mac, " ")));

                        $ip_to_mac[$filtered_mac] = [
                            'mac_addresses' => [
                                $filtered_mac
                            ],
                            'ip_address' => $filtered_ip,
                            'router' => $router,
                        ];
                    }
                } catch(\Exception $e) {
                    Router::where('ip', $router)->update(['check' => false]);
                    
                    Log::error("Could not fetch snmp from $router (No response, Port blocked?, Wrong community?, Wrong IP?, Not allowed?)");
                }
        }

        foreach($ip_to_mac as $mac_address => $data) {
            SnmpMacData::updateOrCreate(
                ['mac_address' => $mac_address],
                [
                    'mac_address' => $mac_address,
                    'ip_address' => $data['ip_address'],
                    'router' => $data['router'],
                ]
            );
        }

        return $ip_to_mac;
    }
}

?>