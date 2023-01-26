<?php

namespace App\Services;

use App\Models\Vlan;

class VlanService
{
    static function createIfNotExists($device, $vid, $vname) {
        Vlan::firstOrCreate([
            'vid' => $vid,
        ],
        [
            'name' => $vname,
            'description' => 'Found on '. $device->name,
        ]);
    }

    static function createVlan($request, $scan, $sync) {
        Vlan::create([
            'name' => $request->name,
            'vid' => $request->vid,
            'description' => $request->description,
            'location_id' => $request->location_id,
            'ip_range' => $request->ip_range,
            'is_client_vlan' => $request->is_client_vlan,
            'is_synced' => $sync,
            'is_scanned' => $scan,
        ]);
    }
    
    static function updateVlan($request, $scan, $sync, $is_client_vlan) {
        Vlan::whereId($request['id'])->update([
            'name' => $request['name'],
            'description' => $request['description'],
            'ip_range' => $request['ip_range'] ?? null,
            'is_scanned' => $scan,
            'is_synced' => $sync,
            'is_client_vlan' => $is_client_vlan,
        ]);
    }
}

?>