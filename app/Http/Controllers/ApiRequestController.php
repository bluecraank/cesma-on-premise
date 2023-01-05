<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;

class ApiRequestController extends Controller
{
    static function login(string $api_password, string $hostname) {
        $url = env('APP_HTTPS') . $hostname . '/rest/v7/login-sessions';

            // Post request to login
            $api_password = EncryptionController::decrypt($api_password);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json'
            ])->retry(2,200, throw: false)->post($url, [
                'userName' => env('APP_API_USERNAME'),
                'password' => $api_password,
            ]);
            
            // if($response->failed()) {
            //     $url = env('APP_HTTPS') . $hostname . '/rest/v1/login-sessions';
            //     $response = NULL;
            //     $response = Http::withHeaders([
            //         'Content-Type' => 'application/json'
            //     ])->retry(2,200, throw: false)->post($url, [
            //         'userName' => env('APP_API_USERNAME'),
            //         'password' => $api_password,
            //     ]);
            // }

            // if($response->failed()) {
            //     $url = env('APP_HTTPS') . $hostname . '/rest/v3/login-sessions';
            //     $response = NULL;
            //     $response = Http::withHeaders([
            //         'Content-Type' => 'application/json'
            //     ])->retry(2,200, throw: false)->post($url, [
            //         'userName' => env('APP_API_USERNAME'),
            //         'password' => $api_password,
            //     ]);
            // }

            // if($response->failed()) {
            //     $url = env('APP_HTTPS') . $hostname . '/rest/v1/login-sessions';
            //     $response = NULL;
            //     $response = Http::withHeaders([
            //         'Content-Type' => 'application/json'
            //     ])->retry(2,200, throw: false)->post($url, [
            //         'userName' => env('APP_API_USERNAME'),
            //         'password' => $api_password,
            //     ]);
            // }

            // Return cookie if login was successful
            if($response->successful() AND !empty($response->json()['cookie'])) {
                return $response->json()['cookie'];
            }

            return false;
    }

    static function getData($cookie, $hostname) {
        $url = env('APP_HTTPS') . $hostname . '/rest/v7/';

        // Try to get data from device
        try {

            // Get data from device
            $responses = Http::pool(fn (Pool $pool) => [
                $pool->as('vlanData')->withHeaders([
                'Cookie' => "$cookie",
                ])->get($url . 'vlans'),

                $pool->as('portStatistic')->withHeaders([
                'Cookie' => "$cookie",
                ])->get($url . 'port-statistics'),

                $pool->as('vlanPort')->withHeaders([
                    'Cookie' => "$cookie",
                ])->get($url . 'vlans-ports'),

                $pool->as('sysStatus')->withHeaders([
                'Cookie' => "$cookie",
                ])->get($url . 'system/status'),
        
                $pool->as('ports')->withHeaders([
                    'Cookie' => "$cookie",
                    ])->get($url . 'ports'),
                
                $pool->as('mac_table')->withHeaders([
                    'Cookie' => "$cookie",
                    ])->get($url . 'mac-table'),
            ]);
            
            // Return data
            if($responses['portStatistic'] and $responses['vlanPort'] and $responses['sysStatus'] and 
            $responses['portStatistic']->successful() AND $responses['vlanPort']->successful() AND $responses['sysStatus']->successful()) {
                return [
                    'vlan_data' => $responses['vlanData']->json(),
                    'portstats_data' => $responses['portStatistic']->json(),
                    'vlanport_data' => $responses['vlanPort']->json(),
                    'sysstatus_data' => $responses['sysStatus']->json(),
                    'ports_data' => $responses['ports']->json(),
                    'mac_table_data' => $responses['mac_table']->json()
                ];
            }
            
            return false;

        } catch (\Exception $e) {
            return false;
        }
    }

    static function logout($cookie, $hostname) {
        $url = env('APP_HTTPS') . $hostname . '/rest/v7/'; 

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'Cookie' => "$cookie",
        ])->delete($url . 'login-sessions');
    }
}
