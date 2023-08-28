<?php

namespace App\Devices;

use App\Helper\CLog;
use App\Interfaces\DeviceInterface;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use App\Models\DeviceVlanPort;
use App\Http\Controllers\BackupController;
use App\Models\Device;
use App\Models\DeviceVlan;
use App\Models\Vlan;

class ArubaCX implements DeviceInterface
{
    use \App\Traits\DefaultApiMethods;
    use \App\Traits\DefaultSnmpMethods;
    use \App\Traits\DefaultDevice;

    static $fetch_from = [
        'snmp' => true,
        'api' => true,
    ];

    static $api_auth = [
        "login" => "login",
        "logout" => "logout",
    ];

    static $available_apis = [
        "status" => 'system?attributes=software_version,subsystems,hostname&depth=3',
        "subsystem" => 'system/subsystems/chassis,1?attributes=product_info',
        "vlans" => 'system/vlans?attributes=name,id&depth=2',
        "ports" => 'system/interfaces?attributes=ifindex,vlan_mode,link_state,description&depth=2',
        "portstats" => 'system/interfaces?attributes=ifindex,rate_statistics,statistics,link_speed,description&depth=2',
        "vlanport" => 'system/interfaces?attributes=ifindex,vlan_mode,vlan_tag,vlan_trunks&depth=2',
    ];

    static $port_if_uri = "system/interfaces/1%2F1%2F";

    static function getApiData(Device $device): array
    {
        if (!$device) {
            return ['success' => false, 'data' => 'Device not found', 'message' => "Device not found"];
        }
        if (!$login_info = self::API_LOGIN($device)) {
            return ['success' => false, 'data' => 'API Login failed', 'message' => "'API Login failed'"];
        }

        $data = [];

        list($cookie, $api_version) = explode(";", $login_info);

        foreach (self::$available_apis as $key => $api) {
            $api_data = self::API_GET_DATA($device->hostname, $cookie, $api, $api_version, false);

            if ($api_data['success']) {
                $data[$key] = $api_data['data'];
            }
        }

        // Only get uptime from snmp
        $snmpSysUptime = snmp2_get($device->hostname, 'public', '.1.3.6.1.2.1.1.3.0', 5000000, 1);
        $uptime = self::snmpFormatSystemData(['uptime' => $snmpSysUptime]);

        $data = [
            'informations' => self::formatSystemData($data['status']),
            'vlans' => (isset($data['vlans'])) ? self::formatVlanData($data['vlans']) : [],
            'ports' => (isset($data['ports'])) ? self::formatPortData($data['ports'], $data['portstats']) : [],
            'vlanports' => (isset($data['vlanport'])) ? self::formatPortVlanData($data['vlanport']) : [],
            'statistics' => (isset($data['portstats'])) ? self::formatExtendedPortStatisticData($data['portstats'], $data['ports']) : [],
            'macs' => (isset($data['vlans'])) ? self::formatMacTableData([], $data['vlans'], $device, $cookie, $api_version) : [],
            'uplinks' => (isset($data['ports'])) ? self::formatUplinkData($data['ports']) : [],
            'success' => true,
        ];

        if (isset($uptime['uptime'])) {
            $data['informations']['uptime'] = $uptime['uptime'];
        }

        self::API_LOGOUT($device->hostname, $cookie, $api_version);

        return $data;
    }

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

    static function formatUplinkData($ports): array
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
            $api_data = self::API_GET_DATA($device->hostname, $cookie, $url, $api_version, false);
            if ($api_data['success']) {
                $vlan_macs[$vlan['id']] = $api_data['data'];
            }
        }

        $return = [];
        foreach ($vlan_macs as $key => $macs) {
            foreach ($macs as $mac) {
                $mac_filtered = str_replace(":", "", strtolower($mac['mac_addr']));

                // skip if mac-port ist empty
                if (empty($mac['port']) || !isset($mac['port'])) {
                    continue;
                }

                $return[$mac_filtered] = [
                    // Expecting 1/1/1 as Port, so we need to explode the key
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
                    'vlan_mode' => $port['vlan_mode'] ?? "access",
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

    static function formatExtendedPortStatisticData($portstats, $portdata): array
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

        foreach ($portdata as $port) {
            if ($port['ifindex'] < 1000) {
                $return[$port['ifindex']]['port_status'] = ($port['link_state'] == "up") ? true : false;
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
            BackupController::store(false, false, false, $device);
            return false;
        }

        list($cookie, $api_version) = explode(";", $login_info);

        $data = self::API_GET_DATA($device->hostname, $cookie, "configs/running-config", $api_version, false);
        $dataraw = self::API_GET_DATA($device->hostname, $cookie, "configs/running-config", $api_version, true);

        self::API_LOGOUT($device->hostname, $cookie, $api_version);

        if ($data['success'] && $dataraw['success']) {
            $json = json_encode($data['data'], true);
            $plain = $dataraw['data'];

            if (strlen($json) > 10) {
                BackupController::store(true, $plain, $json, $device);
                return true;
            }
        }

        BackupController::store(false, false, false, $device);
        return false;
    }

    static function restoreBackup($device, $backup, $password_switch): array
    {
        if ($password_switch != Crypt::decrypt($device->password)) {
            return ['success' => false, 'data' => 'Wrong password'];
        }

        if (!$login_info = self::API_LOGIN($device)) {
            return ['success' => false, 'data' => 'API Login failed'];
        }

        list($cookie, $api_version) = explode(";", $login_info);

        $https = config('app.https');

        $api_url = $https . $device->hostname . '/rest/' . $api_version . '/configs/running-config';


        $data = [
            "configuration" => $backup->restore_data,
        ];

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
            $device->last_pubkey_sync->touch();
            return json_encode(['success' => 'true', 'message' => __('Pubkeys.Sync.Success')]);
        } else {
            return json_encode(['success' => 'false', 'message' => __('Pubkeys.Sync.Error')]);
        }
    }

    static function setUntaggedVlanToPort($newVlan, $port, $device, $vlans, $need_login = true, $logindata = ""): bool
    {
        if ($need_login) {
            $login_info = self::API_LOGIN($device);
        } else {
            $login_info = $logindata;
        }

        if ($login_info) {

            list($cookie, $api_version) = explode(";", $login_info);

            $rest_vlans_uri = "/rest/" . $api_version . "/system/vlans/";

            $data = '{
                "vlan_mode": "' . $port->vlan_mode . '",
                "vlan_tag": "' . $rest_vlans_uri . $vlans[$newVlan]['vlan_id'] . '",
                "vlan_trunks": [
                    "' . $rest_vlans_uri . $vlans[$newVlan]['vlan_id'] . '"
                ]
                }';

            $uri = self::$port_if_uri . $port->name;

            $result = self::API_PATCH_DATA($device->hostname, $cookie, $uri, $api_version, $data);

            if ($result['success']) {
                $old = $port->untaggedVlan();

                if ($old) {
                    DeviceVlanPort::where('device_vlan_id', $old)->where('device_port_id', $port->id)->where('device_id', $device->id)->where('is_tagged', false)->delete();
                }

                DeviceVlanPort::updateOrCreate(
                    ['device_id' => $device->id, 'device_port_id' => $port->id, 'device_vlan_id' => $vlans[$newVlan]['id'], 'is_tagged' => false],
                );
                DeviceVlanPort::updateOrCreate(
                    ['device_id' => $device->id, 'device_port_id' => $port->id, 'device_vlan_id' => $vlans[$newVlan]['id'], 'is_tagged' => true],
                );

                return true;
            } else {
                return false;
            }

            if ($need_login) {
                self::API_LOGOUT($device->hostname, $cookie, $api_version);
            }
        }
    }

    static function setTaggedVlansToPort($taggedVlans, $port, $device, $vlans, $need_login = true, $logindata = ""): array
    {
        $uri = self::$port_if_uri . $port->name;

        // Determine which vlans need to set and removed
        $currentVlans = DeviceVlanPort::where('device_id', $device->id)->where('device_port_id', $port->id)->get()->keyBy('device_vlan_id')->toArray();

        $vlansToSet = [];
        $vlansToRemove = [];
        $vlansSucessfullySet = [];
        $vlansSucessfullyRemoved = [];

        if ($need_login) {
            $login_info = self::API_LOGIN($device);
        } else {
            $login_info = $logindata;
        }

        if ($login_info) {

            list($cookie, $api_version) = explode(";", $login_info);

            foreach ($currentVlans as $currentVlan => $value) {
                if (array_key_exists($currentVlan, $taggedVlans)) {
                    // Vlan existiert, keine Änderung
                } else {
                    // Vlan muss entfernt werden, da in der neuen Liste nicht mehr vorhanden
                    // $vlansToSet[$currentVlan['device_vlan_id']] = $currentVlan['device_vlan_id'];
                    $vlansToRemove[$currentVlan] = $currentVlan;
                }
            }

            foreach ($taggedVlans as $taggedVlan => $value) {
                if (array_key_exists($taggedVlan, $currentVlans)) {
                    // Vlan existiert, keine Änderung
                } else {
                    // Vlan muss hinzugefügt werden, da in der neuen Liste vorhanden
                    $vlansToSet[$taggedVlan] = $taggedVlan;
                }
            }
        }

        return [$vlansToSet, $vlansToRemove, $vlansSucessfullySet, $vlansSucessfullyRemoved];
    }

    static function syncVlans($syncable_vlans, $device, $create_vlans, $rename_vlans, $tag_to_uplink, $testmode): array
    {
        $siteVlans = Vlan::where('site_id', $device->site_id)->get()->keyBy('vid')->toArray();
        $current_vlans = DeviceVlan::where('device_id', $device->id)->get()->keyBy('vlan_id')->toArray();
        // syncable_vlans = key random, value vlan_id
        // current_vlans = key vlan_id, value vlan_name

        if (count($current_vlans) == 0) {
            return ['created' => 'No vlan data exists', 'renamed' => 'No vlan data exists', 'tagged_to_uplink' => '	No vlan data exists', 'test' => $testmode, 'status' => 'error', 'message' => 'No vlan data exists'];
        }

        $create_vlans_data = [];
        $rename_vlans_data = [];
        $tag_to_uplink_data = [];

        $result_create_vlans = 0;
        $result_rename_vlans = 0;
        $result_tagged_vlans_to_uplink = 0;

        if (!$testmode && !$login_info = self::API_LOGIN($device)) {
            return ['created' => 0, 'renamed' => 0, 'tagged_to_uplink' => 0, 'test' => $testmode, 'status' => 'error', 'message' => 'API Login failed'];
        }

        if (!$testmode) {
            list($cookie, $api_version) = explode(";", $login_info);
        }

        foreach ($syncable_vlans as $vlan_id) {
            if (!isset($current_vlans[$vlan_id])) {
                $create_vlans_data[$vlan_id] = $vlan_id;
            }

            if (isset($current_vlans[$vlan_id])) {
                if ($current_vlans[$vlan_id]['name'] != $siteVlans[$vlan_id]['name']) {
                    $rename_vlans_data[$vlan_id] = $siteVlans[$vlan_id]['name'];
                }
            }
        }

        if (($create_vlans || $rename_vlans) && ($create_vlans_data || $rename_vlans_data)) {
            foreach ($create_vlans_data as $vlan) {
                $name = $current_vlans[$vlan]['name'] ?? $siteVlans[$vlan]['name'];
                if (isset($rename_vlans_data[$vlan])) {
                    $name = $rename_vlans_data[$vlan];
                }

                $data = '{
                    "name": "' . $name . '",
                    "id": ' . $vlan . '
                }';

                if (!$testmode) {
                    $response = self::API_POST_DATA($device->hostname, $cookie, "system/vlans", $api_version, $data);
                    if ($response['success']) {
                        $result_create_vlans++;
                        if (isset($rename_vlans_data[$vlan])) {
                            $result_rename_vlans++;
                        }
                    }
                } else {
                    $result_create_vlans++;
                    if (isset($rename_vlans_data[$vlan])) {
                        $result_rename_vlans++;
                    }
                }
            }

            if (count($create_vlans_data) == 0 && $rename_vlans) {
                foreach ($rename_vlans_data as $vlan_id => $name) {
                    $data = '{
                        "name": "' . $name . '"
                    }';

                    if (!$testmode) {
                        $response = self::API_PUT_DATA($device->hostname, $cookie, "system/vlans/" . $vlan_id, $api_version, $data);
                        if ($response['success']) {
                            $result_rename_vlans++;
                        }
                    } else {
                        $result_rename_vlans++;
                    }
                }
            }
        }

        $return_create = $create_vlans == "true" ? $result_create_vlans . " of " . count($create_vlans_data) : 'Not enabled';
        $return_rename = $rename_vlans == "true" ? $result_rename_vlans . " of " . count($rename_vlans_data) : 'Not enabled';
        $return_tagged = $tag_to_uplink == "true" ? $result_tagged_vlans_to_uplink . " of " . count($tag_to_uplink_data) : 'Not enabled';

        return ['created' => $return_create, 'renamed' => $return_rename, 'tagged_to_uplink' => $return_tagged, 'test' => $testmode, 'message' => 'Successful'];
    }

    static function setPortDescription($port, $description, $device, $logininfo): bool
    {
        list($cookie, $api_version) = explode(";", $logininfo);

        $data = '{
            "description": "' . $description . '"
        }';

        $response = self::API_PATCH_DATA($device->hostname, $cookie, self::$port_if_uri . $port->name, $api_version, $data);

        if ($response['success']) {
            $port->description = $description;
            $port->save();
        }

        return $response['success'];
    }
}
