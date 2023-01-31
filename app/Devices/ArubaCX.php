<?php

namespace App\Devices;

use App\Http\Controllers\BackupController;
use Illuminate\Support\Facades\Http;
use App\Interfaces\DeviceInterface;
use App\Models\DeviceVlan;
use Illuminate\Support\Facades\Crypt;

class ArubaCX implements DeviceInterface
{
    static $api_auth = [
        "login" => "login",
        "logout" => "logout",
    ];

    static $available_apis = [
        "status" => 'system?attributes=software_version,subsystems,hostname&depth=3',
        "subsystem" => 'system/subsystems/chassis,1?attributes=product_info',
        "vlans" => 'system/vlans?attributes=name,id&depth=2',
        "ports" => 'system/interfaces?attributes=ifindex,link_state,description&depth=2',
        "portstats" => 'system/interfaces?attributes=ifindex,rate_statistics,statistics,link_speed,description&depth=2',
        "vlanport" => 'system/interfaces?attributes=ifindex,vlan_mode,vlan_tag,vlan_trunks&depth=2',
    ];

    static $port_if_uri = "system/interfaces/1%2F1%2F";

    static function API_GET_VERSIONS($hostname): string
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

        return "v10.11";
    }

    static function API_LOGIN($device): string
    {
        $api_version = self::API_GET_VERSIONS($device->hostname);

        $api_url = config('app.https') . $device->hostname . '/rest/' . $api_version . '/' . self::$api_auth['login'];

        $api_username = config('app.api_username');

        $api_password = Crypt::decrypt($device->password);

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
        // $api_url = config('app.https') . $hostname . '/rest/' . $version . '/' . $api;

        // try {
        // } catch (\Exception $e) {
        //     return ['success' => false, 'data' => []];
        // }

        // NOT NEEDED YET

        return ['success' => false, 'data' => []];
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

        // $system_data = self::formatSystemData($data['status']);
        // $vlan_data = self::formatVlanData($data['vlans']);
        // $port_data = self::formatPortData($data['ports']);
        // $portstat_data = self::formatPortSimpleStatisticData($data['portstats']);
        // $vlanport_data = self::formatPortVlanData($data['vlanport']);
        // $mac_data = self::formatMacTableData($data['vlans'], $device, $cookie, $api_version);
        // PortstatsController::store(self::formatExtendedPortStatisticData($data['portstats']), $device->id);

        $data = [
            'informations' => self::formatSystemData($data['status']),
            'vlans' => self::formatVlanData($data['vlans']),
            'ports' => self::formatPortData($data['ports'], $data['portstats']),
            'vlanports' => self::formatPortVlanData($data['vlanport']),
            'statistics' => self::formatExtendedPortStatisticData($data['portstats']),
            'macs' => self::formatMacTableData([], $data['vlans'], $device, $cookie, $api_version),
            'uplinks' => self::formatUplinkData($data['ports']),
            'success' => true,
        ];

        self::API_LOGOUT($device->hostname, $cookie, $api_version);

        return $data;
    }

    static function formatUplinkData($ports)
    {
        $uplinks = [];
        foreach ($ports as $port) {
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
            $return[$vlan['id']] = $vlan['name'];
        }

        return $return;
    }

    static function formatMacTableData($data = null, $vlans = [], $device, $cookie, $api_version): array
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
        if (empty($system) or !is_array($system) or !isset($system)) {
            return [];
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

    static function formatPortData(array $ports, array $stats): array
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
                    'link' => ($port['link_state'] == "up") ? true : false,
                    'trunk_group' => null,
                ];
            }
        }

        foreach ($stats as $stat) {
            if ($stat['ifindex'] < 1000) {
                $return[$stat['ifindex']]['speed'] = ($stat['link_speed'] != 0) ? $stat['link_speed'] / 1000000 : 0;
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
                    "port_speed_mbps" => ($port['link_speed'] != 0) ? $port['link_speed'] / 1000000 : 0,
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
                    $untagged_vlan = $vlanport['vlan_tag'] ?? [];
                    $tagged_vlans = $vlanport['vlan_trunks'] ?? [];


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

                    if (!empty($untagged_vlan)) {
                        $return[$i] = [
                            "port_id" => $vlanport['ifindex'],
                            "vlan_id" => key($untagged_vlan),
                            "is_tagged" => false,
                        ];
                        $i++;
                    }
                }
            }
        }

        return $return;
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
                BackupController::store(true, $encoded, $data, $device);
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

        $api_url = $https . $device->hostname . '/rest/' . $api_version . '/configs/running-config';


        $data = [
            "configuration" => $backup->restore_data,
        ];

        var_dump(json_encode($data));

        $restore = Http::connectTimeout(30)->withoutVerifying()->withHeaders([
            'Cookie' => $cookie,
        ])->put($api_url, $data);

        if ($restore->status() == 200 && $restore->successful()) {
            return ['success' => true, 'data' => 'Restore successful'];
        }

        self::API_LOGOUT($device->hostname, $cookie, $api_version);

        return ['success' => false, 'data' => 'Restore failed: ' . $restore->json() . " (" . $restore->status() . ")"];
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
            return json_encode(['success' => 'true', 'message' => __('Pubkeys.Sync.Success')]);
        } else {
            return json_encode(['success' => 'false', 'message' => __('Pubkeys.Sync.Error')]);
        }
    }

    static function setUntaggedVlanToPort($vlans, $ports, $device, $need_login = true, $logindata = ""): String
    {
        $success = $failed = 0;
        $failed_ports = [];
        $portcount = count($ports);

        $vlans_in_db = DeviceVlan::all()->keyBy('id');

        if ($need_login) {
            $login_info = self::API_LOGIN($device);
        } else {
            $login_info = $logindata;
        }

        if ($login_info) {

            list($cookie, $api_version) = explode(";", $login_info);

            $rest_vlans_uri = "/rest/" . $api_version . "/system/vlans/";

            foreach ($ports as $key => $port) {
                $data = '{
                    "vlan_mode": "native-untagged",
                    "vlan_tag": "' . $rest_vlans_uri . $vlans_in_db[$vlans[$key]]->vlan_id . '",
                    "vlan_trunks": [
                      "' . $rest_vlans_uri . $vlans_in_db[$vlans[$key]]->vlan_id . '"
                    ]
                  }';

                $uri = self::$port_if_uri . $port;

                $result = self::API_PATCH_DATA($device->hostname, $cookie, $uri, $api_version, $data);

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

            if ($failed !== count($ports)) {
                proc_open('php ' . base_path() . '/artisan device:refresh ' . $device->id . ' > /dev/null &', [], $pipes);
            }

            if ($need_login) {
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
        $uri = self::$port_if_uri . $port;

        if ($need_login) {
            $login_info = self::API_LOGIN($device);
        } else {
            $login_info = $logindata;
        }

        if ($login_info) {

            list($cookie, $api_version) = explode(";", $login_info);
            $rest_vlans_uri = "/rest/" . $api_version . "/system/vlans/";

            $port_id = $device->ports()->where('name', $port)->first()->id;
            $alreadyVlans = $device->vlanports()->where('device_port_id', $port_id)->get()->keyBy('device_vlan_id')->toArray();    // Get all tagged vlans from port
            $known_vlans = $device->vlans()->get()->keyBy('id')->toArray();    // Get all known vlans from device

            $data_builder = [];
            $data_builder['vlan_trunks'] = [];
            $data_builder['vlan_tag'] = "";

            $data_builder['vlan_mode'] = "native-untagged";

            var_dump($alreadyVlans);

            foreach ($alreadyVlans as $device_vlan_id => $vlan) {
                if ($vlan['is_tagged'] == 0) {
                    $data_builder['vlan_tag'] = $rest_vlans_uri . $known_vlans[$device_vlan_id]['vlan_id'];
                }
            }

            foreach ($vlans as $vlan) {
                if (!in_array($rest_vlans_uri . $known_vlans[$vlan]['vlan_id'], $data_builder['vlan_trunks'])) {
                    $data_builder['vlan_trunks'][] = $rest_vlans_uri . $known_vlans[$vlan]['vlan_id'];
                }
            }

            if (empty($data_builder['vlan_tag'])) {
                $data_builder['vlan_tag'] = $rest_vlans_uri . "1";
            }
            
            $data = json_encode($data_builder);

            $uri = self::$port_if_uri . $port;
            $result = self::API_PUT_DATA($device->hostname, $cookie, $uri, $api_version, $data);


            if ($result['success']) {
                // LogController::log('Port aktualisiert', '{"switch": "' .  $device->name . '", "info": "Tagged VLANs geändert", "port": "' . $port . '", "vlan": "' . implode(",", $vlans) . '"}');
                foreach ($data_builder['vlan_trunks'] as $vlan) {
                    $vlan = explode("/", $vlan);
                    $vlan = end($vlan);
                    $return[] = [
                        'success' => true,
                        'message' => __('Vlan.Update.Success.Tagged', ['port' => $port, 'vlan' => $vlan]),
                    ];
                }
            } else {
                $return[] = [
                    'success' => false,
                    'message' => __('Vlan.Update.Error.Tagged', ['port' => $port, 'vlan' => "(Alle)"]),
                ];
            }


            $device->vlanports()->where('device_port_id', $port_id)->delete();
            if ($need_login) {
                proc_open('php ' . base_path() . '/artisan device:refresh ' . $device->id . ' > /dev/null &', [], $pipes);
                self::API_LOGOUT($device->hostname, $cookie, $api_version);
            }
            sleep(5);

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
                        "name": "' . $vlan->name . '",
                        "id": ' . $vlan->vid . '
                    }';

                    $response = self::API_POST_DATA($device->hostname, $cookie, "system/vlans", $api_version, $data);

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
                        "name": "' . $vlan->name . '"
                    }';

                    $response = self::API_PUT_DATA($device->hostname, $cookie, "system/vlans/" . $vlan->vid, $api_version, $data);

                    if ($response['success']) {
                        $return['log'][] = "<span class='tag is-success'>VLAN {$vlan['vid']}</span> " . __('Vlan.Sync.Log.Renamed');
                        $i_vlan_chg_name++;
                    } else {
                        if ($vlan->vid != 1) {
                            $return['log'][] = "<span class='tag is-danger'>VLAN {$vlan['vid']}</span> " . __('Vlan.Sync.Log.NotRenamed');
                        } else {
                            $return['log'][] = "<span class='tag is-danger'>VLAN {$vlan->vid}</span> " . __('Vlan.Sync.Expected.NotRenamed');
                        }
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

    static function setPortName($port, $name, $device, $logininfo)
    {
        list($cookie, $api_version) = explode(";", $logininfo);

        $data = '{
            "description": "' . $name . '"
        }';

        $response = self::API_PATCH_DATA($device->hostname, $cookie, self::$port_if_uri . $port, $api_version, $data);

        if ($response['success']) {
            $device->ports()->where('name', $port)->update(['description' => $name]);
            return json_encode(['success' => "true", 'message' => __('Msg.ApiPortNameSet')]);
        } else {
            return json_encode(['success' => "false", 'message' => __('Msg.ApiPortNameNotSet')]);
        }
    }
}
