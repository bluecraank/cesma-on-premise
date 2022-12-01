<?php

namespace App\Http\Controllers;


use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Pool;

class ApiRequestController extends Controller
{
    static function login(string $api_password, string $api_url) {
        $url = env('APP_HTTPS') . $api_url . '/rest/v7/login-sessions';

        echo $url;
        // Only for testing
        //Http::fake([
        //    $api_url.'.de/*' => Http::response(['cookie' => 'djasd9asdub123kdkj'], 200, ['Headers']),
        //]);

        // Try to login
        try {
            // Post request to login
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->retry(2,200)->post($url, [
                'userName' => env('API_API_USERNAME'),
                'password' => $api_password,
            ]);

            // Return cookie if login was successful
            if($response->successful() AND !empty($response->json()['cookie'])) {
                return $response->json()['cookie'];
            }

            return false;

        } catch (\Exception $e) {
            echo $e;
            return false;
        }
    }

    static function getData($cookie, $api_url) {
        $url = env('APP_HTTPS') . $api_url . '.de/api/v1/login-sessions';

        // Only for testing
        //Http::fake([
        //    $api_url.'.de/*' => Http::response(['data' => 'djasd9asdub123kdkj'], 200, ['Headers']),
        //]);

        // Try to get data from device
        try {

            // Get data from device
            $responses = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'Cookie' => $cookie,
            ])->pool(fn (Pool $pool) => [
                $pool->as('vlanData')->get($url . '/vlans'),
                $pool->as('portStatistic')->get($url . '/port-statistics'),
                $pool->as('vlanPort')->get($url . '/vlan-ports'),
                $pool->as('sysStatus')->get($url . '/system/status'),
            ]);
            
            // Return data
            if($responses['vlanData']->successful() AND $responses['portStatistic']->successful() AND $responses['vlanPort']->successful() AND $responses['sysStatus']->successful()) {
                return [
                    'vlan_data' => $responses['vlanData']->json(),
                    'portstats_data' => $responses['portStatistic']->json(),
                    'vlanport_data' => $responses['vlanPort']->json(),
                    'sysstatus_data' => $responses['sysStatus']->json(),
                ];
            }
            
            return false;

        } catch (\Exception $e) {
            return false;
        }
    }
}
