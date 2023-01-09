<?php

namespace App\ClientProviders;

use App\Interfaces\IClient;
use Illuminate\Support\Facades\Http;

class Baramundi implements IClient
{
    /**
     * @return Array
     * 
    */
    public function queryClientData(): Array {
        $url = config('app.baramundi_api_url')."/bCOnnect/v1.1/Endpoints.json";
        $username = config('app.baramundi_username');
        $password = config('app.baramundi_password');

        $data = Http::withoutVerifying()->withBasicAuth($username, $password)->get($url)->json();

        $endpoints = [];
        $i = 0;

        if($data == null or empty($data)) {
            return $endpoints;
        }

        foreach ($data as $value) {
            if(isset($value['MACList']) and (isset($value['PrimaryIP']) or isset($value['HostName']))) {

                $endpoints[$i] = new \stdClass();

                $maclist = explode(";", strtolower(str_replace(":", "", $value['MACList'])));
                
                if(isset($value['PrimaryMAC'])) {
                    $maclist[] = strtolower(str_replace(":", "", $value['PrimaryMAC']));
                }

                if(isset($value['LogicalMAC'])) {
                    $maclist[] = strtolower(str_replace(":", "", $value['LogicalMAC']));
                }
                $endpoints[$i]->mac_addresses = $maclist;
                $endpoints[$i]->ip_address = (isset($value['PrimaryIP'])) ? $value['PrimaryIP'] : null;
                $endpoints[$i]->hostname = (isset($value['HostName'])) ? $value['HostName'] : null;

                $i++;
            }
        }
        
        return $endpoints;
    }

    public function storeClientData($data) {
        // TODO
    }
}

?>