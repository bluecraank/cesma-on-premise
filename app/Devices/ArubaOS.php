<?php

namespace App\Devices;

use App\Http\Controllers\BackupController;
use App\Interfaces\DeviceInterface;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\LogController;
use App\Models\DeviceVlan;
use App\Services\PublicKeyService;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Net\SFTP;

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
            }
        } catch (\Exception $e) {
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

    static function API_GET_DATA($hostname, $cookie, $api, $version): array
    {
        $api_url = config('app.https') . $hostname . '/rest/' . $version . '/' . $api;

        try {
            $response = Http::withoutVerifying()->withHeaders([
                'Content-Type' => 'application/json',
                'Cookie' => "$cookie",
            ])->get($api_url);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
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
            return ['success' => false, 'data' => __('DeviceNotFound')];
        }

        if (!$login_info = self::API_LOGIN($device)) {
            return ['success' => false, 'data' => __('Msg.ApiLoginFailed')];
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
            'statistics' => self::formatExtendedPortStatisticData($data['portstats']['port_statistics_element']),
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
        if(empty($system) or !is_array($system) or !isset($system)) {
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

    static function formatPortData(Array $ports, Array $stats): array
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
            ];
        }

        foreach ($stats as $stat) {
            $return[$stat['id']]['speed'] = $stat['port_speed_mbps'];
        }

        return $return;
    }

    static function formatPortSimpleStatisticData($portstats): array
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
            ];
        }

        return $return;
    }

    static function formatExtendedPortStatisticData($portstats): array
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

            if($private_key === NULL) {
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

    static function setUntaggedVlanToPort($vlans, $ports, $device, $need_login = true, $logindata = ""): String
    {

        $success = $failed = 0;
        $failed_ports = [];
        $portcount = count($ports);

        $vlans_in_db = DeviceVlan::all()->keyBy('id');

        if($need_login) {
            $login_info = self::API_LOGIN($device);
        } else {
            $login_info = $logindata;
        }

        if ($login_info) {

            list($cookie, $api_version) = explode(";", $login_info);

            foreach ($ports as $key => $port) {
                $data = '{
                    "vlan_id": ' . $vlans_in_db[$vlans[$key]]->vlan_id . ', 
                    "port_id": "' . $port . '", 
                    "port_mode": "POM_UNTAGGED"
                }';

                $result = self::API_POST_DATA($device->hostname, $cookie, self::$available_apis['vlanport'], $api_version, $data);

                if ($result['success']) {
                    $port_id = $device->ports()->where('name', $port)->first()->id;
                    $vlan_id = $device->vlanports()->where('device_port_id', $port_id)->where('is_tagged', 0)->first()->device_vlan_id ?? 0;
                    $device->vlanports()->where('device_port_id', $port_id)->where('device_vlan_id', $vlan_id)->update(['device_vlan_id' => $vlans[$key]]);

                    // LogController::log('Port aktualisiert', '{"switch": "' .  $device->name . '", "info": "Untagged VLAN geändert", "port": "' . $port . '", "vlan": "' . $vlans[$key] . '"}');
                    $success++;
                } else {
                    $failed++;
                    $failed_ports[] = __('Vlan.Update.Error.Untagged', ['port' => $port, 'vlan' => $vlans_in_db[$vlans[$key]]->vlan_id]);
                }
            }

            if ($failed !== count($ports) and $need_login) {
                proc_open('php ' . base_path() . '/artisan device:refresh ' . $device->id . ' > /dev/null &', [], $pipes);
            }

            if($need_login) {
                self::API_LOGOUT($device->hostname, $cookie, $api_version);
            }

            if ($success == $portcount) {
                return json_encode(['success' => 'true', 'message' => __('Vlan.Update.Success', ['success' => $success, 'total' => $portcount])]);
            } else {
                return json_encode(['success' => 'false', 'message' => __('Vlan.Update.Error', ['failed' => $failed, 'total' => count($ports)]) . "
                " . implode("
                ", $failed_ports)]);
            }
        }

        return json_encode(['success' => 'false', 'message' => __('Msg.ApiLoginFailed')]);
    }

    static function setTaggedVlanToPort($vlans, $port, $device, $need_login = true, $logindata = ""): array
    {
        $return = [];
        if($need_login) {
            $login_info = self::API_LOGIN($device);
        } else {
            $login_info = $logindata;
        }

        if ($login_info) {

            list($cookie, $api_version) = explode(";", $login_info);

            $port_id = $device->ports()->where('name', $port)->first()->id;
            $alreadyTaggedVlans = $device->vlanports()->where('device_port_id', $port_id)->where('is_tagged', 1)->get()->keyBy('device_vlan_id')->toArray();    // Get all tagged vlans from port
            $known_vlans = $device->vlans()->get()->keyBy('id')->toArray();    // Get all known vlans from device

            // Add vlan tagged to port
            foreach ($vlans as $vlan) {
                if (!array_key_exists($vlan, $alreadyTaggedVlans)) {
                    $data = '{
                            "vlan_id": ' . $known_vlans[$vlan]['vlan_id'] . ', 
                            "port_id": "' . $port . '", 
                            "port_mode": "POM_TAGGED_STATIC"
                        }';

                    $result = self::API_POST_DATA($device->hostname, $cookie, self::$available_apis['vlanport'], $api_version, $data);

                    if ($result['success']) {
                        // LogController::log('Port aktualisiert', '{"switch": "' .  $device->name . '", "info": "Tagged VLAN hinzugefügt", "port": "' . $port . '", "vlan": "' . $vlan . '"}');

                        $return[] = [
                            'success' => true,
                            'message' => __('Vlan.Update.Success.Tagged', ['port' => $port, 'vlan' => $known_vlans[$vlan]['vlan_id']]),
                        ];
                    } else {
                        $return[] = [
                            'success' => false,
                            'message' => __('Vlan.Update.Error.Tagged', ['port' => $port, 'vlan' => $known_vlans[$vlan]['vlan_id']]),
                        ];
                    }
                }
            }

            // Remove not needed tagged vlans from port
            if (count($vlans) < count($alreadyTaggedVlans)) {
                foreach ($alreadyTaggedVlans as $device_vlan_id => $vlan) {
                    if (!in_array($device_vlan_id, $vlans)) {
                        $data = '{
                                "vlan_id": ' . $known_vlans[$device_vlan_id]['vlan_id'] . ', 
                                "port_id": "' . $port . '", 
                                "port_mode": "POM_TAGGED_STATIC"
                            }';

                        $result = self::API_DELETE_DATA($device->hostname, $cookie, self::$available_apis['vlanport'], $api_version, $data);

                        if ($result['success']) {
                            // LogController::log('Port aktualisiert', '{"switch": "' .  $device->name . '", "info": "Tagged VLAN hinzugefügt", "port": "' . $port . '", "vlan": "' . $vlan . '"}');

                            $return[] = [
                                'success' => true,
                                'message' => __('Vlan.Update.Success.Remove.Tagged', ['port' => $port, 'vlan' => $known_vlans[$device_vlan_id]['vlan_id']]),
                            ];
                        } else {
                            $return[] = [
                                'success' => false,
                                'message' => __('Vlan.Update.Error.Remove.Tagged', ['port' => $port, 'vlan' => $known_vlans[$device_vlan_id]['vlan_id']]),
                            ];
                        }
                    }
                }
            }

            $device->vlanports()->where('device_port_id', $port_id)->where('is_tagged', true)->delete();

            if($need_login) {
                proc_open('php ' . base_path() . '/artisan device:refresh ' . $device->id . ' > /dev/null &', [], $pipes);
                self::API_LOGOUT($device->hostname, $cookie, $api_version);
            }

            return $return;
        }

        $return[] = [
            'success' => false,
            'message' => __('Msg.ApiLoginFailed'),
        ];

        return $return;
    }

    static function syncVlans($syncable_vlans, $current_vlans, $device, $create_vlans, $rename_vlans, $testmode): array
    {

        $start = microtime(true);
        $not_found = $chg_name = $return = [];
        $return['log'] = [];

        $i_not_found = $i_chg_name = $i_vlan_created = $i_vlan_chg_name = 0;

        $return['log'][] = "<b>" . __('Vlan.Sync.Jobs') . "</b></br>";

        foreach ($syncable_vlans as $key => $vlan) {
            if (!array_key_exists($key, $current_vlans)) {
                $not_found[$key] = $vlan;
                $return['log'][] = "<span class='tag is-link'>VLAN $key</span> " . __('Vlan.Sync.Create');
                $i_not_found++;
            } else {
                if ($vlan['name'] != $current_vlans[$key]['name']) {
                    $chg_name[$key] = $vlan;
                    $return['log'][] = "<span class='tag is-link'>VLAN $key</span> " . __('Vlan.Sync.Name') . " ({$current_vlans[$key]['name']} => {$vlan['name']})";
                    $i_chg_name++;
                }
            }
        }

        if ($i_not_found == 0 && $i_chg_name == 0) {
            $return['log'][] = __('Vlan.Sync.NoJobs');
            $return['time'] = number_format(microtime(true) - $start, 2);
            return $return;
        }

        $return['log'][] = "</br><b>[Log]</b></br>";

        if (!$testmode) {
            if (!$login_info = self::API_LOGIN($device)) {
                $return['log'][] = "<span class='tag is-danger'>API</span> Login fehlgeschlagen";
                $return['time'] = number_format(microtime(true) - $start, 2);
                return $return;
            }

            list($cookie, $api_version) = explode(";", $login_info);

            if ($create_vlans) {
                foreach ($not_found as $key => $vlan) {
                    $data = '{
                        "vlan_id": ' . $vlan['vid'] . ',
                        "name": "' . $vlan['name'] . '"
                    }';

                    $response = self::API_POST_DATA($device->hostname, $cookie, "vlans", $api_version, $data);

                    if ($response['success']) {
                        $return['log'][] = "<span class='tag is-success'>VLAN {$vlan['vid']}</span> " . __('Vlan.Sync.Log.Created');
                        $i_vlan_created++;
                    } else {
                        $return['log'][] = "<span class='tag is-danger'>VLAN {$vlan['vid']}</span> " . __('Vlan.Sync.Log.NotCreated');
                    }
                }
            }

            if ($rename_vlans) {
                foreach ($chg_name as $vlan) {
                    $data = '{
                        "vlan_id": ' . $vlan['vid'] . ', 
                        "name": "' . $vlan['name'] . '"
                    }';

                    $response = self::API_PUT_DATA($device->hostname, $cookie, "vlans/" . $vlan['vid'], $api_version, $data);

                    if ($response['success']) {
                        $return['log'][] = "<span class='tag is-success'>VLAN {$vlan['vid']}</span> " . __('Vlan.Sync.Log.Renamed');
                        $i_vlan_chg_name++;
                    } else {
                        $return['log'][] = "<span class='tag is-danger'>VLAN {$vlan['vid']}</span> " . __('Vlan.Sync.Log.NotRenamed');
                    }
                }
            }

            self::API_LOGOUT($device->hostname, $cookie, $api_version);

            proc_open('php ' . base_path() . '/artisan device:refresh ' . $device->id . ' > /dev/null &', [], $pipes);
        }

        $return['log'][] = "</br><b>[" . __('Vlan.Sync.Results') . "]</b></br>";
        $return['log'][] = __('Vlan.Sync.Result.Created', ['created' => $i_vlan_created, 'total' => $i_not_found]);
        $return['log'][] = __('Vlan.Sync.Result.Renamed', ['renamed' => $i_vlan_chg_name, 'total' => $i_chg_name]);
        $return['time'] = number_format(microtime(true) - $start, 2);

        return $return;
    }

    static function setPortName($port, $name, $device, $logininfo) {
        list($cookie, $api_version) = explode(";", $logininfo);

        $data = '{
            "id" : "' . $port . '",
            "name": "' . $name . '"
        }';

        $response = self::API_PUT_DATA($device->hostname, $cookie, "ports/" . $port, $api_version, $data);

        if ($response['success']) {
            $device->ports()->where('name', $port)->update(['description' => $name]);
            return json_encode(['success' => "true", 'message' => __('Msg.ApiPortNameSet')]);
        } else {
            return json_encode(['success' => "false", 'message' => __('Msg.ApiPortNameNotSet')]);
        }
    }
}
