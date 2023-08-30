<?php
    namespace App\Traits;

use App\Models\Device;
use Illuminate\Support\Facades\Http;

    trait DefaultApiMethods
    {
        static function getApiData($device): array
        {
            return [];
        }

        static function API_GET_VERSIONS($device): string
        {
            return "";
        }

        static function API_LOGIN($device): string
        {
            return "";
        }

        static function API_LOGOUT($hostname, $cookie, $api_version): bool
        {
            return true;
        }

        static function API_PUT_DATA($hostname, $cookie, $api, $version, $data): array
        {
            $api_url = config('app.https') . $hostname . '/rest/' . $version . '/' . $api;
            try {
                $response = Http::withBody($data, 'application/json')->withoutVerifying()->withHeaders([
                    'Cookie' => "$cookie",
                ])->put($api_url);

                if ($response->successful()) {
                    return ['success' => true, 'data' => array($response->status(), $response->json())];
                } else {
                    return ['success' => false, 'data' => $response->json()];
                }
            } catch (\Exception $e) {
                return ['success' => false, 'data' => $e->getMessage()];
            }
        }

        static function API_GET_DATA($hostname, $cookie, $api, $version, $plain = false): array
        {
            $api_url = config('app.https') . $hostname . '/rest/' . $version . '/' . $api;

            try {
                if ($plain) {
                    $response = Http::accept('text/plain')->withoutVerifying()->withHeaders([
                        'Content-Type' => 'application/json',
                        'Cookie' => "$cookie",
                    ])->get($api_url);
                } else {
                    $response = Http::withoutVerifying()->withHeaders([
                        'Content-Type' => 'application/json',
                        'Cookie' => "$cookie",
                    ])->get($api_url);
                }

                if ($response->successful()) {
                    return ['success' => true, 'data' => ($plain) ? $response->body() : $response->json()];
                } else {
                    return ['success' => false, 'data' => $response->json()];
                }
            } catch (\Exception $e) {
                return ['success' => false, 'data' => $e->getMessage()];
            }
        }

        static function API_PATCH_DATA($hostname, $cookie, $api, $version, $data): array
        {
            $api_url = config('app.https') . $hostname . '/rest/' . $version . '/' . $api;

            try {
                $response = Http::withBody($data, 'application/json')->withoutVerifying()->withHeaders([
                    'Cookie' => "$cookie",
                ])->patch($api_url);

                if ($response->successful()) {
                    return ['success' => true, 'data' => array($response->status(), $response->json())];
                } else {
                    return ['success' => false, 'data' => $response->json()];
                }
            } catch (\Exception $e) {
                return ['success' => false, 'data' => $e->getMessage()];
            }
        }

        static function API_DELETE_DATA($hostname, $cookie, $api, $version, $data): array
        {
            $api_url = config('app.https') . $hostname . '/rest/' . $version . '/' . $api;

            try {
                $response = Http::withBody($data, 'application/json')->withoutVerifying()->withHeaders([
                    'Content-Type' => 'application/json',
                    'Cookie' => "$cookie",
                ])->delete($api_url);

                if ($response->successful()) {
                    return ['success' => true, 'data' => $response->json()];
                } else {
                    return ['success' => false, 'data' => $response->json()];
                }
            } catch (\Exception $e) {
                return ['success' => false, 'data' => $e->getMessage()];
            }
        }

        static function API_POST_DATA($hostname, $cookie, $api, $version, $data): array
        {
            $api_url = config('app.https') . $hostname . '/rest/' . $version . '/' . $api;

            try {
                $response = Http::withBody($data, 'application/json')->withoutVerifying()->withHeaders([
                    'Cookie' => "$cookie",
                ])->post($api_url);

                if ($response->successful()) {
                    return ['success' => true, 'data' => array($response->status(), $response->json())];
                } else {
                    return ['success' => false, 'data' => $response->json()];
                }
            } catch (\Exception $e) {
                return ['success' => false, 'data' => $e->getMessage()];
            }
        }

        static function createBackup($device): bool
        {
            return false;
        }

        static function restoreBackup($device, $backup, String $password): array
        {
            return [];
        }

        static function formatPortData(array $ports, array $stats): array
        {
            return [];
        }

        static function formatExtendedPortStatisticData(array $portstats, array $portdata): array
        {
            return [];
        }

        static function formatPortVlanData(array $vlanports): array
        {
            return [];
        }

        static function formatUplinkData($data): array
        {
            $uplinks = [];

            return $uplinks;
        }


        static function formatVlanData(array $vlans): array
        {
            return [];
        }

        static function formatMacTableData(array $data, array $vlans, $device, String $cookie, String $api_version): array
        {
            // Not supported by DellEMC
            return [];
        }

        static function formatSystemData(array $system): array
        {
            $return = [];

            return $return;
        }

        static function syncPubkeys($device, $pubkeys): string
        {
            return "";
        }

        static function setUntaggedVlanToPort($vlan, $port, $device, $vlans, $need_login, $login_info): bool
        {
            return [];
        }

        static function setTaggedVlansToPort($taggedVlans, $port, $device, $vlans, $need_login, $login_info): array
        {
            return [];
        }

        static function syncVlans($vlans, $device, Bool $create_vlans, Bool $overwrite_name, Bool $tag_to_uplinks,  Bool $test_mode): array
        {
            return [];
        }

        static function setPortDescription(String $port, String $name, Device $device, String $logininfo): bool
        {
            return false;
        }
    }
