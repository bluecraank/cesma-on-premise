<?php

namespace App\EndpointProviders;

use App\Interfaces\IEndpoint;
use App\Models\Endpoint;
use Illuminate\Support\Facades\Http;

class Baramundi implements IEndpoint
{
    /**
     * @return Array
     * 
    */
    public function queryClientData(): Array {
        $url = config('app.baramundi_api_url');
        $username = config('app.baramundi_username');
        $password = config('app.baramundi_password');

        $data = Http::withoutVerifying()->withBasicAuth($username, $password)->get($url)->json();

        $endpoints = [];
        $i = 0;

        foreach ($data as $key => $value) {
            if(isset($value['MACList']) and (isset($value['PrimaryIP']) or isset($value['HostName']))) {

                $endpoints[$i] = new \stdClass();
                $endpoints[$i]->mac_addresses = strtolower(str_replace(":", "", $value['MACList']));
                $endpoints[$i]->ip_address = (isset($value['PrimaryIP'])) ? $value['PrimaryIP'] : null;
                $endpoints[$i]->hostname = (isset($value['HostName'])) ? $value['HostName'] : null;

                $i++;
            }
        }

        
        return $endpoints;
    }
}

?>