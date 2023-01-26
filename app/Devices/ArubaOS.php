<?php

namespace App\Devices;

use App\Http\Controllers\BackupController;
use App\Http\Controllers\DeviceController;
use App\Interfaces\DeviceInterface;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\EncryptionController;
use App\Http\Controllers\KeyController;
use App\Http\Controllers\LogController;
use App\Models\Backup;
use App\Services\DeviceService;
use Illuminate\Support\Facades\Crypt;
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

    static function API_LOGIN($device): string {
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
            return ['success' => false, 'data' => []];
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
            return ['success' => false, 'data' => []];
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
            return ['success' => false, 'data' => []];
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
            return ['success' => false, 'data' => []];
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
            return ['success' => false, 'data' => []];
        }
    }

    static function API_REQUEST_ALL_DATA($device): array
    {
        if (!$device) {
            return ['success' => false, 'data' => 'Device not found'];
        }

        if (!$login_info = self::API_LOGIN($device)) {
            return ['success' => false, 'data' => 'Login failed'];
        }

        $data = [];

        list($cookie, $api_version) = explode(";", $login_info);

        foreach (self::$available_apis as $key => $api) {
            $api_data = self::API_GET_DATA($device->hostname, $cookie, $api, $api_version);

            $data[$key] = "[]";
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
            'macs' => self::formatMacTableData($data['mac-table']['mac_table_entry_element'], $device, $cookie, $api_version),
            'uplinks' => self::formatUplinkData($data['ports']['port_element']),
        ];

        DeviceService::storeApiData($data, $device);

        self::API_LOGOUT($device->hostname, $cookie, $api_version);
        
        return [];
    }
    
    static function formatUplinkData($ports) {
        $uplinks = [];
        foreach($ports as $port) {
            if(str_contains($port['trunk_group'], "Trk")) {
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

    static function formatMacTableData($data): array
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
            ];
        }

        foreach($stats as $stat) {
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

    static function getDeviceTrunks($device): array
    {
        $trunks = [];
        $ports = json_decode($device->port_data, true);

        if (!$ports || $ports == "" || $ports == []) {
            return [];
        }

        foreach ($ports as $port) {
            if (str_contains($port['id'], "Trk")) {
                $trunks[] = $port['id'];
            }
        }

        return $trunks;
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
            $data = str_replace("Running configuration:", "", $data);
            $data = strstr($data, ";") or $data;
            if ($data !== NULL and strlen($data) > 10 and $data != false) {
                BackupController::store(true, $data, $device);
                return true;
            } else {
                BackupController::store(false, false, $device);
                return false;
            }
        } else {
            Backup::create([
                'device_id' => $device->id,
                'data' => "No data received",
                'status' => 0,
            ]);
            return false;
        }
    }

    static function restoreBackup($device, $backup, $password_switch): array
    {
        if ($password_switch != EncryptionController::decrypt($device->password)) {
            return ['success' => false, 'data' => 'Switch password incorrect'];
        }

        if (!$login_info = self::API_LOGIN($device)) {
            return ['success' => false, 'data' => 'API Login failed'];
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
            $decrypt = EncryptionController::getPrivateKey();
            if ($decrypt !== NULL) {
                $key = PublicKeyLoader::load($decrypt);
            } else {
                return json_encode(['success' => 'false', 'message' => 'Error private key']);
            }
        } else {
            $key = EncryptionController::decrypt($device->password);
        }

        $keys = KeyController::getPubkeysAsText();
        if ($keys != "") {
            try {
                $sftp = new SFTP($device->hostname);
                $sftp->login(config('app.ssh_username'), "fridolin");
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
                            return json_encode(['success' => 'true', 'message' => "Uploaded and aaa configured"]);
                        }
                    }
                }

                return json_encode(['success' => 'true', 'message' => $upload]);
            } catch (\Exception $e) {
                return json_encode(['success' => 'false', 'message' => 'Error sftp connection ' . $e->getMessage()]);
            }
        }

        return json_encode(['success' => 'false', 'message' => 'Error sftp connection']);
    }

    static function setUntaggedVlanToPort($vlans, $ports, $device): String
    {

        $success = 0;
        $failed = 0;
        $failed_ports = [];
        $portcount = count($ports);

        if ($login_info = self::API_LOGIN($device)) {

            list($cookie, $api_version) = explode(";", $login_info);

            foreach ($ports as $key => $port) {
                $data = '{
                    "vlan_id": ' . $vlans[$key] . ', 
                    "port_id": "' . $port . '", 
                    "port_mode":"POM_UNTAGGED"
                }';

                $result = self::API_POST_DATA($device->hostname, $cookie, self::$available_apis['vlanport'], $api_version, $data);

                if ($result['success']) {
                    LogController::log('Port aktualisiert', '{"switch": "' .  $device->name . '", "info": "Untagged VLAN geändert", "port": "' . $port . '", "vlan": "' . $vlans[$key] . '"}');

                    $success++;
                } else {
                    $failed++;
                    $failed_ports[] = "[" . $port . "] => VLAN-" . $vlans[$key];
                }
            }

            if ($failed !== count($ports)) {
                $newVlanPortData = self::API_GET_DATA($device->hostname, $cookie, self::$available_apis['vlanport'], $api_version)['data'];
                $device->vlan_port_data =  self::formatPortVlanData($newVlanPortData['vlan_port_element']);
                $device->save();
            }

            self::API_LOGOUT($device->hostname, $cookie, $api_version);

            if ($success == $portcount) {
                return json_encode(['success' => 'true', 'message' => 'Updated ' . $success . ' of ' . $portcount . ' ports']);
            } else {
                return json_encode(['success' => 'false', 'message' => 'Failed to update ' . $failed . ' of ' . count($ports) . ' ports<br> ' . implode("<br>", $failed_ports)]);
            }
        }

        return json_encode(['success' => 'false', 'message' => 'API Login failed']);
    }

    static function setTaggedVlanToPort($vlans, $port, $device): array
    {
        if ($login_info = self::API_LOGIN($device)) {

            list($cookie, $api_version) = explode(";", $login_info);

            $return = [];

            $compare = [];
            $alreadyTaggedVlans = json_decode($device->vlan_port_data);
            foreach ($alreadyTaggedVlans as $tagged) {
                if ($tagged->is_tagged == 1 && $tagged->port_id == $port)
                    $compare[] = $tagged->vlan_id;
            }

            // Add vlan tagged to port
            foreach ($vlans as $vlan) {
                if (!in_array($vlan, $compare)) {
                    $data = '{
                            "vlan_id": ' . $vlan . ', 
                            "port_id": "' . $port . '", 
                            "port_mode":"POM_TAGGED_STATIC"
                        }';

                    $result = self::API_POST_DATA($device->hostname, $cookie, self::$available_apis['vlanport'], $api_version, $data);

                    if ($result['success']) {
                        LogController::log('Port aktualisiert', '{"switch": "' .  $device->name . '", "info": "Tagged VLAN hinzugefügt", "port": "' . $port . '", "vlan": "' . $vlan . '"}');

                        $return[] = [
                            'success' => true,
                            'message' => '[' . $port . '] Tagged VLAN ' . $vlan,
                        ];
                    } else {
                        $return[] = [
                            'success' => false,
                            'message' => '[' . $port . '] Not Tagged VLAN ' . $vlan,
                        ];
                    }
                }
            }

            // Remove vlan tagged from port
            if (count($vlans) < count($compare)) {
                foreach ($compare as $vlan) {
                    if (!in_array($vlan, $vlans)) {
                        $data = '{
                                "vlan_id": ' . $vlan . ', 
                                "port_id": "' . $port . '", 
                                "port_mode":"POM_TAGGED_STATIC"
                            }';

                        $result = self::API_DELETE_DATA($device->hostname, $cookie, self::$available_apis['vlanport'], $api_version, $data);

                        if ($result['success']) {
                            LogController::log('Port aktualisiert', '{"switch": "' .  $device->name . '", "info": "Tagged VLAN entfernt", "port": "' . $port . '", "vlan": "' . $vlan . '"}');

                            $return[] = [
                                'success' => true,
                                'message' => '[' . $port . '] Tagged VLAN ' . $vlan . ' removed',
                            ];
                        } else {
                            $return[] = [
                                'success' => false,
                                'message' => '[' . $port . '] Tagged VLAN ' . $vlan . ' not removed',
                            ];
                        }
                    }
                }
            }

            $newVlanPortData = self::API_GET_DATA($device->hostname, $cookie, self::$available_apis['vlanport'], $api_version)['data'];
            $device->vlan_port_data =  self::formatPortVlanData($newVlanPortData['vlan_port_element']);
            $device->save();

            self::API_LOGOUT($device->hostname, $cookie, $api_version);
            return $return;
        }

        $return[] = [
            'success' => false,
            'message' => 'API Login failed',
        ];

        return $return;
    }

    static function syncVlans($vlans, $vlans_switch, $device, $create_vlans, $overwrite, $test): array
    {

        $start = microtime(true);
        $not_found = [];
        $chg_name = [];
        $return = [];
        $return['log'] = [];

        $i_not_found = 0;
        $i_chg_name = 0;

        $i_vlan_created = 0;
        $i_vlan_chg_name = 0;

        $return['log'][] = "<b>[Aufgaben]</b></br>";
        foreach ($vlans as $key => $vlan) {
            if (!array_key_exists($key, $vlans_switch)) {
                $not_found[$key] = $vlan;
                $return['log'][] = "<span class='tag is-link'>VLAN $key</span> VLAN erstellen";
                $i_not_found++;
            } else {
                if ($vlan->name != $vlans_switch[$key]['name']) {
                    $chg_name[$key] = $vlan;
                    $return['log'][] = "<span class='tag is-link'>VLAN $key</span> Name ändern ({$vlans_switch[$key]['name']} => {$vlan->name})";
                    $i_chg_name++;
                }
            }
        }

        if ($i_not_found == 0 && $i_chg_name == 0) {
            $return['log'][] = "Keine Aufgaben";
            $return['time'] = number_format(microtime(true) - $start, 2);
            return $return;
        }

        $return['log'][] = "</br><b>[Log]</b></br>";

        if (!$test) {
            if (!$login_info = self::API_LOGIN($device)) {
                $return['log'][] = "<span class='tag is-danger'>API</span> Login fehlgeschlagen";
                $return['time'] = number_format(microtime(true) - $start, 2);
                return $return;
            }

            list($cookie, $api_version) = explode(";", $login_info);

            if ($create_vlans) {
                foreach ($not_found as $key => $vlan) {
                    $data = '{
                        "vlan_id": ' . $vlan->vid . ',
                        "name": "' . $vlan->name . '"
                    }';

                    $response = self::API_POST_DATA($device->hostname, $cookie, "vlans", $api_version, $data);

                    if ($response['success']) {
                        $return['log'][] = "<span class='tag is-success'>VLAN {$vlan->vid}</span> erfolgreich erstellt";
                        $i_vlan_created++;
                    } else {
                        $return['log'][] = "<span class='tag is-danger'>VLAN {$vlan->vid}</span> konnte nicht erstellt werden";
                    }
                }
            }

            if ($overwrite) {
                foreach ($chg_name as $vlan) {
                    $data = '{
                        "vlan_id": ' . $vlan->vid . ', 
                        "name": "' . $vlan->name . '"
                    }';

                    $response = self::API_PUT_DATA($device->hostname, $cookie, "vlans/" . $vlan->vid, $api_version, $data);

                    if ($response['success']) {
                        $return['log'][] = "<span class='tag is-success'>VLAN {$vlan->vid}</span> erfolgreich umbenannt";
                        $i_vlan_chg_name++;
                    } else {
                        $return['log'][] = "<span class='tag is-danger'>VLAN {$vlan->vid}</span> konnte nicht umbenannt werden";
                    }
                }
            }

            self::API_LOGOUT($device->hostname, $cookie, $api_version);
            DeviceController::refresh($device);
        }

        $return['log'][] = "</br><b>[Ergebnisse]</b></br>";
        $return['log'][] = $i_vlan_created . " von " . $i_not_found . " VLANs erstellt";
        $return['log'][] = $i_vlan_chg_name . " von " . $i_chg_name . " VLANs umbenannt";

        $return['time'] = number_format(microtime(true) - $start, 2);
        return $return;
    }
}
