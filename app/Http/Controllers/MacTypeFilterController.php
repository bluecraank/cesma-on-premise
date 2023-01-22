<?php

namespace App\Http\Controllers;

use App\Models\MacTypeFilter;
use App\Models\MacTypeIcon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class MacTypeFilterController extends Controller
{
    public function store(Request $request) {

        $validator = Validator::make($request->all(), [
            'mac_prefix' => 'required|min:6|string',
            'mac_type' => 'string|nullable',
            'mac_desc' => 'string|nullable'
        ])->validate();

        $mac_prefix = $request->input('mac_prefix');
        $mac_prefix = strtolower(str_replace(":", "", $mac_prefix));
        if(strlen($mac_prefix) > 6) {
            $mac_prefix = substr($mac_prefix, 0, 6);
        }

        if($request->input('mac_type_input') == '' || $request->input('mac_type_input') == null) {
            $type = $request->input('mac_type');
        } else {
            $type = $request->input('mac_type_input');
        }

        $macTypeFilter = new MacTypeFilter();
        $macTypeFilter->mac_prefix = $mac_prefix;
        $macTypeFilter->mac_type = $type;
        $macTypeFilter->mac_desc = $request->mac_desc;
        $macTypeFilter->save();

        return redirect()->back()->with('success', 'Mac Type Filter added');

    }

    public function destroy(Request $request) {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer'
        ])->validate();

        if(MacTypeFilter::find($request->input('id'))->delete()) {
            return redirect()->back()->with(['success' => 'Succesfully deleted MAC Type']);
        }

        return redirect()->back()->with(['error' => 'Coult not delete MAC Type']);

    }

    public function storeIcon(Request $request) {

        $validator = Validator::make($request->all(), [
            'mac_type' => 'required|string',
            'mac_icon' => 'required|string|starts_with:fa-'
        ])->validate();

        $macTypeIcon = new MacTypeIcon();
        $macTypeIcon->mac_type = $request->mac_type;
        $macTypeIcon->mac_icon = $request->mac_icon;
        $macTypeIcon->save();

        return redirect()->back()->with('success', 'Mac Type Icon added');

    }
}
