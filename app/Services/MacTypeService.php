<?php

namespace App\Services;

use App\Models\MacType;
use App\Models\MacTypeIcon;
use App\Models\MacVendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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
            return redirect()->back()->withErrors(['message' => 'Invalid MAC Prefix: '.$mac_prefix]);
        }

        if(MacType::where('mac_prefix', $mac_prefix)->first()) {
            return redirect()->back()->withErrors(['message' => 'MAC Prefix already exists: '.$mac_prefix]);
        }

        MacType::create([
            'mac_prefix' => $mac_prefix,
            'type' => $mac_type,
            'description' => $request->input('mac_desc'),
        ]);

        return redirect()->back()->with('success', __('Msg.MacTypeAdded'));
    }
    
    static function delete(Request $request) {
        $id = $request->input('id');
        $mac_type = MacType::where('id', $id)->firstOrFail();

        if(!$mac_type) {
            return redirect()->back()->withErrors(['message' => 'MAC Type not found']);
        }

        if(!$mac_type->delete()) {
            return redirect()->back()->withErrors(['message' => 'MAC Type could not be deleted']);
        }

        return redirect()->back()->with('success', __('Msg.MacTypeDeleted'));
    }

    static function storeIcon(Request $request) {
        $id = $request->input('id');

        // dd($request->all());
        $mac_type = MacType::where('type', $id)->firstOrFail();

        if(!$mac_type) {
            return redirect()->back()->withErrors(['message' => 'MAC Type not found']);
        }

        $validator = Validator::make($request->all(), [
            'mac_icon' => 'required|min:3|max:255|starts_with:fa-',
        ])->validate();

        $icon = $request->input('mac_icon');

        MacTypeIcon::updateOrCreate(
            ['mac_type_id' => $id],
            ['mac_icon' => $icon]
        );

        return redirect()->back()->with('success', __('Msg.MacTypeIconAdded'));

    }

    public static function getMacTypes()
    {
        return MacType::all()->sortBy('mac_type');
    }

    public static function getMacTypesList()
    {
        return MacType::all()->sortBy('type')->pluck('type')->unique();
    }

    public static function getMacVendors()
    {
        return MacVendor::all()->keyBy('mac_prefix');
    }

    public static function getMacIcons()
    {
        return MacTypeIcon::all()->keyBy('mac_type');
    }
}