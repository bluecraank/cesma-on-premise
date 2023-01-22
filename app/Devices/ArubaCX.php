<?php

namespace App\Devices;

use App\Http\Controllers\BackupController;
use App\Interfaces\IDevice;
use Illuminate\Support\Facades\Http;
use App\Http\Controllers\EncryptionController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\MacAddressController;
use App\Http\Controllers\PortstatsController;

class ArubaCX implements IDevice
{
    static $api_auth = [
        "login" => "login",
        "logout" => "logout",
    ];

    static $available_apis = [
        "status" => '/system?attributes=software_version,subsystems,hostname&depth=3',
        "subsystem" => 'system/subsystems/chassis,1?attributes=product_info',
        "vlans" => 'system/vlans?attributes=name,id&depth=2',
        "ports" => 'system/interfaces?attributes=ifindex,link_state,description&depth=2',
        "portstats" => 'system/interfaces?attributes=ifindex,rate_statistics,statistics,link_speed,description&depth=2',
        "vlanport" => 'system/interfaces?attributes=ifindex,vlan_mode,vlan_tag,vlan_trunks&depth=2',
    ];

    static $port_if_uri = "system/interfaces/1%2F1%2F";

    static function GET_API_VERSIONS($hostname): string
    {
        $https = config('app.https');
        $url = $https . $hostname . '/rest';

        try {
            $versions = Http::withoutVerifying()->get($url);

            if ($versions->successful()) {
                $versionsFound = $versions->json()['latest'];
                return $versionsFound['version'];
            }
        } catch (\Exception $e) {
        }

        return "v10.04";
    }

    static function API_LOGIN($device): string
    {
        $api_version = self::GET_API_VERSIONS($device->hostname);
        $api_url = config('app.https') . $device->hostname . '/rest/' . $api_version . '/' . self::$api_auth['login'];

        $api_username = config('app.api_username');
        $api_password = EncryptionController::decrypt($device->password);

        try {
            $response = Http::connectTimeout(3)->withoutVerifying()->asForm()->post($api_url, [
                'username' => $api_username,
                'password' => $api_password,
            ]);

            // Return cookie if login was successful
            if ($response->successful() and !empty($response->header('Set-Cookie'))) {
                return $response->cookies()->toArray()[0]['Name'] . "=" . $response->cookies()->toArray()[0]['Value'] . ";" . $api_version;
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
                'Cookie' => "$cookie",
            ])->post($api_url);

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
                return ['success' => false, 'data' => "Error while fetching $api"];
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
            return ['success' => false, 'data' => $e->getMessage()];
        }
    }

    static function API_DELETE_DATA($hostname, $cookie, $api, $version, $data): array
    {
        $api_url = config('app.https') . $hostname . '/rest/' . $version . '/' . $api;

        try {
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

        $system_data = self::formatSystemData($data['status']);
        $vlan_data = self::formatVlanData($data['vlans']);
        $port_data = self::formatPortData($data['ports']);
        $portstat_data = self::formatPortSimpleStatisticData($data['portstats']);
        $vlanport_data = self::formatPortVlanData($data['vlanport']);
        $mac_data = self::formatMacTableData($data['vlans'], $device, $cookie, $api_version);
        PortstatsController::store(self::formatExtendedPortStatisticData($data['portstats']), $device->id);


        self::API_LOGOUT($device->hostname, $cookie, $api_version);

        return [
            'sysstatus_data' => $system_data,
            'vlan_data' => $vlan_data,
            'ports_data' => $port_data,
            'portstats_data' => $portstat_data,
            'vlanport_data' => $vlanport_data,
            'mac_table_data' => $mac_data,
        ];
    }

    static function formatVlanData($vlans): array
    {
        $return = [];

        if (empty($vlans) or !is_array($vlans) or !isset($vlans)) {
            return $return;
        }

        foreach ($vlans as $vlan) {
            $return[$vlan['id']] = [
                'name' => $vlan['name'],
                'vlan_id' => $vlan['id'],
            ];
        }

        return $return;
    }

    static function formatMacTableData($vlans, $device, $cookie, $api_version): array
    {
        $vlan_macs = [];
        foreach ($vlans as $vlan) {
            $url = "system/vlans/" . $vlan['id'] . "/macs?attributes=port,mac_addr&depth=2";
            $api_data = self::API_GET_DATA($device->hostname, $cookie, $url, $api_version);
            if ($api_data['success']) {
                $vlan_macs[$vlan['id']] = $api_data['data'];
            }
        }

        $return = [];
        foreach ($vlan_macs as $key => $macs) {
            foreach ($macs as $mac) {
                $mac_filtered = str_replace(":", "", strtolower($mac['mac_addr']));
                $return[$mac_filtered] = [
                    'port' => explode("/", key($mac['port']))[2],
                    'mac' => $mac_filtered,
                    'vlan' => $key,
                ];
            }
        }

        return $return;
    }

    static function formatSystemData($system): array
    {
        if (isset($system['hostname']) and $system['hostname'] != "") {
            $return = [
                'name' => "AOS-UNKNOWN",
                'model' => "Unknown",
                'serial' => "Unknown",
                'firmware' => "Unknown",
                'hardware' => "Unknown",
                'mac' => "000000000000",
            ];
        }

        $return = [
            'name' => $system['hostname'],
            'model' => $system['subsystems']['chassis,1']['product_info']['product_name'],
            'serial' => $system['subsystems']['chassis,1']['product_info']['serial_number'],
            'firmware' => $system['software_version'],
            'hardware' => $system['subsystems']['chassis,1']['product_info']['part_number'],
            'mac' => strtolower(str_replace(":", "", $system['subsystems']['chassis,1']['product_info']['base_mac_address'])),
        ];

        return $return;
    }

    static function formatPortData(array $ports): array
    {
        $return = [];

        if (empty($ports) or !is_array($ports) or !isset($ports)) {
            return $return;
        }

        foreach ($ports as $port) {
            if ($port['ifindex'] < 1000) {
                $return[$port['ifindex']] = [
                    'name' => $port['description'],
                    'id' => $port['ifindex'],
                    'is_port_up' => ($port['link_state'] == "up") ? true : false,
                    'trunk_group' => null,
                ];
            }
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
            if ($port['ifindex'] < 1000) {
                $return[$port['ifindex']] = [
                    "id" => $port['ifindex'],
                    "name" => $port['description'],
                    "port_speed_mbps" => $port['link_speed'] / 1000000,
                ];
            }
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
            if ($port['ifindex'] < 1000) {

                $packets_tx = (isset($port['statistics']['tx_packets'])) ? $port['statistics']['tx_packets'] : 0;
                $packets_rx = (isset($port['statistics']['rx_packets'])) ? $port['statistics']['rx_packets'] : 0;
                $no_errors = (isset($port['statistics']['total_packets_no_errors'])) ? $port['statistics']['total_packets_no_errors'] : 0;
                $errors = $packets_rx + $packets_tx - $no_errors;

                $return[$port['ifindex']] = [
                    "id" => $port['ifindex'],
                    "name" => $port['description'] ?? '',
                    "port_speed_mbps" => ($port['link_speed'] != 0) ? $port['link_speed'] / 1000000 : 0,
                    "port_tx_packets" => $packets_tx,
                    "port_rx_packets" => $packets_rx,
                    "port_tx_bytes" => (isset($port['statistics']['tx_bytes'])) ? $port['statistics']['tx_bytes'] : 0,
                    "port_rx_bytes" => (isset($port['statistics']['rx_bytes'])) ? $port['statistics']['rx_bytes'] : 0,
                    "port_tx_errors" => $errors,
                    "port_rx_errors" => $errors,
                    "port_tx_bps" => (isset($port['rate_statistics']['tx_bytes_per_second'])) ? $port['rate_statistics']['tx_bytes_per_second'] : 0,
                    "port_rx_bps" => (isset($port['rate_statistics']['rx_bytes_per_second'])) ? $port['rate_statistics']['rx_bytes_per_second'] : 0,
                ];
            }
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
            if ($vlanport['ifindex'] < 1000) {
                if ($vlanport['vlan_mode'] == "native-untagged") { // Untagged und erlaubte als trunks
                    $untagged_vlan = $vlanport['vlan_tag'];
                    $tagged_vlans = $vlanport['vlan_trunks'];

                    if (is_array($tagged_vlans) and count($tagged_vlans) == 0) {
                        // Man kann davon ausgehen, dass es ein Trunk ist
                        $return[$i] = [
                            "port_id" => $vlanport['ifindex'],
                            "vlan_id" => "Trunk",
                            "is_tagged" => true,
                        ];
                        $i++;
                    }

                    foreach ($tagged_vlans as $tagged_key => $tagged) {
                        $return[$i] = [
                            "port_id" => $vlanport['ifindex'],
                            "vlan_id" => $tagged_key,
                            "is_tagged" => true,
                        ];
                        $i++;
                    }

                    $return[$i] = [
                        "port_id" => $vlanport['ifindex'],
                        "vlan_id" => key($untagged_vlan),
                        "is_tagged" => false,
                    ];
                    $i++;
                }
            }
        }

        return $return;
    }

    static function getDeviceTrunks($device): array
    {

        $trunks = [];
        $ports = json_decode($device->vlan_port_data, true);
        foreach ($ports as $port) {
            if (str_contains($port['vlan_id'], "Trunk")) {
                $trunks[] = $port['port_id'];
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

        $data = self::API_GET_DATA($device->hostname, $cookie, "configs/running-config", $api_version);

        self::API_LOGOUT($device->hostname, $cookie, $api_version);

        if ($data['success']) {
            $encoded = json_encode($data['data'], true);
            if (strlen($encoded) > 10) {
                BackupController::store(true, $data, $device);
                return true;
            } else {
                BackupController::store(false, false, $device);
                return false;
            }
        }

        return false;
    }

    static function restoreBackup($device, $backup, $password_switch): array
    {
        if ($password_switch != EncryptionController::decrypt($device->password)) {
            return ['success' => false, 'data' => 'Wrong password for switch'];
        }

        if (!$login_info = self::API_LOGIN($device)) {
            return ['success' => false, 'data' => 'Login failed'];
        }

        list($cookie, $api_version) = explode(";", $login_info);

        $https = config('app.https');

        $api_url = $https . $device->hostname . '/rest/' . $api_version . '/system/config/cfg_restore/payload';

        $restore = Http::withoutVerifying()->withHeaders([
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

    static function uploadPubkeys($device, $pubkeys): String
    {
        if (!$login_info = self::API_LOGIN($device)) {
            return json_encode(['success' => 'false', 'message' => 'Login failed']);
        }

        list($cookie, $api_version) = explode(";", $login_info);

        $https = config('app.https');

        $api_url = $https . $device->hostname . '/rest/' . $api_version . '/system/users/' . config('app.api_username');

        $upload = Http::withoutVerifying()->withHeaders([
            'Cookie' => $cookie,
            'Content-Type'  => 'application/json',
        ])->patch($api_url, array(
            'authorized_keys' => $pubkeys,
        ));

        // No need to enable public key auth, it's already enabled by default on ArubaCX
        // see https://www.arubanetworks.com/techdocs/AOS-CX/10.10/HTML/security_83xx-8400-9300-10000/Content/Chp_Loc_AAA/Loc_AAA_cmds/ssh-pub-key-aut62.htm

        self::API_LOGOUT($device->hostname, $cookie, $api_version);

        if ($upload->successful()) {
            return json_encode(['success' => 'true', 'message' => 'Pubkeys synced']);
        } else {
            return json_encode(['success' => 'false', 'message' => 'Pubkeys not synced']);
        }
    }

    static function setUntaggedVlanToPort($vlans, $ports, $device): String
    {
        $success = 0;
        $failed = 0;
        $portcount = count($ports);

        if ($login_info = self::API_LOGIN($device)) {

            list($cookie, $api_version) = explode(";", $login_info);

            $rest_vlans_uri = "/rest/" . $api_version . "/system/vlans/";

            foreach ($ports as $key => $port) {
                $data = '{
                    "vlan_mode": "native-untagged",
                    "vlan_tag": "' . $rest_vlans_uri . $vlans[$key] . '",
                    "vlan_trunks": [
                      "' . $rest_vlans_uri . $vlans[$key] . '"
                    ]
                  }';

                $uri = self::$port_if_uri . $port;
                $result = self::API_PATCH_DATA($device->hostname, $cookie, $uri, $api_version, $data);

                if ($result['success']) {
                    LogController::log('Port aktualisiert', '{"switch": "' .  $device->name . '", "info": "Untagged VLAN geändert", "port": "' . $port . '", "vlan": "' . $vlans[$key] . '"}');

                    $success++;
                } else {
                    $failed++;
                }
            }

            if ($failed !== count($ports)) {
                $newVlanPortData = self::API_GET_DATA($device->hostname, $cookie, self::$available_apis['vlanport'], $api_version)['data'];
                $device->vlan_port_data =  self::formatPortVlanData($newVlanPortData);
                $device->save();
            }

            self::API_LOGOUT($device->hostname, $cookie, $api_version);

            if ($success == $portcount) {
                return json_encode(['success' => 'true', 'message' => 'Updated ' . $success . ' of ' . $portcount . ' ports']);
            } else {
                return json_encode(['success' => 'false', 'message' => 'Failed to update ' . $failed . ' of ' . count($ports) . ' ports']);
            }
        }

        return json_encode(['success' => 'false', 'message' => 'Login failed']);
    }

    static function setTaggedVlanToPort($vlans, $port, $device): array
    {
        $return = [];
        $uri = "system/interfaces/1%2F1%2F" . $port;

        if ($login_info = self::API_LOGIN($device)) {

            list($cookie, $api_version) = explode(";", $login_info);

            $data_builder = [];
            $data_builder['vlan_mode'] = "native-untagged";

            $rest_vlans_uri = "/rest/" . $api_version . "/system/vlans/";

            $alreadyTaggedVlans = json_decode($device->vlan_port_data, true);
            foreach ($alreadyTaggedVlans as $taggedVlan) {
                if (!$taggedVlan['is_tagged'] and $taggedVlan['port_id'] == $port) {
                    $data_builder['vlan_trunks'][] = $rest_vlans_uri . $taggedVlan['vlan_id'];
                    $data_builder['vlan_tag'] = $rest_vlans_uri . $taggedVlan['vlan_id'];
                }
            }

            foreach ($vlans as $vlan) {
                if (!in_array($rest_vlans_uri . $vlan, $data_builder['vlan_trunks'])) {
                    $data_builder['vlan_trunks'][] = $rest_vlans_uri . $vlan;
                }
            }

            $data = json_encode($data_builder);

            $uri = self::$port_if_uri . $port;
            $result = self::API_PUT_DATA($device->hostname, $cookie, $uri, $api_version, $data);

            if ($result['success']) {
                LogController::log('Port aktualisiert', '{"switch": "' .  $device->name . '", "info": "Tagged VLANs geändert", "port": "' . $port . '", "vlan": "' . implode(",", $vlans) . '"}');

                $newVlanPortData = self::API_GET_DATA($device->hostname, $cookie, self::$available_apis['vlanport'], $api_version)['data'];
                $device->vlan_port_data =  self::formatPortVlanData($newVlanPortData);
                $device->save();

                foreach ($data_builder['vlan_trunks'] as $vlan) {
                    $vlan = explode("/", $vlan);
                    $vlan = end($vlan);
                    $return[] = [
                        'success' => true,
                        'message' => '[' . $port . '] Tagged VLAN ' . $vlan,
                    ];
                }
            } else {
                $return[] = [
                    'success' => false,
                    'message' => '[' . $port . '] Not Tagged VLAN ' . $vlan,
                ];
            }

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
                        "name": ' . $vlan->name . ',
                        "id": ' . $vlan->vid . '
                    }';

                    $response = self::API_POST_DATA($device->hostname, $cookie, "system/vlans", $api_version, $data);

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
                        "name": "' . $vlan->name . '"
                    }';

                    $response = self::API_PUT_DATA($device->hostname, $cookie, "system/vlans/" . $vlan->vid, $api_version, $data);

                    if ($response['success']) {
                        $return['log'][] = "<span class='tag is-success'>VLAN {$vlan->vid}</span> erfolgreich umbenannt";
                        $i_vlan_chg_name++;
                    } else {
                        if ($vlan->vid != 1) {
                            $return['log'][] = "<span class='tag is-danger'>VLAN {$vlan->vid}</span> konnte nicht umbenannt werden";
                        } else {
                            $return['log'][] = "<span class='tag is-danger'>VLAN {$vlan->vid}</span> VLAN 1 kann nicht umbenannt werden (ArubaCX Einschränkung)";
                        }
                    }
                }
            }

            $vlan_data = self::API_GET_DATA($device->hostname, $cookie, self::$available_apis['vlans'], $api_version)['data'];
            $device->vlan_data = self::formatVlanData($vlan_data);
            $device->save();

            self::API_LOGOUT($device->hostname, $cookie, $api_version);
        }

        $return['log'][] = "</br><b>[Ergebnisse]</b></br>";
        $return['log'][] = $i_vlan_created . " von " . $i_not_found . " VLANs erstellt";
        $return['log'][] = $i_vlan_chg_name . " von " . $i_chg_name . " VLANs umbenannt";

        $return['time'] = number_format(microtime(true) - $start, 2);
        return $return;
    }

    static function refresh($device): Bool
    {
        $device_data = self::API_REQUEST_ALL_DATA($device);

        if (isset($device_data['success']) and $device_data['success'] == false) {
            // return json_encode(['success' => 'false', 'message' => 'Could not get data from device']);
            return false;
        }

        MacAddressController::refreshMacDataFromSwitch($device->id, $device_data['mac_table_data'], $device->uplinks);

        $device->update([
            'mac_table_data' => json_encode($device_data['mac_table_data'], true),
            'vlan_data' => json_encode($device_data['vlan_data'], true),
            'port_data' => json_encode($device_data['ports_data'], true),
            'port_statistic_data' => json_encode($device_data['portstats_data'], true),
            'vlan_port_data' => json_encode($device_data['vlanport_data'], true),
            'system_data' => json_encode($device_data['sysstatus_data'], true)
        ]);
        return true;
    }
}
