<?php

namespace App\ClientProviders;

use App\Interfaces\IClientProvider;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Baramundi implements IClientProvider
{
    /**
     * @return Array
     * 
     */
    static function queryClientData(): array
    {
        $url = config('app.baramundi_api_url') . "/bCOnnect/v1.1/Endpoints.json";
        $username = config('app.baramundi_username');
        $password = config('app.baramundi_password');

        try {
            $data = Http::withoutVerifying()->withBasicAuth($username, $password)->get($url)->json();
        } catch (\Exception $e) {
            Log::error("Could not connect to Baramundi API: " . $e->getMessage());
            return [];
        }

        $endpoints = [];


        if ($data == null or empty($data)) {
            return $endpoints;
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

                if (!isset($value['PrimaryIP']) or $value['PrimaryIP'] == null) {
                    $value['PrimaryIP'] = gethostbyname($value['HostName']);
                    if ($value['PrimaryIP'] == $value['HostName']) {
                        $value['PrimaryIP'] = null;
                    }
                }

                $endpoints[] = [
                    'mac_addresses' => $maclist,
                    'ip_address' => $value['PrimaryIP'] ?? null,
                    'hostname' => (isset($value['HostName'])) ? $value['HostName'] : null,
                ];
            }
        }

        return $endpoints;
    }
}
