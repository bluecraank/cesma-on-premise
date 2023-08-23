<?php

namespace App\Services;

use App\Models\Vlan;
use App\Models\VlanTemplate;
use App\Helper\CLog;
use Illuminate\Support\Facades\Auth;

class VlanService
{
    static function createIfNotExists($device, $vid, $vname) {
        Vlan::firstOrCreate([
            'vid' => $vid,
            'site_id' => $device->site_id,
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
            'site_id' => $request->site_id,
            'ip_range' => $request->ip_range,
            'is_client_vlan' => $request->is_client_vlan,
            'is_synced' => $sync,
            'is_scanned' => $scan,
        ]);
    }
    
    static function updateVlan($request, $sync, $is_client_vlan) {
        $vlan = Vlan::where('vid', $request['vid'])->where('site_id', Auth::user()->currentSite()->id)->first();
        
        if(!$vlan) {
            CLog::error("VLAN", "VLAN {$request->input('name')} ({$request->input('vid')}) not found");
            return false;
        }

        $updated = $vlan->update([
            'name' => $request['name'],
            'description' => $request['description'],
            'ip_range' => $request['ip_range'] ?? null,
            'is_synced' => $sync,
            'is_client_vlan' => $is_client_vlan,
        ]);
        
        return $updated;

        CLog::info("VLAN", "VLAN {$request->input('name')} ({$vlan->vid}) updated");

    }

    static function createVlanTaggingTemplate($request) {
        $name = $request['name'];
        $vlans = $request['vlans_selected'];
        $vlan_ids = Vlan::all()->keyBy('id')->toArray();

        $store_vlans = [];
        foreach($vlans as $vlan) {
            $store_vlans[] = $vlan_ids[$vlan]['vid'];
        }

        $vlanTemplate = VlanTemplate::create([
            'name' => $name,
            'vlans' => json_encode($store_vlans),
            'type' => 'tagging',
        ]);

        if($vlanTemplate) {
            return true;
        } else {
            return false;
        }
    }
}

?>