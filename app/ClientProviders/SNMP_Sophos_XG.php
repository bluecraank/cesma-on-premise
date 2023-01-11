<?php

namespace App\ClientProviders;

use App\Interfaces\IClientProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class SNMP_Sophos_XG implements IClientProvider
{
    static function queryClientData(): Array {

        $cmd = exec("snmpbulkwalk -c public -v 2c gateway-int .1.3.6.1.2.1.4.22.1.2", $output, $return);
        
        $macs = [];

        $start = microtime(true);

        foreach($output as $mac) {
            $data = explode(";", str_replace(["iso.3.6.1.2.1.4.22.1.2.", " = Hex-STRING: "], ["", ";"], $mac));
            $data[0] = strstr($data[0], ".");
            $data[0] = preg_replace('/./', "", $data[0], 1);

            $req = exec('timeout 0.1 host '.$data[0], $dns, $result);

            if(!isset($dns[0])) {
                $dns = array(0 => "pointer ");
            }

            $dns = strstr($dns[0], "pointer");

            $dns = str_replace("pointer ", "", $dns);
            if($dns == "") {
                $dns = "UNK-".Str::random(10);
            }
            
            $macs[] = [
                "ip_address" => $data[0],
                "mac_addresses" => array(str_replace(" ", ":", $data[1])),
                "hostname" => $dns,
            ];
        }

        // echo microtime(true) - $start;
        
        return $macs;
    }

    static function queryClientDataDebug(): Array
    {
        $url = config('app.baramundi_api_url')."/bCOnnect/v1.1/Endpoints.json";
        $username = config('app.baramundi_username');
        $password = config('app.baramundi_password');

        $data = Http::withoutVerifying()->withBasicAuth($username, $password)->get($url)->json();

        $endpoints = [];


        if($data == null or empty($data)) {
            return $endpoints;
        }

        $endpoints = $data;

        
        dd($endpoints); 
    }
}

?>