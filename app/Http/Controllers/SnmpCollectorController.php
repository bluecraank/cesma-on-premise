<?php

namespace App\Http\Controllers;

use App\Models\MacAddress;
use App\Models\SnmpCollector;
use App\Models\UnknownClient;
use Illuminate\Http\Request;

class SnmpCollectorController extends Controller
{

    static function collect() {
        $subnets = [
            // 1 => '192.168.1.0/24',
            // 10 => '192.168.10.0/24',
            // 60 => '192.168.60.0/24',
            // 100 => '192.168.100.0/24',
            // 102 => '192.168.102.0/24',
            // 120 => '192.168.120.0/24',
            200 => '192.168.200.0/24',
            // 503 => '10.50.3.0/24',
            // 510 => '10.50.10.0/24',
            // 512 => '10.50.12.0/24',
            // 520 => '10.50.20.0/22',
            // 530 => '10.50.30.0/24',
            // 560 => '10.50.60.0/24',
        ];

        foreach($subnets as $subnet) {
            $data = simplexml_load_file(storage_path('app/'.explode("/",$subnet)[0]));
            $data = json_encode($data);
            $data = json_decode($data, true);
            $hosts = [];
            foreach($data['host'] as $key => $host) {
                // echo "Added ".$host['address']['@attributes']['addr']."\n";
                $hosts[] = [
                    'status' => $host['status']['@attributes']['state'],
                    'ip' => $host['address']['@attributes']['addr'],
                ];
            }

            $printers = [];

            //$start = ip2long($start_ip);
            // for ($i = 0; $i < $ip_count; $i++) {
            //     $ip = long2ip($start + $i);
            //     // do stuff with $ip...
            // }

            foreach($hosts as $key => $host) {
                if($host['status'] == 'up') {
                    try {
                        // echo "SNMP ".$host['ip'];
                        $snmp_data = snmp2_real_walk($host['ip'], 'public', '.1.3.6.1.2.1.2.2.1.6.1', 100000, 1);
                        // dd($snmp_data);
                        if($snmp_data != false) {
                            if($snmp_data[key($snmp_data)] == "\"\"") {
                                $snmp_data = snmp2_real_walk($host['ip'], 'public', '.1.3.6.1.2.1.2.2.1.6.2', 100000, 1);
                            }
                            $data = strtolower(str_replace(["Hex-STRING: ", " "], "", $snmp_data[key($snmp_data)]));
                            $printers[$data] = [
                                'mac' => $data,
                                'ip' => $host['ip'],
                            ];
                        }
                    } catch(\Exception $e) {
                        continue;
                    }
                }
            }  

            return($printers);
        }
    }

    static function store($ip, $snmp_data, $hostname, $mac) {

        if(SnmpCollector::where('ip_address', $ip)->exists()) {
            return;
        }

        SnmpCollector::create([
            'mac_address' => $mac,
            'description' => $snmp_data['sysDescr'],
            'hostname' => $hostname,
            'ip_address' => $ip,
        ]);

    }
}
