<?php

namespace App\ClientProviders;

use App\Interfaces\IClientProvider;

class SNMP_Routers implements IClientProvider
{
    static function queryClientData(): Array {
        $macs = [];
        
        $routers = explode(",", config('app.snmp_routers'));

        foreach($routers as $router) {
                try {
                    $snmp_data = snmp2_real_walk($router, 'public', '.1.3.6.1.2.1.4.22.1.2', 10000000, 1);
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
                    echo "Could not fetch snmp from $router (". $e->getMessage() ."))";
                }
        }

        return $macs;
    }

    static function debugQuery(): Array {
        $macs = [];
        
        $routers = explode(",", config('app.snmp_routers'));

        $i = 0;

        foreach($routers as $router) {
                    $snmp_ifdesc = snmp2_real_walk($router, 'public', '.1.3.6.1.2.1.2.2.1.2', 1000000, 1);
                    $snmp_ifindex = snmp2_real_walk($router, 'public', '.1.3.6.1.2.1.4.22.1.1', 1000000, 1);
                    $snmp_ip_ip = snmp2_real_walk($router, 'public', '.1.3.6.1.2.1.4.22.1.3', 1000000, 1);
                    $snmp_ip_to_mac = snmp2_real_walk($router, 'public', '.1.3.6.1.2.1.4.22.1.2', 1000000, 1);

                    foreach($snmp_ip_ip as $key => $ip) {
                        $ip_f = str_replace("IpAddress: ", "", $ip);
                        $new_key = str_replace("iso.3.6.1.2.1.4.22.1.3", "iso.3.6.1.2.1.4.22.1.2", $key); 

                        if(isset($snmp_ip_to_mac[$new_key])) {
                            $mac_f = str_replace("Hex-STRING: ", "", $snmp_ip_to_mac[$new_key]);
                            $mac_f = str_replace(" ", "", $mac_f);
                            $mac_f = strtolower($mac_f);
                            $if_key = str_replace("iso.3.6.1.2.1.4.22.1.3", "iso.3.6.1.2.1.4.22.1.1", $key);
                            $if_index = str_replace("INTEGER: ", "", $snmp_ifindex[$if_key]);
                            $thr_key = "iso.3.6.1.2.1.2.2.1.2.".$if_index;
                            if(isset($snmp_ifdesc[$thr_key])) {
                                $thr = str_replace(["\"","STRING: ", "VLAN"], "", $snmp_ifdesc[$thr_key]);

                                $thr = str_replace("DEFAULT_", 1, $thr);

                                if(str_contains($thr, ".")) {
                                    $thr = explode(".", $thr)[1];
                                }

                                if($thr) {
                                    echo $ip_f . " | " . $mac_f . " | " . $thr . " | ". $if_index.  "<br>";
                                }
                            }
                            $i++;
                        }
                    }
            }
                    echo $i;

                    // foreach($snmp_ip_to_mac as $key => $mac) {
                    //     $ip = str_replace("IpAddress: ", "", $snmp_ip_ip[$key]);
                    //     $if_index = str_replace("INTEGER: ", "", $snmp_ifindex[$key]);

                    //     echo $ip . " | " . $mac . " | " . $if_index . ";";
                    // }

        return $macs;
    }
}

?>