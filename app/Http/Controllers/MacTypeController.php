<?php

namespace App\Http\Controllers;

use App\Helper\CLog;
use App\Models\MacType;
use App\Models\MacTypeIcon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MacTypeController extends Controller
{
    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {

    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
            'mac_icon' => 'mdi-help-circle-outline'
        ]);

        CLog::info("MAC Prefixes", "Create mac prefix {$mac_prefix} for type {$mac_type}", null, $request->input('mac_desc'));

        return redirect()->back()->with('success', __('Successfully created mac prefix :prefix', ['prefix' => $mac_prefix]));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mac_icon' => 'required|min:3|max:255|starts_with:mdi-',
            'mac_type' => 'required',
        ])->validate();

        $old = MacTypeIcon::where('mac_type', $request->input('mac_type'))->first()->mac_icon;

        MacTypeIcon::updateOrCreate(
            ['mac_type' => $request->input('mac_type')],
            ['mac_icon' => $request->input('mac_icon')]
        );

        CLog::info("MAC Prefixes", "Updated icon for mac type ". $request->input('mac_type'), null, $old." => ".$request->input('mac_icon'));

        return redirect()->back()->with('success', __('Successfully changed icon for mac prefixes of type :type', ['type' => $request->input('mac_type')]))->withInput(['last_tab' => 'macs']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request)
    {
        $id = $request->input('id');
        $mac_type = MacType::where('id', $id)->firstOrFail();

        if(!$mac_type) {
            return redirect()->back()->withErrors(['message' => 'MAC prefix not found']);
        }

        $icon = MacTypeIcon::where('mac_type', $mac_type->type)->first();
        if($icon->prefixes->count() == 1) {
            $icon->delete();
        }

        if(!$mac_type->delete()) {
            return redirect()->back()->withErrors(['message' => 'MAC prefix could not be deleted']);
        }

        CLog::info("MAC Prefixes", "Mac prefix {$mac_type->mac_prefix} {$mac_type->type} {$mac_type->description} deleted");

        return redirect()->back()->with('success', __('Successfully mac prefix deleted'));
    }
}
