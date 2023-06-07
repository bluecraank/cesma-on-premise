<?php

namespace App\Devices;

use App\Interfaces\DeviceInterface;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SFTP;

use App\Helper\CLog;
use App\Models\DeviceVlanPort;
use App\Services\PublicKeyService;
use App\Http\Controllers\BackupController;

class ArubaOS implements DeviceInterface
{
    static $api_auth = [
        "login" => "login-sessions",
        "logout" => "login-sessions",
    ];

    static $available_apis = [
        "status" => 'system/status',
        "vlans" => 'vlans',
        "ports" => 'ports',
        "portstats" => 'port-statistics',
        "vlanport" => 'vlans-ports',
        "mac-table" => 'mac-table',
    ];

    static function API_GET_VERSIONS($hostname): string
    {
        $https = config('app.https');
        $url = $https . $hostname . '/rest/version';

        try {
            $versions = Http::withoutVerifying()->get($url);

            if ($versions->successful()) {
                $versionsFound = $versions->json()['version_element'];
                return $versionsFound[array_key_last($versionsFound)]['version'];
            }
        } catch (\Exception $e) {
        }

        return "v7";
    }

    static function API_LOGIN($device): string
    {
        $api_version = self::API_GET_VERSIONS($device->hostname);

        $api_url = config('app.https') . $device->hostname . '/rest/' . $api_version . '/' . self::$api_auth['login'];

        $api_username = config('app.api_username');

        $api_password =  Crypt::decrypt($device->password);

        try {
            $response = Http::connectTimeout(3)->withoutVerifying()->withHeaders([
                'Content-Type' => 'application/json'
            ])->post($api_url, [
                'userName' => $api_username,
                'password' => $api_password,
            ]);

            // Return cookie if login was successful
            if ($response->successful() and !empty($response->json()['cookie'])) {
                return $response->json()['cookie'] . ";" . $api_version;
            } else {
                Log::error("[Error] Failed to login to device " . $device->name . " ERROR: " . $response->body());
            }
        } catch (\Exception $e) {
            Log::error("[Error] Failed to login to device " . $device->name . " ERROR: " . $e->getMessage());
            return "";
        }
        return "";
    }

    static function API_LOGOUT($hostname, $cookie, $api_version): bool
    {
        $api_url = config('app.https') . $hostname . '/rest/' . $api_version . '/' . self::$api_auth['logout'];

        try {
            $logout = Http::connectTimeout(8)->withoutVerifying()->withHeaders([
                'Content-Type' => 'application/json',
                'Cookie' => "$cookie",
            ])->delete($api_url);

            if ($logout->successful()) {
                return true;
            }
        } catch (\Exception $e) {
            return false;
        }

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
            if($plain) {
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

    static function API_REQUEST_ALL_DATA($device): array
    {
        if (!$device) {
            return ['success' => false, 'data' => __('DeviceNotFound'), 'message' => "Device not found"];
        }

        if (!$login_info = self::API_LOGIN($device)) {
            return ['success' => false, 'data' => __('Msg.ApiLoginFailed'), 'message' => "API Login failed. See logfile for details"];
        }

        $data = [];

        list($cookie, $api_version) = explode(";", $login_info);

        foreach (self::$available_apis as $key => $api) {
            $api_data = self::API_GET_DATA($device->hostname, $cookie, $api, $api_version);

            if ($api_data['success']) {
                $data[$key] = $api_data['data'];
            }
        }

        $data = [
            'informations' => self::formatSystemData($data['status']),
            'vlans' => self::formatVlanData($data['vlans']['vlan_element']),
            'ports' => self::formatPortData($data['ports']['port_element'], $data['portstats']['port_statistics_element']),
            'vlanports' => self::formatPortVlanData($data['vlanport']['vlan_port_element']),
            'statistics' => self::formatExtendedPortStatisticData($data['portstats']['port_statistics_element'], $data['ports']['port_element']),
            'macs' => self::formatMacTableData($data['mac-table']['mac_table_entry_element']),
            'uplinks' => self::formatUplinkData($data['ports']['port_element']),
            'success' => true,
        ];

        self::API_LOGOUT($device->hostname, $cookie, $api_version);

        return $data;
    }

    static function formatUplinkData($ports)
    {
        $uplinks = [];
        foreach ($ports as $port) {
            if (str_contains($port['trunk_group'], "Trk")) {
                $uplinks[$port['id']] = $port['trunk_group'];
            }
        }

        return $uplinks;
    }

    static function formatVlanData($vlans): array
    {
        $return = [];

        if (empty($vlans) or !is_array($vlans) or !isset($vlans)) {
            return $return;
        }

        foreach ($vlans as $vlan) {
            $return[$vlan['vlan_id']] = $vlan['name'];
        }

        return $return;
    }

    static function formatMacTableData($data, $vlans = [], $device = null, $cookie = null, $api_version = null): array
    {
        $return = [];

        foreach ($data as $mac) {
            $mac_filtered = str_replace([":", "-"], "", strtolower($mac['mac_address']));
            $return[$mac_filtered] = [
                'port' => $mac['port_id'],
                'mac' => $mac_filtered,
                'vlan' => $mac['vlan_id'],
            ];
        }


        return $return;
    }

    static function formatSystemData($system): array
    {
        if (empty($system) or !is_array($system) or !isset($system)) {
            return [];
        }

        $return = [
            'name' => $system['name'],
            'model' => $system['product_model'],
            'serial' => $system['serial_number'],
            'firmware' => $system['firmware_version'],
            'hardware' => $system['hardware_revision'],
            'mac' => strtolower(str_replace("-", "", $system['base_ethernet_address']['octets'])),
        ];

        return $return;
    }

    static function formatPortData(array $ports, array $stats): array
    {
        $return = [];

        if (empty($ports) or !is_array($ports) or !isset($ports)) {
            return $return;
        }

        foreach ($ports as $port) {
            $return[$port['id']] = [
                'name' => $port['name'],
                'id' => $port['id'],
                'link' => $port['is_port_up'],
                'trunk_group' => $port['trunk_group'],
                'vlan_mode' => "native-untagged"
            ];
        }

        foreach ($stats as $stat) {
            $return[$stat['id']]['speed'] = $stat['port_speed_mbps'];
        }

        return $return;
    }

    static function formatExtendedPortStatisticData($portstats, $portdata): array
    {
        $return = [];

        if (empty($portstats) or !is_array($portstats) or !isset($portstats)) {
            return $return;
        }

        foreach ($portstats as $port) {
            $return[$port['id']] = [
                "id" => $port['id'],
                "name" => $port['name'],
                "port_speed_mbps" => $port['port_speed_mbps'],
                "port_rx_packets" => $port['packets_tx'],
                "port_tx_packets" => $port['packets_rx'],
                "port_tx_bytes" => $port['bytes_tx'],
                "port_rx_bytes" => $port['bytes_rx'],
                "port_tx_errors" => $port['error_tx'],
                "port_rx_errors" => $port['error_rx'],
                "port_tx_bps" => $port['throughput_tx_bps'],
                "port_rx_bps" => $port['throughput_rx_bps'],
            ];
        }

        foreach($portdata as $port) {
            $return[$port['id']]['port_status'] = $port['is_port_up'];
        }

        return $return;
    }

    static function formatPortVlanData($vlanports): array
    {
        $return = [];

        if (empty($vlanports) or !is_array($vlanports) or !isset($vlanports)) {
            return $return;
        }

        $i = 0;
        foreach ($vlanports as $vlanport) {
            $return[$i] = [
                "port_id" => $vlanport['port_id'],
                "vlan_id" => $vlanport['vlan_id'],
                "is_tagged" => ($vlanport['port_mode'] == "POM_TAGGED_STATIC") ? true : false,
            ];

            $i++;
        }

        return $return;
    }

    static function createBackup($device): bool
    {
        if (!$login_info = self::API_LOGIN($device)) {
            BackupController::store(false, false, false, $device);
            return false;
        }

        list($cookie, $api_version) = explode(";", $login_info);

        $https = config('app.https');

        $api_url = $https . $device->hostname . '/rest/' . $api_version . '/cli';

        $backup = Http::withoutVerifying()->asJson()->withHeaders([
            'Cookie' => $cookie,
        ])->post($api_url, array(
            'cmd' => 'show running-config',
        ));

        self::API_LOGOUT($device->hostname, $cookie, $api_version);

        if ($backup->successful()) {
            $data = $backup->json()["result_base64_encoded"];
            $data = base64_decode($data);

            $restore = strstr($data, ";");

            if ($data !== NULL and strlen($data) > 10 and $data != false) {
                BackupController::store(true, $data, $restore, $device);
                return true;
            } else {
                BackupController::store(false, false, false, $device);
                return false;
            }
        } else {
            BackupController::store(false, false, false, $device);
            return false;
        }
    }

    static function restoreBackup($device, $backup, $password_switch): array
    {
        if ($password_switch != Crypt::decrypt($device->password)) {
            return ['success' => false, 'data' => 'Wrong password'];
        }

        if (!$login_info = self::API_LOGIN($device)) {
            return ['success' => false, 'data' => __('Msg.ApiLoginFailed')];
        }

        list($cookie, $api_version) = explode(";", $login_info);

        $https = config('app.https');

        $api_url = $https . $device->hostname . '/rest/' . $api_version . '/system/config/cfg_restore/payload';

        $restore = Http::connectTimeout(10)->withoutVerifying()->withHeaders([
            'Cookie' => $cookie,
        ])->post($api_url, [
            'config_base64_encoded' => base64_encode($backup->data),
            'is_forced_reboot_enabled' => false,
            'is_recoverymode_enabled' => false,
        ]);

        if (isset($restore->json()['status']) and $restore->json()['status'] == "CRS_SUCCESS") {
            return ['success' => true, 'data' => 'Restore successful'];
        }

        while (true) {
            sleep(4);
            $status = self::API_GET_DATA($device->hostname, $cookie, '/system/config/cfg_restore/payload/status', $api_version);
            $data = $status['data'];

            if (isset($data['status']) and $data['status'] == "CRS_SUCCESS") {
                return ['success' => true, 'data' => 'Restore successful'];
            }

            if (isset($data['status']) and $data['status'] != "CRS_IN_PROGRESS") {
                break;
            }
        }

        self::API_LOGOUT($device->hostname, $cookie, $api_version);

        return ['success' => false, 'data' => 'Restore failed: ' . $data['status'] . " " . $data['failure_reason']];
    }

    static function uploadPubkeys($device, $pubkeys = false): String
    {
        if (config('app.ssh_private_key')) {
            $private_key = Storage::disk('local')->get('ssh.key');

            if ($private_key === NULL) {
                return json_encode(['success' => 'false', 'message' => 'SSH Key Login Error']);
            }

            $decrypt = Crypt::decrypt(Storage::disk('local')->get('ssh.key'));

            if ($decrypt === NULL) {
                return json_encode(['success' => 'false', 'message' => 'Error decrypting SSH Key']);
            }

            $key = PublicKeyLoader::load($decrypt);
        } else {
            $key = Crypt::decrypt($device->password);
        }

        $keys = PublicKeyService::getPubkeysAsFile();

        try {
            $sftp = new SFTP($device->hostname);
            $sftp->login(config('app.ssh_username'), $key);
            $upload = $sftp->put('/ssh/mgr_keys/authorized_keys', $keys);
            $sftp->disconnect();

            if ($upload) {
                if ($login_info = self::API_LOGIN($device)) {
                    list($cookie, $api_version) = explode(";", $login_info);

                    $url = "authentication/ssh";

                    $data = '{
                        "auth_ssh_login": {
                            "primary_method": "PAM_PUBLIC_KEY"
                        },
                        "auth_ssh_enable": {
                            "primary_method": "PAM_PUBLIC_KEY"
                        }
                    }';

                    $response = self::API_PUT_DATA($device->hostname, $cookie, $url, $api_version, $data);

                    self::API_LOGOUT($device->hostname, $cookie, $api_version);

                    if ($response['success']) {
                        return json_encode(['success' => 'true', 'message' => __('Pubkeys.SyncAndEnable.Success')]);
                    } else {
                        return json_encode(['success' => 'false', 'message' => __('Pubkeys.SyncAndEnable.Error')]);
                    }
                }

                return json_encode(['success' => 'true', 'message' => __('Pubkeys.Sync.Success')]);
            }
        } catch (\Exception $e) {
            return json_encode(['success' => 'false', 'message' => 'Error SFTP connection: ' . $e->getMessage()]);
        }

        return json_encode(['success' => 'false', 'message' => 'Error sftp connection']);
    }

    static function setUntaggedVlanToPort($newVlan, $port, $device, $vlans, $need_login = true, $logindata = ""): array
    {
        $data = '{
            "vlan_id": ' . $vlans[$newVlan]['vlan_id'] . ', 
            "port_id": "' . $port->name . '", 
            "port_mode": "POM_UNTAGGED"
        }';

        if ($need_login) {
            $login_info = self::API_LOGIN($device);
        } else {
            $login_info = $logindata;
        }

        if ($login_info) {
            list($cookie, $api_version) = explode(";", $login_info);

            $result = self::API_POST_DATA($device->hostname, $cookie, self::$available_apis['vlanport'], $api_version, $data);

            if ($result['success']) {
                $old = DeviceVlanPort::where('device_id', $device->id)->where('device_port_id', $port->id)->where('is_tagged', false)->first()->device_vlan_id ?? false;

                $delete = false;

                if ($old) {
                    $delete = DeviceVlanPort::where('device_vlan_id', $old)->where('device_port_id', $port->id)->where('device_id', $device->id)->where('is_tagged', false)->delete();
                }

                $created = DeviceVlanPort::updateOrCreate(
                    ['device_id' => $device->id, 'device_port_id' => $port->id, 'device_vlan_id' => $vlans[$newVlan]['id'], 'is_tagged' => false],
                );

                Log::channel('database')->info(__('Log.Vlan.Untagged.Updated', ['vlan' => $vlans[$newVlan]['vlan_id'], 'port' => $port->name]), ['extra' => Auth::user()->name, 'context' => "Port"]);
                return ['success' => true, 'data' => ''];
            } else {
                Log::channel('database')->error(__('Log.Vlan.Untagged.NotUpdated', ['vlan' => $vlans[$newVlan]['vlan_id'], 'port' => $port->name]), ['extra' => Auth::user()->name, 'context' => "Port"]);

                return ['success' => false, 'data' => $result['data']];
            }

            if ($need_login) {
                self::API_LOGOUT($device->hostname, $cookie, $api_version);
            }
        }
    }

    static function setTaggedVlansToPort($taggedVlans, $port, $device, $vlans, $need_login = true, $logindata = ""): array
    {
        $return = [];
        $total = 0;

        if ($need_login) {
            $login_info = self::API_LOGIN($device);
        } else {
            $login_info = $logindata;
        }

        if ($login_info) {

            list($cookie, $api_version) = explode(";", $login_info);

            $alreadyTaggedVlans = $device->vlanports()->where('device_port_id', $port->id)->where('is_tagged', 1)->get()->keyBy('device_vlan_id')->toArray();    // Get all tagged vlans from port

            // Add vlan tagged to port
            foreach ($taggedVlans as $vlan) {
                if (!array_key_exists($vlan, $alreadyTaggedVlans)) {
                    $total++;

                    $data = '{
                            "vlan_id": ' . $vlans[$vlan]['vlan_id'] . ', 
                            "port_id": "' . $port->name . '", 
                            "port_mode": "POM_TAGGED_STATIC"
                        }';

                    $result = self::API_POST_DATA($device->hostname, $cookie, self::$available_apis['vlanport'], $api_version, $data);

                    if ($result['success']) {
                        $old = DeviceVlanPort::where('device_vlan_id', $vlan)->where('device_port_id', $port->id)->where('device_id', $device->id)->where('is_tagged', true)->first();
                        if ($old) {
                            $old->delete();
                        }

                        DeviceVlanPort::updateOrCreate(
                            ['device_id' => $device->id, 'device_port_id' => $port->id, 'device_vlan_id' => $vlans[$vlan]['id'], 'is_tagged' => true],
                        );

                        Log::channel('database')->info(__('Log.Vlan.Tagged.Updated', ['vlan' => $vlans[$vlan]['vlan_id'], 'port' => $port->name]), ['extra' => Auth::user()->name, 'context' => "Port " . $port->name . " | Device " . $device->hostname]);
                        $return[] = ['success' => true, 'data' => ''];
                    } else {
                        Log::channel('database')->error(__('Log.Vlan.Tagged.NotUpdated', ['vlan' => $vlans[$vlan]['vlan_id'], 'port' => $port->name]), ['extra' => Auth::user()->name, 'context' => "Port " . $port->name . " | Device " . $device->hostname]);
                        $return[] = ['success' => false, 'data' => $result['data']];

                        Log::channel('database')->error("Error adding tagged vlan " . $vlans[$vlan]['name'] . " to port " . $port->name . ": " . $result['data'], ['extra' => Auth::user()->name, 'context' => "Port " . $port->name . " | Device " . $device->hostname]);
                    }
                }
            }

            // Remove not needed tagged vlans from port
            if (count($taggedVlans) < count($alreadyTaggedVlans)) {
                foreach ($alreadyTaggedVlans as $device_vlan_id => $vlan) {
                    if (!in_array($device_vlan_id, $taggedVlans)) {
                        $total++;

                        $data = '{
                                "vlan_id": ' . $vlans[$device_vlan_id]['vlan_id'] . ', 
                                "port_id": "' . $port->name . '", 
                                "port_mode": "POM_TAGGED_STATIC"
                            }';

                        $result = self::API_DELETE_DATA($device->hostname, $cookie, self::$available_apis['vlanport'], $api_version, $data);

                        if ($result['success']) {

                            $old = DeviceVlanPort::where('device_vlan_id', $device_vlan_id)->where('device_port_id', $port->id)->where('device_id', $device->id)->first();
                            if ($old) {
                                $old->delete();
                            }

                            // $return[] = ['success' => true, 'data' => ''];

                            Log::channel('database')->info(__('Log.Vlan.Tagged.Removed', ['vlan' => $vlans[$device_vlan_id]['vlan_id'], 'port' => $port->name]), ['extra' => Auth::user()->name]);
                        } else {
                            // $return[] = ['success' => false, 'data' => $result['data']];
                        }
                    }
                }
            }

            if ($need_login) {
                proc_open('php ' . base_path() . '/artisan device:refresh ' . $device->id . ' > /dev/null &', [], $pipes);
                self::API_LOGOUT($device->hostname, $cookie, $api_version);
            }

            return $return;
        }
    }

    static function syncVlans($syncable_vlans, $current_vlans, $device, $create_vlans, $rename_vlans, $testmode): array
    {

        $start = microtime(true);
        $not_found = $chg_name = [];
        $i_not_found = $i_chg_name = $i_vlan_created = $i_vlan_chg_name = 0;
        $return = [];

        foreach ($syncable_vlans as $key => $vlan) {
            if (!array_key_exists($key, $current_vlans)) {
                $not_found[$key] = $vlan;
                $i_not_found++;
            } else {
                if ($vlan['name'] != $current_vlans[$key]['name']) {
                    $chg_name[$key] = $vlan;
                    $i_chg_name++;
                }
            }
        }

        if ($i_not_found == 0 && $i_chg_name == 0) {
            return [];
        }

        if (!$testmode && !$login_info = self::API_LOGIN($device)) {
            return $return;
        }

        if (!$testmode) {
            list($cookie, $api_version) = explode(";", $login_info);
        }

        if ($create_vlans) {
            foreach ($not_found as $key => $vlan) {
                $return[$vlan->vid]['name'] = $vlan->name;

                $data = '{
                    "vlan_id": ' . $vlan['vid'] . ',
                    "name": "' . $vlan['name'] . '"
                }';

                if (!$testmode) {
                    $response = self::API_POST_DATA($device->hostname, $cookie, "vlans", $api_version, $data);
                } else {
                    $response = ['success' => true];
                }

                if ($response['success']) {
                    $i_vlan_created++;
                }

                $return[$vlan->vid]['created'] = $response['success'];
            }
        }

        if ($rename_vlans) {
            foreach ($chg_name as $vlan) {
                $return[$vlan->vid]['old'] = $current_vlans[$vlan->vid]['name'];
                $return[$vlan->vid]['name'] = $current_vlans[$vlan->vid]['name'];

                $data = '{
                    "vlan_id": ' . $vlan['vid'] . ', 
                    "name": "' . $vlan['name'] . '"
                }';

                if (!$testmode) {
                    $response = self::API_PUT_DATA($device->hostname, $cookie, "vlans/" . $vlan['vid'], $api_version, $data);
                } else {
                    $response = ['success' => true];
                }

                if ($response['success']) {
                    $i_vlan_chg_name++;
                    $return[$vlan->vid]['name'] = $vlan->name;
                }

                $return[$vlan['vid']]['changed'] = ($response['success']) ? true : false;
            }
        }

        if (!$testmode) {
            $logdata = [
                "summary" => [
                    "created" => $i_vlan_created, 
                    "renamed" => $i_vlan_chg_name
                ],
                "vlans" => $return
            ];
    
            CLog::info("Sync", "VLANs synced on device " . $device->name, $device, json_encode($logdata));

            self::API_LOGOUT($device->hostname, $cookie, $api_version);
        }

        return $return;
    }

    static function setPortName($port, $name, $device, $logininfo)
    {
        list($cookie, $api_version) = explode(";", $logininfo);

        $data = '{
            "id" : "' . $port . '",
            "name": "' . $name . '"
        }';

        $response = self::API_PUT_DATA($device->hostname, $cookie, "ports/" . $port, $api_version, $data);

        if ($response['success']) {
            return true;
        } else {
            return false;
        }
    }
}
