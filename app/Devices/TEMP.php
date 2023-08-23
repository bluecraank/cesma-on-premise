 <?php
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

            $untaggedVlan = $device->vlanports()->where('device_port_id', $port->id)->where('is_tagged', false)->first();    // Get all tagged vlans from port
            $currentTaggedVlan = $device->vlanports()->where('device_port_id', $port->id)->where('is_tagged', true)->get();    // Get all tagged vlans from port
            $taggedVlanArray = $currentTaggedVlan->pluck('device_vlan_id')->toArray();
            $data_builder = [];
            $data_builder['vlan_trunks'] = [];
            $data_builder['vlan_mode'] = "native-untagged";

            if (isset($untaggedVlan->device_vlan_id)) {
                $data_builder['vlan_tag'] = $rest_vlans_uri . $vlans[$untaggedVlan->device_vlan_id]['vlan_id'] ?? $rest_vlans_uri . "1";
            }

            foreach ($taggedVlans as $vlan => $value) {
                if (!in_array($rest_vlans_uri . $vlans[$vlan]['vlan_id'], $data_builder['vlan_trunks'])) {
                    $data_builder['vlan_trunks'][] = $rest_vlans_uri . $vlans[$vlan]['vlan_id'];
                }
            }

            $data = json_encode($data_builder);

            $uri = self::$port_if_uri . $port->name;
            $result = self::API_PUT_DATA($device->hostname, $cookie, $uri, $api_version, $data);

            if ($result['success']) {
                DeviceVlanPort::where('device_port_id', $port->id)->where('device_id', $device->id)->where('is_tagged', true)->delete();

                foreach($currentTaggedVlan as $vlan) {
                    if(!in_array($vlan->device_vlan_id, $taggedVlans)) {
                        $return['remove'][$vlans[$vlan->device_vlan_id]['vlan_id']] = true;
                    }
                }

                if(isset($return['remove'])) {
                    $return['count_remove'] = count($return['remove']);
                }

                foreach ($taggedVlans as $vlan) {
                    if(!in_array($vlans[$vlan]['id'], $taggedVlanArray)) {
                        $return['added'][$vlans[$vlan]['vlan_id']] = true;
                    }

                    DeviceVlanPort::updateOrCreate(
                        ['device_id' => $device->id, 'device_port_id' => $port->id, 'device_vlan_id' => $vlans[$vlan]['id'], 'is_tagged' => true],
                    );
                }

                if(isset($return['added'])) {
                    $return['count_add'] = count($return['added']);
                }
            } else {
                foreach ($taggedVlans as $vlan) {
                    if($currentTaggedVlan->count() > count($taggedVlans)) {
                        $return['count_remove'] = $currentTaggedVlan->count()-count($taggedVlans);
                        if(!in_array($vlans[$vlan]['id'], $taggedVlanArray)) {
                            $return['remove'][$vlans[$vlan]['vlan_id']] = false;
                        }
                    } else {
                        $return['count_add'] = count($taggedVlans);
                        $return['added'][$vlans[$vlan]['vlan_id']] = false;
                    }
                }
            }

            if ($need_login) {
                proc_open('php ' . base_path() . '/artisan device:refresh ' . $device->id . ' api > /dev/null &', [], $pipes);
                self::API_LOGOUT($device->hostname, $cookie, $api_version);
            }

            CLog::info("Port", __('Tagged vlans of port :port changed'), null, json_encode(['port' => $port->name, 'added' => $return['count_add'] ?? 0, 'removed' => $return['count_remove'] ?? 0]));

            return $return;
        }

        return ['success' => false, 'data' => ''];
