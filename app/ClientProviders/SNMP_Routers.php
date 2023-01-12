<?php

namespace App\ClientProviders;

use App\Interfaces\IClientProvider;

class SNMP_Routers implements IClientProvider
{
    static function queryClientData(): Array {
        $macs = [];
        
        $cores = [
            1 => '192.168.100.176',
            2 => '192.168.100.175',
            3 => '10.50.10.1'
        ];

        foreach($cores as $core) {
                try {
                    $snmp_data = snmp2_real_walk($core, 'public', '.1.3.6.1.2.1.4.22.1.2', 200000, 1);
                    foreach($snmp_data as $ip => $mac) {
                        $filtered_ip = explode(".", $ip);
                        $filtered_ip = $filtered_ip[11] . "." . $filtered_ip[12] . "." . $filtered_ip[13] . "." . $filtered_ip[14];
                        $filtered_mac = strtolower(str_replace(" ", "", strstr($mac, " ")));

                        $output = "";
                        $get = exec('timeout 0.4 host '.$filtered_ip, $output, $errors);

                        $hostname = $output[0] ?? "";
                        $found = strtoupper(strstr($hostname, "pointer"));
                        $hostname = str_replace(["POINTER", " ", ".DOEPKE.LOCAL."], "", $found);

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
                    echo "Could not fetch snmp from $core (". $e->getMessage() ."))";
                }
        }

        return $macs;
    }

    static function queryClientDataDebug(): Array
    {
        $macs = [];
        
        $cores = [
            1 => '192.168.100.176',
            2 => '192.168.100.175',
            3 => '10.50.10.1'
        ];

        foreach($cores as $core) {
                try {
                    $snmp_data = snmp2_real_walk($core, 'public', '.1.3.6.1.2.1.4.22.1.2', 100000, 1);
                    foreach($snmp_data as $ip => $mac) {
                        $filtered_ip = explode(".", $ip);
                        $filtered_ip = $filtered_ip[11] . "." . $filtered_ip[12] . "." . $filtered_ip[13] . "." . $filtered_ip[14];
                        $filtered_mac = strtolower(str_replace(" ", "", strstr($mac, " ")));
                        // echo $filtered_ip . " | ".$filtered_mac."<br>";
                        $macs[$filtered_mac] = [
                            'mac_addresses' => $filtered_mac,
                            'ip_address' => $filtered_ip,
                            'hostname' => strtoupper("SNMP-".$filtered_mac),
                        ];
                    }
                } catch(\Exception $e) {
                    echo $e->getMessage();
                }
        }
        
        return $macs;
    }
}

?>