<?php

namespace App\Http\Controllers;

use App\EndpointProviders\Baramundi;
use Illuminate\Http\Request;
use App\Interfaces\IEndpoint;
use App\Models\Device;

class EndpointController extends Controller
{
    public function getEndpoints() { 

        //ddd(config('app.baramundi_api_url'));
        if(!empty(config('app.baramundi_api_url'))) {
            $provider = new Baramundi;
            $endpoints = $provider->queryClientData();

            return $endpoints;
        }
    }

    public function getMacAddressTables() {

        $device = Device::all()->keyBy('id');

        $macAddressTables = [];
        
        foreach($device as $switch) {
            $macTable = json_decode($switch->mac_table_data, true);
            $macData = (isset($macTable['mac_table_entry_element'])) ? $macTable['mac_table_entry_element'] : [];
            $macAddressTables = array_merge($macAddressTables, $macData);
        }
        
        return $macAddressTables;
    }

    public function getMergedData() {
        $endpoint_data = $this->getEndpoints();
        $mac_data = $this->getMacAddressTables();

        ddd($endpoint_data, $mac_data);
    }
}
