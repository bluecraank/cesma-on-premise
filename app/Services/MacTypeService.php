<?php

namespace App\Services;

use App\Models\MacType;
use App\Models\MacTypeIcon;
use App\Models\MacVendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helper\CLog;

class MacTypeService
{
    static function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'mac_prefix' => 'required|string|min:6|max:18',
            'mac_desc' => 'required',
        ])->validate();

        $mac_type = $request->input('mac_type');
        if($request->input('mac_type') == null and $request->input('mac_type_input') != null) {
            $mac_type = $request->input('mac_type_input'); 
        }

        $mac_prefix = str_replace(['-', ':', ' '], '', $request->input('mac_prefix'));
        $mac_prefix = substr($mac_prefix, 0, 6);

        $chunks = str_split($mac_prefix."000000", 2);
        $result = implode(':', $chunks);
        
        if(!filter_var($result, FILTER_VALIDATE_MAC)) {
            return redirect()->back()->withErrors(['message' => 'Invalid MAC Prefix: '.$mac_prefix])->withInput(['last_tab' => 'macs']);
        }

        if(MacType::where('mac_prefix', $mac_prefix)->first()) {
            return redirect()->back()->withErrors(['message' => 'MAC Prefix already exists: '.$mac_prefix])->withInput(['last_tab' => 'macs']);
        }

        MacType::create([
            'mac_prefix' => $mac_prefix,
            'type' => $mac_type,
            'description' => $request->input('mac_desc'),
        ]);

        MacTypeIcon::firstOrCreate([
            'mac_type' => $mac_type,
            'mac_icon' => 'fa-question-circle'
        ]);

        CLog::info("MacTypes", "Create MacType {$mac_prefix} {$mac_type} {$request->input('mac_desc')}");

        return redirect()->back()->with('success', __('Msg.MacTypeAdded'))->withInput(['last_tab' => 'macs']);
    }
    
    static function delete(Request $request) {
        $id = $request->input('id');
        $mac_type = MacType::where('id', $id)->firstOrFail();

        if(!$mac_type) {
            return redirect()->back()->withErrors(['message' => 'MAC Type not found'])->withInput(['last_tab' => 'macs']);
        }

        $icon = MacTypeIcon::where('mac_type_id', $mac_type->id)->delete();
        if(!$mac_type->delete()) {
            return redirect()->back()->withErrors(['message' => 'MAC Type could not be deleted'])->withInput(['last_tab' => 'macs']);
        }

        CLog::info("MacTypes", "Delete MacType {$mac_type->mac_prefix} {$mac_type->type} {$mac_type->description}");

        return redirect()->back()->with('success', __('Msg.MacTypeDeleted'))->withInput(['last_tab' => 'macs']);
    }

    static function storeIcon(Request $request) {

        $validator = Validator::make($request->all(), [
            'mac_icon' => 'required|min:3|max:255|starts_with:fa-',
            'mac_type' => 'required',
        ])->validate();

        MacTypeIcon::updateOrCreate(
            ['mac_type' => $request->input('mac_type')],
            ['mac_icon' => $request->input('mac_icon')]
        );

        CLog::info("MacTypes", "Updated mac type icon", null, 'Icon '.$request->input('mac_icon').' for mac type '.$request->input('mac_type').' updated');

        return redirect()->back()->with('success', __('Msg.MacTypeIconAdded'))->withInput(['last_tab' => 'macs']);

    }

    public static function getMacTypes()
    {
        return MacType::all()->sortBy('mac_type');
    }

    public static function getMacTypesList()
    {
        return MacType::all()->sortBy('type')->unique();
    }

    public static function getMacVendors()
    {
        return MacVendor::all()->keyBy('mac_prefix');
    }

    public static function getMacIcons()
    {
        return MacTypeIcon::all();
    }
}