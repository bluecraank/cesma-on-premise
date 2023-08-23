<?php

namespace App\ClientProviders;

use App\Interfaces\ClientProviderInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\SnmpMacData;

class Baramundi implements ClientProviderInterface
{
    /**
     * @return Array
     * 
     */
    static function queryClientData(): array
    {
        if(config('app.baramundi_api_url') && config('app.enable_baramundi')) {
            $url = config('app.baramundi_api_url') . "/bConnect/v1.1/Endpoints.json";
            $username = config('app.baramundi_username');
            $password = config('app.baramundi_password');
    
            try {
                $data = Http::connectTimeout(15)->withoutVerifying()->withBasicAuth($username, $password)->get($url)->json();
            } catch (\Exception $e) {
                Log::error("Could not connect to Baramundi API: " . $e->getMessage());
                return [];
            }
    
            $ip_to_mac = [];
    
    
            if ($data == null or empty($data)) {
                return $ip_to_mac;
            }
    
            foreach ($data as $value) {
                if (isset($value['MACList']) and (isset($value['PrimaryIP']) or isset($value['HostName']))) {
    
                    $maclist = explode(";", strtolower(str_replace(":", "", $value['MACList'])));
    
                    if (isset($value['PrimaryMAC'])) {
                        $maclist[] = strtolower(str_replace(":", "", $value['PrimaryMAC']));
                    }
    
                    if (isset($value['LogicalMAC'])) {
                        $maclist[] = strtolower(str_replace(":", "", $value['LogicalMAC']));
                    }
    
                    // Get IP from hostname if no IP is set
                    if (!isset($value['PrimaryIP']) or $value['PrimaryIP'] == null) {
                        $value['PrimaryIP'] = gethostbyname($value['HostName'] ?? "");
                        if ($value['PrimaryIP'] == $value['HostName']) {
                           continue;
                        }
                    }
    
                    $ip_to_mac[] = [
                        'mac_addresses' => $maclist,
                        'ip_address' => $value['PrimaryIP'],
                        'router' => 'Baramundi API'
                    ];
                }
            }
    
            foreach($ip_to_mac as $data) {
                foreach($data['mac_addresses'] as $mac) {
                    SnmpMacData::updateOrCreate(
                        ['mac_address' => $mac],
                        [
                            'mac_address' => $mac,
                            'ip_address' => $data['ip_address'],
                            'router' => $data['router'],
                        ]
                    );
                }
            }
    
            return $ip_to_mac;
        }
    }
}
