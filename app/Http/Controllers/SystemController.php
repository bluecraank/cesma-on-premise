<?php

namespace App\Http\Controllers;

use App\Models\MacTypeIcon;
use App\Models\PublicKey;
use App\Models\User;
use App\Services\MacTypeService;
use App\Services\PublicKeyService;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    public function index_system()
    {
        $users = User::all();
        $keys = PublicKey::all();
        $keys_list = PublicKeyService::getPubkeysDescriptionAsArray();
        $mac_prefixes = MacTypeService::getMacTypes();
        $mac_types = MacTypeService::getMacTypesList(); 
        $mac_vendors = MacTypeService::getMacVendors();
        $mac_icons = MacTypeService::getMacIcons();

        return view('system.index', compact('users', 'keys', 'keys_list', 'mac_prefixes', 'mac_types', 'mac_vendors', 'mac_icons'));
    }

    public function index_usersettings()
    {
        return view('system.view_usersettings');
    }

    public function updateUserRole(Request $request)
    {
        $guid = $request->input('guid');
        $user = User::where('guid', $guid)->firstOrFail();
        if(!$user) {
            return redirect()->back()->withErrors(['message' => 'User not found']);
        }

        $user->role = ($request['role'] == 0) ? 'user' : 'admin';
        if(!$user->save()) {
            return redirect()->back()->withErrors(['message' => 'User could not be updated']);;
        }
        
        return redirect()->back()->with('success', __('Msg.UserRoleUpdated'));
    }
}
