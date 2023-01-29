<?php

namespace App\ClientProviders;

use App\Interfaces\IClientProvider;
use Illuminate\Support\Facades\Log;

class SNMP_Routers implements IClientProvider
{
    static function queryClientData(): Array {
        $macs = [];
        
        $routers = explode(",", config('app.snmp_routers'));

        foreach($routers as $router) {
                try {
                    $snmp_data = snmp2_real_walk($router, 'public', '.1.3.6.1.2.1.4.22.1.2', 5000000, 1);
                    foreach($snmp_data as $ip => $mac) {
                        $filtered_ip = explode(".", $ip);
                        $filtered_ip = $filtered_ip[11] . "." . $filtered_ip[12] . "." . $filtered_ip[13] . "." . $filtered_ip[14];
                        $filtered_mac = strtolower(str_replace(" ", "", strstr($mac, " ")));

                        $output = "";
                        $get = exec('timeout 0.4 host '.$filtered_ip, $output, $errors);

                        $hostname = $output[0] ?? "";
                        $found = strtoupper(strstr($hostname, "pointer"));
                        $hostname = str_replace(["POINTER", " "], "", $found);
                        
                        if(config('app.DNS_CUT_DOMAIN') == "true") {
                            strstr($hostname, ".", true) ? $hostname = strstr($hostname, ".", true) : $hostname = $hostname;
                        }

                        if($hostname == "" or $hostname == " " or $hostname == null) {
                            $hostname = strtoupper("DEV-".$filtered_mac);
                        }

                        $macs[$filtered_mac] = [
                            'mac_addresses' => [
                                $filtered_mac
                            ],
                            'ip_address' => $filtered_ip,
                            'hostname' => $hostname,
                        ];
                    }
                } catch(\Exception $e) {
                    Log::error("Could not fetch snmp from $router (No response, Port blocked?, Wrong community?, Wrong IP?, Not allowed?)");
                }
        }

        return $macs;
    }
}

?>